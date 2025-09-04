<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class Setting extends Model
{
    protected $fillable = [
        'clinic_id',
        'key',
        'value',
        'type',
        'group',
        'description',
        'is_public',
    ];

    protected $casts = [
        'value' => 'json',
        'is_public' => 'boolean',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        // Clear cache when settings are updated
        static::saved(function ($setting) {
            $setting->clearCache();
        });

        static::deleted(function ($setting) {
            $setting->clearCache();
        });
    }

    /**
     * Scope to filter by group
     */
    public function scopeByGroup(Builder $query, string $group): Builder
    {
        return $query->where('group', $group);
    }

    /**
     * Scope to filter by type
     */
    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to filter public settings
     */
    public function scopePublic(Builder $query): Builder
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope to filter private settings
     */
    public function scopePrivate(Builder $query): Builder
    {
        return $query->where('is_public', false);
    }

    /**
     * Scope to filter by clinic
     */
    public function scopeByClinic(Builder $query, ?int $clinicId): Builder
    {
        return $query->where('clinic_id', $clinicId);
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    /**
     * Get setting value with caching
     */
    public static function getValue(string $key, $default = null, ?int $clinicId = null)
    {
        $cacheKey = "setting_{$key}_" . ($clinicId ?? 'global');
        
        return Cache::remember($cacheKey, 3600, function () use ($key, $default, $clinicId) {
            $setting = static::where('key', $key)
                ->when($clinicId, fn($query) => $query->where('clinic_id', $clinicId))
                ->first();

            return $setting ? $setting->value : $default;
        });
    }

    /**
     * Set setting value with validation
     */
    public static function setValue(string $key, $value, ?int $clinicId = null, string $type = 'string', string $group = 'general', string $description = '', bool $isPublic = false)
    {
        // Validate value based on type
        if (!$setting = static::validateValue($value, $type)) {
            throw new \InvalidArgumentException("Invalid value for type: {$type}");
        }

        $setting = static::updateOrCreate(
            [
                'key' => $key,
                'clinic_id' => $clinicId,
            ],
            [
                'value' => $value,
                'type' => $type,
                'group' => $group,
                'description' => $description,
                'is_public' => $isPublic,
            ]
        );

        // Clear cache for this setting
        $setting->clearCache();

        return $setting;
    }

    /**
     * Get multiple settings by group
     */
    public static function getGroup(string $group, ?int $clinicId = null): array
    {
        $cacheKey = "settings_group_{$group}_" . ($clinicId ?? 'global');
        
        return Cache::remember($cacheKey, 3600, function () use ($group, $clinicId) {
            return static::where('group', $group)
                ->when($clinicId, fn($query) => $query->where('clinic_id', $clinicId))
                ->pluck('value', 'key')
                ->toArray();
        });
    }

    /**
     * Get all settings for a clinic
     */
    public static function getAllForClinic(?int $clinicId = null): array
    {
        $cacheKey = "settings_all_" . ($clinicId ?? 'global');
        
        return Cache::remember($cacheKey, 3600, function () use ($clinicId) {
            return static::when($clinicId, fn($query) => $query->where('clinic_id', $clinicId))
                ->get()
                ->groupBy('group')
                ->map(function ($settings) {
                    return $settings->pluck('value', 'key');
                })
                ->toArray();
        });
    }

    /**
     * Validate setting value based on type
     */
    public static function validateValue($value, string $type): bool
    {
        $rules = [
            'string' => 'string',
            'integer' => 'integer',
            'boolean' => 'boolean',
            'array' => 'array',
            'object' => 'array',
            'json' => 'array',
        ];

        if (!isset($rules[$type])) {
            return false;
        }

        $validator = Validator::make(['value' => $value], [
            'value' => $rules[$type]
        ]);

        return !$validator->fails();
    }

    /**
     * Get setting group display name
     */
    public function getGroupDisplayNameAttribute(): string
    {
        $groups = [
            'clinic' => 'Clinic Information',
            'working_hours' => 'Working Hours',
            'notifications' => 'Notifications',
            'branding' => 'Branding',
            'system' => 'System Settings',
            'billing' => 'Billing',
            'security' => 'Security',
            'general' => 'General',
        ];

        return $groups[$this->group] ?? ucfirst($this->group);
    }

    /**
     * Get setting type display name
     */
    public function getTypeDisplayNameAttribute(): string
    {
        $types = [
            'string' => 'Text',
            'integer' => 'Number',
            'boolean' => 'Yes/No',
            'array' => 'List',
            'object' => 'Object',
            'json' => 'JSON',
        ];

        return $types[$this->type] ?? ucfirst($this->type);
    }

    /**
     * Get formatted value for display
     */
    public function getFormattedValueAttribute(): string
    {
        switch ($this->type) {
            case 'boolean':
                return $this->value ? 'Yes' : 'No';
            case 'array':
            case 'json':
                return is_array($this->value) ? json_encode($this->value, JSON_PRETTY_PRINT) : $this->value;
            case 'integer':
                return number_format($this->value);
            default:
                return (string) $this->value;
        }
    }

    /**
     * Check if setting is editable
     */
    public function isEditable(): bool
    {
        // System settings cannot be edited
        $systemKeys = [
            'system.timezone',
            'system.date_format',
            'system.time_format',
            'system.currency',
        ];

        return !in_array($this->key, $systemKeys);
    }

    /**
     * Check if setting is required
     */
    public function isRequired(): bool
    {
        $requiredKeys = [
            'clinic.name',
            'clinic.phone',
            'clinic.email',
            'clinic.address',
            'working_hours.monday',
            'working_hours.tuesday',
            'working_hours.wednesday',
            'working_hours.thursday',
            'working_hours.friday',
        ];

        return in_array($this->key, $requiredKeys);
    }

    /**
     * Get setting validation rules
     */
    public function getValidationRulesAttribute(): array
    {
        $rules = ['required'];

        switch ($this->type) {
            case 'string':
                $rules[] = 'string';
                $rules[] = 'max:255';
                break;
            case 'integer':
                $rules[] = 'integer';
                $rules[] = 'min:0';
                break;
            case 'boolean':
                $rules[] = 'boolean';
                break;
            case 'array':
            case 'json':
                $rules[] = 'array';
                break;
        }

        // Add specific validation for certain keys
        switch ($this->key) {
            case 'clinic.email':
                $rules[] = 'email';
                break;
            case 'clinic.phone':
                $rules[] = 'regex:/^[\+]?[0-9\s\-\(\)]+$/';
                break;
            case 'branding.primary_color':
            case 'branding.secondary_color':
                $rules[] = 'regex:/^#[0-9A-F]{6}$/i';
                break;
        }

        return $rules;
    }

    /**
     * Get setting help text
     */
    public function getHelpTextAttribute(): string
    {
        $helpTexts = [
            'clinic.name' => 'The official name of your clinic as it appears to patients',
            'clinic.phone' => 'Primary contact phone number for patients',
            'clinic.email' => 'Primary contact email address for patients',
            'clinic.address' => 'Complete clinic address for patient directions',
            'clinic.website' => 'Your clinic website URL (optional)',
            'clinic.description' => 'Brief description of your clinic services',
            'working_hours.monday' => 'Monday operating hours (use 24-hour format)',
            'working_hours.tuesday' => 'Tuesday operating hours (use 24-hour format)',
            'working_hours.wednesday' => 'Wednesday operating hours (use 24-hour format)',
            'working_hours.thursday' => 'Thursday operating hours (use 24-hour format)',
            'working_hours.friday' => 'Friday operating hours (use 24-hour format)',
            'working_hours.saturday' => 'Saturday operating hours (use 24-hour format)',
            'working_hours.sunday' => 'Sunday operating hours (use 24-hour format)',
            'notifications.email_enabled' => 'Enable email notifications for appointments and reminders',
            'notifications.sms_enabled' => 'Enable SMS notifications (requires SMS service setup)',
            'notifications.appointment_reminder_hours' => 'Hours before appointment to send reminder',
            'notifications.follow_up_days' => 'Days after visit to send follow-up message',
            'branding.primary_color' => 'Primary brand color in hex format (e.g., #3B82F6)',
            'branding.secondary_color' => 'Secondary brand color in hex format (e.g., #1E40AF)',
            'branding.logo_url' => 'URL to your clinic logo image',
            'branding.favicon_url' => 'URL to your clinic favicon',
            'system.timezone' => 'Default timezone for the clinic',
            'system.date_format' => 'Date format for display (e.g., Y-m-d)',
            'system.time_format' => 'Time format for display (e.g., H:i)',
            'system.currency' => 'Default currency for billing and pricing',
        ];

        return $helpTexts[$this->key] ?? $this->description;
    }

    /**
     * Get setting category
     */
    public function getCategoryAttribute(): string
    {
        $categories = [
            'clinic' => 'Clinic Information',
            'working_hours' => 'Working Hours',
            'notifications' => 'Notifications',
            'branding' => 'Branding',
            'system' => 'System Settings',
            'billing' => 'Billing',
            'security' => 'Security',
            'general' => 'General',
        ];

        return $categories[$this->group] ?? 'Other';
    }

    /**
     * Clear cache for this setting
     */
    public function clearCache(): void
    {
        $cacheKeys = [
            "setting_{$this->key}_" . ($this->clinic_id ?? 'global'),
            "settings_group_{$this->group}_" . ($this->clinic_id ?? 'global'),
            "settings_all_" . ($this->clinic_id ?? 'global'),
        ];

        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }
    }

    /**
     * Get setting usage statistics
     */
    public function getUsageStatisticsAttribute(): array
    {
        return [
            'is_editable' => $this->isEditable(),
            'is_required' => $this->isRequired(),
            'has_validation' => !empty($this->validation_rules),
            'cache_key' => "setting_{$this->key}_" . ($this->clinic_id ?? 'global'),
        ];
    }

    /**
     * Check if setting value is valid
     */
    public function isValid(): bool
    {
        return static::validateValue($this->value, $this->type);
    }

    /**
     * Get setting dependencies
     */
    public function getDependenciesAttribute(): array
    {
        $dependencies = [
            'notifications.sms_enabled' => ['notifications.sms_provider', 'notifications.sms_api_key'],
            'branding.logo_url' => ['branding.logo_width', 'branding.logo_height'],
            'working_hours.monday' => ['working_hours.timezone'],
        ];

        return $dependencies[$this->key] ?? [];
    }

    /**
     * Check if setting has dependencies
     */
    public function hasDependencies(): bool
    {
        return !empty($this->dependencies);
    }
}
