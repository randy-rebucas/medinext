<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasUuid;

class QueuePatient extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'uuid',
        'queue_id',
        'patient_id',
        'priority',
        'status',
        'joined_at',
        'called_at',
        'served_at',
        'removed_at',
        'estimated_wait_time',
        'actual_wait_time',
        'notes',
        'metadata',
        'created_by',
    ];

    protected $casts = [
        'priority' => 'integer',
        'joined_at' => 'datetime',
        'called_at' => 'datetime',
        'served_at' => 'datetime',
        'removed_at' => 'datetime',
        'estimated_wait_time' => 'integer',
        'actual_wait_time' => 'integer',
        'metadata' => 'array',
    ];

    protected $attributes = [
        'priority' => 1,
        'status' => 'waiting',
        'estimated_wait_time' => 0,
        'actual_wait_time' => 0,
        'metadata' => '{}',
    ];

    // Relationships
    public function queue(): BelongsTo
    {
        return $this->belongsTo(Queue::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeWaiting($query)
    {
        return $query->where('status', 'waiting');
    }

    public function scopeCalled($query)
    {
        return $query->where('status', 'called');
    }

    public function scopeServed($query)
    {
        return $query->where('status', 'served');
    }

    public function scopeRemoved($query)
    {
        return $query->where('status', 'removed');
    }

    public function scopeByPriority($query, int $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeHighPriority($query)
    {
        return $query->where('priority', '>=', 4);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('joined_at', today());
    }

    public function scopeByDate($query, $date)
    {
        return $query->whereDate('joined_at', $date);
    }

    // Accessors & Mutators
    public function getIsWaitingAttribute(): bool
    {
        return $this->status === 'waiting';
    }

    public function getIsCalledAttribute(): bool
    {
        return $this->status === 'called';
    }

    public function getIsServedAttribute(): bool
    {
        return $this->status === 'served';
    }

    public function getIsRemovedAttribute(): bool
    {
        return $this->status === 'removed';
    }

    public function getWaitTimeAttribute(): int
    {
        if ($this->served_at) {
            return $this->joined_at->diffInMinutes($this->served_at);
        }

        if ($this->called_at) {
            return $this->joined_at->diffInMinutes($this->called_at);
        }

        return $this->joined_at->diffInMinutes(now());
    }

    public function getWaitTimeFormattedAttribute(): string
    {
        $minutes = $this->wait_time;
        
        if ($minutes < 60) {
            return $minutes . ' minutes';
        }

        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;

        return $hours . 'h ' . $remainingMinutes . 'm';
    }

    public function getQueuePositionAttribute(): int
    {
        return $this->queue->patients()
                          ->where('status', 'waiting')
                          ->where(function ($query) {
                              $query->where('priority', '>', $this->priority)
                                    ->orWhere(function ($q) {
                                        $q->where('priority', $this->priority)
                                          ->where('joined_at', '<=', $this->joined_at);
                                    });
                          })
                          ->count() + 1;
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'waiting' => 'yellow',
            'called' => 'blue',
            'served' => 'green',
            'removed' => 'red',
            default => 'gray',
        };
    }

    public function getStatusTextAttribute(): string
    {
        return match ($this->status) {
            'waiting' => 'Waiting',
            'called' => 'Called',
            'served' => 'Served',
            'removed' => 'Removed',
            default => 'Unknown',
        };
    }

    // Methods
    public function call(): void
    {
        if ($this->status === 'waiting') {
            $this->update([
                'status' => 'called',
                'called_at' => now(),
            ]);
        }
    }

    public function serve(): void
    {
        if (in_array($this->status, ['waiting', 'called'])) {
            $this->update([
                'status' => 'served',
                'served_at' => now(),
                'actual_wait_time' => $this->wait_time,
            ]);
        }
    }

    public function remove(string $reason = null): void
    {
        if ($this->status !== 'served') {
            $this->update([
                'status' => 'removed',
                'removed_at' => now(),
                'notes' => $reason ? ($this->notes . "\nRemoved: " . $reason) : $this->notes,
            ]);
        }
    }

    public function updatePriority(int $priority): void
    {
        $this->update(['priority' => $priority]);
    }

    public function addNote(string $note): void
    {
        $this->update([
            'notes' => $this->notes ? $this->notes . "\n" . $note : $note,
        ]);
    }

    public function updateMetadata(array $metadata): void
    {
        $currentMetadata = $this->metadata ?: [];
        $this->update([
            'metadata' => array_merge($currentMetadata, $metadata),
        ]);
    }

    public function getMetadata(string $key = null, $default = null)
    {
        if ($key === null) {
            return $this->metadata ?: [];
        }

        return data_get($this->metadata, $key, $default);
    }

    public function setMetadata(string $key, $value): void
    {
        $metadata = $this->metadata ?: [];
        data_set($metadata, $key, $value);
        $this->update(['metadata' => $metadata]);
    }

    public function isOverdue(int $maxWaitTime = 60): bool
    {
        return $this->status === 'waiting' && $this->wait_time > $maxWaitTime;
    }

    public function getEstimatedServiceTime(): int
    {
        // Simple estimation based on queue statistics
        $queueStats = $this->queue->getStatistics();
        $averageServiceTime = $queueStats['average_wait_time'] ?: 15;
        
        return $averageServiceTime * $this->queue_position;
    }

    public function getEstimatedServiceTimeFormatted(): string
    {
        $minutes = $this->getEstimatedServiceTime();
        
        if ($minutes < 60) {
            return $minutes . ' minutes';
        }

        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;

        return $hours . 'h ' . $remainingMinutes . 'm';
    }

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($queuePatient) {
            if (!$queuePatient->joined_at) {
                $queuePatient->joined_at = now();
            }
        });
        
        static::updating(function ($queuePatient) {
            // Update actual wait time when status changes to served
            if ($queuePatient->isDirty('status') && $queuePatient->status === 'served') {
                $queuePatient->actual_wait_time = $queuePatient->wait_time;
            }
        });
    }
}
