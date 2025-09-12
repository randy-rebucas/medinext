<?php

namespace App\Traits;

use App\Services\SettingsService;
use Illuminate\Support\Facades\Auth;

trait HasSettings
{
    /**
     * Get a setting value for the current clinic
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    protected function getSetting(string $key, $default = null)
    {
        $settingsService = app(SettingsService::class);
        $clinicId = $this->getCurrentClinicId();

        return $settingsService->get($key, $default, $clinicId);
    }

    /**
     * Set a setting value for the current clinic
     *
     * @param string $key
     * @param mixed $value
     * @param string $type
     * @param string $group
     * @param string $description
     * @param bool $isPublic
     * @return \App\Models\Setting
     */
    protected function setSetting(string $key, $value, string $type = 'string', string $group = 'general', string $description = '', bool $isPublic = false)
    {
        $settingsService = app(SettingsService::class);
        $clinicId = $this->getCurrentClinicId();

        return $settingsService->set($key, $value, $clinicId, $type, $group, $description, $isPublic);
    }

    /**
     * Get all settings for the current clinic
     *
     * @return array
     */
    protected function getAllSettings(): array
    {
        $settingsService = app(SettingsService::class);
        $clinicId = $this->getCurrentClinicId();

        return $settingsService->getAllForClinic($clinicId);
    }

    /**
     * Get settings by group for the current clinic
     *
     * @param string $group
     * @return array
     */
    protected function getSettingsByGroup(string $group): array
    {
        $settingsService = app(SettingsService::class);
        $clinicId = $this->getCurrentClinicId();

        return $settingsService->getGroup($group, $clinicId);
    }

    /**
     * Check if a setting exists for the current clinic
     *
     * @param string $key
     * @return bool
     */
    protected function hasSetting(string $key): bool
    {
        $settingsService = app(SettingsService::class);
        $clinicId = $this->getCurrentClinicId();

        return $settingsService->has($key, $clinicId);
    }

    /**
     * Get the current clinic ID
     *
     * @return int|null
     */
    protected function getCurrentClinicId(): ?int
    {
        if (Auth::check()) {
            return Auth::user()->current_clinic_id;
        }

        return null;
    }

    /**
     * Get clinic information settings
     *
     * @return array
     */
    protected function getClinicInfo(): array
    {
        $settingsService = app(SettingsService::class);
        $clinicId = $this->getCurrentClinicId();

        return $settingsService->getClinicInfo($clinicId);
    }

    /**
     * Get working hours settings
     *
     * @return array
     */
    protected function getWorkingHours(): array
    {
        $settingsService = app(SettingsService::class);
        $clinicId = $this->getCurrentClinicId();

        return $settingsService->getWorkingHours($clinicId);
    }

    /**
     * Get notification settings
     *
     * @return array
     */
    protected function getNotificationSettings(): array
    {
        $settingsService = app(SettingsService::class);
        $clinicId = $this->getCurrentClinicId();

        return $settingsService->getNotificationSettings($clinicId);
    }

    /**
     * Get branding settings
     *
     * @return array
     */
    protected function getBrandingSettings(): array
    {
        $settingsService = app(SettingsService::class);
        $clinicId = $this->getCurrentClinicId();

        return $settingsService->getBrandingSettings($clinicId);
    }

    /**
     * Get system settings
     *
     * @return array
     */
    protected function getSystemSettings(): array
    {
        $settingsService = app(SettingsService::class);
        $clinicId = $this->getCurrentClinicId();

        return $settingsService->getSystemSettings($clinicId);
    }

    /**
     * Check if clinic is currently open
     *
     * @return bool
     */
    protected function isClinicOpen(): bool
    {
        $settingsService = app(SettingsService::class);
        $clinicId = $this->getCurrentClinicId();

        return $settingsService->isClinicOpen($clinicId);
    }

    /**
     * Get formatted working hours
     *
     * @return array
     */
    protected function getFormattedWorkingHours(): array
    {
        $settingsService = app(SettingsService::class);
        $clinicId = $this->getCurrentClinicId();

        return $settingsService->getFormattedWorkingHours($clinicId);
    }

    /**
     * Get appointment settings
     *
     * @return array
     */
    protected function getAppointmentSettings(): array
    {
        $settingsService = app(SettingsService::class);
        $clinicId = $this->getCurrentClinicId();

        return $settingsService->getAppointmentSettings($clinicId);
    }

    /**
     * Get prescription settings
     *
     * @return array
     */
    protected function getPrescriptionSettings(): array
    {
        $settingsService = app(SettingsService::class);
        $clinicId = $this->getCurrentClinicId();

        return $settingsService->getPrescriptionSettings($clinicId);
    }

    /**
     * Get billing settings
     *
     * @return array
     */
    protected function getBillingSettings(): array
    {
        $settingsService = app(SettingsService::class);
        $clinicId = $this->getCurrentClinicId();

        return $settingsService->getBillingSettings($clinicId);
    }

    /**
     * Get security settings
     *
     * @return array
     */
    protected function getSecuritySettings(): array
    {
        $settingsService = app(SettingsService::class);
        $clinicId = $this->getCurrentClinicId();

        return $settingsService->getSecuritySettings($clinicId);
    }

    /**
     * Get integration settings
     *
     * @return array
     */
    protected function getIntegrationSettings(): array
    {
        $settingsService = app(SettingsService::class);
        $clinicId = $this->getCurrentClinicId();

        return $settingsService->getIntegrationSettings($clinicId);
    }

    /**
     * Get queue settings
     *
     * @return array
     */
    protected function getQueueSettings(): array
    {
        $settingsService = app(SettingsService::class);
        $clinicId = $this->getCurrentClinicId();

        return $settingsService->getQueueSettings($clinicId);
    }

    /**
     * Get EMR settings
     *
     * @return array
     */
    protected function getEMRSettings(): array
    {
        $settingsService = app(SettingsService::class);
        $clinicId = $this->getCurrentClinicId();

        return $settingsService->getEMRSettings($clinicId);
    }

    /**
     * Get file settings
     *
     * @return array
     */
    protected function getFileSettings(): array
    {
        $settingsService = app(SettingsService::class);
        $clinicId = $this->getCurrentClinicId();

        return $settingsService->getFileSettings($clinicId);
    }

    /**
     * Get reporting settings
     *
     * @return array
     */
    protected function getReportingSettings(): array
    {
        $settingsService = app(SettingsService::class);
        $clinicId = $this->getCurrentClinicId();

        return $settingsService->getReportingSettings($clinicId);
    }
}
