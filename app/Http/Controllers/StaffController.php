<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Clinic;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class StaffController extends \Illuminate\Routing\Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('verified');
    }

    /**
     * Get user's clinic and role information
     */
    protected function getUserClinicRole(Request $request)
    {
        $user = $request->user();
        
        if (!$user) {
            return null;
        }

        return $user->userClinicRoles()->with(['clinic', 'role'])->first();
    }

    /**
     * Check if user has permission in clinic
     */
    protected function hasPermissionInClinic(Request $request, string $permission, ?int $clinicId = null): bool
    {
        $user = $request->user();
        
        if (!$user) {
            return false;
        }

        $targetClinicId = $clinicId ?? $this->getUserClinicRole($request)?->clinic_id;
        
        if (!$targetClinicId) {
            return false;
        }

        return $user->hasAnyPermissionInClinic([$permission], $targetClinicId);
    }

    /**
     * Log web request with context
     */
    protected function logWebRequest(string $action, array $context = []): void
    {
        Log::info("Web Request: {$action}", array_merge([
            'user_id' => request()->user()?->id,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'endpoint' => request()->path(),
            'method' => request()->method(),
            'timestamp' => now()->toISOString(),
        ], $context));
    }

    /**
     * Log security event
     */
    protected function logSecurityEvent(string $event, array $context = []): void
    {
        Log::warning("Security Event: {$event}", array_merge([
            'user_id' => request()->user()?->id,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'endpoint' => request()->path(),
            'method' => request()->method(),
            'timestamp' => now()->toISOString(),
        ], $context));
    }

    /**
     * Handle exceptions with proper logging
     */
    protected function handleException(\Exception $e, string $context = 'Web Controller'): void
    {
        Log::error("{$context} Exception: " . $e->getMessage(), [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
            'user_id' => request()->user()?->id,
            'request_url' => request()->fullUrl(),
            'request_method' => request()->method(),
            'request_data' => request()->except(['password', 'password_confirmation', 'token'])
        ]);
    }

    /**
     * Validate and sanitize request data
     */
    protected function validateAndSanitize(Request $request, array $rules, array $messages = []): array
    {
        $validator = Validator::make($request->all(), $rules, $messages);
        
        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        }
        
        $validated = $validator->validated();
        
        // Sanitize input data to prevent XSS and other attacks
        $sanitized = [];
        foreach ($validated as $key => $value) {
            if (is_string($value)) {
                // Remove potentially dangerous characters and HTML tags
                $sanitized[$key] = strip_tags(trim($value));
            } elseif (is_array($value)) {
                $sanitized[$key] = $this->sanitizeInput($value);
            } else {
                $sanitized[$key] = $value;
            }
        }
        
        return $sanitized;
    }

    /**
     * Sanitize input data to prevent XSS and other attacks
     */
    protected function sanitizeInput(array $data): array
    {
        $sanitized = [];
        
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                // Remove potentially dangerous characters and HTML tags
                $sanitized[$key] = strip_tags(trim($value));
            } elseif (is_array($value)) {
                $sanitized[$key] = $this->sanitizeInput($value);
            } else {
                $sanitized[$key] = $value;
            }
        }
        
        return $sanitized;
    }

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

            // Check staff management access
            $this->requireStaffManagementAccess($request, 'index');
            
            $userClinicRole = $this->getUserClinicRole($request);
            $clinicId = $userClinicRole->clinic_id;

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

            // Add security context for frontend
            $securityContext = [
                'can_create_staff' => $this->hasStaffManagementAccess($request),
                'can_edit_staff' => $this->hasStaffManagementAccess($request),
                'can_delete_staff' => $this->hasStaffManagementAccess($request),
                'current_user_role' => $userClinicRole->role->name,
                'is_superadmin' => $userClinicRole->role->name === 'superadmin',
            ];

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
                'security' => $securityContext,
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
            
            if (!$user) {
                $this->logSecurityEvent('Unauthenticated staff show access attempt');
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
                $this->logSecurityEvent('Unauthorized staff show access attempt - no clinic role');
                return redirect()->route('dashboard')->with('error', 'You do not have access to this clinic.');
            }

            // Check staff management access
            $this->requireStaffManagementAccess($request, 'show');
            
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
            $this->logWebRequest('Staff Management Access', ['action' => 'store']);
            
            $currentUser = $request->user();
            
            if (!$currentUser) {
                $this->logSecurityEvent('Unauthenticated staff store access attempt');
                return redirect()->route('login');
            }

            // Get current clinic and check permissions
            $currentClinic = $currentUser->getCurrentClinic();
            if (!$currentClinic) {
                return redirect()->back()->with('error', 'No clinic selected');
            }

            // Check staff management access
            $this->requireStaffManagementAccess($request, 'store');

            // Sanitize and validate input
            $validatedData = $this->validateAndSanitize($request, [
                'name' => 'required|string|max:255|regex:/^[a-zA-Z\s\-\.\']+$/',
                'email' => 'required|email|unique:users,email|max:255',
                'phone' => 'nullable|string|max:20|regex:/^[\+]?[0-9\s\-\(\)]+$/',
                'role' => 'required|string|exists:roles,name',
                'department' => 'required|string|max:100',
                'status' => 'required|string|in:Active,On Leave,Inactive',
                'address' => 'nullable|string|max:500',
                'emergency_contact' => 'nullable|string|max:255|regex:/^[a-zA-Z\s\-\.\']+$/',
                'emergency_phone' => 'nullable|string|max:20|regex:/^[\+]?[0-9\s\-\(\)]+$/',
                'notes' => 'nullable|string|max:1000',
            ], [
                'name.regex' => 'Name can only contain letters, spaces, hyphens, dots, and apostrophes.',
                'phone.regex' => 'Phone number format is invalid.',
                'emergency_contact.regex' => 'Emergency contact name can only contain letters, spaces, hyphens, dots, and apostrophes.',
                'emergency_phone.regex' => 'Emergency phone number format is invalid.',
                'role.exists' => 'Selected role does not exist.',
            ]);
            
            $clinicId = $currentClinic->id;

            // Find role by name
            $role = Role::where('name', $validatedData['role'])->first();
            if (!$role) {
                return redirect()->back()->with('error', 'Invalid role selected')->withInput();
            }

            // Prevent creating superadmin role unless current user is superadmin
            if ($role->name === 'superadmin' && !$currentUser->hasRole('superadmin')) {
                $this->logSecurityEvent('Unauthorized superadmin creation attempt', [
                    'user_id' => $currentUser->id,
                    'attempted_role' => $role->name
                ]);
                return redirect()->back()->with('error', 'You cannot create superadmin users.')->withInput();
            }

            // Generate a secure temporary password
            $tempPassword = $this->generateSecurePassword();

            // Create user
            $user = User::create([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'phone' => $validatedData['phone'],
                'password' => Hash::make($tempPassword),
                'is_active' => $validatedData['status'] === 'Active',
                'email_verified_at' => now(),
            ]);

            // Assign to clinic with role and additional staff information
            $user->clinics()->attach($clinicId, [
                'role_id' => $role->id,
                'department' => $validatedData['department'],
                'assigned_by' => $currentUser->id,
                'assigned_at' => now(),
                'join_date' => now(),
                'address' => $validatedData['address'],
                'emergency_contact' => $validatedData['emergency_contact'],
                'emergency_phone' => $validatedData['emergency_phone'],
                'notes' => $validatedData['notes'],
            ]);

            // Log successful staff creation
            $this->logWebRequest('Staff Member Created', [
                'staff_id' => $user->id,
                'staff_name' => $user->name,
                'staff_email' => $user->email,
                'role' => $role->name,
                'department' => $validatedData['department']
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
            $this->logWebRequest('Staff Management Access', ['action' => 'update', 'staff_id' => $id]);
            
            $currentUser = $request->user();
            
            if (!$currentUser) {
                $this->logSecurityEvent('Unauthenticated staff update access attempt');
                return redirect()->route('login');
            }

            // Get current clinic and check permissions
            $currentClinic = $currentUser->getCurrentClinic();
            if (!$currentClinic) {
                return redirect()->back()->with('error', 'No clinic selected');
            }

            // Check staff management access
            $this->requireStaffManagementAccess($request, 'update');

            // Sanitize and validate input
            $validatedData = $this->validateAndSanitize($request, [
                'name' => 'required|string|max:255|regex:/^[a-zA-Z\s\-\.\']+$/',
                'email' => 'required|email|unique:users,email,' . $id . '|max:255',
                'phone' => 'nullable|string|max:20|regex:/^[\+]?[0-9\s\-\(\)]+$/',
                'role' => 'required|string|exists:roles,name',
                'department' => 'required|string|max:100',
                'status' => 'required|string|in:Active,On Leave,Inactive',
                'address' => 'nullable|string|max:500',
                'emergency_contact' => 'nullable|string|max:255|regex:/^[a-zA-Z\s\-\.\']+$/',
                'emergency_phone' => 'nullable|string|max:20|regex:/^[\+]?[0-9\s\-\(\)]+$/',
                'notes' => 'nullable|string|max:1000',
            ], [
                'name.regex' => 'Name can only contain letters, spaces, hyphens, dots, and apostrophes.',
                'phone.regex' => 'Phone number format is invalid.',
                'emergency_contact.regex' => 'Emergency contact name can only contain letters, spaces, hyphens, dots, and apostrophes.',
                'emergency_phone.regex' => 'Emergency phone number format is invalid.',
                'role.exists' => 'Selected role does not exist.',
            ]);
            
            $clinicId = $currentClinic->id;

            // Find user and check if they belong to current clinic
            $user = User::whereHas('clinics', function ($q) use ($clinicId) {
                $q->where('clinic_id', $clinicId);
            })->findOrFail($id);

            // Find role by name
            $role = Role::where('name', $validatedData['role'])->first();
            if (!$role) {
                return redirect()->back()->with('error', 'Invalid role selected')->withInput();
            }

            // Prevent role escalation unless current user is superadmin
            if ($role->name === 'superadmin' && !$currentUser->hasRole('superadmin')) {
                $this->logSecurityEvent('Unauthorized role escalation attempt', [
                    'user_id' => $currentUser->id,
                    'target_user_id' => $user->id,
                    'attempted_role' => $role->name
                ]);
                return redirect()->back()->with('error', 'You cannot assign superadmin role.')->withInput();
            }

            // Prevent users from modifying their own role to a higher privilege
            if ($user->id === $currentUser->id && $role->name === 'superadmin' && !$currentUser->hasRole('superadmin')) {
                $this->logSecurityEvent('Self-role escalation attempt', [
                    'user_id' => $currentUser->id,
                    'attempted_role' => $role->name
                ]);
                return redirect()->back()->with('error', 'You cannot change your own role to superadmin.')->withInput();
            }

            // Update user data
            $user->update([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'phone' => $validatedData['phone'],
                'is_active' => $validatedData['status'] === 'Active',
            ]);

            // Update clinic role, department, and additional staff information
            $user->clinics()->updateExistingPivot($clinicId, [
                'role_id' => $role->id,
                'department' => $validatedData['department'],
                'updated_by' => $currentUser->id,
                'updated_at' => now(),
                'address' => $validatedData['address'],
                'emergency_contact' => $validatedData['emergency_contact'],
                'emergency_phone' => $validatedData['emergency_phone'],
                'notes' => $validatedData['notes'],
            ]);

            // Log successful staff update
            $this->logWebRequest('Staff Member Updated', [
                'staff_id' => $user->id,
                'staff_name' => $user->name,
                'staff_email' => $user->email,
                'new_role' => $role->name,
                'new_department' => $validatedData['department']
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
            $this->logWebRequest('Staff Management Access', ['action' => 'destroy', 'staff_id' => $id]);
            
            $currentUser = $request->user();
            
            if (!$currentUser) {
                $this->logSecurityEvent('Unauthenticated staff destroy access attempt');
                return redirect()->route('login');
            }

            // Get current clinic and check permissions
            $currentClinic = $currentUser->getCurrentClinic();
            if (!$currentClinic) {
                return redirect()->back()->with('error', 'No clinic selected');
            }

            // Check staff management access
            $this->requireStaffManagementAccess($request, 'destroy');
            
            $clinicId = $currentClinic->id;

            // Find user and check if they belong to current clinic
            $user = User::whereHas('clinics', function ($q) use ($clinicId) {
                $q->where('clinic_id', $clinicId);
            })->findOrFail($id);

            // Check if user is trying to remove themselves
            if ($user->id === $currentUser->id) {
                $this->logSecurityEvent('Self-removal attempt', [
                    'user_id' => $currentUser->id
                ]);
                return redirect()->back()->with('error', 'You cannot remove yourself from the clinic');
            }

            // Prevent removal of superadmin users unless current user is superadmin
            if ($user->hasRole('superadmin') && !$currentUser->hasRole('superadmin')) {
                $this->logSecurityEvent('Unauthorized superadmin removal attempt', [
                    'user_id' => $currentUser->id,
                    'target_user_id' => $user->id
                ]);
                return redirect()->back()->with('error', 'You cannot remove superadmin users.');
            }

            // Check if this is the last admin in the clinic
            $adminCount = $currentClinic->userClinicRoles()
                ->whereHas('role', function ($q) {
                    $q->where('name', 'admin');
                })
                ->count();

            if ($user->hasRole('admin') && $adminCount <= 1) {
                $this->logSecurityEvent('Last admin removal attempt', [
                    'user_id' => $currentUser->id,
                    'target_user_id' => $user->id,
                    'clinic_id' => $clinicId
                ]);
                return redirect()->back()->with('error', 'Cannot remove the last admin from the clinic.');
            }

            // Log the removal before executing
            $this->logWebRequest('Staff Member Removal', [
                'staff_id' => $user->id,
                'staff_name' => $user->name,
                'staff_email' => $user->email,
                'clinic_id' => $clinicId
            ]);

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
     * Generate a secure temporary password
     */
    private function generateSecurePassword(): string
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
        $password = '';
        $length = 12;
        
        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[random_int(0, strlen($characters) - 1)];
        }
        
        return $password;
    }

    /**
     * Check if user has staff management access
     */
    private function hasStaffManagementAccess(Request $request): bool
    {
        $user = $request->user();
        
        if (!$user) {
            return false;
        }

        $userClinicRole = $this->getUserClinicRole($request);
        if (!$userClinicRole) {
            return false;
        }

        // Check for admin role, superadmin role, or staff.view permission
        return $userClinicRole->role->name === 'admin' || 
               $userClinicRole->role->name === 'superadmin' ||
               $this->hasPermissionInClinic($request, 'staff.view');
    }

    /**
     * Require staff management access or redirect with error
     */
    private function requireStaffManagementAccess(Request $request, string $action = 'access'): void
    {
        if (!$this->hasStaffManagementAccess($request)) {
            $user = $request->user();
            $userClinicRole = $this->getUserClinicRole($request);
            
            $this->logSecurityEvent("Unauthorized staff {$action} attempt", [
                'user_id' => $user?->id,
                'role' => $userClinicRole?->role->name ?? 'unknown',
                'action' => $action
            ]);
            
            abort(403, 'You do not have permission to manage staff.');
        }
    }

    /**
     * Get user permissions based on role
     */
    private function getUserPermissions($role)
    {
        $permissions = [
            'superadmin' => [
                'staff.view', 'staff.create', 'staff.edit', 'staff.delete',
                'manage_users', 'manage_clinics', 'manage_licenses', 'view_analytics',
                'manage_settings', 'view_activity_logs', 'manage_roles', 'manage_permissions',
                'view_system_health', 'manage_backups', 'view_financial_reports'
            ],
            'admin' => [
                'staff.view', 'staff.create', 'staff.edit', 'staff.delete',
                'manage_doctors', 'view_appointments', 'view_patients',
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