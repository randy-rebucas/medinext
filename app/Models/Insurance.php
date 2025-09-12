<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasUuid;

class Insurance extends Model
{
    use HasFactory;

    protected $table = 'insurance';

    protected $fillable = [
        'uuid',
        'patient_id',
        'insurance_provider',
        'policy_number',
        'group_number',
        'member_id',
        'policy_holder_name',
        'policy_holder_relationship',
        'coverage_type',
        'effective_date',
        'expiration_date',
        'copay_amount',
        'deductible_amount',
        'coverage_percentage',
        'is_primary',
        'is_active',
        'verification_status',
        'verification_date',
        'verification_notes',
        'contact_phone',
        'contact_email',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'expiration_date' => 'date',
        'verification_date' => 'datetime',
        'copay_amount' => 'decimal:2',
        'deductible_amount' => 'decimal:2',
        'coverage_percentage' => 'decimal:2',
        'is_primary' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected $attributes = [
        'coverage_type' => 'health',
        'copay_amount' => 0.00,
        'deductible_amount' => 0.00,
        'coverage_percentage' => 100.00,
        'is_primary' => true,
        'is_active' => true,
        'verification_status' => 'pending',
    ];

    // Relationships
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    public function scopeVerified($query)
    {
        return $query->where('verification_status', 'verified');
    }

    public function scopePending($query)
    {
        return $query->where('verification_status', 'pending');
    }

    public function scopeExpired($query)
    {
        return $query->where('expiration_date', '<', now());
    }

    public function scopeExpiring($query, $days = 30)
    {
        return $query->where('expiration_date', '<=', now()->addDays($days))
                    ->where('expiration_date', '>', now());
    }

    // Accessors & Mutators
    public function getIsExpiredAttribute(): bool
    {
        return $this->expiration_date && $this->expiration_date < now();
    }

    public function getIsExpiringAttribute(): bool
    {
        return $this->expiration_date && 
               $this->expiration_date <= now()->addDays(30) && 
               $this->expiration_date > now();
    }

    public function getIsVerifiedAttribute(): bool
    {
        return $this->verification_status === 'verified';
    }

    public function getFullPolicyNumberAttribute(): string
    {
        return $this->policy_number . ($this->group_number ? ' - ' . $this->group_number : '');
    }

    // Methods
    public function verify(string $notes = null): void
    {
        $this->verification_status = 'verified';
        $this->verification_date = now();
        if ($notes) {
            $this->verification_notes = $notes;
        }
        $this->save();
    }

    public function reject(string $notes = null): void
    {
        $this->verification_status = 'rejected';
        $this->verification_date = now();
        if ($notes) {
            $this->verification_notes = $notes;
        }
        $this->save();
    }

    public function setAsPrimary(): void
    {
        // Remove primary status from other insurances for this patient
        static::where('patient_id', $this->patient_id)
              ->where('id', '!=', $this->id)
              ->update(['is_primary' => false]);
        
        $this->is_primary = true;
        $this->save();
    }

    public function calculatePatientResponsibility(float $totalAmount): float
    {
        if (!$this->is_verified || $this->is_expired) {
            return $totalAmount; // Patient pays full amount if insurance not verified or expired
        }

        $deductibleRemaining = max(0, $this->deductible_amount);
        $amountAfterDeductible = max(0, $totalAmount - $deductibleRemaining);
        $insuranceCovers = $amountAfterDeductible * ($this->coverage_percentage / 100);
        $patientResponsibility = $totalAmount - $insuranceCovers + $this->copay_amount;

        return max(0, $patientResponsibility);
    }

    public function getCoverageInfo(): array
    {
        return [
            'provider' => $this->insurance_provider,
            'policy_number' => $this->policy_number,
            'member_id' => $this->member_id,
            'coverage_percentage' => $this->coverage_percentage,
            'copay_amount' => $this->copay_amount,
            'deductible_amount' => $this->deductible_amount,
            'is_primary' => $this->is_primary,
            'is_verified' => $this->is_verified,
            'is_active' => $this->is_active,
            'expiration_date' => $this->expiration_date,
        ];
    }

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($insurance) {
            if (empty($insurance->uuid)) {
                $insurance->uuid = (string) \Illuminate\Support\Str::uuid();
            }
            if ($insurance->is_primary) {
                // Ensure only one primary insurance per patient
                static::where('patient_id', $insurance->patient_id)
                      ->update(['is_primary' => false]);
            }
        });
        
        static::updating(function ($insurance) {
            if ($insurance->is_primary && $insurance->isDirty('is_primary')) {
                // Ensure only one primary insurance per patient
                static::where('patient_id', $insurance->patient_id)
                      ->where('id', '!=', $insurance->id)
                      ->update(['is_primary' => false]);
            }
        });
    }
}
