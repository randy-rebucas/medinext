<?php

namespace Database\Seeders;

use App\Models\Encounter;
use App\Models\Patient;
use App\Models\Doctor;
use Illuminate\Database\Seeder;

class EncounterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $patients = Patient::all();
        $doctors = Doctor::all();

        if ($patients->isEmpty() || $doctors->isEmpty()) {
            $this->command->info('No patients or doctors found. Skipping encounter seeding.');
            return;
        }

        $encounterTypes = ['consultation', 'follow_up', 'emergency', 'routine', 'procedure'];
        $encounterStatuses = ['scheduled', 'in_progress', 'completed', 'cancelled'];

        foreach ($patients as $patient) {
            $clinicId = $patient->clinic_id;
            $clinicDoctors = $doctors->where('clinic_id', $clinicId);

            if ($clinicDoctors->isEmpty()) {
                continue;
            }

            // Create 1-3 encounters per patient
            $numEncounters = rand(1, 3);

            for ($i = 0; $i < $numEncounters; $i++) {
                $doctor = $clinicDoctors->random();
                $type = $encounterTypes[array_rand($encounterTypes)];
                $status = $encounterStatuses[array_rand($encounterStatuses)];

                Encounter::create([
                    'clinic_id' => $clinicId,
                    'patient_id' => $patient->id,
                    'doctor_id' => $doctor->id,
                    'date' => now()->subDays(rand(1, 90)),
                    'type' => $type,
                    'status' => $status,
                    'notes_soap' => $this->generateSOAPNotes($type),
                    'vitals' => $this->generateVitals(),
                    'diagnosis_codes' => json_encode($this->generateDiagnosisCodes($type)),
                ]);
            }
        }

        $this->command->info('Sample encounters created successfully!');
    }

    private function generateSOAPNotes(string $type): string
    {
        $notes = [
            'consultation' => 'Patient presents for routine consultation. No acute complaints. General health appears good.',
            'follow_up' => 'Follow-up visit. Patient reports improvement in previous symptoms. Continue current treatment plan.',
            'emergency' => 'Emergency presentation. Patient requires immediate attention. Vital signs stable.',
            'routine' => 'Routine check-up. Patient appears healthy. No significant findings.',
            'procedure' => 'Procedure completed successfully. Patient tolerated well. Post-procedure instructions given.'
        ];

        return $notes[$type] ?? 'Standard medical notes recorded.';
    }

    private function generateVitals(): array
    {
        return [
            'blood_pressure' => rand(110, 140) . '/' . rand(70, 90),
            'heart_rate' => rand(60, 100),
            'temperature' => rand(36, 37) . '.' . rand(0, 9),
            'respiratory_rate' => rand(12, 20),
            'oxygen_saturation' => rand(95, 100)
        ];
    }

    private function generateDiagnosisCodes(string $type): array
    {
        $codes = [
            'consultation' => ['Z00.00', 'Z00.01'],
            'follow_up' => ['Z09.00', 'Z09.01'],
            'emergency' => ['Z00.00', 'R50.9'],
            'routine' => ['Z00.00', 'Z00.01'],
            'procedure' => ['Z00.00', 'Z00.01']
        ];

        return $codes[$type] ?? ['Z00.00'];
    }
}
