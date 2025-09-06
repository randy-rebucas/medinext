<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\License;
use App\Services\LicenseService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class LicenseController extends Controller
{
    protected $licenseService;

    public function __construct(LicenseService $licenseService)
    {
        $this->licenseService = $licenseService;
    }

    /**
     * Get current license status
     */
    public function status(): JsonResponse
    {
        try {
            $status = $this->licenseService->getLicenseStatus();

            return response()->json([
                'success' => true,
                'data' => $status
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get license status', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get license status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get license information
     */
    public function info(): JsonResponse
    {
        try {
            $info = $this->licenseService->getLicenseInfo();

            return response()->json([
                'success' => true,
                'data' => $info
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get license info', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get license info',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validate license
     */
    public function validate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'license_key' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $result = $this->licenseService->validateLicense($request->license_key);

            return response()->json([
                'success' => $result['valid'],
                'data' => $result
            ], $result['valid'] ? 200 : 403);
        } catch (\Exception $e) {
            Log::error('Failed to validate license', [
                'license_key' => $request->license_key,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to validate license',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Activate license
     */
    public function activate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'license_key' => 'required|string|max:255',
            'activation_code' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $result = $this->licenseService->activateLicense(
                $request->license_key,
                $request->activation_code
            );

            return response()->json([
                'success' => $result['success'],
                'data' => $result
            ], $result['success'] ? 200 : 403);
        } catch (\Exception $e) {
            Log::error('Failed to activate license', [
                'license_key' => $request->license_key,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to activate license',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check feature availability
     */
    public function hasFeature(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'feature' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $hasFeature = $this->licenseService->hasFeature($request->feature);

            return response()->json([
                'success' => true,
                'data' => [
                    'feature' => $request->feature,
                    'available' => $hasFeature
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to check feature availability', [
                'feature' => $request->feature,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to check feature availability',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check usage limits
     */
    public function checkUsage(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|string|in:users,clinics,patients,appointments'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $result = $this->licenseService->checkUsageLimit($request->type);

            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to check usage limits', [
                'type' => $request->type,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to check usage limits',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get usage statistics
     */
    public function usage(): JsonResponse
    {
        try {
            $info = $this->licenseService->getLicenseInfo();

            if (!$info['has_license']) {
                return response()->json([
                    'success' => false,
                    'message' => 'No license found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $info['usage']
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get usage statistics', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get usage statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Increment usage
     */
    public function incrementUsage(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|string|in:users,clinics,patients,appointments',
            'amount' => 'integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $amount = $request->get('amount', 1);
            $success = $this->licenseService->incrementUsage($request->type, $amount);

            if (!$success) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to increment usage'
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Usage incremented successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to increment usage', [
                'type' => $request->type,
                'amount' => $request->get('amount', 1),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to increment usage',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Decrement usage
     */
    public function decrementUsage(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|string|in:users,clinics,patients,appointments',
            'amount' => 'integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $amount = $request->get('amount', 1);
            $success = $this->licenseService->decrementUsage($request->type, $amount);

            if (!$success) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to decrement usage'
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Usage decremented successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to decrement usage', [
                'type' => $request->type,
                'amount' => $request->get('amount', 1),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to decrement usage',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reset monthly usage
     */
    public function resetMonthlyUsage(): JsonResponse
    {
        try {
            $success = $this->licenseService->resetMonthlyUsage();

            if (!$success) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to reset monthly usage'
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Monthly usage reset successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to reset monthly usage', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to reset monthly usage',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get license statistics (admin only)
     */
    public function statistics(): JsonResponse
    {
        try {
            // Check if user has admin permissions
            if (!auth()->user() || !auth()->user()->hasRole('admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            $stats = $this->licenseService->getLicenseStatistics();

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get license statistics', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get license statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get expiring licenses (admin only)
     */
    public function expiring(Request $request): JsonResponse
    {
        try {
            // Check if user has admin permissions
            if (!auth()->user() || !auth()->user()->hasRole('admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            $days = $request->get('days', 30);
            $licenses = $this->licenseService->getExpiringLicenses($days);

            return response()->json([
                'success' => true,
                'data' => $licenses
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get expiring licenses', [
                'days' => $request->get('days', 30),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get expiring licenses',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

