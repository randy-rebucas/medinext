<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\Patient;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class PrescriptionController extends BaseController
{
    /**
     * Display a listing of prescriptions
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
     * Store a newly created prescription
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
     * Display the specified prescription
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
     * Get prescription items
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
     * Add item to prescription
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
     * Verify prescription
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
     * Mark prescription as dispensed
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
     * Process refill
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
     * Download prescription PDF
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
     * Get prescription QR code
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
     * Get active prescriptions
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
     * Get expired prescriptions
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
     * Get prescriptions needing refill
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
