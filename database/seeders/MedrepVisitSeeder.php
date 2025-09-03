<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MedrepVisit;
use App\Models\Medrep;
use App\Models\Clinic;
use App\Models\Doctor;
use Carbon\Carbon;

class MedrepVisitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $medreps = Medrep::all();
        $clinics = Clinic::all();
        $doctors = Doctor::all();
        
        if ($medreps->isEmpty() || $clinics->isEmpty() || $doctors->isEmpty()) {
            $this->command->error('Required data not found. Please run MedrepSeeder, ClinicSeeder, and DoctorSeeder first.');
            return;
        }

        $visitPurposes = [
            'Product Introduction',
            'Product Update',
            'Clinical Data Presentation',
            'Sample Distribution',
            'Literature Distribution',
            'Follow-up Visit',
            'Contract Discussion',
            'Training Session',
            'Market Research',
            'Relationship Building'
        ];

        $visitNotes = [
            'Discussed new product benefits and clinical data',
            'Provided product samples and literature',
            'Addressed doctor\'s questions about side effects',
            'Scheduled follow-up meeting for next month',
            'Shared latest clinical trial results',
            'Discussed pricing and availability',
            'Provided product training materials',
            'Collected feedback on existing products',
            'Discussed potential clinical studies',
            'Addressed reimbursement questions'
        ];

        // Generate visits for the past 6 months
        $startDate = Carbon::now()->subMonths(6);
        
        for ($month = 0; $month < 6; $month++) {
            $currentDate = $startDate->copy()->addMonths($month);
            
            // Generate 20-50 visits per month
            $visitsPerMonth = rand(20, 50);
            
            for ($i = 0; $i < $visitsPerMonth; $i++) {
                $medrep = $medreps->random();
                $clinic = $clinics->random();
                $doctor = $doctors->where('clinic_id', $clinic->id)->first();
                
                if (!$doctor) {
                    $doctor = $doctors->random();
                }
                
                // Generate visit time between 8 AM and 6 PM
                $hour = rand(8, 17);
                $minute = rand(0, 3) * 15; // 15-minute intervals
                $startTime = $currentDate->copy()->setTime($hour, $minute);
                $endTime = $startTime->copy()->addMinutes(rand(30, 120)); // 30 minutes to 2 hours
                
                // Skip weekends for most visits
                if ($startTime->isWeekend()) {
                    continue;
                }
                
                MedrepVisit::firstOrCreate(
                    [
                        'clinic_id' => $clinic->id,
                        'medrep_id' => $medrep->id,
                        'doctor_id' => $doctor->id,
                        'start_at' => $startTime,
                        'end_at' => $endTime
                    ],
                    [
                        'clinic_id' => $clinic->id,
                        'medrep_id' => $medrep->id,
                        'doctor_id' => $doctor->id,
                        'start_at' => $startTime,
                        'end_at' => $endTime,
                        'purpose' => $visitPurposes[array_rand($visitPurposes)],
                        'notes' => $visitNotes[array_rand($visitNotes)]
                    ]
                );
            }
        }

        // Generate some future scheduled visits
        $futureStartDate = Carbon::now()->addDays(1);
        for ($day = 0; $day < 30; $day++) {
            $currentDate = $futureStartDate->copy()->addDays($day);
            
            // Skip weekends
            if ($currentDate->isWeekend()) {
                continue;
            }
            
            // Generate 5-15 scheduled visits per day
            $scheduledVisitsPerDay = rand(5, 15);
            
            for ($i = 0; $i < $scheduledVisitsPerDay; $i++) {
                $medrep = $medreps->random();
                $clinic = $clinics->random();
                $doctor = $doctors->where('clinic_id', $clinic->id)->first();
                
                if (!$doctor) {
                    $doctor = $doctors->random();
                }
                
                // Generate visit time between 9 AM and 5 PM
                $hour = rand(9, 16);
                $minute = rand(0, 3) * 15; // 15-minute intervals
                $startTime = $currentDate->copy()->setTime($hour, $minute);
                $endTime = $startTime->copy()->addMinutes(rand(45, 90)); // 45 minutes to 1.5 hours
                
                MedrepVisit::firstOrCreate(
                    [
                        'clinic_id' => $clinic->id,
                        'medrep_id' => $medrep->id,
                        'doctor_id' => $doctor->id,
                        'start_at' => $startTime,
                        'end_at' => $endTime
                    ],
                    [
                        'clinic_id' => $clinic->id,
                        'medrep_id' => $medrep->id,
                        'doctor_id' => $doctor->id,
                        'start_at' => $startTime,
                        'end_at' => $endTime,
                        'purpose' => $visitPurposes[array_rand($visitPurposes)],
                        'notes' => 'Scheduled visit - ' . $visitPurposes[array_rand($visitPurposes)]
                    ]
                );
            }
        }

        $this->command->info('Medical representative visits seeded successfully!');
    }
}
