<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Room extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'clinic_id',
        'name',
        'type',
        'capacity',
        'status',
        'location',
        'description',
        'equipment',
        'maintenance_notes',
        'special_requirements',
        'is_active',
        'floor_number',
        'wing',
        'accessibility_features',
        'cleaning_schedule',
        'last_maintenance_date',
        'next_maintenance_date',
    ];

    protected $casts = [
        'equipment' => 'array',
        'accessibility_features' => 'array',
        'is_active' => 'boolean',
        'last_maintenance_date' => 'datetime',
        'next_maintenance_date' => 'datetime',
    ];

    protected $dates = [
        'last_maintenance_date',
        'next_maintenance_date',
        'deleted_at',
    ];

    // Room status constants
    const STATUS_AVAILABLE = 'Available';
    const STATUS_OCCUPIED = 'Occupied';
    const STATUS_MAINTENANCE = 'Maintenance';
    const STATUS_OUT_OF_SERVICE = 'Out of Service';
    const STATUS_CLEANING = 'Cleaning';

    // Room type constants
    const TYPE_CONSULTATION = 'Consultation';
    const TYPE_EXAMINATION = 'Examination';
    const TYPE_PROCEDURE = 'Procedure';
    const TYPE_SURGERY = 'Surgery';
    const TYPE_RECOVERY = 'Recovery';
    const TYPE_EMERGENCY = 'Emergency';
    const TYPE_WAITING = 'Waiting';
    const TYPE_STORAGE = 'Storage';

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function upcomingAppointments(): HasMany
    {
        return $this->appointments()
            ->where('start_at', '>=', now())
            ->whereIn('status', ['booked', 'arrived'])
            ->orderBy('start_at');
    }

    public function currentAppointment(): HasMany
    {
        return $this->appointments()
            ->where('start_at', '<=', now())
            ->where('end_at', '>=', now())
            ->whereIn('status', ['in-room', 'arrived']);
    }

    public function encounters(): HasMany
    {
        return $this->hasMany(Encounter::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', self::STATUS_AVAILABLE);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByClinic($query, $clinicId)
    {
        return $query->where('clinic_id', $clinicId);
    }

    // Accessors
    public function getIsAvailableAttribute(): bool
    {
        return $this->status === self::STATUS_AVAILABLE && $this->is_active;
    }

    public function getCurrentAppointmentAttribute()
    {
        return $this->currentAppointment()->with('patient', 'doctor')->first();
    }

    public function getNextAppointmentAttribute()
    {
        return $this->upcomingAppointments()->with('patient', 'doctor')->first();
    }

    public function getEquipmentListAttribute(): string
    {
        return is_array($this->equipment) ? implode(', ', $this->equipment) : '';
    }

    public function getFullLocationAttribute(): string
    {
        $parts = array_filter([
            $this->floor_number ? "Floor {$this->floor_number}" : null,
            $this->wing,
            $this->location
        ]);
        
        return implode(', ', $parts);
    }

    // Mutators
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = ucwords(trim($value));
    }

    public function setLocationAttribute($value)
    {
        $this->attributes['location'] = $value ? ucwords(trim($value)) : null;
    }

    // Helper methods
    public function isAvailable(): bool
    {
        return $this->is_available;
    }

    public function isOccupied(): bool
    {
        return $this->status === self::STATUS_OCCUPIED;
    }

    public function isUnderMaintenance(): bool
    {
        return $this->status === self::STATUS_MAINTENANCE;
    }

    public function canBeBooked(): bool
    {
        return $this->is_active && 
               in_array($this->status, [self::STATUS_AVAILABLE, self::STATUS_CLEANING]);
    }

    public function isAvailableForTimeSlot($startTime, $endTime, $excludeAppointmentId = null): bool
    {
        if (!$this->canBeBooked()) {
            return false;
        }

        $query = $this->appointments()
            ->where(function ($q) use ($startTime, $endTime) {
                $q->whereBetween('start_at', [$startTime, $endTime])
                  ->orWhereBetween('end_at', [$startTime, $endTime])
                  ->orWhere(function ($subQ) use ($startTime, $endTime) {
                      $subQ->where('start_at', '<=', $startTime)
                           ->where('end_at', '>=', $endTime);
                  });
            })
            ->whereIn('status', ['booked', 'arrived', 'in-room']);

        if ($excludeAppointmentId) {
            $query->where('id', '!=', $excludeAppointmentId);
        }

        return $query->count() === 0;
    }

    public function getNextAvailableSlot($duration = 30): ?string
    {
        $now = now();
        $endOfDay = now()->endOfDay();
        
        // Check every 15 minutes for the next available slot
        for ($time = $now->copy()->startOfHour(); $time->lte($endOfDay); $time->addMinutes(15)) {
            $endTime = $time->copy()->addMinutes($duration);
            
            if ($this->isAvailableForTimeSlot($time, $endTime)) {
                return $time->format('Y-m-d H:i:s');
            }
        }
        
        return null;
    }

    public function getAvailabilityForDate($date): array
    {
        $startOfDay = \Carbon\Carbon::parse($date)->startOfDay();
        $endOfDay = \Carbon\Carbon::parse($date)->endOfDay();
        
        $appointments = $this->appointments()
            ->whereBetween('start_at', [$startOfDay, $endOfDay])
            ->whereIn('status', ['booked', 'arrived', 'in-room'])
            ->orderBy('start_at')
            ->get();

        $availability = [];
        $currentTime = $startOfDay->copy();
        
        while ($currentTime->lte($endOfDay)) {
            $slotEnd = $currentTime->copy()->addMinutes(30);
            
            $isAvailable = $this->canBeBooked() && 
                          $appointments->where('start_at', '<=', $currentTime)
                                      ->where('end_at', '>', $currentTime)
                                      ->isEmpty();
            
            $availability[] = [
                'time' => $currentTime->format('H:i'),
                'available' => $isAvailable,
                'appointment' => $appointments->where('start_at', '<=', $currentTime)
                                            ->where('end_at', '>', $currentTime)
                                            ->first()
            ];
            
            $currentTime->addMinutes(30);
        }
        
        return $availability;
    }

    public function getStatusColor(): string
    {
        return match($this->status) {
            self::STATUS_AVAILABLE => 'green',
            self::STATUS_OCCUPIED => 'orange',
            self::STATUS_MAINTENANCE => 'red',
            self::STATUS_OUT_OF_SERVICE => 'gray',
            self::STATUS_CLEANING => 'blue',
            default => 'gray'
        };
    }

    public function getTypeIcon(): string
    {
        return match($this->type) {
            self::TYPE_CONSULTATION => 'stethoscope',
            self::TYPE_EXAMINATION => 'activity',
            self::TYPE_PROCEDURE => 'syringe',
            self::TYPE_SURGERY => 'scissors',
            self::TYPE_RECOVERY => 'bed',
            self::TYPE_EMERGENCY => 'alert-triangle',
            self::TYPE_WAITING => 'users',
            self::TYPE_STORAGE => 'package',
            default => 'building'
        };
    }

    // Static methods
    public static function getStatusOptions(): array
    {
        return [
            self::STATUS_AVAILABLE => 'Available',
            self::STATUS_OCCUPIED => 'Occupied',
            self::STATUS_MAINTENANCE => 'Maintenance',
            self::STATUS_OUT_OF_SERVICE => 'Out of Service',
            self::STATUS_CLEANING => 'Cleaning',
        ];
    }

    public static function getTypeOptions(): array
    {
        return [
            self::TYPE_CONSULTATION => 'Consultation',
            self::TYPE_EXAMINATION => 'Examination',
            self::TYPE_PROCEDURE => 'Procedure',
            self::TYPE_SURGERY => 'Surgery',
            self::TYPE_RECOVERY => 'Recovery',
            self::TYPE_EMERGENCY => 'Emergency',
            self::TYPE_WAITING => 'Waiting',
            self::TYPE_STORAGE => 'Storage',
        ];
    }

    public static function getEquipmentOptions(): array
    {
        return [
            'Examination Table',
            'Computer',
            'Printer',
            'Medical Equipment',
            'Surgical Table',
            'Anesthesia Machine',
            'Monitor',
            'Defibrillator',
            'X-Ray Machine',
            'Ultrasound Machine',
            'Blood Pressure Monitor',
            'Thermometer',
            'Stethoscope',
            'Syringe',
            'IV Stand',
            'Wheelchair Access',
            'Handicap Accessible',
            'Air Conditioning',
            'Heating',
            'Ventilation System',
            'Emergency Button',
            'Intercom System',
            'Security Camera',
            'Fire Extinguisher',
            'First Aid Kit',
        ];
    }
}
