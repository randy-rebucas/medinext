<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Database\Seeders\BaseSeeder;

class MediNextSetup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'medinext:setup
                            {--fresh : Drop all tables and recreate them}
                            {--force : Force the operation without confirmation}
                            {--demo : Include demo data in setup}
                            {--memory=2G : Memory limit for seeding}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Complete MediNext EMR system setup with database migration and seeding';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Starting MediNext EMR System Setup...');
        $this->newLine();

        // Set memory limit
        $memoryLimit = $this->option('memory');
        ini_set('memory_limit', $memoryLimit);
        gc_enable();

        $this->info("Memory limit set to: " . ini_get('memory_limit'));
        $this->newLine();

        // Check if we should run fresh migration
        if ($this->option('fresh')) {
            if (!$this->option('force') && !$this->confirm('This will drop all existing data. Are you sure?')) {
                $this->error('Setup cancelled.');
                return Command::FAILURE;
            }

            $this->info('🔄 Running fresh migrations...');
            Artisan::call('migrate:fresh', ['--force' => true]);
            $this->info('✅ Database migrated successfully');
            $this->newLine();
        }

        try {
            // Run the unified BaseSeeder
            $this->info('🌱 Seeding system data...');
            $seeder = new BaseSeeder();
            $seeder->setCommand($this);
            $seeder->run();

            $this->newLine();
            $this->info('🎉 MediNext EMR System setup completed successfully!');
            $this->newLine();

            // Display system information
            $this->displaySystemInfo();

            // Display login information
            $this->displayLoginInfo();

            // Display next steps
            $this->displayNextSteps();

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Error during setup: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            return Command::FAILURE;
        }
    }

    private function displaySystemInfo(): void
    {
        $this->info('📊 SYSTEM INFORMATION');
        $this->info('====================');
        $this->newLine();

        $this->info('🏥 Clinics Created:');
        $clinics = DB::table('clinics')->get();
        foreach ($clinics as $clinic) {
            $this->line("   • {$clinic->name} (ID: {$clinic->id})");
        }
        $this->newLine();

        $this->info('👥 Users Created:');
        $users = DB::table('users')->get();
        foreach ($users as $user) {
            $this->line("   • {$user->name} ({$user->email})");
        }
        $this->newLine();

        $this->info('🔐 Roles Available:');
        $roles = DB::table('roles')->get();
        foreach ($roles as $role) {
            $this->line("   • {$role->name} - {$role->description}");
        }
        $this->newLine();

        $this->info('📋 Permissions: ' . DB::table('permissions')->count() . ' total');
        $this->newLine();
    }

    private function displayLoginInfo(): void
    {
        $this->info('🔐 LOGIN INFORMATION');
        $this->info('==================');
        $this->newLine();

        $this->info('👤 Nova Admin Account:');
        $this->line('   Email: nova@medinext.com');
        $this->line('   Password: nova123');
        $this->newLine();

        $this->info('👨‍⚕️ Demo Admin Account:');
        $this->line('   Email: demo@medinext.com');
        $this->line('   Password: demo123');
        $this->newLine();

        $this->info('👨‍⚕️ Sample Doctor Accounts:');
        $doctors = DB::table('users')
            ->join('user_clinic_roles', 'users.id', '=', 'user_clinic_roles.user_id')
            ->join('roles', 'user_clinic_roles.role_id', '=', 'roles.id')
            ->where('roles.name', 'doctor')
            ->select('users.email', 'users.name')
            ->get();

        foreach ($doctors as $doctor) {
            $this->line("   • {$doctor->name} ({$doctor->email}) - Password: password");
        }
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

        $this->line('3. 🔑 Log in with the Nova admin account for full system access');
        $this->newLine();

        $this->line('4. 🎯 Explore the MediNext EMR features:');
        $this->line('   • Patient Management & Registration');
        $this->line('   • Appointment Scheduling & Management');
        $this->line('   • Electronic Medical Records (EMR)');
        $this->line('   • Prescription Management');
        $this->line('   • Lab Results & Reports');
        $this->line('   • Billing & Insurance Management');
        $this->line('   • Queue Management');
        $this->line('   • Activity Logs & Audit Trail');
        $this->newLine();

        $this->line('5. 🧪 Test different user roles:');
        $this->line('   • Superadmin: Full system access');
        $this->line('   • Admin: Clinic management');
        $this->line('   • Doctor: Clinical workflow');
        $this->line('   • Receptionist: Front desk operations');
        $this->line('   • Patient: Self-service portal');
        $this->newLine();

        $this->line('6. 📊 View sample data:');
        $this->line('   • 2 Clinics with complete setup');
        $this->line('   • 8 Users with different roles');
        $this->line('   • 5 Patients with medical history');
        $this->line('   • 20+ Appointments (past and future)');
        $this->line('   • Prescriptions and lab results');
        $this->line('   • Bills and insurance records');
        $this->line('   • Activity logs and notifications');
        $this->newLine();

        $this->info('💡 TIP: Use the demo data to showcase MediNext EMR features to potential clients!');
        $this->newLine();
    }
}
