<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\Encounter;
use App\Models\Patient;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;

class EncounterController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/v1/encounters",
     *     summary="Get all encounters",
     *     description="Retrieve a paginated list of medical encounters for the current clinic",
     *     tags={"Encounters"},
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
     *         name="status",
     *         in="query",
     *         description="Filter by status",
     *         @OA\Schema(type="string", enum={"scheduled","in_progress","completed","cancelled","no_show"})
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Filter by encounter type",
     *         @OA\Schema(type="string", enum={"consultation","follow_up","emergency","routine_checkup","specialist_consultation","procedure","surgery","lab_test","imaging","physical_therapy"})
     *     ),
     *     @OA\Parameter(
     *         name="patient_id",
     *         in="query",
     *         description="Filter by patient ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="doctor_id",
     *         in="query",
     *         description="Filter by doctor ID",
     *         @OA\Schema(type="integer", example=1)
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
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="Sort field",
     *         @OA\Schema(type="string", enum={"id","date","status","type","created_at"})
     *     ),
     *     @OA\Parameter(
     *         name="direction",
     *         in="query",
     *         description="Sort direction",
     *         @OA\Schema(type="string", enum={"asc","desc"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Encounters retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Encounters retrieved successfully"),
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
     *                                 @OA\Property(property="clinic_id", type="integer", example=1),
     *                                 @OA\Property(property="date", type="string", format="date", example="2024-01-15"),
     *                                 @OA\Property(property="type", type="string", example="consultation"),
     *                                 @OA\Property(property="status", type="string", example="completed"),
     *                                 @OA\Property(property="chief_complaint", type="string", example="Chest pain"),
     *                                 @OA\Property(property="patient", ref="#/components/schemas/Patient"),
     *                                 @OA\Property(property="doctor", ref="#/components/schemas/Doctor"),
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
            // Permission check is handled by middleware, but we can add additional validation
            $this->requirePermission('encounters.view');

            $currentClinic = $this->getCurrentClinic();

            if (!$currentClinic) {
                return $this->errorResponse('No clinic access', null, 403);
            }

            [$perPage, $page] = $this->getPaginationParams($request);
            [$sort, $direction] = $this->getSortingParams($request, [
                'id', 'date', 'status', 'type', 'created_at'
            ]);

            $query = Encounter::where('clinic_id', $currentClinic->id)
                ->with(['patient', 'doctor.user', 'clinic']);

            // Filter by status
            if ($request->has('status')) {
                $query->where('status', $request->get('status'));
            }

            // Filter by type
            if ($request->has('type')) {
                $query->where('type', $request->get('type'));
            }

            // Filter by patient
            if ($request->has('patient_id')) {
                $query->where('patient_id', $request->get('patient_id'));
            }

            // Filter by doctor
            if ($request->has('doctor_id')) {
                $query->where('doctor_id', $request->get('doctor_id'));
            }

            // Filter by date range
            if ($request->has('date_from') || $request->has('date_to')) {
                if ($request->has('date_from')) {
                    $query->where('date', '>=', $request->get('date_from'));
                }
                if ($request->has('date_to')) {
                    $query->where('date', '<=', $request->get('date_to'));
                }
            }

            $encounters = $query->orderBy($sort, $direction)
                ->paginate($perPage, ['*'], 'page', $page);

            return $this->paginatedResponse($encounters, 'Encounters retrieved successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/encounters",
     *     summary="Create a new encounter",
     *     description="Create a new medical encounter record",
     *     tags={"Encounters"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"patient_id","doctor_id","date","type"},
     *             @OA\Property(property="patient_id", type="integer", example=1),
     *             @OA\Property(property="doctor_id", type="integer", example=1),
     *             @OA\Property(property="date", type="string", format="date", example="2024-01-15"),
     *             @OA\Property(property="type", type="string", enum={"consultation","follow_up","emergency","routine_checkup","specialist_consultation","procedure","surgery","lab_test","imaging","physical_therapy"}, example="consultation"),
     *             @OA\Property(property="status", type="string", enum={"scheduled","in_progress","completed","cancelled","no_show"}, example="scheduled"),
     *             @OA\Property(property="chief_complaint", type="string", example="Chest pain for 2 days"),
     *             @OA\Property(property="history_present_illness", type="string", example="Patient reports chest pain that started 2 days ago..."),
     *             @OA\Property(property="physical_examination", type="string", example="Vital signs stable, heart rate regular..."),
     *             @OA\Property(property="assessment", type="string", example="Possible cardiac issue, rule out MI"),
     *             @OA\Property(property="plan", type="string", example="Order EKG, chest X-ray, cardiac enzymes"),
     *             @OA\Property(
     *                 property="notes_soap",
     *                 type="object",
     *                 @OA\Property(property="subjective", type="string", example="Patient reports..."),
     *                 @OA\Property(property="objective", type="string", example="Physical exam shows..."),
     *                 @OA\Property(property="assessment", type="string", example="Diagnosis..."),
     *                 @OA\Property(property="plan", type="string", example="Treatment plan...")
     *             ),
     *             @OA\Property(
     *                 property="vital_signs",
     *                 type="object",
     *                 @OA\Property(property="blood_pressure", type="string", example="120/80"),
     *                 @OA\Property(property="heart_rate", type="integer", example=72),
     *                 @OA\Property(property="temperature", type="number", format="float", example=98.6),
     *                 @OA\Property(property="respiratory_rate", type="integer", example=16),
     *                 @OA\Property(property="oxygen_saturation", type="integer", example=98)
     *             ),
     *             @OA\Property(
     *                 property="diagnosis",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="code", type="string", example="I25.9"),
     *                     @OA\Property(property="description", type="string", example="Chronic ischemic heart disease, unspecified")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Encounter created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Encounter created successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="encounter", type="object")
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
            // Permission check is handled by middleware, but we can add additional validation
            $this->requirePermission('encounters.create');

            $currentClinic = $this->getCurrentClinic();

            if (!$currentClinic) {
                return $this->errorResponse('No clinic access', null, 403);
            }

            $validator = Validator::make($request->all(), [
                'patient_id' => 'required|integer|exists:patients,id',
                'doctor_id' => 'required|integer|exists:doctors,id',
                'date' => 'required|date',
                'type' => 'required|string|in:consultation,follow_up,emergency,routine_checkup,specialist_consultation,procedure,surgery,lab_test,imaging,physical_therapy',
                'status' => 'nullable|string|in:scheduled,in_progress,completed,cancelled,no_show',
                'chief_complaint' => 'nullable|string|max:1000',
                'history_present_illness' => 'nullable|string|max:2000',
                'physical_examination' => 'nullable|string|max:2000',
                'assessment' => 'nullable|string|max:1000',
                'plan' => 'nullable|string|max:1000',
                'notes_soap' => 'nullable|array',
                'vital_signs' => 'nullable|array',
                'diagnosis' => 'nullable|array',
                'treatment_plan' => 'nullable|array',
                'follow_up_instructions' => 'nullable|string|max:1000',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $data = $validator->validated();
            $data['clinic_id'] = $currentClinic->id;
            $data['status'] = $data['status'] ?? 'scheduled';

            // Check if patient belongs to the same clinic
            $patient = Patient::findOrFail($data['patient_id']);
            if ($patient->clinic_id !== $currentClinic->id) {
                return $this->errorResponse('Patient does not belong to this clinic', null, 422);
            }

            // Check if doctor belongs to the same clinic
            $doctor = Doctor::findOrFail($data['doctor_id']);
            if ($doctor->clinic_id !== $currentClinic->id) {
                return $this->errorResponse('Doctor does not belong to this clinic', null, 422);
            }

            $encounter = Encounter::create($data);
            $encounter->load(['patient', 'doctor.user', 'clinic']);

            return $this->successResponse([
                'encounter' => $encounter,
            ], 'Encounter created successfully', 201);

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/encounters/{encounter}",
     *     summary="Get encounter details",
     *     description="Retrieve detailed information about a specific encounter",
     *     tags={"Encounters"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="encounter",
     *         in="path",
     *         description="Encounter ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Encounter details retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Encounter retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="encounter", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="patient_id", type="integer", example=1),
     *                     @OA\Property(property="doctor_id", type="integer", example=1),
     *                     @OA\Property(property="appointment_id", type="integer", example=1),
     *                     @OA\Property(property="encounter_type", type="string", example="consultation"),
     *                     @OA\Property(property="chief_complaint", type="string", example="Chest pain and shortness of breath"),
     *                     @OA\Property(property="encounter_date", type="string", format="date-time", example="2024-01-15T10:00:00Z"),
     *                     @OA\Property(property="status", type="string", example="completed"),
     *                     @OA\Property(property="vital_signs", type="object",
     *                         @OA\Property(property="blood_pressure_systolic", type="integer", example=120),
     *                         @OA\Property(property="blood_pressure_diastolic", type="integer", example=80),
     *                         @OA\Property(property="heart_rate", type="integer", example=72),
     *                         @OA\Property(property="temperature", type="number", format="float", example=98.6),
     *                         @OA\Property(property="respiratory_rate", type="integer", example=16),
     *                         @OA\Property(property="oxygen_saturation", type="integer", example=98),
     *                         @OA\Property(property="weight", type="number", format="float", example=70.5),
     *                         @OA\Property(property="height", type="number", format="float", example=175.0)
     *                     ),
     *                     @OA\Property(property="soap_notes", type="object",
     *                         @OA\Property(property="subjective", type="string", example="Patient reports chest pain that started 2 hours ago"),
     *                         @OA\Property(property="objective", type="string", example="Patient appears anxious, vital signs stable"),
     *                         @OA\Property(property="assessment", type="string", example="Possible cardiac event, rule out myocardial infarction"),
     *                         @OA\Property(property="plan", type="string", example="Order EKG, cardiac enzymes, chest X-ray")
     *                     ),
     *                     @OA\Property(property="diagnosis", type="array", @OA\Items(type="string")),
     *                     @OA\Property(property="treatment_plan", type="string", example="Rest, monitor symptoms, follow up in 24 hours"),
     *                     @OA\Property(property="follow_up_instructions", type="string", example="Return if symptoms worsen, follow up appointment in 1 week"),
     *                     @OA\Property(property="notes", type="string", example="Patient educated about symptoms to watch for"),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time")
     *                 ),
     *                 @OA\Property(property="patient", type="object", nullable=true),
     *                 @OA\Property(property="doctor", type="object", nullable=true),
     *                 @OA\Property(property="appointment", type="object", nullable=true),
     *                 @OA\Property(property="prescriptions", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="lab_results", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="file_assets", type="array", @OA\Items(type="object"))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="No access to this encounter",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Encounter not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function show(Encounter $encounter): JsonResponse
    {
        try {
            if (!$this->hasClinicAccess($encounter->clinic_id)) {
                return $this->forbiddenResponse('No access to this encounter');
            }

            $encounter->load([
                'patient',
                'doctor.user',
                'clinic',
                'prescriptions',
                'labResults',
                'fileAssets'
            ]);

            return $this->successResponse([
                'encounter' => $encounter,
                'soap_notes' => $encounter->soap_notes,
            ], 'Encounter retrieved successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Update the specified encounter
     */
    public function update(Request $request, Encounter $encounter): JsonResponse
    {
        try {
            if (!$this->hasClinicAccess($encounter->clinic_id)) {
                return $this->forbiddenResponse('No access to this encounter');
            }

            $validator = Validator::make($request->all(), [
                'patient_id' => 'sometimes|required|integer|exists:patients,id',
                'doctor_id' => 'sometimes|required|integer|exists:doctors,id',
                'date' => 'sometimes|required|date',
                'type' => 'sometimes|required|string|in:consultation,follow_up,emergency,routine_checkup,specialist_consultation,procedure,surgery,lab_test,imaging,physical_therapy',
                'status' => 'sometimes|required|string|in:scheduled,in_progress,completed,cancelled,no_show',
                'chief_complaint' => 'nullable|string|max:1000',
                'history_present_illness' => 'nullable|string|max:2000',
                'physical_examination' => 'nullable|string|max:2000',
                'assessment' => 'nullable|string|max:1000',
                'plan' => 'nullable|string|max:1000',
                'notes_soap' => 'nullable|array',
                'vital_signs' => 'nullable|array',
                'diagnosis' => 'nullable|array',
                'treatment_plan' => 'nullable|array',
                'follow_up_instructions' => 'nullable|string|max:1000',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $encounter->update($validator->validated());
            $encounter->load(['patient', 'doctor.user', 'clinic']);

            return $this->successResponse([
                'encounter' => $encounter,
            ], 'Encounter updated successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Remove the specified encounter
     */
    public function destroy(Encounter $encounter): JsonResponse
    {
        try {
            if (!$this->hasClinicAccess($encounter->clinic_id)) {
                return $this->forbiddenResponse('No access to this encounter');
            }

            // Check if encounter has any related records
            $hasRecords = $encounter->prescriptions()->exists() ||
                         $encounter->labResults()->exists() ||
                         $encounter->fileAssets()->exists();

            if ($hasRecords) {
                return $this->errorResponse('Cannot delete encounter with existing medical records', null, 422);
            }

            $encounter->delete();

            return $this->successResponse(null, 'Encounter deleted successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/encounters/{encounter}/prescriptions",
     *     summary="Get encounter prescriptions",
     *     description="Retrieve all prescriptions associated with an encounter",
     *     tags={"Encounters"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="encounter",
     *         in="path",
     *         description="Encounter ID",
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
     *         description="Filter by prescription status",
     *         @OA\Schema(type="string", enum={"pending","verified","dispensed","expired"}, example="verified")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Encounter prescriptions retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Encounter prescriptions retrieved successfully"),
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
     *                                 @OA\Property(property="encounter_id", type="integer", example=1),
     *                                 @OA\Property(property="prescription_number", type="string", example="RX-2024-001"),
     *                                 @OA\Property(property="prescription_date", type="string", format="date", example="2024-01-15"),
     *                                 @OA\Property(property="valid_until", type="string", format="date", example="2024-02-15"),
     *                                 @OA\Property(property="status", type="string", example="verified"),
     *                                 @OA\Property(property="refills_allowed", type="integer", example=2),
     *                                 @OA\Property(property="refills_used", type="integer", example=0),
     *                                 @OA\Property(property="is_urgent", type="boolean", example=false),
     *                                 @OA\Property(property="is_verified", type="boolean", example=true),
     *                                 @OA\Property(property="is_dispensed", type="boolean", example=false),
     *                                 @OA\Property(property="items_count", type="integer", example=2),
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
     *         description="No access to this encounter",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Encounter not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function prescriptions(Request $request, Encounter $encounter): JsonResponse
    {
        try {
            if (!$this->hasClinicAccess($encounter->clinic_id)) {
                return $this->forbiddenResponse('No access to this encounter');
            }

            [$perPage, $page] = $this->getPaginationParams($request);

            $prescriptions = $encounter->prescriptions()
                ->with(['patient', 'doctor.user', 'clinic', 'items'])
                ->latest()
                ->paginate($perPage, ['*'], 'page', $page);

            return $this->paginatedResponse($prescriptions, 'Encounter prescriptions retrieved');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/encounters/{encounter}/lab-results",
     *     summary="Get encounter lab results",
     *     description="Retrieve all lab results associated with an encounter",
     *     tags={"Encounters"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="encounter",
     *         in="path",
     *         description="Encounter ID",
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
     *         description="Filter by lab result status",
     *         @OA\Schema(type="string", enum={"pending","completed","abnormal","critical"}, example="completed")
     *     ),
     *     @OA\Parameter(
     *         name="test_name",
     *         in="query",
     *         description="Filter by test name",
     *         @OA\Schema(type="string", example="Complete Blood Count")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Encounter lab results retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Encounter lab results retrieved successfully"),
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
     *                                 @OA\Property(property="encounter_id", type="integer", example=1),
     *                                 @OA\Property(property="test_name", type="string", example="Complete Blood Count"),
     *                                 @OA\Property(property="test_code", type="string", example="CBC"),
     *                                 @OA\Property(property="lab_name", type="string", example="Central Lab Services"),
     *                                 @OA\Property(property="lab_reference", type="string", example="LAB-2024-001"),
     *                                 @OA\Property(property="ordered_date", type="string", format="date", example="2024-01-15"),
     *                                 @OA\Property(property="result_date", type="string", format="date", example="2024-01-17"),
     *                                 @OA\Property(property="status", type="string", example="completed"),
     *                                 @OA\Property(property="priority", type="string", example="routine"),
     *                                 @OA\Property(property="results", type="object", example={"hemoglobin":"14.2 g/dL","hematocrit":"42.1%","white_blood_cells":"7.2 K/uL"}),
     *                                 @OA\Property(property="reference_ranges", type="object", example={"hemoglobin":"12.0-16.0 g/dL","hematocrit":"36.0-46.0%"}),
     *                                 @OA\Property(property="abnormal_flags", type="array", @OA\Items(type="string")),
     *                                 @OA\Property(property="interpretation", type="string", example="All values within normal limits"),
     *                                 @OA\Property(property="is_verified", type="boolean", example=false),
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
     *         description="No access to this encounter",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Encounter not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function labResults(Request $request, Encounter $encounter): JsonResponse
    {
        try {
            if (!$this->hasClinicAccess($encounter->clinic_id)) {
                return $this->forbiddenResponse('No access to this encounter');
            }

            [$perPage, $page] = $this->getPaginationParams($request);

            $labResults = $encounter->labResults()
                ->with(['patient', 'doctor.user', 'clinic'])
                ->latest()
                ->paginate($perPage, ['*'], 'page', $page);

            return $this->paginatedResponse($labResults, 'Encounter lab results retrieved');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/encounters/{encounter}/file-assets",
     *     summary="Get encounter files",
     *     description="Retrieve all file assets associated with an encounter",
     *     tags={"Encounters"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="encounter",
     *         in="path",
     *         description="Encounter ID",
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
     *         name="category",
     *         in="query",
     *         description="Filter by file category",
     *         @OA\Schema(type="string", example="medical_images")
     *     ),
     *     @OA\Parameter(
     *         name="file_type",
     *         in="query",
     *         description="Filter by file type",
     *         @OA\Schema(type="string", example="image")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Encounter files retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Encounter files retrieved successfully"),
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
     *                                 @OA\Property(property="encounter_id", type="integer", example=1),
     *                                 @OA\Property(property="title", type="string", example="Chest X-Ray"),
     *                                 @OA\Property(property="description", type="string", example="Chest X-ray for chest pain evaluation"),
     *                                 @OA\Property(property="filename", type="string", example="chest_xray_20240115.jpg"),
     *                                 @OA\Property(property="original_filename", type="string", example="chest_xray.jpg"),
     *                                 @OA\Property(property="file_size", type="integer", example=2048576),
     *                                 @OA\Property(property="mime_type", type="string", example="image/jpeg"),
     *                                 @OA\Property(property="category", type="string", example="medical_images"),
     *                                 @OA\Property(property="file_path", type="string", example="/storage/files/chest_xray_20240115.jpg"),
     *                                 @OA\Property(property="download_url", type="string", example="/api/v1/file-assets/1/download"),
     *                                 @OA\Property(property="preview_url", type="string", example="/api/v1/file-assets/1/preview"),
     *                                 @OA\Property(property="is_private", type="boolean", example=false),
     *                                 @OA\Property(property="tags", type="array", @OA\Items(type="string")),
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
     *         description="No access to this encounter",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Encounter not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function fileAssets(Request $request, Encounter $encounter): JsonResponse
    {
        try {
            if (!$this->hasClinicAccess($encounter->clinic_id)) {
                return $this->forbiddenResponse('No access to this encounter');
            }

            [$perPage, $page] = $this->getPaginationParams($request);

            $fileAssets = $encounter->fileAssets()
                ->latest()
                ->paginate($perPage, ['*'], 'page', $page);

            return $this->paginatedResponse($fileAssets, 'Encounter files retrieved');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/encounters/{encounter}/file-assets",
     *     summary="Upload encounter file",
     *     description="Upload a file asset for an encounter",
     *     tags={"Encounters"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="encounter",
     *         in="path",
     *         description="Encounter ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="file", type="string", format="binary", description="File to upload"),
     *                 @OA\Property(property="title", type="string", example="Chest X-Ray Report", description="File title"),
     *                 @OA\Property(property="description", type="string", example="Chest X-ray for chest pain evaluation", description="File description"),
     *                 @OA\Property(property="category", type="string", example="medical_images", description="File category"),
     *                 @OA\Property(property="is_private", type="boolean", example=false, description="Whether file is private"),
     *                 @OA\Property(property="tags", type="array", @OA\Items(type="string"), example={"x-ray","chest","diagnostic"}, description="File tags")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="File uploaded successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="File uploaded successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="file_asset", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="encounter_id", type="integer", example=1),
     *                     @OA\Property(property="title", type="string", example="Chest X-Ray Report"),
     *                     @OA\Property(property="description", type="string", example="Chest X-ray for chest pain evaluation"),
     *                     @OA\Property(property="filename", type="string", example="chest_xray_20240115.jpg"),
     *                     @OA\Property(property="original_filename", type="string", example="chest_xray.jpg"),
     *                     @OA\Property(property="file_size", type="integer", example=2048576),
     *                     @OA\Property(property="mime_type", type="string", example="image/jpeg"),
     *                     @OA\Property(property="category", type="string", example="medical_images"),
     *                     @OA\Property(property="file_path", type="string", example="/storage/files/chest_xray_20240115.jpg"),
     *                     @OA\Property(property="download_url", type="string", example="/api/v1/file-assets/1/download"),
     *                     @OA\Property(property="preview_url", type="string", example="/api/v1/file-assets/1/preview"),
     *                     @OA\Property(property="is_private", type="boolean", example=false),
     *                     @OA\Property(property="tags", type="array", @OA\Items(type="string")),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="No access to this encounter",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Encounter not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function uploadFile(Request $request, Encounter $encounter): JsonResponse
    {
        try {
            if (!$this->hasClinicAccess($encounter->clinic_id)) {
                return $this->forbiddenResponse('No access to this encounter');
            }

            $validator = Validator::make($request->all(), [
                'file' => 'required|file|max:10240', // 10MB max
                'category' => 'required|string|max:255',
                'description' => 'nullable|string|max:500',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $file = $request->file('file');
            $path = $file->store('encounters/' . $encounter->id, 'private');

            $fileAsset = $encounter->fileAssets()->create([
                'clinic_id' => $encounter->clinic_id,
                'url' => $path,
                'mime' => $file->getMimeType(),
                'size' => $file->getSize(),
                'checksum' => md5_file($file->getPathname()),
                'category' => $request->category,
                'description' => $request->description,
                'file_name' => $file->hashName(),
                'original_name' => $file->getClientOriginalName(),
            ]);

            return $this->successResponse([
                'file_asset' => $fileAsset,
            ], 'File uploaded successfully', 201);

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/encounters/{encounter}/soap-notes",
     *     summary="Update SOAP notes",
     *     description="Update the SOAP (Subjective, Objective, Assessment, Plan) notes for an encounter",
     *     tags={"Encounters"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="encounter",
     *         in="path",
     *         description="Encounter ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="soap_notes", type="object",
     *                 @OA\Property(property="subjective", type="string", example="Patient reports chest pain that started 2 hours ago, described as sharp and localized to left side", description="Patient's subjective complaints and history"),
     *                 @OA\Property(property="objective", type="string", example="Patient appears anxious but alert. Vital signs: BP 120/80, HR 72, Temp 98.6°F, RR 16, O2 sat 98%. No acute distress noted", description="Objective findings from examination"),
     *                 @OA\Property(property="assessment", type="string", example="Chest pain, rule out cardiac etiology. Differential includes musculoskeletal pain, GERD, anxiety", description="Clinical assessment and differential diagnosis"),
     *                 @OA\Property(property="plan", type="string", example="1. Order EKG and cardiac enzymes 2. Chest X-ray 3. Consider stress test if cardiac enzymes negative 4. Follow up in 24 hours", description="Treatment plan and follow-up")
     *             ),
     *             @OA\Property(property="vital_signs", type="object",
     *                 @OA\Property(property="blood_pressure_systolic", type="integer", example=120),
     *                 @OA\Property(property="blood_pressure_diastolic", type="integer", example=80),
     *                 @OA\Property(property="heart_rate", type="integer", example=72),
     *                 @OA\Property(property="temperature", type="number", format="float", example=98.6),
     *                 @OA\Property(property="respiratory_rate", type="integer", example=16),
     *                 @OA\Property(property="oxygen_saturation", type="integer", example=98),
     *                 @OA\Property(property="weight", type="number", format="float", example=70.5),
     *                 @OA\Property(property="height", type="number", format="float", example=175.0)
     *             ),
     *             @OA\Property(property="diagnosis", type="array", @OA\Items(type="string"), example={"Chest pain, unspecified","Anxiety disorder"}, description="Diagnosis codes or descriptions"),
     *             @OA\Property(property="treatment_plan", type="string", example="Rest, monitor symptoms, follow up in 24 hours. Consider cardiac workup if symptoms persist", description="Treatment plan"),
     *             @OA\Property(property="follow_up_instructions", type="string", example="Return if symptoms worsen, follow up appointment in 1 week", description="Follow-up instructions")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="SOAP notes updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="SOAP notes updated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="encounter", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="patient_id", type="integer", example=1),
     *                     @OA\Property(property="doctor_id", type="integer", example=1),
     *                     @OA\Property(property="encounter_type", type="string", example="consultation"),
     *                     @OA\Property(property="chief_complaint", type="string", example="Chest pain and shortness of breath"),
     *                     @OA\Property(property="encounter_date", type="string", format="date-time", example="2024-01-15T10:00:00Z"),
     *                     @OA\Property(property="status", type="string", example="in_progress"),
     *                     @OA\Property(property="soap_notes", type="object",
     *                         @OA\Property(property="subjective", type="string", example="Patient reports chest pain that started 2 hours ago"),
     *                         @OA\Property(property="objective", type="string", example="Patient appears anxious, vital signs stable"),
     *                         @OA\Property(property="assessment", type="string", example="Possible cardiac event, rule out myocardial infarction"),
     *                         @OA\Property(property="plan", type="string", example="Order EKG, cardiac enzymes, chest X-ray")
     *                     ),
     *                     @OA\Property(property="vital_signs", type="object"),
     *                     @OA\Property(property="diagnosis", type="array", @OA\Items(type="string")),
     *                     @OA\Property(property="treatment_plan", type="string", example="Rest, monitor symptoms, follow up in 24 hours"),
     *                     @OA\Property(property="follow_up_instructions", type="string", example="Return if symptoms worsen, follow up appointment in 1 week"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="No access to this encounter",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Encounter not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function updateSoapNotes(Request $request, Encounter $encounter): JsonResponse
    {
        try {
            if (!$this->hasClinicAccess($encounter->clinic_id)) {
                return $this->forbiddenResponse('No access to this encounter');
            }

            $validator = Validator::make($request->all(), [
                'soap_notes' => 'required|array',
                'soap_notes.subjective' => 'nullable|string|max:2000',
                'soap_notes.objective' => 'nullable|string|max:2000',
                'soap_notes.assessment' => 'nullable|string|max:2000',
                'soap_notes.plan' => 'nullable|string|max:2000',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $encounter->setSoapNotes($request->soap_notes);
            $encounter->save();

            return $this->successResponse([
                'encounter' => $encounter,
                'soap_notes' => $encounter->soap_notes,
            ], 'SOAP notes updated successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/encounters/{encounter}/complete",
     *     summary="Complete encounter",
     *     description="Mark an encounter as completed and finalize all documentation",
     *     tags={"Encounters"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="encounter",
     *         in="path",
     *         description="Encounter ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="completion_notes", type="string", example="Encounter completed successfully. All tests ordered and prescriptions written.", description="Notes about encounter completion"),
     *             @OA\Property(property="final_diagnosis", type="array", @OA\Items(type="string"), example={"Chest pain, unspecified","Anxiety disorder"}, description="Final diagnosis codes"),
     *             @OA\Property(property="discharge_instructions", type="string", example="Return if symptoms worsen, follow up appointment in 1 week", description="Discharge instructions for patient"),
     *             @OA\Property(property="follow_up_required", type="boolean", example=true, description="Whether follow-up is required"),
     *             @OA\Property(property="follow_up_date", type="string", format="date", example="2024-01-22", description="Recommended follow-up date")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Encounter completed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Encounter completed successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="encounter", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="patient_id", type="integer", example=1),
     *                     @OA\Property(property="doctor_id", type="integer", example=1),
     *                     @OA\Property(property="encounter_type", type="string", example="consultation"),
     *                     @OA\Property(property="chief_complaint", type="string", example="Chest pain and shortness of breath"),
     *                     @OA\Property(property="encounter_date", type="string", format="date-time", example="2024-01-15T10:00:00Z"),
     *                     @OA\Property(property="status", type="string", example="completed"),
     *                     @OA\Property(property="completion_notes", type="string", example="Encounter completed successfully. All tests ordered and prescriptions written."),
     *                     @OA\Property(property="final_diagnosis", type="array", @OA\Items(type="string")),
     *                     @OA\Property(property="discharge_instructions", type="string", example="Return if symptoms worsen, follow up appointment in 1 week"),
     *                     @OA\Property(property="follow_up_required", type="boolean", example=true),
     *                     @OA\Property(property="follow_up_date", type="string", format="date", example="2024-01-22"),
     *                     @OA\Property(property="completed_at", type="string", format="date-time"),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time")
     *                 ),
     *                 @OA\Property(
     *                     property="summary",
     *                     type="object",
     *                     @OA\Property(property="prescriptions_count", type="integer", example=2),
     *                     @OA\Property(property="lab_orders_count", type="integer", example=3),
     *                     @OA\Property(property="files_uploaded_count", type="integer", example=1),
     *                     @OA\Property(property="encounter_duration_minutes", type="integer", example=45),
     *                     @OA\Property(property="next_appointment_scheduled", type="boolean", example=true)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="No access to this encounter",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Encounter not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Encounter already completed or validation error",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function complete(Encounter $encounter): JsonResponse
    {
        try {
            if (!$this->hasClinicAccess($encounter->clinic_id)) {
                return $this->forbiddenResponse('No access to this encounter');
            }

            if ($encounter->status === 'completed') {
                return $this->errorResponse('Encounter is already completed', null, 422);
            }

            $encounter->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            $encounter->load(['patient', 'doctor.user', 'clinic']);

            return $this->successResponse([
                'encounter' => $encounter,
            ], 'Encounter completed successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
}
