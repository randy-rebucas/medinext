<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\LicenseKeyGenerator;
use App\Models\License;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class LicenseKeyController extends Controller
{
    /**
     * Generate a single license key
     */
    public function generate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'strategy' => 'sometimes|string|in:standard,compact,segmented,custom',
            'options' => 'sometimes|array',
            'options.prefix' => 'sometimes|string|max:10',
            'options.segment_length' => 'sometimes|integer|min:2|max:8',
            'options.segments' => 'sometimes|integer|min:2|max:10',
            'options.length' => 'sometimes|integer|min:8|max:20',
            'options.format' => 'sometimes|string|max:100',
            'options.custom_format' => 'sometimes|string|max:200',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $strategy = $request->input('strategy', LicenseKeyGenerator::STRATEGY_STANDARD);
            $options = $request->input('options', []);

            // Handle custom format for custom strategy
            if ($strategy === LicenseKeyGenerator::STRATEGY_CUSTOM && isset($options['custom_format'])) {
                $options['format'] = $options['custom_format'];
                unset($options['custom_format']);
            }

            $licenseKey = LicenseKeyGenerator::generate($strategy, $options);

            Log::info('License key generated via API', [
                'strategy' => $strategy,
                'options' => $options,
                'generated_key' => $licenseKey,
                'user' => auth()->user()?->email ?? 'anonymous'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'License key generated successfully',
                'data' => [
                    'license_key' => $licenseKey,
                    'strategy' => $strategy,
                    'options' => $options,
                    'format_info' => LicenseKeyGenerator::parseLicenseKey($licenseKey)
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to generate license key via API', [
                'error' => $e->getMessage(),
                'strategy' => $request->input('strategy'),
                'options' => $request->input('options'),
                'user' => auth()->user()?->email ?? 'anonymous'
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate license key',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate multiple license keys
     */
    public function generateMultiple(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'count' => 'required|integer|min:1|max:100',
            'strategy' => 'sometimes|string|in:standard,compact,segmented,custom',
            'options' => 'sometimes|array',
            'options.prefix' => 'sometimes|string|max:10',
            'options.segment_length' => 'sometimes|integer|min:2|max:8',
            'options.segments' => 'sometimes|integer|min:2|max:10',
            'options.length' => 'sometimes|integer|min:8|max:20',
            'options.format' => 'sometimes|string|max:100',
            'options.custom_format' => 'sometimes|string|max:200',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $count = $request->input('count');
            $strategy = $request->input('strategy', LicenseKeyGenerator::STRATEGY_STANDARD);
            $options = $request->input('options', []);

            // Handle custom format for custom strategy
            if ($strategy === LicenseKeyGenerator::STRATEGY_CUSTOM && isset($options['custom_format'])) {
                $options['format'] = $options['custom_format'];
                unset($options['custom_format']);
            }

            $licenseKeys = LicenseKeyGenerator::generateMultiple($count, $strategy, $options);

            Log::info('Multiple license keys generated via API', [
                'count' => $count,
                'strategy' => $strategy,
                'options' => $options,
                'user' => auth()->user()?->email ?? 'anonymous'
            ]);

            return response()->json([
                'success' => true,
                'message' => "Generated {$count} license keys successfully",
                'data' => [
                    'license_keys' => $licenseKeys,
                    'count' => count($licenseKeys),
                    'strategy' => $strategy,
                    'options' => $options
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to generate multiple license keys via API', [
                'error' => $e->getMessage(),
                'count' => $request->input('count'),
                'strategy' => $request->input('strategy'),
                'options' => $request->input('options'),
                'user' => auth()->user()?->email ?? 'anonymous'
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate license keys',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validate a license key format
     */
    public function validate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'license_key' => 'required|string|max:255',
            'strategy' => 'sometimes|string|in:standard,compact,segmented,custom',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $licenseKey = $request->input('license_key');
            $strategy = $request->input('strategy', LicenseKeyGenerator::STRATEGY_STANDARD);

            $isValid = LicenseKeyGenerator::validateFormat($licenseKey, $strategy);
            $keyInfo = LicenseKeyGenerator::parseLicenseKey($licenseKey);
            $exists = LicenseKeyGenerator::keyExists($licenseKey);

            return response()->json([
                'success' => true,
                'data' => [
                    'license_key' => $licenseKey,
                    'is_valid_format' => $isValid,
                    'exists_in_database' => $exists,
                    'key_info' => $keyInfo,
                    'strategy' => $strategy
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to validate license key',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Parse license key information
     */
    public function parse(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'license_key' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $licenseKey = $request->input('license_key');
            $keyInfo = LicenseKeyGenerator::parseLicenseKey($licenseKey);

            return response()->json([
                'success' => true,
                'data' => [
                    'license_key' => $licenseKey,
                    'parsed_info' => $keyInfo
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to parse license key',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get license key generation statistics
     */
    public function statistics(): JsonResponse
    {
        try {
            $stats = LicenseKeyGenerator::getStatistics();

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Regenerate license key for existing license
     */
    public function regenerate(Request $request, License $license): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'strategy' => 'sometimes|string|in:standard,compact,segmented,custom',
            'options' => 'sometimes|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $strategy = $request->input('strategy', LicenseKeyGenerator::STRATEGY_STANDARD);
            $options = $request->input('options', []);

            $oldKey = $license->license_key;
            $newKey = $license->regenerateLicenseKey($strategy, $options);

            Log::info('License key regenerated via API', [
                'license_id' => $license->id,
                'old_key' => $oldKey,
                'new_key' => $newKey,
                'strategy' => $strategy,
                'options' => $options,
                'user' => auth()->user()?->email ?? 'anonymous'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'License key regenerated successfully',
                'data' => [
                    'license_id' => $license->id,
                    'old_license_key' => $oldKey,
                    'new_license_key' => $newKey,
                    'strategy' => $strategy,
                    'options' => $options
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to regenerate license key via API', [
                'license_id' => $license->id,
                'error' => $e->getMessage(),
                'strategy' => $request->input('strategy'),
                'options' => $request->input('options'),
                'user' => auth()->user()?->email ?? 'anonymous'
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to regenerate license key',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available generation strategies
     */
    public function strategies(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'strategies' => [
                    LicenseKeyGenerator::STRATEGY_STANDARD => [
                        'name' => 'Standard',
                        'description' => 'MEDI-XXXX-XXXX-XXXX-XXXX format',
                        'example' => 'MEDI-A1B2-C3D4-E5F6-G7H8'
                    ],
                    LicenseKeyGenerator::STRATEGY_COMPACT => [
                        'name' => 'Compact',
                        'description' => 'MEDI-XXXXXXXXXXXX format',
                        'example' => 'MEDI-A1B2C3D4E5F6'
                    ],
                    LicenseKeyGenerator::STRATEGY_SEGMENTED => [
                        'name' => 'Segmented',
                        'description' => 'Custom segmented format',
                        'example' => 'MEDI-XXXX-XXXX-XXXX-XXXX'
                    ],
                    LicenseKeyGenerator::STRATEGY_CUSTOM => [
                        'name' => 'Custom',
                        'description' => 'User-defined format with placeholders',
                        'example' => 'CUSTOM-{random:4}-{year}'
                    ]
                ]
            ]
        ]);
    }
}
