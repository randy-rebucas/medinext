<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Services\SettingsService;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'read_at',
        'sent_at',
        'delivery_method',
        'delivery_status',
        'priority',
        'expires_at',
        'notifiable_type',
        'notifiable_id',
        'created_by',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
        'sent_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    protected $attributes = [
        'type' => 'info',
        'priority' => 'normal',
        'delivery_method' => 'database',
        'delivery_status' => 'pending',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByPriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeHighPriority($query)
    {
        return $query->where('priority', 'high');
    }

    public function scopeUrgent($query)
    {
        return $query->where('priority', 'urgent');
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', now());
    }

    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    public function scopeSent($query)
    {
        return $query->whereNotNull('sent_at');
    }

    public function scopePending($query)
    {
        return $query->whereNull('sent_at');
    }

    // Accessors & Mutators
    public function getIsReadAttribute(): bool
    {
        return !is_null($this->read_at);
    }

    public function getIsUnreadAttribute(): bool
    {
        return is_null($this->read_at);
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->expires_at && $this->expires_at < now();
    }

    public function getIsSentAttribute(): bool
    {
        return !is_null($this->sent_at);
    }

    public function getIsPendingAttribute(): bool
    {
        return is_null($this->sent_at);
    }

    public function getFormattedMessageAttribute(): string
    {
        // Replace placeholders in message with actual data
        $message = $this->message;
        if ($this->data) {
            foreach ($this->data as $key => $value) {
                $message = str_replace("{{$key}}", $value, $message);
            }
        }
        return $message;
    }

    // Methods
    public function markAsRead(): void
    {
        if (!$this->is_read) {
            $this->read_at = now();
            $this->save();
        }
    }

    public function markAsUnread(): void
    {
        $this->read_at = null;
        $this->save();
    }

    public function markAsSent(): void
    {
        if (!$this->is_sent) {
            $this->sent_at = now();
            $this->delivery_status = 'sent';
            $this->save();
        }
    }

    public function markAsDelivered(): void
    {
        $this->delivery_status = 'delivered';
        $this->save();
    }

    public function markAsFailed(): void
    {
        $this->delivery_status = 'failed';
        $this->save();
    }

    public function setPriority(string $priority): void
    {
        $this->priority = $priority;
        $this->save();
    }

    public function setExpiration(int $minutes): void
    {
        $this->expires_at = now()->addMinutes($minutes);
        $this->save();
    }

    public function extendExpiration(int $minutes): void
    {
        if ($this->expires_at) {
            $this->expires_at = $this->expires_at->addMinutes($minutes);
        } else {
            $this->expires_at = now()->addMinutes($minutes);
        }
        $this->save();
    }

    // Static methods for creating notifications
    public static function createForUser(
        int $userId,
        string $type,
        string $title,
        string $message,
        array $data = [],
        string $priority = 'normal',
        int $createdBy = null,
        ?int $clinicId = null
    ): self {
        $settingsService = app(SettingsService::class);

        // Check if email notifications are enabled
        $emailEnabled = $settingsService->get('notifications.email_enabled', true, $clinicId);
        $smsEnabled = $settingsService->get('notifications.sms_enabled', false, $clinicId);

        // Determine delivery method based on settings
        $deliveryMethod = 'database'; // Default to database
        if ($emailEnabled && $type === 'appointment_reminder') {
            $deliveryMethod = 'email';
        } elseif ($smsEnabled && $type === 'appointment_reminder') {
            $deliveryMethod = 'sms';
        }

        return static::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'priority' => $priority,
            'delivery_method' => $deliveryMethod,
            'created_by' => $createdBy,
        ]);
    }

    public static function createForAllUsers(
        string $type,
        string $title,
        string $message,
        array $data = [],
        string $priority = 'normal',
        int $createdBy = null
    ): void {
        $userIds = User::pluck('id');

        foreach ($userIds as $userId) {
            static::createForUser($userId, $type, $title, $message, $data, $priority, $createdBy);
        }
    }

    public static function createForRole(
        string $roleName,
        string $type,
        string $title,
        string $message,
        array $data = [],
        string $priority = 'normal',
        int $createdBy = null
    ): void {
        $userIds = User::whereHas('roles', function ($query) use ($roleName) {
            $query->where('name', $roleName);
        })->pluck('id');

        foreach ($userIds as $userId) {
            static::createForUser($userId, $type, $title, $message, $data, $priority, $createdBy);
        }
    }

    public static function createForClinic(
        int $clinicId,
        string $type,
        string $title,
        string $message,
        array $data = [],
        string $priority = 'normal',
        int $createdBy = null
    ): void {
        $userIds = User::whereHas('clinics', function ($query) use ($clinicId) {
            $query->where('clinic_id', $clinicId);
        })->pluck('id');

        foreach ($userIds as $userId) {
            static::createForUser($userId, $type, $title, $message, $data, $priority, $createdBy);
        }
    }

    // Cleanup methods
    public static function cleanupExpired(): int
    {
        return static::where('expires_at', '<', now())->delete();
    }

    public static function cleanupOldRead(int $days = 30): int
    {
        return static::whereNotNull('read_at')
                    ->where('read_at', '<', now()->subDays($days))
                    ->delete();
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($notification) {
            // Set default expiration if not set
            if (!$notification->expires_at && $notification->priority === 'urgent') {
                $notification->expires_at = now()->addHours(24);
            }
        });
    }
}
