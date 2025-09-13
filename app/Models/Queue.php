<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Services\SettingsService;
class Queue extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'clinic_id',
        'name',
        'description',
        'queue_type',
        'status',
        'max_capacity',
        'current_count',
        'average_wait_time',
        'estimated_wait_time',
        'priority_level',
        'is_active',
        'auto_assign',
        'settings',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'max_capacity' => 'integer',
        'current_count' => 'integer',
        'average_wait_time' => 'integer',
        'estimated_wait_time' => 'integer',
        'priority_level' => 'integer',
        'is_active' => 'boolean',
        'auto_assign' => 'boolean',
        'settings' => 'array',
    ];

    protected $attributes = [
        'queue_type' => 'general',
        'status' => 'active',
        'max_capacity' => 50,
        'current_count' => 0,
        'average_wait_time' => 0,
        'estimated_wait_time' => 0,
        'priority_level' => 1,
        'is_active' => true,
        'auto_assign' => false,
        'settings' => '{}',
    ];

    // Relationships
    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function patients(): HasMany
    {
        return $this->hasMany(QueuePatient::class);
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

    public function scopeByType($query, string $type)
    {
        return $query->where('queue_type', $type);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeAvailable($query)
    {
        return $query->where('is_active', true)
                    ->where('status', 'active')
                    ->whereRaw('current_count < max_capacity');
    }

    public function scopeFull($query)
    {
        return $query->whereRaw('current_count >= max_capacity');
    }

    public function scopeByPriority($query, int $priority)
    {
        return $query->where('priority_level', $priority);
    }

    public function scopeHighPriority($query)
    {
        return $query->where('priority_level', '>=', 4);
    }

    // Accessors & Mutators
    public function getIsFullAttribute(): bool
    {
        return $this->current_count >= $this->max_capacity;
    }

    public function getIsAvailableAttribute(): bool
    {
        return $this->is_active && $this->status === 'active' && !$this->is_full;
    }

    public function getAvailableSlotsAttribute(): int
    {
        return max(0, $this->max_capacity - $this->current_count);
    }

    public function getWaitTimeFormattedAttribute(): string
    {
        if ($this->estimated_wait_time <= 0) {
            return 'No wait time';
        }

        $hours = floor($this->estimated_wait_time / 60);
        $minutes = $this->estimated_wait_time % 60;

        if ($hours > 0) {
            return $hours . 'h ' . $minutes . 'm';
        }

        return $minutes . ' minutes';
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'active' => 'green',
            'paused' => 'yellow',
            'closed' => 'red',
            'maintenance' => 'gray',
            default => 'blue',
        };
    }

    // Methods
    public function addPatient(int $patientId, int $priority = 1, array $metadata = []): QueuePatient
    {
        if ($this->is_full) {
            throw new \Exception('Queue is at maximum capacity');
        }

        if (!$this->is_active) {
            throw new \Exception('Queue is not active');
        }

        // Get queue settings
        $settingsService = app(SettingsService::class);
        $allowWalkIns = $settingsService->get('queue.allow_walk_ins', true, $this->clinic_id);
        $priorityLevels = $settingsService->get('queue.priority_levels', [1, 2, 3, 4, 5], $this->clinic_id);

        // Validate priority level
        if (!in_array($priority, $priorityLevels)) {
            $priority = 1; // Default to lowest priority
        }

        // Check if walk-ins are allowed for this queue type
        if ($this->queue_type === 'walk_in' && !$allowWalkIns) {
            throw new \Exception('Walk-ins are not allowed at this time');
        }

        $queuePatient = $this->patients()->create([
            'patient_id' => $patientId,
            'priority' => $priority,
            'status' => 'waiting',
            'joined_at' => now(),
            'metadata' => $metadata,
        ]);

        $this->increment('current_count');
        $this->updateWaitTime();

        return $queuePatient;
    }

    public function removePatient(int $patientId): bool
    {
        $queuePatient = $this->patients()
                            ->where('patient_id', $patientId)
                            ->where('status', 'waiting')
                            ->first();

        if (!$queuePatient) {
            return false;
        }

        $queuePatient->update([
            'status' => 'removed',
            'removed_at' => now(),
        ]);

        $this->decrement('current_count');
        $this->updateWaitTime();

        return true;
    }

    public function getNextPatient(): ?QueuePatient
    {
        return $this->patients()
                    ->where('status', 'waiting')
                    ->orderBy('priority', 'desc')
                    ->orderBy('joined_at', 'asc')
                    ->first();
    }

    public function callNextPatient(): ?QueuePatient
    {
        $nextPatient = $this->getNextPatient();

        if (!$nextPatient) {
            return null;
        }

        // Check if auto call next is enabled
        $settingsService = app(SettingsService::class);
        $autoCallNext = $settingsService->get('queue.auto_call_next', false, $this->clinic_id);

        if (!$autoCallNext) {
            // Manual call - just update status
            $nextPatient->update([
                'status' => 'called',
                'called_at' => now(),
            ]);
        } else {
            // Auto call - update status and automatically serve if within max wait time
            $maxWaitTime = $settingsService->get('queue.max_wait_time_minutes', 30, $this->clinic_id);
            $waitTime = now()->diffInMinutes($nextPatient->joined_at);

            $nextPatient->update([
                'status' => $waitTime > $maxWaitTime ? 'served' : 'called',
                'called_at' => now(),
                'served_at' => $waitTime > $maxWaitTime ? now() : null,
            ]);

            if ($waitTime > $maxWaitTime) {
                $this->decrement('current_count');
                $this->updateWaitTime();
            }
        }

        return $nextPatient;
    }

    public function servePatient(int $patientId): bool
    {
        $queuePatient = $this->patients()
                            ->where('patient_id', $patientId)
                            ->whereIn('status', ['waiting', 'called'])
                            ->first();

        if (!$queuePatient) {
            return false;
        }

        $queuePatient->update([
            'status' => 'served',
            'served_at' => now(),
        ]);

        $this->decrement('current_count');
        $this->updateWaitTime();

        return true;
    }

    public function updateWaitTime(): void
    {
        $waitingPatients = $this->patients()
                               ->where('status', 'waiting')
                               ->count();

        if ($waitingPatients === 0) {
            $this->estimated_wait_time = 0;
        } else {
            // Simple estimation: average service time * number of patients ahead
            $averageServiceTime = $this->average_wait_time ?: 15; // Default 15 minutes
            $this->estimated_wait_time = $averageServiceTime * $waitingPatients;
        }

        $this->save();
    }

    public function pause(): void
    {
        $this->status = 'paused';
        $this->save();
    }

    public function resume(): void
    {
        $this->status = 'active';
        $this->save();
    }

    public function close(): void
    {
        $this->status = 'closed';
        $this->save();
    }

    public function open(): void
    {
        $this->status = 'active';
        $this->save();
    }

    public function getStatistics(): array
    {
        $today = now()->startOfDay();
        $tomorrow = now()->addDay()->startOfDay();

        $stats = [
            'total_patients_today' => $this->patients()
                                          ->whereBetween('joined_at', [$today, $tomorrow])
                                          ->count(),
            'served_today' => $this->patients()
                                  ->whereBetween('served_at', [$today, $tomorrow])
                                  ->count(),
            'waiting_now' => $this->patients()
                                 ->where('status', 'waiting')
                                 ->count(),
            'called_now' => $this->patients()
                                ->where('status', 'called')
                                ->count(),
            'average_wait_time' => $this->average_wait_time,
            'estimated_wait_time' => $this->estimated_wait_time,
        ];

        return $stats;
    }

    public function getQueuePosition(int $patientId): ?int
    {
        $queuePatient = $this->patients()
                            ->where('patient_id', $patientId)
                            ->where('status', 'waiting')
                            ->first();

        if (!$queuePatient) {
            return null;
        }

        return $this->patients()
                   ->where('status', 'waiting')
                   ->where(function ($query) use ($queuePatient) {
                       $query->where('priority', '>', $queuePatient->priority)
                             ->orWhere(function ($q) use ($queuePatient) {
                                 $q->where('priority', $queuePatient->priority)
                                   ->where('joined_at', '<=', $queuePatient->joined_at);
                             });
                   })
                   ->count();
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($queue) {
            if (empty($queue->uuid)) {
                $queue->uuid = (string) \Illuminate\Support\Str::uuid();
            }
            if (empty($queue->settings)) {
                $queue->settings = [
                    'allow_priority' => true,
                    'max_priority' => 5,
                    'auto_call_interval' => 5, // minutes
                    'reminder_enabled' => true,
                    'reminder_minutes' => 10,
                ];
            }
        });
    }
}
