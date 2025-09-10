<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Clinic;
use App\Models\Role;
use App\Models\UserClinicRole;
use Illuminate\Console\Command;

class SetupUserClinicAccess extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'setup:user-clinic-access {--user-email=} {--clinic-id=} {--role=admin}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup user clinic access by creating user-clinic-role relationships';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userEmail = $this->option('user-email');
        $clinicId = $this->option('clinic-id');
        $roleName = $this->option('role');

        // If no user email provided, show all users
        if (!$userEmail) {
            $users = User::all();
            $this->info('Available users:');
            foreach ($users as $user) {
                $clinicCount = $user->clinics()->count();
                $this->line("- {$user->email} ({$user->name}) - Clinics: {$clinicCount}");
            }
            $this->newLine();
        }

        // If no clinic ID provided, show all clinics
        if (!$clinicId) {
            $clinics = Clinic::all();
            $this->info('Available clinics:');
            foreach ($clinics as $clinic) {
                $this->line("- ID: {$clinic->id} - {$clinic->name}");
            }
            $this->newLine();
        }

        // If both user email and clinic ID are provided, create the relationship
        if ($userEmail && $clinicId) {
            $user = User::where('email', $userEmail)->first();
            $clinic = Clinic::find($clinicId);
            $role = Role::where('name', $roleName)->first();

            if (!$user) {
                $this->error("User with email '{$userEmail}' not found.");
                return 1;
            }

            if (!$clinic) {
                $this->error("Clinic with ID '{$clinicId}' not found.");
                return 1;
            }

            if (!$role) {
                $this->error("Role '{$roleName}' not found.");
                return 1;
            }

            // Create the user clinic role relationship
            $userClinicRole = UserClinicRole::firstOrCreate([
                'user_id' => $user->id,
                'clinic_id' => $clinic->id,
                'role_id' => $role->id,
            ]);

            $this->info("Successfully assigned user '{$user->email}' to clinic '{$clinic->name}' with role '{$role->name}'.");
            return 0;
        }

        // If no specific user/clinic provided, set up default access for all users
        if (!$userEmail && !$clinicId) {
            $this->info('Setting up default clinic access for all users...');

            $clinic = Clinic::first();
            $adminRole = Role::where('name', 'admin')->first();

            if (!$clinic) {
                $this->error('No clinics found. Please run the clinic seeder first.');
                return 1;
            }

            if (!$adminRole) {
                $this->error('Admin role not found. Please run the role seeder first.');
                return 1;
            }

            $users = User::all();
            $assignedCount = 0;

            foreach ($users as $user) {
                if ($user->clinics()->count() === 0) {
                    UserClinicRole::firstOrCreate([
                        'user_id' => $user->id,
                        'clinic_id' => $clinic->id,
                        'role_id' => $adminRole->id,
                    ]);
                    $assignedCount++;
                    $this->line("Assigned {$user->email} to {$clinic->name}");
                }
            }

            $this->info("Successfully assigned {$assignedCount} users to the default clinic.");
            return 0;
        }

        $this->info('Usage examples:');
        $this->line('php artisan setup:user-clinic-access --user-email=admin@example.com --clinic-id=1 --role=admin');
        $this->line('php artisan setup:user-clinic-access (to set up default access for all users)');

        return 0;
    }
}
