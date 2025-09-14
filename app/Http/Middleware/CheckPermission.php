<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
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

        // Get clinic ID from various sources
        $clinicId = $this->getClinicIdFromRequest($request);

        // For system-wide permissions (like system.admin, settings.manage)
        if (in_array($permission, ['system.admin', 'settings.manage', 'permissions.view'])) {
            if (!$user->hasPermission($permission)) {
                return $this->handlePermissionDenied($request, $permission);
            }
            return $next($request);
        }

        // If no clinic context, check if user has permission globally
        if (!$clinicId) {
            if (!$user->hasPermission($permission)) {
                return $this->handlePermissionDenied($request, $permission);
            }
            return $next($request);
        }

        // Check if user has the required permission in the clinic
        if (!$user->hasPermissionInClinic($permission, $clinicId)) {
            return $this->handlePermissionDenied($request, $permission);
        }

        return $next($request);
    }

    /**
     * Get clinic ID from request
     */
    protected function getClinicIdFromRequest(Request $request): ?int
    {
        // Try to get from route parameters
        if ($clinic = $request->route('clinic')) {
            return is_object($clinic) ? $clinic->id : (int) $clinic;
        }

        if ($doctor = $request->route('doctor')) {
            return is_object($doctor) ? $doctor->clinic_id : null;
        }

        // Try to get from request input
        if ($clinicId = $request->input('clinic_id')) {
            return (int) $clinicId;
        }

        // Try to get from user's first clinic (fallback)
        $user = $request->user();
        if ($user && $user->clinics()->exists()) {
            return $user->clinics()->first()?->id;
        }

        return null;
    }

    /**
     * Handle permission denied response
     */
    protected function handlePermissionDenied(Request $request, string $permission): Response
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'error' => 'Permission denied',
                'message' => 'You do not have permission to perform this action.',
                'permission' => $permission
            ], 403);
        }

        abort(403, 'Insufficient permissions for this action.');
    }
}
