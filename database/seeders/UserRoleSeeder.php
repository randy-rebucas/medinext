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

        // Create roles if they don't exist with proper descriptions
        $roles = [
            'superadmin' => Role::firstOrCreate(
                ['name' => 'superadmin'],
                [
                    'description' => 'Platform administrator with full system access. Manages tenants, clinics, plans, and global settings.',
                    'is_system_role' => true,
                ]
            ),
            'admin' => Role::firstOrCreate(
                ['name' => 'admin'],
                [
                    'description' => 'Clinic owner/manager with full access within clinic. Can manage staff, billing, and settings.',
                    'is_system_role' => false,
                ]
            ),
            'doctor' => Role::firstOrCreate(
                ['name' => 'doctor'],
                [
                    'description' => 'Medical professional who can view own schedule, manage assigned patients\' EMR, issue prescriptions, and view med samples.',
                    'is_system_role' => false,
                ]
            ),
            'receptionist' => Role::firstOrCreate(
                ['name' => 'receptionist'],
                [
                    'description' => 'Front desk staff who can manage calendar, patients, visits, and billing support. No access to clinical notes by default.',
                    'is_system_role' => false,
                ]
            ),
            'patient' => Role::firstOrCreate(
                ['name' => 'patient'],
                [
                    'description' => 'Self-service portal access. Can book, reschedule, cancel appointments, view summary, and download prescriptions.',
                    'is_system_role' => false,
                ]
            ),
            'medrep' => Role::firstOrCreate(
                ['name' => 'medrep'],
                [
                    'description' => 'Medical representative who can schedule visits with doctors and upload product sheets. No access to patient data.',
                    'is_system_role' => false,
                ]
            ),
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
