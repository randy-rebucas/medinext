<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\User;
use App\Models\Role;
use App\Models\UserClinicRole;
use App\Models\Clinic;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class StaffController extends Controller
{
    /**
     * Display the staff management page
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        // Get user's clinic
        $userClinicRole = $user->userClinicRoles()->with(['clinic', 'role'])->first();

        // Get available roles (always available regardless of user clinic role)
        $roles = $this->getAvailableRoles();

        // Get departments (always available)
        $departments = $this->getDepartments();

        if (!$userClinicRole) {
            return Inertia::render('admin/staff', [
                'staff' => [],
                'roles' => $roles,
                'departments' => $departments,
                'permissions' => [],
            ]);
        }

        $clinicId = $userClinicRole->clinic_id;

        // Get staff members for this clinic
        $staff = $this->getStaffMembers($clinicId);

        // Get user permissions
        $permissions = $this->getUserPermissions($userClinicRole->role->name ?? 'user');

        return Inertia::render('admin/staff', [
            'staff' => $staff,
            'roles' => $roles,
            'departments' => $departments,
            'permissions' => $permissions,
        ]);
    }

    /**
     * Store a newly created staff member
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:20',
            'role' => 'required|string|exists:roles,name',
            'department' => 'required|string|max:255',
            'status' => 'required|string|in:Active,On Leave,Inactive',
            'address' => 'nullable|string|max:500',
            'emergency_contact' => 'nullable|string|max:255',
            'emergency_phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Get user from request (works for both web and API)
            $user = $request->user() ?? $request->get('authenticated_user');

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

            // Get role
            $role = Role::where('name', $request->role)->first();

            // Assign role to clinic
            UserClinicRole::create([
                'user_id' => $newUser->id,
                'clinic_id' => $clinicId,
                'role_id' => $role->id,
                'department' => $request->department,
                'status' => $request->status,
                'address' => $request->address,
                'emergency_contact' => $request->emergency_contact,
                'emergency_phone' => $request->emergency_phone,
                'notes' => $request->notes,
                'join_date' => Carbon::now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Staff member added successfully',
                'staff' => $this->getStaffMember($newUser->id)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add staff member: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified staff member
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
            'role' => 'required|string|exists:roles,name',
            'department' => 'required|string|max:255',
            'status' => 'required|string|in:Active,On Leave,Inactive',
            'address' => 'nullable|string|max:500',
            'emergency_contact' => 'nullable|string|max:255',
            'emergency_phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = User::findOrFail($id);
            $userClinicRole = $user->userClinicRoles()->first();

            if (!$userClinicRole) {
                return response()->json([
                    'success' => false,
                    'message' => 'Staff member not found in clinic'
                ], 404);
            }

            // Update user
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'is_active' => $request->status === 'Active',
            ]);

            // Get role
            $role = Role::where('name', $request->role)->first();

            // Update clinic role
            $userClinicRole->update([
                'role_id' => $role->id,
                'department' => $request->department,
                'status' => $request->status,
                'address' => $request->address,
                'emergency_contact' => $request->emergency_contact,
                'emergency_phone' => $request->emergency_phone,
                'notes' => $request->notes,
            ]);

            // Refresh the models to get updated data
            $user->refresh();
            $userClinicRole->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Staff member updated successfully',
                'staff' => $this->getStaffMember($user->id)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update staff member: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified staff member
     */
    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);
            $userClinicRole = $user->userClinicRoles()->first();

            if (!$userClinicRole) {
                return response()->json([
                    'success' => false,
                    'message' => 'Staff member not found in clinic'
                ], 404);
            }

            // Soft delete - just deactivate
            $user->update(['is_active' => false]);
            $userClinicRole->update(['status' => 'Inactive']);

            return response()->json([
                'success' => true,
                'message' => 'Staff member deactivated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to deactivate staff member: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get staff members for a clinic
     */
    private function getStaffMembers($clinicId)
    {
        return User::whereHas('userClinicRoles', function ($query) use ($clinicId) {
            $query->where('clinic_id', $clinicId);
        })
        ->with(['userClinicRoles' => function ($query) use ($clinicId) {
            $query->where('clinic_id', $clinicId)->with('role');
        }])
        ->get()
        ->map(function ($user) {
            $userClinicRole = $user->userClinicRoles->first();
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $userClinicRole->role->name ?? 'Unknown',
                'department' => $userClinicRole->department ?? 'Unknown',
                'status' => $userClinicRole->status ?? 'Unknown',
                'join_date' => $userClinicRole->join_date ? $userClinicRole->join_date->format('Y-m-d') : null,
                'last_active' => $user->updated_at->diffForHumans(),
                'address' => $userClinicRole->address,
                'emergency_contact' => $userClinicRole->emergency_contact,
                'emergency_phone' => $userClinicRole->emergency_phone,
                'notes' => $userClinicRole->notes,
                'is_active' => $user->is_active,
            ];
        });
    }

    /**
     * Get a single staff member
     */
    private function getStaffMember($userId)
    {
        // Get fresh data from database
        $user = User::with(['userClinicRoles' => function ($query) {
            $query->with('role');
        }])->findOrFail($userId);

        $userClinicRole = $user->userClinicRoles->first();

        if (!$userClinicRole) {
            return null;
        }

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'role' => $userClinicRole->role->name ?? 'Unknown',
            'department' => $userClinicRole->department ?? 'Unknown',
            'status' => $userClinicRole->status ?? 'Unknown',
            'join_date' => $userClinicRole->join_date ? $userClinicRole->join_date->format('Y-m-d') : null,
            'last_active' => $user->updated_at->diffForHumans(),
            'address' => $userClinicRole->address,
            'emergency_contact' => $userClinicRole->emergency_contact,
            'emergency_phone' => $userClinicRole->emergency_phone,
            'notes' => $userClinicRole->notes,
            'is_active' => $user->is_active,
        ];
    }

    /**
     * Get available roles
     */
    private function getAvailableRoles()
    {
        return Role::all(['id', 'name', 'description'])->map(function ($role) {
            return [
                'id' => $role->id,
                'name' => $role->name,
                'description' => $role->description,
            ];
        });
    }

    /**
     * Get departments
     */
    private function getDepartments()
    {
        return [
            'Cardiology',
            'Pediatrics',
            'Emergency',
            'Dermatology',
            'Front Desk',
            'Administration',
            'Laboratory',
            'Pharmacy',
            'Radiology',
            'Surgery',
            'Internal Medicine',
            'Gynecology',
            'Orthopedics',
            'Neurology',
            'Oncology',
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
