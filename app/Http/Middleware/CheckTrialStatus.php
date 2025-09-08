<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckTrialStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip for unauthenticated users
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        // Skip for users who have activated licenses
        if ($user->has_activated_license) {
            return $next($request);
        }

        // Skip for certain routes that should be accessible even with expired trial
        if ($this->shouldSkipTrialCheck($request)) {
            return $next($request);
        }

        // Check if trial has expired
        if ($user->isTrialExpired()) {
            return $this->handleExpiredTrial($request);
        }

        // Check if user is on trial and show warning if close to expiration
        if ($user->isOnTrial()) {
            $daysRemaining = $user->getTrialDaysRemaining();

            // Add trial status to request for frontend to display
            $request->merge(['trial_status' => [
                'is_on_trial' => true,
                'days_remaining' => $daysRemaining,
                'trial_ends_at' => $user->trial_ends_at,
            ]]);
        }

        return $next($request);
    }

    /**
     * Check if trial validation should be skipped for this request
     */
    protected function shouldSkipTrialCheck(Request $request): bool
    {
        $skipRoutes = [
            'license.*',
            'logout',
            'password.*',
            'email.*',
            'verification.*',
            'sanctum.*',
            'health',
            'version',
            'up',
        ];

        $currentRoute = $request->route()?->getName() ?? $request->path();

        foreach ($skipRoutes as $skipRoute) {
            if (fnmatch($skipRoute, $currentRoute)) {
                return true;
            }
        }

        // Skip for API routes that don't require trial validation
        if ($request->is('api/license/*') || $request->is('api/auth/*')) {
            return true;
        }

        return false;
    }

    /**
     * Handle expired trial
     */
    protected function handleExpiredTrial(Request $request): Response
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'error' => 'Trial expired',
                'message' => 'Your free trial has expired. Please activate a license to continue using the application.',
                'error_code' => 'TRIAL_EXPIRED',
                'trial_ended_at' => Auth::user()->trial_ends_at,
            ], 403);
        }

        // For web requests, redirect to license activation page
        return redirect()->route('license.activate')
            ->with('error', 'Your free trial has expired. Please activate a license to continue using the application.')
            ->with('trial_expired', true);
    }
}
