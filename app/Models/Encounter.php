<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Encounter extends Model
{
    protected $fillable = [
        'clinic_id',
        'patient_id',
        'doctor_id',
        'date',
        'type',
        'status',
        'notes_soap',
        'vitals',
        'diagnosis_codes',
    ];

    protected $casts = [
        'date' => 'datetime',
        'vitals' => 'array',
        'diagnosis_codes' => 'array',
    ];

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class);
    }

    /**
     * Get the lab results associated with this encounter
     */
    public function labResults(): HasMany
    {
        return $this->hasMany(LabResult::class);
    }

    /**
     * Get the file assets associated with this encounter
     */
    public function fileAssets(): HasMany
    {
        return $this->hasMany(FileAsset::class, 'owner_id')
            ->where('owner_type', self::class);
    }
}
