<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LabResult;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Clinic;
use App\Models\Encounter;
use Carbon\Carbon;

class LabResultSeeder extends Seeder
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

        $testTypes = [
            'blood' => [
                'Complete Blood Count (CBC)' => ['4.5-11.0', '10^9/L'],
                'Hemoglobin' => ['12.0-16.0', 'g/dL'],
                'White Blood Cell Count' => ['4.5-11.0', '10^9/L'],
                'Platelet Count' => ['150-450', '10^9/L'],
                'Blood Glucose (Fasting)' => ['70-100', 'mg/dL'],
                'Cholesterol Total' => ['<200', 'mg/dL'],
                'HDL Cholesterol' => ['>40', 'mg/dL'],
                'LDL Cholesterol' => ['<100', 'mg/dL'],
                'Triglycerides' => ['<150', 'mg/dL'],
                'Creatinine' => ['0.6-1.2', 'mg/dL'],
                'BUN' => ['7-20', 'mg/dL'],
                'Sodium' => ['135-145', 'mEq/L'],
                'Potassium' => ['3.5-5.0', 'mEq/L'],
                'Chloride' => ['96-106', 'mEq/L'],
                'CO2' => ['22-28', 'mEq/L']
            ],
            'urine' => [
                'Urinalysis - pH' => ['4.5-8.0', ''],
                'Urinalysis - Specific Gravity' => ['1.005-1.030', ''],
                'Urinalysis - Protein' => ['Negative', ''],
                'Urinalysis - Glucose' => ['Negative', ''],
                'Urinalysis - Ketones' => ['Negative', ''],
                'Urinalysis - Blood' => ['Negative', ''],
                'Urinalysis - Leukocytes' => ['Negative', '']
            ],
            'stool' => [
                'Stool Occult Blood' => ['Negative', ''],
                'Stool Culture' => ['No growth', ''],
                'Stool Ova and Parasites' => ['Negative', '']
            ],
            'xray' => [
                'Chest X-Ray' => ['Normal', ''],
                'Abdominal X-Ray' => ['Normal', ''],
                'Spine X-Ray' => ['Normal', '']
            ],
            'mri' => [
                'Brain MRI' => ['Normal', ''],
                'Spine MRI' => ['Normal', ''],
                'Knee MRI' => ['Normal', '']
            ],
            'ct' => [
                'Chest CT' => ['Normal', ''],
                'Abdominal CT' => ['Normal', ''],
                'Head CT' => ['Normal', '']
            ],
            'ultrasound' => [
                'Abdominal Ultrasound' => ['Normal', ''],
                'Pelvic Ultrasound' => ['Normal', ''],
                'Cardiac Echo' => ['Normal', '']
            ],
            'ecg' => [
                '12-Lead ECG' => ['Normal sinus rhythm', ''],
                'Stress Test ECG' => ['Normal', '']
            ],
            'echo' => [
                'Transthoracic Echo' => ['Normal', ''],
                'Transesophageal Echo' => ['Normal', '']
            ]
        ];

        $statuses = ['pending', 'completed', 'abnormal', 'critical'];
        
        // Generate lab results for the past 6 months
        $startDate = Carbon::now()->subMonths(6);
        
        for ($month = 0; $month < 6; $month++) {
            $currentDate = $startDate->copy()->addMonths($month);
            
            // Generate 10-30 lab results per month
            $resultsPerMonth = rand(10, 30);
            
            for ($i = 0; $i < $resultsPerMonth; $i++) {
                $patient = $patients->random();
                $doctor = $doctors->random();
                $clinic = $doctor->clinic;
                $encounter = $encounters->isNotEmpty() ? $encounters->random() : null;
                
                // Select random test type and test
                $testType = array_rand($testTypes);
                $testName = array_rand($testTypes[$testType]);
                $referenceRange = $testTypes[$testType][$testName];
                
                $orderedAt = $currentDate->copy()->addDays(rand(1, 28));
                $completedAt = $orderedAt->copy()->addDays(rand(1, 7));
                $status = $statuses[array_rand($statuses)];
                
                // Generate result value based on test type and status
                $resultValue = $this->generateResultValue($testType, $testName, $status, $referenceRange);
                
                LabResult::firstOrCreate(
                    [
                        'clinic_id' => $clinic->id,
                        'patient_id' => $patient->id,
                        'encounter_id' => $encounter?->id,
                        'test_type' => $testType,
                        'test_name' => $testName,
                        'ordered_at' => $orderedAt
                    ],
                    [
                        'clinic_id' => $clinic->id,
                        'patient_id' => $patient->id,
                        'encounter_id' => $encounter?->id,
                        'test_type' => $testType,
                        'test_name' => $testName,
                        'result_value' => $resultValue,
                        'unit' => $referenceRange[1],
                        'reference_range' => $referenceRange[0],
                        'status' => $status,
                        'ordered_at' => $orderedAt,
                        'completed_at' => $completedAt,
                        'ordered_by_doctor_id' => $doctor->id,
                        'reviewed_by_doctor_id' => $doctor->id,
                        'notes' => $this->generateNotes($testType, $testName, $status)
                    ]
                );
            }
        }

        $this->command->info('Lab results seeded successfully!');
    }
    
    private function generateResultValue($testType, $testName, $status, $referenceRange): string
    {
        if ($status === 'pending') {
            return 'Pending';
        }
        
        switch ($testType) {
            case 'blood':
                return $this->generateBloodResult($testName, $status, $referenceRange);
            case 'urine':
                return $this->generateUrineResult($testName, $status, $referenceRange);
            case 'stool':
                return $this->generateStoolResult($testName, $status, $referenceRange);
            case 'xray':
            case 'mri':
            case 'ct':
            case 'ultrasound':
            case 'ecg':
            case 'echo':
                return $this->generateImagingResult($testName, $status, $referenceRange);
            default:
                return 'Normal';
        }
    }
    
    private function generateBloodResult($testName, $status, $referenceRange): string
    {
        if ($status === 'normal') {
            return (string) rand(70, 100);
        } elseif ($status === 'abnormal') {
            return (string) rand(110, 150);
        } elseif ($status === 'critical') {
            return (string) rand(160, 200);
        }
        return (string) rand(70, 100);
    }
    
    private function generateUrineResult($testName, $status, $referenceRange): string
    {
        if ($status === 'normal') {
            return 'Negative';
        } elseif ($status === 'abnormal') {
            return 'Trace';
        } elseif ($status === 'critical') {
            return 'Positive';
        }
        return 'Negative';
    }
    
    private function generateStoolResult($testName, $status, $referenceRange): string
    {
        if ($status === 'normal') {
            return 'Negative';
        } elseif ($status === 'abnormal') {
            return 'Trace';
        } elseif ($status === 'critical') {
            return 'Positive';
        }
        return 'Negative';
    }
    
    private function generateImagingResult($testName, $status, $referenceRange): string
    {
        if ($status === 'normal') {
            return 'Normal';
        } elseif ($status === 'abnormal') {
            return 'Mild findings';
        } elseif ($status === 'critical') {
            return 'Significant findings';
        }
        return 'Normal';
    }
    
    private function generateNotes($testType, $testName, $status): string
    {
        if ($status === 'normal') {
            return 'Results within normal limits';
        } elseif ($status === 'abnormal') {
            return 'Results slightly outside normal range. Follow-up recommended.';
        } elseif ($status === 'critical') {
            return 'Results significantly abnormal. Immediate attention required.';
        }
        return 'Test ordered, results pending';
    }
}
