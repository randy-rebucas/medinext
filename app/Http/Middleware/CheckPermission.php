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
            return redirect()->route('login');
        }

        // Get clinic ID from route or request
        $clinicId = $request->route('clinic') ??
                    $request->route('doctor') ? $request->route('doctor')->clinic_id : null ??
                    $request->input('clinic_id');

        // If no clinic context, check if user has permission globally
        if (!$clinicId) {
            // For system-wide permissions (like settings.manage)
            if ($permission === 'settings.manage') {
                if (!$user->hasRoleInClinic('superadmin', 1)) {
                    abort(403, 'Insufficient permissions.');
                }
                return $next($request);
            }

            abort(403, 'Clinic context required for this action.');
        }

        // Check if user has the required permission in the clinic
        if (!$user->hasPermissionInClinic($permission, $clinicId)) {
            abort(403, 'Insufficient permissions for this action.');
        }

        return $next($request);
    }
}
