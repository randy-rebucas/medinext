<?php

namespace Database\Seeders;

use App\Models\Patient;
use App\Models\Clinic;
use Illuminate\Database\Seeder;

class PatientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clinics = Clinic::all();

        if ($clinics->isEmpty()) {
            $this->command->info('No clinics found. Skipping patient seeding.');
            return;
        }

        $samplePatients = [
            [
                'first_name' => 'Juan',
                'last_name' => 'Dela Cruz',
                'dob' => '1985-03-15',
                'sex' => 'M',
                'contact' => [
                    'phone' => '+63 912 345 6789',
                    'email' => 'juan.delacruz@email.com',
                    'address' => '123 Rizal Street, Manila'
                ],
                'allergies' => ['Penicillin', 'Sulfa drugs'],
                'consents' => ['treatment', 'privacy', 'data_sharing']
            ],
            [
                'first_name' => 'Maria',
                'last_name' => 'Santos',
                'dob' => '1990-07-22',
                'sex' => 'F',
                'contact' => [
                    'phone' => '+63 923 456 7890',
                    'email' => 'maria.santos@email.com',
                    'address' => '456 Bonifacio Avenue, Quezon City'
                ],
                'allergies' => ['None'],
                'consents' => ['treatment', 'privacy', 'data_sharing']
            ],
            [
                'first_name' => 'Pedro',
                'last_name' => 'Garcia',
                'dob' => '1978-11-08',
                'sex' => 'M',
                'contact' => [
                    'phone' => '+63 934 567 8901',
                    'email' => 'pedro.garcia@email.com',
                    'address' => '789 Mabini Street, Makati'
                ],
                'allergies' => ['Latex', 'Shellfish'],
                'consents' => ['treatment', 'privacy']
            ],
            [
                'first_name' => 'Ana',
                'last_name' => 'Martinez',
                'dob' => '1992-04-30',
                'sex' => 'F',
                'contact' => [
                    'phone' => '+63 945 678 9012',
                    'email' => 'ana.martinez@email.com',
                    'address' => '321 Aguinaldo Road, Taguig'
                ],
                'allergies' => ['Aspirin'],
                'consents' => ['treatment', 'privacy', 'data_sharing', 'research']
            ],
            [
                'first_name' => 'Luis',
                'last_name' => 'Reyes',
                'dob' => '1982-09-14',
                'sex' => 'M',
                'contact' => [
                    'phone' => '+63 956 789 0123',
                    'email' => 'luis.reyes@email.com',
                    'address' => '654 Roxas Boulevard, Pasay'
                ],
                'allergies' => ['None'],
                'consents' => ['treatment', 'privacy']
            ]
        ];

        foreach ($samplePatients as $index => $patientData) {
            $clinic = $clinics->get($index % $clinics->count());

            Patient::create([
                'clinic_id' => $clinic->id,
                'code' => 'P' . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                'first_name' => $patientData['first_name'],
                'last_name' => $patientData['last_name'],
                'dob' => $patientData['dob'],
                'sex' => $patientData['sex'],
                'contact' => $patientData['contact'],
                'allergies' => $patientData['allergies'],
                'consents' => $patientData['consents'],
            ]);
        }

        $this->command->info('Sample patients created successfully!');
    }
}
