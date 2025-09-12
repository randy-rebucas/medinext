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
     * Get the bills for this patient
     */
    public function bills(): HasMany
    {
        return $this->hasMany(Bill::class);
    }

    /**
     * Get the insurance records for this patient
     */
    public function insurance(): HasMany
    {
        return $this->hasMany(Insurance::class);
    }

    /**
     * Get the queue entries for this patient
     */
    public function queuePatients(): HasMany
    {
        return $this->hasMany(QueuePatient::class);
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

    /**
     * Get the displayable singular label of the model.
     * This method exists to prevent errors when code incorrectly calls
     * signularLabel() (with typo) or singularLabel() on the model.
     *
     * @return string
     */
    public static function singularLabel(): string
    {
        return 'Patient';
    }

    /**
     * Get the displayable singular label of the model (with typo).
     * This method exists to prevent errors when code incorrectly calls
     * signularLabel() (with typo) on the model.
     *
     * @return string
     */
    public static function signularLabel(): string
    {
        return 'Patient';
    }

    /**
     * Get the URI key for the model.
     * This method is required for Nova MorphTo relationships.
     *
     * @return string
     */
    public static function uriKey(): string
    {
        return 'patients';
    }
}
