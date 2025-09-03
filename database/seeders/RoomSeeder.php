<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Room;
use App\Models\Clinic;

class RoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clinics = Clinic::all();
        
        if ($clinics->isEmpty()) {
            $this->command->error('No clinics found. Please run ClinicSeeder first.');
            return;
        }

        $roomTypes = [
            'consultation' => 'Consultation Room',
            'examination' => 'Examination Room',
            'procedure' => 'Procedure Room',
            'emergency' => 'Emergency Room',
            'operating' => 'Operating Room',
            'recovery' => 'Recovery Room',
            'waiting' => 'Waiting Room',
            'pharmacy' => 'Pharmacy',
            'laboratory' => 'Laboratory',
            'radiology' => 'Radiology Room',
            'therapy' => 'Therapy Room',
            'isolation' => 'Isolation Room'
        ];

        foreach ($clinics as $clinic) {
            // Create different types of rooms for each clinic
            $roomNames = [
                'consultation' => ['Room 101', 'Room 102', 'Room 103'],
                'examination' => ['Exam Room A', 'Exam Room B', 'Exam Room C'],
                'procedure' => ['Procedure Room 1', 'Procedure Room 2'],
                'emergency' => ['ER 1', 'ER 2'],
                'operating' => ['OR 1', 'OR 2'],
                'recovery' => ['Recovery 1', 'Recovery 2'],
                'waiting' => ['Main Waiting Area', 'VIP Waiting Area'],
                'pharmacy' => ['Main Pharmacy'],
                'laboratory' => ['Lab 1', 'Lab 2'],
                'radiology' => ['X-Ray Room', 'MRI Room', 'CT Room'],
                'therapy' => ['Physical Therapy', 'Occupational Therapy'],
                'isolation' => ['Isolation Room 1', 'Isolation Room 2']
            ];

            foreach ($roomTypes as $type => $displayName) {
                if (isset($roomNames[$type])) {
                    foreach ($roomNames[$type] as $roomName) {
                        Room::firstOrCreate(
                            [
                                'clinic_id' => $clinic->id,
                                'name' => $roomName,
                                'type' => $type
                            ],
                            [
                                'clinic_id' => $clinic->id,
                                'name' => $roomName,
                                'type' => $type
                            ]
                        );
                    }
                }
            }
        }

        $this->command->info('Rooms seeded successfully!');
    }
}
