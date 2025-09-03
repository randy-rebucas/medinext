<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FileAsset;
use App\Models\Clinic;
use App\Models\Patient;
use App\Models\LabResult;
use App\Models\Prescription;
use App\Models\Encounter;
use Carbon\Carbon;

class FileAssetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clinics = Clinic::all();
        $patients = Patient::all();
        $labResults = LabResult::all();
        $prescriptions = Prescription::all();
        $encounters = Encounter::all();
        
        if ($clinics->isEmpty()) {
            $this->command->error('No clinics found. Please run ClinicSeeder first.');
            return;
        }

        $fileCategories = [
            'medical_records' => 'Medical Records',
            'lab_results' => 'Laboratory Results',
            'imaging' => 'Medical Imaging',
            'prescriptions' => 'Prescriptions',
            'consent_forms' => 'Consent Forms',
            'insurance' => 'Insurance Documents',
            'billing' => 'Billing Documents',
            'correspondence' => 'Correspondence',
            'referrals' => 'Referral Letters',
            'discharge_summaries' => 'Discharge Summaries'
        ];

        $mimeTypes = [
            'application/pdf' => 'pdf',
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
            'application/vnd.ms-excel' => 'xls',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
            'text/plain' => 'txt'
        ];

        // Generate file assets for patients
        foreach ($patients as $patient) {
            $clinic = $clinics->random();
            $this->createPatientFiles($patient, $clinic, $fileCategories, $mimeTypes);
        }

        // Generate file assets for lab results
        foreach ($labResults as $labResult) {
            $clinic = $labResult->clinic;
            $this->createLabResultFiles($labResult, $clinic, $fileCategories, $mimeTypes);
        }

        // Generate file assets for prescriptions
        foreach ($prescriptions as $prescription) {
            $clinic = $prescription->clinic;
            $this->createPrescriptionFiles($prescription, $clinic, $fileCategories, $mimeTypes);
        }

        // Generate file assets for encounters
        foreach ($encounters as $encounter) {
            $clinic = $encounter->clinic;
            $this->createEncounterFiles($encounter, $clinic, $fileCategories, $mimeTypes);
        }

        $this->command->info('File assets seeded successfully!');
    }

    private function createPatientFiles($patient, $clinic, $fileCategories, $mimeTypes): void
    {
        $patientFiles = [
            'medical_records' => [
                'Patient History Form',
                'Medical Questionnaire',
                'Allergy Information',
                'Family History',
                'Social History'
            ],
            'insurance' => [
                'Insurance Card Front',
                'Insurance Card Back',
                'Insurance Policy',
                'Authorization Form'
            ],
            'consent_forms' => [
                'General Consent Form',
                'HIPAA Consent Form',
                'Treatment Consent Form'
            ]
        ];

        foreach ($patientFiles as $category => $fileNames) {
            foreach ($fileNames as $fileName) {
                $mimeType = array_rand($mimeTypes);
                $extension = $mimeTypes[$mimeType];
                
                FileAsset::firstOrCreate(
                    [
                        'clinic_id' => $clinic->id,
                        'owner_type' => Patient::class,
                        'owner_id' => $patient->id,
                        'file_name' => $fileName . '.' . $extension
                    ],
                    [
                        'clinic_id' => $clinic->id,
                        'owner_type' => Patient::class,
                        'owner_id' => $patient->id,
                        'url' => '/storage/files/patients/' . $patient->id . '/' . strtolower(str_replace(' ', '_', $fileName)) . '.' . $extension,
                        'mime' => $mimeType,
                        'size' => rand(1024, 10240), // 1KB to 10KB
                        'checksum' => md5(uniqid()),
                        'category' => $category,
                        'description' => $fileName . ' for ' . $patient->name,
                        'file_name' => $fileName . '.' . $extension,
                        'original_name' => $fileName . '.' . $extension
                    ]
                );
            }
        }
    }

    private function createLabResultFiles($labResult, $clinic, $fileCategories, $mimeTypes): void
    {
        $labFiles = [
            'lab_results' => [
                'Lab Report',
                'Test Results Summary',
                'Quality Control Report'
            ],
            'imaging' => [
                'X-Ray Image',
                'MRI Scan',
                'CT Scan',
                'Ultrasound Image'
            ]
        ];

        foreach ($labFiles as $category => $fileNames) {
            foreach ($fileNames as $fileName) {
                $mimeType = array_rand($mimeTypes);
                $extension = $mimeTypes[$mimeType];
                
                FileAsset::firstOrCreate(
                    [
                        'clinic_id' => $clinic->id,
                        'owner_type' => LabResult::class,
                        'owner_id' => $labResult->id,
                        'file_name' => $fileName . '.' . $extension
                    ],
                    [
                        'clinic_id' => $clinic->id,
                        'owner_type' => LabResult::class,
                        'owner_id' => $labResult->id,
                        'url' => '/storage/files/lab_results/' . $labResult->id . '/' . strtolower(str_replace(' ', '_', $fileName)) . '.' . $extension,
                        'mime' => $mimeType,
                        'size' => rand(10240, 102400), // 10KB to 100KB
                        'checksum' => md5(uniqid()),
                        'category' => $category,
                        'description' => $fileName . ' for ' . $labResult->test_name,
                        'file_name' => $fileName . '.' . $extension,
                        'original_name' => $fileName . '.' . $extension
                    ]
                );
            }
        }
    }

    private function createPrescriptionFiles($prescription, $clinic, $fileCategories, $mimeTypes): void
    {
        $prescriptionFiles = [
            'prescriptions' => [
                'Prescription Form',
                'Medication Instructions',
                'Dosage Schedule'
            ]
        ];

        foreach ($prescriptionFiles as $category => $fileNames) {
            foreach ($fileNames as $fileName) {
                $mimeType = array_rand($mimeTypes);
                $extension = $mimeTypes[$mimeType];
                
                FileAsset::firstOrCreate(
                    [
                        'clinic_id' => $clinic->id,
                        'owner_type' => Prescription::class,
                        'owner_id' => $prescription->id,
                        'file_name' => $fileName . '.' . $extension
                    ],
                    [
                        'clinic_id' => $clinic->id,
                        'owner_type' => Prescription::class,
                        'owner_id' => $prescription->id,
                        'url' => '/storage/files/prescriptions/' . $prescription->id . '/' . strtolower(str_replace(' ', '_', $fileName)) . '.' . $extension,
                        'mime' => $mimeType,
                        'size' => rand(1024, 10240), // 1KB to 10KB
                        'checksum' => md5(uniqid()),
                        'category' => $category,
                        'description' => $fileName . ' for prescription #' . $prescription->id,
                        'file_name' => $fileName . '.' . $extension,
                        'original_name' => $fileName . '.' . $extension
                    ]
                );
            }
        }
    }

    private function createEncounterFiles($encounter, $clinic, $fileCategories, $mimeTypes): void
    {
        $encounterFiles = [
            'medical_records' => [
                'Encounter Notes',
                'Vital Signs Record',
                'Physical Examination',
                'Assessment Summary',
                'Treatment Plan'
            ],
            'correspondence' => [
                'Referral Letter',
                'Consultation Report',
                'Follow-up Instructions'
            ]
        ];

        foreach ($encounterFiles as $category => $fileNames) {
            foreach ($fileNames as $fileName) {
                $mimeType = array_rand($mimeTypes);
                $extension = $mimeTypes[$mimeType];
                
                FileAsset::firstOrCreate(
                    [
                        'clinic_id' => $clinic->id,
                        'owner_type' => Encounter::class,
                        'owner_id' => $encounter->id,
                        'file_name' => $fileName . '.' . $extension
                    ],
                    [
                        'clinic_id' => $clinic->id,
                        'owner_type' => Encounter::class,
                        'owner_id' => $encounter->id,
                        'url' => '/storage/files/encounters/' . $encounter->id . '/' . strtolower(str_replace(' ', '_', $fileName)) . '.' . $extension,
                        'mime' => $mimeType,
                        'size' => rand(1024, 10240), // 1KB to 10KB
                        'checksum' => md5(uniqid()),
                        'category' => $category,
                        'description' => $fileName . ' for encounter #' . $encounter->id,
                        'file_name' => $fileName . '.' . $extension,
                        'original_name' => $fileName . '.' . $extension
                    ]
                );
            }
        }
    }
}
