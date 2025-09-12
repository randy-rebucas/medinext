<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\Doctor;
use App\Models\User;
use App\Models\Patient;
use App\Models\Appointment;
use App\Models\Encounter;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;
use Illuminate\Support\Facades\Auth;




class DoctorController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/doctors",
     *     summary="Get all doctors",
     *     description="Retrieve a paginated list of doctors with optional filtering",
     *     operationId="getDoctors",
     *     tags={"Doctors"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(type="integer", example=15)
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search term for doctor name or specialization",
     *         required=false,
     *         @OA\Schema(type="string", example="Cardiology")
     *     ),
     *     @OA\Parameter(
     *         name="clinic_id",
     *         in="query",
     *         description="Filter by clinic ID",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="specialization",
     *         in="query",
     *         description="Filter by specialization",
     *         required=false,
     *         @OA\Schema(type="string", example="Cardiology")
     *     ),
     *     @OA\Parameter(
     *         name="is_available",
     *         in="query",
     *         description="Filter by availability",
     *         required=false,
     *         @OA\Schema(type="boolean", example=true)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Doctors retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Doctors retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/Doctor")
     *                 ),
     *                 @OA\Property(property="pagination", ref="#/components/schemas/Pagination")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Permission check is handled by middleware, but we can add additional validation
            $this->requirePermission('doctors.view');

            $currentClinic = $this->getCurrentClinic();
            if (!$currentClinic) {
                return $this->errorResponse('No clinic access', null, 403);
            }

            $query = Doctor::with(['user', 'clinic'])
                ->where('clinic_id', $currentClinic->id);

            // Apply filters
            if ($request->has('search')) {
                $search = $request->get('search');
                $query->where(function ($q) use ($search) {
                    $q->where('specialization', 'like', "%{$search}%")
                      ->orWhere('license_number', 'like', "%{$search}%")
                      ->orWhereHas('user', function ($userQuery) use ($search) {
                          $userQuery->where('name', 'like', "%{$search}%");
                      });
                });
            }

            if ($request->has('clinic_id')) {
                $clinicId = $request->get('clinic_id');
                $this->requireClinicAccess($clinicId);
                $query->where('clinic_id', $clinicId);
            }

            if ($request->has('specialization')) {
                $query->where('specialization', $request->get('specialization'));
            }

            if ($request->has('is_available')) {
                $query->where('is_available', $request->get('is_available'));
            }

            $perPage = $request->get('per_page', 15);
            $doctors = $query->orderBy('created_at', 'desc')->paginate($perPage);

            return $this->successResponse($doctors, 'Doctors retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve doctors: ' . $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *     path="/api/doctors",
     *     summary="Create a new doctor",
     *     description="Create a new doctor record",
     *     operationId="createDoctor",
     *     tags={"Doctors"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id", "specialization", "license_number"},
     *             @OA\Property(property="user_id", type="integer", example=1),
     *             @OA\Property(property="specialization", type="string", example="Cardiology"),
     *             @OA\Property(property="license_number", type="string", example="MD123456"),
     *             @OA\Property(property="experience_years", type="integer", example=10),
     *             @OA\Property(property="consultation_fee", type="number", format="float", example=150.00),
     *             @OA\Property(property="is_available", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Doctor created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Doctor created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Doctor")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|exists:users,id',
                'specialization' => 'required|string|max:255',
                'license_number' => 'required|string|max:100|unique:doctors,license_number',
                'experience_years' => 'nullable|integer|min:0|max:50',
                'consultation_fee' => 'nullable|numeric|min:0',
                'is_available' => 'nullable|boolean'
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation failed', $validator->errors(), 422);
            }

            $doctorData = $request->all();
            $doctorData['clinic_id'] = Auth::user()->current_clinic_id ?? 1;

            $doctor = Doctor::create($doctorData);
            $doctor->load(['user', 'clinic']);

            return $this->successResponse($doctor, 'Doctor created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create doctor: ' . $e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *     path="/api/doctors/{id}",
     *     summary="Get a specific doctor",
     *     description="Retrieve a specific doctor by ID",
     *     operationId="getDoctor",
     *     tags={"Doctors"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Doctor ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Doctor retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Doctor retrieved successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Doctor")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Doctor not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function show($id): JsonResponse
    {
        try {
            $doctor = Doctor::with(['user', 'clinic'])->findOrFail($id);
            return $this->successResponse($doctor, 'Doctor retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Doctor not found', null, 404);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/doctors/{id}",
     *     summary="Update a doctor",
     *     description="Update an existing doctor record",
     *     operationId="updateDoctor",
     *     tags={"Doctors"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Doctor ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="specialization", type="string", example="Cardiology"),
     *             @OA\Property(property="license_number", type="string", example="MD123456"),
     *             @OA\Property(property="experience_years", type="integer", example=10),
     *             @OA\Property(property="consultation_fee", type="number", format="float", example=150.00),
     *             @OA\Property(property="is_available", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Doctor updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Doctor updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Doctor")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Doctor not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $doctor = Doctor::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'specialization' => 'sometimes|required|string|max:255',
                'license_number' => 'sometimes|required|string|max:100|unique:doctors,license_number,' . $id,
                'experience_years' => 'nullable|integer|min:0|max:50',
                'consultation_fee' => 'nullable|numeric|min:0',
                'is_available' => 'sometimes|boolean'
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation failed', $validator->errors(), 422);
            }

            $doctor->update($request->all());
            $doctor->load(['user', 'clinic']);

            return $this->successResponse($doctor, 'Doctor updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update doctor: ' . $e->getMessage());
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/doctors/{id}",
     *     summary="Delete a doctor",
     *     description="Delete a doctor record (soft delete)",
     *     operationId="deleteDoctor",
     *     tags={"Doctors"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Doctor ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Doctor deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Doctor deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Doctor not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function destroy($id): JsonResponse
    {
        try {
            $doctor = Doctor::findOrFail($id);
            $doctor->delete();

            return $this->successResponse(null, 'Doctor deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to delete doctor: ' . $e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *     path="/api/doctors/{id}/patients",
     *     summary="Get doctor's patients",
     *     description="Retrieve all patients for a specific doctor",
     *     operationId="getDoctorPatients",
     *     tags={"Doctors"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Doctor ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Doctor's patients retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Doctor's patients retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Patient")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Doctor not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function patients($id): JsonResponse
    {
        try {
            $doctor = Doctor::findOrFail($id);
            $patients = $doctor->patients()->with(['appointments'])->get();

            return $this->successResponse($patients, 'Doctor\'s patients retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve doctor\'s patients: ' . $e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *     path="/api/doctors/search",
     *     summary="Search doctors",
     *     description="Search doctors by name, specialization, or other criteria",
     *     operationId="searchDoctors",
     *     tags={"Doctors"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="q",
     *         in="query",
     *         description="Search query",
     *         required=true,
     *         @OA\Schema(type="string", example="Cardiology")
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Maximum number of results",
     *         required=false,
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Search results retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Search results retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Doctor")
     *             )
     *         )
     *     )
     * )
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $query = $request->get('q');
            $limit = $request->get('limit', 10);

            if (empty($query)) {
                return $this->errorResponse('Search query is required', null, 400);
            }

            $doctors = Doctor::with(['user', 'clinic'])
                ->where('specialization', 'like', "%{$query}%")
                ->orWhere('license_number', 'like', "%{$query}%")
                ->orWhereHas('user', function ($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%");
                })
                ->limit($limit)
                ->get();

            return $this->successResponse($doctors, 'Search results retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to search doctors: ' . $e->getMessage());
        }
    }

    /**
     * Get doctor appointments
     */
    public function appointments($id): JsonResponse
    {
        try {
            $doctor = Doctor::findOrFail($id);
            $appointments = Appointment::with(['patient', 'clinic'])
                ->where('doctor_id', $id)
                ->orderBy('appointment_date', 'desc')
                ->orderBy('appointment_time', 'desc')
                ->get();

            return $this->successResponse($appointments, 'Doctor appointments retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to get doctor appointments: ' . $e->getMessage());
        }
    }

    /**
     * Get doctor encounters
     */
    public function encounters($id): JsonResponse
    {
        try {
            $doctor = Doctor::findOrFail($id);
            $encounters = Encounter::with(['patient', 'clinic'])
                ->where('doctor_id', $id)
                ->orderBy('created_at', 'desc')
                ->get();

            return $this->successResponse($encounters, 'Doctor encounters retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to get doctor encounters: ' . $e->getMessage());
        }
    }

    /**
     * Get doctor availability
     */
    public function availability($id): JsonResponse
    {
        try {
            $doctor = Doctor::findOrFail($id);
            
            // Implementation for getting doctor availability would go here
            // This is a placeholder response
            $availability = [
                'monday' => ['09:00-17:00'],
                'tuesday' => ['09:00-17:00'],
                'wednesday' => ['09:00-17:00'],
                'thursday' => ['09:00-17:00'],
                'friday' => ['09:00-17:00'],
                'saturday' => [],
                'sunday' => []
            ];

            return $this->successResponse($availability, 'Doctor availability retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to get doctor availability: ' . $e->getMessage());
        }
    }

    /**
     * Update doctor availability
     */
    public function updateAvailability(Request $request, $id): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'availability' => 'required|array',
                'availability.*' => 'array',
                'availability.*.*' => 'string'
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation failed', $validator->errors(), 422);
            }

            $doctor = Doctor::findOrFail($id);
            
            // Implementation for updating doctor availability would go here
            // This is a placeholder response
            
            return $this->successResponse(null, 'Doctor availability updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update doctor availability: ' . $e->getMessage());
        }
    }

    /**
     * Get doctor statistics
     */
    public function statistics($id): JsonResponse
    {
        try {
            $doctor = Doctor::findOrFail($id);
            
            $stats = [
                'total_appointments' => Appointment::where('doctor_id', $id)->count(),
                'total_patients' => Appointment::where('doctor_id', $id)->distinct('patient_id')->count(),
                'completed_appointments' => Appointment::where('doctor_id', $id)->where('status', 'completed')->count(),
                'cancelled_appointments' => Appointment::where('doctor_id', $id)->where('status', 'cancelled')->count(),
                'this_month_appointments' => Appointment::where('doctor_id', $id)
                    ->whereMonth('appointment_date', now()->month)
                    ->whereYear('appointment_date', now()->year)
                    ->count()
            ];

            return $this->successResponse($stats, 'Doctor statistics retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to get doctor statistics: ' . $e->getMessage());
        }
    }
}
