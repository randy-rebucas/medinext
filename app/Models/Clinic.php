<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;

class Clinic extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'timezone',
        'logo_url',
        'address',
        'phone',
        'email',
        'website',
        'description',
        'settings',
    ];

    protected $casts = [
        'address' => 'array',
        'settings' => 'array',
    ];

    /**
     * Boot the model and add global scopes
     */
    protected static function boot()
    {
        parent::boot();

        // Add any global scopes if needed for multi-clinic support
    }

    /**
     * Scope to filter by active status
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by timezone
     */
    public function scopeByTimezone(Builder $query, string $timezone): Builder
    {
        return $query->where('timezone', $timezone);
    }

    public function userClinicRoles(): HasMany
    {
        return $this->hasMany(UserClinicRole::class);
    }

    /**
     * Get the users associated with this clinic
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_clinic_roles')
                    ->withPivot('role_id')
                    ->withTimestamps();
    }

    /**
     * Get the doctors in this clinic
     */
    public function doctors(): HasMany
    {
        return $this->hasMany(Doctor::class);
    }

    /**
     * Get the active doctors in this clinic
     */
    public function activeDoctors(): HasMany
    {
        return $this->hasMany(Doctor::class)->whereHas('user', function ($query) {
            $query->where('is_active', true);
        });
    }

    /**
     * Get the patients in this clinic
     */
    public function patients(): HasMany
    {
        return $this->hasMany(Patient::class);
    }

    /**
     * Get the rooms in this clinic
     */
    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    /**
     * Get the appointments in this clinic
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * Get the encounters in this clinic
     */
    public function encounters(): HasMany
    {
        return $this->hasMany(Encounter::class);
    }

    /**
     * Get the prescriptions in this clinic
     */
    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class);
    }

    /**
     * Get the activity logs for this clinic
     */
    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    /**
     * Get the medrep visits for this clinic
     */
    public function medrepVisits(): HasMany
    {
        return $this->hasMany(MedrepVisit::class);
    }

    /**
     * Get the lab results in this clinic
     */
    public function labResults(): HasMany
    {
        return $this->hasMany(LabResult::class);
    }

    /**
     * Get the file assets in this clinic
     */
    public function fileAssets(): HasMany
    {
        return $this->hasMany(FileAsset::class);
    }

    /**
     * Get clinic display name
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->name;
    }

    /**
     * Get clinic timezone with fallback
     */
    public function getTimezoneAttribute($value): string
    {
        return $value ?: 'Asia/Manila';
    }

    /**
     * Get formatted address
     */
    public function getFormattedAddressAttribute(): string
    {
        if (!$this->address) {
            return 'Address not specified';
        }

        $parts = [];
        if (isset($this->address['street'])) $parts[] = $this->address['street'];
        if (isset($this->address['city'])) $parts[] = $this->address['city'];
        if (isset($this->address['state'])) $parts[] = $this->address['state'];
        if (isset($this->address['country'])) $parts[] = $this->address['country'];

        return implode(', ', $parts);
    }

    /**
     * Get clinic statistics
     */
    public function getStatisticsAttribute(): array
    {
        return [
            'total_doctors' => $this->doctors()->count(),
            'total_patients' => $this->patients()->count(),
            'total_appointments' => $this->appointments()->count(),
            'total_encounters' => $this->encounters()->count(),
            'total_prescriptions' => $this->prescriptions()->count(),
        ];
    }

    /**
     * Check if clinic has active doctors
     */
    public function hasActiveDoctors(): bool
    {
        return $this->activeDoctors()->exists();
    }

    /**
     * Get clinic working hours from settings
     */
    public function getWorkingHoursAttribute(): array
    {
        return $this->settings['working_hours'] ?? [
            'monday' => ['09:00', '17:00'],
            'tuesday' => ['09:00', '17:00'],
            'wednesday' => ['09:00', '17:00'],
            'thursday' => ['09:00', '17:00'],
            'friday' => ['09:00', '17:00'],
            'saturday' => ['09:00', '12:00'],
            'sunday' => ['closed']
        ];
    }

    /**
     * Check if clinic is open on a specific day and time
     */
    public function isOpen(string $day, string $time = null): bool
    {
        $workingHours = $this->working_hours;
        
        if (!isset($workingHours[strtolower($day)]) || $workingHours[strtolower($day)] === 'closed') {
            return false;
        }

        if ($time && is_array($workingHours[strtolower($day)])) {
            $start = $workingHours[strtolower($day)][0];
            $end = $workingHours[strtolower($day)][1];
            return $time >= $start && $time <= $end;
        }

        return true;
    }
}
