<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\User;
use App\Models\Clinic;
use App\Models\Role;
use App\Models\Schedule;
use App\Models\Room;

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
    public function index(Request $request): Response|RedirectResponse|JsonResponse
    {
        try {
            $this->logWebRequest('Schedule Management Access', ['action' => 'index']);
            
            $user = $request->user();

            if (!$user) {
                $this->logSecurityEvent('Unauthenticated schedule access attempt');
                return redirect()->route('login');
            }

            $userClinicRole = $this->getUserClinicRole($request);
            if (!$userClinicRole) {
                if ($request->expectsJson() || $request->is('api/*')) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No clinic selected',
                        'schedules' => [],
                        'doctors' => [],
                        'rooms' => [],
                        'permissions' => []
                    ], 403);
                }
                return redirect()->route('dashboard')->with('error', 'No clinic selected');
            }
            $clinicId = $userClinicRole->clinic_id;

            // Get user permissions
            $permissions = $this->getUserPermissions($userClinicRole->role->name ?? 'user');

            // Get schedules for the current clinic with filters
            $schedulesQuery = Schedule::forClinic($clinicId)
                ->with(['doctor', 'room', 'appointments']);

            // Apply filters
            if ($request->filled('search')) {
                $search = $request->search;
                $schedulesQuery->whereHas('doctor', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            }

            if ($request->filled('doctor')) {
                $schedulesQuery->where('doctor_id', $request->doctor);
            }

            if ($request->filled('status')) {
                $schedulesQuery->where('status', $request->status);
            }

            if ($request->filled('day')) {
                $schedulesQuery->where('day_of_week', $request->day);
            }

            $schedules = $schedulesQuery->orderBy('day_of_week')
                ->orderBy('start_time')
                ->get();

            // Get doctors for the current clinic
            $doctors = User::whereHas('clinics', function ($q) use ($clinicId) {
                $q->where('clinic_id', $clinicId);
            })->whereHas('roles', function ($q) {
                $q->where('name', 'doctor');
            })->get();

            // Get rooms for the current clinic
            $rooms = Room::where('clinic_id', $clinicId)
                ->where('is_active', true)
                ->get();

            // Add security context for frontend
            $securityContext = [
                'can_create_schedules' => $this->hasPermissionInClinic($request, 'schedules.create'),
                'can_edit_schedules' => $this->hasPermissionInClinic($request, 'schedules.edit'),
                'can_delete_schedules' => $this->hasPermissionInClinic($request, 'schedules.delete'),
                'current_user_role' => $userClinicRole->role->name,
                'is_superadmin' => $userClinicRole->role->name === 'superadmin',
            ];

            // Return JSON for AJAX requests
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => true,
                    'schedules' => $schedules,
                    'doctors' => $doctors,
                    'rooms' => $rooms,
                    'permissions' => $permissions,
                    'security' => $securityContext,
                    'filters' => [
                        'search' => $request->search,
                        'doctor' => $request->doctor,
                        'status' => $request->status,
                        'day' => $request->day,
                    ]
                ]);
            }

            // Return Inertia response for regular page requests
            return Inertia::render('admin/schedules', [
                'schedules' => $schedules,
                'doctors' => $doctors,
                'rooms' => $rooms,
                'permissions' => $permissions,
                'security' => $securityContext,
                'filters' => [
                    'search' => $request->search,
                    'doctor' => $request->doctor,
                    'status' => $request->status,
                    'day' => $request->day,
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

            $clinicId = $currentClinic->id;

            // Get the schedule
            $schedule = Schedule::forClinic($clinicId)
                ->with(['doctor', 'room', 'appointments', 'createdBy', 'updatedBy'])
                ->findOrFail($id);

            // Get user permissions
            $permissions = $this->getUserPermissions($userClinicRole->role->name ?? 'user');

            // Get doctors and rooms for the current clinic
            $doctors = User::whereHas('clinics', function ($q) use ($clinicId) {
                $q->where('clinic_id', $clinicId);
            })->whereHas('roles', function ($q) {
                $q->where('name', 'doctor');
            })->get();

            $rooms = Room::where('clinic_id', $clinicId)
                ->where('is_active', true)
                ->get();

            return Inertia::render('admin/schedules', [
                'schedules' => collect([$schedule]),
                'selectedSchedule' => $schedule,
                'doctors' => $doctors,
                'rooms' => $rooms,
                'permissions' => $permissions,
                'filters' => [
                    'search' => '',
                    'doctor' => '',
                    'status' => '',
                    'day' => '',
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

            // Check permissions
            $this->requirePermissionInClinic($request, 'schedules.create');

            // Validate the request
            $validatedData = $this->validateAndSanitize($request, [
                'doctor_id' => 'required|exists:users,id',
                'room_id' => 'nullable|exists:rooms,id',
                'day_of_week' => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
                'start_time' => 'required|date_format:H:i',
                'end_time' => 'required|date_format:H:i|after:start_time',
                'status' => 'required|in:Active,Inactive,On Leave,Vacation,Sick Leave',
                'is_recurring' => 'boolean',
                'recurring_type' => 'required_if:is_recurring,true|in:none,weekly,biweekly,monthly',
                'recurring_interval' => 'required_if:is_recurring,true|integer|min:1',
                'recurring_end_date' => 'nullable|date|after:today',
                'notes' => 'nullable|string|max:1000',
                'max_appointments' => 'integer|min:1|max:50',
                'appointment_duration' => 'integer|min:15|max:120',
                'break_duration' => 'integer|min:0|max:60',
                'is_active' => 'boolean',
            ]);

            // Check for conflicts
            $conflict = Schedule::forClinic($currentClinic->id)
                ->where('doctor_id', $validatedData['doctor_id'])
                ->where('day_of_week', $validatedData['day_of_week'])
                ->where(function ($query) use ($validatedData) {
                    $query->whereBetween('start_time', [$validatedData['start_time'], $validatedData['end_time']])
                          ->orWhereBetween('end_time', [$validatedData['start_time'], $validatedData['end_time']])
                          ->orWhere(function ($q) use ($validatedData) {
                              $q->where('start_time', '<=', $validatedData['start_time'])
                                ->where('end_time', '>=', $validatedData['end_time']);
                          });
                })
                ->where('is_active', true)
                ->first();

            if ($conflict) {
                return redirect()->back()->with('error', 'Schedule conflicts with existing schedule for this doctor on the same day and time.')->withInput();
            }

            // Create the schedule
            $schedule = Schedule::create([
                'clinic_id' => $currentClinic->id,
                'doctor_id' => $validatedData['doctor_id'],
                'room_id' => $validatedData['room_id'] ?? null,
                'day_of_week' => $validatedData['day_of_week'],
                'start_time' => $validatedData['start_time'],
                'end_time' => $validatedData['end_time'],
                'status' => $validatedData['status'],
                'is_recurring' => $validatedData['is_recurring'] ?? false,
                'recurring_type' => $validatedData['recurring_type'] ?? 'none',
                'recurring_interval' => $validatedData['recurring_interval'] ?? 1,
                'recurring_end_date' => $validatedData['recurring_end_date'] ?? null,
                'notes' => $validatedData['notes'] ?? null,
                'max_appointments' => $validatedData['max_appointments'] ?? 10,
                'appointment_duration' => $validatedData['appointment_duration'] ?? 30,
                'break_duration' => $validatedData['break_duration'] ?? 0,
                'is_active' => $validatedData['is_active'] ?? true,
                'created_by' => $currentUser->id,
            ]);

            $this->logWebRequest('Schedule Created', ['schedule_id' => $schedule->id, 'doctor_id' => $schedule->doctor_id]);

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

            // Check permissions
            $this->requirePermissionInClinic($request, 'schedules.edit');

            // Get the schedule
            $schedule = Schedule::forClinic($currentClinic->id)->findOrFail($id);

            // Validate the request
            $validatedData = $this->validateAndSanitize($request, [
                'doctor_id' => 'required|exists:users,id',
                'room_id' => 'nullable|exists:rooms,id',
                'day_of_week' => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
                'start_time' => 'required|date_format:H:i',
                'end_time' => 'required|date_format:H:i|after:start_time',
                'status' => 'required|in:Active,Inactive,On Leave,Vacation,Sick Leave',
                'is_recurring' => 'boolean',
                'recurring_type' => 'required_if:is_recurring,true|in:none,weekly,biweekly,monthly',
                'recurring_interval' => 'required_if:is_recurring,true|integer|min:1',
                'recurring_end_date' => 'nullable|date|after:today',
                'notes' => 'nullable|string|max:1000',
                'max_appointments' => 'integer|min:1|max:50',
                'appointment_duration' => 'integer|min:15|max:120',
                'break_duration' => 'integer|min:0|max:60',
                'is_active' => 'boolean',
            ]);

            // Check for conflicts (excluding current schedule)
            $conflict = Schedule::forClinic($currentClinic->id)
                ->where('id', '!=', $id)
                ->where('doctor_id', $validatedData['doctor_id'])
                ->where('day_of_week', $validatedData['day_of_week'])
                ->where(function ($query) use ($validatedData) {
                    $query->whereBetween('start_time', [$validatedData['start_time'], $validatedData['end_time']])
                          ->orWhereBetween('end_time', [$validatedData['start_time'], $validatedData['end_time']])
                          ->orWhere(function ($q) use ($validatedData) {
                              $q->where('start_time', '<=', $validatedData['start_time'])
                                ->where('end_time', '>=', $validatedData['end_time']);
                          });
                })
                ->where('is_active', true)
                ->first();

            if ($conflict) {
                return redirect()->back()->with('error', 'Schedule conflicts with existing schedule for this doctor on the same day and time.')->withInput();
            }

            // Update the schedule
            $schedule->update([
                'doctor_id' => $validatedData['doctor_id'],
                'room_id' => $validatedData['room_id'] ?? null,
                'day_of_week' => $validatedData['day_of_week'],
                'start_time' => $validatedData['start_time'],
                'end_time' => $validatedData['end_time'],
                'status' => $validatedData['status'],
                'is_recurring' => $validatedData['is_recurring'] ?? false,
                'recurring_type' => $validatedData['recurring_type'] ?? 'none',
                'recurring_interval' => $validatedData['recurring_interval'] ?? 1,
                'recurring_end_date' => $validatedData['recurring_end_date'] ?? null,
                'notes' => $validatedData['notes'] ?? null,
                'max_appointments' => $validatedData['max_appointments'] ?? 10,
                'appointment_duration' => $validatedData['appointment_duration'] ?? 30,
                'break_duration' => $validatedData['break_duration'] ?? 0,
                'is_active' => $validatedData['is_active'] ?? true,
                'updated_by' => $currentUser->id,
            ]);

            $this->logWebRequest('Schedule Updated', ['schedule_id' => $schedule->id, 'doctor_id' => $schedule->doctor_id]);

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

            // Check permissions
            $this->requirePermissionInClinic($request, 'schedules.delete');

            // Get the schedule
            $schedule = Schedule::forClinic($currentClinic->id)->findOrFail($id);

            // Check if schedule has appointments
            if ($schedule->appointments()->count() > 0) {
                return redirect()->back()->with('error', 'Cannot delete schedule with existing appointments. Please cancel or reschedule appointments first.');
            }

            // Soft delete the schedule
            $schedule->delete();

            $this->logWebRequest('Schedule Deleted', ['schedule_id' => $id, 'doctor_id' => $schedule->doctor_id]);

            return redirect()->route('admin.schedules')->with('success', 'Schedule deleted successfully');
        } catch (\Exception $e) {
            $this->handleException($e, 'ScheduleController::destroy');
            return redirect()->back()->with('error', 'Failed to delete schedule. Please try again.');
        }
    }

    /**
     * Get available time slots for a schedule
     */
    public function availableSlots(Request $request, int $id)
    {
        try {
            $user = $request->user();
            $currentClinic = $user->getCurrentClinic();
            
            if (!$currentClinic) {
                return response()->json(['error' => 'No clinic selected'], 403);
            }

            $schedule = Schedule::forClinic($currentClinic->id)
                ->with(['appointments'])
                ->findOrFail($id);

            $date = $request->get('date', now()->format('Y-m-d'));
            $slots = $schedule->getAvailableSlots($date);

            return response()->json([
                'success' => true,
                'slots' => $slots,
                'schedule' => $schedule,
                'date' => $date
            ]);

        } catch (\Exception $e) {
            $this->handleException($e, 'ScheduleController::availableSlots');
            return response()->json(['error' => 'Failed to get available slots'], 500);
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
                'view_system_health', 'manage_backups', 'view_financial_reports',
                'schedules.view', 'schedules.create', 'schedules.edit', 'schedules.delete'
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
                'manage_treatment_plans', 'view_patient_history', 'manage_soap_notes',
                'schedules.view'
            ],
            'receptionist' => [
                'search_patients', 'manage_appointments', 'manage_queue', 'register_patients',
                'view_encounters', 'view_reports', 'manage_patient_info', 'view_appointments',
                'manage_check_in', 'view_patient_history', 'manage_insurance',
                'schedules.view'
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