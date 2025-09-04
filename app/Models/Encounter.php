<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

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
        'chief_complaint',
        'assessment',
        'plan',
        'follow_up_date',
        'encounter_number',
        'visit_type',
        'payment_status',
        'billing_amount',
    ];

    protected $casts = [
        'date' => 'datetime',
        'vitals' => 'array',
        'diagnosis_codes' => 'array',
        'notes_soap' => 'array',
        'follow_up_date' => 'datetime',
        'billing_amount' => 'decimal:2',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        // Generate encounter number on creation
        static::creating(function ($encounter) {
            if (empty($encounter->encounter_number)) {
                $encounter->encounter_number = static::generateEncounterNumber($encounter->clinic_id);
            }
        });
    }

    /**
     * Scope to filter by clinic
     */
    public function scopeByClinic(Builder $query, int $clinicId): Builder
    {
        return $query->where('clinic_id', $clinicId);
    }

    /**
     * Scope to filter by patient
     */
    public function scopeByPatient(Builder $query, int $patientId): Builder
    {
        return $query->where('patient_id', $patientId);
    }

    /**
     * Scope to filter by doctor
     */
    public function scopeByDoctor(Builder $query, int $doctorId): Builder
    {
        return $query->where('doctor_id', $doctorId);
    }

    /**
     * Scope to filter by date range
     */
    public function scopeByDateRange(Builder $query, Carbon $startDate, Carbon $endDate): Builder
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope to filter by status
     */
    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter by type
     */
    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to filter recent encounters
     */
    public function scopeRecent(Builder $query, int $days = 30): Builder
    {
        return $query->where('date', '>=', Carbon::now()->subDays($days));
    }

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

    /**
     * Get the SOAP notes for this encounter
     */
    public function getSoapNotesAttribute(): array
    {
        if (is_string($this->notes_soap)) {
            return json_decode($this->notes_soap, true) ?? [];
        }
        return $this->notes_soap ?? [];
    }

    /**
     * Set SOAP notes
     */
    public function setSoapNotes(array $notes): void
    {
        $this->notes_soap = $notes;
    }

    /**
     * Get subjective notes
     */
    public function getSubjectiveNotesAttribute(): string
    {
        return $this->soap_notes['subjective'] ?? '';
    }

    /**
     * Get objective notes
     */
    public function getObjectiveNotesAttribute(): string
    {
        return $this->soap_notes['objective'] ?? '';
    }

    /**
     * Get assessment notes
     */
    public function getAssessmentNotesAttribute(): string
    {
        return $this->soap_notes['assessment'] ?? '';
    }

    /**
     * Get plan notes
     */
    public function getPlanNotesAttribute(): string
    {
        return $this->soap_notes['plan'] ?? '';
    }

    /**
     * Get vitals in a structured format
     */
    public function getStructuredVitalsAttribute(): array
    {
        $defaultVitals = [
            'blood_pressure' => ['systolic' => null, 'diastolic' => null],
            'heart_rate' => null,
            'temperature' => null,
            'respiratory_rate' => null,
            'height' => null,
            'weight' => null,
            'bmi' => null,
            'oxygen_saturation' => null,
            'pain_scale' => null,
        ];

        if (is_string($this->vitals)) {
            $vitals = json_decode($this->vitals, true) ?? [];
        } else {
            $vitals = $this->vitals ?? [];
        }

        return array_merge($defaultVitals, $vitals);
    }

    /**
     * Get diagnosis in a structured format
     */
    public function getStructuredDiagnosisAttribute(): array
    {
        $defaultDiagnosis = [
            'primary_diagnosis' => null,
            'secondary_diagnosis' => [],
            'icd_codes' => [],
            'severity' => null,
            'chronic_condition' => false,
        ];

        if (is_string($this->diagnosis_codes)) {
            $diagnosis = json_decode($this->diagnosis_codes, true) ?? [];
        } else {
            $diagnosis = $this->diagnosis_codes ?? [];
        }

        return array_merge($defaultDiagnosis, $diagnosis);
    }

    /**
     * Get encounter summary
     */
    public function getSummaryAttribute(): array
    {
        return [
            'encounter_number' => $this->encounter_number,
            'date' => $this->date->format('Y-m-d H:i'),
            'patient_name' => $this->patient->name ?? 'Unknown',
            'doctor_name' => $this->doctor->user->name ?? 'Unknown',
            'type' => $this->type,
            'status' => $this->status,
            'chief_complaint' => $this->chief_complaint,
            'assessment' => $this->assessment,
            'plan' => $this->plan,
            'follow_up_date' => $this->follow_up_date?->format('Y-m-d'),
            'prescriptions_count' => $this->prescriptions->count(),
            'lab_results_count' => $this->labResults->count(),
            'files_count' => $this->fileAssets->count(),
        ];
    }

    /**
     * Get encounter type display name
     */
    public function getTypeDisplayNameAttribute(): string
    {
        $types = [
            'consultation' => 'Consultation',
            'follow_up' => 'Follow-up',
            'emergency' => 'Emergency',
            'routine_checkup' => 'Routine Checkup',
            'specialist_consultation' => 'Specialist Consultation',
            'procedure' => 'Procedure',
            'surgery' => 'Surgery',
        ];

        return $types[$this->type] ?? ucfirst($this->type);
    }

    /**
     * Get encounter status display name
     */
    public function getStatusDisplayNameAttribute(): string
    {
        $statuses = [
            'scheduled' => 'Scheduled',
            'in_progress' => 'In Progress',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            'no_show' => 'No Show',
            'rescheduled' => 'Rescheduled',
        ];

        return $statuses[$this->status] ?? ucfirst($this->status);
    }

    /**
     * Get visit type display name
     */
    public function getVisitTypeDisplayNameAttribute(): string
    {
        $visitTypes = [
            'new_patient' => 'New Patient',
            'established_patient' => 'Established Patient',
            'urgent_care' => 'Urgent Care',
            'preventive_care' => 'Preventive Care',
            'chronic_disease_management' => 'Chronic Disease Management',
        ];

        return $visitTypes[$this->visit_type] ?? ucfirst($this->visit_type);
    }

    /**
     * Get payment status display name
     */
    public function getPaymentStatusDisplayNameAttribute(): string
    {
        $paymentStatuses = [
            'pending' => 'Pending',
            'paid' => 'Paid',
            'partial' => 'Partial Payment',
            'waived' => 'Waived',
            'insurance_pending' => 'Insurance Pending',
        ];

        return $paymentStatuses[$this->payment_status] ?? ucfirst($this->payment_status);
    }

    /**
     * Check if encounter is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if encounter is in progress
     */
    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    /**
     * Check if encounter is scheduled
     */
    public function isScheduled(): bool
    {
        return $this->status === 'scheduled';
    }

    /**
     * Check if encounter is cancelled
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Check if encounter has follow-up scheduled
     */
    public function hasFollowUp(): bool
    {
        return !is_null($this->follow_up_date);
    }

    /**
     * Check if encounter is overdue for follow-up
     */
    public function isFollowUpOverdue(): bool
    {
        if (!$this->hasFollowUp()) {
            return false;
        }

        return $this->follow_up_date->isPast();
    }

    /**
     * Get encounter duration in minutes
     */
    public function getDurationAttribute(): int
    {
        // Default duration based on type
        $defaultDurations = [
            'consultation' => 30,
            'follow_up' => 20,
            'emergency' => 60,
            'routine_checkup' => 45,
            'specialist_consultation' => 45,
            'procedure' => 90,
            'surgery' => 180,
        ];

        return $defaultDurations[$this->type] ?? 30;
    }

    /**
     * Get encounter cost
     */
    public function getCostAttribute(): float
    {
        if ($this->billing_amount) {
            return (float) $this->billing_amount;
        }

        // Default costs based on type
        $defaultCosts = [
            'consultation' => 50.00,
            'follow_up' => 30.00,
            'emergency' => 100.00,
            'routine_checkup' => 75.00,
            'specialist_consultation' => 100.00,
            'procedure' => 200.00,
            'surgery' => 1000.00,
        ];

        return $defaultCosts[$this->type] ?? 50.00;
    }

    /**
     * Get encounter statistics
     */
    public function getStatisticsAttribute(): array
    {
        return [
            'prescriptions_count' => $this->prescriptions->count(),
            'lab_results_count' => $this->labResults->count(),
            'files_count' => $this->fileAssets->count(),
            'duration_minutes' => $this->duration,
            'cost' => $this->cost,
            'is_completed' => $this->isCompleted(),
            'has_follow_up' => $this->hasFollowUp(),
            'follow_up_overdue' => $this->isFollowUpOverdue(),
        ];
    }

    /**
     * Generate unique encounter number
     */
    public static function generateEncounterNumber(int $clinicId): string
    {
        $clinic = Clinic::find($clinicId);
        $clinicCode = $clinic ? strtoupper(substr($clinic->name, 0, 3)) : 'CLN';

        $date = now()->format('Ymd');
        $sequence = static::where('clinic_id', $clinicId)
            ->whereDate('created_at', today())
            ->count() + 1;

        return sprintf('%s%s%04d', $clinicCode, $date, $sequence);
    }

    /**
     * Get related encounters for the same patient
     */
    public function getRelatedEncountersAttribute()
    {
        return static::where('patient_id', $this->patient_id)
            ->where('id', '!=', $this->id)
            ->orderBy('date', 'desc')
            ->limit(10)
            ->get();
    }

    /**
     * Get encounter timeline
     */
    public function getTimelineAttribute(): array
    {
        $timeline = [];

        // Add encounter creation
        $timeline[] = [
            'date' => $this->created_at->format('Y-m-d H:i'),
            'event' => 'Encounter Created',
            'description' => "Encounter {$this->encounter_number} created for {$this->patient->name}",
            'type' => 'creation'
        ];

        // Add status changes
        if ($this->status !== 'scheduled') {
            $timeline[] = [
                'date' => $this->updated_at->format('Y-m-d H:i'),
                'event' => 'Status Updated',
                'description' => "Status changed to {$this->status_display_name}",
                'type' => 'status_change'
            ];
        }

        // Add prescriptions
        foreach ($this->prescriptions as $prescription) {
            $timeline[] = [
                'date' => $prescription->issued_at->format('Y-m-d H:i'),
                'event' => 'Prescription Issued',
                'description' => "Prescription #{$prescription->id} issued",
                'type' => 'prescription'
            ];
        }

        // Add lab results
        foreach ($this->labResults as $labResult) {
            $timeline[] = [
                'date' => $labResult->created_at->format('Y-m-d H:i'),
                'event' => 'Lab Result Added',
                'description' => "Lab result for {$labResult->test_name}",
                'type' => 'lab_result'
            ];
        }

        // Add file uploads
        foreach ($this->fileAssets as $file) {
            $timeline[] = [
                'date' => $file->created_at->format('Y-m-d H:i'),
                'event' => 'File Uploaded',
                'description' => "File {$file->original_name} uploaded",
                'type' => 'file_upload'
            ];
        }

        // Sort by date
        usort($timeline, function ($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });

        return $timeline;
    }
}
