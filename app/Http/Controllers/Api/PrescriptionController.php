<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\Patient;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="Prescription",
 *     type="object",
 *     title="Prescription",
 *     description="Prescription model",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="patient_id", type="integer", example=1),
 *     @OA\Property(property="doctor_id", type="integer", example=1),
 *     @OA\Property(property="clinic_id", type="integer", example=1),
 *     @OA\Property(property="prescription_number", type="string", example="RX001"),
 *     @OA\Property(property="prescription_date", type="string", format="date", example="2024-01-15"),
 *     @OA\Property(property="status", type="string", enum={"draft", "active", "completed", "cancelled"}, example="active"),
 *     @OA\Property(property="notes", type="string", example="Take with food"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(property="patient", type="object"),
 *     @OA\Property(property="doctor", type="object"),
 *     @OA\Property(
 *         property="items",
 *         type="array",
 *         @OA\Items(type="object")
 *     )
 * )
 */

/**
 * @OA\Schema(
 *     schema="PrescriptionItem",
 *     type="object",
 *     title="Prescription Item",
 *     description="Prescription item model",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="prescription_id", type="integer", example=1),
 *     @OA\Property(property="medication_name", type="string", example="Amoxicillin 500mg"),
 *     @OA\Property(property="dosage", type="string", example="500mg"),
 *     @OA\Property(property="frequency", type="string", example="Twice daily"),
 *     @OA\Property(property="duration", type="string", example="7 days"),
 *     @OA\Property(property="quantity", type="integer", example=14),
 *     @OA\Property(property="instructions", type="string", example="Take with food"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */



