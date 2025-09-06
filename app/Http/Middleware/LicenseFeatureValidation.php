<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\LicenseService;
use Illuminate\Support\Facades\Log;

class LicenseFeatureValidation
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
        // Skip validation for certain routes
        if ($this->shouldSkipValidation($request)) {
            return $next($request);
        }

        // Check if application should be restricted
        if ($this->licenseService->shouldRestrictApplication()) {
            return $this->handleLicenseRestriction($request);
        }

        // Feature is required for this middleware
        if (!$feature) {
            Log::error('LicenseFeatureValidation middleware used without feature parameter');
            return $this->handleFeatureRestriction($request, 'unknown');
        }

        // Check specific feature
        if (!$this->licenseService->hasFeature($feature)) {
            Log::warning('Feature not available in license', [
                'feature' => $feature,
                'url' => $request->url(),
                'ip' => $request->ip(),
                'user_id' => auth()->id(),
                'license_type' => $this->licenseService->getCurrentLicense()?->license_type
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
        $featureDisplayName = $this->getFeatureDisplayName($feature);
        
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'error' => 'Feature not available',
                'message' => "The feature '{$featureDisplayName}' is not available in your current license. Please upgrade your license to access this feature.",
                'error_code' => 'FEATURE_NOT_AVAILABLE',
                'feature' => $feature,
                'feature_display_name' => $featureDisplayName,
                'current_license_type' => $this->licenseService->getCurrentLicense()?->license_type,
                'upgrade_required' => true
            ], 403);
        }

        // For web requests, redirect back with error
        return redirect()->back()
            ->with('error', "The feature '{$featureDisplayName}' is not available in your current license. Please upgrade your license to access this feature.");
    }

    /**
     * Get user-friendly feature display name
     */
    protected function getFeatureDisplayName(string $feature): string
    {
        $featureNames = [
            'basic_appointments' => 'Basic Appointments',
            'patient_management' => 'Patient Management',
            'prescription_management' => 'Prescription Management',
            'basic_reporting' => 'Basic Reporting',
            'advanced_reporting' => 'Advanced Reporting',
            'lab_results' => 'Lab Results',
            'medrep_management' => 'Medical Representative Management',
            'multi_clinic' => 'Multi-Clinic Support',
            'email_notifications' => 'Email Notifications',
            'sms_notifications' => 'SMS Notifications',
            'api_access' => 'API Access',
            'custom_branding' => 'Custom Branding',
            'priority_support' => 'Priority Support',
            'advanced_analytics' => 'Advanced Analytics',
            'backup_restore' => 'Backup & Restore',
        ];

        return $featureNames[$feature] ?? ucwords(str_replace('_', ' ', $feature));
    }
}
