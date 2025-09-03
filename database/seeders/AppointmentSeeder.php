<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Clinic;
use App\Models\Room;
use Carbon\Carbon;

class AppointmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $patients = Patient::all();
        $doctors = Doctor::all();
        $clinics = Clinic::all();
        $rooms = Room::where('type', 'consultation')->get();

        if ($patients->isEmpty() || $doctors->isEmpty() || $clinics->isEmpty() || $rooms->isEmpty()) {
            $this->command->error('Required data not found. Please run PatientSeeder, DoctorSeeder, and RoomSeeder first.');
            return;
        }

        $appointmentStatuses = ['scheduled', 'confirmed', 'in_progress', 'completed', 'cancelled', 'no_show'];
        $appointmentSources = ['walk_in', 'phone', 'online', 'referral', 'follow_up'];

        $reasons = [
            'General Check-up',
            'Follow-up Consultation',
            'Symptom Evaluation',
            'Prescription Renewal',
            'Lab Results Review',
            'Vaccination',
            'Emergency Care',
            'Specialist Consultation',
            'Pre-operative Assessment',
            'Post-operative Follow-up'
        ];

        // Generate appointments for the next 30 days
        $startDate = Carbon::now()->startOfDay();

        for ($day = 0; $day < 30; $day++) {
            $currentDate = $startDate->copy()->addDays($day);

            // Skip weekends for most clinics
            if ($currentDate->isWeekend()) {
                continue;
            }

            // Generate 5-15 appointments per day
            $appointmentsPerDay = rand(5, 15);

            for ($i = 0; $i < $appointmentsPerDay; $i++) {
                $patient = $patients->random();
                $doctor = $doctors->random();
                $clinic = $doctor->clinic;
                $room = $rooms->where('clinic_id', $clinic->id)->first();

                if (!$room) {
                    $room = Room::where('clinic_id', $clinic->id)->first();
                }

                if (!$room) {
                    continue;
                }

                // Generate appointment time between 8 AM and 5 PM
                $hour = rand(8, 16);
                $minute = rand(0, 3) * 15; // 15-minute intervals
                $startTime = $currentDate->copy()->setTime($hour, $minute);
                $endTime = $startTime->copy()->addMinutes(30); // 30-minute appointments

                // Determine status based on date
                $status = 'scheduled';
                if ($currentDate->isPast()) {
                    $status = $appointmentStatuses[array_rand($appointmentStatuses)];
                }

                Appointment::firstOrCreate(
                    [
                        'clinic_id' => $clinic->id,
                        'patient_id' => $patient->id,
                        'doctor_id' => $doctor->id,
                        'start_at' => $startTime,
                        'end_at' => $endTime,
                        'room_id' => $room->id
                    ],
                    [
                        'clinic_id' => $clinic->id,
                        'patient_id' => $patient->id,
                        'doctor_id' => $doctor->id,
                        'start_at' => $startTime,
                        'end_at' => $endTime,
                        'status' => $status,
                        'room_id' => $room->id,
                        'reason' => $reasons[array_rand($reasons)],
                        'source' => $appointmentSources[array_rand($appointmentSources)]
                    ]
                );
            }
        }

        $this->command->info('Appointments seeded successfully!');
    }
}
