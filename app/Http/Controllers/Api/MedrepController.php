<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\Medrep;
use App\Models\MedrepVisit;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;

class MedrepController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/v1/medreps",
     *     summary="Get all medical representatives",
     *     description="Retrieve a paginated list of medical representatives for the current clinic",
     *     tags={"Medreps"},
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
     *         description="Search by name, company, or email",
     *         @OA\Schema(type="string", example="John Smith")
     *     ),
     *     @OA\Parameter(
     *         name="company",
     *         in="query",
     *         description="Filter by company",
     *         @OA\Schema(type="string", example="Pfizer")
     *     ),
     *     @OA\Parameter(
     *         name="specialty",
     *         in="query",
     *         description="Filter by specialty",
     *         @OA\Schema(type="string", example="Cardiology")
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="Sort field",
     *         @OA\Schema(type="string", enum={"id","name","company","email","phone","created_at"})
     *     ),
     *     @OA\Parameter(
     *         name="direction",
     *         in="query",
     *         description="Sort direction",
     *         @OA\Schema(type="string", enum={"asc","desc"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Medical representatives retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Medreps retrieved successfully"),
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
     *                                 @OA\Property(property="name", type="string", example="John Smith"),
     *                                 @OA\Property(property="company", type="string", example="Pfizer"),
     *                                 @OA\Property(property="email", type="string", format="email", example="john@pfizer.com"),
     *                                 @OA\Property(property="phone", type="string", example="+1234567890"),
     *                                 @OA\Property(property="specialty", type="string", example="Cardiology"),
     *                                 @OA\Property(property="territory", type="string", example="North Region"),
     *                                 @OA\Property(property="notes", type="string", example="Specializes in cardiovascular drugs"),
     *                                 @OA\Property(property="clinic_id", type="integer", example=1),
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
     * @OA\Post(
     *     path="/api/v1/medreps",
     *     summary="Create new medical representative",
     *     description="Create a new medical representative record",
     *     tags={"Medreps"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="John Smith"),
     *             @OA\Property(property="company", type="string", example="PharmaCorp Inc."),
     *             @OA\Property(property="email", type="string", format="email", example="john.smith@pharmacorp.com"),
     *             @OA\Property(property="phone", type="string", example="+1234567890"),
     *             @OA\Property(property="specialization", type="string", example="Cardiology"),
     *             @OA\Property(property="territory", type="string", example="North Region"),
     *             @OA\Property(property="license_number", type="string", example="MR123456"),
     *             @OA\Property(property="address", type="object",
     *                 @OA\Property(property="street", type="string", example="123 Business St"),
     *                 @OA\Property(property="city", type="string", example="New York"),
     *                 @OA\Property(property="state", type="string", example="NY"),
     *                 @OA\Property(property="zip", type="string", example="10001"),
     *                 @OA\Property(property="country", type="string", example="USA")
     *             ),
     *             @OA\Property(property="notes", type="string", example="Specializes in cardiovascular medications"),
     *             @OA\Property(property="is_active", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Medical representative created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Medical representative created successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="medrep", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="John Smith"),
     *                     @OA\Property(property="company", type="string", example="PharmaCorp Inc."),
     *                     @OA\Property(property="email", type="string", example="john.smith@pharmacorp.com"),
     *                     @OA\Property(property="phone", type="string", example="+1234567890"),
     *                     @OA\Property(property="specialization", type="string", example="Cardiology"),
     *                     @OA\Property(property="territory", type="string", example="North Region"),
     *                     @OA\Property(property="license_number", type="string", example="MR123456"),
     *                     @OA\Property(property="is_active", type="boolean", example=true),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time")
     *                 )
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
     * @OA\Get(
     *     path="/api/v1/medreps/{medrep}",
     *     summary="Get medical representative details",
     *     description="Retrieve detailed information about a specific medical representative",
     *     tags={"Medreps"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="medrep",
     *         in="path",
     *         description="Medical representative ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Medical representative details retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Medical representative retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="medrep", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="John Smith"),
     *                     @OA\Property(property="company", type="string", example="PharmaCorp Inc."),
     *                     @OA\Property(property="email", type="string", example="john.smith@pharmacorp.com"),
     *                     @OA\Property(property="phone", type="string", example="+1234567890"),
     *                     @OA\Property(property="specialization", type="string", example="Cardiology"),
     *                     @OA\Property(property="territory", type="string", example="North Region"),
     *                     @OA\Property(property="license_number", type="string", example="MR123456"),
     *                     @OA\Property(property="address", type="object"),
     *                     @OA\Property(property="notes", type="string", example="Specializes in cardiovascular medications"),
     *                     @OA\Property(property="is_active", type="boolean", example=true),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time")
     *                 ),
     *                 @OA\Property(
     *                     property="statistics",
     *                     type="object",
     *                     @OA\Property(property="total_visits", type="integer", example=25),
     *                     @OA\Property(property="last_visit", type="string", format="date", example="2024-01-10"),
     *                     @OA\Property(property="next_visit", type="string", format="date", example="2024-01-20"),
     *                     @OA\Property(property="average_visit_duration", type="number", format="float", example=45.5)
     *                 ),
     *                 @OA\Property(
     *                     property="recent_visits",
     *                     type="array",
     *                     @OA\Items(type="object")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="No access to this medical representative",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Medical representative not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
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
     * @OA\Get(
     *     path="/api/v1/medreps/{medrep}/visits",
     *     summary="Get medical representative visits",
     *     description="Retrieve all visits for a specific medical representative",
     *     tags={"Medreps"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="medrep",
     *         in="path",
     *         description="Medical representative ID",
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
     *         description="Filter by visit status",
     *         @OA\Schema(type="string", enum={"scheduled","completed","cancelled","no_show"}, example="completed")
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
     *         description="Medical representative visits retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Medical representative visits retrieved successfully"),
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
     *                                 @OA\Property(property="medrep_id", type="integer", example=1),
     *                                 @OA\Property(property="clinic_id", type="integer", example=1),
     *                                 @OA\Property(property="scheduled_at", type="string", format="date-time", example="2024-01-15T10:00:00Z"),
     *                                 @OA\Property(property="duration", type="integer", example=60, description="Duration in minutes"),
     *                                 @OA\Property(property="status", type="string", example="completed"),
     *                                 @OA\Property(property="purpose", type="string", example="Product presentation"),
     *                                 @OA\Property(property="notes", type="string", example="Discussed new cardiovascular medication"),
     *                                 @OA\Property(property="products_discussed", type="array", @OA\Items(type="string")),
     *                                 @OA\Property(property="samples_provided", type="array", @OA\Items(type="string")),
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
     *         description="No access to this medical representative",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Medical representative not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
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

    /**
     * @OA\Get(
     *     path="/api/v1/medreps/{medrep}/statistics",
     *     summary="Get medical representative statistics",
     *     description="Retrieve statistics and analytics for a specific medical representative",
     *     tags={"Medreps"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="medrep",
     *         in="path",
     *         description="Medical representative ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="period",
     *         in="query",
     *         description="Time period for statistics",
     *         @OA\Schema(type="string", enum={"month","quarter","year"}, example="month")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Medical representative statistics retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Medical representative statistics retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="period", type="string", example="month"),
     *                 @OA\Property(property="overview", type="object",
     *                     @OA\Property(property="total_visits", type="integer", example=25),
     *                     @OA\Property(property="completed_visits", type="integer", example=22),
     *                     @OA\Property(property="cancelled_visits", type="integer", example=2),
     *                     @OA\Property(property="no_show_visits", type="integer", example=1),
     *                     @OA\Property(property="average_visit_duration", type="number", format="float", example=45.5)
     *                 ),
     *                 @OA\Property(property="visit_trends", type="object",
     *                     @OA\Property(property="monthly_visits", type="array", @OA\Items(type="object")),
     *                     @OA\Property(property="visit_frequency", type="string", example="weekly"),
     *                     @OA\Property(property="peak_visit_day", type="string", example="Tuesday")
     *                 ),
     *                 @OA\Property(property="products", type="object",
     *                     @OA\Property(property="most_discussed", type="array", @OA\Items(type="string")),
     *                     @OA\Property(property="samples_provided", type="integer", example=15),
     *                     @OA\Property(property="product_interest_score", type="number", format="float", example=8.5)
     *                 ),
     *                 @OA\Property(property="performance", type="object",
     *                     @OA\Property(property="visit_completion_rate", type="number", format="float", example=88.0),
     *                     @OA\Property(property="punctuality_score", type="number", format="float", example=95.0),
     *                     @OA\Property(property="satisfaction_rating", type="number", format="float", example=4.2)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="No access to this medical representative",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Medical representative not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function statistics(Request $request, Medrep $medrep): JsonResponse
    {
        try {
            if (!$this->hasClinicAccess($medrep->clinic_id)) {
                return $this->forbiddenResponse('No access to this medrep');
            }

            $period = $request->get('period', 'month');

            // Mock statistics data
            $statistics = [
                'period' => $period,
                'overview' => [
                    'total_visits' => 25,
                    'completed_visits' => 22,
                    'cancelled_visits' => 2,
                    'no_show_visits' => 1,
                    'average_visit_duration' => 45.5
                ],
                'visit_trends' => [
                    'monthly_visits' => [],
                    'visit_frequency' => 'weekly',
                    'peak_visit_day' => 'Tuesday'
                ],
                'products' => [
                    'most_discussed' => ['CardioMed', 'BloodPressure Plus', 'HeartGuard'],
                    'samples_provided' => 15,
                    'product_interest_score' => 8.5
                ],
                'performance' => [
                    'visit_completion_rate' => 88.0,
                    'punctuality_score' => 95.0,
                    'satisfaction_rating' => 4.2
                ]
            ];

            return $this->successResponse($statistics, 'Medical representative statistics retrieved successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/medreps/search",
     *     summary="Search medical representatives",
     *     description="Search medical representatives by name, company, or specialization",
     *     tags={"Medreps"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="query",
     *         in="query",
     *         description="Search query",
     *         required=true,
     *         @OA\Schema(type="string", example="John Smith")
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Maximum number of results",
     *         @OA\Schema(type="integer", example=20)
     *     ),
     *     @OA\Parameter(
     *         name="company",
     *         in="query",
     *         description="Filter by company",
     *         @OA\Schema(type="string", example="PharmaCorp Inc.")
     *     ),
     *     @OA\Parameter(
     *         name="specialization",
     *         in="query",
     *         description="Filter by specialization",
     *         @OA\Schema(type="string", example="Cardiology")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Search results retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Search results retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="medreps",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="John Smith"),
     *                         @OA\Property(property="company", type="string", example="PharmaCorp Inc."),
     *                         @OA\Property(property="email", type="string", example="john.smith@pharmacorp.com"),
     *                         @OA\Property(property="phone", type="string", example="+1234567890"),
     *                         @OA\Property(property="specialization", type="string", example="Cardiology"),
     *                         @OA\Property(property="territory", type="string", example="North Region"),
     *                         @OA\Property(property="is_active", type="boolean", example=true)
     *                     )
     *                 ),
     *                 @OA\Property(property="total", type="integer", example=3),
     *                 @OA\Property(property="query", type="string", example="John Smith")
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

            $query = $request->get('query');
            $limit = $request->get('limit', 20);
            $company = $request->get('company');
            $specialization = $request->get('specialization');

            if (!$query) {
                return $this->errorResponse('Search query is required', null, 422);
            }

            // Mock search results
            $searchResults = [
                'medreps' => [
                    [
                        'id' => 1,
                        'name' => 'John Smith',
                        'company' => 'PharmaCorp Inc.',
                        'email' => 'john.smith@pharmacorp.com',
                        'phone' => '+1234567890',
                        'specialization' => 'Cardiology',
                        'territory' => 'North Region',
                        'is_active' => true
                    ],
                    [
                        'id' => 2,
                        'name' => 'Jane Doe',
                        'company' => 'MediPharm Ltd.',
                        'email' => 'jane.doe@medipharm.com',
                        'phone' => '+1234567891',
                        'specialization' => 'Oncology',
                        'territory' => 'South Region',
                        'is_active' => true
                    ]
                ],
                'total' => 2,
                'query' => $query
            ];

            return $this->successResponse($searchResults, 'Search results retrieved successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/medreps/companies",
     *     summary="Get companies list",
     *     description="Retrieve list of pharmaceutical companies with medical representatives",
     *     tags={"Medreps"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search companies by name",
     *         @OA\Schema(type="string", example="PharmaCorp")
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Maximum number of results",
     *         @OA\Schema(type="integer", example=50)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Companies list retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Companies list retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="companies",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="PharmaCorp Inc."),
     *                         @OA\Property(property="website", type="string", example="https://pharmacorp.com"),
     *                         @OA\Property(property="contact_email", type="string", example="contact@pharmacorp.com"),
     *                         @OA\Property(property="contact_phone", type="string", example="+1234567890"),
     *                         @OA\Property(property="specializations", type="array", @OA\Items(type="string"), example={"Cardiology","Oncology","Neurology"}),
     *                         @OA\Property(property="medrep_count", type="integer", example=5),
     *                         @OA\Property(property="is_active", type="boolean", example=true)
     *                     )
     *                 ),
     *                 @OA\Property(property="total", type="integer", example=15)
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
    public function companies(Request $request): JsonResponse
    {
        try {
            $currentClinic = $this->getCurrentClinic();

            if (!$currentClinic) {
                return $this->errorResponse('No clinic access', null, 403);
            }

            $search = $request->get('search');
            $limit = $request->get('limit', 50);

            // Mock companies data
            $companies = [
                'companies' => [
                    [
                        'id' => 1,
                        'name' => 'PharmaCorp Inc.',
                        'website' => 'https://pharmacorp.com',
                        'contact_email' => 'contact@pharmacorp.com',
                        'contact_phone' => '+1234567890',
                        'specializations' => ['Cardiology', 'Oncology', 'Neurology'],
                        'medrep_count' => 5,
                        'is_active' => true
                    ],
                    [
                        'id' => 2,
                        'name' => 'MediPharm Ltd.',
                        'website' => 'https://medipharm.com',
                        'contact_email' => 'info@medipharm.com',
                        'contact_phone' => '+1234567891',
                        'specializations' => ['Oncology', 'Hematology'],
                        'medrep_count' => 3,
                        'is_active' => true
                    ],
                    [
                        'id' => 3,
                        'name' => 'BioTech Solutions',
                        'website' => 'https://biotech-solutions.com',
                        'contact_email' => 'contact@biotech-solutions.com',
                        'contact_phone' => '+1234567892',
                        'specializations' => ['Biotechnology', 'Immunology'],
                        'medrep_count' => 2,
                        'is_active' => true
                    ]
                ],
                'total' => 3
            ];

            // Filter by search if provided
            if ($search) {
                $companies['companies'] = array_filter($companies['companies'], function($company) use ($search) {
                    return stripos($company['name'], $search) !== false;
                });
                $companies['total'] = count($companies['companies']);
            }

            return $this->successResponse($companies, 'Companies list retrieved successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
}
