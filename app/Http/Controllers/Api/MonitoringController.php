<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Services\MonitoringService;
use App\Services\CacheService;
use App\Services\ErrorHandlingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class MonitoringController extends BaseController
{
    /**
     * Get system health status
     */
    public function health(): JsonResponse
    {
        try {
            $this->requirePermission('system.monitor');

            $health = MonitoringService::checkSystemHealth();

            return ErrorHandlingService::successResponse($health, 'System health retrieved successfully');
        } catch (\Exception $e) {
            return ErrorHandlingService::handleApiError($e, 'MonitoringController@health');
        }
    }

    /**
     * Get performance metrics
     */
    public function performance(): JsonResponse
    {
        try {
            $this->requirePermission('system.monitor');

            $metrics = MonitoringService::getPerformanceMetrics();

            return ErrorHandlingService::successResponse($metrics, 'Performance metrics retrieved successfully');
        } catch (\Exception $e) {
            return ErrorHandlingService::handleApiError($e, 'MonitoringController@performance');
        }
    }

    /**
     * Get security metrics
     */
    public function security(): JsonResponse
    {
        try {
            $this->requirePermission('system.monitor');

            $metrics = MonitoringService::getSecurityMetrics();

            return ErrorHandlingService::successResponse($metrics, 'Security metrics retrieved successfully');
        } catch (\Exception $e) {
            return ErrorHandlingService::handleApiError($e, 'MonitoringController@security');
        }
    }

    /**
     * Get business metrics
     */
    public function business(): JsonResponse
    {
        try {
            $this->requirePermission('system.monitor');

            $metrics = MonitoringService::getBusinessMetrics();

            return ErrorHandlingService::successResponse($metrics, 'Business metrics retrieved successfully');
        } catch (\Exception $e) {
            return ErrorHandlingService::handleApiError($e, 'MonitoringController@business');
        }
    }

    /**
     * Get system alerts
     */
    public function alerts(): JsonResponse
    {
        try {
            $this->requirePermission('system.monitor');

            $alerts = MonitoringService::checkAlerts();

            return ErrorHandlingService::successResponse($alerts, 'System alerts retrieved successfully');
        } catch (\Exception $e) {
            return ErrorHandlingService::handleApiError($e, 'MonitoringController@alerts');
        }
    }

    /**
     * Get cache statistics
     */
    public function cache(): JsonResponse
    {
        try {
            $this->requirePermission('system.monitor');

            $stats = CacheService::getCacheStats();

            return ErrorHandlingService::successResponse($stats, 'Cache statistics retrieved successfully');
        } catch (\Exception $e) {
            return ErrorHandlingService::handleApiError($e, 'MonitoringController@cache');
        }
    }

    /**
     * Clear cache
     */
    public function clearCache(): JsonResponse
    {
        try {
            $this->requirePermission('system.admin');

            CacheService::clearAllCache();

            Log::info('Cache cleared by user', [
                'user_id' => Auth::id(),
                'timestamp' => now()
            ]);

            return ErrorHandlingService::successResponse(null, 'Cache cleared successfully');
        } catch (\Exception $e) {
            return ErrorHandlingService::handleApiError($e, 'MonitoringController@clearCache');
        }
    }

    /**
     * Get comprehensive dashboard data
     */
    public function dashboard(): JsonResponse
    {
        try {
            $this->requirePermission('system.monitor');

            $dashboard = [
                'health' => MonitoringService::checkSystemHealth(),
                'performance' => MonitoringService::getPerformanceMetrics(),
                'security' => MonitoringService::getSecurityMetrics(),
                'business' => MonitoringService::getBusinessMetrics(),
                'alerts' => MonitoringService::checkAlerts(),
                'cache' => CacheService::getCacheStats(),
                'timestamp' => now()->toISOString()
            ];

            return ErrorHandlingService::successResponse($dashboard, 'Dashboard data retrieved successfully');
        } catch (\Exception $e) {
            return ErrorHandlingService::handleApiError($e, 'MonitoringController@dashboard');
        }
    }
}
