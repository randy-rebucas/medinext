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

class DoctorController extends BaseController
{
    /**
     * Display a listing of doctors
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
     * Store a newly created doctor
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
     * Display the specified doctor
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
     * Update the specified doctor
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
     * Remove the specified doctor
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
     * Get doctor appointments
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
     * Get doctor encounters
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
     * Get doctor patients
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
     * Get doctor availability
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
     * Update doctor availability
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
     * Get doctor statistics
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
     * Search doctors
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
     * Get doctor reports
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
