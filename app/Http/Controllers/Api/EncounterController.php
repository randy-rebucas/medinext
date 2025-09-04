<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\Encounter;
use App\Models\Patient;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class EncounterController extends BaseController
{
    /**
     * Display a listing of encounters
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
     * Store a newly created encounter
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
     * Display the specified encounter
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
     * Get encounter prescriptions
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
     * Get encounter lab results
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
     * Get encounter file assets
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
     * Upload file for encounter
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
     * Update SOAP notes
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
     * Complete encounter
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
