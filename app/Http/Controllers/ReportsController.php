<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Appointment;
use App\Models\Encounter;
use App\Models\Prescription;
use App\Models\User;
use App\Models\UserClinicRole;
use App\Services\SettingsService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class ReportsController extends Controller
{
    /**
     * Display the reports page
     */
    public function index(Request $request): Response
    {
        $this->logWebRequest('Reports Access', ['action' => 'index']);
        
        $user = $request->user();
        $userClinicRole = $this->getUserClinicRole($request);

        if (!$userClinicRole) {
            return Inertia::render('admin/reports', [
                'analytics' => [],
                'recentReports' => [],
                'permissions' => [],
            ]);
        }

        $clinicId = $userClinicRole->clinic_id;

        // Get analytics data
        $analytics = $this->getAnalytics($clinicId);

        // Get recent reports
        $recentReports = $this->getRecentReports($clinicId);

        // Get user permissions
        $permissions = $this->getUserPermissions($userClinicRole->role->name ?? 'user');

        return Inertia::render('admin/reports', [
            'analytics' => $analytics,
            'recentReports' => $recentReports,
            'permissions' => $permissions,
        ]);
    }

    /**
     * Get analytics data
     */
    public function analytics(Request $request)
    {
        $user = $request->user();
        $userClinicRole = $user->userClinicRoles()->with(['clinic'])->first();

        if (!$userClinicRole) {
            return response()->json([
                'success' => false,
                'message' => 'No clinic access'
            ], 403);
        }

        $clinicId = $userClinicRole->clinic_id;
        $analytics = $this->getAnalytics($clinicId);

        return response()->json([
            'success' => true,
            'analytics' => $analytics
        ]);
    }

    /**
     * Display the analytics page with data
     */
    public function analyticsPage(Request $request): Response
    {
        $this->logWebRequest('Analytics Page Access', ['action' => 'analyticsPage']);
        
        $user = $request->user();
        $userClinicRole = $this->getUserClinicRole($request);

        if (!$userClinicRole) {
            return Inertia::render('admin/analytics', [
                'topPerformers' => [],
                'analytics' => [],
            ]);
        }

        $clinicId = $userClinicRole->clinic_id;
        $topPerformers = $this->getTopPerformers($clinicId);
        $analytics = $this->getAnalytics($clinicId);

        return Inertia::render('admin/analytics', [
            'topPerformers' => $topPerformers,
            'analytics' => $analytics,
        ]);
    }

    /**
     * Generate a new report
     */
    public function generate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'report_type' => 'required|in:monthly_patient,doctor_performance,revenue,appointment_analytics',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'format' => 'required|in:pdf,excel,csv'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors())->withInput();
        }

        try {
            $user = $request->user();
            $userClinicRole = $user->userClinicRoles()->with(['clinic'])->first();

            if (!$userClinicRole) {
                return redirect()->back()->with('error', 'No clinic access');
            }

            // Get reports settings
            $settingsService = app(SettingsService::class);
            $allowedFormats = $settingsService->get('reports.export_formats', ['pdf', 'excel', 'csv'], $userClinicRole->clinic_id);
            $includePatientData = $settingsService->get('reports.include_patient_data', true, $userClinicRole->clinic_id);

            // Validate format against allowed formats
            if (!in_array($request->format, $allowedFormats)) {
                return redirect()->back()->with('error', 'Format not allowed. Allowed formats: ' . implode(', ', $allowedFormats))->withInput();
            }

            $clinicId = $userClinicRole->clinic_id;
            $reportType = $request->report_type;
            $startDate = $request->start_date;
            $endDate = $request->end_date;
            $format = $request->format;

            // Generate report data
            $reportData = $this->generateReportData($reportType, $clinicId, $startDate, $endDate);

            // Generate file
            $fileName = $this->generateReportFile($reportType, $reportData, $format, $startDate, $endDate);

            // Store report record
            $report = $this->storeReportRecord($reportType, $fileName, $startDate, $endDate, $clinicId);

            return redirect()->route('admin.reports')->with('success', 'Report generated successfully')->with('download_url', route('reports.download', $report->id));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to generate report. Please try again.')->withInput();
        }
    }

    /**
     * Download a report
     */
    public function download($reportId)
    {
        try {
            $report = DB::table('generated_reports')->find($reportId);

            if (!$report) {
                abort(404, 'Report not found');
            }

            $filePath = storage_path('app/reports/' . $report->file_name);

            if (!file_exists($filePath)) {
                abort(404, 'Report file not found');
            }

            return response()->download($filePath, $report->original_name);

        } catch (\Exception $e) {
            abort(500, 'Failed to download report');
        }
    }

    /**
     * Get analytics data for the clinic
     */
    private function getAnalytics($clinicId)
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();
        $startOfLastMonth = $now->copy()->subMonth()->startOfMonth();
        $endOfLastMonth = $now->copy()->subMonth()->endOfMonth();
        $startOfWeek = $now->copy()->startOfWeek();
        $endOfWeek = $now->copy()->endOfWeek();

        // Patient analytics
        $totalPatients = Patient::where('clinic_id', $clinicId)->count();
        $newPatientsThisMonth = Patient::where('clinic_id', $clinicId)
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->count();
        $newPatientsLastMonth = Patient::where('clinic_id', $clinicId)
            ->whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])
            ->count();
        $newPatientsThisWeek = Patient::where('clinic_id', $clinicId)
            ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
            ->count();

        // Appointment analytics
        $totalAppointments = Appointment::where('clinic_id', $clinicId)->count();
        $appointmentsThisMonth = Appointment::where('clinic_id', $clinicId)
            ->whereBetween('start_at', [$startOfMonth, $endOfMonth])
            ->count();
        $appointmentsLastMonth = Appointment::where('clinic_id', $clinicId)
            ->whereBetween('start_at', [$startOfLastMonth, $endOfLastMonth])
            ->count();
        $appointmentsThisWeek = Appointment::where('clinic_id', $clinicId)
            ->whereBetween('start_at', [$startOfWeek, $endOfWeek])
            ->count();

        // Doctor analytics
        $totalDoctors = Doctor::where('clinic_id', $clinicId)->count();
        $activeDoctors = Doctor::where('clinic_id', $clinicId)
            ->whereHas('appointments', function($query) use ($startOfMonth, $endOfMonth) {
                $query->whereBetween('start_at', [$startOfMonth, $endOfMonth]);
            })
            ->count();

        // Revenue analytics (using total_amount from appointments)
        $revenueThisMonth = Appointment::where('clinic_id', $clinicId)
            ->whereBetween('start_at', [$startOfMonth, $endOfMonth])
            ->where('status', 'completed')
            ->sum('total_amount') ?? 0;
        $revenueLastMonth = Appointment::where('clinic_id', $clinicId)
            ->whereBetween('start_at', [$startOfLastMonth, $endOfLastMonth])
            ->where('status', 'completed')
            ->sum('total_amount') ?? 0;
        $revenueThisWeek = Appointment::where('clinic_id', $clinicId)
            ->whereBetween('start_at', [$startOfWeek, $endOfWeek])
            ->where('status', 'completed')
            ->sum('total_amount') ?? 0;

        // Appointment status breakdown (current month)
        $appointmentStatuses = Appointment::where('clinic_id', $clinicId)
            ->whereBetween('start_at', [$startOfMonth, $endOfMonth])
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status');

        // Daily appointment trends (last 30 days)
        $dailyTrends = Appointment::where('clinic_id', $clinicId)
            ->where('start_at', '>=', $now->copy()->subDays(30))
            ->select(DB::raw('DATE(start_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Weekly trends (last 12 weeks)
        $weeklyTrends = Appointment::where('clinic_id', $clinicId)
            ->where('start_at', '>=', $now->copy()->subWeeks(12))
            ->select(DB::raw('YEARWEEK(start_at) as week'), DB::raw('count(*) as count'))
            ->groupBy('week')
            ->orderBy('week')
            ->get();

        // Doctor performance (top 5 by appointment count with detailed metrics)
        $doctorPerformance = Doctor::where('clinic_id', $clinicId)
            ->with(['user', 'appointments' => function($query) use ($startOfMonth, $endOfMonth) {
                $query->whereBetween('start_at', [$startOfMonth, $endOfMonth]);
            }])
            ->get()
            ->map(function($doctor) use ($startOfMonth, $endOfMonth) {
                $appointments = $doctor->appointments;
                $completedAppointments = $appointments->where('status', 'completed');
                $cancelledAppointments = $appointments->where('status', 'cancelled');
                $noShowAppointments = $appointments->where('status', 'no_show');
                
                return [
                    'id' => $doctor->id,
                    'name' => $doctor->user->name ?? 'Unknown Doctor',
                    'specialization' => $doctor->specialization ?? 'General Practice',
                    'appointments_count' => $appointments->count(),
                    'completed_appointments' => $completedAppointments->count(),
                    'cancelled_appointments' => $cancelledAppointments->count(),
                    'no_show_appointments' => $noShowAppointments->count(),
                    'completion_rate' => $appointments->count() > 0 ? round(($completedAppointments->count() / $appointments->count()) * 100, 2) : 0,
                    'average_consultation_time' => $this->calculateAverageConsultationTime($completedAppointments),
                    'total_revenue' => $completedAppointments->sum('total_amount') ?? 0,
                ];
            })
            ->sortByDesc('appointments_count')
            ->take(5)
            ->values();

        // Patient demographics
        $patientDemographics = Patient::where('clinic_id', $clinicId)
            ->select('sex', DB::raw('count(*) as count'))
            ->groupBy('sex')
            ->get()
            ->pluck('count', 'sex');

        // Age distribution - calculate from date of birth
        $ageDistribution = Patient::where('clinic_id', $clinicId)
            ->whereNotNull('dob')
            ->selectRaw('
                CASE 
                    WHEN TIMESTAMPDIFF(YEAR, dob, CURDATE()) < 18 THEN "0-17"
                    WHEN TIMESTAMPDIFF(YEAR, dob, CURDATE()) BETWEEN 18 AND 35 THEN "18-35"
                    WHEN TIMESTAMPDIFF(YEAR, dob, CURDATE()) BETWEEN 36 AND 55 THEN "36-55"
                    WHEN TIMESTAMPDIFF(YEAR, dob, CURDATE()) BETWEEN 56 AND 75 THEN "56-75"
                    ELSE "75+"
                END as age_group,
                COUNT(*) as count
            ')
            ->groupBy('age_group')
            ->get()
            ->pluck('count', 'age_group');

        // Appointment types distribution
        $appointmentTypes = Appointment::where('clinic_id', $clinicId)
            ->whereBetween('start_at', [$startOfMonth, $endOfMonth])
            ->select('appointment_type', DB::raw('count(*) as count'))
            ->groupBy('appointment_type')
            ->get()
            ->pluck('count', 'appointment_type');

        // Peak hours analysis
        $peakHours = Appointment::where('clinic_id', $clinicId)
            ->whereBetween('start_at', [$startOfMonth, $endOfMonth])
            ->select(DB::raw('HOUR(start_at) as hour'), DB::raw('count(*) as count'))
            ->groupBy('hour')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get();

        // Average wait time calculation
        $averageWaitTime = Appointment::where('clinic_id', $clinicId)
            ->whereBetween('start_at', [$startOfMonth, $endOfMonth])
            ->where('status', 'completed')
            ->whereNotNull('end_at')
            ->get()
            ->map(function($appointment) {
                $startTime = Carbon::parse($appointment->start_at);
                $endTime = Carbon::parse($appointment->end_at);
                return $endTime->diffInMinutes($startTime);
            })
            ->avg();

        // No-show rate calculation
        $totalAppointmentsForNoShow = Appointment::where('clinic_id', $clinicId)
            ->whereBetween('start_at', [$startOfMonth, $endOfMonth])
            ->count();
        $noShowCount = Appointment::where('clinic_id', $clinicId)
            ->whereBetween('start_at', [$startOfMonth, $endOfMonth])
            ->where('status', 'no_show')
            ->count();
        $noShowRate = $totalAppointmentsForNoShow > 0 ? round(($noShowCount / $totalAppointmentsForNoShow) * 100, 2) : 0;

        return [
            'overview' => [
                'total_patients' => $totalPatients,
                'new_patients_this_month' => $newPatientsThisMonth,
                'new_patients_last_month' => $newPatientsLastMonth,
                'new_patients_this_week' => $newPatientsThisWeek,
                'patients_growth' => $this->calculateGrowthRate($newPatientsThisMonth, $newPatientsLastMonth),

                'total_appointments' => $totalAppointments,
                'appointments_this_month' => $appointmentsThisMonth,
                'appointments_last_month' => $appointmentsLastMonth,
                'appointments_this_week' => $appointmentsThisWeek,
                'appointments_growth' => $this->calculateGrowthRate($appointmentsThisMonth, $appointmentsLastMonth),

                'total_doctors' => $totalDoctors,
                'active_doctors' => $activeDoctors,

                'revenue_this_month' => $revenueThisMonth,
                'revenue_last_month' => $revenueLastMonth,
                'revenue_this_week' => $revenueThisWeek,
                'revenue_growth' => $this->calculateGrowthRate($revenueThisMonth, $revenueLastMonth),
                
                'average_wait_time' => round($averageWaitTime ?? 0, 1),
                'no_show_rate' => $noShowRate,
            ],
            'appointment_statuses' => $appointmentStatuses,
            'appointment_types' => $appointmentTypes,
            'daily_trends' => $dailyTrends,
            'weekly_trends' => $weeklyTrends,
            'doctor_performance' => $doctorPerformance,
            'patient_demographics' => $patientDemographics,
            'age_distribution' => $ageDistribution,
            'peak_hours' => $peakHours,
        ];
    }

    /**
     * Generate report data based on type
     */
    private function generateReportData($reportType, $clinicId, $startDate, $endDate)
    {
        switch ($reportType) {
            case 'monthly_patient':
                return $this->generateMonthlyPatientReport($clinicId, $startDate, $endDate);
            case 'doctor_performance':
                return $this->generateDoctorPerformanceReport($clinicId, $startDate, $endDate);
            case 'revenue':
                return $this->generateRevenueReport($clinicId, $startDate, $endDate);
            case 'appointment_analytics':
                return $this->generateAppointmentAnalyticsReport($clinicId, $startDate, $endDate);
            default:
                throw new \Exception('Invalid report type');
        }
    }

    /**
     * Generate monthly patient report data
     */
    private function generateMonthlyPatientReport($clinicId, $startDate, $endDate)
    {
        $patients = Patient::where('clinic_id', $clinicId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with(['appointments', 'encounters'])
            ->get();

        $demographics = [
            'age_groups' => [
                '0-18' => $patients->filter(fn($p) => $p->age >= 0 && $p->age <= 18)->count(),
                '19-35' => $patients->filter(fn($p) => $p->age >= 19 && $p->age <= 35)->count(),
                '36-55' => $patients->filter(fn($p) => $p->age >= 36 && $p->age <= 55)->count(),
                '55+' => $patients->filter(fn($p) => $p->age >= 55)->count(),
            ],
            'gender_distribution' => $patients->groupBy('sex')->map->count(),
            'insurance_providers' => $patients->pluck('insurance.provider')->filter()->groupBy(function($provider) {
                return $provider;
            })->map->count(),
        ];

        return [
            'period' => ['start' => $startDate, 'end' => $endDate],
            'total_patients' => $patients->count(),
            'demographics' => $demographics,
            'patients' => $patients->map(function($patient) {
                return [
                    'id' => $patient->id,
                    'name' => $patient->name,
                    'age' => $patient->age,
                    'sex' => $patient->sex,
                    'appointments_count' => $patient->appointments->count(),
                    'encounters_count' => $patient->encounters->count(),
                    'created_at' => $patient->created_at->format('Y-m-d'),
                ];
            }),
        ];
    }

    /**
     * Generate doctor performance report data
     */
    private function generateDoctorPerformanceReport($clinicId, $startDate, $endDate)
    {
        $doctors = Doctor::where('clinic_id', $clinicId)
            ->with(['user', 'appointments' => function($query) use ($startDate, $endDate) {
                $query->whereBetween('start_at', [$startDate, $endDate]);
            }])
            ->get();

        return [
            'period' => ['start' => $startDate, 'end' => $endDate],
            'total_doctors' => $doctors->count(),
            'doctors' => $doctors->map(function($doctor) {
                $appointments = $doctor->appointments;
                $completedAppointments = $appointments->where('status', 'completed');

                return [
                    'id' => $doctor->id,
                    'name' => $doctor->user->name,
                    'specialization' => $doctor->specialization,
                    'total_appointments' => $appointments->count(),
                    'completed_appointments' => $completedAppointments->count(),
                    'cancelled_appointments' => $appointments->where('status', 'cancelled')->count(),
                    'no_show_appointments' => $appointments->where('status', 'no_show')->count(),
                    'completion_rate' => $appointments->count() > 0 ? round(($completedAppointments->count() / $appointments->count()) * 100, 2) : 0,
                    'average_consultation_time' => $this->calculateAverageConsultationTime($completedAppointments),
                ];
            })->sortByDesc('total_appointments')->values(),
        ];
    }

    /**
     * Generate revenue report data
     */
    private function generateRevenueReport($clinicId, $startDate, $endDate)
    {
        $appointments = Appointment::where('clinic_id', $clinicId)
            ->whereBetween('start_at', [$startDate, $endDate])
            ->with(['doctor.user', 'patient'])
            ->get();

        $completedAppointments = $appointments->where('status', 'completed');
        $totalRevenue = $completedAppointments->sum('total_amount') ?? 0;

        $revenueByDoctor = $completedAppointments->groupBy('doctor_id')->map(function($doctorAppointments) {
            return [
                'doctor_name' => $doctorAppointments->first()->doctor->user->name,
                'appointments_count' => $doctorAppointments->count(),
                'total_revenue' => $doctorAppointments->sum('total_amount') ?? 0,
            ];
        })->sortByDesc('total_revenue');

        $dailyRevenue = $completedAppointments->groupBy(function($appointment) {
            return $appointment->start_at->format('Y-m-d');
        })->map(function($dayAppointments) {
            return $dayAppointments->sum('total_amount') ?? 0;
        });

        return [
            'period' => ['start' => $startDate, 'end' => $endDate],
            'total_revenue' => $totalRevenue,
            'total_appointments' => $appointments->count(),
            'completed_appointments' => $completedAppointments->count(),
            'cancelled_appointments' => $appointments->where('status', 'cancelled')->count(),
            'no_show_appointments' => $appointments->where('status', 'no_show')->count(),
            'revenue_by_doctor' => $revenueByDoctor,
            'daily_revenue' => $dailyRevenue,
            'average_revenue_per_appointment' => $completedAppointments->count() > 0 ? round($totalRevenue / $completedAppointments->count(), 2) : 0,
        ];
    }

    /**
     * Generate appointment analytics report data
     */
    private function generateAppointmentAnalyticsReport($clinicId, $startDate, $endDate)
    {
        $appointments = Appointment::where('clinic_id', $clinicId)
            ->whereBetween('start_at', [$startDate, $endDate])
            ->with(['doctor.user', 'patient'])
            ->get();

        $hourlyDistribution = $appointments->groupBy(function($appointment) {
            return $appointment->start_at->format('H');
        })->map->count();

        $weeklyDistribution = $appointments->groupBy(function($appointment) {
            return $appointment->start_at->format('l');
        })->map->count();

        $appointmentTypes = $appointments->groupBy('type')->map->count();

        $statusDistribution = $appointments->groupBy('status')->map->count();

        $waitingTimeAnalysis = $appointments->where('status', 'completed')->map(function($appointment) {
            $startTime = Carbon::parse($appointment->start_at);
            $endTime = Carbon::parse($appointment->end_at);
            return $endTime->diffInMinutes($startTime);
        });

        return [
            'period' => ['start' => $startDate, 'end' => $endDate],
            'total_appointments' => $appointments->count(),
            'hourly_distribution' => $hourlyDistribution,
            'weekly_distribution' => $weeklyDistribution,
            'appointment_types' => $appointmentTypes,
            'status_distribution' => $statusDistribution,
            'average_consultation_time' => $waitingTimeAnalysis->count() > 0 ? round($waitingTimeAnalysis->avg(), 2) : 0,
            'min_consultation_time' => $waitingTimeAnalysis->min(),
            'max_consultation_time' => $waitingTimeAnalysis->max(),
        ];
    }

    /**
     * Generate report file
     */
    private function generateReportFile($reportType, $reportData, $format, $startDate, $endDate)
    {
        $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
        $fileName = "{$reportType}_{$timestamp}.{$format}";
        $filePath = storage_path("app/reports/{$fileName}");

        // Ensure reports directory exists
        if (!file_exists(storage_path('app/reports'))) {
            mkdir(storage_path('app/reports'), 0755, true);
        }

        // Generate file based on format
        switch ($format) {
            case 'csv':
                $this->generateCsvFile($filePath, $reportData, $reportType);
                break;
            case 'excel':
                $this->generateExcelFile($filePath, $reportData, $reportType);
                break;
            case 'pdf':
                $this->generatePdfFile($filePath, $reportData, $reportType);
                break;
        }

        return $fileName;
    }

    /**
     * Generate CSV file
     */
    private function generateCsvFile($filePath, $reportData, $reportType)
    {
        $handle = fopen($filePath, 'w');

        // Add headers based on report type
        switch ($reportType) {
            case 'monthly_patient':
                fputcsv($handle, ['ID', 'Name', 'Age', 'Gender', 'Appointments', 'Encounters', 'Created Date']);
                foreach ($reportData['patients'] as $patient) {
                    fputcsv($handle, [
                        $patient['id'],
                        $patient['name'],
                        $patient['age'],
                        $patient['sex'],
                        $patient['appointments_count'],
                        $patient['encounters_count'],
                        $patient['created_at']
                    ]);
                }
                break;
            case 'doctor_performance':
                fputcsv($handle, ['Doctor', 'Specialization', 'Total Appointments', 'Completed', 'Cancelled', 'No Show', 'Completion Rate']);
                foreach ($reportData['doctors'] as $doctor) {
                    fputcsv($handle, [
                        $doctor['name'],
                        $doctor['specialization'],
                        $doctor['total_appointments'],
                        $doctor['completed_appointments'],
                        $doctor['cancelled_appointments'],
                        $doctor['no_show_appointments'],
                        $doctor['completion_rate'] . '%'
                    ]);
                }
                break;
            // Add other report types as needed
        }

        fclose($handle);
    }

    /**
     * Generate Excel file (simplified - you might want to use a proper Excel library)
     */
    private function generateExcelFile($filePath, $reportData, $reportType)
    {
        // For now, generate as CSV with .xlsx extension
        // In a real implementation, you'd use PhpSpreadsheet or similar
        $this->generateCsvFile($filePath, $reportData, $reportType);
    }

    /**
     * Generate PDF file (simplified - you might want to use a proper PDF library)
     */
    private function generatePdfFile($filePath, $reportData, $reportType)
    {
        // For now, generate as text file with .pdf extension
        // In a real implementation, you'd use TCPDF, DomPDF, or similar
        $content = "Report: " . ucwords(str_replace('_', ' ', $reportType)) . "\n";
        $content .= "Period: {$reportData['period']['start']} to {$reportData['period']['end']}\n\n";

        switch ($reportType) {
            case 'monthly_patient':
                $content .= "Total Patients: {$reportData['total_patients']}\n\n";
                $content .= "Patient List:\n";
                foreach ($reportData['patients'] as $patient) {
                    $content .= "- {$patient['name']} (Age: {$patient['age']}, Gender: {$patient['sex']})\n";
                }
                break;
            // Add other report types as needed
        }

        file_put_contents($filePath, $content);
    }

    /**
     * Store report record in database
     */
    private function storeReportRecord($reportType, $fileName, $startDate, $endDate, $clinicId)
    {
        $reportId = DB::table('generated_reports')->insertGetId([
            'report_type' => $reportType,
            'file_name' => $fileName,
            'original_name' => ucwords(str_replace('_', ' ', $reportType)) . '_' . $startDate . '_to_' . $endDate . '.pdf',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'clinic_id' => $clinicId,
            'generated_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return (object) [
            'id' => $reportId,
            'report_type' => $reportType,
            'file_name' => $fileName,
            'original_name' => ucwords(str_replace('_', ' ', $reportType)) . '_' . $startDate . '_to_' . $endDate . '.pdf',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'generated_at' => now()->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Get recent reports
     */
    private function getRecentReports($clinicId)
    {
        return DB::table('generated_reports')
            ->where('clinic_id', $clinicId)
            ->orderBy('generated_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function($report) {
                return [
                    'id' => $report->id,
                    'report_type' => $report->report_type,
                    'original_name' => $report->original_name,
                    'start_date' => $report->start_date,
                    'end_date' => $report->end_date,
                    'generated_at' => $report->generated_at,
                ];
            });
    }

    /**
     * Calculate growth rate percentage
     */
    private function calculateGrowthRate($current, $previous)
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }
        return round((($current - $previous) / $previous) * 100, 2);
    }

    /**
     * Calculate average consultation time
     */
    private function calculateAverageConsultationTime($appointments)
    {
        if ($appointments->count() == 0) {
            return 0;
        }

        $totalMinutes = $appointments->sum(function($appointment) {
            $startTime = Carbon::parse($appointment->start_at);
            $endTime = Carbon::parse($appointment->end_at);
            return $endTime->diffInMinutes($startTime);
        });

        return round($totalMinutes / $appointments->count(), 2);
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

    /**
     * Get top performing doctors
     */
    private function getTopPerformers($clinicId)
    {
        try {
            $now = Carbon::now();
            $startOfMonth = $now->copy()->startOfMonth();
            $endOfMonth = $now->copy()->endOfMonth();
            $startOfLastMonth = $now->copy()->subMonth()->startOfMonth();
            $endOfLastMonth = $now->copy()->subMonth()->endOfMonth();

            // Get doctors with their appointment counts and performance metrics
            $doctors = Doctor::with(['user', 'appointments' => function($query) use ($startOfMonth, $endOfMonth) {
                $query->whereBetween('start_at', [$startOfMonth, $endOfMonth]);
            }])
                ->where('clinic_id', $clinicId)
                ->get()
                ->map(function ($doctor) use ($startOfMonth, $endOfMonth, $startOfLastMonth, $endOfLastMonth) {
                    $appointments = $doctor->appointments;
                    $completedAppointments = $appointments->where('status', 'completed');
                    $totalRevenue = $completedAppointments->sum('total_amount') ?? 0;
                    
                    // Calculate unique patients count
                    $uniquePatients = $appointments->pluck('patient_id')->unique()->count();
                    
                    // Calculate completion rate
                    $completionRate = $appointments->count() > 0 ? 
                        round(($completedAppointments->count() / $appointments->count()) * 100, 1) : 0;
                    
                    // Calculate average consultation time
                    $avgConsultationTime = $this->calculateAverageConsultationTime($completedAppointments);
                    
                    // Calculate rating based on performance metrics
                    // Base rating of 4.0, with bonuses for good performance
                    $baseRating = 4.0;
                    $completionBonus = min(0.5, ($completionRate - 80) / 100); // Up to 0.5 bonus for >80% completion
                    $volumeBonus = min(0.3, $appointments->count() / 100); // Up to 0.3 bonus for high volume
                    $timeBonus = $avgConsultationTime > 0 && $avgConsultationTime <= 30 ? 0.2 : 0; // Bonus for efficient consultations
                    
                    $calculatedRating = min(5.0, $baseRating + $completionBonus + $volumeBonus + $timeBonus);
                    
                    // Get last month's performance for comparison
                    $lastMonthAppointments = $doctor->appointments()
                        ->whereBetween('start_at', [$startOfLastMonth, $endOfLastMonth])
                        ->count();
                    $lastMonthRevenue = $doctor->appointments()
                        ->whereBetween('start_at', [$startOfLastMonth, $endOfLastMonth])
                        ->where('status', 'completed')
                        ->sum('total_amount') ?? 0;
                    
                    return [
                        'name' => $doctor->user->name ?? 'Unknown Doctor',
                        'specialty' => $doctor->specialization ?? 'General Practice',
                        'patients' => $uniquePatients,
                        'rating' => round($calculatedRating, 1),
                        'revenue' => '$' . number_format($totalRevenue, 0),
                        'appointments_count' => $appointments->count(),
                        'completion_rate' => $completionRate,
                        'average_consultation_time' => $avgConsultationTime,
                        'revenue_growth' => $this->calculateGrowthRate($totalRevenue, $lastMonthRevenue),
                        'appointments_growth' => $this->calculateGrowthRate($appointments->count(), $lastMonthAppointments),
                    ];
                })
                ->sortByDesc('patients')
                ->take(5)
                ->values()
                ->toArray();

            // If no doctors found, return empty array instead of mock data
            if (empty($doctors)) {
                return [];
            }

            return $doctors;
        } catch (\Exception $e) {
            // Log the error and return empty array
            \Log::error('Error getting top performers: ' . $e->getMessage());
            return [];
        }
    }
}
