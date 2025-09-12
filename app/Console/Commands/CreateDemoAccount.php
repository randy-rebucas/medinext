<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Database\Seeders\MinimalDemoSeeder;

class CreateDemoAccount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'demo:create
                            {--fresh : Drop all tables and recreate them}
                            {--force : Force the operation without confirmation}
                            {--clinic-name=Demo Medical Center : Name of the demo clinic}
                            {--admin-email=demo@medinext.com : Email for the demo admin}
                            {--admin-password=demo123 : Password for the demo admin}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a comprehensive demo account with sample data for testing and onboarding';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Starting Demo Account Creation Process...');
        $this->newLine();

        // Check if we should run fresh migration
        if ($this->option('fresh')) {
            if (!$this->option('force') && !$this->confirm('This will drop all existing data. Are you sure?')) {
                $this->error('Demo account creation cancelled.');
                return 1;
            }

            $this->info('🔄 Running fresh migrations...');
            Artisan::call('migrate:fresh', ['--force' => true]);
            $this->info('✅ Database migrated successfully');
        }

        // Check if demo data already exists
        if (!$this->option('fresh')) {
            $existingDemoClinic = DB::table('clinics')->where('slug', 'demo-medical-center')->exists();
            if ($existingDemoClinic && !$this->option('force')) {
                if (!$this->confirm('Demo clinic already exists. Do you want to continue and create additional demo data?')) {
                    $this->error('Demo account creation cancelled.');
                    return 1;
                }
            }
        }

        try {
            // Run the demo seeder
            $this->info('🌱 Seeding demo data...');
            $seeder = new MinimalDemoSeeder();
            $seeder->setCommand($this);
            $seeder->run();

            $this->newLine();
            $this->info('🎉 Demo account created successfully!');
            $this->newLine();

            // Display login information
            $this->displayLoginInfo();

            // Display next steps
            $this->displayNextSteps();

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Error creating demo account: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            return 1;
        }
    }

    private function displayLoginInfo(): void
    {
        $this->info('🔐 LOGIN INFORMATION');
        $this->info('==================');
        $this->newLine();

        $this->info('👤 Demo Admin Account:');
        $this->line('   Email: ' . $this->option('admin-email'));
        $this->line('   Password: ' . $this->option('admin-password'));
        $this->newLine();

        $this->info('👨‍⚕️ Demo Staff Accounts:');
        $this->line('   Doctor 1: doctor1@demomedical.com (demo123)');
        $this->line('   Doctor 2: doctor2@demomedical.com (demo123)');
        $this->line('   Doctor 3: doctor3@demomedical.com (demo123)');
        $this->line('   Doctor 4: doctor4@demomedical.com (demo123)');
        $this->line('   Doctor 5: doctor5@demomedical.com (demo123)');
        $this->line('   Receptionist: receptionist@demomedical.com (demo123)');
        $this->newLine();
    }

    private function displayNextSteps(): void
    {
        $this->info('📋 NEXT STEPS');
        $this->info('=============');
        $this->newLine();

        $this->line('1. 🌐 Start your development server:');
        $this->line('   php artisan serve');
        $this->newLine();

        $this->line('2. 🔗 Access the application:');
        $this->line('   http://localhost:8000');
        $this->newLine();

        $this->line('3. 🔑 Log in with the demo admin account');
        $this->newLine();

        $this->line('4. 🎯 Explore the demo features:');
        $this->line('   • Patient Management');
        $this->line('   • Appointment Scheduling');
        $this->line('   • Prescription Management');
        $this->line('   • Lab Results');
        $this->line('   • Billing System');
        $this->line('   • Reports & Analytics');
        $this->newLine();

        $this->line('5. 🧪 Test different user roles:');
        $this->line('   • Admin: Full system access');
        $this->line('   • Doctor: Medical records and appointments');
        $this->line('   • Receptionist: Patient registration and scheduling');
        $this->newLine();

        $this->line('6. 📊 View demo data:');
        $this->line('   • 8 Sample patients with complete profiles');
        $this->line('   • 5 Doctors with different specialties');
        $this->line('   • 60+ Appointments (past and future)');
        $this->line('   • Prescriptions and lab results');
        $this->line('   • Bills and insurance records');
        $this->line('   • Activity logs and notifications');
        $this->newLine();

        $this->info('💡 TIP: Use the demo data to showcase features to potential clients!');
        $this->newLine();
    }
}
