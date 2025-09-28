<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home')->middleware('installation.check');

// CSRF token route for AJAX requests
Route::get('/csrf-token', function () {
    return response()->json(['csrf_token' => csrf_token()]);
})->name('csrf-token');

Route::middleware(['installation.check', 'auth', 'verified', 'trial.check', 'onboarding.check', 'clinic.current'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Admin routes - Clinic admin has full access to all management areas
    Route::prefix('admin')->middleware(['permission:clinics.manage'])->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

        // ===== DOCTOR MANAGEMENT =====
        // Admin has full CRUD access to doctor management
        Route::middleware(['permission:doctors.view'])->group(function () {
            Route::get('doctors', [App\Http\Controllers\DoctorController::class, 'index'])->name('admin.doctors');
            Route::get('doctors/{id}', [App\Http\Controllers\DoctorController::class, 'show'])->name('admin.doctors.show');
        });
        
        Route::middleware(['permission:doctors.create'])->group(function () {
            Route::post('doctors', [App\Http\Controllers\DoctorController::class, 'store'])->name('admin.doctors.store');
        });
        
        Route::middleware(['permission:doctors.edit'])->group(function () {
            Route::put('doctors/{id}', [App\Http\Controllers\DoctorController::class, 'update'])->name('admin.doctors.update');
        });
        
        Route::middleware(['permission:doctors.delete'])->group(function () {
            Route::delete('doctors/{id}', [App\Http\Controllers\DoctorController::class, 'destroy'])->name('admin.doctors.destroy');
        });

        // ===== STAFF MANAGEMENT =====
        // Admin has full CRUD access to staff management
        Route::middleware(['permission:staff.view'])->group(function () {
            Route::get('staff', [App\Http\Controllers\StaffController::class, 'index'])->name('admin.staff');
            Route::get('staff/{id}', [App\Http\Controllers\StaffController::class, 'show'])->name('admin.staff.show');
        });
        
        Route::middleware(['permission:staff.create'])->group(function () {
            Route::post('staff', [App\Http\Controllers\StaffController::class, 'store'])->name('admin.staff.store');
        });
        
        Route::middleware(['permission:staff.edit'])->group(function () {
            Route::put('staff/{id}', [App\Http\Controllers\StaffController::class, 'update'])->name('admin.staff.update');
        });
        
        Route::middleware(['permission:staff.delete'])->group(function () {
            Route::delete('staff/{id}', [App\Http\Controllers\StaffController::class, 'destroy'])->name('admin.staff.destroy');
        });

        // Staff import routes
        Route::middleware(['permission:staff.create'])->group(function () {
            Route::post('staff/import', [App\Http\Controllers\StaffController::class, 'import'])->name('admin.staff.import');
            Route::get('staff/import/template', [App\Http\Controllers\StaffController::class, 'downloadTemplate'])->name('admin.staff.import.template');
        });

        // ===== PATIENT MANAGEMENT =====
        // Admin has full CRUD access to patient management
        Route::middleware(['permission:patients.view'])->group(function () {
            Route::get('patients', [App\Http\Controllers\PatientController::class, 'index'])->name('admin.patients');
            Route::get('patients/{id}', [App\Http\Controllers\PatientController::class, 'show'])->name('admin.patients.show');
            Route::get('patients/{id}/health-records', [App\Http\Controllers\PatientController::class, 'healthRecords'])->name('admin.patients.health-records');
        });
        
        Route::middleware(['permission:patients.create'])->group(function () {
            Route::post('patients', [App\Http\Controllers\PatientController::class, 'store'])->name('admin.patients.store');
        });
        
        Route::middleware(['permission:patients.edit'])->group(function () {
            Route::put('patients/{id}', [App\Http\Controllers\PatientController::class, 'update'])->name('admin.patients.update');
        });
        
        Route::middleware(['permission:patients.delete'])->group(function () {
            Route::delete('patients/{id}', [App\Http\Controllers\PatientController::class, 'destroy'])->name('admin.patients.destroy');
        });

        // ===== APPOINTMENT MANAGEMENT =====
        // Admin has full CRUD access to appointment management
        Route::middleware(['permission:appointments.view'])->group(function () {
            Route::get('appointments', [App\Http\Controllers\AppointmentController::class, 'index'])->name('admin.appointments');
            Route::get('appointments/{id}', [App\Http\Controllers\AppointmentController::class, 'show'])->name('admin.appointments.show');
            Route::get('appointments/calendar/data', [App\Http\Controllers\AppointmentController::class, 'calendar'])->name('admin.appointments.calendar');
        });
        
        Route::middleware(['permission:appointments.create'])->group(function () {
            Route::post('appointments', [App\Http\Controllers\AppointmentController::class, 'store'])->name('admin.appointments.store');
        });
        
        Route::middleware(['permission:appointments.edit'])->group(function () {
            Route::put('appointments/{id}', [App\Http\Controllers\AppointmentController::class, 'update'])->name('admin.appointments.update');
            Route::put('appointments/{id}/status', [App\Http\Controllers\AppointmentController::class, 'updateStatus'])->name('admin.appointments.status');
        });
        
        Route::middleware(['permission:appointments.delete'])->group(function () {
            Route::delete('appointments/{id}', [App\Http\Controllers\AppointmentController::class, 'destroy'])->name('admin.appointments.destroy');
        });

        // ===== REPORTS & ANALYTICS =====
        // Admin has full access to reports and analytics (no license restrictions)
        Route::middleware(['permission:reports.view'])->group(function () {
            Route::get('reports', [App\Http\Controllers\ReportsController::class, 'index'])->name('admin.reports');
            Route::get('reports/analytics', [App\Http\Controllers\ReportsController::class, 'analytics'])->name('admin.reports.analytics');
            Route::get('reports/download/{id}', [App\Http\Controllers\ReportsController::class, 'download'])->name('reports.download');
            Route::post('reports/generate', [App\Http\Controllers\ReportsController::class, 'generate'])->name('admin.reports.generate');
        });

        // Analytics dashboard - separate route with proper controller
        Route::middleware(['permission:reports.view'])->group(function () {
            Route::get('analytics', [App\Http\Controllers\ReportsController::class, 'analyticsPage'])->name('admin.analytics');
        });

        // ===== CLINIC MANAGEMENT =====
        // Admin has full access to clinic management
        Route::middleware(['permission:clinics.view'])->group(function () {
            Route::get('clinic-management', [App\Http\Controllers\ClinicManagementController::class, 'index'])->name('admin.clinic-management');
            Route::get('clinics', [App\Http\Controllers\ClinicManagementController::class, 'index'])->name('admin.clinics.index');
            Route::get('clinics/{clinic}', [App\Http\Controllers\ClinicManagementController::class, 'show'])->name('admin.clinics.show');
            Route::get('clinics/{clinic}/statistics', [App\Http\Controllers\ClinicManagementController::class, 'statistics'])->name('admin.clinics.statistics');
            Route::get('clinics/{clinic}/members', [App\Http\Controllers\ClinicManagementController::class, 'members'])->name('admin.clinics.members');
        });
        
        Route::middleware(['permission:clinics.create'])->group(function () {
            Route::get('clinics/create', [App\Http\Controllers\ClinicManagementController::class, 'create'])->name('admin.clinics.create');
            Route::post('clinics', [App\Http\Controllers\ClinicManagementController::class, 'store'])->name('admin.clinics.store');
        });
        
        Route::middleware(['permission:clinics.edit'])->group(function () {
            Route::get('clinics/{clinic}/edit', [App\Http\Controllers\ClinicManagementController::class, 'edit'])->name('admin.clinics.edit');
            Route::put('clinics/{clinic}', [App\Http\Controllers\ClinicManagementController::class, 'update'])->name('admin.clinics.update');
            Route::post('clinics/{clinic}/members', [App\Http\Controllers\ClinicManagementController::class, 'addMember'])->name('admin.clinics.members.add');
            Route::delete('clinics/{clinic}/members/{member}', [App\Http\Controllers\ClinicManagementController::class, 'removeMember'])->name('admin.clinics.members.remove');
        });
        
        Route::middleware(['permission:clinics.delete'])->group(function () {
            Route::delete('clinics/{clinic}', [App\Http\Controllers\ClinicManagementController::class, 'destroy'])->name('admin.clinics.destroy');
        });


        // ===== CLINIC SETTINGS =====
        // Admin has full access to clinic settings
        Route::middleware(['permission:settings.view'])->group(function () {
            Route::get('settings/clinic', [App\Http\Controllers\ClinicSettingsController::class, 'getSettings'])->name('admin.settings.clinic');
        });

        // ===== ROOM MANAGEMENT =====
        // Admin has full CRUD access to room management
        Route::middleware(['auth'])->group(function () {
            Route::get('rooms', [App\Http\Controllers\RoomController::class, 'index'])->name('admin.rooms');
            Route::get('rooms/{id}', [App\Http\Controllers\RoomController::class, 'show'])->name('admin.rooms.show');
            Route::get('rooms/statistics/overview', [App\Http\Controllers\RoomController::class, 'statistics'])->name('admin.rooms.statistics');
            Route::get('rooms/available/list', [App\Http\Controllers\RoomController::class, 'available'])->name('admin.rooms.available');
            Route::get('rooms/{id}/availability', [App\Http\Controllers\RoomController::class, 'availability'])->name('admin.rooms.availability');
            Route::post('rooms/{id}/check-availability', [App\Http\Controllers\RoomController::class, 'checkAvailability'])->name('admin.rooms.check-availability');
            
            // Test route for debugging
            Route::get('test-rooms', function(\Illuminate\Http\Request $request) {
                $user = $request->user();
                $userClinicRole = $user->userClinicRoles()->with(['clinic', 'role'])->first();
                
                if (!$userClinicRole) {
                    return response()->json(['error' => 'No clinic access'], 403);
                }
                
                $rooms = App\Models\Room::where('clinic_id', $userClinicRole->clinic_id)->get();
                
                return response()->json([
                    'user' => $user->name,
                    'clinic' => $userClinicRole->clinic->name,
                    'role' => $userClinicRole->role->name,
                    'rooms_count' => $rooms->count(),
                    'rooms' => $rooms->toArray()
                ]);
            });
        });
        
        Route::middleware(['auth'])->group(function () {
            Route::post('rooms', [App\Http\Controllers\RoomController::class, 'store'])->name('admin.rooms.store');
            Route::put('rooms/{id}', [App\Http\Controllers\RoomController::class, 'update'])->name('admin.rooms.update');
            Route::put('rooms/{id}/status', [App\Http\Controllers\RoomController::class, 'updateStatus'])->name('admin.rooms.status');
            Route::put('rooms/bulk/status', [App\Http\Controllers\RoomController::class, 'bulkUpdateStatus'])->name('admin.rooms.bulk-status');
            Route::delete('rooms/{id}', [App\Http\Controllers\RoomController::class, 'destroy'])->name('admin.rooms.destroy');
            Route::delete('rooms/bulk/delete', [App\Http\Controllers\RoomController::class, 'bulkDelete'])->name('admin.rooms.bulk-delete');
            Route::get('rooms/export/data', [App\Http\Controllers\RoomController::class, 'export'])->name('admin.rooms.export');
        });

        // ===== SCHEDULE MANAGEMENT =====
        // Admin has full CRUD access to schedule management
        Route::middleware(['permission:schedules.view'])->group(function () {
            Route::get('schedules', [App\Http\Controllers\ScheduleController::class, 'index'])->name('admin.schedules');
            Route::get('schedules/{id}', [App\Http\Controllers\ScheduleController::class, 'show'])->name('admin.schedules.show');
        });
        
        Route::middleware(['permission:schedules.create'])->group(function () {
            Route::post('schedules', [App\Http\Controllers\ScheduleController::class, 'store'])->name('admin.schedules.store');
        });
        
        Route::middleware(['permission:schedules.edit'])->group(function () {
            Route::put('schedules/{id}', [App\Http\Controllers\ScheduleController::class, 'update'])->name('admin.schedules.update');
        });
        
        Route::middleware(['permission:schedules.delete'])->group(function () {
            Route::delete('schedules/{id}', [App\Http\Controllers\ScheduleController::class, 'destroy'])->name('admin.schedules.destroy');
        });

        // Test route for debugging schedules
        Route::get('test-schedules', function(\Illuminate\Http\Request $request) {
            $user = $request->user();
            $userClinicRole = $user->userClinicRoles()->with(['clinic', 'role'])->first();
            
            if (!$userClinicRole) {
                return response()->json(['error' => 'No clinic access'], 403);
            }
            
            $doctors = App\Models\User::whereHas('clinics', function ($q) use ($userClinicRole) {
                $q->where('clinic_id', $userClinicRole->clinic_id);
            })->whereHas('roles', function ($q) {
                $q->where('name', 'doctor');
            })->get();
            
            return response()->json([
                'user' => $user->name,
                'clinic' => $userClinicRole->clinic->name,
                'role' => $userClinicRole->role->name,
                'doctors_count' => $doctors->count(),
                'doctors' => $doctors->map(function($doctor) {
                    return [
                        'id' => $doctor->id,
                        'name' => $doctor->name,
                        'email' => $doctor->email
                    ];
                })
            ]);
        });

        // ===== ADDITIONAL ADMIN FEATURES =====
        // System monitoring and management
        Route::middleware(['permission:system.status'])->group(function () {
            Route::get('system-status', function () {
                return Inertia::render('admin/system-status');
            })->name('admin.system-status');
        });
        
        // User activity monitoring
        Route::middleware(['permission:activity_logs.view'])->group(function () {
            Route::get('activity-logs', function () {
                return Inertia::render('admin/activity-logs');
            })->name('admin.activity-logs');
        });
        
        // System backup and restore
        Route::middleware(['permission:system.backup'])->group(function () {
            Route::get('backup', function () {
                return Inertia::render('admin/backup');
            })->name('admin.backup');
        });
        
        // License management
        Route::middleware(['permission:license.manage'])->group(function () {
            Route::get('license', function () {
                return Inertia::render('admin/license');
            })->name('admin.license');
        });
        
        // Analytics and reporting - moved to proper location above
        
        // Settings management
        Route::middleware(['permission:settings.manage'])->group(function () {
            Route::get('settings', function () {
                return Inertia::render('admin/settings');
            })->name('admin.settings');
        });

        // ===== CLINIC SETTINGS (continued) =====
        Route::middleware(['permission:settings.manage'])->group(function () {
            Route::post('clinic-management', [App\Http\Controllers\ClinicSettingsController::class, 'update'])->name('admin.clinic-management.store');
            Route::put('clinic-management/{id}', [App\Http\Controllers\ClinicSettingsController::class, 'update'])->name('admin.clinic-management.update');
            Route::put('settings/clinic', [App\Http\Controllers\ClinicSettingsController::class, 'updateSettings'])->name('admin.settings.clinic.update');
        });
    });

    // ===== CLINIC SWITCHING =====
    // These routes are outside admin prefix for easier access
    Route::middleware(['permission:clinics.view'])->group(function () {
        Route::get('clinic-selection', [App\Http\Controllers\ClinicSwitchController::class, 'index'])->name('clinic.selection');
        Route::post('clinics/switch', [App\Http\Controllers\ClinicSwitchController::class, 'switch'])->name('clinics.switch');
        Route::get('clinics/current', [App\Http\Controllers\ClinicSwitchController::class, 'current'])->name('clinics.current');
        Route::get('clinics/list', [App\Http\Controllers\ClinicSwitchController::class, 'list'])->name('clinics.list');
    });

    // ===== TEST ROUTES =====
    // Test page for clinic selector functionality
    Route::get('test/clinic-selector', function () {
        return Inertia::render('test-clinic-selector');
    })->name('test.clinic-selector');


    // Medrep routes - Require medrep role and medrep management license feature
    Route::prefix('medrep')->middleware(['permission:medrep_visits.manage', 'license.feature:medrep_management'])->group(function () {
        Route::middleware(['permission:dashboard.view'])->group(function () {
            Route::get('dashboard', function () {
                return Inertia::render('medrep/dashboard', [
                    'stats' => [
                        'totalProducts' => 0,
                        'totalDoctors' => 0,
                        'scheduledMeetings' => 0,
                        'completedInteractions' => 0
                    ],
                    'products' => [],
                    'doctors' => [],
                    'upcomingMeetings' => [],
                    'recentInteractions' => []
                ]);
            })->name('medrep.dashboard');
        });

        Route::middleware(['permission:reports.view', 'license.feature:advanced_analytics'])->group(function () {
            Route::get('analytics', function () {
                return Inertia::render('medrep/analytics');
            })->name('medrep.analytics');

            Route::get('performance', function () {
                return Inertia::render('medrep/performance');
            })->name('medrep.performance');
        });

        Route::middleware(['permission:interactions.view'])->group(function () {
            Route::get('commitments', function () {
                return Inertia::render('medrep/commitments');
            })->name('medrep.commitments');

            Route::get('interactions', function () {
                return Inertia::render('medrep/interactions');
            })->name('medrep.interactions');

            Route::get('meeting-history', function () {
                return Inertia::render('medrep/meeting-history');
            })->name('medrep.meeting-history');
        });

        Route::middleware(['permission:doctors.view'])->group(function () {
            Route::get('doctors', function () {
                return Inertia::render('medrep/doctors');
            })->name('medrep.doctors');
        });

        Route::middleware(['permission:products.view'])->group(function () {
            Route::get('products', function () {
                return Inertia::render('medrep/products');
            })->name('medrep.products');

            Route::get('samples', function () {
                return Inertia::render('medrep/samples');
            })->name('medrep.samples');
        });

        Route::middleware(['permission:meetings.create'])->group(function () {
            Route::get('schedule-meeting', function () {
                return Inertia::render('medrep/schedule-meeting');
            })->name('medrep.schedule-meeting');
        });

        Route::middleware(['permission:medrep_visits.view'])->group(function () {
            Route::get('territory', function () {
                return Inertia::render('medrep/territory');
            })->name('medrep.territory');
        });

        Route::middleware(['permission:interactions.view'])->group(function () {
            Route::get('marketing', function () {
                return Inertia::render('medrep/marketing');
            })->name('medrep.marketing');
        });
    });

    // Patient routes - Require patient role and appropriate permissions
    Route::prefix('patient')->middleware(['permission:profile.view'])->group(function () {
        Route::middleware(['permission:dashboard.view'])->group(function () {
            Route::get('dashboard', function () {
                return Inertia::render('patient/dashboard', [
                    'patient' => [
                        'id' => 1,
                        'name' => 'John Doe',
                        'patient_id' => 'P001',
                        'dob' => '1990-01-01',
                        'sex' => 'Male',
                        'contact' => [
                            'phone' => '+1234567890',
                            'email' => 'john.doe@example.com'
                        ],
                        'address' => '123 Main St, City, State'
                    ],
                    'upcomingAppointments' => [],
                    'recentEncounters' => [],
                    'recentPrescriptions' => [],
                    'recentLabResults' => [],
                    'doctors' => [],
                    'availableSlots' => []
                ]);
            })->name('patient.dashboard');
        });

        Route::middleware(['permission:appointments.view'])->group(function () {
            Route::get('appointments', function () {
                return Inertia::render('patient/appointments');
            })->name('patient.appointments');
        });

        Route::middleware(['permission:appointments.create'])->group(function () {
            Route::get('book-appointment', function () {
                return Inertia::render('patient/book-appointment');
            })->name('patient.book-appointment');
        });

        Route::middleware(['permission:billing.view'])->group(function () {
            Route::get('billing', function () {
                return Inertia::render('patient/billing');
            })->name('patient.billing');
        });

        Route::middleware(['permission:file_assets.download'])->group(function () {
            Route::get('documents', function () {
                return Inertia::render('patient/documents');
            })->name('patient.documents');
        });

        Route::middleware(['permission:encounters.view'])->group(function () {
            Route::get('follow-ups', function () {
                return Inertia::render('patient/follow-ups');
            })->name('patient.follow-ups');
        });

        Route::middleware(['permission:insurance.view'])->group(function () {
            Route::get('insurance', function () {
                return Inertia::render('patient/insurance');
            })->name('patient.insurance');
        });

        Route::middleware(['permission:lab_results.view', 'license.feature:lab_results'])->group(function () {
            Route::get('lab-results', function () {
                return Inertia::render('patient/lab-results');
            })->name('patient.lab-results');
        });

        Route::middleware(['permission:medical_records.view'])->group(function () {
            Route::get('medical-records', function () {
                return Inertia::render('patient/medical-records');
            })->name('patient.medical-records');
        });

        Route::middleware(['permission:notifications.view'])->group(function () {
            Route::get('notifications', function () {
                return Inertia::render('patient/notifications');
            })->name('patient.notifications');
        });

        Route::middleware(['permission:prescriptions.view'])->group(function () {
            Route::get('prescriptions', function () {
                return Inertia::render('patient/prescriptions');
            })->name('patient.prescriptions');
        });

        Route::middleware(['permission:profile.view'])->group(function () {
            Route::get('profile', function () {
                return Inertia::render('patient/profile');
            })->name('patient.profile');
        });
    });

    // Receptionist routes - Require receptionist role and appropriate permissions
    Route::prefix('receptionist')->middleware(['permission:queue.manage'])->group(function () {
        Route::middleware(['permission:dashboard.view'])->group(function () {
            Route::get('dashboard', function () {
                return Inertia::render('receptionist/dashboard', [
                    'stats' => [
                        'totalPatients' => 0,
                        'todayAppointments' => 0,
                        'activeQueue' => 0,
                        'completedEncounters' => 0
                    ],
                    'activeQueue' => [],
                    'recentEncounters' => []
                ]);
            })->name('receptionist.dashboard');
        });

        Route::middleware(['permission:appointments.view'])->group(function () {
            Route::get('appointments', function () {
                return Inertia::render('receptionist/appointments');
            })->name('receptionist.appointments');
        });

        Route::middleware(['permission:appointments.checkin'])->group(function () {
            Route::get('check-in', function () {
                return Inertia::render('receptionist/check-in');
            })->name('receptionist.check-in');
        });

        Route::middleware(['permission:encounters.view'])->group(function () {
            Route::get('encounters', function () {
                return Inertia::render('receptionist/encounters');
            })->name('receptionist.encounters');
        });

        Route::middleware(['permission:insurance.view'])->group(function () {
            Route::get('insurance', function () {
                return Inertia::render('receptionist/insurance');
            })->name('receptionist.insurance');
        });

        Route::middleware(['permission:patients.view'])->group(function () {
            Route::get('patient-history', function () {
                return Inertia::render('receptionist/patient-history');
            })->name('receptionist.patient-history');

            Route::get('patient-search', function () {
                return Inertia::render('receptionist/patient-search');
            })->name('receptionist.patient-search');
        });

        Route::middleware(['permission:queue.view'])->group(function () {
            Route::get('queue', function () {
                return Inertia::render('receptionist/queue');
            })->name('receptionist.queue');
        });

        Route::middleware(['permission:patients.create', 'license.usage:patients'])->group(function () {
            Route::get('register-patient', function () {
                return Inertia::render('receptionist/register-patient');
            })->name('receptionist.register-patient');
        });

        Route::middleware(['permission:reports.view'])->group(function () {
            Route::get('reports', function () {
                return Inertia::render('receptionist/reports');
            })->name('receptionist.reports');
        });
    });

    // Doctor routes - Require doctor role and appropriate permissions
    Route::prefix('doctor')->middleware(['permission:medical_records.view'])->group(function () {
        Route::middleware(['permission:dashboard.view'])->group(function () {
            Route::get('dashboard', function () {
                return Inertia::render('doctor/dashboard', [
                    'stats' => [
                        'todayAppointments' => 0,
                        'upcomingAppointments' => 0,
                        'totalPatients' => 0,
                        'pendingPrescriptions' => 0,
                        'recentAppointments' => [],
                        'recentPrescriptions' => []
                    ]
                ]);
            })->name('doctor.dashboard');
        });

        Route::middleware(['permission:appointments.view'])->group(function () {
            Route::get('appointments', function () {
                return Inertia::render('doctor/appointments', [
                    'appointments' => [],
                    'patients' => [],
                    'rooms' => [],
                    'filters' => [
                        'status' => '',
                        'type' => '',
                        'date' => ''
                    ]
                ]);
            })->name('doctor.appointments');
        });

        Route::middleware(['permission:medical_records.view'])->group(function () {
            Route::get('medical-records', function () {
                return Inertia::render('doctor/medical-records', [
                    'patients' => [],
                    'encounters' => [],
                    'labResults' => [],
                    'prescriptions' => [],
                    'filters' => [
                        'patient_id' => '',
                        'date_range' => '',
                        'type' => ''
                    ]
                ]);
            })->name('doctor.medical-records');
        });

        Route::middleware(['permission:prescriptions.view'])->group(function () {
            Route::get('prescriptions', function () {
                return Inertia::render('doctor/prescriptions', [
                    'prescriptions' => [],
                    'patients' => [],
                    'filters' => [
                        'status' => '',
                        'type' => '',
                        'patient_id' => '',
                        'date_range' => ''
                    ]
                ]);
            })->name('doctor.prescriptions');
        });

        Route::middleware(['permission:encounters.view'])->group(function () {
            Route::get('advice', function () {
                return Inertia::render('doctor/advice', [
                    'advice' => [],
                    'patients' => [],
                    'filters' => [
                        'category' => '',
                        'priority' => '',
                        'status' => '',
                        'patient_id' => '',
                        'date_range' => ''
                    ]
                ]);
            })->name('doctor.advice');
        });

        Route::middleware(['permission:queue.view'])->group(function () {
            Route::get('queue', function () {
                return Inertia::render('doctor/queue', [
                    'queueItems' => [],
                    'completedEncounters' => []
                ]);
            })->name('doctor.queue');
        });

        Route::middleware(['permission:patients.view'])->group(function () {
            Route::get('patient-history', function () {
                return Inertia::render('doctor/patient-history');
            })->name('doctor.patient-history');
        });

        Route::middleware(['permission:lab_results.view', 'license.feature:lab_results'])->group(function () {
            Route::get('lab-results', function () {
                return Inertia::render('doctor/lab-results');
            })->name('doctor.lab-results');
        });
    });
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
require __DIR__.'/license-web.php';
require __DIR__.'/installation.php';
