<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Admin routes
    Route::prefix('admin')->group(function () {
        Route::get('dashboard', function () {
            return Inertia::render('admin/dashboard');
        })->name('admin.dashboard');

        Route::get('doctors', function () {
            return Inertia::render('admin/doctors');
        })->name('admin.doctors');

        Route::get('staff', function () {
            return Inertia::render('admin/staff');
        })->name('admin.staff');

        Route::get('patients', function () {
            return Inertia::render('admin/patients');
        })->name('admin.patients');

        Route::get('appointments', function () {
            return Inertia::render('admin/appointments');
        })->name('admin.appointments');

        Route::get('reports', function () {
            return Inertia::render('admin/reports');
        })->name('admin.reports');

        Route::get('analytics', function () {
            return Inertia::render('admin/analytics');
        })->name('admin.analytics');

        Route::get('clinic-settings', function () {
            return Inertia::render('admin/clinic-settings');
        })->name('admin.clinic-settings');

        Route::get('rooms', function () {
            return Inertia::render('admin/rooms');
        })->name('admin.rooms');

        Route::get('schedules', function () {
            return Inertia::render('admin/schedules');
        })->name('admin.schedules');
    });

    // Medrep routes
    Route::prefix('medrep')->group(function () {
        Route::get('analytics', function () {
            return Inertia::render('medrep/analytics');
        })->name('medrep.analytics');

        Route::get('commitments', function () {
            return Inertia::render('medrep/commitments');
        })->name('medrep.commitments');

        Route::get('doctors', function () {
            return Inertia::render('medrep/doctors');
        })->name('medrep.doctors');

        Route::get('interactions', function () {
            return Inertia::render('medrep/interactions');
        })->name('medrep.interactions');

        Route::get('marketing', function () {
            return Inertia::render('medrep/marketing');
        })->name('medrep.marketing');

        Route::get('meeting-history', function () {
            return Inertia::render('medrep/meeting-history');
        })->name('medrep.meeting-history');

        Route::get('performance', function () {
            return Inertia::render('medrep/performance');
        })->name('medrep.performance');

        Route::get('products', function () {
            return Inertia::render('medrep/products');
        })->name('medrep.products');

        Route::get('samples', function () {
            return Inertia::render('medrep/samples');
        })->name('medrep.samples');

        Route::get('schedule-meeting', function () {
            return Inertia::render('medrep/schedule-meeting');
        })->name('medrep.schedule-meeting');

        Route::get('territory', function () {
            return Inertia::render('medrep/territory');
        })->name('medrep.territory');
    });

    // Patient routes
    Route::prefix('patient')->group(function () {
        Route::get('appointments', function () {
            return Inertia::render('patient/appointments');
        })->name('patient.appointments');

        Route::get('billing', function () {
            return Inertia::render('patient/billing');
        })->name('patient.billing');

        Route::get('book-appointment', function () {
            return Inertia::render('patient/book-appointment');
        })->name('patient.book-appointment');

        Route::get('documents', function () {
            return Inertia::render('patient/documents');
        })->name('patient.documents');

        Route::get('follow-ups', function () {
            return Inertia::render('patient/follow-ups');
        })->name('patient.follow-ups');

        Route::get('insurance', function () {
            return Inertia::render('patient/insurance');
        })->name('patient.insurance');

        Route::get('lab-results', function () {
            return Inertia::render('patient/lab-results');
        })->name('patient.lab-results');

        Route::get('medical-records', function () {
            return Inertia::render('patient/medical-records');
        })->name('patient.medical-records');

        Route::get('notifications', function () {
            return Inertia::render('patient/notifications');
        })->name('patient.notifications');

        Route::get('prescriptions', function () {
            return Inertia::render('patient/prescriptions');
        })->name('patient.prescriptions');

        Route::get('profile', function () {
            return Inertia::render('patient/profile');
        })->name('patient.profile');
    });

    // Receptionist routes
    Route::prefix('receptionist')->group(function () {
        Route::get('appointments', function () {
            return Inertia::render('receptionist/appointments');
        })->name('receptionist.appointments');

        Route::get('check-in', function () {
            return Inertia::render('receptionist/check-in');
        })->name('receptionist.check-in');

        Route::get('encounters', function () {
            return Inertia::render('receptionist/encounters');
        })->name('receptionist.encounters');

        Route::get('insurance', function () {
            return Inertia::render('receptionist/insurance');
        })->name('receptionist.insurance');

        Route::get('patient-history', function () {
            return Inertia::render('receptionist/patient-history');
        })->name('receptionist.patient-history');

        Route::get('patient-search', function () {
            return Inertia::render('receptionist/patient-search');
        })->name('receptionist.patient-search');

        Route::get('queue', function () {
            return Inertia::render('receptionist/queue');
        })->name('receptionist.queue');

        Route::get('register-patient', function () {
            return Inertia::render('receptionist/register-patient');
        })->name('receptionist.register-patient');

        Route::get('reports', function () {
            return Inertia::render('receptionist/reports');
        })->name('receptionist.reports');
    });

    // Doctor routes
    Route::prefix('doctor')->group(function () {
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

        Route::get('queue', function () {
            return Inertia::render('doctor/queue', [
                'queueItems' => [],
                'completedEncounters' => []
            ]);
        })->name('doctor.queue');

        Route::get('patient-history', function () {
            return Inertia::render('doctor/patient-history');
        })->name('doctor.patient-history');

        Route::get('lab-results', function () {
            return Inertia::render('doctor/lab-results');
        })->name('doctor.lab-results');
    });
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
require __DIR__.'/license-web.php';
