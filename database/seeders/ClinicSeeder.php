<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Clinic;
use App\Models\Role;
use App\Models\User;
use App\Models\UserClinicRole;
use App\Models\Doctor;

class ClinicSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles
        $roles = [
            'superadmin',
            'admin',
            'doctor',
            'receptionist',
            'patient',
            'medrep'
        ];

        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }

        // Create sample clinics
        $clinics = [
            [
                'name' => 'Metro Medical Center',
                'slug' => 'metro-medical-center',
                'timezone' => 'Asia/Manila',
                'address' => [
                    'street' => '123 Medical Plaza',
                    'city' => 'Manila',
                    'state' => 'Metro Manila',
                    'postal_code' => '1000',
                    'country' => 'Philippines'
                ],
                'settings' => [
                    'working_hours' => [
                        'monday' => ['08:00', '17:00'],
                        'tuesday' => ['08:00', '17:00'],
                        'wednesday' => ['08:00', '17:00'],
                        'thursday' => ['08:00', '17:00'],
                        'friday' => ['08:00', '17:00'],
                        'saturday' => ['08:00', '12:00'],
                        'sunday' => ['closed']
                    ]
                ]
            ],
            [
                'name' => 'Quezon City General Hospital',
                'slug' => 'quezon-city-general-hospital',
                'timezone' => 'Asia/Manila',
                'address' => [
                    'street' => '456 Health Avenue',
                    'city' => 'Quezon City',
                    'state' => 'Metro Manila',
                    'postal_code' => '1100',
                    'country' => 'Philippines'
                ],
                'settings' => [
                    'working_hours' => [
                        'monday' => ['07:00', '18:00'],
                        'tuesday' => ['07:00', '18:00'],
                        'wednesday' => ['07:00', '18:00'],
                        'thursday' => ['07:00', '18:00'],
                        'friday' => ['07:00', '18:00'],
                        'saturday' => ['07:00', '14:00'],
                        'sunday' => ['08:00', '12:00']
                    ]
                ]
            ],
            [
                'name' => 'Makati Medical Clinic',
                'slug' => 'makati-medical-clinic',
                'timezone' => 'Asia/Manila',
                'address' => [
                    'street' => '789 Wellness Street',
                    'city' => 'Makati',
                    'state' => 'Metro Manila',
                    'postal_code' => '1200',
                    'country' => 'Philippines'
                ],
                'settings' => [
                    'working_hours' => [
                        'monday' => ['09:00', '16:00'],
                        'tuesday' => ['09:00', '16:00'],
                        'wednesday' => ['09:00', '16:00'],
                        'thursday' => ['09:00', '16:00'],
                        'friday' => ['09:00', '16:00'],
                        'saturday' => ['09:00', '13:00'],
                        'sunday' => ['closed']
                    ]
                ]
            ]
        ];

        foreach ($clinics as $clinicData) {
            Clinic::firstOrCreate(['slug' => $clinicData['slug']], $clinicData);
        }

        // Create a superadmin user if it doesn't exist
        $superadmin = User::firstOrCreate(
            ['email' => 'admin@myclinicsoft.com'],
            [
                'name' => 'System Administrator',
                'password' => bcrypt('password'),
            ]
        );

        // Assign superadmin role to all clinics
        $superadminRole = Role::where('name', 'superadmin')->first();
        $clinics = Clinic::all();

        foreach ($clinics as $clinic) {
            UserClinicRole::firstOrCreate([
                'user_id' => $superadmin->id,
                'clinic_id' => $clinic->id,
                'role_id' => $superadminRole->id,
            ]);
        }

        // Create sample doctors for each clinic
        $specialties = [
            'Cardiology',
            'Dermatology',
            'Endocrinology',
            'Gastroenterology',
            'General Practice',
            'Internal Medicine',
            'Neurology',
            'Oncology',
            'Orthopedics',
            'Pediatrics',
            'Psychiatry',
            'Radiology',
            'Surgery',
            'Urology'
        ];

        foreach ($clinics as $clinic) {
            // Create 3-5 doctors per clinic
            $numDoctors = rand(3, 5);
            
            for ($i = 1; $i <= $numDoctors; $i++) {
                $user = User::create([
                    'name' => fake()->name(),
                    'email' => fake()->unique()->safeEmail(),
                    'password' => bcrypt('password'),
                ]);

                // Assign doctor role
                $doctorRole = Role::where('name', 'doctor')->first();
                UserClinicRole::create([
                    'user_id' => $user->id,
                    'clinic_id' => $clinic->id,
                    'role_id' => $doctorRole->id,
                ]);

                // Create doctor record
                Doctor::create([
                    'user_id' => $user->id,
                    'clinic_id' => $clinic->id,
                    'specialty' => $specialties[array_rand($specialties)],
                    'license_no' => 'MD-' . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT),
                ]);
            }
        }

        $this->command->info('Clinics, roles, and sample doctors seeded successfully!');
    }
}
