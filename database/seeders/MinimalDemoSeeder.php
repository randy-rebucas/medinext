<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Clinic;
use App\Models\User;
use App\Models\Role;
use App\Models\UserClinicRole;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Appointment;
use App\Models\Room;
use Carbon\Carbon;

class MinimalDemoSeeder extends Seeder
{
    private $demoClinic;
    private $demoUser;
    private $demoDoctor;
    private $demoPatient;
    private $demoRoom;

    public function run(): void
    {
        $this->command->info('🚀 Creating Minimal Demo Account...');
        
        // Create demo clinic
        $this->createDemoClinic();
        
        // Create demo user (admin)
        $this->createDemoUser();
        
        // Create demo doctor
        $this->createDemoDoctor();
        
        // Create demo room
        $this->createDemoRoom();
        
        // Create demo patient
        $this->createDemoPatient();
        
        // Create demo appointment
        $this->createDemoAppointment();
        
        $this->command->info('✅ Minimal demo account created successfully!');
        $this->displayDemoAccountInfo();
    }

    private function createDemoClinic(): void
    {
        $this->command->info('📋 Creating demo clinic...');
        
        $this->demoClinic = Clinic::create([
            'name' => 'Demo Medical Center',
            'slug' => 'demo-medical-center',
            'timezone' => 'Asia/Manila',
            'phone' => '+63 2 1234 5678',
            'email' => 'info@demomedical.com',
            'address' => [
                'street' => '123 Demo Street',
                'city' => 'Manila',
                'state' => 'Metro Manila',
                'postal_code' => '1000',
                'country' => 'Philippines'
            ],
            'settings' => [
                'working_hours' => [
                    'monday' => ['08:00', '17:00'],
                    'tuesday' => ['08:00', '17:00'],
                    'wednesday' => ['08:00', '17:00'],
                    'thursday' => ['08:00', '17:00'],
                    'friday' => ['08:00', '17:00'],
                    'saturday' => ['08:00', '12:00'],
                    'sunday' => ['closed']
                ]
            ]
        ]);
    }

    private function createDemoUser(): void
    {
        $this->command->info('👤 Creating demo admin user...');
        
        $this->demoUser = User::create([
            'name' => 'Demo Administrator',
            'email' => 'demo@medinext.com',
            'password' => bcrypt('demo123'),
            'phone' => '+63 912 345 6789',
            'is_active' => true,
            'trial_started_at' => now(),
            'trial_ends_at' => now()->addDays(14),
            'is_trial_user' => true,
            'has_activated_license' => false,
        ]);

        // Assign admin role
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        UserClinicRole::create([
            'user_id' => $this->demoUser->id,
            'clinic_id' => $this->demoClinic->id,
            'role_id' => $adminRole->id,
            'department' => 'Administration',
            'status' => 'Active',
            'join_date' => now()->subMonths(6),
        ]);
    }

    private function createDemoDoctor(): void
    {
        $this->command->info('👨‍⚕️ Creating demo doctor...');
        
        $doctorRole = Role::firstOrCreate(['name' => 'doctor']);

        $user = User::create([
            'name' => 'Dr. Maria Santos',
            'email' => 'doctor@demomedical.com',
            'password' => bcrypt('demo123'),
            'phone' => '+63 923 456 7890',
            'is_active' => true,
        ]);

        UserClinicRole::create([
            'user_id' => $user->id,
            'clinic_id' => $this->demoClinic->id,
            'role_id' => $doctorRole->id,
            'department' => 'Medical',
            'status' => 'Active',
            'join_date' => now()->subMonths(3),
        ]);

        $this->demoDoctor = Doctor::create([
            'user_id' => $user->id,
            'clinic_id' => $this->demoClinic->id,
            'specialty' => 'General Practice',
            'license_no' => 'MD-DEMO-001',
            'is_active' => true,
            'consultation_fee' => 1000,
        ]);
    }

    private function createDemoRoom(): void
    {
        $this->command->info('🏥 Creating demo room...');
        
        $this->demoRoom = Room::create([
            'clinic_id' => $this->demoClinic->id,
            'name' => 'Consultation Room 1',
            'type' => 'consultation',
            'capacity' => 2,
            'is_active' => true,
            'description' => 'Demo consultation room',
        ]);
    }

    private function createDemoPatient(): void
    {
        $this->command->info('👥 Creating demo patient...');
        
        $this->demoPatient = Patient::create([
            'clinic_id' => $this->demoClinic->id,
            'code' => 'DEMO-P0001',
            'first_name' => 'Maria',
            'last_name' => 'Santos',
            'dob' => '1985-03-15',
            'sex' => 'F',
            'contact' => [
                'phone' => '+63 912 345 6789',
                'email' => 'maria.santos@email.com',
                'address' => '123 Rizal Street, Manila'
            ],
            'allergies' => ['Penicillin'],
            'consents' => ['treatment', 'privacy', 'data_sharing']
        ]);
    }

    private function createDemoAppointment(): void
    {
        $this->command->info('📅 Creating demo appointment...');
        
        $startTime = Carbon::now()->addDays(1)->setTime(10, 0);
        $endTime = $startTime->copy()->addMinutes(30);

        Appointment::create([
            'clinic_id' => $this->demoClinic->id,
            'patient_id' => $this->demoPatient->id,
            'doctor_id' => $this->demoDoctor->id,
            'room_id' => $this->demoRoom->id,
            'start_at' => $startTime,
            'end_at' => $endTime,
            'status' => 'scheduled',
            'reason' => 'General Check-up',
            'source' => 'online',
            'notes' => 'Regular check-up appointment',
        ]);
    }

    private function displayDemoAccountInfo(): void
    {
        $this->command->info('');
        $this->command->info('🎉 MINIMAL DEMO ACCOUNT CREATED SUCCESSFULLY!');
        $this->command->info('===========================================');
        $this->command->info('');
        $this->command->info('📋 Demo Clinic: ' . $this->demoClinic->name);
        $this->command->info('🌐 Clinic ID: ' . $this->demoClinic->id);
        $this->command->info('');
        $this->command->info('👤 Demo Admin Account:');
        $this->command->info('   Email: demo@medinext.com');
        $this->command->info('   Password: demo123');
        $this->command->info('');
        $this->command->info('👨‍⚕️ Demo Doctor Account:');
        $this->command->info('   Email: doctor@demomedical.com');
        $this->command->info('   Password: demo123');
        $this->command->info('');
        $this->command->info('📊 Demo Data Created:');
        $this->command->info('   • 1 Patient (Maria Santos)');
        $this->command->info('   • 1 Doctor (Dr. Maria Santos)');
        $this->command->info('   • 1 Room (Consultation Room 1)');
        $this->command->info('   • 1 Appointment (Tomorrow at 10:00 AM)');
        $this->command->info('');
        $this->command->info('🚀 You can now log in and explore the demo environment!');
        $this->command->info('');
    }
}
