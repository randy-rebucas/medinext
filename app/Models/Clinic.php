<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Clinic extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'timezone',
        'logo_url',
        'address',
        'settings',
    ];

    protected $casts = [
        'address' => 'array',
        'settings' => 'array',
    ];

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
}
