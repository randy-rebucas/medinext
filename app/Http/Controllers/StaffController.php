<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Clinic;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class StaffController extends Controller
{
    /**
     * Display a listing of staff members
     */
    public function index(Request $request)
    {
        try {
            $this->logWebRequest('Staff Management Access', ['action' => 'index']);
            
            $user = $request->user();
            if (!$user) {
                $this->logSecurityEvent('Unauthenticated staff access attempt');
                return redirect()->route('login');
            }

            // Get current clinic
            $currentClinic = $user->getCurrentClinic();
            if (!$currentClinic) {
                return redirect()->route('dashboard')->with('error', 'No clinic selected. Please select a clinic first.');
            }
            
            // Get user's role in current clinic
            $userClinicRole = $user->userClinicRoles()
                ->where('clinic_id', $currentClinic->id)
                ->with(['clinic', 'role'])
                ->first();
            
            if (!$userClinicRole) {
                return redirect()->route('dashboard')->with('error', 'You do not have access to this clinic.');
            }
            
            $clinicId = $currentClinic->id;

            $query = User::whereHas('clinics', function ($q) use ($clinicId) {
                $q->where('clinic_id', $clinicId);
            })->with(['roles', 'clinics']);

            // Apply filters
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            if ($request->has('role')) {
                $query->whereHas('roles', function ($q) use ($request) {
                    $q->where('name', $request->role);
                });
            }

            if ($request->has('status')) {
                $query->where('is_active', $request->status === 'active');
            }

            $staff = $query->paginate($request->get('per_page', 15));

            // Transform staff data to match frontend expectations
            $transformedStaff = $staff->getCollection()->map(function ($user) {
                $userClinicRole = $user->userClinicRoles->first();
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'role' => $user->roles->first()?->name ?? 'No Role',
                    'department' => $userClinicRole?->department ?? 'General',
                    'status' => $user->is_active ? 'Active' : 'Inactive',
                    'join_date' => $userClinicRole?->join_date?->format('Y-m-d') ?? $user->created_at->format('Y-m-d'),
                    'last_active' => $user->updated_at->format('Y-m-d H:i:s'),
                    'is_active' => $user->is_active,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                    'address' => $userClinicRole?->address ?? '',
                    'emergency_contact' => $userClinicRole?->emergency_contact ?? '',
                    'emergency_phone' => $userClinicRole?->emergency_phone ?? '',
                    'notes' => $userClinicRole?->notes ?? '',
                ];
            });

            // Replace the collection in pagination
            $staff->setCollection($transformedStaff);

            // Get roles for the dropdown
            $roles = Role::where('is_system_role', false)->get();

            // Get departments (you can customize this based on your needs)
            $departments = [
                'General',
                'Administration',
                'Medical',
                'Nursing',
                'Reception',
                'Laboratory',
                'Pharmacy',
                'Maintenance',
                'Security',
                'IT Support'
            ];

            // Get user permissions
            $permissions = $this->getUserPermissions($userClinicRole->role->name ?? 'user');

            return Inertia::render('admin/staff', [
                'staff' => $transformedStaff->toArray(),
                'pagination' => [
                    'current_page' => $staff->currentPage(),
                    'last_page' => $staff->lastPage(),
                    'per_page' => $staff->perPage(),
                    'total' => $staff->total(),
                ],
                'roles' => $roles,
                'departments' => $departments,
                'permissions' => $permissions,
                'filters' => [
                    'search' => $request->search,
                    'role' => $request->role,
                    'status' => $request->status,
                ]
            ]);

        } catch (\Exception $e) {
            $this->handleException($e, 'StaffController::index');
            return redirect()->route('dashboard')->with('error', 'Failed to load staff management. Please try again.');
        }
    }

    /**
     * Display the specified staff member
     */
    public function show(Request $request, int $id): Response
    {
        try {
            $this->logWebRequest('Staff Management Access', ['action' => 'show', 'staff_id' => $id]);
            
            $user = $request->user();
            
            // Get current clinic
            $currentClinic = $user->getCurrentClinic();
            if (!$currentClinic) {
                return redirect()->route('dashboard')->with('error', 'No clinic selected. Please select a clinic first.');
            }
            
            // Get user's role in current clinic
            $userClinicRole = $user->userClinicRoles()
                ->where('clinic_id', $currentClinic->id)
                ->with(['clinic', 'role'])
                ->first();
            
            if (!$userClinicRole) {
                return redirect()->route('dashboard')->with('error', 'You do not have access to this clinic.');
            }
            
            $clinicId = $currentClinic->id;

            // Find staff member in current clinic
            $staff = User::whereHas('clinics', function ($q) use ($clinicId) {
                $q->where('clinic_id', $clinicId);
            })->with(['roles', 'clinics'])->findOrFail($id);

            // Get roles for the dropdown
            $roles = Role::where('is_system_role', false)->get();

            // Get departments (you can customize this based on your needs)
            $departments = [
                'General',
                'Administration',
                'Medical',
                'Nursing',
                'Reception',
                'Laboratory',
                'Pharmacy',
                'Maintenance',
                'Security',
                'IT Support'
            ];

            // Get user permissions
            $permissions = $this->getUserPermissions($userClinicRole->role->name ?? 'user');

            // Transform staff data to match frontend expectations
            $userClinicRole = $staff->userClinicRoles->first();
            $transformedStaff = [
                'id' => $staff->id,
                'name' => $staff->name,
                'email' => $staff->email,
                'phone' => $staff->phone,
                'role' => $staff->roles->first()?->name ?? 'No Role',
                'department' => $userClinicRole?->department ?? 'General',
                'status' => $staff->is_active ? 'Active' : 'Inactive',
                'join_date' => $userClinicRole?->join_date?->format('Y-m-d') ?? $staff->created_at->format('Y-m-d'),
                'last_active' => $staff->updated_at->format('Y-m-d H:i:s'),
                'is_active' => $staff->is_active,
                'created_at' => $staff->created_at,
                'updated_at' => $staff->updated_at,
                'address' => $userClinicRole?->address ?? '',
                'emergency_contact' => $userClinicRole?->emergency_contact ?? '',
                'emergency_phone' => $userClinicRole?->emergency_phone ?? '',
                'notes' => $userClinicRole?->notes ?? '',
            ];

            return Inertia::render('admin/staff', [
                'staff' => [$transformedStaff],
                'selectedStaff' => $transformedStaff,
                'roles' => $roles,
                'departments' => $departments,
                'permissions' => $permissions,
                'filters' => [
                    'search' => '',
                    'role' => '',
                    'status' => '',
                ]
            ]);

        } catch (\Exception $e) {
            $this->handleException($e, 'StaffController::show');
            return redirect()->route('admin.staff')->with('error', 'Staff member not found.');
        }
    }

    /**
     * Store a newly created staff member
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'phone' => 'nullable|string|max:20',
                'role' => 'required|string',
                'department' => 'required|string',
                'status' => 'required|string|in:Active,On Leave,Inactive',
                'address' => 'nullable|string',
                'emergency_contact' => 'nullable|string',
                'emergency_phone' => 'nullable|string',
                'notes' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator->errors())->withInput();
            }

            $currentUser = $request->user();
            
            // Get current clinic
            $currentClinic = $currentUser->getCurrentClinic();
            if (!$currentClinic) {
                return redirect()->back()->with('error', 'No clinic selected');
            }
            
            $clinicId = $currentClinic->id;

            // Find role by name
            $role = Role::where('name', $request->role)->first();
            if (!$role) {
                return redirect()->back()->with('error', 'Invalid role selected')->withInput();
            }

            // Generate a temporary password
            $tempPassword = 'TempPass123!';

            // Create user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($tempPassword),
                'is_active' => $request->status === 'Active',
                'email_verified_at' => now(),
            ]);

            // Assign to clinic with role and additional staff information
            $user->clinics()->attach($clinicId, [
                'role_id' => $role->id,
                'department' => $request->department,
                'assigned_by' => $currentUser->id,
                'assigned_at' => now(),
                'join_date' => now(),
                'address' => $request->address,
                'emergency_contact' => $request->emergency_contact,
                'emergency_phone' => $request->emergency_phone,
                'notes' => $request->notes,
            ]);

            return redirect()->route('admin.staff')->with('success', 'Staff member created successfully');

        } catch (\Exception $e) {
            $this->handleException($e, 'StaffController::store');
            return redirect()->back()->with('error', 'Failed to create staff member. Please try again.')->withInput();
        }
    }

    /**
     * Update the specified staff member
     */
    public function update(Request $request, int $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $id,
                'phone' => 'nullable|string|max:20',
                'role' => 'required|string',
                'department' => 'required|string',
                'status' => 'required|string|in:Active,On Leave,Inactive',
                'address' => 'nullable|string',
                'emergency_contact' => 'nullable|string',
                'emergency_phone' => 'nullable|string',
                'notes' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator->errors())->withInput();
            }

            $currentUser = $request->user();
            
            // Get current clinic
            $currentClinic = $currentUser->getCurrentClinic();
            if (!$currentClinic) {
                return redirect()->back()->with('error', 'No clinic selected');
            }
            
            $clinicId = $currentClinic->id;

            // Find user and check if they belong to current clinic
            $user = User::whereHas('clinics', function ($q) use ($clinicId) {
                $q->where('clinic_id', $clinicId);
            })->findOrFail($id);

            // Find role by name
            $role = Role::where('name', $request->role)->first();
            if (!$role) {
                return redirect()->back()->with('error', 'Invalid role selected')->withInput();
            }

            // Update user data
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'is_active' => $request->status === 'Active',
            ]);

            // Update clinic role, department, and additional staff information
            $user->clinics()->updateExistingPivot($clinicId, [
                'role_id' => $role->id,
                'department' => $request->department,
                'updated_by' => $currentUser->id,
                'updated_at' => now(),
                'address' => $request->address,
                'emergency_contact' => $request->emergency_contact,
                'emergency_phone' => $request->emergency_phone,
                'notes' => $request->notes,
            ]);

            return redirect()->route('admin.staff')->with('success', 'Staff member updated successfully');

        } catch (\Exception $e) {
            $this->handleException($e, 'StaffController::update');
            return redirect()->back()->with('error', 'Failed to update staff member. Please try again.')->withInput();
        }
    }

    /**
     * Remove the specified staff member from clinic
     */
    public function destroy(Request $request, int $id)
    {
        try {
            $currentUser = $request->user();
            
            // Get current clinic
            $currentClinic = $currentUser->getCurrentClinic();
            if (!$currentClinic) {
                return redirect()->back()->with('error', 'No clinic selected');
            }
            
            $clinicId = $currentClinic->id;

            // Find user and check if they belong to current clinic
            $user = User::whereHas('clinics', function ($q) use ($clinicId) {
                $q->where('clinic_id', $clinicId);
            })->findOrFail($id);

            // Check if user is trying to remove themselves
            if ($user->id === Auth::id()) {
                return redirect()->back()->with('error', 'You cannot remove yourself from the clinic');
            }

            // Remove user from clinic
            $user->clinics()->detach($clinicId);

            // If user has no other clinics, deactivate them
            if ($user->clinics()->count() === 0) {
                $user->update(['is_active' => false]);
            }

            return redirect()->route('admin.staff')->with('success', 'Staff member removed from clinic successfully');

        } catch (\Exception $e) {
            $this->handleException($e, 'StaffController::destroy');
            return redirect()->back()->with('error', 'Failed to remove staff member. Please try again.');
        }
    }

    /**
     * Get user permissions based on role
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