<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Doctor;
use App\Models\User;
use App\Models\Clinic;
use App\Models\Role;
use App\Models\UserClinicRole;

class DoctorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $doctorRole = Role::where('name', 'doctor')->first();
        
        if (!$doctorRole) {
            $this->command->error('Doctor role not found. Please run UserRoleSeeder first.');
            return;
        }

        $clinics = Clinic::all();
        
        if ($clinics->isEmpty()) {
            $this->command->error('No clinics found. Please run ClinicSeeder first.');
            return;
        }

        $specialties = [
            'Cardiology',
            'Dermatology',
            'Endocrinology',
            'Gastroenterology',
            'General Practice',
            'Internal Medicine',
            'Neurology',
            'Obstetrics & Gynecology',
            'Oncology',
            'Ophthalmology',
            'Orthopedics',
            'Pediatrics',
            'Psychiatry',
            'Pulmonology',
            'Radiology',
            'Surgery',
            'Urology'
        ];

        $doctors = [
            [
                'name' => 'Dr. Maria Santos',
                'email' => 'maria.santos@example.com',
                'specialty' => 'Cardiology',
                'license_no' => 'MD-2024-001',
            ],
            [
                'name' => 'Dr. Juan Dela Cruz',
                'email' => 'juan.delacruz@example.com',
                'specialty' => 'General Practice',
                'license_no' => 'MD-2024-002',
            ],
            [
                'name' => 'Dr. Ana Reyes',
                'email' => 'ana.reyes@example.com',
                'specialty' => 'Pediatrics',
                'license_no' => 'MD-2024-003',
            ],
            [
                'name' => 'Dr. Carlos Mendoza',
                'email' => 'carlos.mendoza@example.com',
                'specialty' => 'Internal Medicine',
                'license_no' => 'MD-2024-004',
            ],
            [
                'name' => 'Dr. Sofia Garcia',
                'email' => 'sofia.garcia@example.com',
                'specialty' => 'Dermatology',
                'license_no' => 'MD-2024-005',
            ],
            [
                'name' => 'Dr. Roberto Aquino',
                'email' => 'roberto.aquino@example.com',
                'specialty' => 'Orthopedics',
                'license_no' => 'MD-2024-006',
            ],
            [
                'name' => 'Dr. Elena Torres',
                'email' => 'elena.torres@example.com',
                'specialty' => 'Obstetrics & Gynecology',
                'license_no' => 'MD-2024-007',
            ],
            [
                'name' => 'Dr. Miguel Lopez',
                'email' => 'miguel.lopez@example.com',
                'specialty' => 'Neurology',
                'license_no' => 'MD-2024-008',
            ]
        ];

        foreach ($doctors as $index => $doctorData) {
            // Create user for doctor
            $user = User::firstOrCreate(
                ['email' => $doctorData['email']],
                [
                    'name' => $doctorData['name'],
                    'email' => $doctorData['email'],
                    'password' => bcrypt('password'),
                    'email_verified_at' => now(),
                ]
            );

            // Assign to clinic (distribute across available clinics)
            $clinic = $clinics[$index % $clinics->count()];

            // Assign doctor role through UserClinicRole
            UserClinicRole::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'clinic_id' => $clinic->id,
                    'role_id' => $doctorRole->id
                ],
                [
                    'user_id' => $user->id,
                    'clinic_id' => $clinic->id,
                    'role_id' => $doctorRole->id
                ]
            );

            // Create doctor record
            Doctor::firstOrCreate(
                ['user_id' => $user->id, 'clinic_id' => $clinic->id],
                [
                    'user_id' => $user->id,
                    'clinic_id' => $clinic->id,
                    'specialty' => $doctorData['specialty'],
                    'license_no' => $doctorData['license_no'],
                ]
            );
        }

        $this->command->info('Doctors seeded successfully!');
    }
}
