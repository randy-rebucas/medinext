<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

Route::middleware(['auth', 'verified', 'trial.check', 'onboarding.check'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Admin routes - System admin has full access to all management areas
    Route::prefix('admin')->middleware(['permission:system.admin'])->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

        // ===== DOCTOR MANAGEMENT =====
        // Admin has full CRUD access to doctor management
        Route::get('doctors', [App\Http\Controllers\DoctorController::class, 'index'])->name('admin.doctors');
        Route::get('doctors/{id}', [App\Http\Controllers\DoctorController::class, 'show'])->name('admin.doctors.show');
        Route::post('doctors', [App\Http\Controllers\DoctorController::class, 'store'])->name('admin.doctors.store');
        Route::put('doctors/{id}', [App\Http\Controllers\DoctorController::class, 'update'])->name('admin.doctors.update');
        Route::delete('doctors/{id}', [App\Http\Controllers\DoctorController::class, 'destroy'])->name('admin.doctors.destroy');

        // ===== STAFF MANAGEMENT =====
        // Admin has full CRUD access to staff management
        Route::get('staff', [App\Http\Controllers\StaffController::class, 'index'])->name('admin.staff');
        Route::get('staff/{id}', [App\Http\Controllers\StaffController::class, 'show'])->name('admin.staff.show');
        Route::post('staff', [App\Http\Controllers\StaffController::class, 'store'])->name('admin.staff.store');
        Route::put('staff/{id}', [App\Http\Controllers\StaffController::class, 'update'])->name('admin.staff.update');
        Route::delete('staff/{id}', [App\Http\Controllers\StaffController::class, 'destroy'])->name('admin.staff.destroy');

        // ===== PATIENT MANAGEMENT =====
        // Admin has full CRUD access to patient management
        Route::get('patients', [App\Http\Controllers\PatientController::class, 'index'])->name('admin.patients');
        Route::get('patients/{id}', [App\Http\Controllers\PatientController::class, 'show'])->name('admin.patients.show');
        Route::get('patients/{id}/health-records', [App\Http\Controllers\PatientController::class, 'healthRecords'])->name('admin.patients.health-records');
        Route::post('patients', [App\Http\Controllers\PatientController::class, 'store'])->name('admin.patients.store');
        Route::put('patients/{id}', [App\Http\Controllers\PatientController::class, 'update'])->name('admin.patients.update');
        Route::delete('patients/{id}', [App\Http\Controllers\PatientController::class, 'destroy'])->name('admin.patients.destroy');

        // ===== APPOINTMENT MANAGEMENT =====
        // Admin has full CRUD access to appointment management
        Route::get('appointments', [App\Http\Controllers\AppointmentController::class, 'index'])->name('admin.appointments');
        Route::get('appointments/{id}', [App\Http\Controllers\AppointmentController::class, 'show'])->name('admin.appointments.show');
        Route::get('appointments/calendar/data', [App\Http\Controllers\AppointmentController::class, 'calendar'])->name('admin.appointments.calendar');
        Route::post('appointments', [App\Http\Controllers\AppointmentController::class, 'store'])->name('admin.appointments.store');
        Route::put('appointments/{id}', [App\Http\Controllers\AppointmentController::class, 'update'])->name('admin.appointments.update');
        Route::put('appointments/{id}/status', [App\Http\Controllers\AppointmentController::class, 'updateStatus'])->name('admin.appointments.status');
        Route::delete('appointments/{id}', [App\Http\Controllers\AppointmentController::class, 'destroy'])->name('admin.appointments.destroy');

        // ===== REPORTS & ANALYTICS =====
        // Admin has full access to reports and analytics (no license restrictions)
        Route::get('reports', [App\Http\Controllers\ReportsController::class, 'index'])->name('admin.reports');
        Route::get('reports/analytics', [App\Http\Controllers\ReportsController::class, 'analytics'])->name('admin.reports.analytics');
        Route::get('reports/download/{id}', [App\Http\Controllers\ReportsController::class, 'download'])->name('reports.download');
        Route::post('reports/generate', [App\Http\Controllers\ReportsController::class, 'generate'])->name('admin.reports.generate');
        
        // Analytics dashboard
        Route::get('analytics', function () {
            return Inertia::render('admin/analytics');
        })->name('admin.analytics');

        // ===== CLINIC SETTINGS =====
        // Admin has full access to clinic settings management
        Route::get('clinic-settings', [App\Http\Controllers\ClinicSettingsController::class, 'index'])->name('admin.clinic-settings');
        Route::post('clinic-settings', [App\Http\Controllers\ClinicSettingsController::class, 'update'])->name('admin.clinic-settings.update');
        Route::put('clinic-settings/{id}', [App\Http\Controllers\ClinicSettingsController::class, 'update'])->name('admin.clinic-settings.update');

        // ===== ROOM MANAGEMENT =====
        // Admin has full CRUD access to room management
        Route::get('rooms', function () {
            return Inertia::render('admin/rooms');
        })->name('admin.rooms');
        Route::get('rooms/{id}', function ($id) {
            return Inertia::render('admin/rooms', ['roomId' => $id]);
        })->name('admin.rooms.show');
        Route::post('rooms', function () {
            // Handle room creation
            return redirect()->route('admin.rooms')->with('success', 'Room created successfully');
        })->name('admin.rooms.store');
        Route::put('rooms/{id}', function ($id) {
            // Handle room update
            return redirect()->route('admin.rooms')->with('success', 'Room updated successfully');
        })->name('admin.rooms.update');
        Route::delete('rooms/{id}', function ($id) {
            // Handle room deletion
            return redirect()->route('admin.rooms')->with('success', 'Room deleted successfully');
        })->name('admin.rooms.destroy');

        // ===== SCHEDULE MANAGEMENT =====
        // Admin has full CRUD access to schedule management
        Route::get('schedules', function () {
            return Inertia::render('admin/schedules');
        })->name('admin.schedules');
        Route::get('schedules/{id}', function ($id) {
            return Inertia::render('admin/schedules', ['scheduleId' => $id]);
        })->name('admin.schedules.show');
        Route::post('schedules', function () {
            // Handle schedule creation
            return redirect()->route('admin.schedules')->with('success', 'Schedule created successfully');
        })->name('admin.schedules.store');
        Route::put('schedules/{id}', function ($id) {
            // Handle schedule update
            return redirect()->route('admin.schedules')->with('success', 'Schedule updated successfully');
        })->name('admin.schedules.update');
        Route::delete('schedules/{id}', function ($id) {
            // Handle schedule deletion
            return redirect()->route('admin.schedules')->with('success', 'Schedule deleted successfully');
        })->name('admin.schedules.destroy');

        // ===== ADDITIONAL ADMIN FEATURES =====
        // System monitoring and management
        Route::get('system-status', function () {
            return Inertia::render('admin/system-status');
        })->name('admin.system-status');
        
        // User activity monitoring
        Route::get('activity-logs', function () {
            return Inertia::render('admin/activity-logs');
        })->name('admin.activity-logs');
        
        // Backup and maintenance
        Route::get('backup', function () {
            return Inertia::render('admin/backup');
        })->name('admin.backup');
        Route::post('backup/create', function () {
            // Handle backup creation
            return redirect()->route('admin.backup')->with('success', 'Backup created successfully');
        })->name('admin.backup.create');
    });

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
