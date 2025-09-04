<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClinicController;
use App\Http\Controllers\Api\PatientController;
use App\Http\Controllers\Api\DoctorController;
use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\EncounterController;
use App\Http\Controllers\Api\PrescriptionController;
use App\Http\Controllers\Api\LabResultController;
use App\Http\Controllers\Api\FileAssetController;
use App\Http\Controllers\Api\MedrepController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\SettingsController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes (no authentication required)
Route::prefix('v1')->group(function () {
    // Authentication routes
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);
    Route::post('/auth/verify-email', [AuthController::class, 'verifyEmail']);

    // Public clinic information
    Route::get('/clinics', [ClinicController::class, 'publicIndex']);
    Route::get('/clinics/{clinic}', [ClinicController::class, 'publicShow']);
});

// Protected routes (authentication required)
Route::prefix('v1')->middleware(['api.auth'])->group(function () {

    // Authentication management
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::put('/auth/profile', [AuthController::class, 'updateProfile']);
    Route::put('/auth/password', [AuthController::class, 'updatePassword']);

    // Dashboard and analytics
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
    Route::get('/dashboard/notifications', [DashboardController::class, 'notifications']);

    // Clinic management
    Route::apiResource('clinics', ClinicController::class);
    Route::get('/clinics/{clinic}/users', [ClinicController::class, 'users']);
    Route::get('/clinics/{clinic}/doctors', [ClinicController::class, 'doctors']);
    Route::get('/clinics/{clinic}/patients', [ClinicController::class, 'patients']);
    Route::get('/clinics/{clinic}/appointments', [ClinicController::class, 'appointments']);
    Route::get('/clinics/{clinic}/statistics', [ClinicController::class, 'statistics']);

    // Patient management
    Route::apiResource('patients', PatientController::class);
    Route::get('/patients/{patient}/appointments', [PatientController::class, 'appointments']);
    Route::get('/patients/{patient}/encounters', [PatientController::class, 'encounters']);
    Route::get('/patients/{patient}/prescriptions', [PatientController::class, 'prescriptions']);
    Route::get('/patients/{patient}/lab-results', [PatientController::class, 'labResults']);
    Route::get('/patients/{patient}/medical-history', [PatientController::class, 'medicalHistory']);
    Route::get('/patients/{patient}/file-assets', [PatientController::class, 'fileAssets']);
    Route::post('/patients/{patient}/file-assets', [PatientController::class, 'uploadFile']);
    Route::get('/patients/search', [PatientController::class, 'search']);

    // Doctor management
    Route::apiResource('doctors', DoctorController::class);
    Route::get('/doctors/{doctor}/appointments', [DoctorController::class, 'appointments']);
    Route::get('/doctors/{doctor}/encounters', [DoctorController::class, 'encounters']);
    Route::get('/doctors/{doctor}/patients', [DoctorController::class, 'patients']);
    Route::get('/doctors/{doctor}/availability', [DoctorController::class, 'availability']);
    Route::get('/doctors/{doctor}/statistics', [DoctorController::class, 'statistics']);
    Route::post('/doctors/{doctor}/availability', [DoctorController::class, 'updateAvailability']);

    // Appointment management
    Route::apiResource('appointments', AppointmentController::class);
    Route::post('/appointments/{appointment}/check-in', [AppointmentController::class, 'checkIn']);
    Route::post('/appointments/{appointment}/check-out', [AppointmentController::class, 'checkOut']);
    Route::post('/appointments/{appointment}/cancel', [AppointmentController::class, 'cancel']);
    Route::post('/appointments/{appointment}/reschedule', [AppointmentController::class, 'reschedule']);
    Route::post('/appointments/{appointment}/reminder', [AppointmentController::class, 'sendReminder']);
    Route::get('/appointments/available-slots', [AppointmentController::class, 'availableSlots']);
    Route::get('/appointments/conflicts', [AppointmentController::class, 'checkConflicts']);
    Route::get('/appointments/today', [AppointmentController::class, 'today']);
    Route::get('/appointments/upcoming', [AppointmentController::class, 'upcoming']);

    // Encounter management
    Route::apiResource('encounters', EncounterController::class);
    Route::get('/encounters/{encounter}/prescriptions', [EncounterController::class, 'prescriptions']);
    Route::get('/encounters/{encounter}/lab-results', [EncounterController::class, 'labResults']);
    Route::get('/encounters/{encounter}/file-assets', [EncounterController::class, 'fileAssets']);
    Route::post('/encounters/{encounter}/file-assets', [EncounterController::class, 'uploadFile']);
    Route::put('/encounters/{encounter}/soap-notes', [EncounterController::class, 'updateSoapNotes']);
    Route::post('/encounters/{encounter}/complete', [EncounterController::class, 'complete']);

    // Prescription management
    Route::apiResource('prescriptions', PrescriptionController::class);
    Route::get('/prescriptions/{prescription}/items', [PrescriptionController::class, 'items']);
    Route::post('/prescriptions/{prescription}/items', [PrescriptionController::class, 'addItem']);
    Route::put('/prescriptions/{prescription}/items/{item}', [PrescriptionController::class, 'updateItem']);
    Route::delete('/prescriptions/{prescription}/items/{item}', [PrescriptionController::class, 'removeItem']);
    Route::post('/prescriptions/{prescription}/verify', [PrescriptionController::class, 'verify']);
    Route::post('/prescriptions/{prescription}/dispense', [PrescriptionController::class, 'dispense']);
    Route::post('/prescriptions/{prescription}/refill', [PrescriptionController::class, 'refill']);
    Route::get('/prescriptions/{prescription}/pdf', [PrescriptionController::class, 'downloadPdf']);
    Route::get('/prescriptions/{prescription}/qr', [PrescriptionController::class, 'qrCode']);
    Route::get('/prescriptions/active', [PrescriptionController::class, 'active']);
    Route::get('/prescriptions/expired', [PrescriptionController::class, 'expired']);
    Route::get('/prescriptions/needs-refill', [PrescriptionController::class, 'needsRefill']);

    // Lab result management
    Route::apiResource('lab-results', LabResultController::class);
    Route::get('/lab-results/{labResult}/file-assets', [LabResultController::class, 'fileAssets']);
    Route::post('/lab-results/{labResult}/file-assets', [LabResultController::class, 'uploadFile']);
    Route::post('/lab-results/{labResult}/review', [LabResultController::class, 'review']);
    Route::get('/lab-results/pending', [LabResultController::class, 'pending']);
    Route::get('/lab-results/abnormal', [LabResultController::class, 'abnormal']);

    // File asset management
    Route::apiResource('file-assets', FileAssetController::class);
    Route::get('/file-assets/{fileAsset}/download', [FileAssetController::class, 'download']);
    Route::get('/file-assets/{fileAsset}/preview', [FileAssetController::class, 'preview']);
    Route::post('/file-assets/upload', [FileAssetController::class, 'upload']);
    Route::get('/file-assets/categories', [FileAssetController::class, 'categories']);

    // Medrep management
    Route::apiResource('medreps', MedrepController::class);
    Route::get('/medreps/{medrep}/visits', [MedrepController::class, 'visits']);
    Route::post('/medreps/{medrep}/visits', [MedrepController::class, 'scheduleVisit']);
    Route::put('/medreps/{medrep}/visits/{visit}', [MedrepController::class, 'updateVisit']);
    Route::delete('/medreps/{medrep}/visits/{visit}', [MedrepController::class, 'cancelVisit']);
    Route::get('/medrep-visits', [MedrepController::class, 'allVisits']);
    Route::get('/medrep-visits/upcoming', [MedrepController::class, 'upcomingVisits']);

    // Settings and configuration
    Route::get('/settings', [SettingsController::class, 'index']);
    Route::put('/settings', [SettingsController::class, 'update']);
    Route::get('/settings/clinic', [SettingsController::class, 'clinicSettings']);
    Route::put('/settings/clinic', [SettingsController::class, 'updateClinicSettings']);
    Route::get('/settings/user', [SettingsController::class, 'userSettings']);
    Route::put('/settings/user', [SettingsController::class, 'updateUserSettings']);

    // Search and filtering
    Route::get('/search/patients', [PatientController::class, 'search']);
    Route::get('/search/doctors', [DoctorController::class, 'search']);
    Route::get('/search/appointments', [AppointmentController::class, 'search']);
    Route::get('/search/prescriptions', [PrescriptionController::class, 'search']);

    // Reports and analytics
    Route::get('/reports/appointments', [AppointmentController::class, 'reports']);
    Route::get('/reports/prescriptions', [PrescriptionController::class, 'reports']);
    Route::get('/reports/patients', [PatientController::class, 'reports']);
    Route::get('/reports/doctors', [DoctorController::class, 'reports']);
    Route::get('/reports/lab-results', [LabResultController::class, 'reports']);

    // Mobile-specific routes
    Route::prefix('mobile')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'mobile']);
        Route::get('/notifications', [DashboardController::class, 'notifications']);
        Route::post('/notifications/mark-read', [DashboardController::class, 'markNotificationRead']);
        Route::get('/quick-actions', [DashboardController::class, 'quickActions']);
        Route::get('/recent-patients', [PatientController::class, 'recent']);
        Route::get('/today-appointments', [AppointmentController::class, 'today']);
        Route::get('/pending-tasks', [DashboardController::class, 'pendingTasks']);
    });

    // Web-specific routes
    Route::prefix('web')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'web']);
        Route::get('/calendar', [AppointmentController::class, 'calendar']);
        Route::get('/calendar/events', [AppointmentController::class, 'calendarEvents']);
        Route::get('/analytics', [DashboardController::class, 'analytics']);
        Route::get('/export/patients', [PatientController::class, 'export']);
        Route::get('/export/appointments', [AppointmentController::class, 'export']);
        Route::get('/export/prescriptions', [PrescriptionController::class, 'export']);
    });
});

// Webhook routes (no authentication, but with signature verification)
Route::prefix('v1/webhooks')->group(function () {
    Route::post('/lab-results', [LabResultController::class, 'webhook']);
    Route::post('/prescriptions', [PrescriptionController::class, 'webhook']);
    Route::post('/appointments', [AppointmentController::class, 'webhook']);
});

// Health check and system routes
Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'timestamp' => now()->toISOString(),
        'version' => '1.0.0'
    ]);
});

Route::get('/version', function () {
    return response()->json([
        'version' => '1.0.0',
        'api_version' => 'v1',
        'build' => config('app.version', '1.0.0')
    ]);
});
