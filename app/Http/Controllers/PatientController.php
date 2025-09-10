<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Patient;
use App\Models\Clinic;
use App\Models\User;
use App\Models\UserClinicRole;
use App\Models\Appointment;
use App\Models\Encounter;
use App\Models\Prescription;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class PatientController extends Controller
{
    /**
     * Display the patient management page
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        // Get user's clinic
        $userClinicRole = $user->userClinicRoles()->with(['clinic', 'role'])->first();

        if (!$userClinicRole) {
            return Inertia::render('admin/patients', [
                'patients' => [],
                'permissions' => [],
            ]);
        }

        $clinicId = $userClinicRole->clinic_id;

        // Get patients for this clinic
        $patients = $this->getPatients($clinicId);

        // Get user permissions
        $permissions = $this->getUserPermissions($userClinicRole->role->name ?? 'user');

        return Inertia::render('admin/patients', [
            'patients' => $patients,
            'permissions' => $permissions,
        ]);
    }

    /**
     * Store a newly created patient
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'dob' => 'required|date|before:today',
            'sex' => 'required|string|in:Male,Female,Other',
            'contact' => 'required|array',
            'contact.email' => 'required|email|max:255',
            'contact.phone' => 'required|string|max:20',
            'contact.address' => 'nullable|string|max:500',
            'contact.city' => 'nullable|string|max:100',
            'contact.state' => 'nullable|string|max:100',
            'contact.zip_code' => 'nullable|string|max:20',
            'emergency_contact' => 'nullable|array',
            'emergency_contact.name' => 'nullable|string|max:255',
            'emergency_contact.phone' => 'nullable|string|max:20',
            'emergency_contact.relationship' => 'nullable|string|max:100',
            'insurance' => 'nullable|array',
            'insurance.provider' => 'nullable|string|max:255',
            'insurance.policy_number' => 'nullable|string|max:100',
            'insurance.group_number' => 'nullable|string|max:100',
            'allergies' => 'nullable|array',
            'medical_history' => 'nullable|string|max:2000',
            'medications' => 'nullable|string|max:2000',
            'notes' => 'nullable|string|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Get user from request
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $userClinicRole = $user->userClinicRoles()->with(['clinic'])->first();

            if (!$userClinicRole) {
                return response()->json([
                    'success' => false,
                    'message' => 'User does not have clinic access'
                ], 403);
            }

            $clinicId = $userClinicRole->clinic_id;

            // Generate patient ID
            $patientId = $this->generatePatientId($clinicId);

            // Create patient
            $patient = Patient::create([
                'clinic_id' => $clinicId,
                'patient_id' => $patientId,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'dob' => $request->dob,
                'sex' => $request->sex,
                'contact' => $request->contact,
                'emergency_contact' => $request->emergency_contact,
                'insurance' => $request->insurance,
                'allergies' => $request->allergies,
                'medical_history' => $request->medical_history,
                'medications' => $request->medications,
                'notes' => $request->notes,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Patient created successfully',
                'patient' => $this->getPatient($patient->id)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create patient: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified patient
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'dob' => 'required|date|before:today',
            'sex' => 'required|string|in:Male,Female,Other',
            'contact' => 'required|array',
            'contact.email' => 'required|email|max:255',
            'contact.phone' => 'required|string|max:20',
            'contact.address' => 'nullable|string|max:500',
            'contact.city' => 'nullable|string|max:100',
            'contact.state' => 'nullable|string|max:100',
            'contact.zip_code' => 'nullable|string|max:20',
            'emergency_contact' => 'nullable|array',
            'emergency_contact.name' => 'nullable|string|max:255',
            'emergency_contact.phone' => 'nullable|string|max:20',
            'emergency_contact.relationship' => 'nullable|string|max:100',
            'insurance' => 'nullable|array',
            'insurance.provider' => 'nullable|string|max:255',
            'insurance.policy_number' => 'nullable|string|max:100',
            'insurance.group_number' => 'nullable|string|max:100',
            'allergies' => 'nullable|array',
            'medical_history' => 'nullable|string|max:2000',
            'medications' => 'nullable|string|max:2000',
            'notes' => 'nullable|string|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $patient = Patient::findOrFail($id);

            // Update patient
            $patient->update([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'dob' => $request->dob,
                'sex' => $request->sex,
                'contact' => $request->contact,
                'emergency_contact' => $request->emergency_contact,
                'insurance' => $request->insurance,
                'allergies' => $request->allergies,
                'medical_history' => $request->medical_history,
                'medications' => $request->medications,
                'notes' => $request->notes,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Patient updated successfully',
                'patient' => $this->getPatient($patient->id)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update patient: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified patient
     */
    public function destroy($id)
    {
        try {
            $patient = Patient::findOrFail($id);
            $patient->delete();

            return response()->json([
                'success' => true,
                'message' => 'Patient deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete patient: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get patient details
     */
    public function show($id)
    {
        try {
            $patient = $this->getPatient($id);

            if (!$patient) {
                return response()->json([
                    'success' => false,
                    'message' => 'Patient not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Patient retrieved successfully',
                'patient' => $patient
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve patient: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get patient health records
     */
    public function healthRecords($id)
    {
        try {
            $patient = Patient::findOrFail($id);

            $appointments = Appointment::where('patient_id', $id)
                ->with(['doctor.user', 'room'])
                ->orderBy('start_at', 'desc')
                ->get()
                ->map(function ($appointment) {
                    return [
                        'id' => $appointment->id,
                        'date' => $appointment->start_at,
                        'doctor' => $appointment->doctor->user->name,
                        'type' => $appointment->type,
                        'status' => $appointment->status,
                        'room' => $appointment->room->name ?? 'No Room',
                        'reason' => $appointment->reason,
                        'notes' => $appointment->notes,
                    ];
                });

            $encounters = Encounter::where('patient_id', $id)
                ->with(['doctor.user'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($encounter) {
                    return [
                        'id' => $encounter->id,
                        'date' => $encounter->created_at,
                        'doctor' => $encounter->doctor->user->name,
                        'type' => $encounter->type,
                        'chief_complaint' => $encounter->chief_complaint,
                        'diagnosis' => $encounter->diagnosis,
                        'treatment' => $encounter->treatment,
                        'notes' => $encounter->notes,
                    ];
                });

            $prescriptions = Prescription::where('patient_id', $id)
                ->with(['doctor.user'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($prescription) {
                    return [
                        'id' => $prescription->id,
                        'date' => $prescription->created_at,
                        'doctor' => $prescription->doctor->user->name,
                        'medication' => $prescription->medication,
                        'dosage' => $prescription->dosage,
                        'frequency' => $prescription->frequency,
                        'duration' => $prescription->duration,
                        'instructions' => $prescription->instructions,
                        'status' => $prescription->status,
                    ];
                });

            return response()->json([
                'success' => true,
                'patient' => $this->getPatient($patient->id),
                'appointments' => $appointments,
                'encounters' => $encounters,
                'prescriptions' => $prescriptions,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve health records: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get patients for a clinic
     */
    private function getPatients($clinicId)
    {
        return Patient::where('clinic_id', $clinicId)
            ->withCount(['appointments', 'encounters', 'prescriptions'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($patient) {
                $lastAppointment = Appointment::where('patient_id', $patient->id)
                    ->orderBy('start_at', 'desc')
                    ->first();

                $nextAppointment = Appointment::where('patient_id', $patient->id)
                    ->where('start_at', '>', now())
                    ->orderBy('start_at', 'asc')
                    ->first();

                return [
                    'id' => $patient->id,
                    'patient_id' => $patient->patient_id,
                    'name' => $patient->first_name . ' ' . $patient->last_name,
                    'first_name' => $patient->first_name,
                    'last_name' => $patient->last_name,
                    'email' => $patient->contact['email'] ?? '',
                    'phone' => $patient->contact['phone'] ?? '',
                    'dob' => $patient->dob,
                    'age' => Carbon::parse($patient->dob)->age,
                    'sex' => $patient->sex,
                    'address' => $patient->contact['address'] ?? '',
                    'city' => $patient->contact['city'] ?? '',
                    'state' => $patient->contact['state'] ?? '',
                    'zip_code' => $patient->contact['zip_code'] ?? '',
                    'emergency_contact' => $patient->emergency_contact,
                    'insurance' => $patient->insurance,
                    'allergies' => $patient->allergies,
                    'medical_history' => $patient->medical_history,
                    'medications' => $patient->medications,
                    'notes' => $patient->notes,
                    'last_visit' => $lastAppointment ? $lastAppointment->start_at : null,
                    'next_appointment' => $nextAppointment ? $nextAppointment->start_at : null,
                    'total_visits' => $patient->appointments_count,
                    'total_encounters' => $patient->encounters_count,
                    'total_prescriptions' => $patient->prescriptions_count,
                    'status' => $this->getPatientStatus($patient),
                    'created_at' => $patient->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $patient->updated_at->format('Y-m-d H:i:s'),
                ];
            });
    }

    /**
     * Get a single patient
     */
    private function getPatient($patientId)
    {
        $patient = Patient::findOrFail($patientId);

        $lastAppointment = Appointment::where('patient_id', $patient->id)
            ->orderBy('start_at', 'desc')
            ->first();

        $nextAppointment = Appointment::where('patient_id', $patient->id)
            ->where('start_at', '>', now())
            ->orderBy('start_at', 'asc')
            ->first();

        return [
            'id' => $patient->id,
            'patient_id' => $patient->patient_id,
            'name' => $patient->first_name . ' ' . $patient->last_name,
            'first_name' => $patient->first_name,
            'last_name' => $patient->last_name,
            'email' => $patient->contact['email'] ?? '',
            'phone' => $patient->contact['phone'] ?? '',
            'dob' => $patient->dob,
            'age' => Carbon::parse($patient->dob)->age,
            'sex' => $patient->sex,
            'address' => $patient->contact['address'] ?? '',
            'city' => $patient->contact['city'] ?? '',
            'state' => $patient->contact['state'] ?? '',
            'zip_code' => $patient->contact['zip_code'] ?? '',
            'emergency_contact' => $patient->emergency_contact,
            'insurance' => $patient->insurance,
            'allergies' => $patient->allergies,
            'medical_history' => $patient->medical_history,
            'medications' => $patient->medications,
            'notes' => $patient->notes,
            'last_visit' => $lastAppointment ? $lastAppointment->start_at : null,
            'next_appointment' => $nextAppointment ? $nextAppointment->start_at : null,
            'total_visits' => $patient->appointments_count ?? 0,
            'total_encounters' => $patient->encounters_count ?? 0,
            'total_prescriptions' => $patient->prescriptions_count ?? 0,
            'status' => $this->getPatientStatus($patient),
            'created_at' => $patient->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $patient->updated_at->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Get patient status based on recent activity
     */
    private function getPatientStatus($patient)
    {
        $lastAppointment = Appointment::where('patient_id', $patient->id)
            ->orderBy('start_at', 'desc')
            ->first();

        if (!$lastAppointment) {
            return 'New';
        }

        $daysSinceLastVisit = Carbon::parse($lastAppointment->start_at)->diffInDays(now());

        if ($daysSinceLastVisit <= 30) {
            return 'Active';
        } elseif ($daysSinceLastVisit <= 90) {
            return 'Regular';
        } else {
            return 'Inactive';
        }
    }

    /**
     * Generate unique patient ID
     */
    private function generatePatientId($clinicId)
    {
        $clinic = Clinic::find($clinicId);
        $clinicCode = $clinic ? strtoupper(substr($clinic->name, 0, 3)) : 'CLN';

        $lastPatient = Patient::where('clinic_id', $clinicId)
            ->where('patient_id', 'like', $clinicCode . '%')
            ->orderBy('patient_id', 'desc')
            ->first();

        if ($lastPatient) {
            $lastNumber = (int) substr($lastPatient->patient_id, 3);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $clinicCode . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Get user permissions
     */
    private function getUserPermissions($role)
    {
        $permissions = [
            'superadmin' => [
                'manage_users', 'manage_clinics', 'manage_licenses', 'view_analytics',
                'manage_settings', 'view_activity_logs', 'manage_roles', 'manage_permissions',
                'view_system_health', 'manage_backups', 'view_financial_reports'
            ],
            'admin' => [
                'manage_staff', 'manage_doctors', 'view_appointments', 'view_patients',
                'view_reports', 'manage_settings', 'view_analytics', 'manage_clinic_settings',
                'view_financial_reports', 'manage_rooms', 'manage_schedules'
            ],
            'doctor' => [
                'work_on_queue', 'view_appointments', 'manage_prescriptions', 'view_medical_records',
                'view_patients', 'view_analytics', 'manage_encounters', 'view_lab_results',
                'manage_treatment_plans', 'view_patient_history', 'manage_soap_notes'
            ],
            'receptionist' => [
                'search_patients', 'manage_appointments', 'manage_queue', 'register_patients',
                'view_encounters', 'view_reports', 'manage_patient_info', 'view_appointments',
                'manage_check_in', 'view_patient_history', 'manage_insurance'
            ],
            'patient' => [
                'book_appointments', 'view_medical_records', 'view_prescriptions', 'view_lab_results',
                'view_appointments', 'update_profile', 'download_documents', 'view_billing',
                'manage_notifications', 'view_insurance', 'schedule_follow_ups'
            ],
            'medrep' => [
                'manage_products', 'schedule_meetings', 'track_interactions', 'manage_doctors',
                'view_analytics', 'manage_samples', 'view_meeting_history', 'manage_territory',
                'view_performance_metrics', 'manage_marketing_materials', 'track_commitments'
            ]
        ];

        return $permissions[$role] ?? [];
    }
}
