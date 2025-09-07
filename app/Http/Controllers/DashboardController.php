<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Encounter;
use App\Models\Prescription;
use App\Models\ActivityLog;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the dashboard
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        // Get user's clinic and role from the pivot table
        $userClinicRole = $user->userClinicRoles()->with(['clinic', 'role'])->first();

        if (!$userClinicRole) {
            // If no clinic/role found, return with default data
            return Inertia::render('dashboard', [
                'user' => $user,
                'stats' => $this->getDefaultStats(),
                'permissions' => [],
            ]);
        }

        $clinicId = $userClinicRole->clinic_id;
        $role = $userClinicRole->role->name ?? 'user';
        $clinic = $userClinicRole->clinic;

        // Get dashboard statistics
        $stats = $this->getDashboardStats($clinicId, $role);

        // Get user permissions based on role
        $permissions = $this->getUserPermissions($role);

        // Add clinic and role info to user object for frontend
        $user->clinic_id = $clinicId;
        $user->role = $role;
        $user->clinic = $clinic;

        // Determine which dashboard to render based on the route
        $routeName = $request->route()->getName();

        if ($routeName === 'admin.dashboard') {
            return Inertia::render('admin/dashboard', [
                'user' => $user,
                'stats' => $stats,
                'permissions' => $permissions,
            ]);
        }

        return Inertia::render('dashboard', [
            'user' => $user,
            'stats' => $stats,
            'permissions' => $permissions,
        ]);
    }

    /**
     * Get dashboard statistics based on user role and clinic
     */
    private function getDashboardStats($clinicId, $role)
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();
        $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();

        try {
            // Base statistics that apply to all roles
            $stats = [
                'totalUsers' => 0,
                'totalPatients' => Patient::where('clinic_id', $clinicId)->count(),
                'totalAppointments' => Appointment::where('clinic_id', $clinicId)->count(),
                'totalEncounters' => Encounter::where('clinic_id', $clinicId)->count(),
                'totalPrescriptions' => Prescription::where('clinic_id', $clinicId)->count(),
                'totalProducts' => 0,
                'totalMeetings' => 0,
                'totalInteractions' => 0,
                'todayAppointments' => Appointment::where('clinic_id', $clinicId)
                    ->whereDate('start_at', $today)
                    ->count(),
                'activeQueue' => Encounter::where('clinic_id', $clinicId)
                    ->where('status', 'in_progress')
                    ->count(),
                'completedEncounters' => Encounter::where('clinic_id', $clinicId)
                    ->where('status', 'completed')
                    ->whereDate('created_at', $today)
                    ->count(),
                'pendingPrescriptions' => Prescription::where('clinic_id', $clinicId)
                    ->where('status', 'pending')
                    ->count(),
                'upcomingMeetings' => 0,
                'recentActivity' => $this->getRecentActivity($clinicId),
            ];

            // Calculate historical changes
            $stats['changes'] = $this->calculateHistoricalChanges($clinicId, $today, $yesterday, $thisMonth, $lastMonth, $lastMonthEnd);
        } catch (\Exception $e) {
            // Fallback to default stats if database queries fail
            $stats = [
                'totalUsers' => 0,
                'totalPatients' => 0,
                'totalAppointments' => 0,
                'totalEncounters' => 0,
                'totalPrescriptions' => 0,
                'totalProducts' => 0,
                'totalMeetings' => 0,
                'totalInteractions' => 0,
                'todayAppointments' => 0,
                'activeQueue' => 0,
                'completedEncounters' => 0,
                'pendingPrescriptions' => 0,
                'upcomingMeetings' => 0,
                'recentActivity' => [],
                'changes' => $this->getDefaultChanges(),
            ];
        }

        // Role-specific adjustments
        switch ($role) {
            case 'superadmin':
                $stats['totalUsers'] = \App\Models\User::count();
                break;
            case 'admin':
                $stats['totalUsers'] = \App\Models\User::whereHas('userClinicRoles', function ($query) use ($clinicId) {
                    $query->where('clinic_id', $clinicId);
                })->count();
                break;
            case 'doctor':
                // Doctor-specific stats
                $stats['todayAppointments'] = Appointment::where('clinic_id', $clinicId)
                    ->where('doctor_id', $this->getCurrentUserId())
                    ->whereDate('start_at', $today)
                    ->count();
                break;
            case 'medrep':
                $stats['totalProducts'] = 50; // Mock data
                $stats['totalMeetings'] = 25; // Mock data
                $stats['totalInteractions'] = 100; // Mock data
                $stats['upcomingMeetings'] = 5; // Mock data
                break;
        }

        return $stats;
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

    /**
     * Get recent activity logs
     */
    private function getRecentActivity($clinicId)
    {
        try {
            return ActivityLog::where('clinic_id', $clinicId)
                ->with('actor')
                ->latest('at')
                ->limit(10)
                ->get()
                ->map(function ($log) {
                    return [
                        'id' => $log->id,
                        'type' => $log->action,
                        'description' => $log->description ?? $log->action,
                        'user_name' => $log->actor->name ?? 'System',
                        'created_at' => $log->at->toISOString(),
                    ];
                })
                ->toArray();
        } catch (\Exception $e) {
            // Return empty array if activity logs fail
            return [];
        }
    }

    /**
     * Get current user ID
     */
    private function getCurrentUserId()
    {
        return request()->user()->id;
    }

    /**
     * Get default stats when user has no clinic/role
     */
    private function getDefaultStats()
    {
        return [
            'totalUsers' => 0,
            'totalPatients' => 0,
            'totalAppointments' => 0,
            'totalEncounters' => 0,
            'totalPrescriptions' => 0,
            'totalProducts' => 0,
            'totalMeetings' => 0,
            'totalInteractions' => 0,
            'todayAppointments' => 0,
            'activeQueue' => 0,
            'completedEncounters' => 0,
            'pendingPrescriptions' => 0,
            'upcomingMeetings' => 0,
            'recentActivity' => [],
            'changes' => $this->getDefaultChanges(),
        ];
    }

    /**
     * Calculate historical changes for dashboard stats
     */
    private function calculateHistoricalChanges($clinicId, $today, $yesterday, $thisMonth, $lastMonth, $lastMonthEnd)
    {
        try {
            // Calculate changes for each metric
            $changes = [];

            // Total Users change (this month vs last month)
            $thisMonthUsers = \App\Models\User::whereHas('userClinicRoles', function ($query) use ($clinicId) {
                $query->where('clinic_id', $clinicId);
            })->where('created_at', '>=', $thisMonth)->count();

            $lastMonthUsers = \App\Models\User::whereHas('userClinicRoles', function ($query) use ($clinicId) {
                $query->where('clinic_id', $clinicId);
            })->whereBetween('created_at', [$lastMonth, $lastMonthEnd])->count();

            $changes['totalUsers'] = $this->formatChange($thisMonthUsers, $lastMonthUsers, 'this month');

            // Total Patients change (this month vs last month)
            $thisMonthPatients = Patient::where('clinic_id', $clinicId)
                ->where('created_at', '>=', $thisMonth)->count();
            $lastMonthPatients = Patient::where('clinic_id', $clinicId)
                ->whereBetween('created_at', [$lastMonth, $lastMonthEnd])->count();
            $changes['totalPatients'] = $this->formatChange($thisMonthPatients, $lastMonthPatients, 'this month');

            // Today's Appointments change (today vs yesterday)
            $todayAppointments = Appointment::where('clinic_id', $clinicId)
                ->whereDate('start_at', $today)->count();
            $yesterdayAppointments = Appointment::where('clinic_id', $clinicId)
                ->whereDate('start_at', $yesterday)->count();
            $changes['todayAppointments'] = $this->formatChange($todayAppointments, $yesterdayAppointments, 'from yesterday');

            // Active Queue (real-time, no historical comparison)
            $changes['activeQueue'] = 'Real-time';

            // Total Appointments change (this month vs last month)
            $thisMonthAppointments = Appointment::where('clinic_id', $clinicId)
                ->where('created_at', '>=', $thisMonth)->count();
            $lastMonthAppointments = Appointment::where('clinic_id', $clinicId)
                ->whereBetween('created_at', [$lastMonth, $lastMonthEnd])->count();
            $changes['totalAppointments'] = $this->formatChange($thisMonthAppointments, $lastMonthAppointments, 'this month');

            // Total Encounters change (this month vs last month)
            $thisMonthEncounters = Encounter::where('clinic_id', $clinicId)
                ->where('created_at', '>=', $thisMonth)->count();
            $lastMonthEncounters = Encounter::where('clinic_id', $clinicId)
                ->whereBetween('created_at', [$lastMonth, $lastMonthEnd])->count();
            $changes['totalEncounters'] = $this->formatChange($thisMonthEncounters, $lastMonthEncounters, 'this month');

            // Pending Prescriptions (status-based, no historical comparison)
            $changes['pendingPrescriptions'] = 'Needs attention';

            // Completed Today (today's completions, no historical comparison)
            $changes['completedEncounters'] = 'Today\'s completions';

            return $changes;
        } catch (\Exception $e) {
            return $this->getDefaultChanges();
        }
    }

    /**
     * Format change value with percentage or absolute difference
     */
    private function formatChange($current, $previous, $period)
    {
        if ($previous == 0) {
            return $current > 0 ? "+{$current} {$period}" : "No change {$period}";
        }

        $difference = $current - $previous;
        $percentage = round(($difference / $previous) * 100, 1);

        if ($difference > 0) {
            return "+{$percentage}% {$period}";
        } elseif ($difference < 0) {
            return "{$percentage}% {$period}";
        } else {
            return "No change {$period}";
        }
    }

    /**
     * Get default changes when calculation fails
     */
    private function getDefaultChanges()
    {
        return [
            'totalUsers' => 'No change this month',
            'totalPatients' => 'No change this month',
            'todayAppointments' => 'No change from yesterday',
            'activeQueue' => 'Real-time',
            'totalAppointments' => 'No change this month',
            'totalEncounters' => 'No change this month',
            'pendingPrescriptions' => 'Needs attention',
            'completedEncounters' => 'Today\'s completions',
        ];
    }
}