class PrescriptionController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/v1/prescriptions",
     *     summary="Get all prescriptions",
     *     description="Retrieve a paginated list of prescriptions for the current clinic",
     *     tags={"Prescriptions"},
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
     *         @OA\Schema(type="string", enum={"pending","verified","dispensed","cancelled","expired"})
     *     ),
     *     @OA\Parameter(
     *         name="prescription_type",
     *         in="query",
     *         description="Filter by prescription type",
     *         @OA\Schema(type="string", enum={"new","refill","emergency","controlled","compounded","over_the_counter","sample","discharge","maintenance"})
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
     *         @OA\Schema(type="string", enum={"id","issued_at","status","prescription_type","created_at"})
     *     ),
     *     @OA\Parameter(
     *         name="direction",
     *         in="query",
     *         description="Sort direction",
     *         @OA\Schema(type="string", enum={"asc","desc"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Prescriptions retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Prescriptions retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 allOf={
     *                     @OA\Schema(type="object"),
     *                     @OA\Schema(
     *                         @OA\Property(
     *                             property="data",
     *                             type="array",
     *                             @OA\Items(
     *                                 @OA\Property(property="id", type="integer", example=1),
     *                                 @OA\Property(property="patient_id", type="integer", example=1),
     *                                 @OA\Property(property="doctor_id", type="integer", example=1),
     *                                 @OA\Property(property="clinic_id", type="integer", example=1),
     *                                 @OA\Property(property="prescription_type", type="string", example="new"),
     *                                 @OA\Property(property="status", type="string", example="pending"),
     *                                 @OA\Property(property="issued_at", type="string", format="date-time"),
     *                                 @OA\Property(property="patient", type="object"),
     *                                 @OA\Property(property="doctor", type="object"),
     *                                 @OA\Property(property="items", type="array", @OA\Items(type="object")),
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
            $this->requirePermission('prescriptions.view');

            $currentClinic = $this->getCurrentClinic();

            if (!$currentClinic) {
                return $this->errorResponse('No clinic access', null, 403);
            }

            [$perPage, $page] = $this->getPaginationParams($request);
            [$sort, $direction] = $this->getSortingParams($request, [
                'id', 'issued_at', 'status', 'prescription_type', 'created_at'
            ]);

            $query = Prescription::where('clinic_id', $currentClinic->id)
                ->with(['patient', 'doctor.user', 'clinic', 'items']);

            // Filter by status
            if ($request->has('status')) {
                $query->where('status', $request->get('status'));
            }

            // Filter by prescription type
            if ($request->has('prescription_type')) {
                $query->where('prescription_type', $request->get('prescription_type'));
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
                    $query->where('issued_at', '>=', $request->get('date_from'));
                }
                if ($request->has('date_to')) {
                    $query->where('issued_at', '<=', $request->get('date_to'));
                }
            }

            $prescriptions = $query->orderBy($sort, $direction)
                ->paginate($perPage, ['*'], 'page', $page);

            return $this->paginatedResponse($prescriptions, 'Prescriptions retrieved successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/prescriptions",
     *     summary="Create new prescription",
     *     description="Create a new prescription for a patient",
     *     tags={"Prescriptions"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="patient_id", type="integer", example=1, description="Patient ID"),
     *             @OA\Property(property="doctor_id", type="integer", example=1, description="Prescribing doctor ID"),
     *             @OA\Property(property="appointment_id", type="integer", example=1, description="Associated appointment ID"),
     *             @OA\Property(property="encounter_id", type="integer", example=1, description="Associated encounter ID"),
     *             @OA\Property(property="prescription_date", type="string", format="date", example="2024-01-15", description="Date prescription was written"),
     *             @OA\Property(property="valid_until", type="string", format="date", example="2024-02-15", description="Prescription validity end date"),
     *             @OA\Property(property="status", type="string", enum={"draft","pending","verified","dispensed","expired","cancelled"}, example="pending", description="Prescription status"),
     *             @OA\Property(property="notes", type="string", example="Take with food. Monitor blood pressure.", description="Prescription notes"),
     *             @OA\Property(property="pharmacy_notes", type="string", example="Generic substitution allowed", description="Pharmacy-specific notes"),
     *             @OA\Property(property="is_urgent", type="boolean", example=false, description="Whether prescription is urgent"),
     *             @OA\Property(property="refills_allowed", type="integer", example=2, description="Number of refills allowed"),
     *             @OA\Property(property="refills_used", type="integer", example=0, description="Number of refills already used"),
     *             @OA\Property(property="items", type="array", @OA\Items(
     *                 @OA\Property(property="medication_name", type="string", example="Lisinopril 10mg"),
     *                 @OA\Property(property="generic_name", type="string", example="Lisinopril"),
     *                 @OA\Property(property="dosage", type="string", example="10mg"),
     *                 @OA\Property(property="frequency", type="string", example="Once daily"),
     *                 @OA\Property(property="quantity", type="integer", example=30),
     *                 @OA\Property(property="unit", type="string", example="tablets"),
     *                 @OA\Property(property="instructions", type="string", example="Take 1 tablet by mouth once daily"),
     *                 @OA\Property(property="duration", type="string", example="30 days"),
     *                 @OA\Property(property="is_generic_allowed", type="boolean", example=true),
     *                 @OA\Property(property="notes", type="string", example="Take with food")
     *             ), description="Prescription items")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Prescription created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Prescription created successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="prescription", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="patient_id", type="integer", example=1),
     *                     @OA\Property(property="doctor_id", type="integer", example=1),
     *                     @OA\Property(property="prescription_number", type="string", example="RX-2024-001"),
     *                     @OA\Property(property="prescription_date", type="string", format="date", example="2024-01-15"),
     *                     @OA\Property(property="valid_until", type="string", format="date", example="2024-02-15"),
     *                     @OA\Property(property="status", type="string", example="pending"),
     *                     @OA\Property(property="refills_allowed", type="integer", example=2),
     *                     @OA\Property(property="refills_used", type="integer", example=0),
     *                     @OA\Property(property="is_urgent", type="boolean", example=false),
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
            // Permission check is handled by middleware, but we can add additional validation
            $this->requirePermission('prescriptions.create');

            $currentClinic = $this->getCurrentClinic();

            if (!$currentClinic) {
                return $this->errorResponse('No clinic access', null, 403);
            }

            $validator = Validator::make($request->all(), [
                'patient_id' => 'required|integer|exists:patients,id',
                'doctor_id' => 'required|integer|exists:doctors,id',
                'encounter_id' => 'nullable|integer|exists:encounters,id',
                'prescription_type' => 'required|string|in:new,refill,emergency,controlled,compounded,over_the_counter,sample,discharge,maintenance',
                'diagnosis' => 'nullable|string|max:1000',
                'instructions' => 'nullable|string|max:1000',
                'dispense_quantity' => 'nullable|integer|min:1',
                'refills_allowed' => 'nullable|integer|min:0|max:12',
                'expiry_date' => 'nullable|date|after:today',
                'pharmacy_notes' => 'nullable|string|max:500',
                'patient_instructions' => 'nullable|string|max:1000',
                'total_cost' => 'nullable|numeric|min:0',
                'copay_amount' => 'nullable|numeric|min:0',
                'items' => 'required|array|min:1',
                'items.*.medication_name' => 'required|string|max:255',
                'items.*.dosage' => 'required|string|max:255',
                'items.*.frequency' => 'required|string|max:255',
                'items.*.duration' => 'required|string|max:255',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.instructions' => 'nullable|string|max:500',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $data = $validator->validated();
            $data['clinic_id'] = $currentClinic->id;
            $data['status'] = 'draft';
            $data['refills_remaining'] = $data['refills_allowed'] ?? 0;

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

            $prescription = Prescription::create($data);

            // Create prescription items
            foreach ($data['items'] as $itemData) {
                $prescription->items()->create($itemData);
            }

            $prescription->load(['patient', 'doctor.user', 'clinic', 'items']);

            return $this->successResponse([
                'prescription' => $prescription,
            ], 'Prescription created successfully', 201);

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/prescriptions/{prescription}",
     *     summary="Get prescription details",
     *     description="Retrieve detailed information about a specific prescription",
     *     tags={"Prescriptions"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="prescription",
     *         in="path",
     *         description="Prescription ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Prescription details retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Prescription retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="prescription", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="patient_id", type="integer", example=1),
     *                     @OA\Property(property="doctor_id", type="integer", example=1),
     *                     @OA\Property(property="appointment_id", type="integer", example=1),
     *                     @OA\Property(property="encounter_id", type="integer", example=1),
     *                     @OA\Property(property="prescription_number", type="string", example="RX-2024-001"),
     *                     @OA\Property(property="prescription_date", type="string", format="date", example="2024-01-15"),
     *                     @OA\Property(property="valid_until", type="string", format="date", example="2024-02-15"),
     *                     @OA\Property(property="status", type="string", example="verified"),
     *                     @OA\Property(property="notes", type="string", example="Take with food. Monitor blood pressure."),
     *                     @OA\Property(property="pharmacy_notes", type="string", example="Generic substitution allowed"),
     *                     @OA\Property(property="is_urgent", type="boolean", example=false),
     *                     @OA\Property(property="refills_allowed", type="integer", example=2),
     *                     @OA\Property(property="refills_used", type="integer", example=0),
     *                     @OA\Property(property="is_verified", type="boolean", example=true),
     *                     @OA\Property(property="verified_by", type="integer", example=1),
     *                     @OA\Property(property="verified_at", type="string", format="date-time"),
     *                     @OA\Property(property="is_dispensed", type="boolean", example=false),
     *                     @OA\Property(property="dispensed_at", type="string", format="date-time"),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time")
     *                 ),
     *                 @OA\Property(property="patient", type="object", nullable=true),
     *                 @OA\Property(property="doctor", type="object", nullable=true),
     *                 @OA\Property(property="appointment", type="object", nullable=true),
     *                 @OA\Property(property="encounter", type="object", nullable=true),
     *                 @OA\Property(
     *                     property="items",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="medication_name", type="string", example="Lisinopril 10mg"),
     *                         @OA\Property(property="generic_name", type="string", example="Lisinopril"),
     *                         @OA\Property(property="dosage", type="string", example="10mg"),
     *                         @OA\Property(property="frequency", type="string", example="Once daily"),
     *                         @OA\Property(property="quantity", type="integer", example=30),
     *                         @OA\Property(property="unit", type="string", example="tablets"),
     *                         @OA\Property(property="instructions", type="string", example="Take 1 tablet by mouth once daily"),
     *                         @OA\Property(property="duration", type="string", example="30 days"),
     *                         @OA\Property(property="is_generic_allowed", type="boolean", example=true),
     *                         @OA\Property(property="notes", type="string", example="Take with food")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="No access to this prescription",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Prescription not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function show(Prescription $prescription): JsonResponse
    {
        try {
            if (!$this->hasClinicAccess($prescription->clinic_id)) {
                return $this->forbiddenResponse('No access to this prescription');
            }

            $prescription->load(['patient', 'doctor.user', 'clinic', 'items', 'encounter']);

            return $this->successResponse([
                'prescription' => $prescription,
                'statistics' => $prescription->statistics,
                'timeline' => $prescription->timeline,
                'warnings' => $prescription->warnings,
            ], 'Prescription retrieved successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Update the specified prescription
     */
    public function update(Request $request, Prescription $prescription): JsonResponse
    {
        try {
            if (!$this->hasClinicAccess($prescription->clinic_id)) {
                return $this->forbiddenResponse('No access to this prescription');
            }

            $validator = Validator::make($request->all(), [
                'patient_id' => 'sometimes|required|integer|exists:patients,id',
                'doctor_id' => 'sometimes|required|integer|exists:doctors,id',
                'encounter_id' => 'nullable|integer|exists:encounters,id',
                'prescription_type' => 'sometimes|required|string|in:new,refill,emergency,controlled,compounded,over_the_counter,sample,discharge,maintenance',
                'diagnosis' => 'nullable|string|max:1000',
                'instructions' => 'nullable|string|max:1000',
                'dispense_quantity' => 'nullable|integer|min:1',
                'refills_allowed' => 'nullable|integer|min:0|max:12',
                'expiry_date' => 'nullable|date|after:today',
                'pharmacy_notes' => 'nullable|string|max:500',
                'patient_instructions' => 'nullable|string|max:1000',
                'status' => 'sometimes|required|string|in:draft,active,dispensed,expired,cancelled,suspended,completed,pending_verification,verified,rejected',
                'total_cost' => 'nullable|numeric|min:0',
                'copay_amount' => 'nullable|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $prescription->update($validator->validated());
            $prescription->load(['patient', 'doctor.user', 'clinic', 'items']);

            return $this->successResponse([
                'prescription' => $prescription,
            ], 'Prescription updated successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Remove the specified prescription
     */
    public function destroy(Prescription $prescription): JsonResponse
    {
        try {
            if (!$this->hasClinicAccess($prescription->clinic_id)) {
                return $this->forbiddenResponse('No access to this prescription');
            }

            // Check if prescription can be deleted
            if (in_array($prescription->status, ['dispensed', 'verified'])) {
                return $this->errorResponse('Cannot delete dispensed or verified prescriptions', null, 422);
            }

            $prescription->delete();

            return $this->successResponse(null, 'Prescription deleted successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/prescriptions/{prescription}/items",
     *     summary="Get prescription items",
     *     description="Retrieve all items for a specific prescription",
     *     tags={"Prescriptions"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="prescription",
     *         in="path",
     *         description="Prescription ID",
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
     *     @OA\Response(
     *         response=200,
     *         description="Prescription items retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Prescription items retrieved successfully"),
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
     *                                 @OA\Property(property="prescription_id", type="integer", example=1),
     *                                 @OA\Property(property="medication_name", type="string", example="Lisinopril 10mg"),
     *                                 @OA\Property(property="generic_name", type="string", example="Lisinopril"),
     *                                 @OA\Property(property="dosage", type="string", example="10mg"),
     *                                 @OA\Property(property="frequency", type="string", example="Once daily"),
     *                                 @OA\Property(property="quantity", type="integer", example=30),
     *                                 @OA\Property(property="unit", type="string", example="tablets"),
     *                                 @OA\Property(property="instructions", type="string", example="Take 1 tablet by mouth once daily"),
     *                                 @OA\Property(property="duration", type="string", example="30 days"),
     *                                 @OA\Property(property="is_generic_allowed", type="boolean", example=true),
     *                                 @OA\Property(property="notes", type="string", example="Take with food"),
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
     *         description="No access to this prescription",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Prescription not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function items(Prescription $prescription): JsonResponse
    {
        try {
            if (!$this->hasClinicAccess($prescription->clinic_id)) {
                return $this->forbiddenResponse('No access to this prescription');
            }

            $items = $prescription->items;

            return $this->successResponse([
                'items' => $items,
            ], 'Prescription items retrieved');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/prescriptions/{prescription}/items",
     *     summary="Add prescription item",
     *     description="Add a new medication item to an existing prescription",
     *     tags={"Prescriptions"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="prescription",
     *         in="path",
     *         description="Prescription ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="medication_name", type="string", example="Metformin 500mg", description="Brand name of medication"),
     *             @OA\Property(property="generic_name", type="string", example="Metformin", description="Generic name of medication"),
     *             @OA\Property(property="dosage", type="string", example="500mg", description="Medication dosage"),
     *             @OA\Property(property="frequency", type="string", example="Twice daily", description="How often to take medication"),
     *             @OA\Property(property="quantity", type="integer", example=60, description="Quantity to dispense"),
     *             @OA\Property(property="unit", type="string", example="tablets", description="Unit of measurement"),
     *             @OA\Property(property="instructions", type="string", example="Take 1 tablet by mouth twice daily with meals", description="Detailed instructions"),
     *             @OA\Property(property="duration", type="string", example="30 days", description="Duration of treatment"),
     *             @OA\Property(property="is_generic_allowed", type="boolean", example=true, description="Whether generic substitution is allowed"),
     *             @OA\Property(property="notes", type="string", example="Take with food to reduce stomach upset", description="Additional notes")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Prescription item added successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Prescription item added successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="item", type="object",
     *                     @OA\Property(property="id", type="integer", example=2),
     *                     @OA\Property(property="prescription_id", type="integer", example=1),
     *                     @OA\Property(property="medication_name", type="string", example="Metformin 500mg"),
     *                     @OA\Property(property="generic_name", type="string", example="Metformin"),
     *                     @OA\Property(property="dosage", type="string", example="500mg"),
     *                     @OA\Property(property="frequency", type="string", example="Twice daily"),
     *                     @OA\Property(property="quantity", type="integer", example=60),
     *                     @OA\Property(property="unit", type="string", example="tablets"),
     *                     @OA\Property(property="instructions", type="string", example="Take 1 tablet by mouth twice daily with meals"),
     *                     @OA\Property(property="duration", type="string", example="30 days"),
     *                     @OA\Property(property="is_generic_allowed", type="boolean", example=true),
     *                     @OA\Property(property="notes", type="string", example="Take with food to reduce stomach upset"),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="No access to this prescription",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Prescription not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function addItem(Request $request, Prescription $prescription): JsonResponse
    {
        try {
            if (!$this->hasClinicAccess($prescription->clinic_id)) {
                return $this->forbiddenResponse('No access to this prescription');
            }

            $validator = Validator::make($request->all(), [
                'medication_name' => 'required|string|max:255',
                'dosage' => 'required|string|max:255',
                'frequency' => 'required|string|max:255',
                'duration' => 'required|string|max:255',
                'quantity' => 'required|integer|min:1',
                'instructions' => 'nullable|string|max:500',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $item = $prescription->items()->create($validator->validated());

            return $this->successResponse([
                'item' => $item,
            ], 'Item added to prescription successfully', 201);

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Update prescription item
     */
    public function updateItem(Request $request, Prescription $prescription, PrescriptionItem $item): JsonResponse
    {
        try {
            if (!$this->hasClinicAccess($prescription->clinic_id)) {
                return $this->forbiddenResponse('No access to this prescription');
            }

            if ($item->prescription_id !== $prescription->id) {
                return $this->errorResponse('Item does not belong to this prescription', null, 422);
            }

            $validator = Validator::make($request->all(), [
                'medication_name' => 'sometimes|required|string|max:255',
                'dosage' => 'sometimes|required|string|max:255',
                'frequency' => 'sometimes|required|string|max:255',
                'duration' => 'sometimes|required|string|max:255',
                'quantity' => 'sometimes|required|integer|min:1',
                'instructions' => 'nullable|string|max:500',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $item->update($validator->validated());

            return $this->successResponse([
                'item' => $item,
            ], 'Item updated successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Remove item from prescription
     */
    public function removeItem(Prescription $prescription, PrescriptionItem $item): JsonResponse
    {
        try {
            if (!$this->hasClinicAccess($prescription->clinic_id)) {
                return $this->forbiddenResponse('No access to this prescription');
            }

            if ($item->prescription_id !== $prescription->id) {
                return $this->errorResponse('Item does not belong to this prescription', null, 422);
            }

            $item->delete();

            return $this->successResponse(null, 'Item removed from prescription successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/prescriptions/{prescription}/verify",
     *     summary="Verify prescription",
     *     description="Verify a prescription for dispensing",
     *     tags={"Prescriptions"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="prescription",
     *         in="path",
     *         description="Prescription ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="verified_by_doctor_id", type="integer", example=1, description="ID of doctor verifying the prescription"),
     *             @OA\Property(property="verification_notes", type="string", example="Prescription verified. All medications appropriate for patient condition.", description="Verification notes"),
     *             @OA\Property(property="status", type="string", enum={"verified","rejected"}, example="verified", description="Verification status")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Prescription verified successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Prescription verified successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="prescription", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="patient_id", type="integer", example=1),
     *                     @OA\Property(property="doctor_id", type="integer", example=1),
     *                     @OA\Property(property="prescription_number", type="string", example="RX-2024-001"),
     *                     @OA\Property(property="status", type="string", example="verified"),
     *                     @OA\Property(property="is_verified", type="boolean", example=true),
     *                     @OA\Property(property="verified_by", type="integer", example=1),
     *                     @OA\Property(property="verified_at", type="string", format="date-time"),
     *                     @OA\Property(property="verification_notes", type="string", example="Prescription verified. All medications appropriate for patient condition."),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="No access to this prescription",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Prescription not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function verify(Request $request, Prescription $prescription): JsonResponse
    {
        try {
            if (!$this->hasClinicAccess($prescription->clinic_id)) {
                return $this->forbiddenResponse('No access to this prescription');
            }

            $validator = Validator::make($request->all(), [
                'status' => 'required|boolean',
                'notes' => 'nullable|string|max:500',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $prescription->verify($request->status, $request->notes);
            $prescription->load(['patient', 'doctor.user', 'clinic', 'items']);

            return $this->successResponse([
                'prescription' => $prescription,
            ], 'Prescription verification updated successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/prescriptions/{prescription}/dispense",
     *     summary="Dispense prescription",
     *     description="Mark a prescription as dispensed",
     *     tags={"Prescriptions"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="prescription",
     *         in="path",
     *         description="Prescription ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="dispensed_by", type="string", example="Dr. Jane Smith", description="Name of person dispensing"),
     *             @OA\Property(property="dispensed_at", type="string", format="date-time", example="2024-01-15T14:30:00Z", description="Dispensing timestamp"),
     *             @OA\Property(property="pharmacy_name", type="string", example="Central Pharmacy", description="Pharmacy name"),
     *             @OA\Property(property="dispensing_notes", type="string", example="Patient counseled on medication use", description="Dispensing notes")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Prescription dispensed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Prescription dispensed successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="prescription", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="patient_id", type="integer", example=1),
     *                     @OA\Property(property="doctor_id", type="integer", example=1),
     *                     @OA\Property(property="prescription_number", type="string", example="RX-2024-001"),
     *                     @OA\Property(property="status", type="string", example="dispensed"),
     *                     @OA\Property(property="is_dispensed", type="boolean", example=true),
     *                     @OA\Property(property="dispensed_at", type="string", format="date-time"),
     *                     @OA\Property(property="dispensed_by", type="string", example="Dr. Jane Smith"),
     *                     @OA\Property(property="pharmacy_name", type="string", example="Central Pharmacy"),
     *                     @OA\Property(property="dispensing_notes", type="string", example="Patient counseled on medication use"),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="No access to this prescription",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Prescription not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Prescription not verified or already dispensed",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function dispense(Prescription $prescription): JsonResponse
    {
        try {
            if (!$this->hasClinicAccess($prescription->clinic_id)) {
                return $this->forbiddenResponse('No access to this prescription');
            }

            if ($prescription->status !== 'active') {
                return $this->errorResponse('Only active prescriptions can be dispensed', null, 422);
            }

            $prescription->markAsDispensed();
            $prescription->load(['patient', 'doctor.user', 'clinic', 'items']);

            return $this->successResponse([
                'prescription' => $prescription,
            ], 'Prescription marked as dispensed successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/prescriptions/{prescription}/refill",
     *     summary="Refill prescription",
     *     description="Process a prescription refill",
     *     tags={"Prescriptions"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="prescription",
     *         in="path",
     *         description="Prescription ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="refill_quantity", type="integer", example=30, description="Quantity for this refill"),
     *             @OA\Property(property="refill_notes", type="string", example="Patient requested early refill due to travel", description="Refill notes"),
     *             @OA\Property(property="refilled_by", type="string", example="Dr. John Smith", description="Name of person processing refill")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Prescription refilled successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Prescription refilled successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="prescription", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="patient_id", type="integer", example=1),
     *                     @OA\Property(property="doctor_id", type="integer", example=1),
     *                     @OA\Property(property="prescription_number", type="string", example="RX-2024-001"),
     *                     @OA\Property(property="refills_allowed", type="integer", example=2),
     *                     @OA\Property(property="refills_used", type="integer", example=1),
     *                     @OA\Property(property="refills_remaining", type="integer", example=1),
     *                     @OA\Property(property="last_refill_date", type="string", format="date-time"),
     *                     @OA\Property(property="refill_notes", type="string", example="Patient requested early refill due to travel"),
     *                     @OA\Property(property="refilled_by", type="string", example="Dr. John Smith"),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time")
     *                 ),
     *                 @OA\Property(
     *                     property="refill_history",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="refill_number", type="integer", example=1),
     *                         @OA\Property(property="refill_date", type="string", format="date-time"),
     *                         @OA\Property(property="quantity", type="integer", example=30),
     *                         @OA\Property(property="refilled_by", type="string", example="Dr. John Smith"),
     *                         @OA\Property(property="notes", type="string", example="Patient requested early refill due to travel")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="No access to this prescription",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Prescription not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="No refills remaining or prescription expired",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function refill(Prescription $prescription): JsonResponse
    {
        try {
            if (!$this->hasClinicAccess($prescription->clinic_id)) {
                return $this->forbiddenResponse('No access to this prescription');
            }

            if (!$prescription->canBeRefilled()) {
                return $this->errorResponse('Prescription cannot be refilled', null, 422);
            }

            $success = $prescription->processRefill();

            if (!$success) {
                return $this->errorResponse('Failed to process refill', null, 422);
            }

            $prescription->load(['patient', 'doctor.user', 'clinic', 'items']);

            return $this->successResponse([
                'prescription' => $prescription,
            ], 'Refill processed successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/prescriptions/{prescription}/pdf",
     *     summary="Download prescription PDF",
     *     description="Generate and download a PDF copy of the prescription",
     *     tags={"Prescriptions"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="prescription",
     *         in="path",
     *         description="Prescription ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="format",
     *         in="query",
     *         description="PDF format",
     *         @OA\Schema(type="string", enum={"standard","detailed","pharmacy"}, example="standard")
     *     ),
     *     @OA\Parameter(
     *         name="include_signature",
     *         in="query",
     *         description="Include doctor signature",
     *         @OA\Schema(type="boolean", example=true)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Prescription PDF generated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Prescription PDF generated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="pdf_url", type="string", example="/api/v1/prescriptions/1/pdf/download"),
     *                 @OA\Property(property="filename", type="string", example="prescription_RX-2024-001_20240115.pdf"),
     *                 @OA\Property(property="file_size", type="integer", example=156789),
     *                 @OA\Property(property="generated_at", type="string", format="date-time", example="2024-01-15T10:00:00Z"),
     *                 @OA\Property(property="expires_at", type="string", format="date-time", example="2024-01-15T11:00:00Z"),
     *                 @OA\Property(property="format", type="string", example="standard"),
     *                 @OA\Property(property="pages", type="integer", example=1),
     *                 @OA\Property(property="includes_signature", type="boolean", example=true)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="No access to this prescription",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Prescription not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function downloadPdf(Prescription $prescription): JsonResponse
    {
        try {
            if (!$this->hasClinicAccess($prescription->clinic_id)) {
                return $this->forbiddenResponse('No access to this prescription');
            }

            // In a real implementation, you would generate and return the PDF
            // For now, return the download URL
            $downloadUrl = $prescription->pdf_download_url;

            return $this->successResponse([
                'download_url' => $downloadUrl,
                'prescription_number' => $prescription->prescription_number,
            ], 'PDF download URL generated');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/prescriptions/{prescription}/qr",
     *     summary="Get prescription QR code",
     *     description="Generate a QR code for the prescription",
     *     tags={"Prescriptions"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="prescription",
     *         in="path",
     *         description="Prescription ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="size",
     *         in="query",
     *         description="QR code size",
     *         @OA\Schema(type="string", enum={"small","medium","large"}, example="medium")
     *     ),
     *     @OA\Parameter(
     *         name="format",
     *         in="query",
     *         description="QR code format",
     *         @OA\Schema(type="string", enum={"png","svg","base64"}, example="png")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Prescription QR code generated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Prescription QR code generated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="qr_code_url", type="string", example="/api/v1/prescriptions/1/qr/image"),
     *                 @OA\Property(property="qr_code_data", type="string", example="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAA...", description="Base64 encoded QR code image"),
     *                 @OA\Property(property="qr_code_text", type="string", example="RX-2024-001|Patient:John Doe|Doctor:Dr. Smith|Date:2024-01-15", description="QR code text content"),
     *                 @OA\Property(property="size", type="string", example="medium"),
     *                 @OA\Property(property="format", type="string", example="png"),
     *                 @OA\Property(property="dimensions", type="object",
     *                     @OA\Property(property="width", type="integer", example=256),
     *                     @OA\Property(property="height", type="integer", example=256)
     *                 ),
     *                 @OA\Property(property="expires_at", type="string", format="date-time", example="2024-01-15T11:00:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="No access to this prescription",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Prescription not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function qrCode(Prescription $prescription): JsonResponse
    {
        try {
            if (!$this->hasClinicAccess($prescription->clinic_id)) {
                return $this->forbiddenResponse('No access to this prescription');
            }

            $qrData = $prescription->qr_code_data;

            return $this->successResponse([
                'qr_data' => $qrData,
                'qr_hash' => $prescription->qr_hash,
            ], 'QR code data retrieved');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/prescriptions/active",
     *     summary="Get active prescriptions",
     *     description="Retrieve all active prescriptions for the clinic",
     *     tags={"Prescriptions"},
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
     *         name="patient_id",
     *         in="query",
     *         description="Filter by patient",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="doctor_id",
     *         in="query",
     *         description="Filter by doctor",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="medication_name",
     *         in="query",
     *         description="Filter by medication name",
     *         @OA\Schema(type="string", example="Lisinopril")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Active prescriptions retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Active prescriptions retrieved successfully"),
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
     *                                 @OA\Property(property="prescription_number", type="string", example="RX-2024-001"),
     *                                 @OA\Property(property="prescription_date", type="string", format="date", example="2024-01-15"),
     *                                 @OA\Property(property="valid_until", type="string", format="date", example="2024-02-15"),
     *                                 @OA\Property(property="status", type="string", example="verified"),
     *                                 @OA\Property(property="refills_allowed", type="integer", example=2),
     *                                 @OA\Property(property="refills_used", type="integer", example=0),
     *                                 @OA\Property(property="refills_remaining", type="integer", example=2),
     *                                 @OA\Property(property="is_urgent", type="boolean", example=false),
     *                                 @OA\Property(property="is_verified", type="boolean", example=true),
     *                                 @OA\Property(property="is_dispensed", type="boolean", example=false),
     *                                 @OA\Property(property="patient", type="object", nullable=true),
     *                                 @OA\Property(property="doctor", type="object", nullable=true),
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
     *         description="No clinic access",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function active(Request $request): JsonResponse
    {
        try {
            $currentClinic = $this->getCurrentClinic();

            if (!$currentClinic) {
                return $this->errorResponse('No clinic access', null, 403);
            }

            [$perPage, $page] = $this->getPaginationParams($request);

            $prescriptions = Prescription::where('clinic_id', $currentClinic->id)
                ->active()
                ->with(['patient', 'doctor.user', 'clinic', 'items'])
                ->paginate($perPage, ['*'], 'page', $page);

            return $this->paginatedResponse($prescriptions, 'Active prescriptions retrieved');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/prescriptions/expired",
     *     summary="Get expired prescriptions",
     *     description="Retrieve all expired prescriptions for the clinic",
     *     tags={"Prescriptions"},
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
     *         name="patient_id",
     *         in="query",
     *         description="Filter by patient",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="doctor_id",
     *         in="query",
     *         description="Filter by doctor",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="expired_days",
     *         in="query",
     *         description="Filter by days expired",
     *         @OA\Schema(type="integer", example=30)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Expired prescriptions retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Expired prescriptions retrieved successfully"),
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
     *                                 @OA\Property(property="prescription_number", type="string", example="RX-2024-001"),
     *                                 @OA\Property(property="prescription_date", type="string", format="date", example="2024-01-15"),
     *                                 @OA\Property(property="valid_until", type="string", format="date", example="2024-02-15"),
     *                                 @OA\Property(property="status", type="string", example="expired"),
     *                                 @OA\Property(property="refills_allowed", type="integer", example=2),
     *                                 @OA\Property(property="refills_used", type="integer", example=1),
     *                                 @OA\Property(property="refills_remaining", type="integer", example=1),
     *                                 @OA\Property(property="is_urgent", type="boolean", example=false),
     *                                 @OA\Property(property="is_verified", type="boolean", example=true),
     *                                 @OA\Property(property="is_dispensed", type="boolean", example=true),
     *                                 @OA\Property(property="days_expired", type="integer", example=15),
     *                                 @OA\Property(property="patient", type="object", nullable=true),
     *                                 @OA\Property(property="doctor", type="object", nullable=true),
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
     *         description="No clinic access",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function expired(Request $request): JsonResponse
    {
        try {
            $currentClinic = $this->getCurrentClinic();

            if (!$currentClinic) {
                return $this->errorResponse('No clinic access', null, 403);
            }

            [$perPage, $page] = $this->getPaginationParams($request);

            $prescriptions = Prescription::where('clinic_id', $currentClinic->id)
                ->expired()
                ->with(['patient', 'doctor.user', 'clinic', 'items'])
                ->paginate($perPage, ['*'], 'page', $page);

            return $this->paginatedResponse($prescriptions, 'Expired prescriptions retrieved');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/prescriptions/needs-refill",
     *     summary="Get prescriptions needing refill",
     *     description="Retrieve prescriptions that need refills or are running low",
     *     tags={"Prescriptions"},
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
     *         name="patient_id",
     *         in="query",
     *         description="Filter by patient",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="doctor_id",
     *         in="query",
     *         description="Filter by doctor",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="urgency",
     *         in="query",
     *         description="Filter by urgency level",
     *         @OA\Schema(type="string", enum={"low","medium","high","critical"}, example="medium")
     *     ),
     *     @OA\Parameter(
     *         name="days_until_empty",
     *         in="query",
     *         description="Filter by days until medication runs out",
     *         @OA\Schema(type="integer", example=7)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Prescriptions needing refill retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Prescriptions needing refill retrieved successfully"),
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
     *                                 @OA\Property(property="prescription_number", type="string", example="RX-2024-001"),
     *                                 @OA\Property(property="prescription_date", type="string", format="date", example="2024-01-15"),
     *                                 @OA\Property(property="valid_until", type="string", format="date", example="2024-02-15"),
     *                                 @OA\Property(property="status", type="string", example="verified"),
     *                                 @OA\Property(property="refills_allowed", type="integer", example=2),
     *                                 @OA\Property(property="refills_used", type="integer", example=0),
     *                                 @OA\Property(property="refills_remaining", type="integer", example=2),
     *                                 @OA\Property(property="is_urgent", type="boolean", example=false),
     *                                 @OA\Property(property="is_verified", type="boolean", example=true),
     *                                 @OA\Property(property="is_dispensed", type="boolean", example=true),
     *                                 @OA\Property(property="urgency_level", type="string", example="medium"),
     *                                 @OA\Property(property="days_until_empty", type="integer", example=5),
     *                                 @OA\Property(property="last_dispensed", type="string", format="date", example="2024-01-15"),
     *                                 @OA\Property(property="estimated_empty_date", type="string", format="date", example="2024-01-20"),
     *                                 @OA\Property(property="patient", type="object", nullable=true),
     *                                 @OA\Property(property="doctor", type="object", nullable=true),
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
     *         description="No clinic access",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function needsRefill(Request $request): JsonResponse
    {
        try {
            $currentClinic = $this->getCurrentClinic();

            if (!$currentClinic) {
                return $this->errorResponse('No clinic access', null, 403);
            }

            [$perPage, $page] = $this->getPaginationParams($request);

            $prescriptions = Prescription::where('clinic_id', $currentClinic->id)
                ->needsRefill()
                ->with(['patient', 'doctor.user', 'clinic', 'items'])
                ->paginate($perPage, ['*'], 'page', $page);

            return $this->paginatedResponse($prescriptions, 'Prescriptions needing refill retrieved');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Search prescriptions
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

            $prescriptions = Prescription::where('clinic_id', $currentClinic->id)
                ->where('prescription_number', 'like', "%{$query}%")
                ->orWhereHas('patient', function ($q) use ($query) {
                    $q->where('first_name', 'like', "%{$query}%")
                      ->orWhere('last_name', 'like', "%{$query}%")
                      ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$query}%"]);
                })
                ->orWhereHas('doctor.user', function ($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%");
                })
                ->with(['patient', 'doctor.user', 'clinic', 'items'])
                ->limit($limit)
                ->get();

            return $this->successResponse([
                'prescriptions' => $prescriptions,
            ], 'Search results retrieved');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Get prescription reports
     */
    public function reports(Request $request): JsonResponse
    {
        try {
            $currentClinic = $this->getCurrentClinic();

            if (!$currentClinic) {
                return $this->errorResponse('No clinic access', null, 403);
            }

            $reports = [
                'total_prescriptions' => Prescription::where('clinic_id', $currentClinic->id)->count(),
                'active_prescriptions' => Prescription::where('clinic_id', $currentClinic->id)->where('status', 'active')->count(),
                'expired_prescriptions' => Prescription::where('clinic_id', $currentClinic->id)->where('status', 'expired')->count(),
                'prescriptions_this_month' => Prescription::where('clinic_id', $currentClinic->id)
                    ->where('created_at', '>=', now()->startOfMonth())
                    ->count(),
                'prescriptions_by_type' => Prescription::where('clinic_id', $currentClinic->id)
                    ->selectRaw('prescription_type, COUNT(*) as count')
                    ->groupBy('prescription_type')
                    ->get(),
                'prescriptions_by_status' => Prescription::where('clinic_id', $currentClinic->id)
                    ->selectRaw('status, COUNT(*) as count')
                    ->groupBy('status')
                    ->get(),
            ];

            return $this->successResponse([
                'reports' => $reports,
            ], 'Prescription reports retrieved');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Export prescriptions
     */
    public function export(Request $request): JsonResponse
    {
        try {
            $currentClinic = $this->getCurrentClinic();

            if (!$currentClinic) {
                return $this->errorResponse('No clinic access', null, 403);
            }

            $prescriptions = Prescription::where('clinic_id', $currentClinic->id)
                ->with(['patient', 'doctor.user', 'clinic', 'items'])
                ->get();

            return $this->successResponse([
                'prescriptions' => $prescriptions,
                'export_url' => null, // Would be a download URL in real implementation
            ], 'Export data prepared');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
}
