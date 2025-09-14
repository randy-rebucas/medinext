<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Prescription;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Encounter;
use Illuminate\Support\Facades\Validator;

class PrescriptionController extends Controller
{
    /**
     * Display the prescriptions management page
     */
    public function index(Request $request): Response
    {
        $this->logWebRequest('Prescriptions Management Access', ['action' => 'index']);
        
        $user = $request->user();
        $userClinicRole = $this->getUserClinicRole($request);

        if (!$userClinicRole) {
            return Inertia::render('admin/prescriptions', [
                'prescriptions' => [],
                'patients' => [],
                'doctors' => [],
                'permissions' => [],
            ]);
        }

        $clinicId = $userClinicRole->clinic_id;

        // Get prescriptions for this clinic
        $prescriptions = $this->getPrescriptions($clinicId);

        // Get patients for dropdown
        $patients = $this->getPatients($clinicId);

        // Get doctors for dropdown
        $doctors = $this->getDoctors($clinicId);

        // Get user permissions
        $permissions = $this->getUserPermissions($userClinicRole->role->name ?? 'user');

        return Inertia::render('admin/prescriptions', [
            'prescriptions' => $prescriptions,
            'patients' => $patients,
            'doctors' => $doctors,
            'permissions' => $permissions,
        ]);
    }

