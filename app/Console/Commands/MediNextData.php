<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Clinic;
use App\Models\User;
use App\Models\ActivityLog;
use App\Models\Notification;
use App\Models\Setting;
use App\Models\Bill;
use App\Models\Insurance;
use App\Models\Queue;
use App\Models\QueuePatient;
use App\Models\Appointment;
use App\Models\Encounter;
use App\Models\Prescription;
use App\Models\LabResult;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Room;

class MediNextData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'medinext:data
                            {action : Action to perform (clear, reset, status)}
                            {--force : Force operation without confirmation}
                            {--clinic= : Specific clinic slug to target}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage MediNext EMR data (clear, reset, status)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->argument('action');
        $force = $this->option('force');
        $clinicSlug = $this->option('clinic');

        switch ($action) {
            case 'clear':
                return $this->clearData($force, $clinicSlug);
            case 'reset':
                return $this->resetData($force, $clinicSlug);
            case 'status':
                return $this->showStatus();
            default:
                $this->error("Invalid action: {$action}");
                $this->info('Valid actions: clear, reset, status');
                return Command::FAILURE;
        }
    }

    private function clearData(bool $force, ?string $clinicSlug): int
    {
        if (!$force) {
            if (!$this->confirm('This will delete data. Are you sure?')) {
                $this->info('Operation cancelled.');
                return Command::SUCCESS;
            }
        }

        $this->info('🧹 Clearing MediNext EMR data...');

        try {
            if ($clinicSlug) {
                $this->clearClinicData($clinicSlug);
            } else {
                $this->clearAllData();
            }

            $this->info('✅ Data cleared successfully!');
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Failed to clear data: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    private function resetData(bool $force, ?string $clinicSlug): int
    {
        if (!$force) {
            if (!$this->confirm('This will reset data and reseed. Are you sure?')) {
                $this->info('Operation cancelled.');
                return Command::SUCCESS;
            }
        }

        $this->info('🔄 Resetting MediNext EMR data...');

        try {
            // Clear data first
            if ($clinicSlug) {
                $this->clearClinicData($clinicSlug);
            } else {
                $this->clearAllData();
            }

            // Reseed data
            $this->info('🌱 Reseeding data...');
            $seeder = new \Database\Seeders\BaseSeeder();
            $seeder->setCommand($this);
            $seeder->run();

            $this->info('✅ Data reset successfully!');
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Failed to reset data: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    private function showStatus(): int
    {
        $this->info('📊 MediNext EMR Data Status');
        $this->info('===========================');
        $this->newLine();

        // Clinics
        $clinicCount = Clinic::count();
        $this->info("🏥 Clinics: {$clinicCount}");
        if ($clinicCount > 0) {
            $clinics = Clinic::all();
            foreach ($clinics as $clinic) {
                $this->line("   • {$clinic->name} (ID: {$clinic->id}, Slug: {$clinic->slug})");
            }
        }
        $this->newLine();

        // Users
        $userCount = User::count();
        $this->info("👥 Users: {$userCount}");
        if ($userCount > 0) {
            $users = User::with('userClinicRoles.role')->get();
            foreach ($users as $user) {
                $roles = $user->userClinicRoles->pluck('role.name')->unique()->implode(', ');
                $this->line("   • {$user->name} ({$user->email}) - Roles: {$roles}");
            }
        }
        $this->newLine();

        // Patients
        $patientCount = Patient::count();
        $this->info("👤 Patients: {$patientCount}");
        $this->newLine();

        // Appointments
        $appointmentCount = Appointment::count();
        $this->info("📅 Appointments: {$appointmentCount}");
        $this->newLine();

        // Prescriptions
        $prescriptionCount = Prescription::count();
        $this->info("💊 Prescriptions: {$prescriptionCount}");
        $this->newLine();

        // Lab Results
        $labResultCount = LabResult::count();
        $this->info("🧪 Lab Results: {$labResultCount}");
        $this->newLine();

        // Bills
        $billCount = Bill::count();
        $this->info("💰 Bills: {$billCount}");
        $this->newLine();

        // Activity Logs
        $activityLogCount = ActivityLog::count();
        $this->info("📝 Activity Logs: {$activityLogCount}");
        $this->newLine();

        // Notifications
        $notificationCount = Notification::count();
        $this->info("🔔 Notifications: {$notificationCount}");
        $this->newLine();

        return Command::SUCCESS;
    }

    private function clearClinicData(string $clinicSlug): void
    {
        $clinic = Clinic::where('slug', $clinicSlug)->first();
        
        if (!$clinic) {
            throw new \Exception("Clinic with slug '{$clinicSlug}' not found.");
        }

        $this->info("Clearing data for clinic: {$clinic->name}");

        // Delete related data in correct order (respecting foreign key constraints)
        $this->info('Deleting activity logs...');
        ActivityLog::where('clinic_id', $clinic->id)->delete();
        
        $this->info('Deleting notifications...');
        Notification::whereHas('user', function($query) use ($clinic) {
            $query->whereHas('userClinicRoles', function($q) use ($clinic) {
                $q->where('clinic_id', $clinic->id);
            });
        })->delete();
        
        $this->info('Deleting settings...');
        Setting::where('clinic_id', $clinic->id)->delete();
        
        $this->info('Deleting queue patients...');
        QueuePatient::whereHas('queue', function($query) use ($clinic) {
            $query->where('clinic_id', $clinic->id);
        })->delete();
        
        $this->info('Deleting queues...');
        Queue::where('clinic_id', $clinic->id)->delete();
        
        $this->info('Deleting bills...');
        Bill::where('clinic_id', $clinic->id)->delete();
        
        $this->info('Deleting insurance records...');
        Insurance::whereHas('patient', function($query) use ($clinic) {
            $query->where('clinic_id', $clinic->id);
        })->delete();
        
        $this->info('Deleting appointments...');
        Appointment::where('clinic_id', $clinic->id)->delete();
        
        $this->info('Deleting encounters...');
        Encounter::where('clinic_id', $clinic->id)->delete();
        
        $this->info('Deleting prescriptions...');
        Prescription::where('clinic_id', $clinic->id)->delete();
        
        $this->info('Deleting lab results...');
        LabResult::where('clinic_id', $clinic->id)->delete();
        
        $this->info('Deleting patients...');
        Patient::where('clinic_id', $clinic->id)->delete();
        
        $this->info('Deleting doctors...');
        Doctor::where('clinic_id', $clinic->id)->delete();
        
        $this->info('Deleting rooms...');
        Room::where('clinic_id', $clinic->id)->delete();
        
        $this->info('Deleting clinic...');
        $clinic->delete();
    }

    private function clearAllData(): void
    {
        $this->info('Clearing all MediNext EMR data...');

        // Delete related data in correct order (respecting foreign key constraints)
        $this->info('Deleting activity logs...');
        ActivityLog::truncate();
        
        $this->info('Deleting notifications...');
        Notification::truncate();
        
        $this->info('Deleting settings...');
        Setting::truncate();
        
        $this->info('Deleting queue patients...');
        QueuePatient::truncate();
        
        $this->info('Deleting queues...');
        Queue::truncate();
        
        $this->info('Deleting bills...');
        Bill::truncate();
        
        $this->info('Deleting insurance records...');
        Insurance::truncate();
        
        $this->info('Deleting appointments...');
        Appointment::truncate();
        
        $this->info('Deleting encounters...');
        Encounter::truncate();
        
        $this->info('Deleting prescriptions...');
        Prescription::truncate();
        
        $this->info('Deleting lab results...');
        LabResult::truncate();
        
        $this->info('Deleting patients...');
        Patient::truncate();
        
        $this->info('Deleting doctors...');
        Doctor::truncate();
        
        $this->info('Deleting rooms...');
        Room::truncate();
        
        $this->info('Deleting clinics...');
        Clinic::truncate();
        
        $this->info('Deleting users...');
        User::truncate();
    }
}
