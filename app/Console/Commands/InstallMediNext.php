<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Role;
use App\Models\Clinic;
use App\Models\Setting;
use App\Services\InstallationService;

class InstallMediNext extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'medinext:install
                            {--admin-name= : Admin user full name}
                            {--admin-email= : Admin user email}
                            {--admin-password= : Admin user password}
                            {--clinic-name= : Clinic name}
                            {--clinic-phone= : Clinic phone number}
                            {--clinic-email= : Clinic email}
                            {--clinic-address= : Clinic address}
                            {--interactive : Run in interactive mode}
                            {--force : Force installation even if already installed}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install MediNext EMR system via command line';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 MediNext EMR Installation');
        $this->info('============================');
        $this->newLine();

        // Check if already installed
        if (!$this->option('force') && $this->isAlreadyInstalled()) {
            $this->error('❌ MediNext is already installed!');
            $this->line('Use --force to reinstall or access the web interface.');
            return Command::FAILURE;
        }

        try {
            // Check system requirements
            if (!$this->checkSystemRequirements()) {
                $this->error('❌ System requirements not met. Please fix the issues above.');
                return Command::FAILURE;
            }

            // Get installation configuration
            $config = $this->getInstallationConfig();

            // Run installation
            $this->info('🔧 Starting installation...');
            $installationService = new InstallationService();
            $result = $installationService->runInstallation($config);

            if (!$result['success']) {
                $this->error('❌ Installation failed: ' . $result['message']);
                return Command::FAILURE;
            }

            $this->newLine();
            $this->info('✅ Installation completed successfully!');
            $this->newLine();

            // Display installation summary
            $this->displayInstallationSummary($result);

            // Display next steps
            $this->displayNextSteps();

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Installation failed: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            return Command::FAILURE;
        }
    }

    /**
     * Check if system is already installed
     */
    private function isAlreadyInstalled(): bool
    {
        try {
            if (!DB::getSchemaBuilder()->hasTable('users')) {
                return false;
            }

            return User::whereHas('roles', function($query) {
                $query->where('name', 'superadmin');
            })->exists();

        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Check system requirements
     */
    private function checkSystemRequirements(): bool
    {
        $this->info('📋 Checking system requirements...');

        $requirements = [
            'PHP Version' => version_compare(PHP_VERSION, '8.1.0', '>='),
            'OpenSSL Extension' => extension_loaded('openssl'),
            'PDO Extension' => extension_loaded('pdo'),
            'Mbstring Extension' => extension_loaded('mbstring'),
            'Tokenizer Extension' => extension_loaded('tokenizer'),
            'XML Extension' => extension_loaded('xml'),
            'Ctype Extension' => extension_loaded('ctype'),
            'JSON Extension' => extension_loaded('json'),
            'BCMath Extension' => extension_loaded('bcmath'),
            'Fileinfo Extension' => extension_loaded('fileinfo'),
            'cURL Extension' => extension_loaded('curl'),
            'GD Extension' => extension_loaded('gd'),
            'Storage Writable' => is_writable(storage_path()),
            'Bootstrap Cache Writable' => is_writable(base_path('bootstrap/cache')),
        ];

        $allMet = true;
        foreach ($requirements as $requirement => $met) {
            $status = $met ? '✅' : '❌';
            $this->line("   {$status} {$requirement}");
            if (!$met) {
                $allMet = false;
            }
        }

        $this->newLine();
        return $allMet;
    }

    /**
     * Get installation configuration
     */
    private function getInstallationConfig(): array
    {
        if ($this->option('interactive')) {
            return $this->getInteractiveConfig();
        }

        return $this->getCommandLineConfig();
    }

    /**
     * Get configuration from command line options
     */
    private function getCommandLineConfig(): array
    {
        $requiredOptions = [
            'admin-name', 'admin-email', 'admin-password',
            'clinic-name', 'clinic-phone', 'clinic-email', 'clinic-address'
        ];

        $missingOptions = [];
        foreach ($requiredOptions as $option) {
            if (!$this->option($option)) {
                $missingOptions[] = $option;
            }
        }

        if (!empty($missingOptions)) {
            $this->error('❌ Missing required options: ' . implode(', ', $missingOptions));
            $this->line('Use --interactive for guided setup or provide all required options.');
            exit(1);
        }

        return [
            'admin' => [
                'name' => $this->option('admin-name'),
                'email' => $this->option('admin-email'),
                'password' => $this->option('admin-password'),
            ],
            'clinic' => [
                'name' => $this->option('clinic-name'),
                'phone' => $this->option('clinic-phone'),
                'email' => $this->option('clinic-email'),
                'address' => $this->option('clinic-address'),
            ],
            'system' => [
                'timezone' => 'UTC',
                'date_format' => 'Y-m-d',
                'time_format' => 'H:i',
                'currency' => 'USD',
                'language' => 'en',
            ],
            'security' => [
                'session_timeout' => 480,
                'password_min_length' => 8,
                'require_2fa' => false,
                'audit_log_retention_days' => 2555,
                'patient_data_retention_days' => 2555,
            ],
            'appointments' => [
                'default_duration' => 30,
                'buffer_time' => 15,
                'auto_confirm' => true,
                'allow_online_booking' => true,
                'max_advance_days' => 90,
                'min_advance_hours' => 2,
                'cancellation_hours' => 24,
                'reminder_hours' => 24,
                'max_per_day' => 50,
            ],
            'notifications' => [
                'email_enabled' => true,
                'sms_enabled' => false,
                'appointment_reminder_hours' => 24,
                'follow_up_days' => 7,
            ],
            'branding' => [
                'primary_color' => '#3B82F6',
                'secondary_color' => '#1E40AF',
                'logo_url' => '',
                'favicon_url' => '',
            ],
        ];
    }

    /**
     * Get configuration interactively
     */
    private function getInteractiveConfig(): array
    {
        $this->info('📝 Please provide the following information:');
        $this->newLine();

        // Admin information
        $this->info('👤 Administrator Information:');
        $adminName = $this->ask('Admin Full Name');
        $adminEmail = $this->ask('Admin Email');
        $adminPassword = $this->secret('Admin Password (min 8 characters)');

        // Validate admin password
        while (strlen($adminPassword) < 8) {
            $this->error('Password must be at least 8 characters long.');
            $adminPassword = $this->secret('Admin Password (min 8 characters)');
        }

        $this->newLine();

        // Clinic information
        $this->info('🏥 Clinic Information:');
        $clinicName = $this->ask('Clinic Name');
        $clinicPhone = $this->ask('Clinic Phone Number');
        $clinicEmail = $this->ask('Clinic Email');
        $clinicAddress = $this->ask('Clinic Address');

        $this->newLine();

        return [
            'admin' => [
                'name' => $adminName,
                'email' => $adminEmail,
                'password' => $adminPassword,
            ],
            'clinic' => [
                'name' => $clinicName,
                'phone' => $clinicPhone,
                'email' => $clinicEmail,
                'address' => $clinicAddress,
            ],
            'system' => [
                'timezone' => 'UTC',
                'date_format' => 'Y-m-d',
                'time_format' => 'H:i',
                'currency' => 'USD',
                'language' => 'en',
            ],
            'security' => [
                'session_timeout' => 480,
                'password_min_length' => 8,
                'require_2fa' => false,
                'audit_log_retention_days' => 2555,
                'patient_data_retention_days' => 2555,
            ],
            'appointments' => [
                'default_duration' => 30,
                'buffer_time' => 15,
                'auto_confirm' => true,
                'allow_online_booking' => true,
                'max_advance_days' => 90,
                'min_advance_hours' => 2,
                'cancellation_hours' => 24,
                'reminder_hours' => 24,
                'max_per_day' => 50,
            ],
            'notifications' => [
                'email_enabled' => true,
                'sms_enabled' => false,
                'appointment_reminder_hours' => 24,
                'follow_up_days' => 7,
            ],
            'branding' => [
                'primary_color' => '#3B82F6',
                'secondary_color' => '#1E40AF',
                'logo_url' => '',
                'favicon_url' => '',
            ],
        ];
    }

    /**
     * Display installation summary
     */
    private function displayInstallationSummary(array $result): void
    {
        $this->info('📊 Installation Summary:');
        $this->info('========================');
        $this->newLine();

        $this->line('✅ Database migrated successfully');
        $this->line('✅ System permissions and roles created');
        $this->line('✅ Admin account created');
        $this->line('✅ Clinic information configured');
        $this->line('✅ Default settings applied');
        $this->line('✅ Initial data seeded');
        $this->newLine();

        if (isset($result['admin_user'])) {
            $this->info('👤 Admin Account:');
            $this->line('   Email: ' . $result['admin_user']->email);
            $this->line('   Role: Super Administrator');
            $this->newLine();
        }

        if (isset($result['clinic'])) {
            $this->info('🏥 Clinic:');
            $this->line('   Name: ' . $result['clinic']->name);
            $this->line('   Email: ' . $result['clinic']->email);
            $this->newLine();
        }
    }

    /**
     * Display next steps
     */
    private function displayNextSteps(): void
    {
        $this->info('📋 Next Steps:');
        $this->info('==============');
        $this->newLine();

        $this->line('1. 🌐 Access your MediNext installation:');
        $this->line('   http://your-domain.com');
        $this->newLine();

        $this->line('2. 🔑 Log in with your admin credentials');
        $this->newLine();

        $this->line('3. ⚙️  Configure additional settings:');
        $this->line('   • Update clinic information');
        $this->line('   • Set working hours');
        $this->line('   • Configure appointment types');
        $this->line('   • Add staff members');
        $this->newLine();

        $this->line('4. 🧹 Clean up installation files:');
        $this->line('   php artisan install:cleanup');
        $this->newLine();

        $this->line('5. 🔒 Review security settings');
        $this->newLine();

        $this->info('🎉 MediNext EMR is ready for use!');
        $this->newLine();
    }
}
