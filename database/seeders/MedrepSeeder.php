<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Medrep;
use App\Models\User;
use App\Models\Role;
use App\Models\UserClinicRole;
use App\Models\Clinic;

class MedrepSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $medrepRole = Role::where('name', 'medrep')->first();

        if (!$medrepRole) {
            $this->command->error('Medrep role not found. Please run UserRoleSeeder first.');
            return;
        }

        $companies = [
            'Pfizer Philippines',
            'GlaxoSmithKline Philippines',
            'Merck Sharp & Dohme Philippines',
            'Novartis Healthcare Philippines',
            'AstraZeneca Philippines',
            'Sanofi-Aventis Philippines',
            'Johnson & Johnson Philippines',
            'Roche Philippines',
            'Bayer Philippines',
            'Eli Lilly Philippines',
            'Abbott Laboratories Philippines',
            'Boehringer Ingelheim Philippines',
            'Takeda Philippines',
            'Daiichi Sankyo Philippines',
            'Otsuka Philippines'
        ];

        $medreps = [
            [
                'name' => 'Maria Santos',
                'email' => 'maria.santos@pfizer.ph',
                'company' => 'Pfizer Philippines',
                'notes' => 'Specializes in cardiovascular and anti-infective products'
            ],
            [
                'name' => 'Juan Dela Cruz',
                'email' => 'juan.delacruz@gsk.ph',
                'company' => 'GlaxoSmithKline Philippines',
                'notes' => 'Focuses on respiratory and vaccine products'
            ],
            [
                'name' => 'Ana Reyes',
                'email' => 'ana.reyes@msd.ph',
                'company' => 'Merck Sharp & Dohme Philippines',
                'notes' => 'Specializes in diabetes and oncology products'
            ],
            [
                'name' => 'Carlos Mendoza',
                'email' => 'carlos.mendoza@novartis.ph',
                'company' => 'Novartis Healthcare Philippines',
                'notes' => 'Covers cardiovascular and oncology portfolio'
            ],
            [
                'name' => 'Sofia Garcia',
                'email' => 'sofia.garcia@astrazeneca.ph',
                'company' => 'AstraZeneca Philippines',
                'notes' => 'Specializes in respiratory and cardiovascular products'
            ],
            [
                'name' => 'Roberto Aquino',
                'email' => 'roberto.aquino@sanofi.ph',
                'company' => 'Sanofi-Aventis Philippines',
                'notes' => 'Focuses on diabetes and rare disease products'
            ],
            [
                'name' => 'Elena Torres',
                'email' => 'elena.torres@jnj.ph',
                'company' => 'Johnson & Johnson Philippines',
                'notes' => 'Covers immunology and infectious disease products'
            ],
            [
                'name' => 'Miguel Lopez',
                'email' => 'miguel.lopez@roche.ph',
                'company' => 'Roche Philippines',
                'notes' => 'Specializes in oncology and diagnostics'
            ],
            [
                'name' => 'Carmen Rodriguez',
                'email' => 'carmen.rodriguez@bayer.ph',
                'company' => 'Bayer Philippines',
                'notes' => 'Focuses on cardiovascular and women\'s health'
            ],
            [
                'name' => 'Antonio Silva',
                'email' => 'antonio.silva@lilly.ph',
                'company' => 'Eli Lilly Philippines',
                'notes' => 'Specializes in diabetes and oncology products'
            ]
        ];

        foreach ($medreps as $medrepData) {
            // Create user for medrep
            $user = User::firstOrCreate(
                ['email' => $medrepData['email']],
                [
                    'name' => $medrepData['name'],
                    'email' => $medrepData['email'],
                    'password' => bcrypt('password'),
                    'email_verified_at' => now(),
                ]
            );

            // Assign medrep role through UserClinicRole (assign to first clinic)
            $clinic = Clinic::first();
            if ($clinic) {
                UserClinicRole::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'clinic_id' => $clinic->id,
                        'role_id' => $medrepRole->id
                    ],
                    [
                        'user_id' => $user->id,
                        'clinic_id' => $clinic->id,
                        'role_id' => $medrepRole->id
                    ]
                );
            }

            // Create medrep record
            Medrep::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'user_id' => $user->id,
                    'company' => $medrepData['company'],
                    'notes' => $medrepData['notes']
                ]
            );
        }

        // Generate additional random medreps
        for ($i = 0; $i < 15; $i++) {
            $company = $companies[array_rand($companies)];
            $firstName = $this->getRandomFirstName();
            $lastName = $this->getRandomLastName();
            $name = $firstName . ' ' . $lastName;
            $email = strtolower(str_replace(' ', '.', $name)) . '@' . strtolower(str_replace(' ', '', $company)) . '.ph';

            // Create user for medrep
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'email' => $email,
                    'password' => bcrypt('password'),
                    'email_verified_at' => now(),
                ]
            );

            // Assign medrep role through UserClinicRole
            $clinic = Clinic::first();
            if ($clinic) {
                UserClinicRole::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'clinic_id' => $clinic->id,
                        'role_id' => $medrepRole->id
                    ],
                    [
                        'user_id' => $user->id,
                        'clinic_id' => $clinic->id,
                        'role_id' => $medrepRole->id
                    ]
                );
            }

            // Create medrep record
            Medrep::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'user_id' => $user->id,
                    'company' => $company,
                    'notes' => 'Medical representative for ' . $company
                ]
            );
        }

        $this->command->info('Medical representatives seeded successfully!');
    }

    private function getRandomFirstName(): string
    {
        $firstNames = [
            'Jose', 'Maria', 'Antonio', 'Carmen', 'Manuel', 'Isabel', 'Francisco', 'Ana',
            'Pedro', 'Teresa', 'Miguel', 'Rosa', 'Carlos', 'Elena', 'Luis', 'Dolores',
            'Fernando', 'Concepcion', 'Alberto', 'Patricia', 'Roberto', 'Gloria', 'Ricardo',
            'Beatriz', 'Eduardo', 'Adriana', 'Alfredo', 'Monica', 'Hector', 'Veronica'
        ];

        return $firstNames[array_rand($firstNames)];
    }

    private function getRandomLastName(): string
    {
        $lastNames = [
            'Santos', 'Dela Cruz', 'Reyes', 'Mendoza', 'Garcia', 'Aquino', 'Torres',
            'Lopez', 'Rodriguez', 'Silva', 'Martinez', 'Fernandez', 'Gonzalez',
            'Perez', 'Sanchez', 'Ramirez', 'Cruz', 'Morales', 'Rivera', 'Flores',
            'Gomez', 'Diaz', 'Ramos', 'Jimenez', 'Moreno', 'Herrera', 'Vargas'
        ];

        return $lastNames[array_rand($lastNames)];
    }
}
