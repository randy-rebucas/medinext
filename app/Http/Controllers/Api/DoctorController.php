<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\Doctor;
use App\Models\User;
use App\Models\Clinic;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use OpenApi\Annotations as OA;

class DoctorController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/v1/doctors",
     *     summary="Get all doctors",
     *     description="Retrieve a paginated list of doctors for the current clinic",
     *     tags={"Doctors"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page",
     *         @OA\Schema(type="integer", example=15)
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search by name, email, or specialty",
     *         @OA\Schema(type="string", example="Dr. Smith")
     *     ),
     *     @OA\Parameter(
     *         name="specialty",
     *         in="query",
     *         description="Filter by specialty",
     *         @OA\Schema(type="string", example="Cardiology")
     *     ),
     *     @OA\Parameter(
     *         name="is_active",
     *         in="query",
     *         description="Filter by active status",
     *         @OA\Schema(type="boolean", example=true)
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="Sort field",
     *         @OA\Schema(type="string", enum={"id","specialty","license_no","is_active","created_at"})
     *     ),
     *     @OA\Parameter(
     *         name="direction",
     *         in="query",
     *         description="Sort direction",
     *         @OA\Schema(type="string", enum={"asc","desc"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Doctors retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Doctors retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 allOf={
     *                     @OA\Schema(ref="#/components/schemas/Pagination"),
     *                     @OA\Schema(
     *                         @OA\Property(
     *                             property="data",
     *                             type="array",
     *                             @OA\Items(ref="#/components/schemas/Doctor")
     *                         )
     *                     )
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="No clinic access",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $currentClinic = $this->getCurrentClinic();

            if (!$currentClinic) {
                return $this->errorResponse('No clinic access', null, 403);
            }

            [$perPage, $page] = $this->getPaginationParams($request);
            [$sort, $direction] = $this->getSortingParams($request, [
                'id', 'specialty', 'license_no', 'is_active', 'created_at'
            ]);

            $query = Doctor::where('clinic_id', $currentClinic->id)
                ->with(['user', 'clinic']);

            // Search functionality
            if ($request->has('search')) {
                $search = $request->get('search');
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            // Filter by specialty
            if ($request->has('specialty')) {
                $query->where('specialty', $request->get('specialty'));
            }

            // Filter by active status
            if ($request->has('is_active')) {
                $query->where('is_active', $request->boolean('is_active'));
            }

            $doctors = $query->orderBy($sort, $direction)
                ->paginate($perPage, ['*'], 'page', $page);

            return $this->paginatedResponse($doctors, 'Doctors retrieved successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/doctors",
     *     summary="Create a new doctor",
     *     description="Create a new doctor profile and user account",
     *     tags={"Doctors"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password","password_confirmation","specialty","license_no"},
     *             @OA\Property(property="name", type="string", example="Dr. John Smith"),
     *             @OA\Property(property="email", type="string", format="email", example="doctor@clinic.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="password123"),
     *             @OA\Property(property="phone", type="string", example="+1234567890"),
     *             @OA\Property(property="specialty", type="string", example="Cardiology"),
     *             @OA\Property(property="license_no", type="string", example="MD123456"),
     *             @OA\Property(property="signature_url", type="string", format="url", example="https://example.com/signature.png"),
     *             @OA\Property(property="consultation_fee", type="number", format="float", example=150.00),
     *             @OA\Property(
     *                 property="availability_schedule",
     *                 type="object",
     *                 example={
     *                     "monday": {"start": "09:00", "end": "17:00"},
     *                     "tuesday": {"start": "09:00", "end": "17:00"},
     *                     "wednesday": {"start": "09:00", "end": "17:00"},
     *                     "thursday": {"start": "09:00", "end": "17:00"},
     *                     "friday": {"start": "09:00", "end": "17:00"}
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Doctor created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Doctor created successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="doctor", ref="#/components/schemas/Doctor")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="No clinic access",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
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
            $currentClinic = $this->getCurrentClinic();

            if (!$currentClinic) {
                return $this->errorResponse('No clinic access', null, 403);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email',
                'password' => 'required|string|min:8|confirmed',
                'phone' => 'nullable|string|max:20',
                'specialty' => 'required|string|max:255',
                'license_no' => 'required|string|max:255|unique:doctors,license_no',
                'signature_url' => 'nullable|url|max:500',
                'consultation_fee' => 'nullable|numeric|min:0',
                'availability_schedule' => 'nullable|array',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            // Create user first
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'is_active' => true,
            ]);

            // Create doctor profile
            $doctor = Doctor::create([
                'user_id' => $user->id,
                'clinic_id' => $currentClinic->id,
                'specialty' => $request->specialty,
                'license_no' => $request->license_no,
                'signature_url' => $request->signature_url,
                'consultation_fee' => $request->consultation_fee,
                'availability_schedule' => $request->availability_schedule,
                'is_active' => true,
            ]);

            // Assign clinic access
            $user->clinics()->attach($currentClinic->id, [
                'role_id' => 2, // Assuming 2 is doctor role
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $doctor->load(['user', 'clinic']);

            return $this->successResponse([
                'doctor' => $doctor,
            ], 'Doctor created successfully', 201);

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/doctors/{doctor}",
     *     summary="Get doctor details",
     *     description="Retrieve detailed information about a specific doctor",
     *     tags={"Doctors"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="doctor",
     *         in="path",
     *         description="Doctor ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Doctor details retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Doctor retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="doctor", ref="#/components/schemas/Doctor"),
     *                 @OA\Property(
     *                     property="statistics",
     *                     type="object",
     *                     @OA\Property(property="total_appointments", type="integer", example=150),
     *                     @OA\Property(property="total_patients", type="integer", example=75),
     *                     @OA\Property(property="total_encounters", type="integer", example=200)
     *                 ),
     *                 @OA\Property(
     *                     property="recent_patients",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/Patient")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="No access to this doctor",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Doctor not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function show(Doctor $doctor): JsonResponse
    {
        try {
            if (!$this->hasClinicAccess($doctor->clinic_id)) {
                return $this->forbiddenResponse('No access to this doctor');
            }

            $doctor->load(['user', 'clinic']);

            return $this->successResponse([
                'doctor' => $doctor,
                'statistics' => $doctor->statistics,
                'recent_patients' => $doctor->recent_patients,
            ], 'Doctor retrieved successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/doctors/{doctor}",
     *     summary="Update doctor",
     *     description="Update doctor information and profile",
     *     tags={"Doctors"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="doctor",
     *         in="path",
     *         description="Doctor ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Dr. John Smith"),
     *             @OA\Property(property="email", type="string", format="email", example="doctor@clinic.com"),
     *             @OA\Property(property="phone", type="string", example="+1234567890"),
     *             @OA\Property(property="specialty", type="string", example="Cardiology"),
     *             @OA\Property(property="license_no", type="string", example="MD123456"),
     *             @OA\Property(property="signature_url", type="string", format="url", example="https://example.com/signature.png"),
     *             @OA\Property(property="consultation_fee", type="number", format="float", example=150.00),
     *             @OA\Property(property="is_active", type="boolean", example=true),
     *             @OA\Property(
     *                 property="availability_schedule",
     *                 type="object",
     *                 example={
     *                     "monday": {"start": "09:00", "end": "17:00"},
     *                     "tuesday": {"start": "09:00", "end": "17:00"}
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Doctor updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Doctor updated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="doctor", ref="#/components/schemas/Doctor")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="No access to this doctor",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
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
    public function update(Request $request, Doctor $doctor): JsonResponse
    {
        try {
            if (!$this->hasClinicAccess($doctor->clinic_id)) {
                return $this->forbiddenResponse('No access to this doctor');
            }

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255',
                'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $doctor->user_id,
                'phone' => 'nullable|string|max:20',
                'specialty' => 'sometimes|required|string|max:255',
                'license_no' => 'sometimes|required|string|max:255|unique:doctors,license_no,' . $doctor->id,
                'signature_url' => 'nullable|url|max:500',
                'consultation_fee' => 'nullable|numeric|min:0',
                'availability_schedule' => 'nullable|array',
                'is_active' => 'sometimes|boolean',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $data = $validator->validated();

            // Update user data
            if (isset($data['name']) || isset($data['email']) || isset($data['phone'])) {
                $userData = array_intersect_key($data, array_flip(['name', 'email', 'phone']));
                $doctor->user->update($userData);
            }

            // Update doctor data
            $doctorData = array_intersect_key($data, array_flip([
                'specialty', 'license_no', 'signature_url', 'consultation_fee',
                'availability_schedule', 'is_active'
            ]));
            $doctor->update($doctorData);

            $doctor->load(['user', 'clinic']);

            return $this->successResponse([
                'doctor' => $doctor,
            ], 'Doctor updated successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/doctors/{doctor}",
     *     summary="Delete doctor",
     *     description="Delete a doctor record (only if no medical records exist)",
     *     tags={"Doctors"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="doctor",
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
     *             @OA\Property(property="message", type="string", example="Doctor deleted successfully"),
     *             @OA\Property(property="data", type="object", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="No access to this doctor",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Doctor not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Cannot delete doctor with existing medical records",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function destroy(Doctor $doctor): JsonResponse
    {
        try {
            if (!$this->hasClinicAccess($doctor->clinic_id)) {
                return $this->forbiddenResponse('No access to this doctor');
            }

            // Check if doctor has any appointments or encounters
            $hasRecords = $doctor->appointments()->exists() ||
                         $doctor->encounters()->exists() ||
                         $doctor->prescriptions()->exists();

            if ($hasRecords) {
                return $this->errorResponse('Cannot delete doctor with existing medical records', null, 422);
            }

            $doctor->delete();

            return $this->successResponse(null, 'Doctor deleted successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/doctors/{doctor}/appointments",
     *     summary="Get doctor appointments",
     *     description="Retrieve all appointments for a specific doctor",
     *     tags={"Doctors"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="doctor",
     *         in="path",
     *         description="Doctor ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page",
     *         @OA\Schema(type="integer", example=15)
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by appointment status",
     *         @OA\Schema(type="string", enum={"scheduled","confirmed","in_progress","completed","cancelled","no_show","rescheduled","waiting","checked_in","checked_out"})
     *     ),
     *     @OA\Parameter(
     *         name="date_from",
     *         in="query",
     *         description="Filter from date",
     *         @OA\Schema(type="string", format="date", example="2024-01-01")
     *     ),
     *     @OA\Parameter(
     *         name="date_to",
     *         in="query",
     *         description="Filter to date",
     *         @OA\Schema(type="string", format="date", example="2024-12-31")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Doctor appointments retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Doctor appointments retrieved"),
     *             @OA\Property(
     *                 property="data",
     *                 allOf={
     *                     @OA\Schema(ref="#/components/schemas/Pagination"),
     *                     @OA\Schema(
     *                         @OA\Property(
     *                             property="data",
     *                             type="array",
     *                             @OA\Items(ref="#/components/schemas/Appointment")
     *                         )
     *                     )
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="No access to this doctor",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Doctor not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function appointments(Request $request, Doctor $doctor): JsonResponse
    {
        try {
            if (!$this->hasClinicAccess($doctor->clinic_id)) {
                return $this->forbiddenResponse('No access to this doctor');
            }

            [$perPage, $page] = $this->getPaginationParams($request);

            $appointments = $doctor->appointments()
                ->with(['patient', 'clinic', 'room'])
                ->latest()
                ->paginate($perPage, ['*'], 'page', $page);

            return $this->paginatedResponse($appointments, 'Doctor appointments retrieved');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/doctors/{doctor}/encounters",
     *     summary="Get doctor encounters",
     *     description="Retrieve all medical encounters for a specific doctor",
     *     tags={"Doctors"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="doctor",
     *         in="path",
     *         description="Doctor ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page",
     *         @OA\Schema(type="integer", example=15)
     *     ),
     *     @OA\Parameter(
     *         name="patient_id",
     *         in="query",
     *         description="Filter by patient",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="encounter_type",
     *         in="query",
     *         description="Filter by encounter type",
     *         @OA\Schema(type="string", enum={"consultation","follow_up","emergency","procedure","telemedicine"}, example="consultation")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by encounter status",
     *         @OA\Schema(type="string", enum={"in_progress","completed","cancelled","no_show"}, example="completed")
     *     ),
     *     @OA\Parameter(
     *         name="date_from",
     *         in="query",
     *         description="Filter from date",
     *         @OA\Schema(type="string", format="date", example="2024-01-01")
     *     ),
     *     @OA\Parameter(
     *         name="date_to",
     *         in="query",
     *         description="Filter to date",
     *         @OA\Schema(type="string", format="date", example="2024-01-31")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Doctor encounters retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Doctor encounters retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 allOf={
     *                     @OA\Schema(ref="#/components/schemas/Pagination"),
     *                     @OA\Schema(
     *                         @OA\Property(
     *                             property="data",
     *                             type="array",
     *                             @OA\Items(
     *                                 @OA\Property(property="id", type="integer", example=1),
     *                                 @OA\Property(property="patient_id", type="integer", example=1),
     *                                 @OA\Property(property="doctor_id", type="integer", example=1),
     *                                 @OA\Property(property="appointment_id", type="integer", example=1),
     *                                 @OA\Property(property="encounter_type", type="string", example="consultation"),
     *                                 @OA\Property(property="chief_complaint", type="string", example="Chest pain and shortness of breath"),
     *                                 @OA\Property(property="encounter_date", type="string", format="date-time", example="2024-01-15T10:00:00Z"),
     *                                 @OA\Property(property="status", type="string", example="completed"),
     *                                 @OA\Property(property="diagnosis", type="array", @OA\Items(type="string")),
     *                                 @OA\Property(property="treatment_plan", type="string", example="Rest, monitor symptoms, follow up in 24 hours"),
     *                                 @OA\Property(property="patient", type="object", nullable=true),
     *                                 @OA\Property(property="appointment", type="object", nullable=true),
     *                                 @OA\Property(property="prescriptions_count", type="integer", example=2),
     *                                 @OA\Property(property="lab_results_count", type="integer", example=3),
     *                                 @OA\Property(property="file_assets_count", type="integer", example=1),
     *                                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                                 @OA\Property(property="updated_at", type="string", format="date-time")
     *                             )
     *                         )
     *                     )
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="No access to this doctor",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Doctor not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function encounters(Request $request, Doctor $doctor): JsonResponse
    {
        try {
            if (!$this->hasClinicAccess($doctor->clinic_id)) {
                return $this->forbiddenResponse('No access to this doctor');
            }

            [$perPage, $page] = $this->getPaginationParams($request);

            $encounters = $doctor->encounters()
                ->with(['patient', 'clinic'])
                ->latest()
                ->paginate($perPage, ['*'], 'page', $page);

            return $this->paginatedResponse($encounters, 'Doctor encounters retrieved');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/doctors/{doctor}/patients",
     *     summary="Get doctor patients",
     *     description="Retrieve all patients associated with a specific doctor",
     *     tags={"Doctors"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="doctor",
     *         in="path",
     *         description="Doctor ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page",
     *         @OA\Schema(type="integer", example=15)
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search by patient name or ID",
     *         @OA\Schema(type="string", example="John Doe")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by patient status",
     *         @OA\Schema(type="string", enum={"active","inactive","deceased"}, example="active")
     *     ),
     *     @OA\Parameter(
     *         name="last_visit_from",
     *         in="query",
     *         description="Filter by last visit date from",
     *         @OA\Schema(type="string", format="date", example="2024-01-01")
     *     ),
     *     @OA\Parameter(
     *         name="last_visit_to",
     *         in="query",
     *         description="Filter by last visit date to",
     *         @OA\Schema(type="string", format="date", example="2024-01-31")
     *     ),
     *     @OA\Parameter(
     *         name="sort_by",
     *         in="query",
     *         description="Sort patients by",
     *         @OA\Schema(type="string", enum={"name","last_visit","appointments_count","created_at"}, example="last_visit")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Doctor patients retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Doctor patients retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 allOf={
     *                     @OA\Schema(ref="#/components/schemas/Pagination"),
     *                     @OA\Schema(
     *                         @OA\Property(
     *                             property="data",
     *                             type="array",
     *                             @OA\Items(
     *                                 @OA\Property(property="id", type="integer", example=1),
     *                                 @OA\Property(property="first_name", type="string", example="John"),
     *                                 @OA\Property(property="last_name", type="string", example="Doe"),
     *                                 @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *                                 @OA\Property(property="phone", type="string", example="+1234567890"),
     *                                 @OA\Property(property="date_of_birth", type="string", format="date", example="1985-06-15"),
     *                                 @OA\Property(property="gender", type="string", example="male"),
     *                                 @OA\Property(property="status", type="string", example="active"),
     *                                 @OA\Property(property="emergency_contact", type="object",
     *                                     @OA\Property(property="name", type="string", example="Jane Doe"),
     *                                     @OA\Property(property="phone", type="string", example="+1234567891"),
     *                                     @OA\Property(property="relationship", type="string", example="spouse")
     *                                 ),
     *                                 @OA\Property(property="medical_history", type="array", @OA\Items(type="string")),
     *                                 @OA\Property(property="allergies", type="array", @OA\Items(type="string")),
     *                                 @OA\Property(property="current_medications", type="array", @OA\Items(type="string")),
     *                                 @OA\Property(property="last_appointment", type="string", format="date", example="2024-01-10"),
     *                                 @OA\Property(property="next_appointment", type="string", format="date", example="2024-02-10"),
     *                                 @OA\Property(property="total_appointments", type="integer", example=15),
     *                                 @OA\Property(property="total_encounters", type="integer", example=12),
     *                                 @OA\Property(property="total_prescriptions", type="integer", example=8),
     *                                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                                 @OA\Property(property="updated_at", type="string", format="date-time")
     *                             )
     *                         )
     *                     )
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="No access to this doctor",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Doctor not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function patients(Request $request, Doctor $doctor): JsonResponse
    {
        try {
            if (!$this->hasClinicAccess($doctor->clinic_id)) {
                return $this->forbiddenResponse('No access to this doctor');
            }

            [$perPage, $page] = $this->getPaginationParams($request);

            $patients = $doctor->encounters()
                ->with(['patient'])
                ->get()
                ->pluck('patient')
                ->unique('id')
                ->values();

            // Manual pagination since we're using unique
            $total = $patients->count();
            $offset = ($page - 1) * $perPage;
            $items = $patients->slice($offset, $perPage)->values();

            $paginatedData = new \Illuminate\Pagination\LengthAwarePaginator(
                $items,
                $total,
                $perPage,
                $page,
                ['path' => $request->url(), 'pageName' => 'page']
            );

            return $this->paginatedResponse($paginatedData, 'Doctor patients retrieved');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/doctors/{doctor}/availability",
     *     summary="Get doctor availability",
     *     description="Retrieve doctor's availability schedule and working hours",
     *     tags={"Doctors"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="doctor",
     *         in="path",
     *         description="Doctor ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="date_from",
     *         in="query",
     *         description="Start date for availability check",
     *         @OA\Schema(type="string", format="date", example="2024-01-15")
     *     ),
     *     @OA\Parameter(
     *         name="date_to",
     *         in="query",
     *         description="End date for availability check",
     *         @OA\Schema(type="string", format="date", example="2024-01-22")
     *     ),
     *     @OA\Parameter(
     *         name="include_breaks",
     *         in="query",
     *         description="Include break times in response",
     *         @OA\Schema(type="boolean", example=true)
     *     ),
     *     @OA\Parameter(
     *         name="include_appointments",
     *         in="query",
     *         description="Include existing appointments in response",
     *         @OA\Schema(type="boolean", example=false)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Doctor availability retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Doctor availability retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="doctor", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Dr. John Smith"),
     *                     @OA\Property(property="specialization", type="string", example="Cardiology")
     *                 ),
     *                 @OA\Property(property="date_range", type="object",
     *                     @OA\Property(property="from", type="string", format="date", example="2024-01-15"),
     *                     @OA\Property(property="to", type="string", format="date", example="2024-01-22")
     *                 ),
     *                 @OA\Property(property="working_hours", type="object",
     *                     @OA\Property(property="monday", type="object",
     *                         @OA\Property(property="start", type="string", example="09:00"),
     *                         @OA\Property(property="end", type="string", example="17:00"),
     *                         @OA\Property(property="is_available", type="boolean", example=true)
     *                     ),
     *                     @OA\Property(property="tuesday", type="object",
     *                         @OA\Property(property="start", type="string", example="09:00"),
     *                         @OA\Property(property="end", type="string", example="17:00"),
     *                         @OA\Property(property="is_available", type="boolean", example=true)
     *                     ),
     *                     @OA\Property(property="wednesday", type="object",
     *                         @OA\Property(property="start", type="string", example="09:00"),
     *                         @OA\Property(property="end", type="string", example="17:00"),
     *                         @OA\Property(property="is_available", type="boolean", example=true)
     *                     ),
     *                     @OA\Property(property="thursday", type="object",
     *                         @OA\Property(property="start", type="string", example="09:00"),
     *                         @OA\Property(property="end", type="string", example="17:00"),
     *                         @OA\Property(property="is_available", type="boolean", example=true)
     *                     ),
     *                     @OA\Property(property="friday", type="object",
     *                         @OA\Property(property="start", type="string", example="09:00"),
     *                         @OA\Property(property="end", type="string", example="17:00"),
     *                         @OA\Property(property="is_available", type="boolean", example=true)
     *                     ),
     *                     @OA\Property(property="saturday", type="object",
     *                         @OA\Property(property="start", type="string", example="10:00"),
     *                         @OA\Property(property="end", type="string", example="14:00"),
     *                         @OA\Property(property="is_available", type="boolean", example=true)
     *                     ),
     *                     @OA\Property(property="sunday", type="object",
     *                         @OA\Property(property="start", type="string", example="00:00"),
     *                         @OA\Property(property="end", type="string", example="00:00"),
     *                         @OA\Property(property="is_available", type="boolean", example=false)
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="break_times",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="day", type="string", example="monday"),
     *                         @OA\Property(property="start", type="string", example="12:00"),
     *                         @OA\Property(property="end", type="string", example="13:00"),
     *                         @OA\Property(property="description", type="string", example="Lunch break")
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="unavailable_dates",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="date", type="string", format="date", example="2024-01-20"),
     *                         @OA\Property(property="reason", type="string", example="Conference"),
     *                         @OA\Property(property="is_all_day", type="boolean", example=true)
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="available_slots",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="date", type="string", format="date", example="2024-01-15"),
     *                         @OA\Property(property="time", type="string", example="09:00"),
     *                         @OA\Property(property="datetime", type="string", format="date-time", example="2024-01-15T09:00:00Z"),
     *                         @OA\Property(property="is_available", type="boolean", example=true),
     *                         @OA\Property(property="slot_type", type="string", example="regular")
     *                     )
     *                 ),
     *                 @OA\Property(property="total_available_hours", type="number", format="float", example=40.0),
     *                 @OA\Property(property="total_break_hours", type="number", format="float", example=5.0),
     *                 @OA\Property(property="next_available_date", type="string", format="date", example="2024-01-15")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="No access to this doctor",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Doctor not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function availability(Request $request, Doctor $doctor): JsonResponse
    {
        try {
            if (!$this->hasClinicAccess($doctor->clinic_id)) {
                return $this->forbiddenResponse('No access to this doctor');
            }

            $validator = Validator::make($request->all(), [
                'date' => 'nullable|date|after_or_equal:today',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $date = $request->get('date', now()->format('Y-m-d'));
            $availability = $doctor->getAvailabilityForDate($date);

            return $this->successResponse([
                'availability' => $availability,
                'date' => $date,
                'schedule' => $doctor->availability_schedule,
            ], 'Doctor availability retrieved');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/doctors/{doctor}/availability",
     *     summary="Update doctor availability",
     *     description="Update doctor's availability schedule and working hours",
     *     tags={"Doctors"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="doctor",
     *         in="path",
     *         description="Doctor ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="working_hours", type="object",
     *                 @OA\Property(property="monday", type="object",
     *                     @OA\Property(property="start", type="string", example="09:00"),
     *                     @OA\Property(property="end", type="string", example="17:00"),
     *                     @OA\Property(property="is_available", type="boolean", example=true)
     *                 ),
     *                 @OA\Property(property="tuesday", type="object",
     *                     @OA\Property(property="start", type="string", example="09:00"),
     *                     @OA\Property(property="end", type="string", example="17:00"),
     *                     @OA\Property(property="is_available", type="boolean", example=true)
     *                 ),
     *                 @OA\Property(property="wednesday", type="object",
     *                     @OA\Property(property="start", type="string", example="09:00"),
     *                     @OA\Property(property="end", type="string", example="17:00"),
     *                     @OA\Property(property="is_available", type="boolean", example=true)
     *                 ),
     *                 @OA\Property(property="thursday", type="object",
     *                     @OA\Property(property="start", type="string", example="09:00"),
     *                     @OA\Property(property="end", type="string", example="17:00"),
     *                     @OA\Property(property="is_available", type="boolean", example=true)
     *                 ),
     *                 @OA\Property(property="friday", type="object",
     *                     @OA\Property(property="start", type="string", example="09:00"),
     *                     @OA\Property(property="end", type="string", example="17:00"),
     *                     @OA\Property(property="is_available", type="boolean", example=true)
     *                 ),
     *                 @OA\Property(property="saturday", type="object",
     *                     @OA\Property(property="start", type="string", example="10:00"),
     *                     @OA\Property(property="end", type="string", example="14:00"),
     *                     @OA\Property(property="is_available", type="boolean", example=true)
     *                 ),
     *                 @OA\Property(property="sunday", type="object",
     *                     @OA\Property(property="start", type="string", example="00:00"),
     *                     @OA\Property(property="end", type="string", example="00:00"),
     *                     @OA\Property(property="is_available", type="boolean", example=false)
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="break_times",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="day", type="string", example="monday"),
     *                     @OA\Property(property="start", type="string", example="12:00"),
     *                     @OA\Property(property="end", type="string", example="13:00"),
     *                     @OA\Property(property="description", type="string", example="Lunch break")
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="unavailable_dates",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="date", type="string", format="date", example="2024-01-20"),
     *                     @OA\Property(property="reason", type="string", example="Conference"),
     *                     @OA\Property(property="is_all_day", type="boolean", example=true)
     *                 )
     *             ),
     *             @OA\Property(property="appointment_duration", type="integer", example=30, description="Default appointment duration in minutes"),
     *             @OA\Property(property="buffer_time", type="integer", example=5, description="Buffer time between appointments in minutes"),
     *             @OA\Property(property="max_appointments_per_day", type="integer", example=20, description="Maximum appointments per day"),
     *             @OA\Property(property="notes", type="string", example="Updated availability for conference week", description="Notes about the availability update")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Doctor availability updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Doctor availability updated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="doctor", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Dr. John Smith"),
     *                     @OA\Property(property="specialization", type="string", example="Cardiology")
     *                 ),
     *                 @OA\Property(property="working_hours", type="object"),
     *                 @OA\Property(property="break_times", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="unavailable_dates", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="appointment_duration", type="integer", example=30),
     *                 @OA\Property(property="buffer_time", type="integer", example=5),
     *                 @OA\Property(property="max_appointments_per_day", type="integer", example=20),
     *                 @OA\Property(property="total_available_hours", type="number", format="float", example=40.0),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="No access to this doctor",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
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
    public function updateAvailability(Request $request, Doctor $doctor): JsonResponse
    {
        try {
            if (!$this->hasClinicAccess($doctor->clinic_id)) {
                return $this->forbiddenResponse('No access to this doctor');
            }

            $validator = Validator::make($request->all(), [
                'availability_schedule' => 'required|array',
                'availability_schedule.monday' => 'nullable|array',
                'availability_schedule.tuesday' => 'nullable|array',
                'availability_schedule.wednesday' => 'nullable|array',
                'availability_schedule.thursday' => 'nullable|array',
                'availability_schedule.friday' => 'nullable|array',
                'availability_schedule.saturday' => 'nullable|array',
                'availability_schedule.sunday' => 'nullable|array',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $doctor->update([
                'availability_schedule' => $request->availability_schedule,
            ]);

            return $this->successResponse([
                'doctor' => $doctor,
            ], 'Doctor availability updated successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/doctors/{doctor}/statistics",
     *     summary="Get doctor statistics",
     *     description="Retrieve comprehensive statistics and analytics for a specific doctor",
     *     tags={"Doctors"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="doctor",
     *         in="path",
     *         description="Doctor ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="period",
     *         in="query",
     *         description="Statistics period",
     *         @OA\Schema(type="string", enum={"today","week","month","quarter","year","all"}, example="month")
     *     ),
     *     @OA\Parameter(
     *         name="date_from",
     *         in="query",
     *         description="Start date for statistics",
     *         @OA\Schema(type="string", format="date", example="2024-01-01")
     *     ),
     *     @OA\Parameter(
     *         name="date_to",
     *         in="query",
     *         description="End date for statistics",
     *         @OA\Schema(type="string", format="date", example="2024-01-31")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Doctor statistics retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Doctor statistics retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="doctor", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Dr. John Smith"),
     *                     @OA\Property(property="specialization", type="string", example="Cardiology")
     *                 ),
     *                 @OA\Property(property="period", type="object",
     *                     @OA\Property(property="type", type="string", example="month"),
     *                     @OA\Property(property="from", type="string", format="date", example="2024-01-01"),
     *                     @OA\Property(property="to", type="string", format="date", example="2024-01-31")
     *                 ),
     *                 @OA\Property(property="appointments", type="object",
     *                     @OA\Property(property="total", type="integer", example=85),
     *                     @OA\Property(property="completed", type="integer", example=78),
     *                     @OA\Property(property="cancelled", type="integer", example=5),
     *                     @OA\Property(property="no_show", type="integer", example=2),
     *                     @OA\Property(property="completion_rate", type="number", format="float", example=91.8),
     *                     @OA\Property(property="average_duration", type="number", format="float", example=32.5),
     *                     @OA\Property(property="total_revenue", type="number", format="float", example=12750.00)
     *                 ),
     *                 @OA\Property(property="patients", type="object",
     *                     @OA\Property(property="total", type="integer", example=45),
     *                     @OA\Property(property="new_patients", type="integer", example=8),
     *                     @OA\Property(property="returning_patients", type="integer", example=37),
     *                     @OA\Property(property="active_patients", type="integer", example=42),
     *                     @OA\Property(property="patient_satisfaction", type="number", format="float", example=4.7)
     *                 ),
     *                 @OA\Property(property="encounters", type="object",
     *                     @OA\Property(property="total", type="integer", example=78),
     *                     @OA\Property(property="consultations", type="integer", example=45),
     *                     @OA\Property(property="follow_ups", type="integer", example=25),
     *                     @OA\Property(property="emergencies", type="integer", example=3),
     *                     @OA\Property(property="procedures", type="integer", example=5),
     *                     @OA\Property(property="average_encounter_duration", type="number", format="float", example=28.5)
     *                 ),
     *                 @OA\Property(property="prescriptions", type="object",
     *                     @OA\Property(property="total", type="integer", example=156),
     *                     @OA\Property(property="verified", type="integer", example=148),
     *                     @OA\Property(property="dispensed", type="integer", example=142),
     *                     @OA\Property(property="refills", type="integer", example=23),
     *                     @OA\Property(property="most_prescribed_medication", type="string", example="Lisinopril")
     *                 ),
     *                 @OA\Property(property="lab_results", type="object",
     *                     @OA\Property(property="total_ordered", type="integer", example=89),
     *                     @OA\Property(property="completed", type="integer", example=85),
     *                     @OA\Property(property="abnormal", type="integer", example=12),
     *                     @OA\Property(property="critical", type="integer", example=2),
     *                     @OA\Property(property="pending", type="integer", example=4)
     *                 ),
     *                 @OA\Property(property="productivity", type="object",
     *                     @OA\Property(property="appointments_per_day", type="number", format="float", example=3.2),
     *                     @OA\Property(property="patients_per_day", type="number", format="float", example=2.8),
     *                     @OA\Property(property="working_hours", type="number", format="float", example=160.0),
     *                     @OA\Property(property="utilization_rate", type="number", format="float", example=87.5),
     *                     @OA\Property(property="revenue_per_hour", type="number", format="float", example=79.69)
     *                 ),
     *                 @OA\Property(property="trends", type="object",
     *                     @OA\Property(property="appointments_trend", type="string", example="increasing"),
     *                     @OA\Property(property="revenue_trend", type="string", example="stable"),
     *                     @OA\Property(property="patient_satisfaction_trend", type="string", example="improving"),
     *                     @OA\Property(property="busiest_day", type="string", example="Tuesday"),
     *                     @OA\Property(property="busiest_time", type="string", example="10:00-12:00")
     *                 ),
     *                 @OA\Property(property="comparison", type="object",
     *                     @OA\Property(property="previous_period", type="object",
     *                         @OA\Property(property="appointments", type="integer", example=78),
     *                         @OA\Property(property="revenue", type="number", format="float", example=11700.00),
     *                         @OA\Property(property="patients", type="integer", example=42)
     *                     ),
     *                     @OA\Property(property="growth", type="object",
     *                         @OA\Property(property="appointments_growth", type="number", format="float", example=8.97),
     *                         @OA\Property(property="revenue_growth", type="number", format="float", example=8.97),
     *                         @OA\Property(property="patients_growth", type="number", format="float", example=7.14)
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="No access to this doctor",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Doctor not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function statistics(Doctor $doctor): JsonResponse
    {
        try {
            if (!$this->hasClinicAccess($doctor->clinic_id)) {
                return $this->forbiddenResponse('No access to this doctor');
            }

            $statistics = $doctor->statistics;

            return $this->successResponse([
                'statistics' => $statistics,
            ], 'Doctor statistics retrieved');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/doctors/search",
     *     summary="Search doctors",
     *     description="Search doctors by name, specialty, or license number",
     *     tags={"Doctors"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="query",
     *         in="query",
     *         description="Search query",
     *         required=true,
     *         @OA\Schema(type="string", example="Dr. Smith")
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Maximum number of results",
     *         @OA\Schema(type="integer", example=20)
     *     ),
     *     @OA\Parameter(
     *         name="specialty",
     *         in="query",
     *         description="Filter by specialty",
     *         @OA\Schema(type="string", example="Cardiology")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Search results retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Search results retrieved"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="doctors",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/Doctor")
     *                 ),
     *                 @OA\Property(property="total", type="integer", example=3),
     *                 @OA\Property(property="query", type="string", example="Dr. Smith")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="No clinic access",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $currentClinic = $this->getCurrentClinic();

            if (!$currentClinic) {
                return $this->errorResponse('No clinic access', null, 403);
            }

            $validator = Validator::make($request->all(), [
                'query' => 'required|string|min:2',
                'limit' => 'nullable|integer|min:1|max:50',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $query = $request->get('query');
            $limit = $request->get('limit', 20);

            $doctors = Doctor::where('clinic_id', $currentClinic->id)
                ->whereHas('user', function ($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%")
                      ->orWhere('email', 'like', "%{$query}%");
                })
                ->orWhere('specialty', 'like', "%{$query}%")
                ->orWhere('license_no', 'like', "%{$query}%")
                ->with(['user'])
                ->limit($limit)
                ->get();

            return $this->successResponse([
                'doctors' => $doctors,
            ], 'Search results retrieved');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/doctors/{doctor}/reports",
     *     summary="Get doctor reports",
     *     description="Generate comprehensive reports for a specific doctor",
     *     tags={"Doctors"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="doctor",
     *         in="path",
     *         description="Doctor ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="report_type",
     *         in="query",
     *         description="Type of report to generate",
     *         @OA\Schema(type="string", enum={"summary","detailed","financial","productivity","patient_analysis","appointment_analysis"}, example="summary")
     *     ),
     *     @OA\Parameter(
     *         name="period",
     *         in="query",
     *         description="Report period",
     *         @OA\Schema(type="string", enum={"today","week","month","quarter","year","custom"}, example="month")
     *     ),
     *     @OA\Parameter(
     *         name="date_from",
     *         in="query",
     *         description="Start date for custom period",
     *         @OA\Schema(type="string", format="date", example="2024-01-01")
     *     ),
     *     @OA\Parameter(
     *         name="date_to",
     *         in="query",
     *         description="End date for custom period",
     *         @OA\Schema(type="string", format="date", example="2024-01-31")
     *     ),
     *     @OA\Parameter(
     *         name="format",
     *         in="query",
     *         description="Report format",
     *         @OA\Schema(type="string", enum={"json","pdf","excel","csv"}, example="json")
     *     ),
     *     @OA\Parameter(
     *         name="include_charts",
     *         in="query",
     *         description="Include chart data in report",
     *         @OA\Schema(type="boolean", example=true)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Doctor report generated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Doctor report generated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="report", type="object",
     *                     @OA\Property(property="id", type="string", example="RPT-2024-001"),
     *                     @OA\Property(property="type", type="string", example="summary"),
     *                     @OA\Property(property="period", type="object",
     *                         @OA\Property(property="type", type="string", example="month"),
     *                         @OA\Property(property="from", type="string", format="date", example="2024-01-01"),
     *                         @OA\Property(property="to", type="string", format="date", example="2024-01-31")
     *                     ),
     *                     @OA\Property(property="generated_at", type="string", format="date-time", example="2024-01-31T23:59:59Z"),
     *                     @OA\Property(property="doctor", type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="Dr. John Smith"),
     *                         @OA\Property(property="specialization", type="string", example="Cardiology")
     *                     ),
     *                     @OA\Property(property="summary", type="object",
     *                         @OA\Property(property="total_appointments", type="integer", example=85),
     *                         @OA\Property(property="total_patients", type="integer", example=45),
     *                         @OA\Property(property="total_revenue", type="number", format="float", example=12750.00),
     *                         @OA\Property(property="completion_rate", type="number", format="float", example=91.8),
     *                         @OA\Property(property="patient_satisfaction", type="number", format="float", example=4.7)
     *                     ),
     *                     @OA\Property(property="detailed_metrics", type="object",
     *                         @OA\Property(property="appointments", type="object"),
     *                         @OA\Property(property="patients", type="object"),
     *                         @OA\Property(property="encounters", type="object"),
     *                         @OA\Property(property="prescriptions", type="object"),
     *                         @OA\Property(property="lab_results", type="object"),
     *                         @OA\Property(property="productivity", type="object"),
     *                         @OA\Property(property="financial", type="object")
     *                     ),
     *                     @OA\Property(property="trends", type="object",
     *                         @OA\Property(property="appointments_trend", type="string", example="increasing"),
     *                         @OA\Property(property="revenue_trend", type="string", example="stable"),
     *                         @OA\Property(property="patient_satisfaction_trend", type="string", example="improving")
     *                     ),
     *                     @OA\Property(property="recommendations", type="array", @OA\Items(type="string"), example={"Consider extending working hours on Tuesdays","Patient satisfaction is high, maintain current practices"}),
     *                     @OA\Property(property="charts", type="object",
     *                         @OA\Property(property="appointments_by_day", type="array", @OA\Items(type="object")),
     *                         @OA\Property(property="revenue_trend", type="array", @OA\Items(type="object")),
     *                         @OA\Property(property="patient_satisfaction", type="array", @OA\Items(type="object"))
     *                     ),
     *                     @OA\Property(property="export_urls", type="object",
     *                         @OA\Property(property="pdf", type="string", example="/api/v1/doctors/1/reports/RPT-2024-001/download?format=pdf"),
     *                         @OA\Property(property="excel", type="string", example="/api/v1/doctors/1/reports/RPT-2024-001/download?format=excel"),
     *                         @OA\Property(property="csv", type="string", example="/api/v1/doctors/1/reports/RPT-2024-001/download?format=csv")
     *                     ),
     *                     @OA\Property(property="expires_at", type="string", format="date-time", example="2024-02-07T23:59:59Z")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="No access to this doctor",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Doctor not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function reports(Request $request, Doctor $doctor): JsonResponse
    {
        try {
            if (!$this->hasClinicAccess($doctor->clinic_id)) {
                return $this->forbiddenResponse('No access to this doctor');
            }

            $reports = [
                'total_appointments' => $doctor->appointments()->count(),
                'completed_appointments' => $doctor->appointments()->where('status', 'completed')->count(),
                'total_encounters' => $doctor->encounters()->count(),
                'total_prescriptions' => $doctor->prescriptions()->count(),
                'total_patients' => $doctor->encounters()->distinct('patient_id')->count(),
                'appointments_this_month' => $doctor->appointments()
                    ->where('start_at', '>=', now()->startOfMonth())
                    ->count(),
                'appointments_by_status' => $doctor->appointments()
                    ->selectRaw('status, COUNT(*) as count')
                    ->groupBy('status')
                    ->get(),
                'appointments_by_type' => $doctor->appointments()
                    ->selectRaw('appointment_type, COUNT(*) as count')
                    ->groupBy('appointment_type')
                    ->get(),
            ];

            return $this->successResponse([
                'reports' => $reports,
            ], 'Doctor reports retrieved');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
}
