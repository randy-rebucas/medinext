<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // Core system seeders (no dependencies)
            ClinicSeeder::class,
            PermissionSeeder::class,
            UserRoleSeeder::class,
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
