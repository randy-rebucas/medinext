<?php

namespace App\Services;

use App\Models\License;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class LicenseKeyGenerator
{
    /**
     * License key generation strategies
     */
    const STRATEGY_STANDARD = 'standard';
    const STRATEGY_COMPACT = 'compact';
    const STRATEGY_SEGMENTED = 'segmented';
    const STRATEGY_CUSTOM = 'custom';

    /**
     * Default license key format
     */
    const DEFAULT_FORMAT = 'MEDI-{segment1}-{segment2}-{segment3}-{segment4}';

    /**
     * Generate a unique license key using the specified strategy
     */
    public static function generate(string $strategy = self::STRATEGY_STANDARD, array $options = []): string
    {
        $method = 'generate' . ucfirst($strategy) . 'Key';

        if (!method_exists(self::class, $method)) {
            throw new \InvalidArgumentException("Invalid license key generation strategy: {$strategy}");
        }

        $attempts = 0;
        $maxAttempts = 100;

        do {
            $key = self::$method($options);
            $attempts++;

            if ($attempts >= $maxAttempts) {
                throw new \RuntimeException('Unable to generate unique license key after maximum attempts');
            }
        } while (self::keyExists($key));

        // Log the generation for audit purposes
        Log::info('License key generated', [
            'strategy' => $strategy,
            'key' => $key,
            'attempts' => $attempts,
            'options' => $options
        ]);

        return $key;
    }

    /**
     * Generate standard license key (MEDI-XXXX-XXXX-XXXX-XXXX)
     */
    protected static function generateStandardKey(array $options = []): string
    {
        $prefix = $options['prefix'] ?? 'MEDI';
        $segmentLength = $options['segment_length'] ?? 4;
        $segments = $options['segments'] ?? 4;

        $key = $prefix;
        for ($i = 0; $i < $segments; $i++) {
            $key .= '-' . strtoupper(Str::random($segmentLength));
        }

        return $key;
    }

    /**
     * Generate compact license key (MEDI-XXXXXXXXXXXX)
     */
    protected static function generateCompactKey(array $options = []): string
    {
        $prefix = $options['prefix'] ?? 'MEDI';
        $length = $options['length'] ?? 12;

        return $prefix . '-' . strtoupper(Str::random($length));
    }

    /**
     * Generate segmented license key with custom segments
     */
    protected static function generateSegmentedKey(array $options = []): string
    {
        $format = $options['format'] ?? self::DEFAULT_FORMAT;
        $segmentLength = $options['segment_length'] ?? 4;

        $key = $format;
        $segmentCount = substr_count($format, '{segment');

        for ($i = 1; $i <= $segmentCount; $i++) {
            $segment = strtoupper(Str::random($segmentLength));
            $key = str_replace("{segment{$i}}", $segment, $key);
        }

        return $key;
    }

    /**
     * Generate custom license key based on provided format
     */
    protected static function generateCustomKey(array $options = []): string
    {
        if (!isset($options['format'])) {
            throw new \InvalidArgumentException('Custom format is required for custom strategy');
        }

        $format = $options['format'];
        $key = $format;

        // Replace placeholders with random characters
        $key = preg_replace_callback('/\{random:(\d+)\}/', function ($matches) {
            return strtoupper(Str::random((int)$matches[1]));
        }, $key);

        // Replace timestamp placeholders
        $key = preg_replace_callback('/\{timestamp:([^}]+)\}/', function ($matches) {
            return date($matches[1]);
        }, $key);

        // Replace year placeholders
        $key = str_replace('{year}', date('Y'), $key);
        $key = str_replace('{month}', date('m'), $key);
        $key = str_replace('{day}', date('d'), $key);

        return $key;
    }

    /**
     * Check if a license key already exists
     */
    public static function keyExists(string $key): bool
    {
        return Cache::remember("license_key_exists_{$key}", 3600, function () use ($key) {
            return License::where('license_key', $key)->exists();
        });
    }

    /**
     * Validate license key format
     */
    public static function validateFormat(string $key, string $strategy = self::STRATEGY_STANDARD): bool
    {
        switch ($strategy) {
            case self::STRATEGY_STANDARD:
                return preg_match('/^MEDI-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}$/', $key);

            case self::STRATEGY_COMPACT:
                return preg_match('/^MEDI-[A-Z0-9]{12}$/', $key);

            case self::STRATEGY_SEGMENTED:
                return preg_match('/^MEDI-[A-Z0-9]+(-[A-Z0-9]+)*$/', $key);

            default:
                return !empty($key) && strlen($key) >= 10;
        }
    }

    /**
     * Generate multiple unique license keys
     */
    public static function generateMultiple(int $count, string $strategy = self::STRATEGY_STANDARD, array $options = []): array
    {
        $keys = [];
        $attempts = 0;
        $maxAttempts = $count * 10; // Allow more attempts for multiple keys

        while (count($keys) < $count && $attempts < $maxAttempts) {
            try {
                $key = self::generate($strategy, $options);
                if (!in_array($key, $keys)) {
                    $keys[] = $key;
                }
            } catch (\Exception $e) {
                Log::warning('Failed to generate license key', [
                    'attempt' => $attempts + 1,
                    'error' => $e->getMessage()
                ]);
            }
            $attempts++;
        }

        if (count($keys) < $count) {
            throw new \RuntimeException("Only generated " . count($keys) . " out of {$count} requested license keys");
        }

        return $keys;
    }

    /**
     * Generate license key with specific characteristics
     */
    public static function generateWithCharacteristics(array $characteristics = []): string
    {
        $options = [];

        // Set prefix based on license type
        if (isset($characteristics['license_type'])) {
            $prefixMap = [
                'standard' => 'STD',
                'premium' => 'PRM',
                'enterprise' => 'ENT',
            ];
            $options['prefix'] = $prefixMap[$characteristics['license_type']] ?? 'MEDI';
        }

        // Set strategy based on characteristics
        $strategy = $characteristics['strategy'] ?? self::STRATEGY_STANDARD;

        // Add custom options
        if (isset($characteristics['options'])) {
            $options = array_merge($options, $characteristics['options']);
        }

        return self::generate($strategy, $options);
    }

    /**
     * Get license key statistics
     */
    public static function getStatistics(): array
    {
        return Cache::remember('license_key_statistics', 3600, function () {
            $total = License::count();
            $byType = License::selectRaw('license_type, COUNT(*) as count')
                ->groupBy('license_type')
                ->pluck('count', 'license_type')
                ->toArray();

            $byStatus = License::selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            $expiring = License::where('expires_at', '<=', now()->addDays(30))
                ->where('status', 'active')
                ->count();

            return [
                'total_licenses' => $total,
                'by_type' => $byType,
                'by_status' => $byStatus,
                'expiring_soon' => $expiring,
                'generation_strategies' => [
                    self::STRATEGY_STANDARD => 'Standard (MEDI-XXXX-XXXX-XXXX-XXXX)',
                    self::STRATEGY_COMPACT => 'Compact (MEDI-XXXXXXXXXXXX)',
                    self::STRATEGY_SEGMENTED => 'Segmented (Custom segments)',
                    self::STRATEGY_CUSTOM => 'Custom (User-defined format)',
                ]
            ];
        });
    }

    /**
     * Clear license key cache
     */
    public static function clearCache(): void
    {
        Cache::forget('license_key_statistics');
        // Clear individual key existence caches would require tracking all keys
        // For now, we rely on the 1-hour TTL
    }

    /**
     * Generate activation code
     */
    public static function generateActivationCode(int $length = 8): string
    {
        return strtoupper(Str::random($length));
    }

    /**
     * Generate server fingerprint for license validation
     */
    public static function generateServerFingerprint(array $serverInfo = []): string
    {
        $defaultInfo = [
            'domain' => request()->getHost() ?? 'localhost',
            'ip' => request()->ip() ?? '127.0.0.1',
            'user_agent' => request()->userAgent() ?? 'unknown',
            'server_name' => $_SERVER['SERVER_NAME'] ?? 'localhost',
            'timestamp' => now()->timestamp,
        ];

        $info = array_merge($defaultInfo, $serverInfo);
        return hash('sha256', json_encode($info));
    }

    /**
     * Parse license key to extract information
     */
    public static function parseLicenseKey(string $key): array
    {
        $parts = explode('-', $key);

        return [
            'prefix' => $parts[0] ?? '',
            'segments' => array_slice($parts, 1),
            'segment_count' => count($parts) - 1,
            'total_length' => strlen($key),
            'format' => self::detectFormat($key),
        ];
    }

    /**
     * Detect license key format
     */
    protected static function detectFormat(string $key): string
    {
        if (preg_match('/^MEDI-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}$/', $key)) {
            return self::STRATEGY_STANDARD;
        }

        if (preg_match('/^MEDI-[A-Z0-9]{12}$/', $key)) {
            return self::STRATEGY_COMPACT;
        }

        if (preg_match('/^MEDI-[A-Z0-9]+(-[A-Z0-9]+)*$/', $key)) {
            return self::STRATEGY_SEGMENTED;
        }

        return self::STRATEGY_CUSTOM;
    }
}
