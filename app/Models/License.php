<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Services\LicenseKeyGenerator;

class License extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'license_key',
        'license_type',
        'status',
        'name',
        'description',
        'customer_name',
        'customer_email',
        'customer_company',
        'customer_phone',
        'max_users',
        'max_clinics',
        'max_patients',
        'max_appointments_per_month',
        'features',
        'starts_at',
        'expires_at',
        'grace_period_days',
        'server_domain',
        'server_ip',
        'server_fingerprint',
        'auto_renew',
        'monthly_fee',
        'billing_cycle',
        'last_payment_date',
        'next_payment_date',
        'current_users',
        'current_clinics',
        'current_patients',
        'appointments_this_month',
        'last_usage_reset',
        'activation_code',
        'activated_at',
        'last_validated_at',
        'validation_attempts',
        'last_validation_attempt',
        'support_level',
        'support_notes',
        'assigned_support_agent',
        'audit_log',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'features' => 'array',
        'audit_log' => 'array',
        'starts_at' => 'date',
        'expires_at' => 'date',
        'last_payment_date' => 'date',
        'next_payment_date' => 'date',
        'last_usage_reset' => 'date',
        'activated_at' => 'datetime',
        'last_validated_at' => 'datetime',
        'last_validation_attempt' => 'datetime',
        'auto_renew' => 'boolean',
        'monthly_fee' => 'decimal:2',
        'max_users' => 'integer',
        'max_clinics' => 'integer',
        'max_patients' => 'integer',
        'max_appointments_per_month' => 'integer',
        'current_users' => 'integer',
        'current_clinics' => 'integer',
        'current_patients' => 'integer',
        'appointments_this_month' => 'integer',
        'grace_period_days' => 'integer',
        'validation_attempts' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($license) {
            if (empty($license->uuid)) {
                $license->uuid = Str::uuid();
            }
            if (empty($license->license_key)) {
                $license->license_key = static::generateLicenseKey();
            }
            if (empty($license->activation_code)) {
                $license->activation_code = static::generateActivationCode();
            }
        });

        static::saved(function ($license) {
            $license->clearCache();
        });

        static::deleted(function ($license) {
            $license->clearCache();
        });
    }

    /**
     * Generate a unique license key using the enhanced generator
     */
    public static function generateLicenseKey(string $strategy = LicenseKeyGenerator::STRATEGY_STANDARD, array $options = []): string
    {
        return LicenseKeyGenerator::generate($strategy, $options);
    }

    /**
     * Generate an activation code using the enhanced generator
     */
    public static function generateActivationCode(int $length = 8): string
    {
        return LicenseKeyGenerator::generateActivationCode($length);
    }

    /**
     * Scope for active licenses
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for expired licenses
     */
    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('expires_at', '<', now());
    }

    /**
     * Scope for expiring soon licenses
     */
    public function scopeExpiringSoon(Builder $query, int $days = 30): Builder
    {
        return $query->whereBetween('expires_at', [now(), now()->addDays($days)]);
    }

    /**
     * Scope for license type
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('license_type', $type);
    }

    /**
     * Check if license is valid
     */
    public function isValid(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        $now = now();
        $expirationDate = $this->expires_at;
        $gracePeriodEnd = $expirationDate->addDays($this->grace_period_days);

        return $now->lte($gracePeriodEnd);
    }

    /**
     * Check if license is expired
     */
    public function isExpired(): bool
    {
        $now = now();
        $expirationDate = $this->expires_at;
        $gracePeriodEnd = $expirationDate->addDays($this->grace_period_days);

        return $now->gt($gracePeriodEnd);
    }

    /**
     * Check if license is in grace period
     */
    public function isInGracePeriod(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        $now = now();
        $expirationDate = $this->expires_at;
        $gracePeriodEnd = $expirationDate->addDays($this->grace_period_days);

        return $now->gt($expirationDate) && $now->lte($gracePeriodEnd);
    }

    /**
     * Get days until expiration
     */
    public function getDaysUntilExpirationAttribute(): int
    {
        if (!$this->expires_at) {
            return 0;
        }

        $days = now()->diffInDays($this->expires_at, false);
        return is_numeric($days) ? (int) $days : 0;
    }

    /**
     * Get days in grace period
     */
    public function getDaysInGracePeriodAttribute(): int
    {
        if (!$this->isInGracePeriod()) {
            return 0;
        }

        $gracePeriodEnd = $this->expires_at->addDays($this->grace_period_days);
        return now()->diffInDays($gracePeriodEnd, false);
    }

    /**
     * Check if feature is enabled
     */
    public function hasFeature(string $feature): bool
    {
        $features = $this->features ?? [];
        return in_array($feature, $features);
    }

    /**
     * Enable a feature
     */
    public function enableFeature(string $feature): void
    {
        $features = $this->features ?? [];
        if (!in_array($feature, $features)) {
            $features[] = $feature;
            $this->features = $features;
            $this->save();
        }
    }

    /**
     * Disable a feature
     */
    public function disableFeature(string $feature): void
    {
        $features = $this->features ?? [];
        $features = array_filter($features, fn($f) => $f !== $feature);
        $this->features = array_values($features);
        $this->save();
    }

    /**
     * Check if usage limit is exceeded
     */
    public function isUsageLimitExceeded(string $type): bool
    {
        switch ($type) {
            case 'users':
                return $this->current_users >= $this->max_users;
            case 'clinics':
                return $this->current_clinics >= $this->max_clinics;
            case 'patients':
                return $this->current_patients >= $this->max_patients;
            case 'appointments':
                return $this->appointments_this_month >= $this->max_appointments_per_month;
            default:
                return false;
        }
    }

    /**
     * Get usage percentage
     */
    public function getUsagePercentage(string $type): float
    {
        switch ($type) {
            case 'users':
                $current = $this->current_users ?? 0;
                $max = $this->max_users ?? 0;
                return $max > 0 ? ($current / $max) * 100 : 0;
            case 'clinics':
                $current = $this->current_clinics ?? 0;
                $max = $this->max_clinics ?? 0;
                return $max > 0 ? ($current / $max) * 100 : 0;
            case 'patients':
                $current = $this->current_patients ?? 0;
                $max = $this->max_patients ?? 0;
                return $max > 0 ? ($current / $max) * 100 : 0;
            case 'appointments':
                $current = $this->appointments_this_month ?? 0;
                $max = $this->max_appointments_per_month ?? 0;
                return $max > 0 ? ($current / $max) * 100 : 0;
            default:
                return 0;
        }
    }

    /**
     * Increment usage counter
     */
    public function incrementUsage(string $type, int $amount = 1): void
    {
        switch ($type) {
            case 'users':
                $this->increment('current_users', $amount);
                break;
            case 'clinics':
                $this->increment('current_clinics', $amount);
                break;
            case 'patients':
                $this->increment('current_patients', $amount);
                break;
            case 'appointments':
                $this->increment('appointments_this_month', $amount);
                break;
        }
    }

    /**
     * Decrement usage counter
     */
    public function decrementUsage(string $type, int $amount = 1): void
    {
        switch ($type) {
            case 'users':
                $this->decrement('current_users', $amount);
                break;
            case 'clinics':
                $this->decrement('current_clinics', $amount);
                break;
            case 'patients':
                $this->decrement('current_patients', $amount);
                break;
            case 'appointments':
                $this->decrement('appointments_this_month', $amount);
                break;
        }
    }

    /**
     * Reset monthly usage counters
     */
    public function resetMonthlyUsage(): void
    {
        $this->update([
            'appointments_this_month' => 0,
            'last_usage_reset' => now(),
        ]);
    }

    /**
     * Activate license
     */
    public function activate(string $serverDomain = null, string $serverIp = null): bool
    {
        if ($this->activated_at) {
            return false; // Already activated
        }

        $this->update([
            'activated_at' => now(),
            'server_domain' => $serverDomain,
            'server_ip' => $serverIp,
            'server_fingerprint' => $this->generateServerFingerprint(),
            'last_validated_at' => now(),
        ]);

        $this->addAuditLog('License activated', [
            'server_domain' => $serverDomain,
            'server_ip' => $serverIp,
        ]);

        return true;
    }

    /**
     * Validate license
     */
    public function validate(): bool
    {
        $this->increment('validation_attempts');
        $this->update(['last_validation_attempt' => now()]);

        if (!$this->isValid()) {
            $this->addAuditLog('License validation failed', [
                'reason' => $this->isExpired() ? 'expired' : 'inactive',
                'expires_at' => $this->expires_at,
            ]);
            return false;
        }

        $this->update(['last_validated_at' => now()]);
        return true;
    }

    /**
     * Suspend license
     */
    public function suspend(string $reason = null): void
    {
        $this->update(['status' => 'suspended']);
        $this->addAuditLog('License suspended', ['reason' => $reason]);
    }

    /**
     * Revoke license
     */
    public function revoke(string $reason = null): void
    {
        $this->update(['status' => 'revoked']);
        $this->addAuditLog('License revoked', ['reason' => $reason]);
    }

    /**
     * Renew license
     */
    public function renew(int $months = 12): void
    {
        $newExpirationDate = $this->expires_at->addMonths($months);
        $this->update(['expires_at' => $newExpirationDate]);
        $this->addAuditLog('License renewed', [
            'months' => $months,
            'new_expires_at' => $newExpirationDate,
        ]);
    }

    /**
     * Generate server fingerprint using the enhanced generator
     */
    public function generateServerFingerprint(array $serverInfo = []): string
    {
        return LicenseKeyGenerator::generateServerFingerprint($serverInfo);
    }

    /**
     * Add audit log entry
     */
    public function addAuditLog(string $action, array $data = []): void
    {
        $auditLog = $this->audit_log ?? [];
        $auditLog[] = [
            'action' => $action,
            'data' => $data,
            'timestamp' => now()->toISOString(),
            'user' => 'system',
        ];

        $this->update(['audit_log' => $auditLog]);
    }

    /**
     * Get license status badge color
     */
    public function getStatusBadgeColorAttribute(): string
    {
        return match ($this->status) {
            'active' => 'success',
            'expired' => 'danger',
            'suspended' => 'warning',
            'revoked' => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Get license type badge color
     */
    public function getTypeBadgeColorAttribute(): string
    {
        return match ($this->license_type) {
            'standard' => 'info',
            'premium' => 'warning',
            'enterprise' => 'success',
            default => 'secondary',
        };
    }

    /**
     * Get available features for license type
     */
    public static function getAvailableFeatures(string $type): array
    {
        $features = [
            'standard' => [
                'basic_appointments',
                'patient_management',
                'prescription_management',
                'basic_reporting',
            ],
            'premium' => [
                'basic_appointments',
                'patient_management',
                'prescription_management',
                'basic_reporting',
                'advanced_reporting',
                'lab_results',
                'medrep_management',
                'multi_clinic',
                'email_notifications',
            ],
            'enterprise' => [
                'basic_appointments',
                'patient_management',
                'prescription_management',
                'basic_reporting',
                'advanced_reporting',
                'lab_results',
                'medrep_management',
                'multi_clinic',
                'email_notifications',
                'sms_notifications',
                'api_access',
                'custom_branding',
                'priority_support',
                'advanced_analytics',
                'backup_restore',
            ],
        ];

        return $features[$type] ?? [];
    }

    /**
     * Clear cache
     */
    public function clearCache(): void
    {
        Cache::forget("license_{$this->license_key}");
        Cache::forget("license_{$this->uuid}");
        Cache::forget('active_licenses');
        Cache::forget('expiring_licenses');
    }

    /**
     * Get cached license by key
     */
    public static function getCached(string $licenseKey): ?self
    {
        return Cache::remember("license_{$licenseKey}", 3600, function () use ($licenseKey) {
            return static::where('license_key', $licenseKey)->first();
        });
    }

    /**
     * Get all active licenses (cached)
     */
    public static function getActiveLicenses(): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember('active_licenses', 3600, function () {
            return static::active()->get();
        });
    }

    /**
     * Get expiring licenses (cached)
     */
    public static function getExpiringLicenses(int $days = 30): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember("expiring_licenses_{$days}", 3600, function () use ($days) {
            return static::active()->expiringSoon($days)->get();
        });
    }

    /**
     * Validate license key format
     */
    public function validateLicenseKeyFormat(): bool
    {
        return LicenseKeyGenerator::validateFormat($this->license_key);
    }

    /**
     * Parse license key information
     */
    public function getLicenseKeyInfo(): array
    {
        return LicenseKeyGenerator::parseLicenseKey($this->license_key);
    }

    /**
     * Regenerate license key
     */
    public function regenerateLicenseKey(string $strategy = LicenseKeyGenerator::STRATEGY_STANDARD, array $options = []): string
    {
        $oldKey = $this->license_key;
        $newKey = LicenseKeyGenerator::generate($strategy, $options);
        
        $this->update(['license_key' => $newKey]);
        
        $this->addAuditLog('License key regenerated', [
            'old_key' => $oldKey,
            'new_key' => $newKey,
            'strategy' => $strategy,
            'options' => $options
        ]);
        
        return $newKey;
    }

    /**
     * Regenerate activation code
     */
    public function regenerateActivationCode(int $length = 8): string
    {
        $oldCode = $this->activation_code;
        $newCode = LicenseKeyGenerator::generateActivationCode($length);
        
        $this->update(['activation_code' => $newCode]);
        
        $this->addAuditLog('Activation code regenerated', [
            'old_code' => $oldCode,
            'new_code' => $newCode,
            'length' => $length
        ]);
        
        return $newCode;
    }

    /**
     * Get license key statistics
     */
    public static function getLicenseKeyStatistics(): array
    {
        return LicenseKeyGenerator::getStatistics();
    }

    /**
     * Get license type configuration with features and limits
     */
    public static function getLicenseTypeConfig(string $licenseType): array
    {
        $configs = [
            'standard' => [
                'name' => 'Standard',
                'description' => 'Standard license with basic features for small practices',
                'features' => [
                    'basic_appointments',
                    'patient_management',
                    'prescription_management',
                    'basic_reporting',
                ],
                'limits' => [
                    'max_users' => 5,
                    'max_clinics' => 1,
                    'max_patients' => 500,
                    'max_appointments_per_month' => 200,
                ],
                'grace_period_days' => 7,
                'billing' => [
                    'monthly_fee' => 99.00,
                    'billing_cycle' => 'monthly',
                    'auto_renew' => true,
                ],
                'support' => [
                    'level' => 'standard',
                ],
                'validity' => [
                    'duration_months' => 12,
                ],
            ],
            'premium' => [
                'name' => 'Premium',
                'description' => 'Premium license with advanced features for growing practices',
                'features' => [
                    'basic_appointments',
                    'patient_management',
                    'prescription_management',
                    'basic_reporting',
                    'advanced_reporting',
                    'lab_results',
                    'medrep_management',
                    'multi_clinic',
                    'email_notifications',
                ],
                'limits' => [
                    'max_users' => 25,
                    'max_clinics' => 5,
                    'max_patients' => 2500,
                    'max_appointments_per_month' => 1000,
                ],
                'grace_period_days' => 14,
                'billing' => [
                    'monthly_fee' => 299.00,
                    'billing_cycle' => 'monthly',
                    'auto_renew' => true,
                ],
                'support' => [
                    'level' => 'premium',
                ],
                'validity' => [
                    'duration_months' => 12,
                ],
            ],
            'enterprise' => [
                'name' => 'Enterprise',
                'description' => 'Enterprise license with all features for large organizations',
                'features' => [
                    'basic_appointments',
                    'patient_management',
                    'prescription_management',
                    'basic_reporting',
                    'advanced_reporting',
                    'lab_results',
                    'medrep_management',
                    'multi_clinic',
                    'email_notifications',
                    'sms_notifications',
                    'api_access',
                    'custom_branding',
                    'priority_support',
                    'advanced_analytics',
                    'backup_restore',
                ],
                'limits' => [
                    'max_users' => 100,
                    'max_clinics' => 20,
                    'max_patients' => 10000,
                    'max_appointments_per_month' => 5000,
                ],
                'grace_period_days' => 30,
                'billing' => [
                    'monthly_fee' => 999.00,
                    'billing_cycle' => 'yearly',
                    'auto_renew' => true,
                ],
                'support' => [
                    'level' => 'enterprise',
                ],
                'validity' => [
                    'duration_months' => 12,
                ],
            ],
        ];

        return $configs[$licenseType] ?? $configs['standard'];
    }

    /**
     * Get all available license type configurations
     */
    public static function getAllLicenseTypeConfigs(): array
    {
        return [
            'standard' => static::getLicenseTypeConfig('standard'),
            'premium' => static::getLicenseTypeConfig('premium'),
            'enterprise' => static::getLicenseTypeConfig('enterprise'),
        ];
    }

    /**
     * Auto-fill license fields based on license type
     */
    public function autoFillFromType(string $licenseType, bool $overwriteExisting = false): array
    {
        $config = static::getLicenseTypeConfig($licenseType);
        $updateData = [];

        // Update license type
        if ($overwriteExisting || !$this->license_type) {
            $updateData['license_type'] = $licenseType;
        }

        // Update features
        if ($overwriteExisting || !$this->features || empty($this->features)) {
            $updateData['features'] = $config['features'];
        }

        // Update limits
        if ($overwriteExisting || !$this->max_users) {
            $updateData['max_users'] = $config['limits']['max_users'];
        }

        if ($overwriteExisting || !$this->max_clinics) {
            $updateData['max_clinics'] = $config['limits']['max_clinics'];
        }

        if ($overwriteExisting || !$this->max_patients) {
            $updateData['max_patients'] = $config['limits']['max_patients'];
        }

        if ($overwriteExisting || !$this->max_appointments_per_month) {
            $updateData['max_appointments_per_month'] = $config['limits']['max_appointments_per_month'];
        }

        // Update grace period
        if ($overwriteExisting || !$this->grace_period_days) {
            $updateData['grace_period_days'] = $config['grace_period_days'];
        }

        // Update billing information
        if ($overwriteExisting || !$this->monthly_fee) {
            $updateData['monthly_fee'] = $config['billing']['monthly_fee'];
        }

        if ($overwriteExisting || !$this->billing_cycle) {
            $updateData['billing_cycle'] = $config['billing']['billing_cycle'];
        }

        if ($overwriteExisting || !$this->auto_renew) {
            $updateData['auto_renew'] = $config['billing']['auto_renew'];
        }

        // Update support level
        if ($overwriteExisting || !$this->support_level) {
            $updateData['support_level'] = $config['support']['level'];
        }

        // Update validity period
        if ($overwriteExisting || !$this->starts_at) {
            $updateData['starts_at'] = now();
        }

        if ($overwriteExisting || !$this->expires_at) {
            $duration = $config['validity']['duration_months'];
            $updateData['expires_at'] = now()->addMonths($duration);
        }

        // Update status if not set
        if ($overwriteExisting || !$this->status) {
            $updateData['status'] = 'active';
        }

        // Update description if not set
        if ($overwriteExisting || !$this->description) {
            $updateData['description'] = $config['description'];
        }

        return $updateData;
    }
}

