<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckOnboardingStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Skip onboarding check for certain routes
        $skipRoutes = [
            'onboarding.*',
            'logout',
            'verification.*',
            'password.*',
        ];

        $currentRoute = $request->route()?->getName();

        foreach ($skipRoutes as $skipRoute) {
            if ($currentRoute && fnmatch($skipRoute, $currentRoute)) {
                return $next($request);
            }
        }

        // If user is authenticated and hasn't completed onboarding
        if ($user && !$user->onboarding_completed_at) {
            // Redirect to onboarding welcome page
            return redirect()->route('onboarding.welcome');
        }

        return $next($request);
    }
}
