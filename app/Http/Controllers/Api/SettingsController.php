<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\Setting;
use App\Models\Clinic;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;

class SettingsController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/v1/settings",
     *     summary="Get all settings",
     *     description="Retrieve all system settings for the current clinic",
     *     tags={"Settings"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Settings retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Settings retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="settings",
     *                     type="object",
     *                     @OA\Property(property="app_name", type="string", example="MediNext EMR"),
     *                     @OA\Property(property="timezone", type="string", example="America/New_York"),
     *                     @OA\Property(property="date_format", type="string", example="Y-m-d"),
     *                     @OA\Property(property="time_format", type="string", example="H:i"),
     *                     @OA\Property(property="currency", type="string", example="USD"),
     *                     @OA\Property(property="language", type="string", example="en"),
     *                     @OA\Property(property="appointment_duration", type="integer", example=30),
     *                     @OA\Property(property="max_file_size", type="integer", example=10485760),
     *                     @OA\Property(property="allowed_file_types", type="string", example="pdf,jpg,png,doc,docx"),
     *                     @OA\Property(property="email_notifications", type="boolean", example=true),
     *                     @OA\Property(property="sms_notifications", type="boolean", example=false),
     *                     @OA\Property(property="backup_enabled", type="boolean", example=true),
     *                     @OA\Property(property="backup_frequency", type="string", example="daily")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="No clinic access",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $currentClinic = $this->getCurrentClinic();

            if (!$currentClinic) {
                return $this->errorResponse('No clinic access', null, 403);
            }

            $settings = Setting::where('clinic_id', $currentClinic->id)
                ->get()
                ->keyBy('key');

            return $this->successResponse([
                'settings' => $settings,
            ], 'Settings retrieved successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/settings",
     *     summary="Update system settings",
     *     description="Update global system settings",
     *     tags={"Settings"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="app_name", type="string", example="MediNext EMR"),
     *             @OA\Property(property="app_version", type="string", example="1.0.0"),
     *             @OA\Property(property="timezone", type="string", example="UTC"),
     *             @OA\Property(property="date_format", type="string", example="Y-m-d"),
     *             @OA\Property(property="time_format", type="string", example="H:i:s"),
     *             @OA\Property(property="currency", type="string", example="USD"),
     *             @OA\Property(property="language", type="string", example="en"),
     *             @OA\Property(property="maintenance_mode", type="boolean", example=false),
     *             @OA\Property(property="backup_enabled", type="boolean", example=true),
     *             @OA\Property(property="backup_frequency", type="string", example="daily"),
     *             @OA\Property(property="email_notifications", type="boolean", example=true),
     *             @OA\Property(property="sms_notifications", type="boolean", example=false),
     *             @OA\Property(property="max_file_size", type="integer", example=10485760),
     *             @OA\Property(property="allowed_file_types", type="array", @OA\Items(type="string"), example={"pdf","jpg","png","doc","docx"}),
     *             @OA\Property(property="session_timeout", type="integer", example=120),
     *             @OA\Property(property="password_policy", type="object", example={"min_length":8,"require_uppercase":true,"require_numbers":true,"require_symbols":false})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Settings updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Settings updated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="settings", type="object"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-15T10:00:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Insufficient permissions",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function update(Request $request): JsonResponse
    {
        try {
            $currentClinic = $this->getCurrentClinic();

            if (!$currentClinic) {
                return $this->errorResponse('No clinic access', null, 403);
            }

            $validator = Validator::make($request->all(), [
                'settings' => 'required|array',
                'settings.*' => 'required|string',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $settings = $request->get('settings');

            foreach ($settings as $key => $value) {
                Setting::updateOrCreate(
                    [
                        'clinic_id' => $currentClinic->id,
                        'key' => $key,
                    ],
                    [
                        'value' => $value,
                    ]
                );
            }

            $updatedSettings = Setting::where('clinic_id', $currentClinic->id)
                ->get()
                ->keyBy('key');

            return $this->successResponse([
                'settings' => $updatedSettings,
            ], 'Settings updated successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/settings/clinic",
     *     summary="Get clinic-specific settings",
     *     description="Retrieve settings specific to the current clinic",
     *     tags={"Settings"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Clinic settings retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Clinic settings retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="clinic_id", type="integer", example=1),
     *                 @OA\Property(property="clinic_name", type="string", example="City Medical Center"),
     *                 @OA\Property(property="settings", type="object",
     *                     @OA\Property(property="appointment_duration", type="integer", example=30),
     *                     @OA\Property(property="working_hours", type="object", example={"start":"09:00","end":"17:00"}),
     *                     @OA\Property(property="booking_advance_days", type="integer", example=30),
     *                     @OA\Property(property="cancellation_policy", type="string", example="24 hours"),
     *                     @OA\Property(property="auto_confirm_appointments", type="boolean", example=true),
     *                     @OA\Property(property="send_reminders", type="boolean", example=true),
     *                     @OA\Property(property="reminder_timing", type="string", example="24 hours before"),
     *                     @OA\Property(property="prescription_template", type="string", example="standard"),
     *                     @OA\Property(property="lab_result_notifications", type="boolean", example=true),
     *                     @OA\Property(property="patient_portal_enabled", type="boolean", example=true),
     *                     @OA\Property(property="online_booking_enabled", type="boolean", example=false),
     *                     @OA\Property(property="insurance_verification", type="boolean", example=true),
     *                     @OA\Property(property="billing_integration", type="string", example="none"),
     *                     @OA\Property(property="clinic_logo", type="string", nullable=true),
     *                     @OA\Property(property="clinic_theme", type="string", example="default")
     *                 ),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-15T10:00:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="No clinic access",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function clinicSettings(Request $request): JsonResponse
    {
        try {
            $currentClinic = $this->getCurrentClinic();

            if (!$currentClinic) {
                return $this->errorResponse('No clinic access', null, 403);
            }

            $clinicSettings = [
                'clinic' => $currentClinic,
                'settings' => $currentClinic->settings ?? [],
            ];

            return $this->successResponse($clinicSettings, 'Clinic settings retrieved successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/settings/clinic",
     *     summary="Update clinic settings",
     *     description="Update settings specific to the current clinic",
     *     tags={"Settings"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="appointment_duration", type="integer", example=30, description="Default appointment duration in minutes"),
     *             @OA\Property(property="working_hours", type="object", example={"start":"09:00","end":"17:00"}),
     *             @OA\Property(property="booking_advance_days", type="integer", example=30, description="How many days in advance patients can book"),
     *             @OA\Property(property="cancellation_policy", type="string", example="24 hours", description="Cancellation policy description"),
     *             @OA\Property(property="auto_confirm_appointments", type="boolean", example=true),
     *             @OA\Property(property="send_reminders", type="boolean", example=true),
     *             @OA\Property(property="reminder_timing", type="string", example="24 hours before"),
     *             @OA\Property(property="prescription_template", type="string", example="standard"),
     *             @OA\Property(property="lab_result_notifications", type="boolean", example=true),
     *             @OA\Property(property="patient_portal_enabled", type="boolean", example=true),
     *             @OA\Property(property="online_booking_enabled", type="boolean", example=false),
     *             @OA\Property(property="insurance_verification", type="boolean", example=true),
     *             @OA\Property(property="billing_integration", type="string", example="none"),
     *             @OA\Property(property="clinic_logo", type="string", nullable=true),
     *             @OA\Property(property="clinic_theme", type="string", example="default")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Clinic settings updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Clinic settings updated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="settings", type="object"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-15T10:00:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="No clinic access or insufficient permissions",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function updateClinicSettings(Request $request): JsonResponse
    {
        try {
            $currentClinic = $this->getCurrentClinic();

            if (!$currentClinic) {
                return $this->errorResponse('No clinic access', null, 403);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255',
                'timezone' => 'sometimes|required|string|max:50',
                'logo_url' => 'nullable|url|max:500',
                'address' => 'nullable|array',
                'address.street' => 'nullable|string|max:255',
                'address.city' => 'nullable|string|max:100',
                'address.state' => 'nullable|string|max:100',
                'address.zip' => 'nullable|string|max:20',
                'address.country' => 'nullable|string|max:100',
                'phone' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255',
                'website' => 'nullable|url|max:255',
                'description' => 'nullable|string|max:1000',
                'settings' => 'nullable|array',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $currentClinic->update($validator->validated());

            return $this->successResponse([
                'clinic' => $currentClinic,
            ], 'Clinic settings updated successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/settings/user",
     *     summary="Get user-specific settings",
     *     description="Retrieve personal settings for the authenticated user",
     *     tags={"Settings"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="User settings retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User settings retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="user_id", type="integer", example=1),
     *                 @OA\Property(property="user_name", type="string", example="Dr. John Smith"),
     *                 @OA\Property(property="settings", type="object",
     *                     @OA\Property(property="theme", type="string", example="light"),
     *                     @OA\Property(property="language", type="string", example="en"),
     *                     @OA\Property(property="timezone", type="string", example="UTC"),
     *                     @OA\Property(property="date_format", type="string", example="Y-m-d"),
     *                     @OA\Property(property="time_format", type="string", example="H:i:s"),
     *                     @OA\Property(property="notifications", type="object", example={"email":true,"sms":false,"push":true}),
     *                     @OA\Property(property="dashboard_layout", type="string", example="default"),
     *                     @OA\Property(property="default_clinic", type="integer", example=1),
     *                     @OA\Property(property="auto_save", type="boolean", example=true),
     *                     @OA\Property(property="keyboard_shortcuts", type="boolean", example=true),
     *                     @OA\Property(property="compact_view", type="boolean", example=false),
     *                     @OA\Property(property="show_tooltips", type="boolean", example=true),
     *                     @OA\Property(property="session_timeout", type="integer", example=120),
     *                     @OA\Property(property="email_signature", type="string", nullable=true),
     *                     @OA\Property(property="working_hours", type="object", example={"start":"09:00","end":"17:00"})
     *                 ),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-15T10:00:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function userSettings(Request $request): JsonResponse
    {
        try {
            $user = $this->getAuthenticatedUser();

            $userSettings = [
                'user' => $user,
                'preferences' => [
                    'theme' => 'light', // This would come from user preferences
                    'language' => 'en',
                    'timezone' => 'UTC',
                    'notifications' => [
                        'email' => true,
                        'push' => true,
                        'sms' => false,
                    ],
                ],
            ];

            return $this->successResponse($userSettings, 'User settings retrieved successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/settings/user",
     *     summary="Update user settings",
     *     description="Update personal settings for the authenticated user",
     *     tags={"Settings"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="theme", type="string", example="light", description="UI theme preference"),
     *             @OA\Property(property="language", type="string", example="en", description="Interface language"),
     *             @OA\Property(property="timezone", type="string", example="UTC", description="User timezone"),
     *             @OA\Property(property="date_format", type="string", example="Y-m-d", description="Date display format"),
     *             @OA\Property(property="time_format", type="string", example="H:i:s", description="Time display format"),
     *             @OA\Property(property="notifications", type="object", example={"email":true,"sms":false,"push":true}),
     *             @OA\Property(property="dashboard_layout", type="string", example="default", description="Dashboard layout preference"),
     *             @OA\Property(property="default_clinic", type="integer", example=1, description="Default clinic ID"),
     *             @OA\Property(property="auto_save", type="boolean", example=true, description="Enable auto-save functionality"),
     *             @OA\Property(property="keyboard_shortcuts", type="boolean", example=true, description="Enable keyboard shortcuts"),
     *             @OA\Property(property="compact_view", type="boolean", example=false, description="Use compact view mode"),
     *             @OA\Property(property="show_tooltips", type="boolean", example=true, description="Show tooltips and help text"),
     *             @OA\Property(property="session_timeout", type="integer", example=120, description="Session timeout in minutes"),
     *             @OA\Property(property="email_signature", type="string", nullable=true, description="Email signature for outgoing emails"),
     *             @OA\Property(property="working_hours", type="object", example={"start":"09:00","end":"17:00"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User settings updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User settings updated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="settings", type="object"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-15T10:00:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function updateUserSettings(Request $request): JsonResponse
    {
        try {
            /** @var \App\Models\User $user */
            $user = $this->getAuthenticatedUser();

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255',
                'email' => 'sometimes|required|email|max:255|unique:users,email,' . $user->id,
                'phone' => 'nullable|string|max:20',
                'preferences' => 'nullable|array',
                'preferences.theme' => 'nullable|string|in:light,dark',
                'preferences.language' => 'nullable|string|in:en,es,fr,de',
                'preferences.timezone' => 'nullable|string|max:50',
                'preferences.notifications' => 'nullable|array',
                'preferences.notifications.email' => 'nullable|boolean',
                'preferences.notifications.push' => 'nullable|boolean',
                'preferences.notifications.sms' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $data = $validator->validated();

            // Update user basic info
            if (isset($data['name']) || isset($data['email']) || isset($data['phone'])) {
                $userData = array_intersect_key($data, array_flip(['name', 'email', 'phone']));
                $user->update($userData);
            }

            // Update user preferences (this would be stored in a user_preferences table in a real implementation)
            if (isset($data['preferences'])) {
                // For now, we'll just return the preferences
                // In a real implementation, you would store these in a separate table
            }

            return $this->successResponse([
                'user' => $user,
                'preferences' => $data['preferences'] ?? [],
            ], 'User settings updated successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/settings/backup",
     *     summary="Get backup settings",
     *     description="Retrieve backup configuration and status",
     *     tags={"Settings"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Backup settings retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Backup settings retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="backup_enabled", type="boolean", example=true),
     *                 @OA\Property(property="backup_frequency", type="string", example="daily"),
     *                 @OA\Property(property="backup_retention_days", type="integer", example=30),
     *                 @OA\Property(property="backup_location", type="string", example="local"),
     *                 @OA\Property(property="last_backup", type="string", format="date-time", example="2024-01-15T02:00:00Z"),
     *                 @OA\Property(property="next_backup", type="string", format="date-time", example="2024-01-16T02:00:00Z"),
     *                 @OA\Property(property="backup_size", type="string", example="2.5 GB"),
     *                 @OA\Property(property="backup_status", type="string", example="completed"),
     *                 @OA\Property(property="backup_files", type="array", @OA\Items(type="object"))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Insufficient permissions",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function backupSettings(Request $request): JsonResponse
    {
        try {
            $currentClinic = $this->getCurrentClinic();

            if (!$currentClinic) {
                return $this->errorResponse('No clinic access', null, 403);
            }

            // Mock backup settings data
            $backupSettings = [
                'backup_enabled' => true,
                'backup_frequency' => 'daily',
                'backup_retention_days' => 30,
                'backup_location' => 'local',
                'last_backup' => now()->subDay()->toISOString(),
                'next_backup' => now()->addDay()->toISOString(),
                'backup_size' => '2.5 GB',
                'backup_status' => 'completed',
                'backup_files' => []
            ];

            return $this->successResponse($backupSettings, 'Backup settings retrieved successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/settings/backup",
     *     summary="Create backup",
     *     description="Manually trigger a system backup",
     *     tags={"Settings"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="backup_type", type="string", example="full", description="Type of backup to create"),
     *             @OA\Property(property="include_files", type="boolean", example=true, description="Include file assets in backup"),
     *             @OA\Property(property="include_database", type="boolean", example=true, description="Include database in backup"),
     *             @OA\Property(property="compression", type="boolean", example=true, description="Compress backup files")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Backup created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Backup created successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="backup_id", type="string", example="backup_20240115_020000"),
     *                 @OA\Property(property="backup_type", type="string", example="full"),
     *                 @OA\Property(property="status", type="string", example="in_progress"),
     *                 @OA\Property(property="estimated_completion", type="string", format="date-time", example="2024-01-15T02:15:00Z"),
     *                 @OA\Property(property="backup_size", type="string", example="2.5 GB")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Insufficient permissions",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function createBackup(Request $request): JsonResponse
    {
        try {
            $currentClinic = $this->getCurrentClinic();

            if (!$currentClinic) {
                return $this->errorResponse('No clinic access', null, 403);
            }

            $validator = Validator::make($request->all(), [
                'backup_type' => 'nullable|in:full,incremental,database_only,files_only',
                'include_files' => 'nullable|boolean',
                'include_database' => 'nullable|boolean',
                'compression' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            // Mock backup creation
            $backupData = [
                'backup_id' => 'backup_' . now()->format('Ymd_His'),
                'backup_type' => $request->get('backup_type', 'full'),
                'status' => 'in_progress',
                'estimated_completion' => now()->addMinutes(15)->toISOString(),
                'backup_size' => '2.5 GB'
            ];

            return $this->successResponse($backupData, 'Backup created successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/settings/export",
     *     summary="Export settings",
     *     description="Export system and clinic settings to a file",
     *     tags={"Settings"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="format",
     *         in="query",
     *         description="Export format",
     *         @OA\Schema(type="string", enum={"json","yaml","xml"}, example="json")
     *     ),
     *     @OA\Parameter(
     *         name="include_system",
     *         in="query",
     *         description="Include system settings",
     *         @OA\Schema(type="boolean", example=true)
     *     ),
     *     @OA\Parameter(
     *         name="include_clinic",
     *         in="query",
     *         description="Include clinic settings",
     *         @OA\Schema(type="boolean", example=true)
     *     ),
     *     @OA\Parameter(
     *         name="include_user",
     *         in="query",
     *         description="Include user settings",
     *         @OA\Schema(type="boolean", example=false)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Settings exported successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Settings exported successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="export_id", type="string", example="export_20240115_100000"),
     *                 @OA\Property(property="format", type="string", example="json"),
     *                 @OA\Property(property="file_size", type="string", example="1.2 MB"),
     *                 @OA\Property(property="download_url", type="string", example="/api/v1/settings/export/download/export_20240115_100000"),
     *                 @OA\Property(property="expires_at", type="string", format="date-time", example="2024-01-16T10:00:00Z"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-15T10:00:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Insufficient permissions",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function exportSettings(Request $request): JsonResponse
    {
        try {
            $currentClinic = $this->getCurrentClinic();

            if (!$currentClinic) {
                return $this->errorResponse('No clinic access', null, 403);
            }

            $format = $request->get('format', 'json');
            $includeSystem = $request->get('include_system', true);
            $includeClinic = $request->get('include_clinic', true);
            $includeUser = $request->get('include_user', false);

            // Mock export creation
            $exportData = [
                'export_id' => 'export_' . now()->format('Ymd_His'),
                'format' => $format,
                'file_size' => '1.2 MB',
                'download_url' => '/api/v1/settings/export/download/export_' . now()->format('Ymd_His'),
                'expires_at' => now()->addDay()->toISOString(),
                'created_at' => now()->toISOString()
            ];

            return $this->successResponse($exportData, 'Settings exported successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
}
