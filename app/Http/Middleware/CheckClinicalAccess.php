<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckClinicalAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $clinicId = $this->getClinicId($request);

        if (!$clinicId) {
            return response()->json(['error' => 'Clinic context required'], 400);
        }

        // Check if user can access clinical notes/data
        if ($user->canAccessClinicalNotes($clinicId)) {
            return $next($request);
        }

        // If user is super admin, allow access regardless of clinic permissions
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        return response()->json(['error' => 'Access to clinical data not permitted'], 403);
    }

    /**
     * Get clinic ID from request
     */
    private function getClinicId(Request $request): ?int
    {
        // Try to get clinic ID from various sources
        $clinicId = $request->route('clinic_id')
            ?? $request->input('clinic_id')
            ?? $request->header('X-Clinic-ID')
            ?? session('current_clinic_id');

        return $clinicId ? (int) $clinicId : null;
    }
}
