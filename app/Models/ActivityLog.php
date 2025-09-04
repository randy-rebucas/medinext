<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class ActivityLog extends Model
{
    protected $fillable = [
        'clinic_id',
        'actor_user_id',
        'entity',
        'entity_id',
        'action',
        'at',
        'ip',
        'meta',
        'before_hash',
        'after_hash',
    ];

    protected $casts = [
        'at' => 'datetime',
        'meta' => 'array',
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
     * Scope to filter by entity type
     */
    public function scopeByEntity(Builder $query, string $entity): Builder
    {
        return $query->where('entity', $entity);
    }

    /**
     * Scope to filter by action type
     */
    public function scopeByAction(Builder $query, string $action): Builder
    {
        return $query->where('action', $action);
    }

    /**
     * Scope to filter by clinic
     */
    public function scopeByClinic(Builder $query, int $clinicId): Builder
    {
        return $query->where('clinic_id', $clinicId);
    }

    /**
     * Scope to filter by actor
     */
    public function scopeByActor(Builder $query, int $userId): Builder
    {
        return $query->where('actor_user_id', $userId);
    }

    /**
     * Scope to filter by date range
     */
    public function scopeByDateRange(Builder $query, Carbon $startDate, Carbon $endDate): Builder
    {
        return $query->whereBetween('at', [$startDate, $endDate]);
    }

    /**
     * Scope to filter recent activities
     */
    public function scopeRecent(Builder $query, int $days = 7): Builder
    {
        return $query->where('at', '>=', Carbon::now()->subDays($days));
    }

    /**
     * Scope to filter today's activities
     */
    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('at', Carbon::today());
    }

    /**
     * Scope to filter this week's activities
     */
    public function scopeThisWeek(Builder $query): Builder
    {
        return $query->whereBetween('at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
    }

    /**
     * Scope to filter this month's activities
     */
    public function scopeThisMonth(Builder $query): Builder
    {
        return $query->whereMonth('at', Carbon::now()->month);
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }

    /**
     * Get activity category
     */
    public function getCategoryAttribute(): string
    {
        $categories = [
            'User' => 'User Management',
            'Patient' => 'Patient Care',
            'Doctor' => 'Medical Staff',
            'Appointment' => 'Scheduling',
            'Prescription' => 'Medications',
            'LabResult' => 'Laboratory',
            'Clinic' => 'Clinic Management',
            'Setting' => 'System Settings',
            'Role' => 'Access Control',
            'Permission' => 'Security',
            'Billing' => 'Financial',
            'Report' => 'Analytics',
        ];

        return $categories[$this->entity] ?? 'Other';
    }

    /**
     * Get action display name
     */
    public function getActionDisplayNameAttribute(): string
    {
        $actions = [
            'created' => 'Created',
            'updated' => 'Updated',
            'deleted' => 'Deleted',
            'viewed' => 'Viewed',
            'exported' => 'Exported',
            'imported' => 'Imported',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'scheduled' => 'Scheduled',
            'cancelled' => 'Cancelled',
            'completed' => 'Completed',
            'assigned' => 'Assigned',
            'unassigned' => 'Unassigned',
            'logged_in' => 'Logged In',
            'logged_out' => 'Logged Out',
            'password_changed' => 'Password Changed',
            'role_assigned' => 'Role Assigned',
            'permission_granted' => 'Permission Granted',
            'file_uploaded' => 'File Uploaded',
            'file_downloaded' => 'File Downloaded',
            'appointment_scheduled' => 'Appointment Scheduled',
            'prescription_issued' => 'Prescription Issued',
            'lab_result_ordered' => 'Lab Result Ordered',
            'checkin' => 'Patient Check-in',
            'checkout' => 'Patient Check-out',
            'payment_received' => 'Payment Received',
            'refund_issued' => 'Refund Issued',
        ];

        return $actions[$this->action] ?? ucfirst($this->action);
    }

    /**
     * Get activity severity level
     */
    public function getSeverityAttribute(): string
    {
        $severities = [
            'deleted' => 'High',
            'password_changed' => 'High',
            'role_assigned' => 'Medium',
            'permission_granted' => 'Medium',
            'file_uploaded' => 'Medium',
            'file_downloaded' => 'Medium',
            'payment_received' => 'Medium',
            'refund_issued' => 'Medium',
            'created' => 'Low',
            'updated' => 'Low',
            'viewed' => 'Low',
            'exported' => 'Low',
            'imported' => 'Low',
            'approved' => 'Low',
            'rejected' => 'Low',
            'scheduled' => 'Low',
            'cancelled' => 'Low',
            'completed' => 'Low',
            'assigned' => 'Low',
            'unassigned' => 'Low',
            'logged_in' => 'Low',
            'logged_out' => 'Low',
            'appointment_scheduled' => 'Low',
            'prescription_issued' => 'Low',
            'lab_result_ordered' => 'Low',
            'checkin' => 'Low',
            'checkout' => 'Low',
        ];

        return $severities[$this->action] ?? 'Medium';
    }

    /**
     * Get activity description
     */
    public function getDescriptionAttribute(): string
    {
        $actorName = $this->actor->name ?? 'Unknown User';
        $entityName = $this->entity;
        $actionName = $this->action_display_name;

        switch ($this->action) {
            case 'logged_in':
                return "{$actorName} logged into the system";
            case 'logged_out':
                return "{$actorName} logged out of the system";
            case 'password_changed':
                return "{$actorName} changed their password";
            case 'role_assigned':
                $roleName = $this->meta['role_name'] ?? 'Unknown Role';
                return "{$actorName} assigned role '{$roleName}' to user";
            case 'permission_granted':
                $permissionName = $this->meta['permission_name'] ?? 'Unknown Permission';
                return "{$actorName} granted permission '{$permissionName}'";
            case 'file_uploaded':
                $fileName = $this->meta['file_name'] ?? 'Unknown File';
                return "{$actorName} uploaded file '{$fileName}'";
            case 'file_downloaded':
                $fileName = $this->meta['file_name'] ?? 'Unknown File';
                return "{$actorName} downloaded file '{$fileName}'";
            case 'appointment_scheduled':
                $patientName = $this->meta['patient_name'] ?? 'Unknown Patient';
                $appointmentDate = $this->meta['appointment_date'] ?? 'Unknown Date';
                return "{$actorName} scheduled appointment for {$patientName} on {$appointmentDate}";
            case 'prescription_issued':
                $patientName = $this->meta['patient_name'] ?? 'Unknown Patient';
                $prescriptionDate = $this->meta['prescription_date'] ?? 'Unknown Date';
                return "{$actorName} issued prescription for {$patientName} on {$prescriptionDate}";
            case 'lab_result_ordered':
                $testName = $this->meta['test_name'] ?? 'Unknown Test';
                $patientName = $this->meta['patient_name'] ?? 'Unknown Patient';
                return "{$actorName} ordered lab test '{$testName}' for {$patientName}";
            case 'checkin':
                $patientName = $this->meta['patient_name'] ?? 'Unknown Patient';
                return "{$actorName} checked in patient {$patientName}";
            case 'checkout':
                $patientName = $this->meta['patient_name'] ?? 'Unknown Patient';
                return "{$actorName} checked out patient {$patientName}";
            case 'payment_received':
                $amount = $this->meta['amount'] ?? 'Unknown Amount';
                $patientName = $this->meta['patient_name'] ?? 'Unknown Patient';
                return "{$actorName} received payment of {$amount} from {$patientName}";
            case 'refund_issued':
                $amount = $this->meta['amount'] ?? 'Unknown Amount';
                $patientName = $this->meta['patient_name'] ?? 'Unknown Patient';
                return "{$actorName} issued refund of {$amount} to {$patientName}";
            default:
                return "{$actorName} {$actionName} {$entityName}";
        }
    }

    /**
     * Get activity icon
     */
    public function getIconAttribute(): string
    {
        $icons = [
            'created' => 'plus-circle',
            'updated' => 'pencil',
            'deleted' => 'trash',
            'viewed' => 'eye',
            'exported' => 'download',
            'imported' => 'upload',
            'approved' => 'check-circle',
            'rejected' => 'x-circle',
            'scheduled' => 'calendar',
            'cancelled' => 'x',
            'completed' => 'check',
            'assigned' => 'user-plus',
            'unassigned' => 'user-minus',
            'logged_in' => 'log-in',
            'logged_out' => 'log-out',
            'password_changed' => 'lock',
            'role_assigned' => 'shield',
            'permission_granted' => 'key',
            'file_uploaded' => 'file-plus',
            'file_downloaded' => 'file-minus',
            'appointment_scheduled' => 'calendar-plus',
            'prescription_issued' => 'prescription',
            'lab_result_ordered' => 'flask',
            'checkin' => 'user-check',
            'checkout' => 'user-x',
            'payment_received' => 'credit-card',
            'refund_issued' => 'refresh-cw',
        ];

        return $icons[$this->action] ?? 'activity';
    }

    /**
     * Get activity color
     */
    public function getColorAttribute(): string
    {
        $colors = [
            'High' => 'danger',
            'Medium' => 'warning',
            'Low' => 'info',
        ];

        return $colors[$this->severity] ?? 'info';
    }

    /**
     * Get formatted timestamp
     */
    public function getFormattedTimeAttribute(): string
    {
        $now = Carbon::now();
        $diff = $this->at->diffForHumans($now);

        if ($this->at->isToday()) {
            return "Today at " . $this->at->format('g:i A');
        } elseif ($this->at->isYesterday()) {
            return "Yesterday at " . $this->at->format('g:i A');
        } elseif ($this->at->diffInDays($now) < 7) {
            return $this->at->format('l at g:i A');
        } else {
            return $this->at->format('M j, Y at g:i A');
        }
    }

    /**
     * Get activity summary
     */
    public function getSummaryAttribute(): array
    {
        return [
            'entity' => $this->entity,
            'action' => $this->action,
            'actor' => $this->actor->name ?? 'Unknown',
            'timestamp' => $this->formatted_time,
            'severity' => $this->severity,
            'category' => $this->category,
            'description' => $this->description,
        ];
    }

    /**
     * Check if activity is recent
     */
    public function isRecent(int $minutes = 60): bool
    {
        return $this->at->diffInMinutes(Carbon::now()) <= $minutes;
    }

    /**
     * Check if activity is critical
     */
    public function isCritical(): bool
    {
        return $this->severity === 'High';
    }

    /**
     * Get related activities
     */
    public function getRelatedActivitiesAttribute()
    {
        return static::where('entity', $this->entity)
            ->where('entity_id', $this->entity_id)
            ->where('id', '!=', $this->id)
            ->orderBy('at', 'desc')
            ->limit(10)
            ->get();
    }

    /**
     * Get activity trends
     */
    public static function getTrends(int $clinicId, int $days = 30): array
    {
        $startDate = Carbon::now()->subDays($days);
        
        $trends = static::where('clinic_id', $clinicId)
            ->where('at', '>=', $startDate)
            ->selectRaw('DATE(at) as date, COUNT(*) as count, entity, action')
            ->groupBy('date', 'entity', 'action')
            ->orderBy('date')
            ->get()
            ->groupBy('date');

        return $trends->map(function ($dayActivities) {
            return $dayActivities->groupBy('entity')->map(function ($entityActivities) {
                return $entityActivities->groupBy('action')->map(function ($actionActivities) {
                    return $actionActivities->sum('count');
                });
            });
        })->toArray();
    }

    /**
     * Get activity statistics
     */
    public static function getStatistics(int $clinicId, int $days = 30): array
    {
        $startDate = Carbon::now()->subDays($days);
        
        $totalActivities = static::where('clinic_id', $clinicId)
            ->where('at', '>=', $startDate)
            ->count();

        $activitiesByEntity = static::where('clinic_id', $clinicId)
            ->where('at', '>=', $startDate)
            ->selectRaw('entity, COUNT(*) as count')
            ->groupBy('entity')
            ->pluck('count', 'entity')
            ->toArray();

        $activitiesByAction = static::where('clinic_id', $clinicId)
            ->where('at', '>=', $startDate)
            ->selectRaw('action, COUNT(*) as count')
            ->groupBy('action')
            ->pluck('count', 'action')
            ->toArray();

        $activitiesByUser = static::where('clinic_id', $clinicId)
            ->where('at', '>=', $startDate)
            ->selectRaw('actor_user_id, COUNT(*) as count')
            ->groupBy('actor_user_id')
            ->pluck('count', 'actor_user_id')
            ->toArray();

        return [
            'total_activities' => $totalActivities,
            'by_entity' => $activitiesByEntity,
            'by_action' => $activitiesByAction,
            'by_user' => $activitiesByUser,
            'period_days' => $days,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => Carbon::now()->format('Y-m-d'),
        ];
    }

    /**
     * Log activity with automatic metadata
     */
    public static function log(string $entity, string $action, int $entityId, int $clinicId, int $actorUserId, array $meta = [], string $ip = null): self
    {
        $log = static::create([
            'clinic_id' => $clinicId,
            'actor_user_id' => $actorUserId,
            'entity' => $entity,
            'entity_id' => $entityId,
            'action' => $action,
            'at' => Carbon::now(),
            'ip' => $ip ?? request()->ip(),
            'meta' => $meta,
            'before_hash' => null, // Can be implemented for change tracking
            'after_hash' => null,  // Can be implemented for change tracking
        ]);

        // Clear cache if needed
        $log->clearCache();

        return $log;
    }

    /**
     * Clear cache for this activity log
     */
    public function clearCache(): void
    {
        $cacheKeys = [
            "activity_trends_{$this->clinic_id}",
            "activity_stats_{$this->clinic_id}",
        ];

        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }
    }

    /**
     * Get activity metadata as formatted text
     */
    public function getFormattedMetaAttribute(): string
    {
        if (empty($this->meta)) {
            return 'No additional information';
        }

        $formatted = [];
        foreach ($this->meta as $key => $value) {
            if (is_array($value)) {
                $value = json_encode($value, JSON_PRETTY_PRINT);
            }
            $formatted[] = ucfirst(str_replace('_', ' ', $key)) . ': ' . $value;
        }

        return implode("\n", $formatted);
    }
}
