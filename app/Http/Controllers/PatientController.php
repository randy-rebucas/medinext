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
        $this->logWebRequest('Patient Management Access', ['action' => 'index']);
        
        $user = $request->user();
        $userClinicRole = $this->getUserClinicRole($request);

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
        $rules = [
            'first_name' => 'required|string|max:255|regex:/^[a-zA-Z\s\-\'\.]+$/',
            'last_name' => 'required|string|max:255|regex:/^[a-zA-Z\s\-\'\.]+$/',
            'dob' => 'required|date|before:today|after:1900-01-01',
            'sex' => 'required|string|in:Male,Female,Other',
            'contact' => 'required|array',
            'contact.email' => 'required|email:rfc,dns|max:255',
            'contact.phone' => 'required|string|max:20|regex:/^[\+]?[0-9\s\-\(\)]+$/',
            'contact.address' => 'nullable|string|max:500',
            'contact.city' => 'nullable|string|max:100|regex:/^[a-zA-Z\s\-\'\.]+$/',
            'contact.state' => 'nullable|string|max:100|regex:/^[a-zA-Z\s\-\'\.]+$/',
            'contact.zip_code' => 'nullable|string|max:20|regex:/^[0-9\-\s]+$/',
            'emergency_contact' => 'nullable|array',
            'emergency_contact.name' => 'nullable|string|max:255|regex:/^[a-zA-Z\s\-\'\.]+$/',
            'emergency_contact.phone' => 'nullable|string|max:20|regex:/^[\+]?[0-9\s\-\(\)]+$/',
            'emergency_contact.relationship' => 'nullable|string|max:100|regex:/^[a-zA-Z\s\-\'\.]+$/',
            'insurance' => 'nullable|array',
            'insurance.provider' => 'nullable|string|max:255',
            'insurance.policy_number' => 'nullable|string|max:100|alpha_num',
            'insurance.group_number' => 'nullable|string|max:100|alpha_num',
            'allergies' => 'nullable|array',
            'medical_history' => 'nullable|string|max:2000',
            'medications' => 'nullable|string|max:2000',
            'notes' => 'nullable|string|max:2000',
        ];

        $messages = [
            'first_name.regex' => 'First name can only contain letters, spaces, hyphens, apostrophes, and periods.',
            'last_name.regex' => 'Last name can only contain letters, spaces, hyphens, apostrophes, and periods.',
            'dob.after' => 'Date of birth must be after 1900.',
            'contact.email.email' => 'Please provide a valid email address.',
            'contact.phone.regex' => 'Phone number format is invalid.',
            'contact.city.regex' => 'City can only contain letters, spaces, hyphens, apostrophes, and periods.',
            'contact.state.regex' => 'State can only contain letters, spaces, hyphens, apostrophes, and periods.',
            'contact.zip_code.regex' => 'ZIP code format is invalid.',
            'emergency_contact.name.regex' => 'Emergency contact name can only contain letters, spaces, hyphens, apostrophes, and periods.',
            'emergency_contact.phone.regex' => 'Emergency contact phone format is invalid.',
            'emergency_contact.relationship.regex' => 'Relationship can only contain letters, spaces, hyphens, apostrophes, and periods.',
            'insurance.policy_number.alpha_num' => 'Policy number can only contain letters and numbers.',
            'insurance.group_number.alpha_num' => 'Group number can only contain letters and numbers.',
        ];

        try {
            $validatedData = $this->validateAndSanitize($request, $rules, $messages);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        }

        try {
            $this->logWebRequest('Create Patient', ['action' => 'store']);
            
            // Get user from request
            $user = $request->user();

            if (!$user) {
                $this->logSecurityEvent('Unauthenticated patient creation attempt');
                return redirect()->back()->with('error', 'User not authenticated');
            }

            $userClinicRole = $this->getUserClinicRole($request);

            if (!$userClinicRole) {
                $this->logSecurityEvent('Unauthorized clinic access attempt', ['user_id' => $user->id]);
                return redirect()->back()->with('error', 'User does not have clinic access');
            }

            $clinicId = $userClinicRole->clinic_id;

            // Generate patient ID
            $patientId = $this->generatePatientId($clinicId);

            // Create patient
            $patient = Patient::create([
                'clinic_id' => $clinicId,
                'patient_id' => $patientId,
                'first_name' => $validatedData['first_name'],
                'last_name' => $validatedData['last_name'],
                'dob' => $validatedData['dob'],
                'sex' => $validatedData['sex'],
                'contact' => $validatedData['contact'],
                'emergency_contact' => $validatedData['emergency_contact'] ?? null,
                'insurance' => $validatedData['insurance'] ?? null,
                'allergies' => $validatedData['allergies'] ?? null,
                'medical_history' => $validatedData['medical_history'] ?? null,
                'medications' => $validatedData['medications'] ?? null,
                'notes' => $validatedData['notes'] ?? null,
            ]);

            return redirect()->route('admin.patients')->with('success', 'Patient created successfully');

        } catch (\Exception $e) {
            $this->handleException($e, 'PatientController::store');
            return redirect()->back()->with('error', 'Failed to create patient. Please try again.')->withInput();
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
            return redirect()->back()->withErrors($validator->errors())->withInput();
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

            return redirect()->route('admin.patients')->with('success', 'Patient updated successfully');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update patient. Please try again.')->withInput();
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

            return redirect()->route('admin.patients')->with('success', 'Patient deleted successfully');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete patient. Please try again.');
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
     * Get patients for a clinic with caching
     */
    private function getPatients($clinicId)
    {
        $cacheKey = $this->getClinicCacheKey('patients', $clinicId);
        
        return $this->remember($cacheKey, 30, function () use ($clinicId) {
            return Patient::where('clinic_id', $clinicId)
                ->withCount(['appointments', 'encounters', 'prescriptions'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($patient) {
                    // Optimize appointment queries by using a single query with subqueries
                    $appointments = Appointment::where('patient_id', $patient->id)
                        ->selectRaw('
                            MAX(CASE WHEN start_at <= NOW() THEN start_at END) as last_appointment,
                            MIN(CASE WHEN start_at > NOW() THEN start_at END) as next_appointment
                        ')
                        ->first();

                    return [
                        'id' => $patient->id,
                        'patient_id' => $patient->code,
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
                        'last_visit' => $appointments->last_appointment,
                        'next_appointment' => $appointments->next_appointment,
                        'total_visits' => $patient->appointments_count,
                        'total_encounters' => $patient->encounters_count,
                        'total_prescriptions' => $patient->prescriptions_count,
                        'status' => $this->getPatientStatus($patient),
                        'created_at' => $patient->created_at->format('Y-m-d H:i:s'),
                        'updated_at' => $patient->updated_at->format('Y-m-d H:i:s'),
                    ];
                });
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
            'patient_id' => $patient->code,
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
