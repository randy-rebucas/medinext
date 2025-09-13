<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use App\Models\Setting;
use App\Services\SettingsService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class ClinicSettingsController extends Controller
{
    protected SettingsService $settingsService;

    public function __construct(SettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
    }

    /**
     * Show the clinic settings page
     */
    public function index()
    {
        return Inertia::render('admin/clinic-settings');
    }

    /**
     * Get clinic settings
     */
    public function getSettings(Request $request): JsonResponse
    {
        try {
            $clinicId = $request->user()->current_clinic_id;

            if (!$clinicId) {
                return response()->json([
                    'success' => false,
                    'message' => 'No clinic selected'
                ], 400);
            }

            $clinic = Clinic::findOrFail($clinicId);

            // Get all settings for the clinic using the service
            $settings = $this->settingsService->getAllForClinic($clinicId);

            // Get specific setting groups
            $clinicInfo = $this->settingsService->getClinicInfo($clinicId);
            $workingHours = $this->settingsService->getWorkingHours($clinicId);
            $notifications = $this->settingsService->getNotificationSettings($clinicId);
            $branding = $this->settingsService->getBrandingSettings($clinicId);
            $system = $this->settingsService->getSystemSettings($clinicId);
            $appointments = $this->settingsService->getAppointmentSettings($clinicId);
            $prescriptions = $this->settingsService->getPrescriptionSettings($clinicId);
            $billing = $this->settingsService->getBillingSettings($clinicId);
            $security = $this->settingsService->getSecuritySettings($clinicId);
            $integrations = $this->settingsService->getIntegrationSettings($clinicId);
            $queue = $this->settingsService->getQueueSettings($clinicId);
            $emr = $this->settingsService->getEMRSettings($clinicId);
            $files = $this->settingsService->getFileSettings($clinicId);
            $reports = $this->settingsService->getReportingSettings($clinicId);

            return response()->json([
                'success' => true,
                'data' => [
                    'clinic' => $clinic,
                    'settings' => $settings,
                    'clinic_info' => $clinicInfo,
                    'working_hours' => $workingHours,
                    'notifications' => $notifications,
                    'branding' => $branding,
                    'system' => $system,
                    'appointments' => $appointments,
                    'prescriptions' => $prescriptions,
                    'billing' => $billing,
                    'security' => $security,
                    'integrations' => $integrations,
                    'queue' => $queue,
                    'emr' => $emr,
                    'files' => $files,
                    'reports' => $reports,
                    'formatted_working_hours' => $this->settingsService->getFormattedWorkingHours($clinicId),
                    'is_clinic_open' => $this->settingsService->isClinicOpen($clinicId),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve clinic settings',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update clinic settings
     */
    public function updateSettings(Request $request): JsonResponse
    {
        try {
            $clinicId = $request->user()->current_clinic_id;

            if (!$clinicId) {
                return response()->json([
                    'success' => false,
                    'message' => 'No clinic selected'
                ], 400);
            }

            $validator = Validator::make($request->all(), [
                'clinic_name' => 'sometimes|string|max:255',
                'clinic_address' => 'sometimes|string|max:500',
                'clinic_phone' => 'sometimes|string|max:20',
                'clinic_email' => 'sometimes|email|max:255',
                'clinic_website' => 'sometimes|url|max:255',
                'timezone' => 'sometimes|string|max:50',
                'date_format' => 'sometimes|string|max:20',
                'time_format' => 'sometimes|string|max:20',
                'currency' => 'sometimes|string|max:3',
                'language' => 'sometimes|string|max:5',
                'appointment_duration' => 'sometimes|integer|min:5|max:480',
                'appointment_buffer_time' => 'sometimes|integer|min:0|max:60',
                'auto_confirm_appointments' => 'sometimes|boolean',
                'send_appointment_reminders' => 'sometimes|boolean',
                'reminder_hours_before' => 'sometimes|integer|min:1|max:168',
                'allow_online_booking' => 'sometimes|boolean',
                'require_patient_verification' => 'sometimes|boolean',
                'max_appointments_per_day' => 'sometimes|integer|min:1|max:200',
                'working_hours' => 'sometimes|array',
                'notification_settings' => 'sometimes|array',
                'privacy_settings' => 'sometimes|array',
                'integration_settings' => 'sometimes|array',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $clinic = Clinic::findOrFail($clinicId);
            $settings = $request->except(['clinic_name', 'clinic_address', 'clinic_phone', 'clinic_email', 'clinic_website']);

            // Update clinic basic information
            if ($request->has('clinic_name')) {
                $clinic->name = $request->clinic_name;
            }
            if ($request->has('clinic_address')) {
                $clinic->address = $request->clinic_address;
            }
            if ($request->has('clinic_phone')) {
                $clinic->phone = $request->clinic_phone;
            }
            if ($request->has('clinic_email')) {
                $clinic->email = $request->clinic_email;
            }
            if ($request->has('clinic_website')) {
                $clinic->website = $request->clinic_website;
            }
            $clinic->save();

            // Update settings using the service
            foreach ($settings as $key => $value) {
                $group = explode('.', $key)[0] ?? 'general';
                $type = is_array($value) ? 'json' : (is_bool($value) ? 'boolean' : (is_int($value) ? 'integer' : 'string'));

                $this->settingsService->set($key, $value, $clinicId, $type, $group, "Updated via API");
            }

            // Clear cache
            $this->settingsService->clearCache($clinicId);

            return response()->json([
                'success' => true,
                'message' => 'Clinic settings updated successfully',
                'data' => [
                    'clinic' => $clinic->fresh(),
                    'settings' => $this->settingsService->getAllForClinic($clinicId),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update clinic settings',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Initialize default settings for a clinic
     */
    public function initializeSettings(Request $request): JsonResponse
    {
        try {
            $clinicId = $request->user()->current_clinic_id;

            if (!$clinicId) {
                return response()->json([
                    'success' => false,
                    'message' => 'No clinic selected'
                ], 400);
            }

            $this->settingsService->initializeClinicSettings($clinicId);

            return response()->json([
                'success' => true,
                'message' => 'Default settings initialized successfully',
                'data' => $this->settingsService->getAllForClinic($clinicId)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to initialize settings',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
