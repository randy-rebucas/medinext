<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Encounter;
use App\Models\Prescription;
use App\Models\LabResult;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class DashboardController extends BaseController
{
    /**
     * Get dashboard data
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $currentClinic = $this->getCurrentClinic();

            if (!$currentClinic) {
                return $this->errorResponse('No clinic access', null, 403);
            }

            $user = $this->getAuthenticatedUser();
            $isMobile = $this->isMobileRequest($request);

            $dashboardData = [
                'clinic' => $currentClinic,
                'user' => $user,
                'statistics' => $this->getStatistics($currentClinic->id),
                'today_appointments' => $this->getTodayAppointments($currentClinic->id),
                'upcoming_appointments' => $this->getUpcomingAppointments($currentClinic->id),
                'recent_patients' => $this->getRecentPatients($currentClinic->id),
                'pending_tasks' => $this->getPendingTasks($currentClinic->id),
                'notifications' => $this->getNotifications($currentClinic->id),
            ];

            if ($isMobile) {
                $dashboardData['quick_actions'] = $this->getQuickActions();
            }

            return $this->successResponse($dashboardData, 'Dashboard data retrieved successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Get dashboard statistics
     */
    public function stats(Request $request): JsonResponse
    {
        try {
            $currentClinic = $this->getCurrentClinic();

            if (!$currentClinic) {
                return $this->errorResponse('No clinic access', null, 403);
            }

            $statistics = $this->getStatistics($currentClinic->id);

            return $this->successResponse([
                'statistics' => $statistics,
            ], 'Statistics retrieved successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Get notifications
     */
    public function notifications(Request $request): JsonResponse
    {
        try {
            $currentClinic = $this->getCurrentClinic();

            if (!$currentClinic) {
                return $this->errorResponse('No clinic access', null, 403);
            }

            [$perPage, $page] = $this->getPaginationParams($request);

            $notifications = $this->getNotifications($currentClinic->id, $perPage, $page);

            return $this->successResponse([
                'notifications' => $notifications,
            ], 'Notifications retrieved successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Mark notification as read
     */
    public function markNotificationRead(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'notification_id' => 'required|string',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            // In a real implementation, you would mark the notification as read in the database
            // For now, we'll just return success

            return $this->successResponse(null, 'Notification marked as read');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Get mobile dashboard
     */
    public function mobile(Request $request): JsonResponse
    {
        try {
            $currentClinic = $this->getCurrentClinic();

            if (!$currentClinic) {
                return $this->errorResponse('No clinic access', null, 403);
            }

            $mobileData = [
                'statistics' => $this->getMobileStatistics($currentClinic->id),
                'today_appointments' => $this->getTodayAppointments($currentClinic->id, 10),
                'quick_actions' => $this->getQuickActions(),
                'recent_patients' => $this->getRecentPatients($currentClinic->id, 5),
                'pending_tasks' => $this->getPendingTasks($currentClinic->id, 5),
                'notifications' => $this->getNotifications($currentClinic->id, 10),
            ];

            return $this->successResponse($mobileData, 'Mobile dashboard data retrieved');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Get web dashboard
     */
    public function web(Request $request): JsonResponse
    {
        try {
            $currentClinic = $this->getCurrentClinic();

            if (!$currentClinic) {
                return $this->errorResponse('No clinic access', null, 403);
            }

            $webData = [
                'statistics' => $this->getStatistics($currentClinic->id),
                'appointments_chart' => $this->getAppointmentsChart($currentClinic->id),
                'patients_chart' => $this->getPatientsChart($currentClinic->id),
                'revenue_chart' => $this->getRevenueChart($currentClinic->id),
                'recent_activities' => $this->getRecentActivities($currentClinic->id),
            ];

            return $this->successResponse($webData, 'Web dashboard data retrieved');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Get analytics data
     */
    public function analytics(Request $request): JsonResponse
    {
        try {
            $currentClinic = $this->getCurrentClinic();

            if (!$currentClinic) {
                return $this->errorResponse('No clinic access', null, 403);
            }

            $analytics = [
                'appointments_analytics' => $this->getAppointmentsAnalytics($currentClinic->id),
                'patients_analytics' => $this->getPatientsAnalytics($currentClinic->id),
                'revenue_analytics' => $this->getRevenueAnalytics($currentClinic->id),
                'doctor_performance' => $this->getDoctorPerformance($currentClinic->id),
            ];

            return $this->successResponse($analytics, 'Analytics data retrieved');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Get pending tasks
     */
    public function pendingTasks(Request $request): JsonResponse
    {
        try {
            $currentClinic = $this->getCurrentClinic();

            if (!$currentClinic) {
                return $this->errorResponse('No clinic access', null, 403);
            }

            $limit = $request->get('limit', 10);
            $tasks = $this->getPendingTasks($currentClinic->id, $limit);

            return $this->successResponse([
                'tasks' => $tasks,
            ], 'Pending tasks retrieved');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Get statistics for dashboard
     */
    private function getStatistics(int $clinicId): array
    {
        $today = Carbon::today();
        $thisWeek = Carbon::now()->startOfWeek();
        $thisMonth = Carbon::now()->startOfMonth();

        return [
            'total_patients' => Patient::where('clinic_id', $clinicId)->count(),
            'total_doctors' => Doctor::where('clinic_id', $clinicId)->count(),
            'total_appointments' => Appointment::where('clinic_id', $clinicId)->count(),
            'total_encounters' => Encounter::where('clinic_id', $clinicId)->count(),
            'total_prescriptions' => Prescription::where('clinic_id', $clinicId)->count(),
            'total_lab_results' => LabResult::where('clinic_id', $clinicId)->count(),
            
            'today_appointments' => Appointment::where('clinic_id', $clinicId)
                ->whereDate('start_at', $today)->count(),
            'this_week_appointments' => Appointment::where('clinic_id', $clinicId)
                ->where('start_at', '>=', $thisWeek)->count(),
            'this_month_appointments' => Appointment::where('clinic_id', $clinicId)
                ->where('start_at', '>=', $thisMonth)->count(),
            
            'new_patients_today' => Patient::where('clinic_id', $clinicId)
                ->whereDate('created_at', $today)->count(),
            'new_patients_this_month' => Patient::where('clinic_id', $clinicId)
                ->where('created_at', '>=', $thisMonth)->count(),
            
            'pending_lab_results' => LabResult::where('clinic_id', $clinicId)
                ->where('status', 'pending')->count(),
            'active_prescriptions' => Prescription::where('clinic_id', $clinicId)
                ->where('status', 'active')->count(),
        ];
    }

    /**
     * Get mobile statistics
     */
    private function getMobileStatistics(int $clinicId): array
    {
        $today = Carbon::today();

        return [
            'today_appointments' => Appointment::where('clinic_id', $clinicId)
                ->whereDate('start_at', $today)->count(),
            'pending_tasks' => $this->getPendingTasksCount($clinicId),
            'new_patients_today' => Patient::where('clinic_id', $clinicId)
                ->whereDate('created_at', $today)->count(),
            'pending_lab_results' => LabResult::where('clinic_id', $clinicId)
                ->where('status', 'pending')->count(),
        ];
    }

    /**
     * Get today's appointments
     */
    private function getTodayAppointments(int $clinicId, int $limit = 20): array
    {
        return Appointment::where('clinic_id', $clinicId)
            ->whereDate('start_at', Carbon::today())
            ->with(['patient', 'doctor.user', 'room'])
            ->orderBy('start_at')
            ->limit($limit)
            ->get()
            ->map(function ($appointment) {
                return [
                    'id' => $appointment->id,
                    'patient_name' => $appointment->patient->full_name,
                    'doctor_name' => $appointment->doctor->user->name,
                    'start_time' => $appointment->start_at->format('H:i'),
                    'status' => $appointment->status,
                    'type' => $appointment->appointment_type,
                    'room' => $appointment->room->name ?? null,
                ];
            })
            ->toArray();
    }

    /**
     * Get upcoming appointments
     */
    private function getUpcomingAppointments(int $clinicId, int $limit = 10): array
    {
        return Appointment::where('clinic_id', $clinicId)
            ->where('start_at', '>', now())
            ->where('start_at', '<=', now()->addDays(7))
            ->with(['patient', 'doctor.user', 'room'])
            ->orderBy('start_at')
            ->limit($limit)
            ->get()
            ->map(function ($appointment) {
                return [
                    'id' => $appointment->id,
                    'patient_name' => $appointment->patient->full_name,
                    'doctor_name' => $appointment->doctor->user->name,
                    'start_time' => $appointment->start_at->format('M j, H:i'),
                    'status' => $appointment->status,
                    'type' => $appointment->appointment_type,
                ];
            })
            ->toArray();
    }

    /**
     * Get recent patients
     */
    private function getRecentPatients(int $clinicId, int $limit = 10): array
    {
        return Patient::where('clinic_id', $clinicId)
            ->latest()
            ->limit($limit)
            ->get(['id', 'code', 'first_name', 'last_name', 'dob', 'sex', 'created_at'])
            ->map(function ($patient) {
                return [
                    'id' => $patient->id,
                    'code' => $patient->code,
                    'name' => $patient->full_name,
                    'age' => $patient->age,
                    'sex' => $patient->sex,
                    'created_at' => $patient->created_at->format('M j, Y'),
                ];
            })
            ->toArray();
    }

    /**
     * Get pending tasks
     */
    private function getPendingTasks(int $clinicId, int $limit = 10): array
    {
        $tasks = [];

        // Pending lab results
        $pendingLabResults = LabResult::where('clinic_id', $clinicId)
            ->where('status', 'pending')
            ->with(['patient', 'doctor.user'])
            ->limit(5)
            ->get();

        foreach ($pendingLabResults as $labResult) {
            $tasks[] = [
                'id' => 'lab_' . $labResult->id,
                'type' => 'lab_result',
                'title' => 'Review Lab Result',
                'description' => "Lab result for {$labResult->patient->full_name} - {$labResult->test_name}",
                'priority' => 'medium',
                'due_date' => $labResult->created_at->addDays(2)->format('Y-m-d'),
                'patient_id' => $labResult->patient_id,
                'doctor_id' => $labResult->doctor_id,
            ];
        }

        // Overdue appointments
        $overdueAppointments = Appointment::where('clinic_id', $clinicId)
            ->where('start_at', '<', now())
            ->whereIn('status', ['scheduled', 'confirmed'])
            ->with(['patient', 'doctor.user'])
            ->limit(5)
            ->get();

        foreach ($overdueAppointments as $appointment) {
            $tasks[] = [
                'id' => 'appointment_' . $appointment->id,
                'type' => 'appointment',
                'title' => 'Overdue Appointment',
                'description' => "Appointment with {$appointment->patient->full_name} is overdue",
                'priority' => 'high',
                'due_date' => $appointment->start_at->format('Y-m-d'),
                'patient_id' => $appointment->patient_id,
                'doctor_id' => $appointment->doctor_id,
            ];
        }

        return array_slice($tasks, 0, $limit);
    }

    /**
     * Get pending tasks count
     */
    private function getPendingTasksCount(int $clinicId): int
    {
        $count = 0;

        // Pending lab results
        $count += LabResult::where('clinic_id', $clinicId)
            ->where('status', 'pending')
            ->count();

        // Overdue appointments
        $count += Appointment::where('clinic_id', $clinicId)
            ->where('start_at', '<', now())
            ->whereIn('status', ['scheduled', 'confirmed'])
            ->count();

        return $count;
    }

    /**
     * Get notifications
     */
    private function getNotifications(int $clinicId, int $perPage = 20, int $page = 1): array
    {
        $notifications = [];

        // Recent appointments
        $recentAppointments = Appointment::where('clinic_id', $clinicId)
            ->where('created_at', '>=', now()->subDays(7))
            ->with(['patient', 'doctor.user'])
            ->latest()
            ->limit(10)
            ->get();

        foreach ($recentAppointments as $appointment) {
            $notifications[] = [
                'id' => 'appointment_' . $appointment->id,
                'type' => 'appointment',
                'title' => 'New Appointment',
                'message' => "New appointment scheduled with {$appointment->patient->full_name}",
                'date' => $appointment->created_at->format('Y-m-d H:i'),
                'is_read' => false,
            ];
        }

        // Recent patients
        $recentPatients = Patient::where('clinic_id', $clinicId)
            ->where('created_at', '>=', now()->subDays(7))
            ->latest()
            ->limit(5)
            ->get();

        foreach ($recentPatients as $patient) {
            $notifications[] = [
                'id' => 'patient_' . $patient->id,
                'type' => 'patient',
                'title' => 'New Patient',
                'message' => "New patient registered: {$patient->full_name}",
                'date' => $patient->created_at->format('Y-m-d H:i'),
                'is_read' => false,
            ];
        }

        // Sort by date and paginate
        usort($notifications, function ($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });

        $offset = ($page - 1) * $perPage;
        return array_slice($notifications, $offset, $perPage);
    }

    /**
     * Get quick actions for mobile
     */
    private function getQuickActions(): array
    {
        return [
            [
                'id' => 'new_patient',
                'title' => 'New Patient',
                'icon' => 'user-plus',
                'route' => '/patients/create',
                'color' => '#10b981',
            ],
            [
                'id' => 'new_appointment',
                'title' => 'New Appointment',
                'icon' => 'calendar-plus',
                'route' => '/appointments/create',
                'color' => '#3b82f6',
            ],
            [
                'id' => 'new_prescription',
                'title' => 'New Prescription',
                'icon' => 'prescription',
                'route' => '/prescriptions/create',
                'color' => '#8b5cf6',
            ],
            [
                'id' => 'lab_results',
                'title' => 'Lab Results',
                'icon' => 'flask',
                'route' => '/lab-results',
                'color' => '#f59e0b',
            ],
            [
                'id' => 'search_patient',
                'title' => 'Search Patient',
                'icon' => 'search',
                'route' => '/patients/search',
                'color' => '#06b6d4',
            ],
            [
                'id' => 'calendar',
                'title' => 'Calendar',
                'icon' => 'calendar',
                'route' => '/calendar',
                'color' => '#ef4444',
            ],
        ];
    }

    /**
     * Get appointments chart data
     */
    private function getAppointmentsChart(int $clinicId): array
    {
        $data = [];
        $labels = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $count = Appointment::where('clinic_id', $clinicId)
                ->whereDate('start_at', $date)
                ->count();

            $data[] = $count;
            $labels[] = $date->format('M j');
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    /**
     * Get patients chart data
     */
    private function getPatientsChart(int $clinicId): array
    {
        $data = [];
        $labels = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $count = Patient::where('clinic_id', $clinicId)
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();

            $data[] = $count;
            $labels[] = $date->format('M Y');
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    /**
     * Get revenue chart data
     */
    private function getRevenueChart(int $clinicId): array
    {
        $data = [];
        $labels = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $revenue = Appointment::where('clinic_id', $clinicId)
                ->whereYear('start_at', $date->year)
                ->whereMonth('start_at', $date->month)
                ->where('status', 'completed')
                ->sum('total_amount');

            $data[] = (float) $revenue;
            $labels[] = $date->format('M Y');
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    /**
     * Get recent activities
     */
    private function getRecentActivities(int $clinicId): array
    {
        $activities = [];

        // Recent appointments
        $recentAppointments = Appointment::where('clinic_id', $clinicId)
            ->with(['patient', 'doctor.user'])
            ->latest()
            ->limit(10)
            ->get();

        foreach ($recentAppointments as $appointment) {
            $activities[] = [
                'id' => 'appointment_' . $appointment->id,
                'type' => 'appointment',
                'title' => 'Appointment ' . ucfirst($appointment->status),
                'description' => "Appointment with {$appointment->patient->full_name}",
                'date' => $appointment->created_at->format('Y-m-d H:i'),
                'user' => $appointment->doctor->user->name,
            ];
        }

        // Recent patients
        $recentPatients = Patient::where('clinic_id', $clinicId)
            ->latest()
            ->limit(5)
            ->get();

        foreach ($recentPatients as $patient) {
            $activities[] = [
                'id' => 'patient_' . $patient->id,
                'type' => 'patient',
                'title' => 'New Patient',
                'description' => "Patient {$patient->full_name} registered",
                'date' => $patient->created_at->format('Y-m-d H:i'),
                'user' => 'System',
            ];
        }

        // Sort by date
        usort($activities, function ($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });

        return array_slice($activities, 0, 20);
    }

    /**
     * Get appointments analytics
     */
    private function getAppointmentsAnalytics(int $clinicId): array
    {
        return [
            'total_appointments' => Appointment::where('clinic_id', $clinicId)->count(),
            'completed_appointments' => Appointment::where('clinic_id', $clinicId)->where('status', 'completed')->count(),
            'cancelled_appointments' => Appointment::where('clinic_id', $clinicId)->where('status', 'cancelled')->count(),
            'no_show_appointments' => Appointment::where('clinic_id', $clinicId)->where('status', 'no_show')->count(),
            'average_duration' => Appointment::where('clinic_id', $clinicId)->avg('duration'),
            'appointments_by_type' => Appointment::where('clinic_id', $clinicId)
                ->selectRaw('appointment_type, COUNT(*) as count')
                ->groupBy('appointment_type')
                ->get(),
        ];
    }

    /**
     * Get patients analytics
     */
    private function getPatientsAnalytics(int $clinicId): array
    {
        return [
            'total_patients' => Patient::where('clinic_id', $clinicId)->count(),
            'new_patients_this_month' => Patient::where('clinic_id', $clinicId)
                ->where('created_at', '>=', Carbon::now()->startOfMonth())
                ->count(),
            'patients_by_sex' => Patient::where('clinic_id', $clinicId)
                ->selectRaw('sex, COUNT(*) as count')
                ->groupBy('sex')
                ->get(),
            'patients_by_age_group' => Patient::where('clinic_id', $clinicId)
                ->selectRaw('
                    CASE 
                        WHEN TIMESTAMPDIFF(YEAR, dob, CURDATE()) < 18 THEN "Under 18"
                        WHEN TIMESTAMPDIFF(YEAR, dob, CURDATE()) BETWEEN 18 AND 65 THEN "18-65"
                        ELSE "Over 65"
                    END as age_group,
                    COUNT(*) as count
                ')
                ->groupBy('age_group')
                ->get(),
        ];
    }

    /**
     * Get revenue analytics
     */
    private function getRevenueAnalytics(int $clinicId): array
    {
        return [
            'total_revenue' => Appointment::where('clinic_id', $clinicId)
                ->where('status', 'completed')
                ->sum('total_amount'),
            'monthly_revenue' => Appointment::where('clinic_id', $clinicId)
                ->where('status', 'completed')
                ->where('start_at', '>=', Carbon::now()->startOfMonth())
                ->sum('total_amount'),
            'average_appointment_value' => Appointment::where('clinic_id', $clinicId)
                ->where('status', 'completed')
                ->avg('total_amount'),
            'revenue_by_type' => Appointment::where('clinic_id', $clinicId)
                ->where('status', 'completed')
                ->selectRaw('appointment_type, SUM(total_amount) as revenue')
                ->groupBy('appointment_type')
                ->get(),
        ];
    }

    /**
     * Get doctor performance
     */
    private function getDoctorPerformance(int $clinicId): array
    {
        return Doctor::where('clinic_id', $clinicId)
            ->with(['user', 'appointments', 'encounters'])
            ->get()
            ->map(function ($doctor) {
                return [
                    'id' => $doctor->id,
                    'name' => $doctor->user->name,
                    'specialty' => $doctor->specialty,
                    'total_appointments' => $doctor->appointments->count(),
                    'completed_appointments' => $doctor->appointments->where('status', 'completed')->count(),
                    'total_encounters' => $doctor->encounters->count(),
                    'average_rating' => 4.5, // This would come from a ratings system
                ];
            })
            ->toArray();
    }
}
