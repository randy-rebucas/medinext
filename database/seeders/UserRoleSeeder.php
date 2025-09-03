<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use App\Models\Clinic;
use App\Models\UserClinicRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserRoleSeeder extends Seeder
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
                        'saturday' => ['08:00', '12:00'],
                        'sunday' => []
                    ]
                ]
            ]);
        }

        // Create roles if they don't exist
        $roles = [
            'superadmin' => Role::firstOrCreate(['name' => 'superadmin']),
            'admin' => Role::firstOrCreate(['name' => 'admin']),
            'doctor' => Role::firstOrCreate(['name' => 'doctor']),
            'receptionist' => Role::firstOrCreate(['name' => 'receptionist']),
            'patient' => Role::firstOrCreate(['name' => 'patient']),
            'medrep' => Role::firstOrCreate(['name' => 'medrep']),
        ];

        // Create users for each role
        $users = [
            'superadmin' => [
                'name' => 'Super Admin',
                'email' => 'superadmin@clinicflow.com',
                'password' => Hash::make('password123'),
            ],
            'admin' => [
                'name' => 'Clinic Admin',
                'email' => 'admin@clinicflow.com',
                'password' => Hash::make('password123'),
            ],
            'doctor' => [
                'name' => 'Dr. John Smith',
                'email' => 'doctor@clinicflow.com',
                'password' => Hash::make('password123'),
            ],
            'receptionist' => [
                'name' => 'Jane Receptionist',
                'email' => 'receptionist@clinicflow.com',
                'password' => Hash::make('password123'),
            ],
            'patient' => [
                'name' => 'Patient User',
                'email' => 'patient@clinicflow.com',
                'password' => Hash::make('password123'),
            ],
            'medrep' => [
                'name' => 'Medical Representative',
                'email' => 'medrep@clinicflow.com',
                'password' => Hash::make('password123'),
            ],
        ];

        // Create users and assign roles
        foreach ($users as $roleName => $userData) {
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                $userData
            );

            // Assign role to user in the clinic
            UserClinicRole::firstOrCreate([
                'user_id' => $user->id,
                'clinic_id' => $clinic->id,
                'role_id' => $roles[$roleName]->id,
            ]);
        }

        $this->command->info('Created users for all roles successfully!');
        $this->command->info('Default clinic: ' . $clinic->name);
        $this->command->info('All users have password: password123');
    }
}
