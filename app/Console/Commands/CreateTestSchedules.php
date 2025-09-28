<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Schedule;
use App\Models\User;
use App\Models\Clinic;
use App\Models\Room;
use App\Models\Role;

class CreateTestSchedules extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:test-schedules';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create test schedules for demonstration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Creating test schedules...');
        
        $clinic = Clinic::first();
        if (!$clinic) {
            $this->error('No clinic found!');
            return;
        }
        
        $doctorRole = Role::where('name', 'doctor')->first();
        if (!$doctorRole) {
            $this->error('No doctor role found!');
            return;
        }
        
        $doctors = User::whereHas('userClinicRoles', function ($q) use ($clinic, $doctorRole) {
            $q->where('clinic_id', $clinic->id)
              ->where('role_id', $doctorRole->id);
        })->get();
        
        if ($doctors->isEmpty()) {
            $this->error('No doctors found in clinic!');
            return;
        }
        
        $rooms = Room::where('clinic_id', $clinic->id)->get();
        
        $scheduleData = [
            ['day' => 'Monday', 'start' => '09:00:00', 'end' => '17:00:00'],
            ['day' => 'Tuesday', 'start' => '09:00:00', 'end' => '17:00:00'],
            ['day' => 'Wednesday', 'start' => '09:00:00', 'end' => '17:00:00'],
            ['day' => 'Thursday', 'start' => '09:00:00', 'end' => '17:00:00'],
            ['day' => 'Friday', 'start' => '09:00:00', 'end' => '17:00:00'],
        ];
        
        $created = 0;
        foreach ($doctors as $doctor) {
            foreach ($scheduleData as $data) {
                Schedule::create([
                    'clinic_id' => $clinic->id,
                    'doctor_id' => $doctor->id,
                    'room_id' => $rooms->isNotEmpty() ? $rooms->random()->id : null,
                    'day_of_week' => $data['day'],
                    'start_time' => $data['start'],
                    'end_time' => $data['end'],
                    'status' => 'Active',
                    'is_recurring' => true,
                    'recurring_type' => 'weekly',
                    'recurring_interval' => 1,
                    'notes' => 'Test schedule for ' . $doctor->name,
                    'max_appointments' => 15,
                    'appointment_duration' => 30,
                    'break_duration' => 15,
                    'is_active' => true,
                    'created_by' => $doctor->id,
                ]);
                $created++;
            }
        }
        
        $this->info("✅ Created {$created} test schedules!");
        $this->info("Total schedules: " . Schedule::count());
    }
}
