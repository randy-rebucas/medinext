<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Schedule;
use App\Models\User;
use App\Models\Clinic;
use App\Models\Role;
use App\Models\Room;

class TestScheduleSystem extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:schedule-system';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the schedule management system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing Schedule Management System...');
        
        // Check if we have the necessary data
        $clinics = Clinic::count();
        $users = User::count();
        $roles = Role::count();
        $rooms = Room::count();
        $schedules = Schedule::count();
        
        $this->info("Clinics: {$clinics}");
        $this->info("Users: {$users}");
        $this->info("Roles: {$roles}");
        $this->info("Rooms: {$rooms}");
        $this->info("Schedules: {$schedules}");
        
        // Check roles
        $this->info("\nAvailable Roles:");
        Role::all()->each(function ($role) {
            $this->info("- {$role->name}");
        });
        
        // Check if we have doctors
        $doctorRole = Role::where('name', 'doctor')->first();
        if ($doctorRole) {
            $doctors = User::whereHas('roles', function ($q) use ($doctorRole) {
                $q->where('role_id', $doctorRole->id);
            })->count();
            $this->info("\nDoctors: {$doctors}");
        } else {
            $this->warn("No 'doctor' role found!");
        }
        
        // Test schedule creation if we have the necessary data
        if ($clinics > 0 && $users > 0 && $doctorRole) {
            $clinic = Clinic::first();
            $doctor = User::whereHas('roles', function ($q) use ($doctorRole) {
                $q->where('role_id', $doctorRole->id);
            })->first();
            
            if ($doctor) {
                $this->info("\nTesting schedule creation...");
                
                $schedule = Schedule::create([
                    'clinic_id' => $clinic->id,
                    'doctor_id' => $doctor->id,
                    'room_id' => Room::first()?->id,
                    'day_of_week' => 'Monday',
                    'start_time' => '09:00:00',
                    'end_time' => '17:00:00',
                    'status' => 'Active',
                    'is_recurring' => true,
                    'recurring_type' => 'weekly',
                    'recurring_interval' => 1,
                    'notes' => 'Test schedule',
                    'max_appointments' => 10,
                    'appointment_duration' => 30,
                    'break_duration' => 15,
                    'is_active' => true,
                    'created_by' => $doctor->id,
                ]);
                
                $this->info("✅ Schedule created successfully! ID: {$schedule->id}");
                
                // Test schedule relationships
                $this->info("✅ Doctor: {$schedule->doctor->name}");
                $this->info("✅ Clinic: {$schedule->clinic->name}");
                if ($schedule->room) {
                    $this->info("✅ Room: {$schedule->room->name}");
                }
                
                // Test available slots
                $slots = $schedule->getAvailableSlots();
                $this->info("✅ Available slots: " . count($slots));
                
                // Clean up test schedule
                $schedule->delete();
                $this->info("✅ Test schedule cleaned up");
                
            } else {
                $this->warn("No doctors found!");
            }
        }
        
        $this->info("\n✅ Schedule system test completed!");
    }
}