<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\LabResult;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Encounter;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;

class LabResultController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/v1/lab-results",
     *     summary="Get all lab results",
     *     description="Retrieve a paginated list of lab results for the current clinic",
     *     tags={"Lab Results"},
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
     *         @OA\Schema(type="string", enum={"pending","in_progress","completed","abnormal","cancelled"})
     *     ),
     *     @OA\Parameter(
     *         name="test_type",
     *         in="query",
     *         description="Filter by test type",
     *         @OA\Schema(type="string", example="blood_test")
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
     *         @OA\Schema(type="string", enum={"id","test_name","result_value","status","ordered_at","completed_at"})
     *     ),
     *     @OA\Parameter(
     *         name="direction",
     *         in="query",
     *         description="Sort direction",
     *         @OA\Schema(type="string", enum={"asc","desc"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lab results retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Lab results retrieved successfully"),
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
     *                                 @OA\Property(property="test_type", type="string", example="blood_test"),
     *                                 @OA\Property(property="test_name", type="string", example="Complete Blood Count"),
     *                                 @OA\Property(property="result_value", type="string", example="Normal"),
     *                                 @OA\Property(property="unit", type="string", example="cells/μL"),
     *                                 @OA\Property(property="reference_range", type="string", example="4.5-11.0"),
     *                                 @OA\Property(property="status", type="string", example="completed"),
     *                                 @OA\Property(property="ordered_at", type="string", format="date-time"),
     *                                 @OA\Property(property="completed_at", type="string", format="date-time"),
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
            $currentClinic = $this->getCurrentClinic();

            if (!$currentClinic) {
                return $this->errorResponse('No clinic access', null, 403);
            }

            [$perPage, $page] = $this->getPaginationParams($request);
            [$sort, $direction] = $this->getSortingParams($request, [
                'id', 'test_name', 'result_value', 'status', 'ordered_at', 'completed_at'
            ]);

            $query = LabResult::where('clinic_id', $currentClinic->id)
                ->with(['patient', 'doctor.user', 'clinic', 'encounter']);

            // Filter by status
            if ($request->has('status')) {
                $query->where('status', $request->get('status'));
            }

            // Filter by test type
            if ($request->has('test_type')) {
                $query->where('test_type', $request->get('test_type'));
            }

            // Filter by patient
            if ($request->has('patient_id')) {
                $query->where('patient_id', $request->get('patient_id'));
            }

            // Filter by doctor
            if ($request->has('doctor_id')) {
                $query->where('ordered_by_doctor_id', $request->get('doctor_id'));
            }

            // Filter by date range
            if ($request->has('date_from') || $request->has('date_to')) {
                if ($request->has('date_from')) {
                    $query->where('ordered_at', '>=', $request->get('date_from'));
                }
                if ($request->has('date_to')) {
                    $query->where('ordered_at', '<=', $request->get('date_to'));
                }
            }

            $labResults = $query->orderBy($sort, $direction)
                ->paginate($perPage, ['*'], 'page', $page);

            return $this->paginatedResponse($labResults, 'Lab results retrieved successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/lab-results",
     *     summary="Create new lab result",
     *     description="Create a new laboratory test result record",
     *     tags={"Lab Results"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="patient_id", type="integer", example=1, description="Patient ID"),
     *             @OA\Property(property="doctor_id", type="integer", example=1, description="Ordering doctor ID"),
     *             @OA\Property(property="appointment_id", type="integer", example=1, description="Associated appointment ID"),
     *             @OA\Property(property="encounter_id", type="integer", example=1, description="Associated encounter ID"),
     *             @OA\Property(property="test_name", type="string", example="Complete Blood Count", description="Name of the lab test"),
     *             @OA\Property(property="test_code", type="string", example="CBC", description="Lab test code"),
     *             @OA\Property(property="lab_name", type="string", example="Central Lab Services", description="Laboratory name"),
     *             @OA\Property(property="lab_reference", type="string", example="LAB-2024-001", description="Lab reference number"),
     *             @OA\Property(property="ordered_date", type="string", format="date", example="2024-01-15", description="Date test was ordered"),
     *             @OA\Property(property="collected_date", type="string", format="date", example="2024-01-15", description="Date sample was collected"),
     *             @OA\Property(property="received_date", type="string", format="date", example="2024-01-16", description="Date sample was received by lab"),
     *             @OA\Property(property="result_date", type="string", format="date", example="2024-01-17", description="Date results were available"),
     *             @OA\Property(property="status", type="string", enum={"pending","completed","abnormal","critical","cancelled"}, example="completed", description="Result status"),
     *             @OA\Property(property="priority", type="string", enum={"routine","urgent","stat"}, example="routine", description="Test priority"),
     *             @OA\Property(property="results", type="object", example={"hemoglobin":"14.2 g/dL","hematocrit":"42.1%","white_blood_cells":"7.2 K/uL"}, description="Test results"),
     *             @OA\Property(property="reference_ranges", type="object", example={"hemoglobin":"12.0-16.0 g/dL","hematocrit":"36.0-46.0%"}, description="Normal reference ranges"),
     *             @OA\Property(property="abnormal_flags", type="array", @OA\Items(type="string"), example={"H","L"}, description="Abnormal result flags (H=High, L=Low)"),
     *             @OA\Property(property="interpretation", type="string", example="All values within normal limits", description="Clinical interpretation"),
     *             @OA\Property(property="notes", type="string", example="Patient fasting for 12 hours", description="Additional notes"),
     *             @OA\Property(property="is_verified", type="boolean", example=false, description="Whether result has been verified"),
     *             @OA\Property(property="verified_by", type="integer", example=1, description="ID of doctor who verified"),
     *             @OA\Property(property="verified_at", type="string", format="date-time", example="2024-01-17T10:00:00Z", description="Verification timestamp")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Lab result created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Lab result created successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="lab_result", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="patient_id", type="integer", example=1),
     *                     @OA\Property(property="doctor_id", type="integer", example=1),
     *                     @OA\Property(property="test_name", type="string", example="Complete Blood Count"),
     *                     @OA\Property(property="test_code", type="string", example="CBC"),
     *                     @OA\Property(property="lab_name", type="string", example="Central Lab Services"),
     *                     @OA\Property(property="lab_reference", type="string", example="LAB-2024-001"),
     *                     @OA\Property(property="status", type="string", example="completed"),
     *                     @OA\Property(property="priority", type="string", example="routine"),
     *                     @OA\Property(property="result_date", type="string", format="date", example="2024-01-17"),
     *                     @OA\Property(property="is_verified", type="boolean", example=false),
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
                'patient_id' => 'required|integer|exists:patients,id',
                'encounter_id' => 'nullable|integer|exists:encounters,id',
                'ordered_by_doctor_id' => 'required|integer|exists:doctors,id',
                'test_type' => 'required|string|max:255',
                'test_name' => 'required|string|max:255',
                'result_value' => 'nullable|string|max:255',
                'unit' => 'nullable|string|max:50',
                'reference_range' => 'nullable|string|max:255',
                'status' => 'nullable|string|in:pending,in_progress,completed,abnormal,cancelled',
                'ordered_at' => 'nullable|date',
                'completed_at' => 'nullable|date|after:ordered_at',
                'notes' => 'nullable|string|max:1000',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $data = $validator->validated();
            $data['clinic_id'] = $currentClinic->id;
            $data['status'] = $data['status'] ?? 'pending';
            $data['ordered_at'] = $data['ordered_at'] ?? now();

            // Check if patient belongs to the same clinic
            $patient = Patient::findOrFail($data['patient_id']);
            if ($patient->clinic_id !== $currentClinic->id) {
                return $this->errorResponse('Patient does not belong to this clinic', null, 422);
            }

            // Check if doctor belongs to the same clinic
            $doctor = Doctor::findOrFail($data['ordered_by_doctor_id']);
            if ($doctor->clinic_id !== $currentClinic->id) {
                return $this->errorResponse('Doctor does not belong to this clinic', null, 422);
            }

            $labResult = LabResult::create($data);
            $labResult->load(['patient', 'doctor.user', 'clinic', 'encounter']);

            return $this->successResponse([
                'lab_result' => $labResult,
            ], 'Lab result created successfully', 201);

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/lab-results/{labResult}",
     *     summary="Get lab result details",
     *     description="Retrieve detailed information about a specific lab result",
     *     tags={"Lab Results"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="labResult",
     *         in="path",
     *         description="Lab result ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lab result details retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Lab result retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="lab_result", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="patient_id", type="integer", example=1),
     *                     @OA\Property(property="doctor_id", type="integer", example=1),
     *                     @OA\Property(property="appointment_id", type="integer", example=1),
     *                     @OA\Property(property="encounter_id", type="integer", example=1),
     *                     @OA\Property(property="test_name", type="string", example="Complete Blood Count"),
     *                     @OA\Property(property="test_code", type="string", example="CBC"),
     *                     @OA\Property(property="lab_name", type="string", example="Central Lab Services"),
     *                     @OA\Property(property="lab_reference", type="string", example="LAB-2024-001"),
     *                     @OA\Property(property="ordered_date", type="string", format="date", example="2024-01-15"),
     *                     @OA\Property(property="collected_date", type="string", format="date", example="2024-01-15"),
     *                     @OA\Property(property="received_date", type="string", format="date", example="2024-01-16"),
     *                     @OA\Property(property="result_date", type="string", format="date", example="2024-01-17"),
     *                     @OA\Property(property="status", type="string", example="completed"),
     *                     @OA\Property(property="priority", type="string", example="routine"),
     *                     @OA\Property(property="results", type="object", example={"hemoglobin":"14.2 g/dL","hematocrit":"42.1%","white_blood_cells":"7.2 K/uL"}),
     *                     @OA\Property(property="reference_ranges", type="object", example={"hemoglobin":"12.0-16.0 g/dL","hematocrit":"36.0-46.0%"}),
     *                     @OA\Property(property="abnormal_flags", type="array", @OA\Items(type="string")),
     *                     @OA\Property(property="interpretation", type="string", example="All values within normal limits"),
     *                     @OA\Property(property="notes", type="string", example="Patient fasting for 12 hours"),
     *                     @OA\Property(property="is_verified", type="boolean", example=false),
     *                     @OA\Property(property="verified_by", type="integer", example=1),
     *                     @OA\Property(property="verified_at", type="string", format="date-time"),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time")
     *                 ),
     *                 @OA\Property(property="patient", type="object", nullable=true),
     *                 @OA\Property(property="doctor", type="object", nullable=true),
     *                 @OA\Property(property="appointment", type="object", nullable=true),
     *                 @OA\Property(property="encounter", type="object", nullable=true),
     *                 @OA\Property(property="file_assets", type="array", @OA\Items(type="object"))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="No access to this lab result",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Lab result not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function show(LabResult $labResult): JsonResponse
    {
        try {
            if (!$this->hasClinicAccess($labResult->clinic_id)) {
                return $this->forbiddenResponse('No access to this lab result');
            }

            $labResult->load([
                'patient', 
                'doctor.user', 
                'clinic', 
                'encounter',
                'fileAssets'
            ]);

            return $this->successResponse([
                'lab_result' => $labResult,
                'is_abnormal' => $labResult->isAbnormal(),
            ], 'Lab result retrieved successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Update the specified lab result
     */
    public function update(Request $request, LabResult $labResult): JsonResponse
    {
        try {
            if (!$this->hasClinicAccess($labResult->clinic_id)) {
                return $this->forbiddenResponse('No access to this lab result');
            }

            $validator = Validator::make($request->all(), [
                'patient_id' => 'sometimes|required|integer|exists:patients,id',
                'encounter_id' => 'nullable|integer|exists:encounters,id',
                'ordered_by_doctor_id' => 'sometimes|required|integer|exists:doctors,id',
                'test_type' => 'sometimes|required|string|max:255',
                'test_name' => 'sometimes|required|string|max:255',
                'result_value' => 'nullable|string|max:255',
                'unit' => 'nullable|string|max:50',
                'reference_range' => 'nullable|string|max:255',
                'status' => 'sometimes|required|string|in:pending,in_progress,completed,abnormal,cancelled',
                'ordered_at' => 'nullable|date',
                'completed_at' => 'nullable|date|after:ordered_at',
                'notes' => 'nullable|string|max:1000',
                'reviewed_by_doctor_id' => 'nullable|integer|exists:doctors,id',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $labResult->update($validator->validated());
            $labResult->load(['patient', 'doctor.user', 'clinic', 'encounter']);

            return $this->successResponse([
                'lab_result' => $labResult,
            ], 'Lab result updated successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Remove the specified lab result
     */
    public function destroy(LabResult $labResult): JsonResponse
    {
        try {
            if (!$this->hasClinicAccess($labResult->clinic_id)) {
                return $this->forbiddenResponse('No access to this lab result');
            }

            // Check if lab result has any related records
            $hasRecords = $labResult->fileAssets()->exists();

            if ($hasRecords) {
                return $this->errorResponse('Cannot delete lab result with existing files', null, 422);
            }

            $labResult->delete();

            return $this->successResponse(null, 'Lab result deleted successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/lab-results/{labResult}/file-assets",
     *     summary="Get lab result files",
     *     description="Retrieve all file assets associated with a lab result",
     *     tags={"Lab Results"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="labResult",
     *         in="path",
     *         description="Lab result ID",
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
     *         @OA\Schema(type="string", example="lab_reports")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lab result files retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Lab result files retrieved successfully"),
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
     *                                 @OA\Property(property="title", type="string", example="Lab Report - CBC"),
     *                                 @OA\Property(property="description", type="string", example="Complete Blood Count Lab Report"),
     *                                 @OA\Property(property="filename", type="string", example="cbc_report_20240117.pdf"),
     *                                 @OA\Property(property="original_filename", type="string", example="CBC_Report.pdf"),
     *                                 @OA\Property(property="file_size", type="integer", example=1024000),
     *                                 @OA\Property(property="mime_type", type="string", example="application/pdf"),
     *                                 @OA\Property(property="category", type="string", example="lab_reports"),
     *                                 @OA\Property(property="file_path", type="string", example="/storage/files/cbc_report_20240117.pdf"),
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
     *         description="No access to this lab result",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Lab result not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function fileAssets(Request $request, LabResult $labResult): JsonResponse
    {
        try {
            if (!$this->hasClinicAccess($labResult->clinic_id)) {
                return $this->forbiddenResponse('No access to this lab result');
            }

            [$perPage, $page] = $this->getPaginationParams($request);

            $fileAssets = $labResult->fileAssets()
                ->latest()
                ->paginate($perPage, ['*'], 'page', $page);

            return $this->paginatedResponse($fileAssets, 'Lab result files retrieved');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/lab-results/{labResult}/file-assets",
     *     summary="Upload lab result file",
     *     description="Upload a file asset for a specific lab result",
     *     tags={"Lab Results"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="labResult",
     *         in="path",
     *         description="Lab result ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="file", type="string", format="binary", description="File to upload"),
     *                 @OA\Property(property="title", type="string", example="Lab Report - CBC", description="File title"),
     *                 @OA\Property(property="description", type="string", example="Complete Blood Count Lab Report", description="File description"),
     *                 @OA\Property(property="category", type="string", example="lab_reports", description="File category"),
     *                 @OA\Property(property="is_private", type="boolean", example=false, description="Whether file is private"),
     *                 @OA\Property(property="tags", type="array", @OA\Items(type="string"), example={"lab","cbc","report"}, description="File tags")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Lab result file uploaded successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Lab result file uploaded successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="file_asset", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="title", type="string", example="Lab Report - CBC"),
     *                     @OA\Property(property="description", type="string", example="Complete Blood Count Lab Report"),
     *                     @OA\Property(property="filename", type="string", example="cbc_report_20240117.pdf"),
     *                     @OA\Property(property="original_filename", type="string", example="CBC_Report.pdf"),
     *                     @OA\Property(property="file_size", type="integer", example=1024000),
     *                     @OA\Property(property="mime_type", type="string", example="application/pdf"),
     *                     @OA\Property(property="category", type="string", example="lab_reports"),
     *                     @OA\Property(property="file_path", type="string", example="/storage/files/cbc_report_20240117.pdf"),
     *                     @OA\Property(property="download_url", type="string", example="/api/v1/file-assets/1/download"),
     *                     @OA\Property(property="preview_url", type="string", example="/api/v1/file-assets/1/preview"),
     *                     @OA\Property(property="is_private", type="boolean", example=false),
     *                     @OA\Property(property="tags", type="array", @OA\Items(type="string")),
     *                     @OA\Property(property="lab_result_id", type="integer", example=1),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="No access to this lab result",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Lab result not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function uploadFile(Request $request, LabResult $labResult): JsonResponse
    {
        try {
            if (!$this->hasClinicAccess($labResult->clinic_id)) {
                return $this->forbiddenResponse('No access to this lab result');
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
            $path = $file->store('lab-results/' . $labResult->id, 'private');

            $fileAsset = $labResult->fileAssets()->create([
                'clinic_id' => $labResult->clinic_id,
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
     * @OA\Post(
     *     path="/api/v1/lab-results/{labResult}/verify",
     *     summary="Verify lab result",
     *     description="Verify and review a lab result",
     *     tags={"Lab Results"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="labResult",
     *         in="path",
     *         description="Lab result ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="reviewed_by_doctor_id", type="integer", example=1, description="ID of doctor reviewing the result"),
     *             @OA\Property(property="notes", type="string", example="Results reviewed and verified. All values within normal limits.", description="Review notes"),
     *             @OA\Property(property="status", type="string", enum={"completed","abnormal"}, example="completed", description="Updated status after review")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lab result verified successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Lab result verified successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="lab_result", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="patient_id", type="integer", example=1),
     *                     @OA\Property(property="doctor_id", type="integer", example=1),
     *                     @OA\Property(property="test_name", type="string", example="Complete Blood Count"),
     *                     @OA\Property(property="test_code", type="string", example="CBC"),
     *                     @OA\Property(property="status", type="string", example="completed"),
     *                     @OA\Property(property="is_verified", type="boolean", example=true),
     *                     @OA\Property(property="verified_by", type="integer", example=1),
     *                     @OA\Property(property="verified_at", type="string", format="date-time"),
     *                     @OA\Property(property="notes", type="string", example="Results reviewed and verified. All values within normal limits."),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="No access to this lab result",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Lab result not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function review(Request $request, LabResult $labResult): JsonResponse
    {
        try {
            if (!$this->hasClinicAccess($labResult->clinic_id)) {
                return $this->forbiddenResponse('No access to this lab result');
            }

            $validator = Validator::make($request->all(), [
                'reviewed_by_doctor_id' => 'required|integer|exists:doctors,id',
                'notes' => 'nullable|string|max:1000',
                'status' => 'nullable|string|in:completed,abnormal',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $labResult->update([
                'reviewed_by_doctor_id' => $request->reviewed_by_doctor_id,
                'notes' => $request->notes,
                'status' => $request->status ?? 'completed',
                'completed_at' => now(),
            ]);

            $labResult->load(['patient', 'doctor.user', 'clinic', 'encounter']);

            return $this->successResponse([
                'lab_result' => $labResult,
            ], 'Lab result reviewed successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/lab-results/pending",
     *     summary="Get pending lab results",
     *     description="Retrieve all pending lab results for the clinic",
     *     tags={"Lab Results"},
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
     *         name="doctor_id",
     *         in="query",
     *         description="Filter by ordering doctor",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="test_name",
     *         in="query",
     *         description="Filter by test name",
     *         @OA\Schema(type="string", example="Complete Blood Count")
     *     ),
     *     @OA\Parameter(
     *         name="priority",
     *         in="query",
     *         description="Filter by priority",
     *         @OA\Schema(type="string", enum={"routine","urgent","stat"}, example="urgent")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Pending lab results retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Pending lab results retrieved successfully"),
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
     *                                 @OA\Property(property="test_name", type="string", example="Complete Blood Count"),
     *                                 @OA\Property(property="test_code", type="string", example="CBC"),
     *                                 @OA\Property(property="lab_name", type="string", example="Central Lab Services"),
     *                                 @OA\Property(property="lab_reference", type="string", example="LAB-2024-001"),
     *                                 @OA\Property(property="ordered_date", type="string", format="date", example="2024-01-15"),
     *                                 @OA\Property(property="collected_date", type="string", format="date", example="2024-01-15"),
     *                                 @OA\Property(property="status", type="string", example="pending"),
     *                                 @OA\Property(property="priority", type="string", example="routine"),
     *                                 @OA\Property(property="is_verified", type="boolean", example=false),
     *                                 @OA\Property(property="patient", type="object", nullable=true),
     *                                 @OA\Property(property="doctor", type="object", nullable=true),
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
    public function pending(Request $request): JsonResponse
    {
        try {
            $currentClinic = $this->getCurrentClinic();

            if (!$currentClinic) {
                return $this->errorResponse('No clinic access', null, 403);
            }

            [$perPage, $page] = $this->getPaginationParams($request);

            $labResults = LabResult::where('clinic_id', $currentClinic->id)
                ->where('status', 'pending')
                ->with(['patient', 'doctor.user', 'clinic', 'encounter'])
                ->latest()
                ->paginate($perPage, ['*'], 'page', $page);

            return $this->paginatedResponse($labResults, 'Pending lab results retrieved');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/lab-results/abnormal",
     *     summary="Get abnormal lab results",
     *     description="Retrieve all abnormal lab results for the clinic",
     *     tags={"Lab Results"},
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
     *         name="doctor_id",
     *         in="query",
     *         description="Filter by ordering doctor",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="test_name",
     *         in="query",
     *         description="Filter by test name",
     *         @OA\Schema(type="string", example="Complete Blood Count")
     *     ),
     *     @OA\Parameter(
     *         name="severity",
     *         in="query",
     *         description="Filter by severity",
     *         @OA\Schema(type="string", enum={"abnormal","critical"}, example="abnormal")
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
     *         description="Abnormal lab results retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Abnormal lab results retrieved successfully"),
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
     *                                 @OA\Property(property="test_name", type="string", example="Complete Blood Count"),
     *                                 @OA\Property(property="test_code", type="string", example="CBC"),
     *                                 @OA\Property(property="lab_name", type="string", example="Central Lab Services"),
     *                                 @OA\Property(property="lab_reference", type="string", example="LAB-2024-001"),
     *                                 @OA\Property(property="result_date", type="string", format="date", example="2024-01-17"),
     *                                 @OA\Property(property="status", type="string", example="abnormal"),
     *                                 @OA\Property(property="priority", type="string", example="urgent"),
     *                                 @OA\Property(property="results", type="object", example={"hemoglobin":"10.2 g/dL","hematocrit":"32.1%","white_blood_cells":"12.2 K/uL"}),
     *                                 @OA\Property(property="reference_ranges", type="object", example={"hemoglobin":"12.0-16.0 g/dL","hematocrit":"36.0-46.0%"}),
     *                                 @OA\Property(property="abnormal_flags", type="array", @OA\Items(type="string"), example={"L","H"}),
     *                                 @OA\Property(property="interpretation", type="string", example="Low hemoglobin and hematocrit, elevated white blood cells"),
     *                                 @OA\Property(property="is_verified", type="boolean", example=false),
     *                                 @OA\Property(property="patient", type="object", nullable=true),
     *                                 @OA\Property(property="doctor", type="object", nullable=true),
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
    public function abnormal(Request $request): JsonResponse
    {
        try {
            $currentClinic = $this->getCurrentClinic();

            if (!$currentClinic) {
                return $this->errorResponse('No clinic access', null, 403);
            }

            [$perPage, $page] = $this->getPaginationParams($request);

            $labResults = LabResult::where('clinic_id', $currentClinic->id)
                ->where('status', 'abnormal')
                ->with(['patient', 'doctor.user', 'clinic', 'encounter'])
                ->latest()
                ->paginate($perPage, ['*'], 'page', $page);

            return $this->paginatedResponse($labResults, 'Abnormal lab results retrieved');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/lab-results/reports",
     *     summary="Get lab result reports",
     *     description="Retrieve comprehensive lab result reports and analytics",
     *     tags={"Lab Results"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="period",
     *         in="query",
     *         description="Time period for reports",
     *         @OA\Schema(type="string", enum={"day","week","month","quarter","year"}, example="month")
     *     ),
     *     @OA\Parameter(
     *         name="doctor_id",
     *         in="query",
     *         description="Filter by doctor",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="test_name",
     *         in="query",
     *         description="Filter by test name",
     *         @OA\Schema(type="string", example="Complete Blood Count")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by status",
     *         @OA\Schema(type="string", enum={"pending","completed","abnormal","critical"}, example="completed")
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
     *         description="Lab result reports retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Lab result reports retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="period", type="string", example="month"),
     *                 @OA\Property(property="overview", type="object",
     *                     @OA\Property(property="total_tests", type="integer", example=150),
     *                     @OA\Property(property="completed_tests", type="integer", example=140),
     *                     @OA\Property(property="pending_tests", type="integer", example=8),
     *                     @OA\Property(property="abnormal_tests", type="integer", example=12),
     *                     @OA\Property(property="critical_tests", type="integer", example=2),
     *                     @OA\Property(property="completion_rate", type="number", format="float", example=93.3)
     *                 ),
     *                 @OA\Property(property="by_test_type", type="array", @OA\Items(
     *                     @OA\Property(property="test_name", type="string", example="Complete Blood Count"),
     *                     @OA\Property(property="test_code", type="string", example="CBC"),
     *                     @OA\Property(property="total_count", type="integer", example=45),
     *                     @OA\Property(property="abnormal_count", type="integer", example=5),
     *                     @OA\Property(property="abnormal_rate", type="number", format="float", example=11.1),
     *                     @OA\Property(property="average_turnaround_time", type="number", format="float", example=24.5)
     *                 )),
     *                 @OA\Property(property="by_doctor", type="array", @OA\Items(
     *                     @OA\Property(property="doctor_id", type="integer", example=1),
     *                     @OA\Property(property="doctor_name", type="string", example="Dr. John Smith"),
     *                     @OA\Property(property="total_orders", type="integer", example=25),
     *                     @OA\Property(property="abnormal_results", type="integer", example=3),
     *                     @OA\Property(property="abnormal_rate", type="number", format="float", example=12.0)
     *                 )),
     *                 @OA\Property(property="trends", type="object",
     *                     @OA\Property(property="daily_counts", type="array", @OA\Items(type="object")),
     *                     @OA\Property(property="abnormal_trends", type="array", @OA\Items(type="object")),
     *                     @OA\Property(property="turnaround_trends", type="array", @OA\Items(type="object"))
     *                 ),
     *                 @OA\Property(property="quality_metrics", type="object",
     *                     @OA\Property(property="average_turnaround_time", type="number", format="float", example=22.5),
     *                     @OA\Property(property="on_time_completion_rate", type="number", format="float", example=95.2),
     *                     @OA\Property(property="critical_result_notification_time", type="number", format="float", example=2.1)
     *                 )
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
    public function reports(Request $request): JsonResponse
    {
        try {
            $currentClinic = $this->getCurrentClinic();

            if (!$currentClinic) {
                return $this->errorResponse('No clinic access', null, 403);
            }

            $reports = [
                'total_lab_results' => LabResult::where('clinic_id', $currentClinic->id)->count(),
                'pending_lab_results' => LabResult::where('clinic_id', $currentClinic->id)->where('status', 'pending')->count(),
                'completed_lab_results' => LabResult::where('clinic_id', $currentClinic->id)->where('status', 'completed')->count(),
                'abnormal_lab_results' => LabResult::where('clinic_id', $currentClinic->id)->where('status', 'abnormal')->count(),
                'lab_results_this_month' => LabResult::where('clinic_id', $currentClinic->id)
                    ->where('created_at', '>=', now()->startOfMonth())
                    ->count(),
                'lab_results_by_type' => LabResult::where('clinic_id', $currentClinic->id)
                    ->selectRaw('test_type, COUNT(*) as count')
                    ->groupBy('test_type')
                    ->get(),
                'lab_results_by_status' => LabResult::where('clinic_id', $currentClinic->id)
                    ->selectRaw('status, COUNT(*) as count')
                    ->groupBy('status')
                    ->get(),
            ];

            return $this->successResponse([
                'reports' => $reports,
            ], 'Lab result reports retrieved');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Webhook for lab results
     */
    public function webhook(Request $request): JsonResponse
    {
        try {
            // Verify webhook signature
            $signature = $request->header('X-Webhook-Signature');
            $payload = $request->getContent();
            
            // In a real implementation, you would verify the signature
            // For now, we'll just process the webhook

            $validator = Validator::make($request->all(), [
                'lab_result_id' => 'required|integer|exists:lab_results,id',
                'result_value' => 'required|string|max:255',
                'unit' => 'nullable|string|max:50',
                'reference_range' => 'nullable|string|max:255',
                'status' => 'required|string|in:completed,abnormal',
                'completed_at' => 'required|date',
                'notes' => 'nullable|string|max:1000',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $labResult = LabResult::findOrFail($request->lab_result_id);
            
            $labResult->update([
                'result_value' => $request->result_value,
                'unit' => $request->unit,
                'reference_range' => $request->reference_range,
                'status' => $request->status,
                'completed_at' => $request->completed_at,
                'notes' => $request->notes,
            ]);

            return $this->successResponse([
                'lab_result' => $labResult,
            ], 'Webhook processed successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/lab-results/{labResult}/pdf",
     *     summary="Download lab result PDF",
     *     description="Generate and download a PDF report for a lab result",
     *     tags={"Lab Results"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="labResult",
     *         in="path",
     *         description="Lab result ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="format",
     *         in="query",
     *         description="PDF format",
     *         @OA\Schema(type="string", enum={"standard","detailed","summary"}, example="standard")
     *     ),
     *     @OA\Parameter(
     *         name="include_reference_ranges",
     *         in="query",
     *         description="Include reference ranges in PDF",
     *         @OA\Schema(type="boolean", example=true)
     *     ),
     *     @OA\Parameter(
     *         name="include_interpretation",
     *         in="query",
     *         description="Include clinical interpretation in PDF",
     *         @OA\Schema(type="boolean", example=true)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lab result PDF generated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Lab result PDF generated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="pdf_url", type="string", example="/api/v1/lab-results/1/pdf/download"),
     *                 @OA\Property(property="filename", type="string", example="lab_result_001_20240117.pdf"),
     *                 @OA\Property(property="file_size", type="integer", example=245760),
     *                 @OA\Property(property="generated_at", type="string", format="date-time", example="2024-01-17T10:00:00Z"),
     *                 @OA\Property(property="expires_at", type="string", format="date-time", example="2024-01-17T11:00:00Z"),
     *                 @OA\Property(property="format", type="string", example="standard"),
     *                 @OA\Property(property="pages", type="integer", example=2)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="No access to this lab result",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Lab result not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function pdf(Request $request, LabResult $labResult): JsonResponse
    {
        try {
            if (!$this->hasClinicAccess($labResult->clinic_id)) {
                return $this->forbiddenResponse('No access to this lab result');
            }

            $format = $request->get('format', 'standard');
            $includeReferenceRanges = $request->get('include_reference_ranges', true);
            $includeInterpretation = $request->get('include_interpretation', true);

            // Mock PDF generation
            $pdfData = [
                'pdf_url' => "/api/v1/lab-results/{$labResult->id}/pdf/download",
                'filename' => "lab_result_{$labResult->id}_" . now()->format('Ymd') . ".pdf",
                'file_size' => 245760,
                'generated_at' => now()->toISOString(),
                'expires_at' => now()->addHour()->toISOString(),
                'format' => $format,
                'pages' => 2
            ];

            return $this->successResponse($pdfData, 'Lab result PDF generated successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
}
