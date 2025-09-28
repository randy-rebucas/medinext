<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCurrentClinic
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Skip clinic selection for certain routes
        $skipRoutes = [
            'clinic.selection',
            'clinics.switch',
            'admin.clinics.create',
            'admin.clinics.store',
            'logout',
            'login',
            'register',
            'password.request',
            'password.reset',
            'password.email',
            'password.update',
            'verification.notice',
            'verification.verify',
            'verification.resend',
        ];

        $currentRoute = $request->route()?->getName();
        
        if (in_array($currentRoute, $skipRoutes)) {
            return $next($request);
        }

        // Check if user has any clinics
        if ($user->clinics()->count() === 0) {
            // User has no clinics, redirect to clinic creation
            return redirect()->route('admin.clinics.create');
        }

        // Check if user has a current clinic set
        $currentClinic = $user->getCurrentClinic();
        
        if (!$currentClinic) {
            // User has clinics but no current clinic selected, redirect to clinic selection
            return redirect()->route('clinic.selection');
        }

        // Add current clinic to request for easy access in controllers
        $request->attributes->set('current_clinic', $currentClinic);

        return $next($request);
    }
}
