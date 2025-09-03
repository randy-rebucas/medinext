<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Doctor extends Model
{
    protected $fillable = [
        'user_id',
        'clinic_id',
        'specialty',
        'license_no',
        'signature_url',
    ];

    /**
     * Get the user that is this doctor
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the clinic where this doctor works
     */
    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    /**
     * Get the encounters for this doctor
     */
    public function encounters(): HasMany
    {
        return $this->hasMany(Encounter::class);
    }

    /**
     * Get the appointments for this doctor
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * Get the prescriptions written by this doctor
     */
    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class);
    }

    /**
     * Get the medrep visits for this doctor
     */
    public function medrepVisits(): HasMany
    {
        return $this->hasMany(MedrepVisit::class);
    }

    /**
     * Get doctor's full name
     */
    public function getFullNameAttribute(): string
    {
        return $this->user->name;
    }

    /**
     * Get doctor's specialty display name
     */
    public function getSpecialtyDisplayAttribute(): string
    {
        return $this->specialty ?: 'General Practice';
    }
}
