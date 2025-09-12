<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\Clinic;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SettingsService
{
    /**
     * Get a setting value with fallback to global default
     *
     * @param string $key
     * @param mixed $default
     * @param int|null $clinicId
     * @return mixed
     */
    public function get(string $key, $default = null, ?int $clinicId = null)
    {
        try {
            // First try clinic-specific setting
            if ($clinicId) {
                $value = Setting::getValue($key, null, $clinicId);
                if ($value !== null) {
                    return $value;
                }
            }

            // Fallback to global setting
            return Setting::getValue($key, $default, null);
        } catch (\Exception $e) {
            Log::error("Error getting setting {$key}: " . $e->getMessage());
            return $default;
        }
    }

    /**
     * Set a setting value
     *
     * @param string $key
     * @param mixed $value
     * @param int|null $clinicId
     * @param string $type
     * @param string $group
     * @param string $description
     * @param bool $isPublic
     * @return Setting
     */
    public function set(string $key, $value, ?int $clinicId = null, string $type = 'string', string $group = 'general', string $description = '', bool $isPublic = false): Setting
    {
        return Setting::setValue($key, $value, $clinicId, $type, $group, $description, $isPublic);
    }

    /**
     * Get all settings for a clinic with global fallbacks
     *
     * @param int|null $clinicId
     * @return array
     */
    public function getAllForClinic(?int $clinicId = null): array
    {
        $cacheKey = "settings_service_all_" . ($clinicId ?? 'global');

        return Cache::remember($cacheKey, 3600, function () use ($clinicId) {
            $settings = [];

            // Get all global settings first
            $globalSettings = Setting::whereNull('clinic_id')->get()->keyBy('key');

            // Get clinic-specific settings
            $clinicSettings = $clinicId ? Setting::where('clinic_id', $clinicId)->get()->keyBy('key') : collect();

            // Merge settings (clinic-specific override global)
            foreach ($globalSettings as $key => $setting) {
                $settings[$key] = $setting->value;
            }

            foreach ($clinicSettings as $key => $setting) {
                $settings[$key] = $setting->value;
            }

            return $settings;
        });
    }

    /**
     * Get settings by group
     *
     * @param string $group
     * @param int|null $clinicId
     * @return array
     */
    public function getGroup(string $group, ?int $clinicId = null): array
    {
        return Setting::getGroup($group, $clinicId);
    }

    /**
     * Get clinic information settings
     *
     * @param int|null $clinicId
     * @return array
     */
    public function getClinicInfo(?int $clinicId = null): array
    {
        return $this->getGroup('clinic', $clinicId);
    }

    /**
     * Get working hours settings
     *
     * @param int|null $clinicId
     * @return array
     */
    public function getWorkingHours(?int $clinicId = null): array
    {
        return $this->getGroup('working_hours', $clinicId);
    }

    /**
     * Get notification settings
     *
     * @param int|null $clinicId
     * @return array
     */
    public function getNotificationSettings(?int $clinicId = null): array
    {
        return $this->getGroup('notifications', $clinicId);
    }

    /**
     * Get branding settings
     *
     * @param int|null $clinicId
     * @return array
     */
    public function getBrandingSettings(?int $clinicId = null): array
    {
        return $this->getGroup('branding', $clinicId);
    }

    /**
     * Get system settings
     *
     * @param int|null $clinicId
     * @return array
     */
    public function getSystemSettings(?int $clinicId = null): array
    {
        return $this->getGroup('system', $clinicId);
    }

    /**
     * Get appointment settings
     *
     * @param int|null $clinicId
     * @return array
     */
    public function getAppointmentSettings(?int $clinicId = null): array
    {
        return $this->getGroup('appointments', $clinicId);
    }

    /**
     * Get prescription settings
     *
     * @param int|null $clinicId
     * @return array
     */
    public function getPrescriptionSettings(?int $clinicId = null): array
    {
        return $this->getGroup('prescriptions', $clinicId);
    }

    /**
     * Get billing settings
     *
     * @param int|null $clinicId
     * @return array
     */
    public function getBillingSettings(?int $clinicId = null): array
    {
        return $this->getGroup('billing', $clinicId);
    }

    /**
     * Get security settings
     *
     * @param int|null $clinicId
     * @return array
     */
    public function getSecuritySettings(?int $clinicId = null): array
    {
        return $this->getGroup('security', $clinicId);
    }

    /**
     * Get integration settings
     *
     * @param int|null $clinicId
     * @return array
     */
    public function getIntegrationSettings(?int $clinicId = null): array
    {
        return $this->getGroup('integrations', $clinicId);
    }

    /**
     * Get queue management settings
     *
     * @param int|null $clinicId
     * @return array
     */
    public function getQueueSettings(?int $clinicId = null): array
    {
        return $this->getGroup('queue', $clinicId);
    }

    /**
     * Get EMR settings
     *
     * @param int|null $clinicId
     * @return array
     */
    public function getEMRSettings(?int $clinicId = null): array
    {
        return $this->getGroup('emr', $clinicId);
    }

    /**
     * Get file management settings
     *
     * @param int|null $clinicId
     * @return array
     */
    public function getFileSettings(?int $clinicId = null): array
    {
        return $this->getGroup('files', $clinicId);
    }

    /**
     * Get reporting settings
     *
     * @param int|null $clinicId
     * @return array
     */
    public function getReportingSettings(?int $clinicId = null): array
    {
        return $this->getGroup('reports', $clinicId);
    }

    /**
     * Check if a setting exists
     *
     * @param string $key
     * @param int|null $clinicId
     * @return bool
     */
    public function has(string $key, ?int $clinicId = null): bool
    {
        return $this->get($key, null, $clinicId) !== null;
    }

    /**
     * Delete a setting
     *
     * @param string $key
     * @param int|null $clinicId
     * @return bool
     */
    public function delete(string $key, ?int $clinicId = null): bool
    {
        try {
            $setting = Setting::where('key', $key)
                ->when($clinicId, fn($query) => $query->where('clinic_id', $clinicId))
                ->first();

            if ($setting) {
                $setting->delete();
                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error("Error deleting setting {$key}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Clear all settings cache
     *
     * @param int|null $clinicId
     * @return void
     */
    public function clearCache(?int $clinicId = null): void
    {
        $cacheKeys = [
            "settings_service_all_" . ($clinicId ?? 'global'),
            "settings_all_" . ($clinicId ?? 'global'),
        ];

        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }

        // Clear individual setting caches
        $settings = Setting::when($clinicId, fn($query) => $query->where('clinic_id', $clinicId))->get();
        foreach ($settings as $setting) {
            $setting->clearCache();
        }
    }

    /**
     * Initialize default settings for a clinic
     *
     * @param int $clinicId
     * @return void
     */
    public function initializeClinicSettings(int $clinicId): void
    {
        $defaultSettings = [
            // Clinic Information
            'clinic.name' => 'New Clinic',
            'clinic.phone' => '+63 123 456 7890',
            'clinic.email' => 'info@newclinic.com',
            'clinic.address' => [
                'street' => '123 Main Street',
                'city' => 'Manila',
                'state' => 'Metro Manila',
                'postal_code' => '1000',
                'country' => 'Philippines'
            ],
            'clinic.website' => 'https://newclinic.com',
            'clinic.description' => 'Professional healthcare services',

            // Working Hours
            'working_hours.monday' => ['start' => '08:00', 'end' => '17:00', 'closed' => false],
            'working_hours.tuesday' => ['start' => '08:00', 'end' => '17:00', 'closed' => false],
            'working_hours.wednesday' => ['start' => '08:00', 'end' => '17:00', 'closed' => false],
            'working_hours.thursday' => ['start' => '08:00', 'end' => '17:00', 'closed' => false],
            'working_hours.friday' => ['start' => '08:00', 'end' => '17:00', 'closed' => false],
            'working_hours.saturday' => ['start' => '08:00', 'end' => '12:00', 'closed' => false],
            'working_hours.sunday' => ['start' => '00:00', 'end' => '00:00', 'closed' => true],

            // Notifications
            'notifications.email_enabled' => true,
            'notifications.sms_enabled' => false,
            'notifications.appointment_reminder_hours' => 24,
            'notifications.follow_up_days' => 7,

            // Branding
            'branding.primary_color' => '#3B82F6',
            'branding.secondary_color' => '#1E40AF',
            'branding.logo_url' => null,
            'branding.favicon_url' => null,
        ];

        foreach ($defaultSettings as $key => $value) {
            $group = explode('.', $key)[0];
            $type = is_array($value) ? 'json' : (is_bool($value) ? 'boolean' : (is_int($value) ? 'integer' : 'string'));

            $this->set($key, $value, $clinicId, $type, $group, "Default {$group} setting", true);
        }
    }

    /**
     * Get formatted working hours for display
     *
     * @param int|null $clinicId
     * @return array
     */
    public function getFormattedWorkingHours(?int $clinicId = null): array
    {
        $workingHours = $this->getWorkingHours($clinicId);
        $formatted = [];

        $days = [
            'monday' => 'Monday',
            'tuesday' => 'Tuesday',
            'wednesday' => 'Wednesday',
            'thursday' => 'Thursday',
            'friday' => 'Friday',
            'saturday' => 'Saturday',
            'sunday' => 'Sunday',
        ];

        foreach ($days as $key => $day) {
            $hours = $workingHours["working_hours.{$key}"] ?? null;
            if ($hours && is_array($hours)) {
                if ($hours['closed']) {
                    $formatted[$day] = 'Closed';
                } else {
                    $formatted[$day] = $hours['start'] . ' - ' . $hours['end'];
                }
            } else {
                $formatted[$day] = 'Not Set';
            }
        }

        return $formatted;
    }

    /**
     * Check if clinic is currently open
     *
     * @param int|null $clinicId
     * @return bool
     */
    public function isClinicOpen(?int $clinicId = null): bool
    {
        $now = now();
        $dayOfWeek = strtolower($now->format('l')); // monday, tuesday, etc.

        $workingHours = $this->get("working_hours.{$dayOfWeek}", null, $clinicId);

        if (!$workingHours || !is_array($workingHours) || $workingHours['closed']) {
            return false;
        }

        $currentTime = $now->format('H:i');
        return $currentTime >= $workingHours['start'] && $currentTime <= $workingHours['end'];
    }
}
