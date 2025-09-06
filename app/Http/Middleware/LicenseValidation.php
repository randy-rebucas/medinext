<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\LicenseService;
use Illuminate\Support\Facades\Log;

class LicenseValidation
{
    protected $licenseService;

    public function __construct(LicenseService $licenseService)
    {
        $this->licenseService = $licenseService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $feature = null): Response
    {
        // Skip license validation for certain routes
        if ($this->shouldSkipValidation($request)) {
            return $next($request);
        }

        // Check if application should be restricted
        if ($this->licenseService->shouldRestrictApplication()) {
            Log::warning('License validation failed', [
                'url' => $request->url(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'message' => $this->licenseService->getRestrictionMessage()
            ]);

            return $this->handleLicenseRestriction($request);
        }

        // Check specific feature if provided
        if ($feature && !$this->licenseService->hasFeature($feature)) {
            Log::warning('Feature not available in license', [
                'feature' => $feature,
                'url' => $request->url(),
                'ip' => $request->ip()
            ]);

            return $this->handleFeatureRestriction($request, $feature);
        }

        return $next($request);
    }

    /**
     * Check if validation should be skipped for this request
     */
    protected function shouldSkipValidation(Request $request): bool
    {
        $skipRoutes = [
            'nova/*',
            'license/*',
            'api/license/*',
            'login',
            'logout',
            'password/*',
            'email/*',
            'register',
            'verification/*',
            'sanctum/*',
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

        // Skip for local development
        if (app()->environment('local')) {
            return true;
        }

        // Skip for health checks and system routes
        if (in_array($request->path(), ['health', 'version', 'up'])) {
            return true;
        }

        return false;
    }

    /**
     * Handle license restriction
     */
    protected function handleLicenseRestriction(Request $request): Response
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'error' => 'License validation failed',
                'message' => $this->licenseService->getRestrictionMessage(),
                'error_code' => 'LICENSE_VALIDATION_FAILED'
            ], 403);
        }

        // For web requests, redirect to license error page
        return redirect()->route('license.error')
            ->with('error', $this->licenseService->getRestrictionMessage());
    }

    /**
     * Handle feature restriction
     */
    protected function handleFeatureRestriction(Request $request, string $feature): Response
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'error' => 'Feature not available',
                'message' => "The feature '{$feature}' is not available in your current license.",
                'error_code' => 'FEATURE_NOT_AVAILABLE',
                'feature' => $feature
            ], 403);
        }

        // For web requests, redirect back with error
        return redirect()->back()
            ->with('error', "The feature '{$feature}' is not available in your current license.");
    }
}
