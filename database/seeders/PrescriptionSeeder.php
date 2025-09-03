<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Prescription;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Clinic;
use App\Models\Encounter;
use Carbon\Carbon;

class PrescriptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $patients = Patient::all();
        $doctors = Doctor::all();
        $clinics = Clinic::all();
        $encounters = Encounter::all();
        
        if ($patients->isEmpty() || $doctors->isEmpty() || $clinics->isEmpty()) {
            $this->command->error('Required data not found. Please run PatientSeeder and DoctorSeeder first.');
            return;
        }

        $prescriptionStatuses = ['active', 'completed', 'cancelled', 'expired'];
        
        // Common prescription scenarios
        $prescriptions = [
            [
                'patient' => $patients->random(),
                'doctor' => $doctors->random(),
                'encounter' => $encounters->isNotEmpty() ? $encounters->random() : null,
                'status' => 'active',
                'issued_at' => Carbon::now()->subDays(rand(1, 30)),
            ],
            [
                'patient' => $patients->random(),
                'doctor' => $doctors->random(),
                'encounter' => $encounters->isNotEmpty() ? $encounters->random() : null,
                'status' => 'completed',
                'issued_at' => Carbon::now()->subDays(rand(31, 90)),
            ],
            [
                'patient' => $patients->random(),
                'doctor' => $doctors->random(),
                'encounter' => $encounters->isNotEmpty() ? $encounters->random() : null,
                'status' => 'active',
                'issued_at' => Carbon::now()->subDays(rand(1, 7)),
            ],
            [
                'patient' => $patients->random(),
                'doctor' => $doctors->random(),
                'encounter' => $encounters->isNotEmpty() ? $encounters->random() : null,
                'status' => 'completed',
                'issued_at' => Carbon::now()->subDays(rand(15, 45)),
            ],
            [
                'patient' => $patients->random(),
                'doctor' => $doctors->random(),
                'encounter' => $encounters->isNotEmpty() ? $encounters->random() : null,
                'status' => 'active',
                'issued_at' => Carbon::now()->subDays(rand(1, 14)),
            ]
        ];

        foreach ($prescriptions as $prescriptionData) {
            $clinic = $prescriptionData['doctor']->clinic;
            
            // Generate QR hash for prescription
            $qrHash = 'RX-' . strtoupper(uniqid()) . '-' . $prescriptionData['patient']->id;
            
            Prescription::firstOrCreate(
                [
                    'clinic_id' => $clinic->id,
                    'patient_id' => $prescriptionData['patient']->id,
                    'doctor_id' => $prescriptionData['doctor']->id,
                    'encounter_id' => $prescriptionData['encounter']?->id,
                    'issued_at' => $prescriptionData['issued_at']
                ],
                [
                    'clinic_id' => $clinic->id,
                    'patient_id' => $prescriptionData['patient']->id,
                    'doctor_id' => $prescriptionData['doctor']->id,
                    'encounter_id' => $prescriptionData['encounter']?->id,
                    'issued_at' => $prescriptionData['issued_at'],
                    'status' => $prescriptionData['status'],
                    'pdf_url' => null, // Will be generated when prescription is processed
                    'qr_hash' => $qrHash
                ]
            );
        }

        // Generate additional random prescriptions
        for ($i = 0; $i < 20; $i++) {
            $patient = $patients->random();
            $doctor = $doctors->random();
            $clinic = $doctor->clinic;
            $encounter = $encounters->isNotEmpty() ? $encounters->random() : null;
            $status = $prescriptionStatuses[array_rand($prescriptionStatuses)];
            $issuedAt = Carbon::now()->subDays(rand(1, 180));
            
            // Generate QR hash
            $qrHash = 'RX-' . strtoupper(uniqid()) . '-' . $patient->id;
            
            Prescription::firstOrCreate(
                [
                    'clinic_id' => $clinic->id,
                    'patient_id' => $patient->id,
                    'doctor_id' => $doctor->id,
                    'encounter_id' => $encounter?->id,
                    'issued_at' => $issuedAt
                ],
                [
                    'clinic_id' => $clinic->id,
                    'patient_id' => $patient->id,
                    'doctor_id' => $doctor->id,
                    'encounter_id' => $encounter?->id,
                    'issued_at' => $issuedAt,
                    'status' => $status,
                    'pdf_url' => null,
                    'qr_hash' => $qrHash
                ]
            );
        }

        $this->command->info('Prescriptions seeded successfully!');
    }
}
