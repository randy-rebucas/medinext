<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Schedule;
use App\Models\User;
use App\Models\Clinic;
use App\Models\Room;
use App\Models\Role;

class CreateDemoSchedules extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:demo-schedules';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create demo schedules for the schedule management system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Creating demo schedules...');
        
        $clinic = Clinic::first();
        $doctor = User::first();
        $room = Room::first();
        
        if (!$clinic || !$doctor || !$room) {
            $this->error('Missing required data: clinic, doctor, or room');
            return;
        }
        
        $schedules = [
            [
                'day_of_week' => 'Monday',
                'start_time' => '09:00:00',
                'end_time' => '17:00:00',
                'status' => 'Active',
                'notes' => 'Monday morning clinic',
            ],
            [
                'day_of_week' => 'Tuesday',
                'start_time' => '09:00:00',
                'end_time' => '17:00:00',
                'status' => 'Active',
                'notes' => 'Tuesday clinic hours',
            ],
            [
                'day_of_week' => 'Wednesday',
                'start_time' => '09:00:00',
                'end_time' => '17:00:00',
                'status' => 'Active',
                'notes' => 'Wednesday clinic hours',
            ],
            [
                'day_of_week' => 'Thursday',
                'start_time' => '09:00:00',
                'end_time' => '17:00:00',
                'status' => 'Active',
                'notes' => 'Thursday clinic hours',
            ],
            [
                'day_of_week' => 'Friday',
                'start_time' => '09:00:00',
                'end_time' => '17:00:00',
                'status' => 'Active',
                'notes' => 'Friday clinic hours',
            ],
            [
                'day_of_week' => 'Saturday',
                'start_time' => '09:00:00',
                'end_time' => '13:00:00',
                'status' => 'Active',
                'notes' => 'Saturday half day',
            ],
        ];
        
        $created = 0;
        foreach ($schedules as $scheduleData) {
            Schedule::create([
                'clinic_id' => $clinic->id,
                'doctor_id' => $doctor->id,
                'room_id' => $room->id,
                'day_of_week' => $scheduleData['day_of_week'],
                'start_time' => $scheduleData['start_time'],
                'end_time' => $scheduleData['end_time'],
                'status' => $scheduleData['status'],
                'is_recurring' => true,
                'recurring_type' => 'weekly',
                'recurring_interval' => 1,
                'notes' => $scheduleData['notes'],
                'max_appointments' => 15,
                'appointment_duration' => 30,
                'break_duration' => 15,
                'is_active' => true,
                'created_by' => $doctor->id,
            ]);
            $created++;
        }
        
        $this->info("✅ Created {$created} demo schedules!");
        $this->info("Total schedules: " . Schedule::count());
    }
}
