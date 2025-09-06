<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\LicenseService;
use Illuminate\Support\Facades\Log;

class LicenseUsageValidation
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
    public function handle(Request $request, Closure $next, string $usageType = null): Response
    {
        // Skip validation for certain routes
        if ($this->shouldSkipValidation($request)) {
            return $next($request);
        }

        // Check if application should be restricted
        if ($this->licenseService->shouldRestrictApplication()) {
            return $this->handleLicenseRestriction($request);
        }

        // Usage type is required for this middleware
        if (!$usageType) {
            Log::error('LicenseUsageValidation middleware used without usage type parameter');
            return $this->handleUsageRestriction($request, 'unknown');
        }

        // Validate usage type
        if (!in_array($usageType, ['users', 'clinics', 'patients', 'appointments'])) {
            Log::error('Invalid usage type provided to LicenseUsageValidation middleware', [
                'usage_type' => $usageType,
                'url' => $request->url()
            ]);
            return $this->handleUsageRestriction($request, $usageType);
        }

        // Check usage limits
        $usage = $this->licenseService->checkUsageLimit($usageType);
        if (!$usage['allowed']) {
            Log::warning('Usage limit exceeded', [
                'usage_type' => $usageType,
                'current' => $usage['current'],
                'limit' => $usage['limit'],
                'url' => $request->url(),
                'ip' => $request->ip(),
                'user_id' => auth()->id()
            ]);

            return $this->handleUsageRestriction($request, $usageType, $usage);
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
     * Handle usage restriction
     */
    protected function handleUsageRestriction(Request $request, string $usageType, array $usage = null): Response
    {
        $usageDisplayName = $this->getUsageDisplayName($usageType);
        
        if ($request->expectsJson() || $request->is('api/*')) {
            $response = [
                'error' => 'Usage limit exceeded',
                'message' => "You have reached the maximum limit for {$usageDisplayName}. Please upgrade your license to add more {$usageDisplayName}.",
                'error_code' => 'USAGE_LIMIT_EXCEEDED',
                'usage_type' => $usageType,
                'usage_display_name' => $usageDisplayName,
                'upgrade_required' => true
            ];

            if ($usage) {
                $response['current'] = $usage['current'];
                $response['limit'] = $usage['limit'];
                $response['percentage'] = round(($usage['current'] / $usage['limit']) * 100, 1);
            }

            return response()->json($response, 403);
        }

        // For web requests, redirect back with error
        $message = $usage 
            ? "You have reached the maximum limit for {$usageDisplayName} ({$usage['current']}/{$usage['limit']}). Please upgrade your license to add more {$usageDisplayName}."
            : "You have reached the maximum limit for {$usageDisplayName}. Please upgrade your license to add more {$usageDisplayName}.";

        return redirect()->back()
            ->with('error', $message);
    }

    /**
     * Get user-friendly usage display name
     */
    protected function getUsageDisplayName(string $usageType): string
    {
        $usageNames = [
            'users' => 'users',
            'clinics' => 'clinics',
            'patients' => 'patients',
            'appointments' => 'appointments per month',
        ];

        return $usageNames[$usageType] ?? $usageType;
    }
}
