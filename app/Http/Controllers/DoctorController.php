<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Doctor;
use App\Models\User;
use App\Models\Role;
use App\Models\UserClinicRole;
use App\Models\Clinic;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class DoctorController extends Controller
{
    /**
     * Display the doctor management page
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        // Get user's clinic
        $userClinicRole = $user->userClinicRoles()->with(['clinic', 'role'])->first();

        // Get available specializations
        $specializations = $this->getSpecializations();

        if (!$userClinicRole) {
            return Inertia::render('admin/doctors', [
                'doctors' => [],
                'specializations' => $specializations,
                'permissions' => [],
            ]);
        }

        $clinicId = $userClinicRole->clinic_id;

        // Get doctors for this clinic
        $doctors = $this->getDoctors($clinicId);

        // Get user permissions
        $permissions = $this->getUserPermissions($userClinicRole->role->name ?? 'user');

        return Inertia::render('admin/doctors', [
            'doctors' => $doctors,
            'specializations' => $specializations,
            'permissions' => $permissions,
        ]);
    }

    /**
     * Store a newly created doctor
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:20',
            'specialization' => 'required|string|max:255',
            'license' => 'required|string|max:255|unique:doctors,license_number',
            'status' => 'required|string|in:Active,On Leave,Inactive',
            'experience' => 'required|string|max:255',
            'education' => 'nullable|string|max:1000',
            'certifications' => 'nullable|string|max:1000',
            'address' => 'nullable|string|max:500',
            'emergency_contact' => 'nullable|string|max:255',
            'emergency_phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:1000',
            'consultation_fee' => 'nullable|numeric|min:0',
            'availability' => 'nullable|array',
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

            // Create new user
            $newUser = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make('password123'), // Default password
                'is_active' => $request->status === 'Active',
            ]);

            // Create doctor record
            $doctor = Doctor::create([
                'user_id' => $newUser->id,
                'clinic_id' => $clinicId,
                'specialization' => $request->specialization,
                'license_number' => $request->license,
                'experience' => $request->experience,
                'education' => $request->education,
                'certifications' => $request->certifications,
                'address' => $request->address,
                'emergency_contact' => $request->emergency_contact,
                'emergency_phone' => $request->emergency_phone,
                'notes' => $request->notes,
                'consultation_fee' => $request->consultation_fee,
                'availability' => $request->availability,
                'is_active' => $request->status === 'Active',
            ]);

            // Get doctor role
            $role = Role::where('name', 'doctor')->first();

            // Assign role to clinic
            UserClinicRole::create([
                'user_id' => $newUser->id,
                'clinic_id' => $clinicId,
                'role_id' => $role->id,
                'department' => $request->specialization,
                'status' => $request->status,
                'join_date' => Carbon::now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Doctor added successfully',
                'doctor' => $this->getDoctor($doctor->id)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add doctor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified doctor
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($id)
            ],
            'phone' => 'required|string|max:20',
            'specialization' => 'required|string|max:255',
            'license' => [
                'required',
                'string',
                'max:255',
                Rule::unique('doctors', 'license_number')->ignore($id)
            ],
            'status' => 'required|string|in:Active,On Leave,Inactive',
            'experience' => 'required|string|max:255',
            'education' => 'nullable|string|max:1000',
            'certifications' => 'nullable|string|max:1000',
            'address' => 'nullable|string|max:500',
            'emergency_contact' => 'nullable|string|max:255',
            'emergency_phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:1000',
            'consultation_fee' => 'nullable|numeric|min:0',
            'availability' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $doctor = Doctor::findOrFail($id);
            $user = $doctor->user;

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Doctor user not found'
                ], 404);
            }

            // Update user
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'is_active' => $request->status === 'Active',
            ]);

            // Update doctor
            $doctor->update([
                'specialization' => $request->specialization,
                'license_number' => $request->license,
                'experience' => $request->experience,
                'education' => $request->education,
                'certifications' => $request->certifications,
                'address' => $request->address,
                'emergency_contact' => $request->emergency_contact,
                'emergency_phone' => $request->emergency_phone,
                'notes' => $request->notes,
                'consultation_fee' => $request->consultation_fee,
                'availability' => $request->availability,
                'is_active' => $request->status === 'Active',
            ]);

            // Update clinic role
            $userClinicRole = $user->userClinicRoles()->where('clinic_id', $doctor->clinic_id)->first();
            if ($userClinicRole) {
                $userClinicRole->update([
                    'department' => $request->specialization,
                    'status' => $request->status,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Doctor updated successfully',
                'doctor' => $this->getDoctor($doctor->id)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update doctor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified doctor
     */
    public function destroy($id)
    {
        try {
            $doctor = Doctor::findOrFail($id);
            $user = $doctor->user;

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Doctor user not found'
                ], 404);
            }

            // Soft delete - just deactivate
            $user->update(['is_active' => false]);
            $doctor->update(['is_active' => false]);

            // Update clinic role status
            $userClinicRole = $user->userClinicRoles()->where('clinic_id', $doctor->clinic_id)->first();
            if ($userClinicRole) {
                $userClinicRole->update(['status' => 'Inactive']);
            }

            return response()->json([
                'success' => true,
                'message' => 'Doctor deactivated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to deactivate doctor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get doctor details
     */
    public function show($id)
    {
        try {
            $doctor = $this->getDoctor($id);

            if (!$doctor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Doctor not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Doctor retrieved successfully',
                'doctor' => $doctor
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve doctor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get doctors for a clinic
     */
    private function getDoctors($clinicId)
    {
        return Doctor::where('clinic_id', $clinicId)
            ->with(['user', 'clinic'])
            ->get()
            ->map(function ($doctor) {
                return [
                    'id' => $doctor->id,
                    'name' => $doctor->user->name,
                    'email' => $doctor->user->email,
                    'phone' => $doctor->user->phone,
                    'specialization' => $doctor->specialization,
                    'license' => $doctor->license_number,
                    'status' => $doctor->is_active ? 'Active' : 'Inactive',
                    'experience' => $doctor->experience,
                    'education' => $doctor->education,
                    'certifications' => $doctor->certifications,
                    'address' => $doctor->address,
                    'emergency_contact' => $doctor->emergency_contact,
                    'emergency_phone' => $doctor->emergency_phone,
                    'notes' => $doctor->notes,
                    'consultation_fee' => $doctor->consultation_fee,
                    'availability' => $doctor->availability,
                    'patients' => 0, // TODO: Calculate from appointments
                    'next_appointment' => null, // TODO: Get from appointments
                    'rating' => 4.5, // TODO: Calculate from reviews
                    'created_at' => $doctor->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $doctor->updated_at->format('Y-m-d H:i:s'),
                ];
            });
    }

    /**
     * Get a single doctor
     */
    private function getDoctor($doctorId)
    {
        $doctor = Doctor::with(['user', 'clinic'])->findOrFail($doctorId);

        return [
            'id' => $doctor->id,
            'name' => $doctor->user->name,
            'email' => $doctor->user->email,
            'phone' => $doctor->user->phone,
            'specialization' => $doctor->specialization,
            'license' => $doctor->license_number,
            'status' => $doctor->is_active ? 'Active' : 'Inactive',
            'experience' => $doctor->experience,
            'education' => $doctor->education,
            'certifications' => $doctor->certifications,
            'address' => $doctor->address,
            'emergency_contact' => $doctor->emergency_contact,
            'emergency_phone' => $doctor->emergency_phone,
            'notes' => $doctor->notes,
            'consultation_fee' => $doctor->consultation_fee,
            'availability' => $doctor->availability,
            'patients' => 0, // TODO: Calculate from appointments
            'next_appointment' => null, // TODO: Get from appointments
            'rating' => 4.5, // TODO: Calculate from reviews
            'created_at' => $doctor->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $doctor->updated_at->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Get available specializations
     */
    private function getSpecializations()
    {
        return [
            'Cardiology',
            'Pediatrics',
            'Dermatology',
            'Orthopedics',
            'Neurology',
            'Internal Medicine',
            'Emergency Medicine',
            'Radiology',
            'Pathology',
            'Anesthesiology',
            'Gynecology',
            'Oncology',
            'Psychiatry',
            'Urology',
            'Ophthalmology',
            'ENT',
            'General Surgery',
            'Plastic Surgery',
            'Vascular Surgery',
            'Neurosurgery',
        ];
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
