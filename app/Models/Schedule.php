<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Schedule extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'clinic_id',
        'doctor_id',
        'room_id',
        'day_of_week',
        'start_time',
        'end_time',
        'status',
        'is_recurring',
        'recurring_type',
        'recurring_interval',
        'recurring_end_date',
        'notes',
        'max_appointments',
        'appointment_duration',
        'break_duration',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'recurring_end_date' => 'date',
        'is_recurring' => 'boolean',
        'is_active' => 'boolean',
        'max_appointments' => 'integer',
        'appointment_duration' => 'integer',
        'break_duration' => 'integer',
    ];

    protected $dates = [
        'recurring_end_date',
        'deleted_at',
    ];

    // Schedule status constants
    const STATUS_ACTIVE = 'Active';
    const STATUS_INACTIVE = 'Inactive';
    const STATUS_ON_LEAVE = 'On Leave';
    const STATUS_VACATION = 'Vacation';
    const STATUS_SICK_LEAVE = 'Sick Leave';

    // Recurring type constants
    const RECURRING_NONE = 'none';
    const RECURRING_WEEKLY = 'weekly';
    const RECURRING_BIWEEKLY = 'biweekly';
    const RECURRING_MONTHLY = 'monthly';

    // Day of week constants
    const DAY_MONDAY = 'Monday';
    const DAY_TUESDAY = 'Tuesday';
    const DAY_WEDNESDAY = 'Wednesday';
    const DAY_THURSDAY = 'Thursday';
    const DAY_FRIDAY = 'Friday';
    const DAY_SATURDAY = 'Saturday';
    const DAY_SUNDAY = 'Sunday';

    /**
     * Get the clinic that owns the schedule
     */
    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    /**
     * Get the doctor for this schedule
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    /**
     * Get the room for this schedule
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Get appointments for this schedule
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'doctor_id', 'doctor_id')
            ->where('clinic_id', $this->clinic_id);
    }

    /**
     * Get the user who created this schedule
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this schedule
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scope to filter by clinic
     */
    public function scopeForClinic($query, $clinicId)
    {
        return $query->where('clinic_id', $clinicId);
    }

    /**
     * Scope to filter by doctor
     */
    public function scopeForDoctor($query, $doctorId)
    {
        return $query->where('doctor_id', $doctorId);
    }

    /**
     * Scope to filter by day of week
     */
    public function scopeForDay($query, $day)
    {
        return $query->where('day_of_week', $day);
    }

    /**
     * Scope to filter by status
     */
    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter active schedules
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get available time slots for this schedule
     */
    public function getAvailableSlots($date = null)
    {
        $date = $date ? Carbon::parse($date) : Carbon::today();
        $slots = [];
        
        $start = Carbon::parse($this->start_time->format('H:i'));
        $end = Carbon::parse($this->end_time->format('H:i'));
        $duration = $this->appointment_duration ?? 30; // Default 30 minutes
        $break = $this->break_duration ?? 0; // Default no break
        
        while ($start->addMinutes($duration)->lte($end)) {
            $slotStart = $start->copy()->subMinutes($duration);
            $slotEnd = $start->copy();
            
            // Check if this slot conflicts with existing appointments
            $hasConflict = \App\Models\Appointment::where('doctor_id', $this->doctor_id)
                ->where('clinic_id', $this->clinic_id)
                ->whereDate('start_at', $date->format('Y-m-d'))
                ->where(function ($query) use ($slotStart, $slotEnd) {
                    $query->whereBetween(DB::raw('TIME(start_at)'), [$slotStart->format('H:i'), $slotEnd->format('H:i')])
                          ->orWhereBetween(DB::raw('TIME(end_at)'), [$slotStart->format('H:i'), $slotEnd->format('H:i')])
                          ->orWhere(function ($q) use ($slotStart, $slotEnd) {
                              $q->where(DB::raw('TIME(start_at)'), '<=', $slotStart->format('H:i'))
                                ->where(DB::raw('TIME(end_at)'), '>=', $slotEnd->format('H:i'));
                          });
                })
                ->whereIn('status', ['booked', 'arrived', 'in-room'])
                ->exists();
            
            if (!$hasConflict) {
                $slots[] = [
                    'start' => $slotStart->format('H:i'),
                    'end' => $slotEnd->format('H:i'),
                    'available' => true
                ];
            }
            
            // Add break time if specified
            if ($break > 0) {
                $start->addMinutes($break);
            }
        }
        
        return $slots;
    }

    /**
     * Check if schedule is available on a specific date
     */
    public function isAvailableOn($date)
    {
        $date = Carbon::parse($date);
        $dayOfWeek = $date->format('l'); // Full day name
        
        return $this->day_of_week === $dayOfWeek && 
               $this->is_active && 
               $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Get the number of appointments for this schedule
     */
    public function getAppointmentsCountAttribute()
    {
        return $this->appointments()->count();
    }

    /**
     * Get the next available appointment slot
     */
    public function getNextAvailableSlot($date = null)
    {
        $date = $date ? Carbon::parse($date) : Carbon::today();
        $slots = $this->getAvailableSlots($date);
        
        return !empty($slots) ? $slots[0] : null;
    }

    /**
     * Get all available days for this schedule
     */
    public static function getAvailableDays()
    {
        return [
            self::DAY_MONDAY,
            self::DAY_TUESDAY,
            self::DAY_WEDNESDAY,
            self::DAY_THURSDAY,
            self::DAY_FRIDAY,
            self::DAY_SATURDAY,
            self::DAY_SUNDAY,
        ];
    }

    /**
     * Get all available statuses
     */
    public static function getAvailableStatuses()
    {
        return [
            self::STATUS_ACTIVE,
            self::STATUS_INACTIVE,
            self::STATUS_ON_LEAVE,
            self::STATUS_VACATION,
            self::STATUS_SICK_LEAVE,
        ];
    }

    /**
     * Get all recurring types
     */
    public static function getRecurringTypes()
    {
        return [
            self::RECURRING_NONE,
            self::RECURRING_WEEKLY,
            self::RECURRING_BIWEEKLY,
            self::RECURRING_MONTHLY,
        ];
    }
}
