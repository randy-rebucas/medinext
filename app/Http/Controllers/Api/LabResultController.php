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

class LabResultController extends BaseController
{
    /**
     * Display a listing of lab results
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
     * Store a newly created lab result
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
     * Display the specified lab result
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
     * Get lab result file assets
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
     * Upload file for lab result
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
     * Review lab result
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
     * Get pending lab results
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
     * Get abnormal lab results
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
     * Get lab result reports
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
}
