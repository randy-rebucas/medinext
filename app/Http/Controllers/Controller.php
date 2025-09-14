<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

abstract class Controller
{
    /**
     * Validate request data with custom messages
     */
    protected function validateRequest(Request $request, array $rules, array $messages = []): array
    {
        $validator = Validator::make($request->all(), $rules, $messages);
        
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        
        return $validator->validated();
    }

    /**
     * Sanitize input data to prevent XSS and other attacks
     */
    protected function sanitizeInput(array $data): array
    {
        $sanitized = [];
        
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                // Remove potentially dangerous characters and HTML tags
                $sanitized[$key] = strip_tags(trim($value));
            } elseif (is_array($value)) {
                $sanitized[$key] = $this->sanitizeInput($value);
            } else {
                $sanitized[$key] = $value;
            }
        }
        
        return $sanitized;
    }

    /**
     * Validate and sanitize request data
     */
    protected function validateAndSanitize(Request $request, array $rules, array $messages = []): array
    {
        $validated = $this->validateRequest($request, $rules, $messages);
        return $this->sanitizeInput($validated);
    }

    /**
     * Get user's clinic and role information
     */
    protected function getUserClinicRole(Request $request)
    {
        $user = $request->user();
        
        if (!$user) {
            return null;
        }

        return $user->userClinicRoles()->with(['clinic', 'role'])->first();
    }

    /**
     * Check if user has permission in clinic
     */
    protected function hasPermissionInClinic(Request $request, string $permission, ?int $clinicId = null): bool
    {
        $user = $request->user();
        
        if (!$user) {
            return false;
        }

        $targetClinicId = $clinicId ?? $this->getUserClinicRole($request)?->clinic_id;
        
        if (!$targetClinicId) {
            return false;
        }

        return $user->hasAnyPermissionInClinic([$permission], $targetClinicId);
    }

    /**
     * Require permission in clinic or abort
     */
    protected function requirePermissionInClinic(Request $request, string $permission, ?int $clinicId = null): void
    {
        if (!$this->hasPermissionInClinic($request, $permission, $clinicId)) {
            abort(403, 'Insufficient permissions.');
        }
    }

    /**
     * Cache data with a key and TTL
     */
    protected function cacheData(string $key, $data, int $ttlMinutes = 60): void
    {
        Cache::put($key, $data, now()->addMinutes($ttlMinutes));
    }

    /**
     * Get cached data or execute callback and cache result
     */
    protected function remember(string $key, int $ttlMinutes, callable $callback)
    {
        return Cache::remember($key, now()->addMinutes($ttlMinutes), $callback);
    }

    /**
     * Generate cache key for user-specific data
     */
    protected function getUserCacheKey(string $prefix, ?int $userId = null): string
    {
        $userId = $userId ?? request()->user()?->id;
        return "{$prefix}_user_{$userId}";
    }

    /**
     * Generate cache key for clinic-specific data
     */
    protected function getClinicCacheKey(string $prefix, ?int $clinicId = null): string
    {
        $clinicId = $clinicId ?? $this->getUserClinicRole(request())?->clinic_id;
        return "{$prefix}_clinic_{$clinicId}";
    }

    /**
     * Log web request with context
     */
    protected function logWebRequest(string $action, array $context = []): void
    {
        Log::info("Web Request: {$action}", array_merge([
            'user_id' => request()->user()?->id,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'endpoint' => request()->path(),
            'method' => request()->method(),
            'timestamp' => now()->toISOString(),
        ], $context));
    }

    /**
     * Log security event
     */
    protected function logSecurityEvent(string $event, array $context = []): void
    {
        Log::warning("Security Event: {$event}", array_merge([
            'user_id' => request()->user()?->id,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'endpoint' => request()->path(),
            'method' => request()->method(),
            'timestamp' => now()->toISOString(),
        ], $context));
    }

    /**
     * Handle exceptions with proper logging
     */
    protected function handleException(\Exception $e, string $context = 'Web Controller'): void
    {
        Log::error("{$context} Exception: " . $e->getMessage(), [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
            'user_id' => request()->user()?->id,
            'request_url' => request()->fullUrl(),
            'request_method' => request()->method(),
            'request_data' => request()->except(['password', 'password_confirmation', 'token'])
        ]);
    }
}
