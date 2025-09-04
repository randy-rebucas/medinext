<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\Medrep;
use App\Models\MedrepVisit;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class MedrepController extends BaseController
{
    /**
     * Display a listing of medreps
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
                'id', 'name', 'company', 'email', 'phone', 'created_at'
            ]);

            $query = Medrep::where('clinic_id', $currentClinic->id)
                ->with(['clinic']);

            // Search functionality
            if ($request->has('search')) {
                $search = $request->get('search');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('company', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            // Filter by company
            if ($request->has('company')) {
                $query->where('company', $request->get('company'));
            }

            // Filter by specialty
            if ($request->has('specialty')) {
                $query->where('specialty', $request->get('specialty'));
            }

            $medreps = $query->orderBy($sort, $direction)
                ->paginate($perPage, ['*'], 'page', $page);

            return $this->paginatedResponse($medreps, 'Medreps retrieved successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Store a newly created medrep
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
                'company' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'phone' => 'nullable|string|max:20',
                'specialty' => 'nullable|string|max:255',
                'territory' => 'nullable|string|max:255',
                'notes' => 'nullable|string|max:1000',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $data = $validator->validated();
            $data['clinic_id'] = $currentClinic->id;

            $medrep = Medrep::create($data);
            $medrep->load(['clinic']);

            return $this->successResponse([
                'medrep' => $medrep,
            ], 'Medrep created successfully', 201);

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Display the specified medrep
     */
    public function show(Medrep $medrep): JsonResponse
    {
        try {
            if (!$this->hasClinicAccess($medrep->clinic_id)) {
                return $this->forbiddenResponse('No access to this medrep');
            }

            $medrep->load(['clinic', 'visits.doctor.user']);

            return $this->successResponse([
                'medrep' => $medrep,
            ], 'Medrep retrieved successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Update the specified medrep
     */
    public function update(Request $request, Medrep $medrep): JsonResponse
    {
        try {
            if (!$this->hasClinicAccess($medrep->clinic_id)) {
                return $this->forbiddenResponse('No access to this medrep');
            }

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255',
                'company' => 'sometimes|required|string|max:255',
                'email' => 'sometimes|required|email|max:255',
                'phone' => 'nullable|string|max:20',
                'specialty' => 'nullable|string|max:255',
                'territory' => 'nullable|string|max:255',
                'notes' => 'nullable|string|max:1000',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $medrep->update($validator->validated());
            $medrep->load(['clinic']);

            return $this->successResponse([
                'medrep' => $medrep,
            ], 'Medrep updated successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Remove the specified medrep
     */
    public function destroy(Medrep $medrep): JsonResponse
    {
        try {
            if (!$this->hasClinicAccess($medrep->clinic_id)) {
                return $this->forbiddenResponse('No access to this medrep');
            }

            // Check if medrep has any visits
            if ($medrep->visits()->exists()) {
                return $this->errorResponse('Cannot delete medrep with existing visits', null, 422);
            }

            $medrep->delete();

            return $this->successResponse(null, 'Medrep deleted successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Get medrep visits
     */
    public function visits(Request $request, Medrep $medrep): JsonResponse
    {
        try {
            if (!$this->hasClinicAccess($medrep->clinic_id)) {
                return $this->forbiddenResponse('No access to this medrep');
            }

            [$perPage, $page] = $this->getPaginationParams($request);

            $visits = $medrep->visits()
                ->with(['doctor.user', 'clinic'])
                ->latest()
                ->paginate($perPage, ['*'], 'page', $page);

            return $this->paginatedResponse($visits, 'Medrep visits retrieved');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Schedule medrep visit
     */
    public function scheduleVisit(Request $request, Medrep $medrep): JsonResponse
    {
        try {
            if (!$this->hasClinicAccess($medrep->clinic_id)) {
                return $this->forbiddenResponse('No access to this medrep');
            }

            $validator = Validator::make($request->all(), [
                'doctor_id' => 'required|integer|exists:doctors,id',
                'scheduled_at' => 'required|date|after:now',
                'duration' => 'nullable|integer|min:15|max:480',
                'purpose' => 'nullable|string|max:500',
                'notes' => 'nullable|string|max:1000',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $data = $validator->validated();
            $data['clinic_id'] = $medrep->clinic_id;
            $data['medrep_id'] = $medrep->id;
            $data['status'] = 'scheduled';

            // Check if doctor belongs to the same clinic
            $doctor = Doctor::findOrFail($data['doctor_id']);
            if ($doctor->clinic_id !== $medrep->clinic_id) {
                return $this->errorResponse('Doctor does not belong to this clinic', null, 422);
            }

            $visit = MedrepVisit::create($data);
            $visit->load(['doctor.user', 'clinic', 'medrep']);

            return $this->successResponse([
                'visit' => $visit,
            ], 'Medrep visit scheduled successfully', 201);

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Update medrep visit
     */
    public function updateVisit(Request $request, Medrep $medrep, MedrepVisit $visit): JsonResponse
    {
        try {
            if (!$this->hasClinicAccess($medrep->clinic_id)) {
                return $this->forbiddenResponse('No access to this medrep');
            }

            if ($visit->medrep_id !== $medrep->id) {
                return $this->errorResponse('Visit does not belong to this medrep', null, 422);
            }

            $validator = Validator::make($request->all(), [
                'doctor_id' => 'sometimes|required|integer|exists:doctors,id',
                'scheduled_at' => 'sometimes|required|date',
                'duration' => 'nullable|integer|min:15|max:480',
                'purpose' => 'nullable|string|max:500',
                'notes' => 'nullable|string|max:1000',
                'status' => 'sometimes|required|string|in:scheduled,completed,cancelled,no_show',
                'feedback' => 'nullable|string|max:1000',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $visit->update($validator->validated());
            $visit->load(['doctor.user', 'clinic', 'medrep']);

            return $this->successResponse([
                'visit' => $visit,
            ], 'Medrep visit updated successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Cancel medrep visit
     */
    public function cancelVisit(Medrep $medrep, MedrepVisit $visit): JsonResponse
    {
        try {
            if (!$this->hasClinicAccess($medrep->clinic_id)) {
                return $this->forbiddenResponse('No access to this medrep');
            }

            if ($visit->medrep_id !== $medrep->id) {
                return $this->errorResponse('Visit does not belong to this medrep', null, 422);
            }

            if ($visit->status === 'cancelled') {
                return $this->errorResponse('Visit is already cancelled', null, 422);
            }

            $visit->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
            ]);

            $visit->load(['doctor.user', 'clinic', 'medrep']);

            return $this->successResponse([
                'visit' => $visit,
            ], 'Medrep visit cancelled successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Get all medrep visits
     */
    public function allVisits(Request $request): JsonResponse
    {
        try {
            $currentClinic = $this->getCurrentClinic();

            if (!$currentClinic) {
                return $this->errorResponse('No clinic access', null, 403);
            }

            [$perPage, $page] = $this->getPaginationParams($request);

            $visits = MedrepVisit::where('clinic_id', $currentClinic->id)
                ->with(['doctor.user', 'clinic', 'medrep'])
                ->latest()
                ->paginate($perPage, ['*'], 'page', $page);

            return $this->paginatedResponse($visits, 'All medrep visits retrieved');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Get upcoming medrep visits
     */
    public function upcomingVisits(Request $request): JsonResponse
    {
        try {
            $currentClinic = $this->getCurrentClinic();

            if (!$currentClinic) {
                return $this->errorResponse('No clinic access', null, 403);
            }

            $visits = MedrepVisit::where('clinic_id', $currentClinic->id)
                ->where('scheduled_at', '>=', now())
                ->where('status', 'scheduled')
                ->with(['doctor.user', 'clinic', 'medrep'])
                ->orderBy('scheduled_at')
                ->get();

            return $this->successResponse([
                'visits' => $visits,
            ], 'Upcoming medrep visits retrieved');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
}
