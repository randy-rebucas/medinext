<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LabResult extends Model
{
    protected $fillable = [
        'clinic_id',
        'patient_id',
        'encounter_id',
        'test_type',
        'test_name',
        'result_value',
        'unit',
        'reference_range',
        'status',
        'ordered_at',
        'completed_at',
        'notes',
        'ordered_by_doctor_id',
        'reviewed_by_doctor_id',
    ];

    protected $casts = [
        'ordered_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the clinic this lab result belongs to
     */
    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    /**
     * Get the patient this lab result belongs to
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get the encounter this lab result is associated with
     */
    public function encounter(): BelongsTo
    {
        return $this->belongsTo(Encounter::class);
    }

    /**
     * Get the doctor who ordered this test
     */
    public function orderedByDoctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'ordered_by_doctor_id');
    }

    /**
     * Get the doctor who reviewed this result
     */
    public function reviewedByDoctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'reviewed_by_doctor_id');
    }

    /**
     * Get the file assets associated with this lab result
     */
    public function fileAssets(): HasMany
    {
        return $this->hasMany(FileAsset::class, 'owner_id')
            ->where('owner_type', self::class);
    }

    /**
     * Check if result is abnormal
     */
    public function isAbnormal(): bool
    {
        return $this->status === 'abnormal' || $this->status === 'critical';
    }

    /**
     * Check if result is critical
     */
    public function isCritical(): bool
    {
        return $this->status === 'critical';
    }

    /**
     * Check if result is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Get the test category
     */
    public function getTestCategoryAttribute(): string
    {
        return match($this->test_type) {
            'blood' => 'Blood Tests',
            'urine' => 'Urine Tests',
            'stool' => 'Stool Tests',
            'xray' => 'Imaging - X-Ray',
            'mri' => 'Imaging - MRI',
            'ct' => 'Imaging - CT',
            'ultrasound' => 'Imaging - Ultrasound',
            'ecg' => 'Cardiac - ECG',
            'echo' => 'Cardiac - Echocardiogram',
            'biopsy' => 'Pathology - Biopsy',
            'culture' => 'Microbiology - Culture',
            default => 'Other Tests',
        };
    }
}
