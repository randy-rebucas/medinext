<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * For production deployment, use: php artisan db:seed --class=InitialSeeder
     * For development with demo data, use: php artisan db:seed
     */
    public function run(): void
    {
        // Check if this is a fresh installation (no users exist)
        $hasUsers = \App\Models\User::count() > 0;

        if (!$hasUsers) {
            $this->command->info('Fresh installation detected. Running initial seeder...');
            $this->call(InitialSeeder::class);
            return;
        }

        $this->command->info('Existing data detected. Running full development seeders...');

        $this->call([
            // Core system seeders (no dependencies)
            ClinicSeeder::class,
            PermissionSeeder::class,
            UserRoleSeeder::class,
            NovaUserSeeder::class,
            SettingsSeeder::class,

            // User and role seeders
            DoctorSeeder::class,
            MedrepSeeder::class,

            // Patient and encounter seeders
            PatientSeeder::class,
            EncounterSeeder::class,

            // Infrastructure seeders
            RoomSeeder::class,

            // Business logic seeders
            AppointmentSeeder::class,
            PrescriptionSeeder::class,
            PrescriptionItemSeeder::class,
            LabResultSeeder::class,
            FileAssetSeeder::class,
            MedrepVisitSeeder::class,

            // EMR data seeding (comprehensive data)
            EMRSeeder::class,

            // Activity logging (depends on all other entities)
            ActivityLogSeeder::class,
        ]);
    }
}
