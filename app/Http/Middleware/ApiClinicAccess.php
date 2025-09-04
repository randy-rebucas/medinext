<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ApiClinicAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('sanctum')->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
                'error_code' => 'UNAUTHENTICATED',
                'timestamp' => now()->toISOString(),
            ], 401);
        }

        // Check if user has access to any clinic
        if ($user->clinics()->count() === 0) {
            return response()->json([
                'success' => false,
                'message' => 'No clinic access',
                'error_code' => 'NO_CLINIC_ACCESS',
                'timestamp' => now()->toISOString(),
            ], 403);
        }

        // If clinic_id is provided in route parameters, check specific access
        $clinicId = $request->route('clinic') ?? $request->route('clinic_id');
        
        if ($clinicId) {
            if (!$user->clinics()->where('clinic_id', $clinicId)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No access to specified clinic',
                    'error_code' => 'CLINIC_ACCESS_DENIED',
                    'timestamp' => now()->toISOString(),
                ], 403);
            }
        }

        return $next($request);
    }
}
