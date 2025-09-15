<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\Response;

class CheckInstallationStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip installation check for installation routes
        $currentRoute = $request->route()?->getName();
        if ($currentRoute && str_starts_with($currentRoute, 'installation.')) {
            return $next($request);
        }

        // Check if installation is complete
        if ($this->isInstallationComplete()) {
            return $next($request);
        }

        // If installation is not complete, redirect to installation
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'error' => 'Installation required',
                'message' => 'The system needs to be installed before it can be used.',
                'installation_url' => route('installation.index')
            ], 503);
        }

        return redirect()->route('installation.index');
    }

    /**
     * Check if installation is complete
     */
    private function isInstallationComplete(): bool
    {
        try {
            // Check if installation flag file exists
            $flagFile = storage_path('app/installation_complete.flag');
            if (!File::exists($flagFile)) {
                return false;
            }

            // Check if database is properly set up
            if (!DB::getSchemaBuilder()->hasTable('users')) {
                return false;
            }

            // Check if there's at least one user with superadmin role
            $superadminExists = DB::table('users')
                ->join('user_roles', 'users.id', '=', 'user_roles.user_id')
                ->join('roles', 'user_roles.role_id', '=', 'roles.id')
                ->where('roles.name', 'superadmin')
                ->exists();

            return $superadminExists;

        } catch (\Exception $e) {
            // If there's any error checking the installation status,
            // assume installation is not complete
            return false;
        }
    }
}
