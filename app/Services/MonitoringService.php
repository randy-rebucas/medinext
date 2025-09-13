<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use App\Models\ActivityLog;
use App\Models\User;
use App\Models\Appointment;
use App\Models\Patient;
use Carbon\Carbon;

class MonitoringService
{
    /**
     * Monitor system health
     */
    public static function checkSystemHealth(): array
    {
        $health = [
            'status' => 'healthy',
            'timestamp' => now()->toISOString(),
            'checks' => []
        ];

        // Database connectivity
        try {
            DB::connection()->getPdo();
            $health['checks']['database'] = [
                'status' => 'healthy',
                'response_time' => self::measureDatabaseResponseTime()
            ];
        } catch (\Exception $e) {
            $health['checks']['database'] = [
                'status' => 'unhealthy',
                'error' => $e->getMessage()
            ];
            $health['status'] = 'unhealthy';
        }

        // Cache connectivity
        try {
            Cache::put('health_check', 'ok', 60);
            $cacheStatus = Cache::get('health_check') === 'ok';
            $health['checks']['cache'] = [
                'status' => $cacheStatus ? 'healthy' : 'unhealthy'
            ];
        } catch (\Exception $e) {
            $health['checks']['cache'] = [
                'status' => 'unhealthy',
                'error' => $e->getMessage()
            ];
            $health['status'] = 'unhealthy';
        }

        // Queue system
        try {
            $queueSize = Queue::size();
            $health['checks']['queue'] = [
                'status' => 'healthy',
                'pending_jobs' => $queueSize
            ];
        } catch (\Exception $e) {
            $health['checks']['queue'] = [
                'status' => 'unhealthy',
                'error' => $e->getMessage()
            ];
        }

        // Disk space
        $diskUsage = disk_free_space('/') / disk_total_space('/') * 100;
        $health['checks']['disk'] = [
            'status' => $diskUsage > 10 ? 'healthy' : 'warning',
            'free_space_percent' => round($diskUsage, 2)
        ];

        if ($diskUsage <= 10) {
            $health['status'] = 'warning';
        }

        return $health;
    }

    /**
     * Monitor application performance
     */
    public static function getPerformanceMetrics(): array
    {
        $metrics = [
            'timestamp' => now()->toISOString(),
            'database' => [],
            'cache' => [],
            'application' => []
        ];

        // Database metrics
        $metrics['database'] = [
            'connections' => DB::getConnections(),
            'slow_queries' => self::getSlowQueries(),
            'table_sizes' => self::getTableSizes()
        ];

        // Cache metrics
        $metrics['cache'] = [
            'hit_rate' => self::getCacheHitRate(),
            'memory_usage' => self::getCacheMemoryUsage(),
            'key_count' => self::getCacheKeyCount()
        ];

        // Application metrics
        $metrics['application'] = [
            'active_users' => self::getActiveUsersCount(),
            'recent_activity' => self::getRecentActivityCount(),
            'error_rate' => self::getErrorRate()
        ];

        return $metrics;
    }

    /**
     * Monitor security events
     */
    public static function getSecurityMetrics(): array
    {
        $last24Hours = now()->subDay();

        $metrics = [
            'timestamp' => now()->toISOString(),
            'failed_logins' => ActivityLog::where('module', 'auth')
                ->where('action', 'failed_login')
                ->where('created_at', '>=', $last24Hours)
                ->count(),
            'suspicious_activities' => ActivityLog::where('description', 'like', '%suspicious%')
                ->where('created_at', '>=', $last24Hours)
                ->count(),
            'permission_denied' => ActivityLog::where('action', 'permission_denied')
                ->where('created_at', '>=', $last24Hours)
                ->count(),
            'data_access' => ActivityLog::where('module', 'patient')
                ->where('action', 'view')
                ->where('created_at', '>=', $last24Hours)
                ->count()
        ];

        return $metrics;
    }

    /**
     * Monitor business metrics
     */
    public static function getBusinessMetrics(): array
    {
        $today = now()->startOfDay();
        $thisWeek = now()->startOfWeek();
        $thisMonth = now()->startOfMonth();

        $metrics = [
            'timestamp' => now()->toISOString(),
            'appointments' => [
                'today' => Appointment::whereDate('appointment_date', $today)->count(),
                'this_week' => Appointment::where('appointment_date', '>=', $thisWeek)->count(),
                'this_month' => Appointment::where('appointment_date', '>=', $thisMonth)->count()
            ],
            'patients' => [
                'total' => Patient::count(),
                'new_this_month' => Patient::where('created_at', '>=', $thisMonth)->count()
            ],
            'users' => [
                'total' => User::count(),
                'active_today' => User::where('last_login_at', '>=', $today)->count()
            ]
        ];

        return $metrics;
    }

