<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class Appointment extends Model
{
    protected $fillable = [
        'clinic_id',
        'patient_id',
        'doctor_id',
        'start_at',
        'end_at',
        'status',
        'room_id',
        'reason',
        'source',
        'appointment_type',
        'duration',
        'notes',
        'reminder_sent',
        'reminder_sent_at',
        'cancellation_reason',
        'cancelled_by',
        'cancelled_at',
        'check_in_time',
        'check_out_time',
        'wait_time',
        'priority',
        'insurance_info',
        'copay_amount',
        'total_amount',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'duration' => 'integer',
        'reminder_sent' => 'boolean',
        'reminder_sent_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'check_in_time' => 'datetime',
        'check_out_time' => 'datetime',
        'wait_time' => 'integer',
        'copay_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'insurance_info' => 'array',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        // Set end time based on duration if not provided
        static::creating(function ($appointment) {
            if ($appointment->start_at && $appointment->duration && !$appointment->end_at) {
                $appointment->end_at = $appointment->start_at->copy()->addMinutes($appointment->duration);
            }
        });

        // Update end time when start time or duration changes
        static::updating(function ($appointment) {
            if ($appointment->isDirty(['start_at', 'duration']) && $appointment->start_at && $appointment->duration) {
                $appointment->end_at = $appointment->start_at->copy()->addMinutes($appointment->duration);
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
     * Scope to filter by date range
     */
    public function scopeByDateRange(Builder $query, Carbon $startDate, Carbon $endDate): Builder
    {
        return $query->whereBetween('start_at', [$startDate, $endDate]);
    }

    /**
     * Scope to filter by appointment type
     */
    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('appointment_type', $type);
    }

    /**
     * Scope to filter today's appointments
     */
    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('start_at', Carbon::today());
    }

    /**
     * Scope to filter upcoming appointments
     */
    public function scopeUpcoming(Builder $query, int $days = 7): Builder
    {
        return $query->where('start_at', '>=', Carbon::now())
            ->where('start_at', '<=', Carbon::now()->addDays($days));
    }

    /**
     * Scope to filter past appointments
     */
    public function scopePast(Builder $query, int $days = 30): Builder
    {
        return $query->where('start_at', '<', Carbon::now())
            ->where('start_at', '>=', Carbon::now()->subDays($days));
    }

    /**
     * Scope to filter by priority
     */
    public function scopeByPriority(Builder $query, string $priority): Builder
    {
        return $query->where('priority', $priority);
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

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Get the encounter associated with this appointment
     */
    public function encounter(): BelongsTo
    {
        return $this->belongsTo(Encounter::class);
    }

    /**
     * Get appointment type display name
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
            'lab_test' => 'Lab Test',
            'imaging' => 'Imaging',
            'physical_therapy' => 'Physical Therapy',
        ];

        return $types[$this->appointment_type] ?? ucfirst($this->appointment_type);
    }

    /**
     * Get appointment status display name
     */
    public function getStatusDisplayNameAttribute(): string
    {
        $statuses = [
            'scheduled' => 'Scheduled',
            'confirmed' => 'Confirmed',
            'in_progress' => 'In Progress',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            'no_show' => 'No Show',
            'rescheduled' => 'Rescheduled',
            'waiting' => 'Waiting',
            'checked_in' => 'Checked In',
            'checked_out' => 'Checked Out',
        ];

        return $statuses[$this->status] ?? ucfirst($this->status);
    }

    /**
     * Get priority display name
     */
    public function getPriorityDisplayNameAttribute(): string
    {
        $priorities = [
            'low' => 'Low',
            'normal' => 'Normal',
            'high' => 'High',
            'urgent' => 'Urgent',
            'emergency' => 'Emergency',
        ];

        return $priorities[$this->priority] ?? ucfirst($this->priority);
    }

    /**
     * Get source display name
     */
    public function getSourceDisplayNameAttribute(): string
    {
        $sources = [
            'phone' => 'Phone',
            'online' => 'Online',
            'walk_in' => 'Walk-in',
            'referral' => 'Referral',
            'emergency' => 'Emergency',
            'follow_up' => 'Follow-up',
        ];

        return $sources[$this->source] ?? ucfirst($this->source);
    }

    /**
     * Check if appointment is scheduled
     */
    public function isScheduled(): bool
    {
        return in_array($this->status, ['scheduled', 'confirmed']);
    }

    /**
     * Check if appointment is in progress
     */
    public function isInProgress(): bool
    {
        return in_array($this->status, ['in_progress', 'waiting', 'checked_in']);
    }

    /**
     * Check if appointment is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if appointment is cancelled
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Check if appointment is no show
     */
    public function isNoShow(): bool
    {
        return $this->status === 'no_show';
    }

    /**
     * Check if appointment is overdue
     */
    public function isOverdue(): bool
    {
        return $this->start_at->isPast() && $this->isScheduled();
    }

    /**
     * Check if appointment is today
     */
    public function isToday(): bool
    {
        return $this->start_at->isToday();
    }

    /**
     * Check if appointment is tomorrow
     */
    public function isTomorrow(): bool
    {
        return $this->start_at->isTomorrow();
    }

    /**
     * Check if appointment is this week
     */
    public function isThisWeek(): bool
    {
        return $this->start_at->isCurrentWeek();
    }

    /**
     * Get appointment duration in minutes
     */
    public function getDurationInMinutesAttribute(): int
    {
        if ($this->duration) {
            return $this->duration;
        }

        if ($this->start_at && $this->end_at) {
            return $this->start_at->diffInMinutes($this->end_at);
        }

        // Default duration based on type
        $defaultDurations = [
            'consultation' => 30,
            'follow_up' => 20,
            'emergency' => 60,
            'routine_checkup' => 45,
            'specialist_consultation' => 45,
            'procedure' => 90,
            'surgery' => 180,
            'lab_test' => 15,
            'imaging' => 30,
            'physical_therapy' => 60,
        ];

        return $defaultDurations[$this->appointment_type] ?? 30;
    }

    /**
     * Get appointment cost
     */
    public function getCostAttribute(): float
    {
        if ($this->total_amount) {
            return (float) $this->total_amount;
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
            'lab_test' => 25.00,
            'imaging' => 150.00,
            'physical_therapy' => 80.00,
        ];

        return $defaultCosts[$this->appointment_type] ?? 50.00;
    }

    /**
     * Get appointment summary
     */
    public function getSummaryAttribute(): array
    {
        return [
            'id' => $this->id,
            'patient_name' => $this->patient->name ?? 'Unknown',
            'doctor_name' => $this->doctor->user->name ?? 'Unknown',
            'date' => $this->start_at->format('Y-m-d'),
            'time' => $this->start_at->format('H:i'),
            'duration' => $this->duration_in_minutes,
            'type' => $this->type_display_name,
            'status' => $this->status_display_name,
            'priority' => $this->priority_display_name,
            'room' => $this->room->name ?? 'Not Assigned',
            'reason' => $this->reason,
            'cost' => $this->cost,
        ];
    }

    /**
     * Get appointment timeline
     */
    public function getTimelineAttribute(): array
    {
        $timeline = [];

        // Appointment creation
        $timeline[] = [
            'date' => $this->created_at->format('Y-m-d H:i'),
            'event' => 'Appointment Created',
            'description' => "Appointment scheduled for {$this->patient->name}",
            'type' => 'creation'
        ];

        // Status changes
        if ($this->status !== 'scheduled') {
            $timeline[] = [
                'date' => $this->updated_at->format('Y-m-d H:i'),
                'event' => 'Status Updated',
                'description' => "Status changed to {$this->status_display_name}",
                'type' => 'status_change'
            ];
        }

        // Check-in
        if ($this->check_in_time) {
            $timeline[] = [
                'date' => $this->check_in_time->format('Y-m-d H:i'),
                'event' => 'Patient Checked In',
                'description' => "Patient checked in at {$this->check_in_time->format('H:i')}",
                'type' => 'check_in'
            ];
        }

        // Check-out
        if ($this->check_out_time) {
            $timeline[] = [
                'date' => $this->check_out_time->format('Y-m-d H:i'),
                'event' => 'Patient Checked Out',
                'description' => "Patient checked out at {$this->check_out_time->format('H:i')}",
                'type' => 'check_out'
            ];
        }

        // Cancellation
        if ($this->cancelled_at) {
            $timeline[] = [
                'date' => $this->cancelled_at->format('Y-m-d H:i'),
                'event' => 'Appointment Cancelled',
                'description' => "Cancelled by {$this->cancelled_by} - {$this->cancellation_reason}",
                'type' => 'cancellation'
            ];
        }

        // Reminder sent
        if ($this->reminder_sent_at) {
            $timeline[] = [
                'date' => $this->reminder_sent_at->format('Y-m-d H:i'),
                'event' => 'Reminder Sent',
                'description' => "Appointment reminder sent to patient",
                'type' => 'reminder'
            ];
        }

        // Sort by date
        usort($timeline, function ($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });

        return $timeline;
    }

    /**
     * Check if appointment conflicts with another
     */
    public function conflictsWith(Appointment $other): bool
    {
        if ($this->id === $other->id) {
            return false;
        }

        if ($this->doctor_id !== $other->doctor_id) {
            return false;
        }

        if ($this->room_id && $other->room_id && $this->room_id === $other->room_id) {
            return $this->start_at < $other->end_at && $this->end_at > $other->start_at;
        }

        return $this->start_at < $other->end_at && $this->end_at > $other->start_at;
    }

    /**
     * Check if time slot is available
     */
    public static function isTimeSlotAvailable(int $doctorId, Carbon $startTime, int $duration, ?int $roomId = null, ?int $excludeAppointmentId = null): bool
    {
        $endTime = $startTime->copy()->addMinutes($duration);

        $query = static::where('doctor_id', $doctorId)
            ->where('status', '!=', 'cancelled')
            ->where('status', '!=', 'no_show')
            ->where(function ($q) use ($startTime, $endTime) {
                $q->where(function ($subQ) use ($startTime, $endTime) {
                    $subQ->where('start_at', '<', $endTime)
                        ->where('end_at', '>', $startTime);
                });
            });

        if ($excludeAppointmentId) {
            $query->where('id', '!=', $excludeAppointmentId);
        }

        if ($roomId) {
            $query->where('room_id', $roomId);
        }

        return $query->count() === 0;
    }

    /**
     * Get available time slots for a doctor
     */
    public static function getAvailableTimeSlots(int $doctorId, Carbon $date, int $duration = 30, ?int $roomId = null): array
    {
        $startOfDay = $date->copy()->startOfDay();
        $endOfDay = $date->copy()->endOfDay();
        
        $timeSlots = [];
        $currentTime = $startOfDay->copy()->addHours(8); // Start at 8 AM
        $endTime = $startOfDay->copy()->addHours(17); // End at 5 PM

        while ($currentTime < $endTime) {
            if (static::isTimeSlotAvailable($doctorId, $currentTime, $duration, $roomId)) {
                $timeSlots[] = [
                    'start_time' => $currentTime->format('H:i'),
                    'end_time' => $currentTime->copy()->addMinutes($duration)->format('H:i'),
                    'available' => true
                ];
            } else {
                $timeSlots[] = [
                    'start_time' => $currentTime->format('H:i'),
                    'end_time' => $currentTime->copy()->addMinutes($duration)->format('H:i'),
                    'available' => false
                ];
            }
            
            $currentTime->addMinutes($duration);
        }

        return $timeSlots;
    }

    /**
     * Get appointment statistics
     */
    public function getStatisticsAttribute(): array
    {
        return [
            'duration_minutes' => $this->duration_in_minutes,
            'cost' => $this->cost,
            'is_overdue' => $this->isOverdue(),
            'is_today' => $this->isToday(),
            'is_tomorrow' => $this->isTomorrow(),
            'is_this_week' => $this->isThisWeek(),
            'wait_time_minutes' => $this->wait_time,
            'has_reminder' => $this->reminder_sent,
            'is_checked_in' => !is_null($this->check_in_time),
            'is_checked_out' => !is_null($this->check_out_time),
        ];
    }

    /**
     * Calculate wait time
     */
    public function calculateWaitTime(): int
    {
        if (!$this->check_in_time || !$this->start_at) {
            return 0;
        }

        $waitTime = $this->check_in_time->diffInMinutes($this->start_at);
        return max(0, $waitTime);
    }

    /**
     * Mark appointment as checked in
     */
    public function checkIn(): void
    {
        $this->update([
            'check_in_time' => now(),
            'status' => 'checked_in',
            'wait_time' => $this->calculateWaitTime()
        ]);
    }

    /**
     * Mark appointment as checked out
     */
    public function checkOut(): void
    {
        $this->update([
            'check_out_time' => now(),
            'status' => 'checked_out'
        ]);
    }

    /**
     * Cancel appointment
     */
    public function cancel(string $reason, string $cancelledBy): void
    {
        $this->update([
            'status' => 'cancelled',
            'cancellation_reason' => $reason,
            'cancelled_by' => $cancelledBy,
            'cancelled_at' => now()
        ]);
    }

    /**
     * Reschedule appointment
     */
    public function reschedule(Carbon $newStartTime, ?int $newRoomId = null): void
    {
        $this->update([
            'start_at' => $newStartTime,
            'end_at' => $newStartTime->copy()->addMinutes($this->duration_in_minutes),
            'room_id' => $newRoomId ?? $this->room_id,
            'status' => 'rescheduled'
        ]);
    }

    /**
     * Send reminder
     */
    public function sendReminder(): void
    {
        $this->update([
            'reminder_sent' => true,
            'reminder_sent_at' => now()
        ]);
    }
}
