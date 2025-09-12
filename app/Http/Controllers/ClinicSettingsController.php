<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ClinicSettingsController extends Controller
{
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
            
            // Get clinic-specific settings
            $settings = Setting::where('clinic_id', $clinicId)
                              ->orWhereNull('clinic_id')
                              ->get()
                              ->keyBy('key');

            // Get default settings
            $defaultSettings = [
                'clinic_name' => $clinic->name,
                'clinic_address' => $clinic->address,
                'clinic_phone' => $clinic->phone,
                'clinic_email' => $clinic->email,
                'clinic_website' => $clinic->website,
                'timezone' => 'UTC',
                'date_format' => 'Y-m-d',
                'time_format' => 'H:i',
                'currency' => 'USD',
                'language' => 'en',
                'appointment_duration' => 30,
                'appointment_buffer_time' => 15,
                'auto_confirm_appointments' => false,
                'send_appointment_reminders' => true,
                'reminder_hours_before' => 24,
                'allow_online_booking' => true,
                'require_patient_verification' => false,
                'max_appointments_per_day' => 50,
                'working_hours' => [
                    'monday' => ['start' => '09:00', 'end' => '17:00', 'enabled' => true],
                    'tuesday' => ['start' => '09:00', 'end' => '17:00', 'enabled' => true],
                    'wednesday' => ['start' => '09:00', 'end' => '17:00', 'enabled' => true],
                    'thursday' => ['start' => '09:00', 'end' => '17:00', 'enabled' => true],
                    'friday' => ['start' => '09:00', 'end' => '17:00', 'enabled' => true],
                    'saturday' => ['start' => '09:00', 'end' => '13:00', 'enabled' => false],
                    'sunday' => ['start' => '09:00', 'end' => '13:00', 'enabled' => false],
                ],
                'notification_settings' => [
                    'email_notifications' => true,
                    'sms_notifications' => false,
                    'push_notifications' => true,
                    'appointment_reminders' => true,
                    'prescription_ready' => true,
                    'lab_results_ready' => true,
                ],
                'privacy_settings' => [
                    'patient_data_retention_days' => 2555, // 7 years
                    'auto_delete_expired_data' => false,
                    'require_consent_for_data_sharing' => true,
                    'allow_patient_portal_access' => true,
                ],
                'integration_settings' => [
                    'lab_integration_enabled' => false,
                    'pharmacy_integration_enabled' => false,
                    'insurance_verification_enabled' => false,
                    'payment_gateway_enabled' => false,
                ],
            ];

            // Merge with saved settings
            $mergedSettings = [];
            foreach ($defaultSettings as $key => $defaultValue) {
                $mergedSettings[$key] = $settings->get($key)?->value ?? $defaultValue;
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'clinic' => $clinic,
                    'settings' => $mergedSettings,
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

            // Update settings
            foreach ($settings as $key => $value) {
                Setting::updateOrCreate(
                    [
                        'key' => $key,
                        'clinic_id' => $clinicId,
                    ],
                    [
                        'value' => is_array($value) ? json_encode($value) : $value,
                        'updated_by' => Auth::id(),
                    ]
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Clinic settings updated successfully',
                'data' => [
                    'clinic' => $clinic->fresh(),
                    'settings' => $settings,
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
}