    /**
     * Store a newly created prescription
     */
    public function store(Request $request)
    {
        $rules = [
            'patient_id' => 'required|exists:patients,id',
            'encounter_id' => 'nullable|exists:encounters,id',
            'doctor_id' => 'required|exists:doctors,id',
            'medication_name' => 'required|string|max:255|regex:/^[a-zA-Z0-9\s\-\'\.&]+$/',
            'medication_code' => 'nullable|string|max:100|regex:/^[a-zA-Z0-9\-\s]+$/',
            'dosage' => 'required|string|max:255|regex:/^[a-zA-Z0-9\s\-\'\.\/]+$/',
            'frequency' => 'required|string|max:255|regex:/^[a-zA-Z0-9\s\-\'\.\/]+$/',
            'duration' => 'required|string|max:255|regex:/^[a-zA-Z0-9\s\-\'\.\/]+$/',
            'quantity' => 'required|integer|min:1|max:9999',
            'refills' => 'nullable|integer|min:0|max:12',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'nullable|date|after:start_date',
            'status' => 'required|string|in:active,completed,cancelled,expired',
            'instructions' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:1000',
        ];

        $messages = [
            'patient_id.exists' => 'Selected patient does not exist.',
            'encounter_id.exists' => 'Selected encounter does not exist.',
            'doctor_id.exists' => 'Selected doctor does not exist.',
            'medication_name.regex' => 'Medication name can only contain letters, numbers, spaces, hyphens, apostrophes, periods, and ampersands.',
            'medication_code.regex' => 'Medication code can only contain letters, numbers, hyphens, and spaces.',
            'dosage.regex' => 'Dosage can only contain letters, numbers, spaces, hyphens, apostrophes, periods, and forward slashes.',
            'frequency.regex' => 'Frequency can only contain letters, numbers, spaces, hyphens, apostrophes, periods, and forward slashes.',
            'duration.regex' => 'Duration can only contain letters, numbers, spaces, hyphens, apostrophes, periods, and forward slashes.',
            'quantity.min' => 'Quantity must be at least 1.',
            'quantity.max' => 'Quantity cannot exceed 9999.',
            'refills.max' => 'Refills cannot exceed 12.',
            'start_date.after_or_equal' => 'Start date cannot be in the past.',
            'end_date.after' => 'End date must be after start date.',
            'status.in' => 'Invalid prescription status.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $this->logWebRequest('Create Prescription', ['action' => 'store']);
            
            $user = $request->user();

            if (!$user) {
                $this->logSecurityEvent('Unauthenticated prescription creation attempt');
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $userClinicRole = $this->getUserClinicRole($request);

            if (!$userClinicRole) {
                $this->logSecurityEvent('Unauthorized clinic access attempt', ['user_id' => $user->id]);
                return response()->json([
                    'success' => false,
                    'message' => 'User does not have clinic access'
                ], 403);
            }

            $clinicId = $userClinicRole->clinic_id;

            // Create prescription
            $prescription = Prescription::create([
                'clinic_id' => $clinicId,
                'patient_id' => $request->patient_id,
                'encounter_id' => $request->encounter_id,
                'doctor_id' => $request->doctor_id,
                'medication_name' => $request->medication_name,
                'medication_code' => $request->medication_code,
                'dosage' => $request->dosage,
                'frequency' => $request->frequency,
                'duration' => $request->duration,
                'quantity' => $request->quantity,
                'refills' => $request->refills ?? 0,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'status' => $request->status,
                'instructions' => $request->instructions,
                'notes' => $request->notes,
                'prescribed_by' => $user->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Prescription created successfully',
                'prescription' => $this->getPrescription($prescription->id)
            ]);

        } catch (\Exception $e) {
            $this->handleException($e, 'PrescriptionController::store');
            return response()->json([
                'success' => false,
                'message' => 'Failed to create prescription. Please try again.'
            ], 500);
        }
    }

    /**
     * Update the specified prescription
     */
    public function update(Request $request, $id)
    {
        $rules = [
            'patient_id' => 'required|exists:patients,id',
            'encounter_id' => 'nullable|exists:encounters,id',
            'doctor_id' => 'required|exists:doctors,id',
            'medication_name' => 'required|string|max:255|regex:/^[a-zA-Z0-9\s\-\'\.&]+$/',
            'medication_code' => 'nullable|string|max:100|regex:/^[a-zA-Z0-9\-\s]+$/',
            'dosage' => 'required|string|max:255|regex:/^[a-zA-Z0-9\s\-\'\.\/]+$/',
            'frequency' => 'required|string|max:255|regex:/^[a-zA-Z0-9\s\-\'\.\/]+$/',
            'duration' => 'required|string|max:255|regex:/^[a-zA-Z0-9\s\-\'\.\/]+$/',
            'quantity' => 'required|integer|min:1|max:9999',
            'refills' => 'nullable|integer|min:0|max:12',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'status' => 'required|string|in:active,completed,cancelled,expired',
            'instructions' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:1000',
        ];

        $messages = [
            'patient_id.exists' => 'Selected patient does not exist.',
            'encounter_id.exists' => 'Selected encounter does not exist.',
            'doctor_id.exists' => 'Selected doctor does not exist.',
            'medication_name.regex' => 'Medication name can only contain letters, numbers, spaces, hyphens, apostrophes, periods, and ampersands.',
            'medication_code.regex' => 'Medication code can only contain letters, numbers, hyphens, and spaces.',
            'dosage.regex' => 'Dosage can only contain letters, numbers, spaces, hyphens, apostrophes, periods, and forward slashes.',
            'frequency.regex' => 'Frequency can only contain letters, numbers, spaces, hyphens, apostrophes, periods, and forward slashes.',
            'duration.regex' => 'Duration can only contain letters, numbers, spaces, hyphens, apostrophes, periods, and forward slashes.',
            'quantity.min' => 'Quantity must be at least 1.',
            'quantity.max' => 'Quantity cannot exceed 9999.',
            'refills.max' => 'Refills cannot exceed 12.',
            'end_date.after' => 'End date must be after start date.',
            'status.in' => 'Invalid prescription status.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $prescription = Prescription::findOrFail($id);

            // Update prescription
            $prescription->update([
                'patient_id' => $request->patient_id,
                'encounter_id' => $request->encounter_id,
                'doctor_id' => $request->doctor_id,
                'medication_name' => $request->medication_name,
                'medication_code' => $request->medication_code,
                'dosage' => $request->dosage,
                'frequency' => $request->frequency,
                'duration' => $request->duration,
                'quantity' => $request->quantity,
                'refills' => $request->refills ?? 0,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'status' => $request->status,
                'instructions' => $request->instructions,
                'notes' => $request->notes,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Prescription updated successfully',
                'prescription' => $this->getPrescription($prescription->id)
            ]);

        } catch (\Exception $e) {
            $this->handleException($e, 'PrescriptionController::update');
            return response()->json([
                'success' => false,
                'message' => 'Failed to update prescription. Please try again.'
            ], 500);
        }
    }

    /**
     * Remove the specified prescription
     */
    public function destroy($id)
    {
        try {
            $prescription = Prescription::findOrFail($id);
            $prescription->delete();

            return response()->json([
                'success' => true,
                'message' => 'Prescription deleted successfully'
            ]);

        } catch (\Exception $e) {
            $this->handleException($e, 'PrescriptionController::destroy');
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete prescription. Please try again.'
            ], 500);
        }
    }

    /**
     * Get prescription details
     */
    public function show($id)
    {
        try {
            $prescription = $this->getPrescription($id);

            if (!$prescription) {
                return response()->json([
                    'success' => false,
                    'message' => 'Prescription not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Prescription retrieved successfully',
                'prescription' => $prescription
            ]);

        } catch (\Exception $e) {
            $this->handleException($e, 'PrescriptionController::show');
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve prescription. Please try again.'
            ], 500);
        }
    }

    /**
     * Get prescriptions for a clinic with caching
     */
    private function getPrescriptions($clinicId)
    {
        $cacheKey = $this->getClinicCacheKey('prescriptions', $clinicId);
        
        return $this->remember($cacheKey, 30, function () use ($clinicId) {
            return Prescription::where('clinic_id', $clinicId)
                ->with(['patient:id,first_name,last_name', 'doctor.user:id,name', 'encounter:id,date'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($prescription) {
                    return [
                        'id' => $prescription->id,
                        'medication_name' => $prescription->medication_name,
                        'medication_code' => $prescription->medication_code,
                        'dosage' => $prescription->dosage,
                        'frequency' => $prescription->frequency,
                        'duration' => $prescription->duration,
                        'quantity' => $prescription->quantity,
                        'refills' => $prescription->refills,
                        'start_date' => $prescription->start_date,
                        'end_date' => $prescription->end_date,
                        'status' => $prescription->status,
                        'patient_name' => $prescription->patient->first_name . ' ' . $prescription->patient->last_name,
                        'patient_id' => $prescription->patient_id,
                        'doctor_name' => $prescription->doctor->user->name,
                        'doctor_id' => $prescription->doctor_id,
                        'encounter_id' => $prescription->encounter_id,
                        'instructions' => $prescription->instructions,
                        'notes' => $prescription->notes,
                        'created_at' => $prescription->created_at->format('Y-m-d H:i:s'),
                        'updated_at' => $prescription->updated_at->format('Y-m-d H:i:s'),
                    ];
                });
        });
    }

    /**
     * Get patients for dropdown
     */
    private function getPatients($clinicId)
    {
        return Patient::where('clinic_id', $clinicId)
            ->select('id', 'first_name', 'last_name')
            ->orderBy('first_name')
            ->get()
            ->map(function ($patient) {
                return [
                    'id' => $patient->id,
                    'name' => $patient->first_name . ' ' . $patient->last_name,
                ];
            });
    }

    /**
     * Get doctors for dropdown
     */
    private function getDoctors($clinicId)
    {
        return Doctor::where('clinic_id', $clinicId)
            ->with(['user:id,name'])
            ->select('id', 'user_id')
            ->orderBy('user_id')
            ->get()
            ->map(function ($doctor) {
                return [
                    'id' => $doctor->id,
                    'name' => $doctor->user->name,
                ];
            });
    }

    /**
     * Get a single prescription
     */
    private function getPrescription($prescriptionId)
    {
        $prescription = Prescription::with(['patient', 'doctor.user', 'encounter'])->findOrFail($prescriptionId);

        return [
            'id' => $prescription->id,
            'medication_name' => $prescription->medication_name,
            'medication_code' => $prescription->medication_code,
            'dosage' => $prescription->dosage,
            'frequency' => $prescription->frequency,
            'duration' => $prescription->duration,
            'quantity' => $prescription->quantity,
            'refills' => $prescription->refills,
            'start_date' => $prescription->start_date,
            'end_date' => $prescription->end_date,
            'status' => $prescription->status,
            'instructions' => $prescription->instructions,
            'notes' => $prescription->notes,
            'patient' => [
                'id' => $prescription->patient->id,
                'name' => $prescription->patient->first_name . ' ' . $prescription->patient->last_name,
            ],
            'doctor' => [
                'id' => $prescription->doctor->id,
                'name' => $prescription->doctor->user->name,
            ],
            'encounter' => $prescription->encounter ? [
                'id' => $prescription->encounter->id,
                'date' => $prescription->encounter->date,
            ] : null,
            'created_at' => $prescription->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $prescription->updated_at->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Get user permissions
     */
    private function getUserPermissions($role)
    {
        $permissions = [
            'superadmin' => [
                'manage_prescriptions', 'view_prescriptions', 'create_prescriptions',
                'edit_prescriptions', 'delete_prescriptions'
            ],
            'admin' => [
                'manage_prescriptions', 'view_prescriptions', 'create_prescriptions',
                'edit_prescriptions', 'delete_prescriptions'
            ],
            'doctor' => [
                'view_prescriptions', 'create_prescriptions', 'edit_prescriptions'
            ],
            'receptionist' => [
                'view_prescriptions', 'create_prescriptions'
            ],
            'patient' => [
                'view_prescriptions'
            ]
        ];

        return $permissions[$role] ?? [];
    }
}
