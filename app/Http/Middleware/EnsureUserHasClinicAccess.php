<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Clinic;

class EnsureUserHasClinicAccess
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

        // Check if user is accessing a specific clinic
        $clinicId = $request->route('clinic') ?? $request->input('clinic_id');
        
        if ($clinicId) {
            $clinic = Clinic::find($clinicId);
            
            if (!$clinic) {
                abort(404, 'Clinic not found.');
            }

            // Check if user has access to this clinic
            if (!$user->hasRoleInClinic('superadmin', $clinic->id) && 
                !$user->hasRoleInClinic('admin', $clinic->id) &&
                !$user->hasRoleInClinic('doctor', $clinic->id) &&
                !$user->hasRoleInClinic('receptionist', $clinic->id)) {
                abort(403, 'You do not have access to this clinic.');
            }

            // Add clinic to the request for easy access in controllers
            $request->attributes->set('current_clinic', $clinic);
        }

        return $next($request);
    }
}
