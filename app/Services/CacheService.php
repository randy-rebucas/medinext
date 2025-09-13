<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CacheService
{
    /**
     * Cache duration constants
     */
    const CACHE_DURATION_SHORT = 300; // 5 minutes
    const CACHE_DURATION_MEDIUM = 1800; // 30 minutes
    const CACHE_DURATION_LONG = 3600; // 1 hour
    const CACHE_DURATION_VERY_LONG = 86400; // 24 hours

    /**
     * Cache user permissions with clinic context
     */
    public static function cacheUserPermissions(int $userId, int $clinicId, array $permissions): void
    {
        $key = "user_permissions_{$userId}_{$clinicId}";
        Cache::put($key, $permissions, self::CACHE_DURATION_MEDIUM);
    }

    /**
     * Get cached user permissions
     */
    public static function getCachedUserPermissions(int $userId, int $clinicId): ?array
    {
        $key = "user_permissions_{$userId}_{$clinicId}";
        return Cache::get($key);
    }

    /**
     * Cache clinic settings
     */
    public static function cacheClinicSettings(int $clinicId, array $settings): void
    {
        $key = "clinic_settings_{$clinicId}";
        Cache::put($key, $settings, self::CACHE_DURATION_LONG);
    }

    /**
     * Get cached clinic settings
     */
    public static function getCachedClinicSettings(int $clinicId): ?array
    {
        $key = "clinic_settings_{$clinicId}";
        return Cache::get($key);
    }

    /**
     * Cache appointment availability
     */
    public static function cacheAppointmentAvailability(int $doctorId, string $date, array $availability): void
    {
        $key = "appointment_availability_{$doctorId}_{$date}";
        Cache::put($key, $availability, self::CACHE_DURATION_SHORT);
    }

    /**
     * Get cached appointment availability
     */
    public static function getCachedAppointmentAvailability(int $doctorId, string $date): ?array
    {
        $key = "appointment_availability_{$doctorId}_{$date}";
        return Cache::get($key);
    }

    /**
     * Cache dashboard statistics
     */
    public static function cacheDashboardStats(int $clinicId, array $stats): void
    {
        $key = "dashboard_stats_{$clinicId}";
        Cache::put($key, $stats, self::CACHE_DURATION_MEDIUM);
    }

    /**
     * Get cached dashboard statistics
     */
    public static function getCachedDashboardStats(int $clinicId): ?array
    {
        $key = "dashboard_stats_{$clinicId}";
        return Cache::get($key);
    }

    /**
     * Cache patient search results
     */
    public static function cachePatientSearch(string $query, int $clinicId, array $results): void
    {
        $key = "patient_search_" . md5($query . $clinicId);
        Cache::put($key, $results, self::CACHE_DURATION_SHORT);
    }

    /**
     * Get cached patient search results
     */
    public static function getCachedPatientSearch(string $query, int $clinicId): ?array
    {
        $key = "patient_search_" . md5($query . $clinicId);
        return Cache::get($key);
    }

    /**
     * Invalidate cache by pattern
     */
    public static function invalidateCache(string $pattern): void
    {
        try {
            $keys = Cache::getRedis()->keys($pattern);
            if (!empty($keys)) {
                Cache::getRedis()->del($keys);
            }
        } catch (\Exception $e) {
            Log::error('Cache invalidation failed: ' . $e->getMessage());
        }
    }

    /**
     * Invalidate user-related cache
     */
    public static function invalidateUserCache(int $userId): void
    {
        self::invalidateCache("user_permissions_{$userId}_*");
        self::invalidateCache("user_*_{$userId}");
    }

    /**
     * Invalidate clinic-related cache
     */
    public static function invalidateClinicCache(int $clinicId): void
    {
        self::invalidateCache("clinic_settings_{$clinicId}");
        self::invalidateCache("dashboard_stats_{$clinicId}");
        self::invalidateCache("patient_search_*");
        self::invalidateCache("appointment_availability_*");
    }

    /**
     * Clear all cache
     */
    public static function clearAllCache(): void
    {
        try {
            Cache::flush();
        } catch (\Exception $e) {
            Log::error('Cache clear failed: ' . $e->getMessage());
        }
    }

    /**
     * Get cache statistics
     */
    public static function getCacheStats(): array
    {
        try {
            $redis = Cache::getRedis();
            $info = $redis->info();

            return [
                'memory_used' => $info['used_memory_human'] ?? 'N/A',
                'connected_clients' => $info['connected_clients'] ?? 0,
                'total_commands_processed' => $info['total_commands_processed'] ?? 0,
                'keyspace_hits' => $info['keyspace_hits'] ?? 0,
                'keyspace_misses' => $info['keyspace_misses'] ?? 0,
                'hit_rate' => $info['keyspace_hits'] && $info['keyspace_misses']
                    ? round($info['keyspace_hits'] / ($info['keyspace_hits'] + $info['keyspace_misses']) * 100, 2)
                    : 0
            ];
        } catch (\Exception $e) {
            Log::error('Cache stats failed: ' . $e->getMessage());
            return [];
        }
    }
}
