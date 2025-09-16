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
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

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
            try {
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
            } catch (\Illuminate\Validation\ValidationException $e) {
                // Handle AJAX requests
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Validation failed',
                        'errors' => $e->errors()
                    ], 422);
                }
                
                return redirect()->back()->withErrors($e->errors())->withInput();
            }
            
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

            // Handle AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Staff member created successfully',
                    'data' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $role->name,
                        'department' => $validatedData['department']
                    ]
                ]);
            }

            return redirect()->route('admin.staff')->with('success', 'Staff member created successfully');

        } catch (\Exception $e) {
            $this->handleException($e, 'StaffController::store');
            
            // Handle AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create staff member. Please try again.',
                    'error' => $e->getMessage()
                ], 500);
            }
            
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
            try {
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
            } catch (\Illuminate\Validation\ValidationException $e) {
                // Handle AJAX requests
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Validation failed',
                        'errors' => $e->errors()
                    ], 422);
                }
                
                return redirect()->back()->withErrors($e->errors())->withInput();
            }
            
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
                'status' => $validatedData['status'],
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

            // Handle AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Staff member updated successfully',
                    'data' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $role->name,
                        'department' => $validatedData['department']
                    ]
                ]);
            }

            return redirect()->route('admin.staff')->with('success', 'Staff member updated successfully');

        } catch (\Exception $e) {
            $this->handleException($e, 'StaffController::update');
            
            // Handle AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update staff member. Please try again.',
                    'error' => $e->getMessage()
                ], 500);
            }
            
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
                
                // Handle AJAX requests
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Authentication required'
                    ], 401);
                }
                
                return redirect()->route('login');
            }

            // Get current clinic and check permissions
            $currentClinic = $currentUser->getCurrentClinic();
            if (!$currentClinic) {
                // Handle AJAX requests
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No clinic selected'
                    ], 422);
                }
                
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
                
                // Handle AJAX requests
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You cannot remove yourself from the clinic'
                    ], 422);
                }
                
                return redirect()->back()->with('error', 'You cannot remove yourself from the clinic');
            }

            // Prevent removal of superadmin users unless current user is superadmin
            if ($user->hasRole('superadmin') && !$currentUser->hasRole('superadmin')) {
                $this->logSecurityEvent('Unauthorized superadmin removal attempt', [
                    'user_id' => $currentUser->id,
                    'target_user_id' => $user->id
                ]);
                
                // Handle AJAX requests
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You cannot remove superadmin users.'
                    ], 422);
                }
                
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
                
                // Handle AJAX requests
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot remove the last admin from the clinic.'
                    ], 422);
                }
                
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

            // Handle AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Staff member removed from clinic successfully'
                ]);
            }

            return redirect()->route('admin.staff')->with('success', 'Staff member removed from clinic successfully');

        } catch (\Exception $e) {
            $this->handleException($e, 'StaffController::destroy');
            
            // Handle AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to remove staff member. Please try again.',
                    'error' => $e->getMessage()
                ], 500);
            }
            
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

    /**
     * Import staff members from CSV/Excel file
     */
    public function import(Request $request)
    {
        try {
            $this->logWebRequest('Staff Import Access', ['action' => 'import']);
            
            $currentUser = $request->user();
            
            if (!$currentUser) {
                $this->logSecurityEvent('Unauthenticated staff import access attempt');
                return redirect()->route('login');
            }

            // Get current clinic and check permissions
            $currentClinic = $currentUser->getCurrentClinic();
            if (!$currentClinic) {
                return redirect()->back()->with('error', 'No clinic selected');
            }

            // Check staff management access
            $this->requireStaffManagementAccess($request, 'import');

            // Validate file upload
            $validatedData = $this->validateAndSanitize($request, [
                'import_file' => 'required|file|mimes:csv,xlsx,xls|max:10240', // 10MB max
            ], [
                'import_file.required' => 'Please select a file to import.',
                'import_file.file' => 'The uploaded file is not valid.',
                'import_file.mimes' => 'The file must be a CSV or Excel file.',
                'import_file.max' => 'The file size must not exceed 10MB.',
            ]);

            $file = $request->file('import_file');
            $clinicId = $currentClinic->id;

            // Process the import
            $importResult = $this->processStaffImport($file, $clinicId, $currentUser);

            // Log import result
            $this->logWebRequest('Staff Import Completed', [
                'total_rows' => $importResult['total_rows'],
                'successful_imports' => $importResult['successful_imports'],
                'failed_imports' => $importResult['failed_imports'],
                'errors' => $importResult['errors']
            ]);

            // Handle AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Import completed. {$importResult['successful_imports']} staff members imported successfully.",
                    'data' => $importResult
                ]);
            }

            $message = "Import completed successfully! {$importResult['successful_imports']} staff members imported.";
            if ($importResult['failed_imports'] > 0) {
                $message .= " {$importResult['failed_imports']} records failed to import.";
            }

            return redirect()->route('admin.staff')->with('success', $message);

        } catch (\Exception $e) {
            $this->handleException($e, 'StaffController::import');
            
            // Handle AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to import staff members. Please check your file format and try again.',
                    'error' => $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Failed to import staff members. Please check your file format and try again.');
        }
    }

    /**
     * Download staff import template
     */
    public function downloadTemplate(Request $request)
    {
        try {
            $this->logWebRequest('Staff Import Template Download', ['action' => 'download_template']);
            
            $currentUser = $request->user();
            
            if (!$currentUser) {
                $this->logSecurityEvent('Unauthenticated template download attempt');
                return redirect()->route('login');
            }

            // Check staff management access
            $this->requireStaffManagementAccess($request, 'download_template');

            // Get available roles and departments
            $roles = Role::where('is_system_role', false)->pluck('name')->toArray();
            $departments = [
                'General', 'Administration', 'Medical', 'Nursing', 'Reception',
                'Laboratory', 'Pharmacy', 'Maintenance', 'Security', 'IT Support'
            ];

            // Create CSV template
            $templateData = [
                [
                    'name' => 'John Doe',
                    'email' => 'john.doe@example.com',
                    'phone' => '+1234567890',
                    'role' => 'Doctor',
                    'department' => 'Medical',
                    'status' => 'Active',
                    'address' => '123 Main St, City, State',
                    'emergency_contact' => 'Jane Doe',
                    'emergency_phone' => '+1234567891',
                    'notes' => 'Sample staff member'
                ]
            ];

            $filename = 'staff_import_template.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function() use ($templateData) {
                $file = fopen('php://output', 'w');
                
                // Add headers
                fputcsv($file, array_keys($templateData[0]));
                
                // Add sample data
                foreach ($templateData as $row) {
                    fputcsv($file, $row);
                }
                
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);

        } catch (\Exception $e) {
            $this->handleException($e, 'StaffController::downloadTemplate');
            return redirect()->back()->with('error', 'Failed to download template.');
        }
    }

    /**
     * Process staff import from uploaded file
     */
    private function processStaffImport($file, int $clinicId, $currentUser): array
    {
        $result = [
            'total_rows' => 0,
            'successful_imports' => 0,
            'failed_imports' => 0,
            'errors' => []
        ];

        try {
            // Get file extension
            $extension = $file->getClientOriginalExtension();
            
            if ($extension === 'csv') {
                $data = $this->parseCsvFile($file);
            } else {
                // For Excel files, use Laravel Excel
                $data = Excel::toArray(new class implements ToModel, WithHeadingRow {
                    public function model(array $row) {
                        return $row;
                    }
                }, $file)[0];
            }

            $result['total_rows'] = count($data);

            foreach ($data as $index => $row) {
                try {
                    // Skip empty rows
                    if (empty(array_filter($row))) {
                        continue;
                    }

                    // Validate and process each row
                    $staffData = $this->validateAndProcessStaffRow($row, $index + 1);
                    
                    if ($staffData) {
                        // Create staff member
                        $this->createStaffFromImport($staffData, $clinicId, $currentUser);
                        $result['successful_imports']++;
                    } else {
                        $result['failed_imports']++;
                    }

                } catch (\Exception $e) {
                    $result['failed_imports']++;
                    $result['errors'][] = "Row " . ($index + 1) . ": " . $e->getMessage();
                }
            }

        } catch (\Exception $e) {
            $result['errors'][] = "File processing error: " . $e->getMessage();
        }

        return $result;
    }

    /**
     * Parse CSV file
     */
    private function parseCsvFile($file): array
    {
        $data = [];
        $handle = fopen($file->getPathname(), 'r');
        
        if ($handle !== false) {
            $headers = fgetcsv($handle);
            
            while (($row = fgetcsv($handle)) !== false) {
                $data[] = array_combine($headers, $row);
            }
            
            fclose($handle);
        }
        
        return $data;
    }

    /**
     * Validate and process a single staff row
     */
    private function validateAndProcessStaffRow(array $row, int $rowNumber): ?array
    {
        try {
            // Normalize column names (handle different variations)
            $normalizedRow = $this->normalizeRowData($row);

            // Validate required fields
            $requiredFields = ['name', 'email', 'role', 'department'];
            foreach ($requiredFields as $field) {
                if (empty($normalizedRow[$field])) {
                    throw new \Exception("Missing required field: {$field}");
                }
            }

            // Validate email format
            if (!filter_var($normalizedRow['email'], FILTER_VALIDATE_EMAIL)) {
                throw new \Exception("Invalid email format: {$normalizedRow['email']}");
            }

            // Check if email already exists
            if (User::where('email', $normalizedRow['email'])->exists()) {
                throw new \Exception("Email already exists: {$normalizedRow['email']}");
            }

            // Validate role exists
            $role = Role::where('name', $normalizedRow['role'])->first();
            if (!$role) {
                throw new \Exception("Invalid role: {$normalizedRow['role']}");
            }

            // Set default values
            $staffData = [
                'name' => trim($normalizedRow['name']),
                'email' => trim($normalizedRow['email']),
                'phone' => trim($normalizedRow['phone'] ?? ''),
                'role' => $normalizedRow['role'],
                'department' => trim($normalizedRow['department']),
                'status' => $normalizedRow['status'] ?? 'Active',
                'address' => trim($normalizedRow['address'] ?? ''),
                'emergency_contact' => trim($normalizedRow['emergency_contact'] ?? ''),
                'emergency_phone' => trim($normalizedRow['emergency_phone'] ?? ''),
                'notes' => trim($normalizedRow['notes'] ?? ''),
            ];

            // Validate status
            if (!in_array($staffData['status'], ['Active', 'On Leave', 'Inactive'])) {
                $staffData['status'] = 'Active';
            }

            return $staffData;

        } catch (\Exception $e) {
            throw new \Exception("Row {$rowNumber}: " . $e->getMessage());
        }
    }

    /**
     * Normalize row data to handle different column name variations
     */
    private function normalizeRowData(array $row): array
    {
        $normalized = [];
        $mappings = [
            'name' => ['name', 'full_name', 'fullname', 'staff_name'],
            'email' => ['email', 'email_address', 'e_mail'],
            'phone' => ['phone', 'phone_number', 'mobile', 'contact'],
            'role' => ['role', 'position', 'job_title'],
            'department' => ['department', 'dept', 'division'],
            'status' => ['status', 'active_status', 'employment_status'],
            'address' => ['address', 'home_address', 'location'],
            'emergency_contact' => ['emergency_contact', 'emergency_contact_name', 'emergency_name'],
            'emergency_phone' => ['emergency_phone', 'emergency_contact_phone', 'emergency_mobile'],
            'notes' => ['notes', 'comments', 'remarks', 'additional_info']
        ];

        foreach ($mappings as $key => $variations) {
            foreach ($variations as $variation) {
                if (isset($row[$variation]) && !empty($row[$variation])) {
                    $normalized[$key] = $row[$variation];
                    break;
                }
            }
        }

        return $normalized;
    }

    /**
     * Create staff member from import data
     */
    private function createStaffFromImport(array $staffData, int $clinicId, $currentUser): void
    {
        // Find role
        $role = Role::where('name', $staffData['role'])->first();
        if (!$role) {
            throw new \Exception("Role not found: {$staffData['role']}");
        }

        // Generate secure temporary password
        $tempPassword = $this->generateSecurePassword();

        // Create user
        $user = User::create([
            'name' => $staffData['name'],
            'email' => $staffData['email'],
            'phone' => $staffData['phone'],
            'password' => Hash::make($tempPassword),
            'is_active' => $staffData['status'] === 'Active',
            'email_verified_at' => now(),
        ]);

        // Assign to clinic with role and additional staff information
        $user->clinics()->attach($clinicId, [
            'role_id' => $role->id,
            'department' => $staffData['department'],
            'assigned_by' => $currentUser->id,
            'assigned_at' => now(),
            'join_date' => now(),
            'address' => $staffData['address'],
            'emergency_contact' => $staffData['emergency_contact'],
            'emergency_phone' => $staffData['emergency_phone'],
            'notes' => $staffData['notes'],
        ]);
    }
}