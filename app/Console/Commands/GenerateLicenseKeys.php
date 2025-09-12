<?php

namespace App\Console\Commands;

use App\Services\LicenseKeyGenerator;
use App\Models\License;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GenerateLicenseKeys extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'license:generate-keys 
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
    protected $description = 'Generate unique license keys using various strategies';

    /**
     * Execute the console command.
     */
    public function handle()
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
            return 1;
        }

        // Build options
        $options = $this->buildOptions($strategy);

        if ($dryRun) {
            $this->info("DRY RUN - Would generate {$count} license key(s) with strategy: {$strategy}");
            $this->displayOptions($options);
            return 0;
        }

        $this->info("Generating {$count} license key(s) with strategy: {$strategy}");

        try {
            $startTime = microtime(true);
            
            if ($count === 1) {
                $keys = [LicenseKeyGenerator::generate($strategy, $options)];
            } else {
                $keys = LicenseKeyGenerator::generateMultiple($count, $strategy, $options);
            }

            $endTime = microtime(true);
            $duration = round($endTime - $startTime, 2);

            $this->info("Successfully generated " . count($keys) . " license key(s) in {$duration} seconds");

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
            Log::info('License keys generated via console command', [
                'count' => count($keys),
                'strategy' => $strategy,
                'options' => $options,
                'duration' => $duration,
                'user' => 'console'
            ]);

            return 0;

        } catch (\Exception $e) {
            $this->error("Failed to generate license keys: " . $e->getMessage());
            Log::error('Failed to generate license keys via console command', [
                'error' => $e->getMessage(),
                'count' => $count,
                'strategy' => $strategy,
                'options' => $options
            ]);
            return 1;
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
        $this->info("\nGenerated License Keys:");
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
        $this->info("\nValidating generated keys...");
        
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
            $content = "# Generated License Keys\n";
            $content .= "# Generated at: " . now()->toDateTimeString() . "\n";
            $content .= "# Count: " . count($keys) . "\n\n";

            foreach ($keys as $index => $key) {
                $content .= sprintf("%d. %s\n", $index + 1, $key);
            }

            file_put_contents($outputFile, $content);
            $this->info("✅ License keys saved to: {$outputFile}");
        } catch (\Exception $e) {
            $this->error("Failed to save keys to file: " . $e->getMessage());
        }
    }
}
