<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\User;
use App\Models\Clinic;
use App\Models\Role;

class ScheduleController extends \Illuminate\Routing\Controller
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
     * Display a listing of schedules
     */
    public function index(Request $request): Response|RedirectResponse
    {
        try {
            $this->logWebRequest('Schedule Management Access', ['action' => 'index']);
            
            $user = $request->user();

            if (!$user) {
                $this->logSecurityEvent('Unauthenticated schedule access attempt');
                return redirect()->route('login');
            }

            // Check schedule management access
            $this->requireScheduleManagementAccess($request, 'index');
            
            $userClinicRole = $this->getUserClinicRole($request);
            if (!$userClinicRole) {
                return redirect()->route('dashboard')->with('error', 'No clinic selected');
            }
            $clinicId = $userClinicRole->clinic_id;

            // Get user permissions
            $permissions = $this->getUserPermissions($userClinicRole->role->name ?? 'user');

            // For now, return empty data structure - this will be populated when schedule functionality is implemented
            $schedules = collect([]);
            $doctors = User::whereHas('clinics', function ($q) use ($clinicId) {
                $q->where('clinic_id', $clinicId);
            })->whereHas('roles', function ($q) {
                $q->where('name', 'doctor');
            })->get();

            // Add security context for frontend
            $securityContext = [
                'can_create_schedules' => $this->hasPermissionInClinic($request, 'schedules.create'),
                'can_edit_schedules' => $this->hasPermissionInClinic($request, 'schedules.edit'),
                'can_delete_schedules' => $this->hasPermissionInClinic($request, 'schedules.delete'),
                'current_user_role' => $userClinicRole->role->name,
                'is_superadmin' => $userClinicRole->role->name === 'superadmin',
            ];

            return Inertia::render('admin/schedules', [
                'schedules' => $schedules,
                'doctors' => $doctors,
                'permissions' => $permissions,
                'security' => $securityContext,
                'filters' => [
                    'search' => $request->search,
                    'doctor' => $request->doctor,
                    'status' => $request->status,
                ]
            ]);

        } catch (\Exception $e) {
            $this->handleException($e, 'ScheduleController::index');
            return redirect()->route('dashboard')->with('error', 'Failed to load schedule management. Please try again.');
        }
    }

    /**
     * Display the specified schedule
     */
    public function show(Request $request, int $id): Response|RedirectResponse
    {
        try {
            $this->logWebRequest('Schedule Management Access', ['action' => 'show', 'schedule_id' => $id]);
            
            $user = $request->user();
            
            if (!$user) {
                $this->logSecurityEvent('Unauthenticated schedule show access attempt');
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
                $this->logSecurityEvent('Unauthorized schedule show access attempt - no clinic role');
                return redirect()->route('dashboard')->with('error', 'You do not have access to this clinic.');
            }

            // Check schedule management access
            $this->requireScheduleManagementAccess($request, 'show');
            
            $clinicId = $currentClinic->id;

            // Get user permissions
            $permissions = $this->getUserPermissions($userClinicRole->role->name ?? 'user');

            // For now, return empty data structure - this will be populated when schedule functionality is implemented
            $schedules = collect([]);
            $doctors = User::whereHas('clinics', function ($q) use ($clinicId) {
                $q->where('clinic_id', $clinicId);
            })->whereHas('roles', function ($q) {
                $q->where('name', 'doctor');
            })->get();

            return Inertia::render('admin/schedules', [
                'schedules' => $schedules,
                'selectedSchedule' => null, // Will be populated when schedule model is created
                'doctors' => $doctors,
                'permissions' => $permissions,
                'filters' => [
                    'search' => '',
                    'doctor' => '',
                    'status' => '',
                ]
            ]);

        } catch (\Exception $e) {
            $this->handleException($e, 'ScheduleController::show');
            return redirect()->route('admin.schedules')->with('error', 'Schedule not found.');
        }
    }

    /**
     * Store a newly created schedule
     */
    public function store(Request $request)
    {
        try {
            $this->logWebRequest('Schedule Creation', ['action' => 'store']);
            
            $currentUser = $request->user();
            
            if (!$currentUser) {
                $this->logSecurityEvent('Unauthenticated schedule store access attempt');
                return redirect()->route('login');
            }

            // Get current clinic and check permissions
            $currentClinic = $currentUser->getCurrentClinic();
            if (!$currentClinic) {
                return redirect()->back()->with('error', 'No clinic selected');
            }

            // Check schedule management access
            $this->requireScheduleManagementAccess($request, 'store');

            // TODO: Add validation rules when schedule model is implemented
            // $validatedData = $this->validateAndSanitize($request, [
            //     'title' => 'required|string|max:255',
            //     'description' => 'nullable|string',
            //     'start_time' => 'required|date',
            //     'end_time' => 'required|date|after:start_time',
            //     'doctor_id' => 'required|exists:users,id',
            // ]);
            
            // TODO: Implement schedule creation logic
            return redirect()->route('admin.schedules')->with('success', 'Schedule created successfully');
        } catch (\Exception $e) {
            $this->handleException($e, 'ScheduleController::store');
            return redirect()->back()->with('error', 'Failed to create schedule. Please try again.')->withInput();
        }
    }

    /**
     * Update the specified schedule
     */
    public function update(Request $request, int $id)
    {
        try {
            $this->logWebRequest('Schedule Update', ['action' => 'update', 'schedule_id' => $id]);
            
            $currentUser = $request->user();
            
            if (!$currentUser) {
                $this->logSecurityEvent('Unauthenticated schedule update access attempt');
                return redirect()->route('login');
            }

            // Get current clinic and check permissions
            $currentClinic = $currentUser->getCurrentClinic();
            if (!$currentClinic) {
                return redirect()->back()->with('error', 'No clinic selected');
            }

            // Check schedule management access
            $this->requireScheduleManagementAccess($request, 'update');

            // TODO: Add validation rules when schedule model is implemented
            // $validatedData = $this->validateAndSanitize($request, [
            //     'title' => 'required|string|max:255',
            //     'description' => 'nullable|string',
            //     'start_time' => 'required|date',
            //     'end_time' => 'required|date|after:start_time',
            //     'doctor_id' => 'required|exists:users,id',
            // ]);
            
            // TODO: Implement schedule update logic
            return redirect()->route('admin.schedules')->with('success', 'Schedule updated successfully');
        } catch (\Exception $e) {
            $this->handleException($e, 'ScheduleController::update');
            return redirect()->back()->with('error', 'Failed to update schedule. Please try again.')->withInput();
        }
    }

    /**
     * Remove the specified schedule
     */
    public function destroy(Request $request, int $id)
    {
        try {
            $this->logWebRequest('Schedule Deletion', ['action' => 'destroy', 'schedule_id' => $id]);
            
            $currentUser = $request->user();
            
            if (!$currentUser) {
                $this->logSecurityEvent('Unauthenticated schedule destroy access attempt');
                return redirect()->route('login');
            }

            // Get current clinic and check permissions
            $currentClinic = $currentUser->getCurrentClinic();
            if (!$currentClinic) {
                return redirect()->back()->with('error', 'No clinic selected');
            }

            // Check schedule management access
            $this->requireScheduleManagementAccess($request, 'destroy');
            
            // TODO: Implement schedule deletion logic
            return redirect()->route('admin.schedules')->with('success', 'Schedule deleted successfully');
        } catch (\Exception $e) {
            $this->handleException($e, 'ScheduleController::destroy');
            return redirect()->back()->with('error', 'Failed to delete schedule. Please try again.');
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
                'view_financial_reports', 'manage_rooms', 'manage_schedules',
                'schedules.view', 'schedules.create', 'schedules.edit', 'schedules.delete'
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
     * Require permission in clinic or abort
     */
    protected function requirePermissionInClinic(Request $request, string $permission, ?int $clinicId = null): void
    {
        if (!$this->hasPermissionInClinic($request, $permission, $clinicId)) {
            abort(403, 'Insufficient permissions.');
        }
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
     * Check if user has schedule management access
     */
    private function hasScheduleManagementAccess(Request $request): bool
    {
        $user = $request->user();
        
        if (!$user) {
            return false;
        }

        $userClinicRole = $this->getUserClinicRole($request);
        if (!$userClinicRole) {
            return false;
        }

        // Check for admin role, superadmin role, or schedules.view permission
        return $userClinicRole->role->name === 'admin' || 
               $userClinicRole->role->name === 'superadmin' ||
               $this->hasPermissionInClinic($request, 'schedules.view');
    }

    /**
     * Require schedule management access or redirect with error
     */
    private function requireScheduleManagementAccess(Request $request, string $action = 'access'): void
    {
        if (!$this->hasScheduleManagementAccess($request)) {
            $user = $request->user();
            $userClinicRole = $this->getUserClinicRole($request);
            
            $this->logSecurityEvent("Unauthorized schedule {$action} attempt", [
                'user_id' => $user?->id,
                'role' => $userClinicRole?->role->name ?? 'unknown',
                'action' => $action
            ]);
            
            abort(403, 'You do not have permission to manage schedules.');
        }
    }

    /**
     * Handle exceptions with proper logging
     */
    protected function handleException(\Exception $e, string $context = 'ScheduleController'): void
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
}
