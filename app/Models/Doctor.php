<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Doctor extends Model
{
    protected $fillable = [
        'user_id',
        'clinic_id',
        'specialty',
        'license_no',
        'signature_url',
        'is_active',
        'consultation_fee',
        'availability_schedule',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'consultation_fee' => 'decimal:2',
        'availability_schedule' => 'array',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        // Add any global scopes if needed
    }

    /**
     * Scope to filter by active doctors
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by specialty
     */
    public function scopeBySpecialty(Builder $query, string $specialty): Builder
    {
        return $query->where('specialty', $specialty);
    }

    /**
     * Scope to filter by clinic
     */
    public function scopeByClinic(Builder $query, int $clinicId): Builder
    {
        return $query->where('clinic_id', $clinicId);
    }

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
     * Get the upcoming appointments for this doctor
     */
    public function upcomingAppointments(): HasMany
    {
        return $this->hasMany(Appointment::class)->where('appointment_date', '>=', now());
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

    /**
     * Get doctor's email
     */
    public function getEmailAttribute(): string
    {
        return $this->user->email;
    }

    /**
     * Get doctor's phone
     */
    public function getPhoneAttribute(): string
    {
        return $this->user->phone;
    }

    /**
     * Check if doctor is available on a specific date and time
     */
    public function isAvailable(string $date, string $time = null): bool
    {
        if (!$this->is_active) {
            return false;
        }

        // Check if doctor has any conflicting appointments
        $conflictingAppointments = $this->appointments()
            ->where('appointment_date', $date)
            ->when($time, function ($query) use ($time) {
                $query->where('appointment_time', $time);
            })
            ->exists();

        return !$conflictingAppointments;
    }

    /**
     * Get doctor's availability for a specific date
     */
    public function getAvailabilityForDate(string $date): array
    {
        $dayOfWeek = strtolower(date('l', strtotime($date)));
        $schedule = $this->availability_schedule[$dayOfWeek] ?? null;

        if (!$schedule || $schedule === 'closed') {
            return [];
        }

        // Get booked time slots
        $bookedSlots = $this->appointments()
            ->where('appointment_date', $date)
            ->pluck('appointment_time')
            ->toArray();

        // Filter out booked slots from available schedule
        $availableSlots = array_diff($schedule, $bookedSlots);

        return array_values($availableSlots);
    }

    /**
     * Get doctor's statistics
     */
    public function getStatisticsAttribute(): array
    {
        return [
            'total_appointments' => $this->appointments()->count(),
            'total_encounters' => $this->encounters()->count(),
            'total_prescriptions' => $this->prescriptions()->count(),
            'total_patients' => $this->encounters()->distinct('patient_id')->count(),
        ];
    }

    /**
     * Get doctor's recent patients
     */
    public function getRecentPatientsAttribute()
    {
        return $this->encounters()
            ->with('patient')
            ->latest()
            ->take(10)
            ->get()
            ->pluck('patient')
            ->unique('id');
    }
}
