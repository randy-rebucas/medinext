<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Database\Seeders\BaseSeeder;
use Illuminate\Support\Facades\DB;

class MediNextSeeder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'medinext:seed 
                            {--step=* : Specific seeding steps to run (core, infrastructure, users, business, activity)}
                            {--method=* : Specific methods to run (permissions, roles, settings, clinics, rooms, nova-admin, demo-users, doctors, patients, appointments, encounters, prescriptions, lab-results, bills, insurance, queue, notifications, activity-logs)}
                            {--skip-existing : Skip creating records that already exist}
                            {--validate-data : Validate data before inserting}
                            {--show-progress : Show progress during seeding}
                            {--memory-optimized : Use memory optimization}
                            {--create-demo-data : Create demo data}
                            {--list : List all available seeding options}
                            {--interactive : Interactive mode to select options}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run BaseSeeder with options to select specific seeds or steps';

    /**
     * Available seeding steps
     */
    private $availableSteps = [
        'core' => [
            'name' => 'Core System',
            'description' => 'Permissions, roles, and default settings',
            'methods' => ['permissions', 'roles', 'settings']
        ],
        'infrastructure' => [
            'name' => 'Infrastructure',
            'description' => 'Clinics and rooms',
            'methods' => ['clinics', 'rooms']
        ],
        'users' => [
            'name' => 'Users and Roles',
            'description' => 'Nova admin, demo users, and doctors',
            'methods' => ['nova-admin', 'demo-users', 'doctors']
        ],
        'business' => [
            'name' => 'Business Data',
            'description' => 'Patients, appointments, encounters, prescriptions, lab results, bills, insurance, queue, notifications',
            'methods' => ['patients', 'appointments', 'encounters', 'prescriptions', 'lab-results', 'bills', 'insurance', 'queue', 'notifications']
        ],
        'activity' => [
            'name' => 'Activity Logs',
            'description' => 'System activity logs',
            'methods' => ['activity-logs']
        ]
    ];

    /**
     * Available individual methods
     */
    private $availableMethods = [
        'permissions' => 'Create system permissions',
        'roles' => 'Create user roles',
        'settings' => 'Create default settings',
        'clinics' => 'Create clinics',
        'rooms' => 'Create rooms',
        'nova-admin' => 'Create Nova admin user',
        'demo-users' => 'Create demo users',
        'doctors' => 'Create doctors',
        'patients' => 'Create patients',
        'appointments' => 'Create appointments',
        'encounters' => 'Create encounters',
        'prescriptions' => 'Create prescriptions',
        'lab-results' => 'Create lab results',
        'bills' => 'Create bills',
        'insurance' => 'Create insurance records',
        'queue' => 'Create queue data',
        'notifications' => 'Create notifications',
        'activity-logs' => 'Create activity logs'
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🌱 MediNext Database Seeder');
        $this->info('============================');

        // Handle list option
        if ($this->option('list')) {
            $this->displayAvailableOptions();
            return 0;
        }

        // Handle interactive mode
        if ($this->option('interactive')) {
            return $this->handleInteractiveMode();
        }

        // Get selected steps and methods
        $selectedSteps = $this->option('step');
        $selectedMethods = $this->option('method');

        // If no specific options provided, run full seeder
        if (empty($selectedSteps) && empty($selectedMethods)) {
            $this->info('No specific options provided. Running full BaseSeeder...');
            $this->runFullSeeder();
            return 0;
        }

        // Validate selected options
        if (!$this->validateOptions($selectedSteps, $selectedMethods)) {
            return 1;
        }

        // Run selected seeds
        $this->runSelectedSeeds($selectedSteps, $selectedMethods);

        return 0;
    }

    /**
     * Display available seeding options
     */
    private function displayAvailableOptions(): void
    {
        $this->info('');
        $this->info('📋 Available Seeding Steps:');
        $this->info('============================');
        
        foreach ($this->availableSteps as $key => $step) {
            $this->line("  <comment>{$key}</comment> - {$step['name']}");
            $this->line("      {$step['description']}");
            $this->line("      Methods: " . implode(', ', $step['methods']));
            $this->line('');
        }

        $this->info('🔧 Available Individual Methods:');
        $this->info('=================================');
        
        foreach ($this->availableMethods as $key => $description) {
            $this->line("  <comment>{$key}</comment> - {$description}");
        }

        $this->info('');
        $this->info('💡 Usage Examples:');
        $this->info('==================');
        $this->line('  # Run specific steps:');
        $this->line('  php artisan medinext:seed --step=core --step=infrastructure');
        $this->line('');
        $this->line('  # Run specific methods:');
        $this->line('  php artisan medinext:seed --method=permissions --method=roles');
        $this->line('');
        $this->line('  # Run with options:');
        $this->line('  php artisan medinext:seed --step=core --skip-existing --show-progress');
        $this->line('');
        $this->line('  # Interactive mode:');
        $this->line('  php artisan medinext:seed --interactive');
    }

    /**
     * Handle interactive mode
     */
    private function handleInteractiveMode(): int
    {
        $this->info('🎯 Interactive Seeding Mode');
        $this->info('============================');

        // Ask for seeding type
        $seedingType = $this->choice(
            'What would you like to seed?',
            ['Full Seeder (All)', 'Specific Steps', 'Specific Methods', 'Custom Selection'],
            'Full Seeder (All)'
        );

        switch ($seedingType) {
            case 'Full Seeder (All)':
                $this->runFullSeeder();
                break;

            case 'Specific Steps':
                $this->runInteractiveSteps();
                break;

            case 'Specific Methods':
                $this->runInteractiveMethods();
                break;

            case 'Custom Selection':
                $this->runCustomSelection();
                break;
        }

        return 0;
    }

    /**
     * Run interactive steps selection
     */
    private function runInteractiveSteps(): void
    {
        $this->info('');
        $this->info('Select the steps you want to run:');
        
        $stepChoices = [];
        foreach ($this->availableSteps as $key => $step) {
            $stepChoices[$key] = "{$step['name']} - {$step['description']}";
        }

        $selectedSteps = $this->choice(
            'Choose steps (use comma to separate multiple choices)',
            $stepChoices,
            null,
            null,
            true
        );

        $this->runSelectedSeeds($selectedSteps, []);
    }

    /**
     * Run interactive methods selection
     */
    private function runInteractiveMethods(): void
    {
        $this->info('');
        $this->info('Select the methods you want to run:');
        
        $methodChoices = [];
        foreach ($this->availableMethods as $key => $description) {
            $methodChoices[$key] = $description;
        }

        $selectedMethods = $this->choice(
            'Choose methods (use comma to separate multiple choices)',
            $methodChoices,
            null,
            null,
            true
        );

        $this->runSelectedSeeds([], $selectedMethods);
    }

    /**
     * Run custom selection
     */
    private function runCustomSelection(): void
    {
        $this->info('');
        $this->info('Custom Selection Mode');
        $this->info('=====================');

        // Ask for steps
        $this->info('Available steps: ' . implode(', ', array_keys($this->availableSteps)));
        $stepInput = $this->ask('Enter steps to run (comma-separated, or press Enter to skip)');
        $selectedSteps = $stepInput ? array_map('trim', explode(',', $stepInput)) : [];

        // Ask for methods
        $this->info('Available methods: ' . implode(', ', array_keys($this->availableMethods)));
        $methodInput = $this->ask('Enter methods to run (comma-separated, or press Enter to skip)');
        $selectedMethods = $methodInput ? array_map('trim', explode(',', $methodInput)) : [];

        if (empty($selectedSteps) && empty($selectedMethods)) {
            $this->error('No steps or methods selected. Exiting.');
            return;
        }

        $this->runSelectedSeeds($selectedSteps, $selectedMethods);
    }

    /**
     * Validate selected options
     */
    private function validateOptions(array $selectedSteps, array $selectedMethods): bool
    {
        $valid = true;

        // Validate steps
        foreach ($selectedSteps as $step) {
            if (!array_key_exists($step, $this->availableSteps)) {
                $this->error("Invalid step: {$step}");
                $valid = false;
            }
        }

        // Validate methods
        foreach ($selectedMethods as $method) {
            if (!array_key_exists($method, $this->availableMethods)) {
                $this->error("Invalid method: {$method}");
                $valid = false;
            }
        }

        if (!$valid) {
            $this->error('Please use --list to see available options.');
        }

        return $valid;
    }

    /**
     * Run selected seeds
     */
    private function runSelectedSeeds(array $selectedSteps, array $selectedMethods): void
    {
        $startTime = microtime(true);

        // Optimize memory usage
        $this->optimizeMemory();

        $this->info('');
        $this->info('🚀 Starting selected seeding...');

        if (!empty($selectedSteps)) {
            $this->info('Selected steps: ' . implode(', ', $selectedSteps));
        }
        if (!empty($selectedMethods)) {
            $this->info('Selected methods: ' . implode(', ', $selectedMethods));
        }

        $this->info('');

        try {
            DB::transaction(function () use ($selectedSteps, $selectedMethods) {
                $seeder = new BaseSeeder();
                
                // Set command instance and options
                $seeder->setCommand($this);
                $seeder->setOptions($this->getSeederOptions());
                
                // Run selected steps
                if (!empty($selectedSteps)) {
                    $this->runSteps($seeder, $selectedSteps);
                }
                
                // Run selected methods
                if (!empty($selectedMethods)) {
                    $this->runMethods($seeder, $selectedMethods);
                }
                
                // Force garbage collection
                gc_collect_cycles();
            });

            $endTime = microtime(true);
            $executionTime = round($endTime - $startTime, 2);

            $this->info('');
            $this->info('✅ Selected seeding completed successfully!');
            $this->info("⏱️  Execution time: {$executionTime} seconds");

        } catch (\Exception $e) {
            $this->error('❌ Seeding failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Run full seeder
     */
    private function runFullSeeder(): void
    {
        $this->info('Running full BaseSeeder...');
        
        // Optimize memory usage
        $this->optimizeMemory();
        
        $seeder = new BaseSeeder();
        $seeder->setCommand($this);
        $seeder->setOptions($this->getSeederOptions());
        $seeder->run();
    }

    /**
     * Optimize memory usage
     */
    private function optimizeMemory(): void
    {
        // Increase memory limit
        ini_set('memory_limit', '4G');
        
        // Enable garbage collection
        gc_enable();
        
        // Force garbage collection
        gc_collect_cycles();
        
        $this->info('💾 Memory optimized for seeding operations');
    }

    /**
     * Run selected steps
     */
    private function runSteps(BaseSeeder $seeder, array $selectedSteps): void
    {
        foreach ($selectedSteps as $step) {
            $stepInfo = $this->availableSteps[$step];
            $this->info("📋 Running step: {$stepInfo['name']}");
            
            switch ($step) {
                case 'core':
                    $seeder->seedCoreSystem();
                    break;
                case 'infrastructure':
                    $seeder->seedInfrastructure();
                    break;
                case 'users':
                    $seeder->seedUsersAndRoles();
                    break;
                case 'business':
                    $seeder->seedBusinessData();
                    break;
                case 'activity':
                    $seeder->seedActivityLogs();
                    break;
            }
            
            // Force garbage collection after each step
            gc_collect_cycles();
        }
    }

    /**
     * Run selected methods
     */
    private function runMethods(BaseSeeder $seeder, array $selectedMethods): void
    {
        foreach ($selectedMethods as $method) {
            $this->info("🔧 Running method: {$this->availableMethods[$method]}");
            
            switch ($method) {
                case 'permissions':
                    $seeder->createPermissions();
                    break;
                case 'roles':
                    $seeder->createRoles();
                    break;
                case 'settings':
                    $seeder->createDefaultSettings();
                    break;
                case 'clinics':
                    $seeder->createClinics();
                    break;
                case 'rooms':
                    $seeder->createRooms();
                    break;
                case 'nova-admin':
                    $seeder->createNovaAdmin();
                    break;
                case 'demo-users':
                    $seeder->createDemoUsers();
                    break;
                case 'doctors':
                    $seeder->createDoctors();
                    break;
                case 'patients':
                    $seeder->createPatients();
                    break;
                case 'appointments':
                    $seeder->createAppointments();
                    break;
                case 'encounters':
                    $seeder->createEncounters();
                    break;
                case 'prescriptions':
                    $seeder->createPrescriptions();
                    break;
                case 'lab-results':
                    $seeder->createLabResults();
                    break;
                case 'bills':
                    $seeder->createBills();
                    break;
                case 'insurance':
                    $seeder->createInsurance();
                    break;
                case 'queue':
                    $seeder->createQueue();
                    break;
                case 'notifications':
                    $seeder->createNotifications();
                    break;
                case 'activity-logs':
                    $seeder->createActivityLogs();
                    break;
            }
            
            // Force garbage collection after each method
            gc_collect_cycles();
        }
    }

    /**
     * Get seeder options from command options
     */
    private function getSeederOptions(): array
    {
        return [
            'skip_existing' => $this->option('skip-existing'),
            'validate_data' => $this->option('validate-data'),
            'show_progress' => $this->option('show-progress'),
            'memory_optimized' => $this->option('memory-optimized'),
            'create_demo_data' => $this->option('create-demo-data'),
        ];
    }
}
