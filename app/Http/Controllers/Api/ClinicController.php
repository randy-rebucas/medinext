<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\Clinic;
use App\Models\User;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ClinicController extends BaseController
{
    /**
     * Display a listing of clinics (public)
     */
    public function publicIndex(Request $request): JsonResponse
    {
        try {
            $clinics = Clinic::where('is_active', true)
                ->select(['id', 'name', 'slug', 'address', 'phone', 'email', 'website', 'description'])
                ->get();

            return $this->successResponse([
                'clinics' => $clinics,
            ], 'Public clinics retrieved successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Display the specified clinic (public)
     */
    public function publicShow(Clinic $clinic): JsonResponse
    {
        try {
            if (!$clinic->is_active) {
                return $this->notFoundResponse('Clinic not found');
            }

            $clinic->load(['doctors.user']);

            return $this->successResponse([
                'clinic' => $clinic,
            ], 'Clinic details retrieved successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Display a listing of clinics
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $this->getAuthenticatedUser();
            
            [$perPage, $page] = $this->getPaginationParams($request);
            [$sort, $direction] = $this->getSortingParams($request, [
                'id', 'name', 'created_at', 'updated_at'
            ]);

            $query = $user->clinics();

            // Search functionality
            if ($request->has('search')) {
                $search = $request->get('search');
                $query->where('name', 'like', "%{$search}%");
            }

            $clinics = $query->orderBy($sort, $direction)
                ->paginate($perPage, ['*'], 'page', $page);

            return $this->paginatedResponse($clinics, 'Clinics retrieved successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Store a newly created clinic
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'slug' => 'required|string|max:255|unique:clinics,slug',
                'timezone' => 'required|string|max:50',
                'logo_url' => 'nullable|url|max:500',
                'address' => 'nullable|array',
                'address.street' => 'nullable|string|max:255',
                'address.city' => 'nullable|string|max:100',
                'address.state' => 'nullable|string|max:100',
                'address.zip' => 'nullable|string|max:20',
                'address.country' => 'nullable|string|max:100',
                'phone' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255',
                'website' => 'nullable|url|max:255',
                'description' => 'nullable|string|max:1000',
                'settings' => 'nullable|array',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $clinic = Clinic::create($validator->validated());

            // Assign current user to the clinic with admin role
            $user = $this->getAuthenticatedUser();
            $clinic->users()->attach($user->id, [
                'role_id' => 1, // Assuming 1 is admin role
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return $this->successResponse([
                'clinic' => $clinic,
            ], 'Clinic created successfully', 201);

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Display the specified clinic
     */
    public function show(Clinic $clinic): JsonResponse
    {
        try {
            if (!$this->hasClinicAccess($clinic->id)) {
                return $this->forbiddenResponse('No access to this clinic');
            }

            $clinic->load(['users', 'doctors.user']);

            return $this->successResponse([
                'clinic' => $clinic,
            ], 'Clinic retrieved successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Update the specified clinic
     */
    public function update(Request $request, Clinic $clinic): JsonResponse
    {
        try {
            if (!$this->hasClinicAccess($clinic->id)) {
                return $this->forbiddenResponse('No access to this clinic');
            }

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255',
                'slug' => 'sometimes|required|string|max:255|unique:clinics,slug,' . $clinic->id,
                'timezone' => 'sometimes|required|string|max:50',
                'logo_url' => 'nullable|url|max:500',
                'address' => 'nullable|array',
                'address.street' => 'nullable|string|max:255',
                'address.city' => 'nullable|string|max:100',
                'address.state' => 'nullable|string|max:100',
                'address.zip' => 'nullable|string|max:20',
                'address.country' => 'nullable|string|max:100',
                'phone' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255',
                'website' => 'nullable|url|max:255',
                'description' => 'nullable|string|max:1000',
                'settings' => 'nullable|array',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $clinic->update($validator->validated());

            return $this->successResponse([
                'clinic' => $clinic,
            ], 'Clinic updated successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Remove the specified clinic
     */
    public function destroy(Clinic $clinic): JsonResponse
    {
        try {
            if (!$this->hasClinicAccess($clinic->id)) {
                return $this->forbiddenResponse('No access to this clinic');
            }

            // Check if clinic has any data
            $hasData = $clinic->patients()->exists() || 
                      $clinic->appointments()->exists() || 
                      $clinic->encounters()->exists();

            if ($hasData) {
                return $this->errorResponse('Cannot delete clinic with existing data', null, 422);
            }

            $clinic->delete();

            return $this->successResponse(null, 'Clinic deleted successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Get clinic users
     */
    public function users(Request $request, Clinic $clinic): JsonResponse
    {
        try {
            if (!$this->hasClinicAccess($clinic->id)) {
                return $this->forbiddenResponse('No access to this clinic');
            }

            [$perPage, $page] = $this->getPaginationParams($request);

            $users = $clinic->users()
                ->with(['roles'])
                ->paginate($perPage, ['*'], 'page', $page);

            return $this->paginatedResponse($users, 'Clinic users retrieved');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Get clinic doctors
     */
    public function doctors(Request $request, Clinic $clinic): JsonResponse
    {
        try {
            if (!$this->hasClinicAccess($clinic->id)) {
                return $this->forbiddenResponse('No access to this clinic');
            }

            [$perPage, $page] = $this->getPaginationParams($request);

            $doctors = $clinic->doctors()
                ->with(['user'])
                ->paginate($perPage, ['*'], 'page', $page);

            return $this->paginatedResponse($doctors, 'Clinic doctors retrieved');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Get clinic patients
     */
    public function patients(Request $request, Clinic $clinic): JsonResponse
    {
        try {
            if (!$this->hasClinicAccess($clinic->id)) {
                return $this->forbiddenResponse('No access to this clinic');
            }

            [$perPage, $page] = $this->getPaginationParams($request);

            $patients = $clinic->patients()
                ->paginate($perPage, ['*'], 'page', $page);

            return $this->paginatedResponse($patients, 'Clinic patients retrieved');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Get clinic appointments
     */
    public function appointments(Request $request, Clinic $clinic): JsonResponse
    {
        try {
            if (!$this->hasClinicAccess($clinic->id)) {
                return $this->forbiddenResponse('No access to this clinic');
            }

            [$perPage, $page] = $this->getPaginationParams($request);

            $appointments = $clinic->appointments()
                ->with(['patient', 'doctor.user', 'room'])
                ->latest()
                ->paginate($perPage, ['*'], 'page', $page);

            return $this->paginatedResponse($appointments, 'Clinic appointments retrieved');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Get clinic statistics
     */
    public function statistics(Clinic $clinic): JsonResponse
    {
        try {
            if (!$this->hasClinicAccess($clinic->id)) {
                return $this->forbiddenResponse('No access to this clinic');
            }

            $statistics = [
                'total_users' => $clinic->users()->count(),
                'total_doctors' => $clinic->doctors()->count(),
                'total_patients' => $clinic->patients()->count(),
                'total_appointments' => $clinic->appointments()->count(),
                'total_encounters' => $clinic->encounters()->count(),
                'total_prescriptions' => $clinic->prescriptions()->count(),
                'total_lab_results' => $clinic->labResults()->count(),
                
                'appointments_today' => $clinic->appointments()
                    ->whereDate('start_at', now()->toDateString())
                    ->count(),
                'appointments_this_week' => $clinic->appointments()
                    ->whereBetween('start_at', [now()->startOfWeek(), now()->endOfWeek()])
                    ->count(),
                'appointments_this_month' => $clinic->appointments()
                    ->whereBetween('start_at', [now()->startOfMonth(), now()->endOfMonth()])
                    ->count(),
                
                'new_patients_this_month' => $clinic->patients()
                    ->where('created_at', '>=', now()->startOfMonth())
                    ->count(),
                
                'appointments_by_status' => $clinic->appointments()
                    ->selectRaw('status, COUNT(*) as count')
                    ->groupBy('status')
                    ->get(),
                
                'appointments_by_type' => $clinic->appointments()
                    ->selectRaw('appointment_type, COUNT(*) as count')
                    ->groupBy('appointment_type')
                    ->get(),
            ];

            return $this->successResponse([
                'statistics' => $statistics,
            ], 'Clinic statistics retrieved');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
}
