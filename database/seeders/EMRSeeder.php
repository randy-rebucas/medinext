<?php

namespace Database\Seeders;

use App\Models\LabResult;
use App\Models\FileAsset;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Encounter;
use Illuminate\Database\Seeder;

class EMRSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing patients, doctors, and encounters
        $patients = Patient::all();
        $doctors = Doctor::all();
        $encounters = Encounter::all();

        if ($patients->isEmpty() || $doctors->isEmpty()) {
            $this->command->info('No patients or doctors found. Skipping EMR seeding.');
            return;
        }

        // Sample lab results
        $labResults = [
            [
                'test_type' => 'blood',
                'test_name' => 'Complete Blood Count (CBC)',
                'result_value' => 'Normal',
                'unit' => null,
                'reference_range' => 'Normal range',
                'status' => 'completed',
                'notes' => 'All parameters within normal limits',
            ],
            [
                'test_type' => 'blood',
                'test_name' => 'Hemoglobin',
                'result_value' => '14.2',
                'unit' => 'g/dL',
                'reference_range' => '12.0-16.0',
                'status' => 'completed',
                'notes' => 'Within normal range',
            ],
            [
                'test_type' => 'blood',
                'test_name' => 'Blood Glucose (Fasting)',
                'result_value' => '95',
                'unit' => 'mg/dL',
                'reference_range' => '70-100',
                'status' => 'completed',
                'notes' => 'Normal fasting glucose level',
            ],
            [
                'test_type' => 'urine',
                'test_name' => 'Urinalysis',
                'result_value' => 'Normal',
                'unit' => null,
                'reference_range' => 'Normal range',
                'status' => 'completed',
                'notes' => 'No abnormalities detected',
            ],
            [
                'test_type' => 'xray',
                'test_name' => 'Chest X-Ray',
                'result_value' => 'Normal',
                'unit' => null,
                'reference_range' => 'Normal range',
                'status' => 'completed',
                'notes' => 'Clear lung fields, normal cardiac silhouette',
            ],
            [
                'test_type' => 'ecg',
                'test_name' => 'Electrocardiogram',
                'result_value' => 'Normal sinus rhythm',
                'unit' => null,
                'reference_range' => 'Normal range',
                'status' => 'completed',
                'notes' => 'Normal ECG with regular rhythm',
            ],
        ];

        // Create lab results for each patient
        foreach ($patients as $patient) {
            $clinicId = $patient->clinic_id;
            $clinicDoctors = $doctors->where('clinic_id', $clinicId);
            $clinicEncounters = $encounters->where('clinic_id', $clinicId);

            if ($clinicDoctors->isEmpty() || $clinicEncounters->isEmpty()) {
                continue;
            }

            $doctor = $clinicDoctors->random();
            $encounter = $clinicEncounters->random();

            // Create 2-4 lab results per patient
            $numResults = rand(2, 4);
            $labResultsKeys = array_keys($labResults);
            $selectedTestIndices = array_rand($labResultsKeys, $numResults);

            if (!is_array($selectedTestIndices)) {
                $selectedTestIndices = [$selectedTestIndices];
            }

            foreach ($selectedTestIndices as $testIndex) {
                $testData = $labResults[$labResultsKeys[$testIndex]];

                // Debug logging
                $this->command->info("Creating lab result: " . json_encode($testData));

                LabResult::create([
                    'clinic_id' => $clinicId,
                    'patient_id' => $patient->id,
                    'encounter_id' => $encounter->id,
                    'test_type' => $testData['test_type'],
                    'test_name' => $testData['test_name'],
                    'result_value' => $testData['result_value'],
                    'unit' => $testData['unit'],
                    'reference_range' => $testData['reference_range'],
                    'status' => $testData['status'],
                    'ordered_at' => now()->subDays(rand(1, 30)),
                    'completed_at' => now()->subDays(rand(0, 29)),
                    'notes' => $testData['notes'],
                    'ordered_by_doctor_id' => $doctor->id,
                    'reviewed_by_doctor_id' => $doctor->id,
                ]);
            }
        }

        // Sample file assets (medical documents)
        $fileCategories = [
            'lab_report' => 'Lab Report',
            'xray' => 'X-Ray Image',
            'mri' => 'MRI Scan',
            'ct' => 'CT Scan',
            'ultrasound' => 'Ultrasound Image',
            'ecg' => 'ECG Report',
            'echo' => 'Echocardiogram',
            'biopsy' => 'Biopsy Report',
            'prescription' => 'Prescription',
            'medical_record' => 'Medical Record',
            'consent_form' => 'Consent Form',
        ];

        // Create file assets for some patients
        foreach ($patients->take(5) as $patient) {
            $clinicId = $patient->clinic_id;

            // Create 1-3 file assets per patient
            $numFiles = rand(1, 3);
            $categoryKeys = array_keys($fileCategories);
            $selectedCategoryIndices = array_rand($categoryKeys, $numFiles);

            if (!is_array($selectedCategoryIndices)) {
                $selectedCategoryIndices = [$selectedCategoryIndices];
            }

            foreach ($selectedCategoryIndices as $categoryIndex) {
                $category = $categoryKeys[$categoryIndex];
                $categoryName = $fileCategories[$category];

                FileAsset::create([
                    'clinic_id' => $clinicId,
                    'owner_type' => Patient::class,
                    'owner_id' => $patient->id,
                    'url' => 'sample-files/' . $category . '_' . $patient->id . '.pdf',
                    'mime' => 'application/pdf',
                    'size' => rand(100000, 2000000), // 100KB to 2MB
                    'checksum' => md5($patient->id . $category . time()),
                    'category' => $category,
                    'description' => $categoryName . ' for ' . $patient->full_name,
                    'file_name' => $category . '_' . $patient->id . '.pdf',
                    'original_name' => $categoryName . '_' . $patient->full_name . '.pdf',
                ]);
            }
        }

        $this->command->info('EMR data seeded successfully!');
        $this->command->info('Created lab results and file assets for patients.');
    }
}
