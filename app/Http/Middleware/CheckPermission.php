<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        // Check if user is authenticated
        if (!$user) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'error' => 'Authentication required',
                    'message' => 'You must be logged in to access this resource.'
                ], 401);
            }
            return redirect()->route('login');
        }

        // Skip permission check for onboarding routes
        $currentRoute = $request->route()?->getName();
        if ($currentRoute && str_starts_with($currentRoute, 'onboarding.')) {
            return $next($request);
        }

        // Log permission check for debugging
        Log::debug('Permission check', [
            'user_id' => $user->id,
            'permission' => $permission,
            'route' => $currentRoute,
            'url' => $request->fullUrl()
        ]);

        // Get clinic ID from various sources
        $clinicId = $this->getClinicIdFromRequest($request);

        // For system-wide permissions (like system.admin, settings.manage, permissions.view)
        if ($this->isSystemWidePermission($permission)) {
            if (!$user->hasPermission($permission)) {
                Log::warning('System permission denied', [
                    'user_id' => $user->id,
                    'permission' => $permission,
                    'route' => $currentRoute
                ]);
                return $this->handlePermissionDenied($request, $permission);
            }
            return $next($request);
        }

        // If no clinic context, check if user has permission globally
        if (!$clinicId) {
            if (!$user->hasPermission($permission)) {
                Log::warning('Global permission denied', [
                    'user_id' => $user->id,
                    'permission' => $permission,
                    'route' => $currentRoute,
                    'reason' => 'no_clinic_context'
                ]);
                return $this->handlePermissionDenied($request, $permission);
            }
            return $next($request);
        }

        // Check if user has the required permission in the clinic
        if (!$user->hasPermissionInClinic($permission, $clinicId)) {
            Log::warning('Clinic permission denied', [
                'user_id' => $user->id,
                'permission' => $permission,
                'clinic_id' => $clinicId,
                'route' => $currentRoute
            ]);
            return $this->handlePermissionDenied($request, $permission);
        }

        Log::debug('Permission granted', [
            'user_id' => $user->id,
            'permission' => $permission,
            'clinic_id' => $clinicId,
            'route' => $currentRoute
        ]);

        return $next($request);
    }

    /**
     * Get clinic ID from request
     */
    protected function getClinicIdFromRequest(Request $request): ?int
    {
        // Try to get from route parameters (most reliable for web routes)
        if ($clinic = $request->route('clinic')) {
            return is_object($clinic) ? $clinic->id : (int) $clinic;
        }

        // Try to get from other route parameters that might contain clinic info
        if ($doctor = $request->route('doctor')) {
            return is_object($doctor) ? $doctor->clinic_id : null;
        }

        if ($patient = $request->route('patient')) {
            return is_object($patient) ? $patient->clinic_id : null;
        }

        if ($appointment = $request->route('appointment')) {
            return is_object($appointment) ? $appointment->clinic_id : null;
        }

        // Try to get from request input (for forms and API calls)
        if ($clinicId = $request->input('clinic_id')) {
            return (int) $clinicId;
        }

        // Try to get from query parameters
        if ($clinicId = $request->query('clinic_id')) {
            return (int) $clinicId;
        }

        // Try to get from user's current clinic context (session)
        $user = $request->user();
        if ($user) {
            $currentClinic = $user->getCurrentClinic();
            if ($currentClinic) {
                return $currentClinic->id;
            }
        }

        return null;
    }

    /**
     * Check if permission is system-wide (not clinic-specific)
     */
    protected function isSystemWidePermission(string $permission): bool
    {
        $systemPermissions = [
            'system.admin',
            'settings.manage',
            'permissions.view',
            'permissions.manage',
            'users.manage',
            'system.status',
            'backups.manage',
            'activity_logs.view'
        ];

        return in_array($permission, $systemPermissions);
    }

    /**
     * Handle permission denied response
     */
    protected function handlePermissionDenied(Request $request, string $permission): Response
    {
        $user = $request->user();
        $currentRoute = $request->route()?->getName();

        // Log the permission denial for security monitoring
        Log::warning('Permission denied access attempt', [
            'user_id' => $user?->id,
            'permission' => $permission,
            'route' => $currentRoute,
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'error' => 'Permission denied',
                'message' => 'You do not have permission to perform this action.',
                'permission' => $permission,
                'code' => 'INSUFFICIENT_PERMISSIONS'
            ], 403);
        }

        // For web requests, redirect to appropriate page with error message
        $redirectRoute = $this->getRedirectRouteForPermission($permission);
        
        return redirect()->route($redirectRoute)
            ->with('error', 'You do not have permission to access this resource.')
            ->with('permission_denied', $permission);
    }

    /**
     * Get appropriate redirect route based on permission type
     */
    protected function getRedirectRouteForPermission(string $permission): string
    {
        // Map permissions to appropriate redirect routes
        $permissionRedirects = [
            'system.admin' => 'dashboard',
            'settings.manage' => 'dashboard',
            'permissions.view' => 'dashboard',
            'permissions.manage' => 'dashboard',
            'users.manage' => 'dashboard',
            'system.status' => 'dashboard',
            'backups.manage' => 'dashboard',
            'activity_logs.view' => 'dashboard',
        ];

        return $permissionRedirects[$permission] ?? 'dashboard';
    }
}
