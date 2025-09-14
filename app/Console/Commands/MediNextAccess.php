<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Clinic;
use App\Models\Role;
use App\Models\UserClinicRole;
use Illuminate\Console\Command;

class MediNextAccess extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'medinext:access
                            {action : Action to perform (setup, assign, list, status)}
                            {--user-email= : User email to target}
                            {--clinic-id= : Clinic ID to target}
                            {--role=admin : Role to assign}
                            {--force : Force operation without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage MediNext EMR user access and clinic assignments';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->argument('action');
        $userEmail = $this->option('user-email');
        $clinicId = $this->option('clinic-id');
        $roleName = $this->option('role');
        $force = $this->option('force');

        switch ($action) {
            case 'setup':
                return $this->setupDefaultAccess($force);
            case 'assign':
                return $this->assignUserAccess($userEmail, $clinicId, $roleName, $force);
            case 'list':
                return $this->listAccess();
            case 'status':
                return $this->showStatus();
            default:
                $this->error("Invalid action: {$action}");
                $this->info('Valid actions: setup, assign, list, status');
                return Command::FAILURE;
        }
    }

    private function setupDefaultAccess(bool $force): int
    {
        if (!$force) {
            if (!$this->confirm('This will set up default clinic access for all users. Are you sure?')) {
                $this->info('Operation cancelled.');
                return Command::SUCCESS;
            }
        }

        $this->info('🔧 Setting up default MediNext EMR access...');

        try {
            $clinic = Clinic::first();
            $adminRole = Role::where('name', 'admin')->first();

            if (!$clinic) {
                $this->error('❌ No clinics found. Please run the setup command first.');
                return Command::FAILURE;
            }

            if (!$adminRole) {
                $this->error('❌ Admin role not found. Please run the permissions command first.');
                return Command::FAILURE;
            }

            $users = User::all();
            $assignedCount = 0;

            foreach ($users as $user) {
                if ($user->userClinicRoles()->count() === 0) {
                    UserClinicRole::firstOrCreate([
                        'user_id' => $user->id,
                        'clinic_id' => $clinic->id,
                        'role_id' => $adminRole->id,
                    ]);
                    $assignedCount++;
                    $this->line("✅ Assigned {$user->email} to {$clinic->name}");
                }
            }

            $this->info("✅ Successfully assigned {$assignedCount} users to the default clinic.");
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Failed to setup default access: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    private function assignUserAccess(?string $userEmail, ?string $clinicId, string $roleName, bool $force): int
    {
        if (!$userEmail || !$clinicId) {
            $this->error('❌ Both --user-email and --clinic-id are required for assignment.');
            $this->info('Usage: php artisan medinext:access assign --user-email=user@example.com --clinic-id=1 --role=admin');
            return Command::FAILURE;
        }

        if (!$force) {
            if (!$this->confirm("Assign user '{$userEmail}' to clinic ID '{$clinicId}' with role '{$roleName}'?")) {
                $this->info('Operation cancelled.');
                return Command::SUCCESS;
            }
        }

        $this->info("🔧 Assigning MediNext EMR access...");

        try {
            $user = User::where('email', $userEmail)->first();
            $clinic = Clinic::find($clinicId);
            $role = Role::where('name', $roleName)->first();

            if (!$user) {
                $this->error("❌ User with email '{$userEmail}' not found.");
                return Command::FAILURE;
            }

            if (!$clinic) {
                $this->error("❌ Clinic with ID '{$clinicId}' not found.");
                return Command::FAILURE;
            }

            if (!$role) {
                $this->error("❌ Role '{$roleName}' not found.");
                return Command::FAILURE;
            }

            // Create the user clinic role relationship
            $userClinicRole = UserClinicRole::firstOrCreate([
                'user_id' => $user->id,
                'clinic_id' => $clinic->id,
                'role_id' => $role->id,
            ]);

            $this->info("✅ Successfully assigned user '{$user->email}' to clinic '{$clinic->name}' with role '{$role->name}'.");
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Failed to assign user access: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    private function listAccess(): int
    {
        $this->info('📋 MediNext EMR Access List');
        $this->info('===========================');
        $this->newLine();

        try {
            // List all users
            $this->info('👥 Available Users:');
            $users = User::all();
            foreach ($users as $user) {
                $clinicCount = $user->userClinicRoles()->count();
                $this->line("   • {$user->email} ({$user->name}) - Clinics: {$clinicCount}");
            }
            $this->newLine();

            // List all clinics
            $this->info('🏥 Available Clinics:');
            $clinics = Clinic::all();
            foreach ($clinics as $clinic) {
                $this->line("   • ID: {$clinic->id} - {$clinic->name} (Slug: {$clinic->slug})");
            }
            $this->newLine();

            // List all roles
            $this->info('👤 Available Roles:');
            $roles = Role::all();
            foreach ($roles as $role) {
                $this->line("   • {$role->name} - {$role->description}");
            }
            $this->newLine();

            // List current assignments
            $this->info('🔗 Current Access Assignments:');
            $assignments = UserClinicRole::with(['user', 'clinic', 'role'])->get();
            if ($assignments->isEmpty()) {
                $this->line('   No access assignments found.');
            } else {
                foreach ($assignments as $assignment) {
                    $this->line("   • {$assignment->user->email} → {$assignment->clinic->name} ({$assignment->role->name})");
                }
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Failed to list access: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    private function showStatus(): int
    {
        $this->info('📊 MediNext EMR Access Status');
        $this->info('==============================');
        $this->newLine();

        try {
            $totalUsers = User::count();
            $usersWithAccess = User::whereHas('userClinicRoles')->count();
            $usersWithoutAccess = $totalUsers - $usersWithAccess;

            $totalClinics = Clinic::count();
            $totalRoles = Role::count();
            $totalAssignments = UserClinicRole::count();

            $this->info("👥 Users: {$totalUsers} total, {$usersWithAccess} with access, {$usersWithoutAccess} without access");
            $this->info("🏥 Clinics: {$totalClinics}");
            $this->info("👤 Roles: {$totalRoles}");
            $this->info("🔗 Access Assignments: {$totalAssignments}");
            $this->newLine();

            if ($usersWithoutAccess > 0) {
                $this->warn("⚠️  {$usersWithoutAccess} users without clinic access:");
                $usersWithoutAccess = User::whereDoesntHave('userClinicRoles')->get();
                foreach ($usersWithoutAccess as $user) {
                    $this->line("   • {$user->email} ({$user->name})");
                }
                $this->newLine();
            }

            // Show access by clinic
            $this->info('🏥 Access by Clinic:');
            $clinics = Clinic::with(['userClinicRoles.user', 'userClinicRoles.role'])->get();
            foreach ($clinics as $clinic) {
                $this->line("   • {$clinic->name}:");
                $assignments = $clinic->userClinicRoles;
                if ($assignments->isEmpty()) {
                    $this->line("     No users assigned");
                } else {
                    foreach ($assignments as $assignment) {
                        $this->line("     - {$assignment->user->email} ({$assignment->role->name})");
                    }
                }
            }
            $this->newLine();

            // Show access by role
            $this->info('👤 Access by Role:');
            $roles = Role::with(['userClinicRoles.user', 'userClinicRoles.clinic'])->get();
            foreach ($roles as $role) {
                $this->line("   • {$role->name}:");
                $assignments = $role->userClinicRoles;
                if ($assignments->isEmpty()) {
                    $this->line("     No users assigned");
                } else {
                    foreach ($assignments as $assignment) {
                        $this->line("     - {$assignment->user->email} → {$assignment->clinic->name}");
                    }
                }
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Failed to get access status: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
