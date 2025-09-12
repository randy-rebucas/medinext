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
use App\Http\Controllers\ClinicSettingsController;
use App\Http\Controllers\Api\LicenseController;
use App\Http\Controllers\Api\LicenseKeyController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\ActivityLogController;
use App\Http\Controllers\Api\RoomController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\QueueController;
use App\Http\Controllers\Api\BillController;
use App\Http\Controllers\Api\InsuranceController;
use App\Http\Controllers\Api\SystemController;
use App\Http\Controllers\Api\IntegrationController;
use App\Http\Controllers\Api\AuditController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\DemoController;

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
    Route::get('/public/clinics', [ClinicController::class, 'publicIndex']);
    Route::get('/public/clinics/{clinic}', [ClinicController::class, 'publicShow']);

    // Demo account management (public routes for easy setup)
    Route::prefix('demo')->group(function () {
        Route::post('/create', [DemoController::class, 'createDemoAccount']);
        Route::get('/info', [DemoController::class, 'getDemoAccountInfo']);
        Route::delete('/delete', [DemoController::class, 'deleteDemoAccount']);
        Route::post('/reset', [DemoController::class, 'resetDemoData']);
    });
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
    Route::middleware(['api.permission:dashboard.view'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index']);
        Route::get('/dashboard/notifications', [DashboardController::class, 'notifications']);
    });

    Route::middleware(['api.permission:dashboard.stats'])->group(function () {
        Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
    });

    // Settings and configuration
    Route::middleware(['api.permission:settings.view'])->group(function () {
        Route::get('/settings', [SettingsController::class, 'index']);
        Route::get('/settings/clinic', [ClinicSettingsController::class, 'getSettings']);
        Route::get('/settings/user', [SettingsController::class, 'userSettings']);
    });

    Route::middleware(['api.permission:settings.manage'])->group(function () {
        Route::put('/settings', [SettingsController::class, 'update']);
        Route::put('/settings/clinic', [ClinicSettingsController::class, 'updateSettings']);
        Route::put('/settings/user', [SettingsController::class, 'updateUserSettings']);
        Route::post('/settings/clinic/initialize', [ClinicSettingsController::class, 'initializeSettings']);
    });

    // User Management Routes
    Route::middleware(['api.permission:users.view'])->group(function () {
        Route::get('/users', [App\Http\Controllers\Api\UserController::class, 'index']);
        Route::get('/users/{user}', [App\Http\Controllers\Api\UserController::class, 'show']);
        Route::get('/users/{user}/permissions', [App\Http\Controllers\Api\UserController::class, 'permissions']);
        Route::get('/users/{user}/roles', [App\Http\Controllers\Api\UserController::class, 'roles']);
        Route::get('/users/{user}/activity', [App\Http\Controllers\Api\UserController::class, 'activity']);
    });

    Route::middleware(['api.permission:users.create'])->group(function () {
        Route::post('/users', [App\Http\Controllers\Api\UserController::class, 'store']);
    });

    Route::middleware(['api.permission:users.edit'])->group(function () {
        Route::put('/users/{user}', [App\Http\Controllers\Api\UserController::class, 'update']);
        Route::post('/users/{user}/permissions', [App\Http\Controllers\Api\UserController::class, 'assignPermissions']);
        Route::post('/users/{user}/roles', [App\Http\Controllers\Api\UserController::class, 'assignRoles']);
        Route::post('/users/{user}/reset-password', [App\Http\Controllers\Api\UserController::class, 'resetPassword']);
    });

    Route::middleware(['api.permission:users.delete'])->group(function () {
        Route::delete('/users/{user}', [App\Http\Controllers\Api\UserController::class, 'destroy']);
    });

    Route::middleware(['api.permission:users.activate'])->group(function () {
        Route::post('/users/{user}/activate', [App\Http\Controllers\Api\UserController::class, 'activate']);
    });

    Route::middleware(['api.permission:users.deactivate'])->group(function () {
        Route::post('/users/{user}/deactivate', [App\Http\Controllers\Api\UserController::class, 'deactivate']);
    });

    // Role Management Routes
    Route::middleware(['api.permission:roles.view'])->group(function () {
        Route::get('/roles', [App\Http\Controllers\Api\RoleController::class, 'index']);
        Route::get('/roles/{role}', [App\Http\Controllers\Api\RoleController::class, 'show']);
        Route::get('/roles/{role}/permissions', [App\Http\Controllers\Api\RoleController::class, 'permissions']);
        Route::get('/roles/{role}/users', [App\Http\Controllers\Api\RoleController::class, 'users']);
    });

    Route::middleware(['api.permission:roles.create'])->group(function () {
        Route::post('/roles', [App\Http\Controllers\Api\RoleController::class, 'store']);
    });

    Route::middleware(['api.permission:roles.edit'])->group(function () {
        Route::put('/roles/{role}', [App\Http\Controllers\Api\RoleController::class, 'update']);
        Route::post('/roles/{role}/permissions', [App\Http\Controllers\Api\RoleController::class, 'assignPermissions']);
    });

    Route::middleware(['api.permission:roles.delete'])->group(function () {
        Route::delete('/roles/{role}', [App\Http\Controllers\Api\RoleController::class, 'destroy']);
    });

    // Permission Management Routes
    Route::middleware(['api.permission:permissions.view'])->group(function () {
        Route::get('/permissions', [App\Http\Controllers\Api\PermissionController::class, 'index']);
        Route::get('/permissions/{permission}', [App\Http\Controllers\Api\PermissionController::class, 'show']);
        Route::get('/permissions/modules', [App\Http\Controllers\Api\PermissionController::class, 'modules']);
    });

    Route::middleware(['api.permission:permissions.create'])->group(function () {
        Route::post('/permissions', [App\Http\Controllers\Api\PermissionController::class, 'store']);
    });

    Route::middleware(['api.permission:permissions.edit'])->group(function () {
        Route::put('/permissions/{permission}', [App\Http\Controllers\Api\PermissionController::class, 'update']);
    });

    Route::middleware(['api.permission:permissions.delete'])->group(function () {
        Route::delete('/permissions/{permission}', [App\Http\Controllers\Api\PermissionController::class, 'destroy']);
    });

    // Staff Management Routes (Legacy - redirects to users)
    Route::get('/staff', [App\Http\Controllers\StaffController::class, 'index']);
    Route::post('/staff', [App\Http\Controllers\StaffController::class, 'store']);
    Route::put('/staff/{id}', [App\Http\Controllers\StaffController::class, 'update']);
    Route::delete('/staff/{id}', [App\Http\Controllers\StaffController::class, 'destroy']);

    // Clinic management (with usage validation)
    Route::middleware(['license.usage:clinics', 'api.permission:clinics.create'])->group(function () {
        Route::post('/clinics', [ClinicController::class, 'store']);
    });

    Route::middleware(['api.permission:clinics.view'])->group(function () {
        Route::get('/clinics', [ClinicController::class, 'index']);
        Route::get('/clinics/{clinic}', [ClinicController::class, 'show']);
        Route::get('/clinics/{clinic}/users', [ClinicController::class, 'users']);
        Route::get('/clinics/{clinic}/doctors', [ClinicController::class, 'doctors']);
        Route::get('/clinics/{clinic}/patients', [ClinicController::class, 'patients']);
        Route::get('/clinics/{clinic}/appointments', [ClinicController::class, 'appointments']);
        Route::get('/clinics/{clinic}/statistics', [ClinicController::class, 'statistics']);
    });

    Route::middleware(['api.permission:clinics.edit'])->group(function () {
        Route::put('/clinics/{clinic}', [ClinicController::class, 'update']);
    });

    Route::middleware(['api.permission:clinics.delete'])->group(function () {
        Route::delete('/clinics/{clinic}', [ClinicController::class, 'destroy']);
    });

    // Patient management (with usage validation)
    Route::middleware(['license.usage:patients', 'api.permission:patients.create'])->group(function () {
        Route::post('/patients', [PatientController::class, 'store']);
    });

    Route::middleware(['api.permission:patients.view'])->group(function () {
        Route::get('/patients', [PatientController::class, 'index']);
        Route::get('/patients/{patient}', [PatientController::class, 'show']);
        Route::get('/patients/{patient}/appointments', [PatientController::class, 'appointments']);
        Route::get('/patients/{patient}/encounters', [PatientController::class, 'encounters']);
        Route::get('/patients/{patient}/prescriptions', [PatientController::class, 'prescriptions']);
        Route::get('/patients/{patient}/lab-results', [PatientController::class, 'labResults']);
        Route::get('/patients/{patient}/medical-history', [PatientController::class, 'medicalHistory']);
        Route::get('/patients/{patient}/file-assets', [PatientController::class, 'fileAssets']);
        Route::get('/patients/search', [PatientController::class, 'search']);
    });

    Route::middleware(['api.permission:patients.edit'])->group(function () {
        Route::put('/patients/{patient}', [PatientController::class, 'update']);
    });

    Route::middleware(['api.permission:patients.delete'])->group(function () {
        Route::delete('/patients/{patient}', [PatientController::class, 'destroy']);
    });

    Route::middleware(['api.permission:file_assets.upload'])->group(function () {
        Route::post('/patients/{patient}/file-assets', [PatientController::class, 'uploadFile']);
    });

    // Doctor management (with usage validation)
    Route::middleware(['license.usage:users', 'api.permission:doctors.create'])->group(function () {
        Route::post('/doctors', [DoctorController::class, 'store']);
    });

    Route::middleware(['api.permission:doctors.view'])->group(function () {
        Route::get('/doctors', [DoctorController::class, 'index']);
        Route::get('/doctors/{doctor}', [DoctorController::class, 'show']);
        Route::get('/doctors/{doctor}/appointments', [DoctorController::class, 'appointments']);
        Route::get('/doctors/{doctor}/encounters', [DoctorController::class, 'encounters']);
        Route::get('/doctors/{doctor}/patients', [DoctorController::class, 'patients']);
        Route::get('/doctors/{doctor}/availability', [DoctorController::class, 'availability']);
        Route::get('/doctors/{doctor}/statistics', [DoctorController::class, 'statistics']);
    });

    Route::middleware(['api.permission:doctors.edit'])->group(function () {
        Route::put('/doctors/{doctor}', [DoctorController::class, 'update']);
        Route::post('/doctors/{doctor}/availability', [DoctorController::class, 'updateAvailability']);
    });

    Route::middleware(['api.permission:doctors.delete'])->group(function () {
        Route::delete('/doctors/{doctor}', [DoctorController::class, 'destroy']);
    });

    // Appointment management (with usage validation)
    Route::middleware(['license.usage:appointments', 'api.permission:appointments.create'])->group(function () {
        Route::post('/appointments', [AppointmentController::class, 'store']);
    });

    Route::middleware(['api.permission:appointments.view'])->group(function () {
        Route::get('/appointments', [AppointmentController::class, 'index']);
        Route::get('/appointments/{appointment}', [AppointmentController::class, 'show']);
        Route::get('/appointments/available-slots', [AppointmentController::class, 'availableSlots']);
        Route::get('/appointments/conflicts', [AppointmentController::class, 'checkConflicts']);
        Route::get('/appointments/today', [AppointmentController::class, 'today']);
        Route::get('/appointments/upcoming', [AppointmentController::class, 'upcoming']);
    });

    Route::middleware(['api.permission:appointments.edit'])->group(function () {
        Route::put('/appointments/{appointment}', [AppointmentController::class, 'update']);
        Route::post('/appointments/{appointment}/reschedule', [AppointmentController::class, 'reschedule']);
        Route::post('/appointments/{appointment}/reminder', [AppointmentController::class, 'sendReminder']);
    });

    Route::middleware(['api.permission:appointments.delete'])->group(function () {
        Route::delete('/appointments/{appointment}', [AppointmentController::class, 'destroy']);
    });

    Route::middleware(['api.permission:appointments.checkin'])->group(function () {
        Route::post('/appointments/{appointment}/check-in', [AppointmentController::class, 'checkIn']);
        Route::post('/appointments/{appointment}/check-out', [AppointmentController::class, 'checkOut']);
    });

    Route::middleware(['api.permission:appointments.cancel'])->group(function () {
        Route::post('/appointments/{appointment}/cancel', [AppointmentController::class, 'cancel']);
    });

    // Encounter management
    Route::middleware(['api.permission:encounters.view'])->group(function () {
        Route::get('/encounters', [EncounterController::class, 'index']);
        Route::get('/encounters/{encounter}', [EncounterController::class, 'show']);
        Route::get('/encounters/{encounter}/prescriptions', [EncounterController::class, 'prescriptions']);
        Route::get('/encounters/{encounter}/lab-results', [EncounterController::class, 'labResults']);
        Route::get('/encounters/{encounter}/file-assets', [EncounterController::class, 'fileAssets']);
    });

    Route::middleware(['api.permission:encounters.create'])->group(function () {
        Route::post('/encounters', [EncounterController::class, 'store']);
    });

    Route::middleware(['api.permission:encounters.edit'])->group(function () {
        Route::put('/encounters/{encounter}', [EncounterController::class, 'update']);
        Route::put('/encounters/{encounter}/soap-notes', [EncounterController::class, 'updateSoapNotes']);
    });

    Route::middleware(['api.permission:encounters.delete'])->group(function () {
        Route::delete('/encounters/{encounter}', [EncounterController::class, 'destroy']);
    });

    Route::middleware(['api.permission:encounters.complete'])->group(function () {
        Route::post('/encounters/{encounter}/complete', [EncounterController::class, 'complete']);
    });

    Route::middleware(['api.permission:file_assets.upload'])->group(function () {
        Route::post('/encounters/{encounter}/file-assets', [EncounterController::class, 'uploadFile']);
    });

    // Prescription management
    Route::middleware(['api.permission:prescriptions.view'])->group(function () {
        Route::get('/prescriptions', [PrescriptionController::class, 'index']);
        Route::get('/prescriptions/{prescription}', [PrescriptionController::class, 'show']);
        Route::get('/prescriptions/{prescription}/items', [PrescriptionController::class, 'items']);
        Route::get('/prescriptions/{prescription}/qr', [PrescriptionController::class, 'qrCode']);
        Route::get('/prescriptions/active', [PrescriptionController::class, 'active']);
        Route::get('/prescriptions/expired', [PrescriptionController::class, 'expired']);
        Route::get('/prescriptions/needs-refill', [PrescriptionController::class, 'needsRefill']);
    });

    Route::middleware(['api.permission:prescriptions.create'])->group(function () {
        Route::post('/prescriptions', [PrescriptionController::class, 'store']);
        Route::post('/prescriptions/{prescription}/items', [PrescriptionController::class, 'addItem']);
    });

    Route::middleware(['api.permission:prescriptions.edit'])->group(function () {
        Route::put('/prescriptions/{prescription}', [PrescriptionController::class, 'update']);
        Route::put('/prescriptions/{prescription}/items/{item}', [PrescriptionController::class, 'updateItem']);
        Route::post('/prescriptions/{prescription}/verify', [PrescriptionController::class, 'verify']);
        Route::post('/prescriptions/{prescription}/dispense', [PrescriptionController::class, 'dispense']);
        Route::post('/prescriptions/{prescription}/refill', [PrescriptionController::class, 'refill']);
    });

    Route::middleware(['api.permission:prescriptions.delete'])->group(function () {
        Route::delete('/prescriptions/{prescription}', [PrescriptionController::class, 'destroy']);
        Route::delete('/prescriptions/{prescription}/items/{item}', [PrescriptionController::class, 'removeItem']);
    });

    Route::middleware(['api.permission:prescriptions.download'])->group(function () {
        Route::get('/prescriptions/{prescription}/pdf', [PrescriptionController::class, 'downloadPdf']);
    });

    // Lab result management (requires lab_results feature)
    Route::middleware(['license.feature:lab_results'])->group(function () {
        Route::apiResource('lab-results', LabResultController::class);
        Route::get('/lab-results/{labResult}/file-assets', [LabResultController::class, 'fileAssets']);
        Route::post('/lab-results/{labResult}/file-assets', [LabResultController::class, 'uploadFile']);
        Route::post('/lab-results/{labResult}/review', [LabResultController::class, 'review']);
        Route::get('/lab-results/pending', [LabResultController::class, 'pending']);
        Route::get('/lab-results/abnormal', [LabResultController::class, 'abnormal']);
    });

    // File asset management
    Route::apiResource('file-assets', FileAssetController::class);
    Route::get('/file-assets/{fileAsset}/download', [FileAssetController::class, 'download']);
    Route::get('/file-assets/{fileAsset}/preview', [FileAssetController::class, 'preview']);
    Route::post('/file-assets/upload', [FileAssetController::class, 'upload']);
    Route::get('/file-assets/categories', [FileAssetController::class, 'categories']);

    // Activity Log Management
    Route::apiResource('activity-logs', App\Http\Controllers\Api\ActivityLogController::class);
    Route::get('/activity-logs/user/{user}', [App\Http\Controllers\Api\ActivityLogController::class, 'userActivity']);
    Route::get('/activity-logs/module/{module}', [App\Http\Controllers\Api\ActivityLogController::class, 'moduleActivity']);
    Route::get('/activity-logs/export', [App\Http\Controllers\Api\ActivityLogController::class, 'export']);

    // Room Management
    Route::apiResource('rooms', App\Http\Controllers\Api\RoomController::class);
    Route::get('/rooms/{room}/availability', [App\Http\Controllers\Api\RoomController::class, 'availability']);
    Route::post('/rooms/{room}/book', [App\Http\Controllers\Api\RoomController::class, 'book']);
    Route::post('/rooms/{room}/release', [App\Http\Controllers\Api\RoomController::class, 'release']);
    Route::get('/rooms/available', [App\Http\Controllers\Api\RoomController::class, 'available']);

    // Notification Management
    Route::apiResource('notifications', App\Http\Controllers\Api\NotificationController::class);
    Route::post('/notifications/mark-read', [App\Http\Controllers\Api\NotificationController::class, 'markAsRead']);
    Route::post('/notifications/mark-all-read', [App\Http\Controllers\Api\NotificationController::class, 'markAllAsRead']);
    Route::get('/notifications/unread', [App\Http\Controllers\Api\NotificationController::class, 'unread']);
    Route::post('/notifications/send', [App\Http\Controllers\Api\NotificationController::class, 'send']);

    // Queue Management
    Route::middleware(['api.permission:queue.view'])->group(function () {
        Route::get('/queues', [App\Http\Controllers\Api\QueueController::class, 'index']);
        Route::get('/queues/{queue}', [App\Http\Controllers\Api\QueueController::class, 'show']);
        Route::get('/queues/{queue}/patients', [App\Http\Controllers\Api\QueueController::class, 'patients']);
        Route::get('/queues/active', [App\Http\Controllers\Api\QueueController::class, 'active']);
    });

    Route::middleware(['api.permission:queue.add'])->group(function () {
        Route::post('/queues', [App\Http\Controllers\Api\QueueController::class, 'store']);
        Route::post('/queues/{queue}/add-patient', [App\Http\Controllers\Api\QueueController::class, 'addPatient']);
    });

    Route::middleware(['api.permission:queue.remove'])->group(function () {
        Route::delete('/queues/{queue}', [App\Http\Controllers\Api\QueueController::class, 'destroy']);
        Route::post('/queues/{queue}/remove-patient', [App\Http\Controllers\Api\QueueController::class, 'removePatient']);
    });

    Route::middleware(['api.permission:queue.process'])->group(function () {
        Route::put('/queues/{queue}', [App\Http\Controllers\Api\QueueController::class, 'update']);
        Route::post('/queues/{queue}/next-patient', [App\Http\Controllers\Api\QueueController::class, 'nextPatient']);
    });

    // Billing and Payment Management
    Route::apiResource('bills', App\Http\Controllers\Api\BillController::class);
    Route::get('/bills/{bill}/items', [App\Http\Controllers\Api\BillController::class, 'items']);
    Route::post('/bills/{bill}/items', [App\Http\Controllers\Api\BillController::class, 'addItem']);
    Route::put('/bills/{bill}/items/{item}', [App\Http\Controllers\Api\BillController::class, 'updateItem']);
    Route::delete('/bills/{bill}/items/{item}', [App\Http\Controllers\Api\BillController::class, 'removeItem']);
    Route::post('/bills/{bill}/pay', [App\Http\Controllers\Api\BillController::class, 'pay']);
    Route::get('/bills/{bill}/pdf', [App\Http\Controllers\Api\BillController::class, 'downloadPdf']);
    Route::get('/bills/outstanding', [App\Http\Controllers\Api\BillController::class, 'outstanding']);
    Route::get('/bills/paid', [App\Http\Controllers\Api\BillController::class, 'paid']);

    // Insurance Management
    Route::apiResource('insurance', App\Http\Controllers\Api\InsuranceController::class);
    Route::get('/insurance/{insurance}/verify', [App\Http\Controllers\Api\InsuranceController::class, 'verify']);
    Route::get('/insurance/providers', [App\Http\Controllers\Api\InsuranceController::class, 'providers']);

    // Medrep management (requires medrep_management feature)
    Route::middleware(['license.feature:medrep_management'])->group(function () {
        Route::apiResource('medreps', MedrepController::class);
        Route::get('/medreps/{medrep}/visits', [MedrepController::class, 'visits']);
        Route::post('/medreps/{medrep}/visits', [MedrepController::class, 'scheduleVisit']);
        Route::put('/medreps/{medrep}/visits/{visit}', [MedrepController::class, 'updateVisit']);
        Route::delete('/medreps/{medrep}/visits/{visit}', [MedrepController::class, 'cancelVisit']);
        Route::get('/medrep-visits', [MedrepController::class, 'allVisits']);
        Route::get('/medrep-visits/upcoming', [MedrepController::class, 'upcomingVisits']);
    });


    // License management routes are now in routes/license.php

    // Search and filtering
    Route::middleware(['api.permission:search.patients'])->group(function () {
        Route::get('/search/patients', [PatientController::class, 'search']);
    });

    Route::middleware(['api.permission:search.doctors'])->group(function () {
        Route::get('/search/doctors', [DoctorController::class, 'search']);
    });

    Route::middleware(['api.permission:search.global'])->group(function () {
        Route::get('/search/appointments', [AppointmentController::class, 'search']);
        Route::get('/search/prescriptions', [PrescriptionController::class, 'search']);
        Route::get('/search/users', [UserController::class, 'search']);
        Route::get('/search/global', [App\Http\Controllers\Api\SearchController::class, 'global']);
    });

    // Bulk Operations
    Route::prefix('bulk')->group(function () {
        Route::post('/patients/import', [PatientController::class, 'bulkImport']);
        Route::post('/patients/export', [PatientController::class, 'bulkExport']);
        Route::post('/appointments/create', [AppointmentController::class, 'bulkCreate']);
        Route::post('/prescriptions/create', [PrescriptionController::class, 'bulkCreate']);
        Route::post('/users/import', [UserController::class, 'bulkImport']);
        Route::post('/users/export', [UserController::class, 'bulkExport']);
    });

    // Reports and analytics (with license validation)
    Route::middleware(['license'])->group(function () {
        Route::get('/reports/appointments', [AppointmentController::class, 'reports']);
        Route::get('/reports/prescriptions', [PrescriptionController::class, 'reports']);
        Route::get('/reports/patients', [PatientController::class, 'reports']);
        Route::get('/reports/doctors', [DoctorController::class, 'reports']);
        Route::get('/reports/lab-results', [LabResultController::class, 'reports']);
    });

    // Advanced reporting (requires advanced_reporting feature)
    Route::middleware(['license.feature:advanced_reporting'])->group(function () {
        Route::get('/reports/advanced', [AppointmentController::class, 'advancedReports']);
        Route::get('/reports/analytics', [DashboardController::class, 'analytics']);
        Route::get('/reports/export', [AppointmentController::class, 'exportReports']);
    });

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

    // System Management Routes
    Route::middleware(['api.permission:system.info'])->group(function () {
        Route::get('/system/health', [App\Http\Controllers\Api\SystemController::class, 'health']);
        Route::get('/system/status', [App\Http\Controllers\Api\SystemController::class, 'status']);
        Route::get('/system/usage', [App\Http\Controllers\Api\SystemController::class, 'usage']);
    });

    Route::middleware(['api.permission:system.admin'])->group(function () {
        Route::get('/system/logs', [App\Http\Controllers\Api\SystemController::class, 'logs']);
        Route::post('/system/backup', [App\Http\Controllers\Api\SystemController::class, 'backup']);
        Route::post('/system/clear-cache', [App\Http\Controllers\Api\SystemController::class, 'clearCache']);
    });

    // Integration Routes
    Route::prefix('integrations')->group(function () {
        // Lab Integration
        Route::post('/lab/sync', [App\Http\Controllers\Api\IntegrationController::class, 'syncLabResults']);
        Route::get('/lab/providers', [App\Http\Controllers\Api\IntegrationController::class, 'labProviders']);

        // Pharmacy Integration
        Route::post('/pharmacy/sync', [App\Http\Controllers\Api\IntegrationController::class, 'syncPharmacy']);
        Route::get('/pharmacy/providers', [App\Http\Controllers\Api\IntegrationController::class, 'pharmacyProviders']);

        // Insurance Integration
        Route::post('/insurance/verify', [App\Http\Controllers\Api\IntegrationController::class, 'verifyInsurance']);
        Route::get('/insurance/providers', [App\Http\Controllers\Api\IntegrationController::class, 'insuranceProviders']);

        // Payment Gateway Integration
        Route::post('/payment/process', [App\Http\Controllers\Api\IntegrationController::class, 'processPayment']);
        Route::get('/payment/methods', [App\Http\Controllers\Api\IntegrationController::class, 'paymentMethods']);

        // SMS/Email Integration
        Route::post('/sms/send', [App\Http\Controllers\Api\IntegrationController::class, 'sendSms']);
        Route::post('/email/send', [App\Http\Controllers\Api\IntegrationController::class, 'sendEmail']);
    });

    // Audit and Compliance Routes
    Route::middleware(['api.permission:activity_logs.view'])->group(function () {
        Route::get('/audit/logs', [App\Http\Controllers\Api\AuditController::class, 'logs']);
        Route::get('/audit/compliance', [App\Http\Controllers\Api\AuditController::class, 'compliance']);
        Route::get('/audit/security', [App\Http\Controllers\Api\AuditController::class, 'security']);
    });

    Route::middleware(['api.permission:activity_logs.export'])->group(function () {
        Route::get('/audit/export', [App\Http\Controllers\Api\AuditController::class, 'export']);
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

// Include license API routes
require __DIR__.'/license.php';
