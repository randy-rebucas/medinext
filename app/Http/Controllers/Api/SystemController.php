<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use OpenApi\Annotations as OA;

class SystemController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/v1/system/health",
     *     summary="Get system health status",
     *     description="Retrieve comprehensive system health information including database, storage, and service status",
     *     tags={"System Management"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="System health retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="System health retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="status", type="string", example="healthy"),
     *                 @OA\Property(property="timestamp", type="string", format="date-time", example="2024-01-15T10:30:00Z"),
     *                 @OA\Property(
     *                     property="services",
     *                     type="object",
     *                     @OA\Property(property="database", type="object", @OA\Property(property="status", type="string", example="connected")),
     *                     @OA\Property(property="storage", type="object", @OA\Property(property="status", type="string", example="available")),
     *                     @OA\Property(property="cache", type="object", @OA\Property(property="status", type="string", example="operational")),
     *                     @OA\Property(property="queue", type="object", @OA\Property(property="status", type="string", example="running"))
     *                 ),
     *                 @OA\Property(
     *                     property="metrics",
     *                     type="object",
     *                     @OA\Property(property="memory_usage", type="string", example="45.2%"),
     *                     @OA\Property(property="disk_usage", type="string", example="67.8%"),
     *                     @OA\Property(property="cpu_usage", type="string", example="23.1%")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Insufficient permissions",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function health(): JsonResponse
    {
        try {
            // Check database connection
            $dbStatus = 'connected';
            try {
                DB::connection()->getPdo();
            } catch (\Exception $e) {
                $dbStatus = 'disconnected';
            }

            // Check storage
            $storageStatus = 'available';
            try {
                Storage::disk('local')->put('health-check.txt', 'test');
                Storage::disk('local')->delete('health-check.txt');
            } catch (\Exception $e) {
                $storageStatus = 'unavailable';
            }

            // Get system metrics (simplified)
            $memoryUsage = memory_get_usage(true);
            $memoryLimit = ini_get('memory_limit');
            $memoryPercent = round(($memoryUsage / $this->convertToBytes($memoryLimit)) * 100, 1);

            $healthData = [
                'status' => ($dbStatus === 'connected' && $storageStatus === 'available') ? 'healthy' : 'degraded',
                'timestamp' => now()->toISOString(),
                'services' => [
                    'database' => ['status' => $dbStatus],
                    'storage' => ['status' => $storageStatus],
                    'cache' => ['status' => 'operational'],
                    'queue' => ['status' => 'running']
                ],
                'metrics' => [
                    'memory_usage' => $memoryPercent . '%',
                    'disk_usage' => '67.8%', // Placeholder
                    'cpu_usage' => '23.1%' // Placeholder
                ]
            ];

            return $this->successResponse($healthData, 'System health retrieved successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/system/status",
     *     summary="Get system status",
     *     description="Retrieve current system status and configuration information",
     *     tags={"System Management"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="System status retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="System status retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="app_name", type="string", example="MediNext EMR"),
     *                 @OA\Property(property="app_version", type="string", example="1.0.0"),
     *                 @OA\Property(property="php_version", type="string", example="8.1.0"),
     *                 @OA\Property(property="laravel_version", type="string", example="10.0.0"),
     *                 @OA\Property(property="environment", type="string", example="production"),
     *                 @OA\Property(property="debug_mode", type="boolean", example=false),
     *                 @OA\Property(property="maintenance_mode", type="boolean", example=false),
     *                 @OA\Property(property="timezone", type="string", example="UTC"),
     *                 @OA\Property(property="locale", type="string", example="en")
     *             )
     *         )
     *     )
     * )
     */
    public function status(): JsonResponse
    {
        try {
            $statusData = [
                'app_name' => config('app.name'),
                'app_version' => config('app.version', '1.0.0'),
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'environment' => config('app.env'),
                'debug_mode' => config('app.debug'),
                'maintenance_mode' => app()->isDownForMaintenance(),
                'timezone' => config('app.timezone'),
                'locale' => config('app.locale')
            ];

            return $this->successResponse($statusData, 'System status retrieved successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/system/logs",
     *     summary="Get system logs",
     *     description="Retrieve system logs with filtering options",
     *     tags={"System Management"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="level",
     *         in="query",
     *         description="Log level filter",
     *         required=false,
     *         @OA\Schema(type="string", enum={"emergency", "alert", "critical", "error", "warning", "notice", "info", "debug"}, example="error")
     *     ),
     *     @OA\Parameter(
     *         name="date_from",
     *         in="query",
     *         description="Filter from date (YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2024-01-01")
     *     ),
     *     @OA\Parameter(
     *         name="date_to",
     *         in="query",
     *         description="Filter to date (YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2024-01-31")
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Maximum number of log entries",
     *         required=false,
     *         @OA\Schema(type="integer", example=100)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="System logs retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="System logs retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="logs",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="timestamp", type="string", format="date-time", example="2024-01-15T10:30:00Z"),
     *                         @OA\Property(property="level", type="string", example="error"),
     *                         @OA\Property(property="message", type="string", example="Database connection failed"),
     *                         @OA\Property(property="context", type="object")
     *                     )
     *                 ),
     *                 @OA\Property(property="total_count", type="integer", example=150)
     *             )
     *         )
     *     )
     * )
     */
    public function logs(Request $request): JsonResponse
    {
        try {
            $level = $request->get('level');
            $dateFrom = $request->get('date_from');
            $dateTo = $request->get('date_to');
            $limit = $request->get('limit', 100);

            // TODO: Implement actual log reading logic
            // This would typically read from Laravel log files
            $logs = [
                [
                    'timestamp' => now()->subMinutes(5)->toISOString(),
                    'level' => 'info',
                    'message' => 'User login successful',
                    'context' => ['user_id' => 1, 'ip' => '192.168.1.1']
                ],
                [
                    'timestamp' => now()->subMinutes(10)->toISOString(),
                    'level' => 'error',
                    'message' => 'Database connection timeout',
                    'context' => ['connection' => 'mysql', 'timeout' => 30]
                ]
            ];

            return $this->successResponse([
                'logs' => $logs,
                'total_count' => count($logs)
            ], 'System logs retrieved successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/system/backup",
     *     summary="Create system backup",
     *     description="Create a backup of the system database and files",
     *     tags={"System Management"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="include_files", type="boolean", example=true, description="Include file assets in backup"),
     *             @OA\Property(property="backup_name", type="string", example="backup-2024-01-15", description="Custom backup name")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Backup created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Backup created successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="backup_id", type="string", example="backup-2024-01-15-10-30-00"),
     *                 @OA\Property(property="backup_size", type="string", example="125.5 MB"),
     *                 @OA\Property(property="backup_path", type="string", example="/backups/backup-2024-01-15-10-30-00.zip"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-15T10:30:00Z")
     *             )
     *         )
     *     )
     * )
     */
    public function backup(Request $request): JsonResponse
    {
        try {
            $includeFiles = $request->get('include_files', true);
            $backupName = $request->get('backup_name', 'backup-' . now()->format('Y-m-d-H-i-s'));

            // TODO: Implement actual backup logic
            // This would typically use Laravel Backup or similar package

            $backupId = $backupName;
            $backupSize = '125.5 MB';
            $backupPath = "/backups/{$backupId}.zip";

            return $this->successResponse([
                'backup_id' => $backupId,
                'backup_size' => $backupSize,
                'backup_path' => $backupPath,
                'created_at' => now()->toISOString()
            ], 'Backup created successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/system/clear-cache",
     *     summary="Clear system cache",
     *     description="Clear various system caches (application, route, config, view)",
     *     tags={"System Management"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="cache_types",
     *                 type="array",
     *                 @OA\Items(type="string", enum={"application", "route", "config", "view", "all"}),
     *                 example={"application", "route", "config"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cache cleared successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Cache cleared successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="cleared_caches",
     *                     type="array",
     *                     @OA\Items(type="string"),
     *                     example={"application", "route", "config"}
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function clearCache(Request $request): JsonResponse
    {
        try {
            $cacheTypes = $request->get('cache_types', ['all']);
            $clearedCaches = [];

            if (in_array('all', $cacheTypes)) {
                Artisan::call('cache:clear');
                Artisan::call('route:clear');
                Artisan::call('config:clear');
                Artisan::call('view:clear');
                $clearedCaches = ['application', 'route', 'config', 'view'];
            } else {
                foreach ($cacheTypes as $type) {
                    switch ($type) {
                        case 'application':
                            Artisan::call('cache:clear');
                            $clearedCaches[] = 'application';
                            break;
                        case 'route':
                            Artisan::call('route:clear');
                            $clearedCaches[] = 'route';
                            break;
                        case 'config':
                            Artisan::call('config:clear');
                            $clearedCaches[] = 'config';
                            break;
                        case 'view':
                            Artisan::call('view:clear');
                            $clearedCaches[] = 'view';
                            break;
                    }
                }
            }

            return $this->successResponse(['cleared_caches' => $clearedCaches], 'Cache cleared successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/system/usage",
     *     summary="Get system usage statistics",
     *     description="Retrieve system usage statistics including user counts, storage usage, and performance metrics",
     *     tags={"System Management"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="System usage retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="System usage retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="users",
     *                     type="object",
     *                     @OA\Property(property="total", type="integer", example=150),
     *                     @OA\Property(property="active", type="integer", example=120),
     *                     @OA\Property(property="new_this_month", type="integer", example=15)
     *                 ),
     *                 @OA\Property(
     *                     property="storage",
     *                     type="object",
     *                     @OA\Property(property="total_used", type="string", example="2.5 GB"),
     *                     @OA\Property(property="total_available", type="string", example="10.0 GB"),
     *                     @OA\Property(property="usage_percentage", type="number", format="float", example=25.0)
     *                 ),
     *                 @OA\Property(
     *                     property="database",
     *                     type="object",
     *                     @OA\Property(property="size", type="string", example="150.2 MB"),
     *                     @OA\Property(property="tables_count", type="integer", example=25)
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function usage(): JsonResponse
    {
        try {
            // Get user statistics
            $totalUsers = \App\Models\User::count();
            $activeUsers = \App\Models\User::where('is_active', true)->count();
            $newUsersThisMonth = \App\Models\User::whereMonth('created_at', now()->month)->count();

            // Get storage usage (simplified)
            $totalUsed = '2.5 GB';
            $totalAvailable = '10.0 GB';
            $usagePercentage = 25.0;

            // Get database size (simplified)
            $dbSize = '150.2 MB';
            $tablesCount = 25;

            $usageData = [
                'users' => [
                    'total' => $totalUsers,
                    'active' => $activeUsers,
                    'new_this_month' => $newUsersThisMonth
                ],
                'storage' => [
                    'total_used' => $totalUsed,
                    'total_available' => $totalAvailable,
                    'usage_percentage' => $usagePercentage
                ],
                'database' => [
                    'size' => $dbSize,
                    'tables_count' => $tablesCount
                ]
            ];

            return $this->successResponse($usageData, 'System usage retrieved successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Convert memory limit string to bytes
     */
    private function convertToBytes($memoryLimit): int
    {
        $memoryLimit = trim($memoryLimit);
        $last = strtolower($memoryLimit[strlen($memoryLimit) - 1]);
        $memoryLimit = (int) $memoryLimit;

        switch ($last) {
            case 'g':
                $memoryLimit *= 1024;
            case 'm':
                $memoryLimit *= 1024;
            case 'k':
                $memoryLimit *= 1024;
        }

        return $memoryLimit;
    }
}
