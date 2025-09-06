<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class Prescription extends Model
{
    protected $fillable = [
        'clinic_id',
        'patient_id',
        'doctor_id',
        'encounter_id',
        'issued_at',
        'status',
        'pdf_url',
        'qr_hash',
        'prescription_number',
        'prescription_type',
        'diagnosis',
        'instructions',
        'dispense_quantity',
        'refills_allowed',
        'refills_remaining',
        'expiry_date',
        'pharmacy_notes',
        'patient_instructions',
        'side_effects',
        'contraindications',
        'drug_interactions',
        'allergies_warning',
        'pregnancy_warning',
        'breastfeeding_warning',
        'driving_warning',
        'alcohol_warning',
        'dietary_restrictions',
        'follow_up_date',
        'next_refill_date',
        'total_cost',
        'insurance_coverage',
        'copay_amount',
        'prior_authorization',
        'prior_auth_number',
        'prescription_source',
        'digital_signature',
        'signature_date',
        'verification_status',
        'verification_date',
        'verification_notes',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'expiry_date' => 'datetime',
        'follow_up_date' => 'datetime',
        'next_refill_date' => 'datetime',
        'signature_date' => 'datetime',
        'verification_date' => 'datetime',
        'dispense_quantity' => 'integer',
        'refills_allowed' => 'integer',
        'refills_remaining' => 'integer',
        'total_cost' => 'decimal:2',
        'copay_amount' => 'decimal:2',
        'insurance_coverage' => 'array',
        'prior_authorization' => 'boolean',
        'verification_status' => 'boolean',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        // Generate prescription number on creation
        static::creating(function ($prescription) {
            if (empty($prescription->prescription_number)) {
                $prescription->prescription_number = static::generatePrescriptionNumber($prescription->clinic_id);
            }
            
            if (empty($prescription->qr_hash)) {
                $prescription->qr_hash = static::generateQrHash();
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
     * Scope to filter by status
     */
    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter by prescription type
     */
    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('prescription_type', $type);
    }

    /**
     * Scope to filter active prescriptions
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active')
            ->where('expiry_date', '>', now())
            ->where('refills_remaining', '>', 0);
    }

    /**
     * Scope to filter expired prescriptions
     */
    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('expiry_date', '<', now());
    }

    /**
     * Scope to filter prescriptions needing refills
     */
    public function scopeNeedsRefill(Builder $query): Builder
    {
        return $query->where('status', 'active')
            ->where('refills_remaining', '>', 0)
            ->where('next_refill_date', '<=', now());
    }

    /**
     * Scope to filter by date range
     */
    public function scopeByDateRange(Builder $query, Carbon $startDate, Carbon $endDate): Builder
    {
        return $query->whereBetween('issued_at', [$startDate, $endDate]);
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

    public function encounter(): BelongsTo
    {
        return $this->belongsTo(Encounter::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PrescriptionItem::class);
    }

    /**
     * Get prescription type display name
     */
    public function getTypeDisplayNameAttribute(): string
    {
        $types = [
            'new' => 'New Prescription',
            'refill' => 'Refill',
            'emergency' => 'Emergency',
            'controlled' => 'Controlled Substance',
            'compounded' => 'Compounded',
            'over_the_counter' => 'Over the Counter',
            'sample' => 'Sample',
            'discharge' => 'Discharge',
            'maintenance' => 'Maintenance',
        ];

        return $types[$this->prescription_type] ?? ucfirst($this->prescription_type);
    }

    /**
     * Get prescription status display name
     */
    public function getStatusDisplayNameAttribute(): string
    {
        $statuses = [
            'draft' => 'Draft',
            'active' => 'Active',
            'dispensed' => 'Dispensed',
            'expired' => 'Expired',
            'cancelled' => 'Cancelled',
            'suspended' => 'Suspended',
            'completed' => 'Completed',
            'pending_verification' => 'Pending Verification',
            'verified' => 'Verified',
            'rejected' => 'Rejected',
        ];

        return $statuses[$this->status] ?? ucfirst($this->status);
    }

    /**
     * Get verification status display name
     */
    public function getVerificationStatusDisplayNameAttribute(): string
    {
        if ($this->verification_status === null) {
            return 'Not Verified';
        }

        return $this->verification_status ? 'Verified' : 'Rejected';
    }

    /**
     * Check if prescription is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active' && 
               $this->expiry_date > now() && 
               $this->refills_remaining > 0;
    }

    /**
     * Check if prescription is expired
     */
    public function isExpired(): bool
    {
        return $this->expiry_date < now();
    }

    /**
     * Check if prescription needs refill
     */
    public function needsRefill(): bool
    {
        return $this->isActive() && $this->next_refill_date <= now();
    }

    /**
     * Check if prescription is verified
     */
    public function isVerified(): bool
    {
        return $this->verification_status === true;
    }

    /**
     * Check if prescription is pending verification
     */
    public function isPendingVerification(): bool
    {
        return $this->verification_status === null;
    }

    /**
     * Check if prescription is rejected
     */
    public function isRejected(): bool
    {
        return $this->verification_status === false;
    }

    /**
     * Get prescription summary
     */
    public function getSummaryAttribute(): array
    {
        return [
            'prescription_number' => $this->prescription_number,
            'patient_name' => $this->patient->name ?? 'Unknown',
            'doctor_name' => $this->doctor->user->name ?? 'Unknown',
            'issued_date' => $this->issued_at->format('Y-m-d'),
            'type' => $this->type_display_name,
            'status' => $this->status_display_name,
            'items_count' => $this->items->count(),
            'refills_remaining' => $this->refills_remaining,
            'expiry_date' => $this->expiry_date?->format('Y-m-d'),
            'total_cost' => $this->total_cost,
            'verification_status' => $this->verification_status_display_name,
        ];
    }

    /**
     * Get prescription timeline
     */
    public function getTimelineAttribute(): array
    {
        $timeline = [];

        // Prescription creation
        $timeline[] = [
            'date' => $this->created_at->format('Y-m-d H:i'),
            'event' => 'Prescription Created',
            'description' => "Prescription {$this->prescription_number} created",
            'type' => 'creation'
        ];

        // Prescription issued
        if ($this->issued_at) {
            $timeline[] = [
                'date' => $this->issued_at->format('Y-m-d H:i'),
                'event' => 'Prescription Issued',
                'description' => "Prescription issued to patient",
                'type' => 'issued'
            ];
        }

        // Digital signature
        if ($this->signature_date) {
            $timeline[] = [
                'date' => $this->signature_date->format('Y-m-d H:i'),
                'event' => 'Digitally Signed',
                'description' => "Prescription digitally signed by doctor",
                'type' => 'signed'
            ];
        }

        // Verification
        if ($this->verification_date) {
            $event = $this->verification_status ? 'Verified' : 'Rejected';
            $description = $this->verification_status ? 
                'Prescription verified and approved' : 
                "Prescription rejected: {$this->verification_notes}";
            
            $timeline[] = [
                'date' => $this->verification_date->format('Y-m-d H:i'),
                'event' => $event,
                'description' => $description,
                'type' => $this->verification_status ? 'verified' : 'rejected'
            ];
        }

        // Status changes
        if ($this->status !== 'draft') {
            $timeline[] = [
                'date' => $this->updated_at->format('Y-m-d H:i'),
                'event' => 'Status Updated',
                'description' => "Status changed to {$this->status_display_name}",
                'type' => 'status_change'
            ];
        }

        // Sort by date
        usort($timeline, function ($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });

        return $timeline;
    }

    /**
     * Get prescription warnings
     */
    public function getWarningsAttribute(): array
    {
        $warnings = [];

        if ($this->side_effects) {
            $warnings[] = [
                'type' => 'side_effects',
                'title' => 'Side Effects',
                'description' => $this->side_effects,
                'severity' => 'medium'
            ];
        }

        if ($this->contraindications) {
            $warnings[] = [
                'type' => 'contraindications',
                'title' => 'Contraindications',
                'description' => $this->contraindications,
                'severity' => 'high'
            ];
        }

        if ($this->drug_interactions) {
            $warnings[] = [
                'type' => 'drug_interactions',
                'title' => 'Drug Interactions',
                'description' => $this->drug_interactions,
                'severity' => 'high'
            ];
        }

        if ($this->allergies_warning) {
            $warnings[] = [
                'type' => 'allergies',
                'title' => 'Allergies Warning',
                'description' => $this->allergies_warning,
                'severity' => 'high'
            ];
        }

        if ($this->pregnancy_warning) {
            $warnings[] = [
                'type' => 'pregnancy',
                'title' => 'Pregnancy Warning',
                'description' => $this->pregnancy_warning,
                'severity' => 'high'
            ];
        }

        if ($this->breastfeeding_warning) {
            $warnings[] = [
                'type' => 'breastfeeding',
                'title' => 'Breastfeeding Warning',
                'description' => $this->breastfeeding_warning,
                'severity' => 'medium'
            ];
        }

        if ($this->driving_warning) {
            $warnings[] = [
                'type' => 'driving',
                'title' => 'Driving Warning',
                'description' => $this->driving_warning,
                'severity' => 'medium'
            ];
        }

        if ($this->alcohol_warning) {
            $warnings[] = [
                'type' => 'alcohol',
                'title' => 'Alcohol Warning',
                'description' => $this->alcohol_warning,
                'severity' => 'medium'
            ];
        }

        return $warnings;
    }

    /**
     * Get prescription statistics
     */
    public function getStatisticsAttribute(): array
    {
        return [
            'items_count' => $this->items->count(),
            'refills_remaining' => $this->refills_remaining,
            'days_until_expiry' => $this->expiry_date ? $this->expiry_date->diffInDays(now()) : null,
            'is_active' => $this->isActive(),
            'is_expired' => $this->isExpired(),
            'needs_refill' => $this->needsRefill(),
            'is_verified' => $this->isVerified(),
            'is_pending_verification' => $this->isPendingVerification(),
            'is_rejected' => $this->isRejected(),
            'total_cost' => $this->total_cost,
            'copay_amount' => $this->copay_amount,
            'has_prior_authorization' => $this->prior_authorization,
        ];
    }

    /**
     * Generate unique prescription number
     */
    public static function generatePrescriptionNumber(int $clinicId): string
    {
        $clinic = Clinic::find($clinicId);
        $clinicCode = $clinic ? strtoupper(substr($clinic->name, 0, 3)) : 'CLN';
        
        $date = now()->format('Ymd');
        $sequence = static::where('clinic_id', $clinicId)
            ->whereDate('created_at', today())
            ->count() + 1;

        return sprintf('RX%s%s%04d', $clinicCode, $date, $sequence);
    }

    /**
     * Generate QR hash for prescription
     */
    public static function generateQrHash(): string
    {
        return 'RX' . uniqid() . '_' . time();
    }

    /**
     * Verify prescription
     */
    public function verify(bool $status, string $notes = null): void
    {
        $this->update([
            'verification_status' => $status,
            'verification_notes' => $notes,
            'verification_date' => now()
        ]);
    }

    /**
     * Mark prescription as dispensed
     */
    public function markAsDispensed(): void
    {
        $this->update([
            'status' => 'dispensed',
            'next_refill_date' => $this->calculateNextRefillDate()
        ]);
    }

    /**
     * Calculate next refill date
     */
    public function calculateNextRefillDate(): ?Carbon
    {
        if ($this->refills_remaining <= 0) {
            return null;
        }

        // Default to 30 days from now, can be customized based on medication
        return now()->addDays(30);
    }

    /**
     * Process refill
     */
    public function processRefill(): bool
    {
        if ($this->refills_remaining <= 0) {
            return false;
        }

        $this->update([
            'refills_remaining' => $this->refills_remaining - 1,
            'next_refill_date' => $this->calculateNextRefillDate()
        ]);

        return true;
    }

    /**
     * Get PDF download URL
     */
    public function getPdfDownloadUrlAttribute(): string
    {
        if ($this->pdf_url) {
            return $this->pdf_url;
        }

        // Generate PDF URL if not exists
        return route('prescriptions.download', $this->id);
    }

    /**
     * Get QR code data
     */
    public function getQrCodeDataAttribute(): array
    {
        return [
            'prescription_number' => $this->prescription_number,
            'patient_name' => $this->patient->name ?? 'Unknown',
            'doctor_name' => $this->doctor->user->name ?? 'Unknown',
            'issued_date' => $this->issued_at->format('Y-m-d'),
            'expiry_date' => $this->expiry_date?->format('Y-m-d'),
            'qr_hash' => $this->qr_hash,
            'verification_url' => route('prescriptions.verify', $this->qr_hash),
        ];
    }

    /**
     * Check if prescription can be refilled
     */
    public function canBeRefilled(): bool
    {
        return $this->isActive() && 
               $this->refills_remaining > 0 && 
               !$this->isExpired();
    }

    /**
     * Get prescription cost breakdown
     */
    public function getCostBreakdownAttribute(): array
    {
        $totalCost = $this->total_cost ?? 0;
        $copayAmount = $this->copay_amount ?? 0;
        $insuranceAmount = $totalCost - $copayAmount;

        return [
            'total_cost' => $totalCost,
            'copay_amount' => $copayAmount,
            'insurance_amount' => $insuranceAmount,
            'patient_responsibility' => $copayAmount,
            'has_insurance' => $totalCost > $copayAmount,
        ];
    }

    /**
     * Get the URI key for the model.
     * This method is required for Nova MorphTo relationships.
     *
     * @return string
     */
    public static function uriKey(): string
    {
        return 'prescriptions';
    }
}
