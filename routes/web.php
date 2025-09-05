<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    // Main dashboard - accessible to all authenticated users
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');

    // Patient routes
    Route::middleware(['role:patient'])->group(function () {
        Route::get('appointments', function () {
            return Inertia::render('patient/appointments');
        })->name('patient.appointments');

        Route::get('appointments/book', function () {
            return Inertia::render('patient/book-appointment');
        })->name('patient.appointments.book');

        Route::get('prescriptions', function () {
            return Inertia::render('patient/prescriptions');
        })->name('patient.prescriptions');

        Route::get('records', function () {
            return Inertia::render('patient/records');
        })->name('patient.records');

        Route::get('lab-results', function () {
            return Inertia::render('patient/lab-results');
        })->name('patient.lab-results');
    });

    // Doctor routes
    Route::middleware(['role:doctor,admin,superadmin'])->group(function () {
        Route::get('doctor', function () {
            return Inertia::render('admin/index');
        })->name('doctor.index');

        Route::get('doctor/dashboard', function () {
            // Mock data for the admin dashboard
            $stats = [
                'todayAppointments' => 8,
                'upcomingAppointments' => 24,
                'activePatients' => 156,
                'pendingPrescriptions' => 12,
                'recentMedsSamples' => 5,
                'urgentTasks' => 3,
            ];

            return Inertia::render('admin/dashboard', [
                'stats' => $stats
            ]);
        })->name('doctor.dashboard');

        // Doctor Pages
        Route::get('doctor/patients', function () {
            return Inertia::render('admin/patients');
        })->name('doctor.patients');

        Route::get('doctor/appointments', function () {
            return Inertia::render('admin/appointments');
        })->name('doctor.appointments');

        Route::get('doctor/records', function () {
            return Inertia::render('admin/records');
        })->name('doctor.records');

        Route::get('doctor/prescriptions', function () {
            return Inertia::render('admin/prescriptions');
        })->name('doctor.prescriptions');

        Route::get('doctor/lab-results', function () {
            return Inertia::render('admin/lab-results');
        })->name('doctor.lab-results');

        Route::get('doctor/reports', function () {
            return Inertia::render('admin/reports');
        })->name('doctor.reports');

        Route::get('doctor/med-samples', function () {
            return Inertia::render('admin/med-samples');
        })->name('doctor.med-samples');

        Route::get('doctor/messages', function () {
            return Inertia::render('admin/messages');
        })->name('doctor.messages');

        Route::get('doctor/settings', function () {
            return Inertia::render('admin/settings');
        })->name('doctor.settings');
    });

    // Receptionist routes
    Route::middleware(['role:receptionist,admin,superadmin'])->group(function () {
        Route::get('receptionist/dashboard', function () {
            return Inertia::render('receptionist/dashboard');
        })->name('receptionist.dashboard');

        Route::get('checkin', function () {
            return Inertia::render('receptionist/checkin');
        })->name('receptionist.checkin');

        Route::get('appointments/create', function () {
            return Inertia::render('receptionist/create-appointment');
        })->name('receptionist.appointments.create');

        Route::get('patients/create', function () {
            return Inertia::render('receptionist/create-patient');
        })->name('receptionist.patients.create');

        Route::get('billing', function () {
            return Inertia::render('receptionist/billing');
        })->name('receptionist.billing');
    });

    // MedRep routes
    Route::middleware(['role:medrep'])->group(function () {
        Route::get('medrep/dashboard', function () {
            return Inertia::render('medrep/dashboard');
        })->name('medrep.dashboard');

        Route::get('medrep/schedule', function () {
            return Inertia::render('medrep/schedule');
        })->name('medrep.schedule');

        Route::get('medrep/upload', function () {
            return Inertia::render('medrep/upload');
        })->name('medrep.upload');
    });

    // Admin/SuperAdmin routes
    Route::middleware(['role:admin,superadmin'])->group(function () {
        Route::get('admin/dashboard', function () {
            return Inertia::render('admin/dashboard');
        })->name('admin.dashboard');

        Route::get('admin/users', function () {
            return Inertia::render('admin/users');
        })->name('admin.users');

        Route::get('admin/settings', function () {
            return Inertia::render('admin/settings');
        })->name('admin.settings');
    });

    // SuperAdmin only routes
    Route::middleware(['role:superadmin'])->group(function () {
        Route::get('superadmin/dashboard', function () {
            return Inertia::render('superadmin/dashboard');
        })->name('superadmin.dashboard');

        Route::get('superadmin/tenants', function () {
            return Inertia::render('superadmin/tenants');
        })->name('superadmin.tenants');

        Route::get('superadmin/global-settings', function () {
            return Inertia::render('superadmin/global-settings');
        })->name('superadmin.global-settings');
    });
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