    /**
     * Check for alerts
     */
    public static function checkAlerts(): array
    {
        $alerts = [];

        // High error rate
        $errorRate = self::getErrorRate();
        if ($errorRate > 5) {
            $alerts[] = [
                'type' => 'error_rate',
                'severity' => 'high',
                'message' => "High error rate detected: {$errorRate}%",
                'timestamp' => now()->toISOString()
            ];
        }

        // High failed login attempts
        $failedLogins = ActivityLog::where('module', 'auth')
            ->where('action', 'failed_login')
            ->where('created_at', '>=', now()->subHour())
            ->count();

        if ($failedLogins > 10) {
            $alerts[] = [
                'type' => 'security',
                'severity' => 'high',
                'message' => "High number of failed login attempts: {$failedLogins}",
                'timestamp' => now()->toISOString()
            ];
        }

        // Low disk space
        $diskUsage = disk_free_space('/') / disk_total_space('/') * 100;
        if ($diskUsage < 20) {
            $alerts[] = [
                'type' => 'system',
                'severity' => $diskUsage < 10 ? 'critical' : 'warning',
                'message' => "Low disk space: {$diskUsage}% free",
                'timestamp' => now()->toISOString()
            ];
        }

        return $alerts;
    }

    /**
     * Measure database response time
     */
    private static function measureDatabaseResponseTime(): float
    {
        $start = microtime(true);
        DB::select('SELECT 1');
        return round((microtime(true) - $start) * 1000, 2);
    }

    /**
     * Get slow queries
     */
    private static function getSlowQueries(): array
    {
        // This would require query log analysis
        // For now, return empty array
        return [];
    }

    /**
     * Get table sizes
     */
    private static function getTableSizes(): array
    {
        $tables = ['users', 'patients', 'appointments', 'encounters', 'prescriptions'];
        $sizes = [];

        foreach ($tables as $table) {
            try {
                $size = DB::select("SELECT COUNT(*) as count FROM {$table}")[0]->count;
                $sizes[$table] = $size;
            } catch (\Exception $e) {
                $sizes[$table] = 0;
            }
        }

        return $sizes;
    }

    /**
     * Get cache hit rate
     */
    private static function getCacheHitRate(): float
    {
        try {
            $redis = Cache::getRedis();
            $info = $redis->info();
            $hits = $info['keyspace_hits'] ?? 0;
            $misses = $info['keyspace_misses'] ?? 0;

            if ($hits + $misses > 0) {
                return round($hits / ($hits + $misses) * 100, 2);
            }
        } catch (\Exception $e) {
            Log::error('Cache hit rate calculation failed: ' . $e->getMessage());
        }

        return 0;
    }

    /**
     * Get cache memory usage
     */
    private static function getCacheMemoryUsage(): string
    {
        try {
            $redis = Cache::getRedis();
            $info = $redis->info();
            return $info['used_memory_human'] ?? 'N/A';
        } catch (\Exception $e) {
            return 'N/A';
        }
    }

    /**
     * Get cache key count
     */
    private static function getCacheKeyCount(): int
    {
        try {
            $redis = Cache::getRedis();
            return $redis->dbSize();
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get active users count
     */
    private static function getActiveUsersCount(): int
    {
        return User::where('last_login_at', '>=', now()->subHour())->count();
    }

    /**
     * Get recent activity count
     */
    private static function getRecentActivityCount(): int
    {
        return ActivityLog::where('created_at', '>=', now()->subHour())->count();
    }

    /**
     * Get error rate
     */
    private static function getErrorRate(): float
    {
        $totalRequests = ActivityLog::where('created_at', '>=', now()->subHour())->count();
        $errors = ActivityLog::where('created_at', '>=', now()->subHour())
            ->where('action', 'like', '%error%')
            ->count();

        if ($totalRequests > 0) {
            return round($errors / $totalRequests * 100, 2);
        }

        return 0;
    }
}
