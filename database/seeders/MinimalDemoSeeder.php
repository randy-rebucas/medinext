<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Clinic;
use App\Models\User;
use App\Models\Role;
use App\Models\UserClinicRole;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Room;
use App\Models\Setting;

class MinimalDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🚀 Creating minimal demo data...');

        // Create demo clinic
        $clinic = Clinic::where('slug', 'demo-medical-center')->first();

        if (!$clinic) {
            $clinic = Clinic::create([
                'name' => 'Demo Medical Center',
                'slug' => 'demo-medical-center',
                'timezone' => 'Asia/Manila',
                'phone' => '+63 2 1234 5678',
                'email' => 'info@demomedical.com',
                'description' => 'A medical center for demonstration purposes',
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

        // Create demo admin user
        $user = User::where('email', 'demo@medinext.com')->first();

        if (!$user) {
            $user = User::create([
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
        }

        // Assign admin role
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $existingRole = UserClinicRole::where('user_id', $user->id)
            ->where('clinic_id', $clinic->id)
            ->where('role_id', $adminRole->id)
            ->first();

        if (!$existingRole) {
            UserClinicRole::create([
                'user_id' => $user->id,
                'clinic_id' => $clinic->id,
                'role_id' => $adminRole->id,
                'department' => 'Administration',
                'status' => 'Active',
                'join_date' => now()->subMonths(6),
            ]);
        }

        // Create demo doctor
        $doctorUser = User::where('email', 'doctor1@demomedical.com')->first();

        if (!$doctorUser) {
            $doctorUser = User::create([
                'name' => 'Dr. John Smith',
                'email' => 'doctor1@demomedical.com',
                'password' => bcrypt('demo123'),
                'phone' => '+63 923 456 7890',
                'is_active' => true,
            ]);
        }

        $doctorRole = Role::firstOrCreate(['name' => 'doctor']);
        $existingDoctorRole = UserClinicRole::where('user_id', $doctorUser->id)
            ->where('clinic_id', $clinic->id)
            ->where('role_id', $doctorRole->id)
            ->first();

        if (!$existingDoctorRole) {
            UserClinicRole::create([
                'user_id' => $doctorUser->id,
                'clinic_id' => $clinic->id,
                'role_id' => $doctorRole->id,
                'department' => 'Medical',
                'status' => 'Active',
                'join_date' => now()->subMonths(3),
            ]);
        }

        $doctor = Doctor::where('user_id', $doctorUser->id)
            ->where('clinic_id', $clinic->id)
            ->first();

        if (!$doctor) {
            Doctor::create([
                'user_id' => $doctorUser->id,
                'clinic_id' => $clinic->id,
                'specialty' => 'General Practice',
                'license_no' => 'MD-DEMO-001',
                'is_active' => true,
                'consultation_fee' => 1000,
            ]);
        }

        // Create demo room
        $room = Room::where('clinic_id', $clinic->id)
            ->where('name', 'Consultation Room 1')
            ->first();

        if (!$room) {
            Room::create([
                'clinic_id' => $clinic->id,
                'name' => 'Consultation Room 1',
                'type' => 'consultation',
            ]);
        }

        // Create demo patient
        $patient = Patient::where('clinic_id', $clinic->id)
            ->where('code', 'DEMO-P0001')
            ->first();

        if (!$patient) {
            Patient::create([
                'clinic_id' => $clinic->id,
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
                'consents' => ['treatment', 'privacy']
            ]);
        }

        // Create demo settings
        $settings = [
            [
                'key' => 'clinic_name',
                'value' => 'Demo Medical Center',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Name of the clinic'
            ],
            [
                'key' => 'appointment_duration',
                'value' => '30',
                'type' => 'integer',
                'group' => 'clinic',
                'description' => 'Default appointment duration in minutes'
            ]
        ];

        foreach ($settings as $settingData) {
            $existingSetting = Setting::where('clinic_id', $clinic->id)
                ->where('key', $settingData['key'])
                ->first();

            if (!$existingSetting) {
                Setting::create([
                    'clinic_id' => $clinic->id,
                    'key' => $settingData['key'],
                    'value' => $settingData['value'],
                    'type' => $settingData['type'],
                    'group' => $settingData['group'],
                    'description' => $settingData['description'],
                    'is_public' => false,
                ]);
            }
        }

        $this->command->info('✅ Minimal demo data created successfully!');
        $this->command->info('');
        $this->command->info('📋 Demo Clinic: ' . $clinic->name);
        $this->command->info('🌐 Clinic ID: ' . $clinic->id);
        $this->command->info('');
        $this->command->info('👤 Demo Admin Account:');
        $this->command->info('   Email: demo@medinext.com');
        $this->command->info('   Password: demo123');
        $this->command->info('');
        $this->command->info('👨‍⚕️ Demo Doctor Account:');
        $this->command->info('   Email: doctor1@demomedical.com');
        $this->command->info('   Password: demo123');
        $this->command->info('');
        $this->command->info('🚀 You can now log in and explore the demo environment!');
    }
}
