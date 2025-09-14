<?php

namespace App\Console\Commands;

use App\Services\LicenseKeyGenerator;
use App\Models\License;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class MediNextLicense extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'medinext:license
                            {action : Action to perform (generate, validate, status)}
                            {--count=1 : Number of license keys to generate}
                            {--strategy=standard : Generation strategy (standard, compact, segmented, custom)}
                            {--prefix=MEDI : License key prefix}
                            {--segment-length=4 : Length of each segment}
                            {--segments=4 : Number of segments}
                            {--length=12 : Total length for compact strategy}
                            {--format= : Custom format for segmented/custom strategies}
                            {--output= : Output file path to save generated keys}
                            {--dry-run : Show what would be generated without actually generating}
                            {--validate : Validate generated keys}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage MediNext EMR license keys and validation';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->argument('action');

        switch ($action) {
            case 'generate':
                return $this->generateLicenseKeys();
            case 'validate':
                return $this->validateLicenseKeys();
            case 'status':
                return $this->showStatus();
            default:
                $this->error("Invalid action: {$action}");
                $this->info('Valid actions: generate, validate, status');
                return Command::FAILURE;
        }
    }

    private function generateLicenseKeys(): int
    {
        $count = (int) $this->option('count');
        $strategy = $this->option('strategy');
        $dryRun = $this->option('dry-run');
        $validate = $this->option('validate');
        $outputFile = $this->option('output');

        // Validate strategy
        $validStrategies = [
            LicenseKeyGenerator::STRATEGY_STANDARD,
            LicenseKeyGenerator::STRATEGY_COMPACT,
            LicenseKeyGenerator::STRATEGY_SEGMENTED,
            LicenseKeyGenerator::STRATEGY_CUSTOM
        ];

        if (!in_array($strategy, $validStrategies)) {
            $this->error("Invalid strategy: {$strategy}");
            $this->info("Valid strategies: " . implode(', ', $validStrategies));
            return Command::FAILURE;
        }

        // Build options
        $options = $this->buildOptions($strategy);

        if ($dryRun) {
            $this->info("DRY RUN - Would generate {$count} license key(s) with strategy: {$strategy}");
            $this->displayOptions($options);
            return Command::SUCCESS;
        }

        $this->info("🔑 Generating {$count} MediNext EMR license key(s) with strategy: {$strategy}");

        try {
            $startTime = microtime(true);
            
            if ($count === 1) {
                $keys = [LicenseKeyGenerator::generate($strategy, $options)];
            } else {
                $keys = LicenseKeyGenerator::generateMultiple($count, $strategy, $options);
            }

            $endTime = microtime(true);
            $duration = round($endTime - $startTime, 2);

            $this->info("✅ Successfully generated " . count($keys) . " license key(s) in {$duration} seconds");

            // Display generated keys
            $this->displayGeneratedKeys($keys);

            // Validate keys if requested
            if ($validate) {
                $this->validateKeys($keys, $strategy);
            }

            // Save to file if output specified
            if ($outputFile) {
                $this->saveToFile($keys, $outputFile);
            }

            // Log the generation
            Log::info('MediNext EMR license keys generated via console command', [
                'count' => count($keys),
                'strategy' => $strategy,
                'options' => $options,
                'duration' => $duration,
                'user' => 'console'
            ]);

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("❌ Failed to generate license keys: " . $e->getMessage());
            Log::error('Failed to generate MediNext EMR license keys via console command', [
                'error' => $e->getMessage(),
                'count' => $count,
                'strategy' => $strategy,
                'options' => $options
            ]);
            return Command::FAILURE;
        }
    }

    private function validateLicenseKeys(): int
    {
        $this->info('🔍 Validating MediNext EMR License Keys...');
        $this->newLine();

        try {
            // Get all licenses from database
            $licenses = License::all();
            
            if ($licenses->isEmpty()) {
                $this->info('No license keys found in database.');
                return Command::SUCCESS;
            }

            $validCount = 0;
            $invalidCount = 0;
            $expiredCount = 0;
            $activeCount = 0;

            $this->info('Validating ' . $licenses->count() . ' license key(s)...');
            $this->newLine();

            foreach ($licenses as $license) {
                $isValid = LicenseKeyGenerator::validateFormat($license->license_key, 'standard');
                $isExpired = $license->expires_at && $license->expires_at->isPast();
                $isActive = $license->is_active;

                if ($isValid) {
                    $validCount++;
                    $status = $isExpired ? 'EXPIRED' : ($isActive ? 'ACTIVE' : 'INACTIVE');
                    $this->line("✅ {$license->license_key} - {$status}");
                } else {
                    $invalidCount++;
                    $this->line("❌ {$license->license_key} - INVALID FORMAT");
                }

                if ($isExpired) {
                    $expiredCount++;
                } elseif ($isActive) {
                    $activeCount++;
                }
            }

            $this->newLine();
            $this->info('📊 Validation Summary:');
            $this->line("  • Valid Keys: {$validCount}");
            $this->line("  • Invalid Keys: {$invalidCount}");
            $this->line("  • Active Keys: {$activeCount}");
            $this->line("  • Expired Keys: {$expiredCount}");

            return $invalidCount > 0 ? Command::FAILURE : Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Failed to validate license keys: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    private function showStatus(): int
    {
        $this->info('📊 MediNext EMR License Status');
        $this->info('==============================');
        $this->newLine();

        try {
            $totalLicenses = License::count();
            $activeLicenses = License::where('is_active', true)->count();
            $expiredLicenses = License::where('expires_at', '<', now())->count();
            $trialLicenses = License::where('type', 'trial')->count();
            $fullLicenses = License::where('type', 'full')->count();

            $this->info("🔑 Total Licenses: {$totalLicenses}");
            $this->info("✅ Active Licenses: {$activeLicenses}");
            $this->info("⏰ Expired Licenses: {$expiredLicenses}");
            $this->info("🧪 Trial Licenses: {$trialLicenses}");
            $this->info("💎 Full Licenses: {$fullLicenses}");
            $this->newLine();

            if ($totalLicenses > 0) {
                $this->info('📋 License Details:');
                $licenses = License::with('clinic')->get();
                foreach ($licenses as $license) {
                    $clinicName = $license->clinic ? $license->clinic->name : 'Unassigned';
                    $status = $license->is_active ? 'ACTIVE' : 'INACTIVE';
                    $expiry = $license->expires_at ? $license->expires_at->format('Y-m-d') : 'Never';
                    $this->line("   • {$license->license_key} - {$clinicName} - {$status} - Expires: {$expiry}");
                }
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Failed to get license status: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Build options array based on strategy and command options
     */
    protected function buildOptions(string $strategy): array
    {
        $options = [];

        switch ($strategy) {
            case LicenseKeyGenerator::STRATEGY_STANDARD:
                $options['prefix'] = $this->option('prefix');
                $options['segment_length'] = (int) $this->option('segment-length');
                $options['segments'] = (int) $this->option('segments');
                break;

            case LicenseKeyGenerator::STRATEGY_COMPACT:
                $options['prefix'] = $this->option('prefix');
                $options['length'] = (int) $this->option('length');
                break;

            case LicenseKeyGenerator::STRATEGY_SEGMENTED:
                $options['format'] = $this->option('format') ?: LicenseKeyGenerator::DEFAULT_FORMAT;
                $options['segment_length'] = (int) $this->option('segment-length');
                break;

            case LicenseKeyGenerator::STRATEGY_CUSTOM:
                $format = $this->option('format');
                if (!$format) {
                    throw new \InvalidArgumentException('Custom format is required for custom strategy');
                }
                $options['format'] = $format;
                break;
        }

        return $options;
    }

    /**
     * Display generation options
     */
    protected function displayOptions(array $options): void
    {
        $this->info("Options:");
        foreach ($options as $key => $value) {
            $this->line("  {$key}: {$value}");
        }
    }

    /**
     * Display generated license keys
     */
    protected function displayGeneratedKeys(array $keys): void
    {
        $this->info("\n🔑 Generated MediNext EMR License Keys:");
        $this->line(str_repeat('-', 50));

        foreach ($keys as $index => $key) {
            $this->line(sprintf("%3d. %s", $index + 1, $key));
        }

        $this->line(str_repeat('-', 50));
    }

    /**
     * Validate generated keys
     */
    protected function validateKeys(array $keys, string $strategy): void
    {
        $this->info("\n🔍 Validating generated keys...");
        
        $validCount = 0;
        $invalidKeys = [];

        foreach ($keys as $key) {
            if (LicenseKeyGenerator::validateFormat($key, $strategy)) {
                $validCount++;
            } else {
                $invalidKeys[] = $key;
            }
        }

        if ($validCount === count($keys)) {
            $this->info("✅ All {$validCount} keys are valid");
        } else {
            $this->warn("⚠️  {$validCount} valid, " . count($invalidKeys) . " invalid");
            foreach ($invalidKeys as $invalidKey) {
                $this->error("  Invalid: {$invalidKey}");
            }
        }
    }

    /**
     * Save generated keys to file
     */
    protected function saveToFile(array $keys, string $outputFile): void
    {
        try {
            $content = "# MediNext EMR Generated License Keys\n";
            $content .= "# Generated at: " . now()->toDateTimeString() . "\n";
            $content .= "# Count: " . count($keys) . "\n\n";

            foreach ($keys as $index => $key) {
                $content .= sprintf("%d. %s\n", $index + 1, $key);
            }

            file_put_contents($outputFile, $content);
            $this->info("✅ License keys saved to: {$outputFile}");
        } catch (\Exception $e) {
            $this->error("❌ Failed to save keys to file: " . $e->getMessage());
        }
    }
}
