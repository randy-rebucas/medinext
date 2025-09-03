<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Patient extends Model
{
    protected $fillable = [
        'clinic_id',
        'code',
        'first_name',
        'last_name',
        'dob',
        'sex',
        'contact',
        'allergies',
        'consents',
    ];

    protected $casts = [
        'dob' => 'date',
        'contact' => 'array',
        'allergies' => 'array',
        'consents' => 'array',
    ];

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function encounters(): HasMany
    {
        return $this->hasMany(Encounter::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class);
    }

    /**
     * Get the lab results for this patient
     */
    public function labResults(): HasMany
    {
        return $this->hasMany(LabResult::class);
    }

    /**
     * Get the file assets associated with this patient
     */
    public function fileAssets(): MorphMany
    {
        return $this->morphMany(FileAsset::class, 'owner');
    }

    /**
     * Get patient's full name
     */
    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    /**
     * Get patient's age
     */
    public function getAgeAttribute(): ?int
    {
        return $this->dob ? $this->dob->age : null;
    }

    /**
     * Get patient's complete medical history
     */
    public function getMedicalHistoryAttribute()
    {
        return $this->encounters()
            ->with(['prescriptions', 'labResults', 'doctor'])
            ->orderBy('date', 'desc')
            ->get();
    }

    /**
     * Get patient's recent lab results
     */
    public function getRecentLabResultsAttribute()
    {
        return $this->labResults()
            ->with(['encounter', 'orderedByDoctor'])
            ->orderBy('ordered_at', 'desc')
            ->limit(10)
            ->get();
    }

    /**
     * Get patient's medical documents
     */
    public function getMedicalDocumentsAttribute()
    {
        return $this->fileAssets()
            ->whereIn('category', ['lab_report', 'xray', 'mri', 'ct', 'ultrasound', 'ecg', 'echo', 'biopsy', 'other'])
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
