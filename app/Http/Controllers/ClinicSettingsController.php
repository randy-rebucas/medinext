<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Inertia\Response;

class ClinicSettingsController extends Controller
{
    /**
     * Display the clinic settings page
     */
    public function index(): Response
    {
        $user = Auth::user();

        if (!$user) {
            abort(401, 'User not authenticated');
        }

        $clinic = $this->getCurrentClinic($user);

        if (!$clinic) {
            abort(403, 'No clinic access. Please ensure you have been assigned to a clinic.');
        }

        $settings = $this->getClinicSettings($clinic->id);

        return Inertia::render('admin/clinic-settings', [
            'clinic' => $clinic,
            'settings' => $settings,
        ]);
    }

    /**
     * Get clinic settings via API
     */
    public function getSettings(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }

        $clinic = $this->getCurrentClinic($user);

        if (!$clinic) {
            return response()->json([
                'success' => false,
                'message' => 'No clinic access. Please ensure you have been assigned to a clinic.',
                'debug' => [
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'user_clinic_roles_count' => $user->userClinicRoles()->count(),
                    'user_clinics_count' => $user->clinics()->count(),
                    'total_clinics' => Clinic::count(),
                ]
            ], 403);
        }

        $settings = $this->getClinicSettings($clinic->id);

        return response()->json([
            'success' => true,
            'data' => [
                'clinic' => $clinic,
                'settings' => $settings,
            ]
        ]);
    }

    /**
     * Update clinic settings via API
     */
    public function updateSettings(Request $request)
    {
        $user = Auth::user();
        $clinic = $this->getCurrentClinic($user);

        if (!$clinic) {
            return response()->json([
                'success' => false,
                'message' => 'No clinic access'
            ], 403);
        }

        // Check if user has permission to update clinic settings
        /** @var User $user */
        if (!$user->hasRoleInClinic('admin', $clinic->id) && !$user->hasRoleInClinic('superadmin', $clinic->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient permissions'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'clinic_name' => 'required|string|max:255',
            'clinic_code' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:1000',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'website' => 'nullable|url|max:255',
            'license' => 'nullable|string|max:100',
            'opening_time' => 'nullable|date_format:H:i',
            'closing_time' => 'nullable|date_format:H:i',
            'working_days' => 'nullable|array',
            'working_days.*' => 'string|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'email_notifications' => 'boolean',
            'sms_notifications' => 'boolean',
            'online_booking' => 'boolean',
            'patient_portal' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Update clinic basic information
            $clinic->update([
                'name' => $request->clinic_name,
                'description' => $request->description,
                'phone' => $request->phone,
                'email' => $request->email,
                'website' => $request->website,
            ]);

            // Update clinic address
            if ($request->address) {
                $clinic->update([
                    'address' => [
                        'street' => $request->address,
                        'formatted' => $request->address,
                    ]
                ]);
            }

            // Update settings
            $this->updateClinicSettings($clinic->id, $request->all());

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Clinic settings updated successfully',
                'data' => [
                    'clinic' => $clinic->fresh(),
                    'settings' => $this->getClinicSettings($clinic->id),
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to update clinic settings',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get current clinic for the user
     */
    private function getCurrentClinic($user): ?Clinic
    {
        if (!$user) {
            return null;
        }

        // First try to get from user clinic roles
        $userClinicRole = $user->userClinicRoles()->with('clinic')->first();
        if ($userClinicRole && $userClinicRole->clinic) {
            return $userClinicRole->clinic;
        }

        // If no user clinic roles, try to get from clinics relationship
        $clinic = $user->clinics()->first();
        if ($clinic) {
            return $clinic;
        }

        // If still no clinic, only auto-assign for superadmin users (for development/testing)
        if ($user->hasRole('superadmin')) {
            $firstClinic = Clinic::first();
            if ($firstClinic) {
                // Create a user clinic role for superadmin if none exists
                $superadminRole = \App\Models\Role::where('name', 'superadmin')->first();
                if ($superadminRole) {
                    \App\Models\UserClinicRole::firstOrCreate([
                        'user_id' => $user->id,
                        'clinic_id' => $firstClinic->id,
                        'role_id' => $superadminRole->id,
                    ]);
                }
                return $firstClinic;
            }
        }

        return null;
    }

    /**
     * Get all clinic settings
     */
    private function getClinicSettings(int $clinicId): array
    {
        $settings = Setting::where('clinic_id', $clinicId)->get()->keyBy('key');

        return [
            // Basic Information
            'clinic_name' => $settings->get('clinic.name')?->value ?? '',
            'clinic_code' => $settings->get('clinic.code')?->value ?? '',
            'description' => $settings->get('clinic.description')?->value ?? '',

            // Contact Information
            'phone' => $settings->get('clinic.phone')?->value ?? '',
            'email' => $settings->get('clinic.email')?->value ?? '',
            'address' => $settings->get('clinic.address')?->value ?? '',
            'website' => $settings->get('clinic.website')?->value ?? '',
            'license' => $settings->get('clinic.license')?->value ?? '',

            // Operating Hours
            'opening_time' => $settings->get('working_hours.opening_time')?->value ?? '08:00',
            'closing_time' => $settings->get('working_hours.closing_time')?->value ?? '18:00',
            'working_days' => $settings->get('working_hours.days')?->value ?? ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],

            // System Settings
            'email_notifications' => $settings->get('notifications.email_enabled')?->value ?? true,
            'sms_notifications' => $settings->get('notifications.sms_enabled')?->value ?? true,
            'online_booking' => $settings->get('system.online_booking')?->value ?? true,
            'patient_portal' => $settings->get('system.patient_portal')?->value ?? true,
        ];
    }

    /**
     * Update clinic settings
     */
    private function updateClinicSettings(int $clinicId, array $data): void
    {
        $settingsToUpdate = [
            // Basic Information
            'clinic.name' => $data['clinic_name'] ?? null,
            'clinic.code' => $data['clinic_code'] ?? null,
            'clinic.description' => $data['description'] ?? null,

            // Contact Information
            'clinic.phone' => $data['phone'] ?? null,
            'clinic.email' => $data['email'] ?? null,
            'clinic.address' => $data['address'] ?? null,
            'clinic.website' => $data['website'] ?? null,
            'clinic.license' => $data['license'] ?? null,

            // Operating Hours
            'working_hours.opening_time' => $data['opening_time'] ?? null,
            'working_hours.closing_time' => $data['closing_time'] ?? null,
            'working_hours.days' => $data['working_days'] ?? null,

            // System Settings
            'notifications.email_enabled' => $data['email_notifications'] ?? null,
            'notifications.sms_enabled' => $data['sms_notifications'] ?? null,
            'system.online_booking' => $data['online_booking'] ?? null,
            'system.patient_portal' => $data['patient_portal'] ?? null,
        ];

        foreach ($settingsToUpdate as $key => $value) {
            if ($value !== null) {
                $group = explode('.', $key)[0];
                $type = $this->getSettingType($key, $value);

                Setting::setValue(
                    $key,
                    $value,
                    $clinicId,
                    $type,
                    $group,
                    $this->getSettingDescription($key),
                    false
                );
            }
        }
    }

    /**
     * Get setting type based on key and value
     */
    private function getSettingType(string $key, $value): string
    {
        if (is_bool($value)) {
            return 'boolean';
        }

        if (is_array($value)) {
            return 'array';
        }

        if (is_numeric($value)) {
            return 'integer';
        }

        return 'string';
    }

    /**
     * Get setting description
     */
    private function getSettingDescription(string $key): string
    {
        $descriptions = [
            'clinic.name' => 'The official name of the clinic',
            'clinic.code' => 'Unique clinic identifier code',
            'clinic.description' => 'Brief description of clinic services',
            'clinic.phone' => 'Primary contact phone number',
            'clinic.email' => 'Primary contact email address',
            'clinic.address' => 'Complete clinic address',
            'clinic.website' => 'Clinic website URL',
            'clinic.license' => 'Clinic license number',
            'working_hours.opening_time' => 'Daily opening time',
            'working_hours.closing_time' => 'Daily closing time',
            'working_hours.days' => 'Working days of the week',
            'notifications.email_enabled' => 'Enable email notifications',
            'notifications.sms_enabled' => 'Enable SMS notifications',
            'system.online_booking' => 'Allow online appointment booking',
            'system.patient_portal' => 'Enable patient portal access',
        ];

        return $descriptions[$key] ?? '';
    }
}
