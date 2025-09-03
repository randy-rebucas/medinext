<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PrescriptionItem;
use App\Models\Prescription;

class PrescriptionItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $prescriptions = Prescription::all();
        
        if ($prescriptions->isEmpty()) {
            $this->command->error('No prescriptions found. Please run PrescriptionSeeder first.');
            return;
        }

        $drugNames = [
            'Paracetamol', 'Ibuprofen', 'Amoxicillin', 'Omeprazole', 'Metformin',
            'Amlodipine', 'Losartan', 'Simvastatin', 'Aspirin', 'Warfarin',
            'Insulin', 'Morphine', 'Codeine', 'Tramadol', 'Diazepam',
            'Loratadine', 'Cetirizine', 'Ranitidine', 'Ciprofloxacin', 'Azithromycin',
            'Doxycycline', 'Clarithromycin', 'Fluconazole', 'Acyclovir', 'Valacyclovir',
            'Lisinopril', 'Enalapril', 'Furosemide', 'Spironolactone', 'Hydrochlorothiazide',
            'Atorvastatin', 'Rosuvastatin', 'Pravastatin', 'Clopidogrel', 'Ticagrelor'
        ];

        $strengths = [
            '250mg', '500mg', '750mg', '1000mg', '10mg', '20mg', '40mg', '50mg',
            '100mg', '200mg', '300mg', '400mg', '5mg', '15mg', '25mg', '30mg',
            '1mg', '2mg', '5mg', '10mg', '20mg', '25mg', '50mg', '75mg'
        ];

        $forms = [
            'tablet', 'capsule', 'liquid', 'injection', 'cream', 'ointment',
            'gel', 'suppository', 'inhaler', 'drops', 'syrup', 'suspension'
        ];

        $sigExamples = [
            'Take 1 tablet by mouth every 8 hours as needed for pain',
            'Take 1 capsule by mouth twice daily with food',
            'Take 2 tablets by mouth once daily in the morning',
            'Apply thin layer to affected area 3 times daily',
            'Take 1 tablet by mouth every 12 hours',
            'Take 1 tablet by mouth once daily at bedtime',
            'Take 1 tablet by mouth 30 minutes before meals',
            'Take 1 tablet by mouth with plenty of water',
            'Apply to clean, dry skin twice daily',
            'Take 1 tablet by mouth every 6 hours as needed'
        ];

        foreach ($prescriptions as $prescription) {
            // Generate 1-4 prescription items per prescription
            $itemsCount = rand(1, 4);
            
            for ($i = 0; $i < $itemsCount; $i++) {
                $drugName = $drugNames[array_rand($drugNames)];
                $strength = $strengths[array_rand($strengths)];
                $form = $forms[array_rand($forms)];
                $sig = $sigExamples[array_rand($sigExamples)];
                $quantity = rand(10, 90);
                $refills = rand(0, 3);
                
                PrescriptionItem::firstOrCreate(
                    [
                        'prescription_id' => $prescription->id,
                        'drug_name' => $drugName,
                        'strength' => $strength,
                        'form' => $form
                    ],
                    [
                        'prescription_id' => $prescription->id,
                        'drug_name' => $drugName,
                        'strength' => $strength,
                        'form' => $form,
                        'sig' => $sig,
                        'quantity' => $quantity,
                        'refills' => $refills,
                        'notes' => $this->generateNotes($drugName, $form)
                    ]
                );
            }
        }

        $this->command->info('Prescription items seeded successfully!');
    }

    private function generateNotes($drugName, $form): string
    {
        $notes = [
            'Take with food to minimize stomach upset',
            'Avoid alcohol while taking this medication',
            'Store in a cool, dry place',
            'Keep out of reach of children',
            'Do not drive or operate machinery while taking this medication',
            'Take at the same time each day',
            'Complete the full course of treatment',
            'May cause drowsiness',
            'Take on an empty stomach',
            'Shake well before use',
            'Apply to clean, dry skin only',
            'Do not exceed recommended dosage'
        ];

        return $notes[array_rand($notes)];
    }
}
