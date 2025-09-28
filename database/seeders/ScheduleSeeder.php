<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Schedule;
use App\Models\User;
use App\Models\Clinic;
use App\Models\Room;
use App\Models\Role;

class ScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first clinic
        $clinic = Clinic::first();
        if (!$clinic) {
            $this->command->info('No clinic found. Please run ClinicSeeder first.');
            return;
        }

        // Get doctors from the clinic
        $doctorRole = Role::where('name', 'doctor')->first();
        if (!$doctorRole) {
            $this->command->info('No doctor role found.');
            return;
        }

        $doctors = User::whereHas('userClinicRoles', function ($q) use ($clinic, $doctorRole) {
            $q->where('clinic_id', $clinic->id)
              ->where('role_id', $doctorRole->id);
        })->get();

        if ($doctors->isEmpty()) {
            $this->command->info('No doctors found in clinic. Please create doctors first.');
            return;
        }

        // Get rooms from the clinic
        $rooms = Room::where('clinic_id', $clinic->id)->get();

        // Create sample schedules
        $scheduleData = [
            [
                'day_of_week' => 'Monday',
                'start_time' => '09:00:00',
                'end_time' => '17:00:00',
                'status' => 'Active',
                'max_appointments' => 15,
                'appointment_duration' => 30,
                'break_duration' => 15,
            ],
            [
                'day_of_week' => 'Tuesday',
                'start_time' => '09:00:00',
                'end_time' => '17:00:00',
                'status' => 'Active',
                'max_appointments' => 15,
                'appointment_duration' => 30,
                'break_duration' => 15,
            ],
            [
                'day_of_week' => 'Wednesday',
                'start_time' => '09:00:00',
                'end_time' => '17:00:00',
                'status' => 'Active',
                'max_appointments' => 15,
                'appointment_duration' => 30,
                'break_duration' => 15,
            ],
            [
                'day_of_week' => 'Thursday',
                'start_time' => '09:00:00',
                'end_time' => '17:00:00',
                'status' => 'Active',
                'max_appointments' => 15,
                'appointment_duration' => 30,
                'break_duration' => 15,
            ],
            [
                'day_of_week' => 'Friday',
                'start_time' => '09:00:00',
                'end_time' => '17:00:00',
                'status' => 'Active',
                'max_appointments' => 15,
                'appointment_duration' => 30,
                'break_duration' => 15,
            ],
            [
                'day_of_week' => 'Saturday',
                'start_time' => '09:00:00',
                'end_time' => '13:00:00',
                'status' => 'Active',
                'max_appointments' => 8,
                'appointment_duration' => 30,
                'break_duration' => 15,
            ],
        ];

        foreach ($doctors as $doctor) {
            foreach ($scheduleData as $data) {
                Schedule::create([
                    'clinic_id' => $clinic->id,
                    'doctor_id' => $doctor->id,
                    'room_id' => $rooms->isNotEmpty() ? $rooms->random()->id : null,
                    'day_of_week' => $data['day_of_week'],
                    'start_time' => $data['start_time'],
                    'end_time' => $data['end_time'],
                    'status' => $data['status'],
                    'is_recurring' => true,
                    'recurring_type' => 'weekly',
                    'recurring_interval' => 1,
                    'notes' => 'Regular weekly schedule',
                    'max_appointments' => $data['max_appointments'],
                    'appointment_duration' => $data['appointment_duration'],
                    'break_duration' => $data['break_duration'],
                    'is_active' => true,
                    'created_by' => $doctor->id,
                ]);
            }
        }

        $this->command->info('Schedules created successfully!');
    }
}