<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use App\Models\Clinic;
use App\Models\UserClinicRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class NovaUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing clinic or create a default one if none exists
        $clinic = Clinic::first();

        if (!$clinic) {
            $clinic = Clinic::create([
                'name' => 'Main Clinic',
                'slug' => 'main-clinic',
                'timezone' => 'Asia/Manila',
                'address' => [
                    'street' => '123 Main Street',
                    'city' => 'Manila',
                    'province' => 'Metro Manila',
                    'postal_code' => '1000',
                    'country' => 'Philippines'
                ],
                'settings' => [
                    'appointment_duration' => 30,
                    'working_hours' => [
                        'monday' => ['08:00', '17:00'],
                        'tuesday' => ['08:00', '17:00'],
                        'wednesday' => ['08:00', '17:00'],
                        'thursday' => ['08:00', '17:00'],
                        'friday' => ['08:00', '17:00'],
                        'saturday' => ['12:00', '12:00'],
                        'sunday' => []
                    ]
                ]
            ]);
        }

        // Ensure superadmin role exists
        $superadminRole = Role::firstOrCreate(
            ['name' => 'superadmin'],
            [
                'description' => 'Full system access and management. Can manage all clinics, users, and system settings.',
                'is_system_role' => true,
                'permissions_config' => [
                    'clinics.manage',
                    'doctors.manage',
                    'patients.manage',
                    'appointments.manage',
                    'prescriptions.manage',
                    'users.manage',
                    'roles.manage',
                    'billing.manage',
                    'reports.view',
                    'reports.export',
                    'settings.manage'
                ]
            ]
        );

        // Create Nova admin user
        $novaUser = User::firstOrCreate(
            ['email' => 'nova@medinext.com'],
            [
                'name' => 'Nova Administrator',
                'password' => Hash::make('nova123'),
                'phone' => '+63 912 345 6789',
                'is_active' => true,
            ]
        );

        // Assign superadmin role to Nova user in the clinic
        UserClinicRole::firstOrCreate([
            'user_id' => $novaUser->id,
            'clinic_id' => $clinic->id,
            'role_id' => $superadminRole->id,
        ]);

        // If there are multiple clinics, assign the Nova user to all of them
        $allClinics = Clinic::all();
        foreach ($allClinics as $clinicItem) {
            UserClinicRole::firstOrCreate([
                'user_id' => $novaUser->id,
                'clinic_id' => $clinicItem->id,
                'role_id' => $superadminRole->id,
            ]);
        }

        $this->command->info('Nova admin user created successfully!');
        $this->command->info('Email: nova@medinext.com');
        $this->command->info('Password: nova123');
        $this->command->info('Role: superadmin');
        $this->command->info('Assigned to ' . $allClinics->count() . ' clinic(s)');
    }
}
