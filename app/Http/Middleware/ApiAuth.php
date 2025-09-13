<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\SettingsService;
use Symfony\Component\HttpFoundation\Response;

class ApiAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated via Sanctum or session
        $user = null;

        if (Auth::guard('sanctum')->check()) {
            $user = Auth::guard('sanctum')->user();
        } elseif (Auth::guard('web')->check()) {
            $user = Auth::guard('web')->user();
        }

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
                'error_code' => 'UNAUTHENTICATED',
                'timestamp' => now()->toISOString(),
            ], 401);
        }

        // Check if user is active
        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Account is deactivated',
                'error_code' => 'ACCOUNT_DEACTIVATED',
                'timestamp' => now()->toISOString(),
            ], 401);
        }

        // Check IP whitelist if enabled
        $settingsService = app(SettingsService::class);
        $ipWhitelist = $settingsService->get('security.ip_whitelist', [], $user->current_clinic_id);

        if (!empty($ipWhitelist)) {
            $clientIp = $request->ip();
            if (!in_array($clientIp, $ipWhitelist)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied from this IP address',
                    'error_code' => 'IP_NOT_WHITELISTED',
                    'timestamp' => now()->toISOString(),
                ], 403);
            }
        }

        // Add user to request for easy access
        $request->merge(['authenticated_user' => $user]);

        return $next($request);
    }
}
