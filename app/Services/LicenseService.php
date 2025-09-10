<?php

namespace App\Services;

use App\Models\License;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class LicenseService
{
    /**
     * Get the current active license
     */
    public function getCurrentLicense(): ?License
    {
        return Cache::remember('current_license', 3600, function () {
            return License::active()
                ->where('expires_at', '>', now())
                ->first();
        });
    }

    /**
     * Get license status from settings
     */
    public function getLicenseStatusFromSettings(): array
    {
        return Cache::remember('license_status_from_settings', 3600, function () {
            $status = Setting::getValue('license.status', 'inactive');
            $type = Setting::getValue('license.type', null);
            $expiresAt = Setting::getValue('license.expires_at', null);
            $features = Setting::getValue('license.features', []);

            return [
                'status' => $status,
                'type' => $type,
                'expires_at' => $expiresAt ? Carbon::parse($expiresAt) : null,
                'features' => $features,
                'has_license' => $status === 'active' && $type !== null,
            ];
        });
    }

    /**
     * Get license usage from settings
     */
    public function getLicenseUsageFromSettings(): array
    {
        return Cache::remember('license_usage_from_settings', 3600, function () {
            return [
                'users' => Setting::getValue('license.usage.users', ['current' => 0, 'limit' => 0, 'percentage' => 0]),
                'clinics' => Setting::getValue('license.usage.clinics', ['current' => 0, 'limit' => 0, 'percentage' => 0]),
                'patients' => Setting::getValue('license.usage.patients', ['current' => 0, 'limit' => 0, 'percentage' => 0]),
                'appointments' => Setting::getValue('license.usage.appointments', ['current' => 0, 'limit' => 0, 'percentage' => 0]),
            ];
        });
    }

    /**
     * Validate license by key
     */
    public function validateLicense(string $licenseKey, $currentUser = null): array
    {
        $license = License::getCached($licenseKey);

        if (!$license) {
            return [
                'valid' => false,
                'message' => 'License key not found. Please check the key and try again.',
                'error_code' => 'LICENSE_NOT_FOUND'
            ];
        }

        if (!$license->isValid()) {
            if ($license->isExpired()) {
                $expiryDate = $license->expires_at->format('F j, Y');
                return [
                    'valid' => false,
                    'message' => "License has expired on {$expiryDate}. Please contact support for renewal.",
                    'error_code' => 'LICENSE_EXPIRED',
                    'expires_at' => $license->expires_at,
                    'grace_period_end' => $license->expires_at->addDays($license->grace_period_days)
                ];
            } else {
                return [
                    'valid' => false,
                    'message' => 'License is not active. Please contact support for assistance.',
                    'error_code' => 'LICENSE_INACTIVE',
                    'expires_at' => $license->expires_at,
                    'grace_period_end' => $license->expires_at->addDays($license->grace_period_days)
                ];
            }
        }

        // Check if license is already assigned to another user
        $existingUser = \App\Models\User::where('license_key', $licenseKey)
            ->where('has_activated_license', true)
            ->when($currentUser, function ($query, $user) {
                return $query->where('id', '!=', $user->id);
            })
            ->first();

        if ($existingUser) {
            $assignedTo = $existingUser->name ?? $existingUser->email;
            return [
                'valid' => false,
                'message' => "This license key is already in use by another user ({$assignedTo}). Each license can only be used by one user at a time.",
                'error_code' => 'LICENSE_ALREADY_IN_USE',
                'assigned_to' => $assignedTo
            ];
        }

        // Update last validation
        $license->validate();

        $expiryDate = $license->expires_at->format('F j, Y');
        $daysRemaining = $license->days_until_expiration;

        return [
            'valid' => true,
            'message' => "License is valid and available! Expires on {$expiryDate} ({$daysRemaining} days remaining).",
            'license' => $license,
            'expires_at' => $license->expires_at,
            'days_until_expiration' => $license->days_until_expiration
        ];
    }

    /**
     * Activate license
     */
    public function activateLicense(string $licenseKey, string $activationCode): array
    {
        $license = License::where('license_key', $licenseKey)->first();

        if (!$license) {
            return [
                'success' => false,
                'message' => 'License not found',
                'error_code' => 'LICENSE_NOT_FOUND'
            ];
        }

        if ($license->activation_code !== $activationCode) {
            return [
                'success' => false,
                'message' => 'Invalid activation code',
                'error_code' => 'INVALID_ACTIVATION_CODE'
            ];
        }

        if ($license->activated_at) {
            return [
                'success' => false,
                'message' => 'License already activated',
                'error_code' => 'ALREADY_ACTIVATED'
            ];
        }

        $serverDomain = request()->getHost();
        $serverIp = request()->ip();

        $activated = $license->activate($serverDomain, $serverIp);

        if ($activated) {
            // Clear current license cache
            Cache::forget('current_license');

            return [
                'success' => true,
                'message' => 'License activated successfully',
                'license' => $license
            ];
        }

        return [
            'success' => false,
            'message' => 'Failed to activate license',
            'error_code' => 'ACTIVATION_FAILED'
        ];
    }

    /**
     * Check if feature is available
     */
    public function hasFeature(string $feature): bool
    {
        // First try to get from settings (faster)
        $settingsStatus = $this->getLicenseStatusFromSettings();

        if ($settingsStatus['has_license'] && isset($settingsStatus['features'])) {
            return in_array($feature, $settingsStatus['features']);
        }

        // Fallback to license model
        $license = $this->getCurrentLicense();

        if (!$license) {
            return false;
        }

        return $license->hasFeature($feature);
    }

    /**
     * Check usage limits
     */
    public function checkUsageLimit(string $type): array
    {
        $license = $this->getCurrentLicense();

        if (!$license) {
            return [
                'allowed' => false,
                'message' => 'No valid license found',
                'error_code' => 'NO_LICENSE'
            ];
        }

        if ($license->isUsageLimitExceeded($type)) {
            return [
                'allowed' => false,
                'message' => "Usage limit exceeded for {$type}",
                'error_code' => 'USAGE_LIMIT_EXCEEDED',
                'current' => $this->getCurrentUsage($type),
                'limit' => $this->getUsageLimit($type)
            ];
        }

        return [
            'allowed' => true,
            'message' => 'Usage within limits',
            'current' => $this->getCurrentUsage($type),
            'limit' => $this->getUsageLimit($type),
            'percentage' => $license->getUsagePercentage($type)
        ];
    }

    /**
     * Get current usage for a type
     */
    public function getCurrentUsage(string $type): int
    {
        // First try to get from settings (faster)
        $usageSettings = $this->getLicenseUsageFromSettings();

        if (isset($usageSettings[$type]['current'])) {
            return $usageSettings[$type]['current'];
        }

        // Fallback to license model
        $license = $this->getCurrentLicense();

        if (!$license) {
            return 0;
        }

        return match ($type) {
            'users' => $license->current_users,
            'clinics' => $license->current_clinics,
            'patients' => $license->current_patients,
            'appointments' => $license->appointments_this_month,
            default => 0,
        };
    }

    /**
     * Get usage limit for a type
     */
    public function getUsageLimit(string $type): int
    {
        // First try to get from settings (faster)
        $usageSettings = $this->getLicenseUsageFromSettings();

        if (isset($usageSettings[$type]['limit'])) {
            return $usageSettings[$type]['limit'];
        }

        // Fallback to license model
        $license = $this->getCurrentLicense();

        if (!$license) {
            return 0;
        }

        return match ($type) {
            'users' => $license->max_users,
            'clinics' => $license->max_clinics,
            'patients' => $license->max_patients,
            'appointments' => $license->max_appointments_per_month,
            default => 0,
        };
    }

    /**
     * Increment usage
     */
    public function incrementUsage(string $type, int $amount = 1): bool
    {
        $license = $this->getCurrentLicense();

        if (!$license) {
            return false;
        }

        $license->incrementUsage($type, $amount);

        // Update settings with new usage
        $this->updateUsageInSettings($type);

        // Clear cache
        Cache::forget('current_license');
        Cache::forget('license_usage_from_settings');

        return true;
    }

    /**
     * Decrement usage
     */
    public function decrementUsage(string $type, int $amount = 1): bool
    {
        $license = $this->getCurrentLicense();

        if (!$license) {
            return false;
        }

        $license->decrementUsage($type, $amount);

        // Update settings with new usage
        $this->updateUsageInSettings($type);

        // Clear cache
        Cache::forget('current_license');
        Cache::forget('license_usage_from_settings');

        return true;
    }

    /**
     * Update usage in settings
     */
    private function updateUsageInSettings(string $type): void
    {
        $license = $this->getCurrentLicense();
        if (!$license) {
            return;
        }

        $current = match ($type) {
            'users' => $license->current_users,
            'clinics' => $license->current_clinics,
            'patients' => $license->current_patients,
            'appointments' => $license->appointments_this_month,
            default => 0,
        };

        $limit = match ($type) {
            'users' => $license->max_users,
            'clinics' => $license->max_clinics,
            'patients' => $license->max_patients,
            'appointments' => $license->max_appointments_per_month,
            default => 0,
        };

        $percentage = $limit > 0 ? ($current / $limit) * 100 : 0;

        Setting::setValue(
            "license.usage.{$type}",
            [
                'current' => $current,
                'limit' => $limit,
                'percentage' => $percentage
            ],
            null,
            'json',
            'system',
            ucfirst($type) . ' usage statistics',
            false
        );
    }

    /**
     * Get license status
     */
    public function getLicenseStatus(): array
    {
        $license = $this->getCurrentLicense();

        if (!$license) {
            return [
                'has_license' => false,
                'status' => 'no_license',
                'message' => 'No license found'
            ];
        }

        $status = 'active';
        $message = 'License is active';

        if ($license->isExpired()) {
            $status = 'expired';
            $message = 'License has expired';
        } elseif ($license->isInGracePeriod()) {
            $status = 'grace_period';
            $message = 'License is in grace period';
        } elseif ($license->status !== 'active') {
            $status = $license->status;
            $message = "License status: {$license->status}";
        }

        return [
            'has_license' => true,
            'status' => $status,
            'message' => $message,
            'license' => $license,
            'expires_at' => $license->expires_at,
            'days_until_expiration' => $license->days_until_expiration,
            'is_in_grace_period' => $license->isInGracePeriod(),
            'days_in_grace_period' => $license->days_in_grace_period
        ];
    }

    /**
     * Get license information for display
     */
    public function getLicenseInfo(): array
    {
        // First try to get from settings (faster)
        $settingsStatus = $this->getLicenseStatusFromSettings();
        $usageSettings = $this->getLicenseUsageFromSettings();

        if ($settingsStatus['has_license']) {
            return [
                'has_license' => true,
                'license_type' => $settingsStatus['type'],
                'customer_name' => $this->getCurrentLicense()?->customer_name,
                'expires_at' => $settingsStatus['expires_at'],
                'days_until_expiration' => $settingsStatus['expires_at'] ? now()->diffInDays($settingsStatus['expires_at'], false) : 0,
                'features' => $settingsStatus['features'] ?? [],
                'usage' => $usageSettings
            ];
        }

        // Fallback to license model
        $license = $this->getCurrentLicense();

        if (!$license) {
            return [
                'has_license' => false,
                'license_type' => null,
                'customer_name' => null,
                'expires_at' => null,
                'features' => [],
                'usage' => []
            ];
        }

        return [
            'has_license' => true,
            'license_type' => $license->license_type,
            'customer_name' => $license->customer_name,
            'expires_at' => $license->expires_at,
            'days_until_expiration' => $license->days_until_expiration,
            'features' => $license->features ?? [],
            'usage' => [
                'users' => [
                    'current' => $license->current_users,
                    'limit' => $license->max_users,
                    'percentage' => $license->getUsagePercentage('users')
                ],
                'clinics' => [
                    'current' => $license->current_clinics,
                    'limit' => $license->max_clinics,
                    'percentage' => $license->getUsagePercentage('clinics')
                ],
                'patients' => [
                    'current' => $license->current_patients,
                    'limit' => $license->max_patients,
                    'percentage' => $license->getUsagePercentage('patients')
                ],
                'appointments' => [
                    'current' => $license->appointments_this_month,
                    'limit' => $license->max_appointments_per_month,
                    'percentage' => $license->getUsagePercentage('appointments')
                ]
            ]
        ];
    }

    /**
     * Sync license settings with current license
     */
    public function syncLicenseSettings(): void
    {
        $license = $this->getCurrentLicense();

        if (!$license) {
            // Clear license settings if no license
            Setting::setValue('license.status', 'inactive', null, 'string', 'system', 'Current license status', false);
            Setting::setValue('license.type', null, null, 'string', 'system', 'Current license type', false);
            Setting::setValue('license.expires_at', null, null, 'string', 'system', 'License expiration date', false);
            Setting::setValue('license.features', [], null, 'json', 'system', 'Available license features', false);

            // Clear usage settings
            foreach (['users', 'clinics', 'patients', 'appointments'] as $type) {
                Setting::setValue(
                    "license.usage.{$type}",
                    ['current' => 0, 'limit' => 0, 'percentage' => 0],
                    null,
                    'json',
                    'system',
                    ucfirst($type) . ' usage statistics',
                    false
                );
            }

            // Clear cache
            Cache::forget('license_status_from_settings');
            Cache::forget('license_usage_from_settings');
            return;
        }

        // Update license settings
        Setting::setValue('license.status', $license->status, null, 'string', 'system', 'Current license status', false);
        Setting::setValue('license.type', $license->license_type, null, 'string', 'system', 'Current license type', false);
        Setting::setValue('license.expires_at', $license->expires_at->toDateString(), null, 'string', 'system', 'License expiration date', false);
        Setting::setValue('license.features', $license->features ?? [], null, 'json', 'system', 'Available license features', false);

        // Update usage settings
        foreach (['users', 'clinics', 'patients', 'appointments'] as $type) {
            $this->updateUsageInSettings($type);
        }

        // Clear cache
        Cache::forget('license_status_from_settings');
        Cache::forget('license_usage_from_settings');
    }

    /**
     * Reset monthly usage counters
     */
    public function resetMonthlyUsage(): bool
    {
        $license = $this->getCurrentLicense();

        if (!$license) {
            return false;
        }

        $license->resetMonthlyUsage();

        // Clear cache
        Cache::forget('current_license');

        return true;
    }

    /**
     * Get expiring licenses
     */
    public function getExpiringLicenses(int $days = 30): \Illuminate\Database\Eloquent\Collection
    {
        return License::getExpiringLicenses($days);
    }

    /**
     * Get license statistics
     */
    public function getLicenseStatistics(): array
    {
        $totalLicenses = License::count();
        $activeLicenses = License::active()->count();
        $expiredLicenses = License::expired()->count();
        $expiringSoon = License::active()->expiringSoon(30)->count();

        $revenue = License::whereNotNull('monthly_fee')
            ->where('status', 'active')
            ->sum('monthly_fee');

        return [
            'total_licenses' => $totalLicenses,
            'active_licenses' => $activeLicenses,
            'expired_licenses' => $expiredLicenses,
            'expiring_soon' => $expiringSoon,
            'monthly_revenue' => $revenue,
            'license_types' => [
                'standard' => License::ofType('standard')->count(),
                'premium' => License::ofType('premium')->count(),
                'enterprise' => License::ofType('enterprise')->count(),
            ]
        ];
    }

    /**
     * Create a new license
     */
    public function createLicense(array $data): License
    {
        $license = License::create($data);

        // Clear cache
        Cache::forget('active_licenses');

        return $license;
    }

    /**
     * Update license
     */
    public function updateLicense(License $license, array $data): License
    {
        $license->update($data);

        // Clear cache
        Cache::forget('current_license');
        Cache::forget('active_licenses');

        return $license;
    }

    /**
     * Suspend license
     */
    public function suspendLicense(License $license, string $reason = null): bool
    {
        $license->suspend($reason);

        // Clear cache
        Cache::forget('current_license');
        Cache::forget('active_licenses');

        return true;
    }

    /**
     * Revoke license
     */
    public function revokeLicense(License $license, string $reason = null): bool
    {
        $license->revoke($reason);

        // Clear cache
        Cache::forget('current_license');
        Cache::forget('active_licenses');

        return true;
    }

    /**
     * Renew license
     */
    public function renewLicense(License $license, int $months = 12): bool
    {
        $license->renew($months);

        // Clear cache
        Cache::forget('current_license');
        Cache::forget('active_licenses');

        return true;
    }

    /**
     * Activate license for a specific user
     */
    public function activateLicenseForUser($user, string $licenseKey): array
    {
        // First validate the license key (including usage check)
        $validation = $this->validateLicense($licenseKey, $user);

        if (!$validation['valid']) {
            return [
                'success' => false,
                'message' => $validation['message'],
                'error_code' => $validation['error_code'] ?? 'VALIDATION_FAILED'
            ];
        }

        // Activate the license for the user
        $user->activateLicense($licenseKey);

        return [
            'success' => true,
            'message' => 'License activated successfully',
            'license' => $validation['license']
        ];
    }

    /**
     * Check if application should be restricted
     */
    public function shouldRestrictApplication(): bool
    {
        // Check if user is authenticated and has valid access (trial or license)
        if (Auth::check()) {
            $user = Auth::user();
            if ($user instanceof \App\Models\User && $user->hasValidAccess()) {
                return false; // User has valid access
            }
        }

        // Check for system-wide license
        $license = $this->getCurrentLicense();

        if (!$license) {
            return true; // No license means restricted
        }

        if (!$license->isValid()) {
            return true; // Invalid license means restricted
        }

        return false;
    }

    /**
     * Get restriction message
     */
    public function getRestrictionMessage(): string
    {
        // Check if user is authenticated and has trial/license status
        if (Auth::check()) {
            $user = Auth::user();

            if ($user instanceof \App\Models\User && $user->isTrialExpired()) {
                return 'Your free trial has expired. Please activate a license to continue using the application.';
            }

            if ($user->has_activated_license) {
                $license = $user->license;
                if ($license && $license->isExpired()) {
                    return 'Your license has expired. Please renew your license to continue using the application.';
                }
                if ($license && $license->status === 'suspended') {
                    return 'Your license has been suspended. Please contact support for assistance.';
                }
                if ($license && $license->status === 'revoked') {
                    return 'Your license has been revoked. Please contact support for assistance.';
                }
            }
        }

        // Check for system-wide license
        $license = $this->getCurrentLicense();

        if (!$license) {
            return 'No valid license found. Please contact support.';
        }

        if ($license->isExpired()) {
            return 'Your license has expired. Please renew your license to continue using the application.';
        }

        if ($license->status === 'suspended') {
            return 'Your license has been suspended. Please contact support for assistance.';
        }

        if ($license->status === 'revoked') {
            return 'Your license has been revoked. Please contact support for assistance.';
        }

        return 'License validation failed. Please contact support.';
    }
}
