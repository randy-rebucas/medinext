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
use App\Models\Prescription;
use App\Models\Encounter;
use App\Models\Room;
use App\Models\LabResult;
use App\Models\Bill;
use App\Models\BillItem;
use App\Models\Insurance;
use App\Models\Queue;
use App\Models\QueuePatient;
use App\Models\ActivityLog;
use App\Models\Notification;
use App\Models\Setting;
use Carbon\Carbon;
use Faker\Factory as Faker;

class SimpleDemoSeeder extends Seeder
{
    private $faker;
    private $demoClinic;
    private $demoUser;
    private $demoDoctors = [];
    private $demoPatients = [];
    private $demoRooms = [];

    public function run(): void
    {
        $this->faker = Faker::create();

        $this->command->info('🚀 Creating Simple Demo Account...');

        // Create demo clinic
        $this->createDemoClinic();

        // Create demo user (admin)
        $this->createDemoUser();

        // Create demo staff (doctors, receptionist)
        $this->createDemoStaff();

        // Create demo infrastructure (rooms)
        $this->createDemoRooms();

        // Create demo patients
        $this->createDemoPatients();

        // Create demo appointments
        $this->createDemoAppointments();

        // Create demo encounters
        $this->createDemoEncounters();

        // Create demo prescriptions
        $this->createDemoPrescriptions();

        // Create demo lab results
        $this->createDemoLabResults();

        // Create demo bills
        $this->createDemoBills();

        // Create demo insurance records
        $this->createDemoInsurance();

        // Create demo queue data
        $this->createDemoQueue();

        // Create demo notifications
        $this->createDemoNotifications();

        // Create demo activity logs
        $this->createDemoActivityLogs();

        // Create demo settings
        $this->createDemoSettings();

        $this->command->info('✅ Simple demo account created successfully!');
        $this->displayDemoAccountInfo();
    }

    private function createDemoClinic(): void
    {
        $this->command->info('📋 Creating demo clinic...');

        $this->demoClinic = Clinic::create([
            'name' => 'Demo Medical Center',
            'slug' => 'demo-medical-center',
            'timezone' => 'Asia/Manila',
            'logo_url' => null,
            'phone' => '+63 2 1234 5678',
            'email' => 'info@demomedical.com',
            'website' => 'https://demomedical.com',
            'description' => 'A comprehensive medical center for demonstration purposes',
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
                ],
                'appointment_duration' => 30,
                'max_appointments_per_day' => 50,
                'allow_online_booking' => true,
                'require_patient_verification' => true
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

    private function createDemoStaff(): void
    {
        $this->command->info('👨‍⚕️ Creating demo staff...');

        $doctorRole = Role::firstOrCreate(['name' => 'doctor']);
        $receptionistRole = Role::firstOrCreate(['name' => 'receptionist']);

        $specialties = [
            'General Practice',
            'Cardiology',
            'Pediatrics',
            'Internal Medicine',
            'Dermatology'
        ];

        // Create 3 demo doctors (reduced from 5)
        for ($i = 1; $i <= 3; $i++) {
            $user = User::create([
                'name' => $this->faker->name(),
                'email' => "doctor{$i}@demomedical.com",
                'password' => bcrypt('demo123'),
                'phone' => $this->faker->phoneNumber(),
                'is_active' => true,
            ]);

            UserClinicRole::create([
                'user_id' => $user->id,
                'clinic_id' => $this->demoClinic->id,
                'role_id' => $doctorRole->id,
                'department' => 'Medical',
                'status' => 'Active',
                'join_date' => now()->subMonths(rand(1, 12)),
            ]);

            $doctor = Doctor::create([
                'user_id' => $user->id,
                'clinic_id' => $this->demoClinic->id,
                'specialty' => $specialties[array_rand($specialties)],
                'license_no' => 'MD-DEMO-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'is_active' => true,
                'consultation_fee' => rand(500, 2000),
                'availability_schedule' => [
                    'monday' => ['08:00', '17:00'],
                    'tuesday' => ['08:00', '17:00'],
                    'wednesday' => ['08:00', '17:00'],
                    'thursday' => ['08:00', '17:00'],
                    'friday' => ['08:00', '17:00'],
                    'saturday' => ['08:00', '12:00'],
                    'sunday' => ['closed']
                ]
            ]);

            $this->demoDoctors[] = $doctor;
        }

        // Create receptionist
        $receptionist = User::create([
            'name' => 'Demo Receptionist',
            'email' => 'receptionist@demomedical.com',
            'password' => bcrypt('demo123'),
            'phone' => '+63 923 456 7890',
            'is_active' => true,
        ]);

        UserClinicRole::create([
            'user_id' => $receptionist->id,
            'clinic_id' => $this->demoClinic->id,
            'role_id' => $receptionistRole->id,
            'department' => 'Front Office',
            'status' => 'Active',
            'join_date' => now()->subMonths(3),
        ]);
    }

    private function createDemoRooms(): void
    {
        $this->command->info('🏥 Creating demo rooms...');

        $roomTypes = [
            ['name' => 'Consultation Room 1', 'type' => 'consultation', 'capacity' => 2],
            ['name' => 'Consultation Room 2', 'type' => 'consultation', 'capacity' => 2],
            ['name' => 'Examination Room 1', 'type' => 'examination', 'capacity' => 3],
            ['name' => 'Waiting Area', 'type' => 'waiting', 'capacity' => 20],
        ];

        foreach ($roomTypes as $roomData) {
            $room = Room::create([
                'clinic_id' => $this->demoClinic->id,
                'name' => $roomData['name'],
                'type' => $roomData['type'],
                'capacity' => $roomData['capacity'],
                'is_active' => true,
                'description' => "Demo {$roomData['type']} room",
            ]);

            $this->demoRooms[] = $room;
        }
    }

    private function createDemoPatients(): void
    {
        $this->command->info('👥 Creating demo patients...');

        $samplePatients = [
            [
                'first_name' => 'Maria',
                'last_name' => 'Santos',
                'dob' => '1985-03-15',
                'sex' => 'F',
                'contact' => [
                    'phone' => '+63 912 345 6789',
                    'email' => 'maria.santos@email.com',
                    'address' => '123 Rizal Street, Manila'
                ],
                'allergies' => ['Penicillin', 'Sulfa drugs'],
                'consents' => ['treatment', 'privacy', 'data_sharing']
            ],
            [
                'first_name' => 'Juan',
                'last_name' => 'Dela Cruz',
                'dob' => '1990-07-22',
                'sex' => 'M',
                'contact' => [
                    'phone' => '+63 923 456 7890',
                    'email' => 'juan.delacruz@email.com',
                    'address' => '456 Bonifacio Avenue, Quezon City'
                ],
                'allergies' => ['None'],
                'consents' => ['treatment', 'privacy', 'data_sharing']
            ],
            [
                'first_name' => 'Ana',
                'last_name' => 'Garcia',
                'dob' => '1978-11-08',
                'sex' => 'F',
                'contact' => [
                    'phone' => '+63 934 567 8901',
                    'email' => 'ana.garcia@email.com',
                    'address' => '789 Mabini Street, Makati'
                ],
                'allergies' => ['Latex', 'Shellfish'],
                'consents' => ['treatment', 'privacy']
            ],
            [
                'first_name' => 'Pedro',
                'last_name' => 'Martinez',
                'dob' => '1992-04-30',
                'sex' => 'M',
                'contact' => [
                    'phone' => '+63 945 678 9012',
                    'email' => 'pedro.martinez@email.com',
                    'address' => '321 Aguinaldo Road, Taguig'
                ],
                'allergies' => ['Aspirin'],
                'consents' => ['treatment', 'privacy', 'data_sharing', 'research']
            ],
            [
                'first_name' => 'Sofia',
                'last_name' => 'Reyes',
                'dob' => '1982-09-14',
                'sex' => 'F',
                'contact' => [
                    'phone' => '+63 956 789 0123',
                    'email' => 'sofia.reyes@email.com',
                    'address' => '654 Roxas Boulevard, Pasay'
                ],
                'allergies' => ['None'],
                'consents' => ['treatment', 'privacy']
            ]
        ];

        foreach ($samplePatients as $index => $patientData) {
            $patient = Patient::create([
                'clinic_id' => $this->demoClinic->id,
                'code' => 'DEMO-P' . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                'first_name' => $patientData['first_name'],
                'last_name' => $patientData['last_name'],
                'dob' => $patientData['dob'],
                'sex' => $patientData['sex'],
                'contact' => $patientData['contact'],
                'allergies' => $patientData['allergies'],
                'consents' => $patientData['consents'],
            ]);

            $this->demoPatients[] = $patient;
        }
    }

    private function createDemoAppointments(): void
    {
        $this->command->info('📅 Creating demo appointments...');

        $statuses = ['scheduled', 'confirmed', 'in_progress', 'completed', 'cancelled'];
        $reasons = [
            'General Check-up',
            'Follow-up Consultation',
            'Symptom Evaluation',
            'Prescription Renewal',
            'Lab Results Review'
        ];

        // Create appointments for the past 3 days and next 3 days (reduced)
        for ($day = -3; $day <= 3; $day++) {
            $date = Carbon::now()->addDays($day);

            // Skip weekends
            if ($date->isWeekend()) {
                continue;
            }

            // Create 1-2 appointments per day (reduced)
            $appointmentsPerDay = rand(1, 2);

            for ($i = 0; $i < $appointmentsPerDay; $i++) {
                $patient = $this->demoPatients[array_rand($this->demoPatients)];
                $doctor = $this->demoDoctors[array_rand($this->demoDoctors)];
                $room = $this->demoRooms[array_rand($this->demoRooms)];

                $hour = rand(8, 16);
                $minute = rand(0, 3) * 15;
                $startTime = $date->copy()->setTime($hour, $minute);
                $endTime = $startTime->copy()->addMinutes(30);

                $status = 'scheduled';
                if ($date->isPast()) {
                    $status = $statuses[array_rand($statuses)];
                }

                Appointment::create([
                    'clinic_id' => $this->demoClinic->id,
                    'patient_id' => $patient->id,
                    'doctor_id' => $doctor->id,
                    'room_id' => $room->id,
                    'start_at' => $startTime,
                    'end_at' => $endTime,
                    'status' => $status,
                    'reason' => $reasons[array_rand($reasons)],
                    'source' => ['walk_in', 'phone', 'online'][array_rand(['walk_in', 'phone', 'online'])],
                    'notes' => $this->faker->optional(0.3)->sentence(),
                ]);
            }
        }
    }

    private function createDemoEncounters(): void
    {
        $this->command->info('🩺 Creating demo encounters...');

        $appointments = Appointment::where('clinic_id', $this->demoClinic->id)
            ->where('status', 'completed')
            ->get();

        foreach ($appointments as $appointment) {
            Encounter::create([
                'clinic_id' => $this->demoClinic->id,
                'patient_id' => $appointment->patient_id,
                'doctor_id' => $appointment->doctor_id,
                'appointment_id' => $appointment->id,
                'encounter_type' => 'consultation',
                'chief_complaint' => $this->faker->sentence(6),
                'history_of_present_illness' => $this->faker->paragraph(3),
                'physical_examination' => $this->faker->paragraph(2),
                'assessment' => $this->faker->sentence(8),
                'plan' => $this->faker->paragraph(2),
                'vital_signs' => [
                    'blood_pressure' => rand(90, 140) . '/' . rand(60, 90),
                    'heart_rate' => rand(60, 100),
                    'temperature' => rand(36, 38) . '.' . rand(0, 9),
                    'respiratory_rate' => rand(12, 20),
                    'oxygen_saturation' => rand(95, 100)
                ],
                'encounter_date' => $appointment->start_at,
                'status' => 'completed'
            ]);
        }
    }

    private function createDemoPrescriptions(): void
    {
        $this->command->info('💊 Creating demo prescriptions...');

        $encounters = Encounter::where('clinic_id', $this->demoClinic->id)->get();

        foreach ($encounters as $encounter) {
            if (rand(0, 1)) { // 50% chance of prescription
                $qrHash = 'RX-DEMO-' . strtoupper(uniqid()) . '-' . $encounter->patient_id;

                Prescription::create([
                    'clinic_id' => $this->demoClinic->id,
                    'patient_id' => $encounter->patient_id,
                    'doctor_id' => $encounter->doctor_id,
                    'encounter_id' => $encounter->id,
                    'issued_at' => $encounter->encounter_date,
                    'status' => ['active', 'completed'][array_rand(['active', 'completed'])],
                    'qr_hash' => $qrHash,
                    'notes' => $this->faker->optional(0.4)->sentence(),
                ]);
            }
        }
    }

    private function createDemoLabResults(): void
    {
        $this->command->info('🧪 Creating demo lab results...');

        $testTypes = [
            'Complete Blood Count',
            'Blood Chemistry',
            'Urinalysis',
            'Lipid Profile',
            'Thyroid Function Test'
        ];

        foreach ($this->demoPatients as $patient) {
            // Create 1 lab result per patient (reduced)
            LabResult::create([
                'clinic_id' => $this->demoClinic->id,
                'patient_id' => $patient->id,
                'test_name' => $testTypes[array_rand($testTypes)],
                'test_type' => 'laboratory',
                'result_value' => $this->faker->randomFloat(2, 1, 100),
                'unit' => 'mg/dL',
                'reference_range' => 'Normal: 0-100',
                'status' => ['completed', 'abnormal', 'critical'][array_rand(['completed', 'abnormal', 'critical'])],
                'ordered_at' => Carbon::now()->subDays(rand(1, 30)),
                'completed_at' => Carbon::now()->subDays(rand(1, 30)),
                'notes' => $this->faker->optional(0.3)->sentence(),
            ]);
        }
    }

    private function createDemoBills(): void
    {
        $this->command->info('💰 Creating demo bills...');

        $appointments = Appointment::where('clinic_id', $this->demoClinic->id)
            ->where('status', 'completed')
            ->get();

        foreach ($appointments as $appointment) {
            if (rand(0, 1)) { // 50% chance of bill
                $bill = Bill::create([
                    'clinic_id' => $this->demoClinic->id,
                    'patient_id' => $appointment->patient_id,
                    'appointment_id' => $appointment->id,
                    'bill_number' => 'BILL-DEMO-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
                    'bill_date' => $appointment->start_at,
                    'due_date' => $appointment->start_at->addDays(30),
                    'subtotal' => rand(500, 2000),
                    'tax_amount' => 0,
                    'discount_amount' => rand(0, 200),
                    'total_amount' => rand(500, 2000),
                    'status' => ['pending', 'paid', 'overdue'][array_rand(['pending', 'paid', 'overdue'])],
                    'payment_method' => ['cash', 'card', 'insurance'][array_rand(['cash', 'card', 'insurance'])],
                    'created_by' => $this->demoUser->id,
                ]);

                // Create 1 bill item per bill (reduced)
                BillItem::create([
                    'bill_id' => $bill->id,
                    'description' => ['Consultation Fee', 'Lab Test', 'Medication'][array_rand(['Consultation Fee', 'Lab Test', 'Medication'])],
                    'quantity' => 1,
                    'unit_price' => rand(100, 800),
                    'total_price' => rand(100, 800),
                    'created_by' => $this->demoUser->id,
                ]);
            }
        }
    }

    private function createDemoInsurance(): void
    {
        $this->command->info('🏥 Creating demo insurance records...');

        $insuranceProviders = [
            'PhilHealth',
            'Maxicare',
            'Intellicare',
            'Medicard'
        ];

        foreach ($this->demoPatients as $patient) {
            if (rand(0, 1)) { // 50% chance of insurance
                Insurance::create([
                    'patient_id' => $patient->id,
                    'insurance_provider' => $insuranceProviders[array_rand($insuranceProviders)],
                    'policy_number' => 'POL-' . strtoupper(uniqid()),
                    'member_id' => 'MEM-' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT),
                    'group_number' => 'GRP-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
                    'policy_holder_name' => $patient->first_name . ' ' . $patient->last_name,
                    'policy_holder_relationship' => 'self',
                    'coverage_type' => 'health',
                    'effective_date' => Carbon::now()->subMonths(rand(1, 24)),
                    'expiration_date' => Carbon::now()->addMonths(rand(6, 12)),
                    'copay_amount' => rand(50, 500),
                    'deductible_amount' => rand(1000, 5000),
                    'coverage_percentage' => 100.00,
                    'is_primary' => true,
                    'is_active' => true,
                    'verification_status' => 'verified',
                    'created_by' => $this->demoUser->id,
                ]);
            }
        }
    }

    private function createDemoQueue(): void
    {
        $this->command->info('📋 Creating demo queue data...');

        $queue = Queue::create([
            'clinic_id' => $this->demoClinic->id,
            'name' => 'General Consultation Queue',
            'description' => 'Main queue for general consultations',
            'is_active' => true,
            'created_by' => $this->demoUser->id,
        ]);

        // Add some patients to the queue
        $patients = array_slice($this->demoPatients, 0, 2); // Reduced from 3
        foreach ($patients as $index => $patient) {
            QueuePatient::create([
                'queue_id' => $queue->id,
                'patient_id' => $patient->id,
                'queue_number' => $index + 1,
                'status' => ['waiting', 'in_progress', 'completed'][array_rand(['waiting', 'in_progress', 'completed'])],
                'priority' => ['normal', 'urgent'][array_rand(['normal', 'urgent'])],
                'notes' => $this->faker->optional(0.3)->sentence(),
                'created_by' => $this->demoUser->id,
            ]);
        }
    }

    private function createDemoNotifications(): void
    {
        $this->command->info('🔔 Creating demo notifications...');

        $notifications = [
            [
                'title' => 'Welcome to Demo Medical Center',
                'message' => 'Your demo account has been set up successfully. Explore all the features!',
                'type' => 'info',
                'priority' => 'normal'
            ],
            [
                'title' => 'New Patient Registration',
                'message' => 'A new patient has been registered in the system.',
                'type' => 'success',
                'priority' => 'normal'
            ],
            [
                'title' => 'Appointment Reminder',
                'message' => 'You have upcoming appointments today.',
                'type' => 'warning',
                'priority' => 'high'
            ]
        ];

        foreach ($notifications as $notificationData) {
            Notification::create([
                'clinic_id' => $this->demoClinic->id,
                'user_id' => $this->demoUser->id,
                'title' => $notificationData['title'],
                'message' => $notificationData['message'],
                'type' => $notificationData['type'],
                'priority' => $notificationData['priority'],
                'is_read' => false,
                'created_by' => $this->demoUser->id,
            ]);
        }
    }

    private function createDemoActivityLogs(): void
    {
        $this->command->info('📊 Creating demo activity logs...');

        $entities = [
            'Patient' => $this->demoPatients,
            'Doctor' => $this->demoDoctors,
            'Appointment' => Appointment::where('clinic_id', $this->demoClinic->id)->get(),
            'Prescription' => Prescription::where('clinic_id', $this->demoClinic->id)->get(),
        ];

        $actions = [
            'created', 'updated', 'viewed', 'scheduled', 'completed', 'cancelled',
            'logged_in', 'logged_out'
        ];

        // Generate activity logs for the past 3 days (reduced)
        for ($day = 0; $day < 3; $day++) {
            $date = Carbon::now()->subDays($day);
            $logsPerDay = rand(3, 8); // Reduced

            for ($i = 0; $i < $logsPerDay; $i++) {
                $entityType = array_rand($entities);
                $entityCollection = $entities[$entityType];

                if ($entityCollection->isEmpty()) {
                    continue;
                }

                $entity = $entityCollection->random();
                $action = $actions[array_rand($actions)];
                $timestamp = $date->copy()->addHours(rand(0, 23))->addMinutes(rand(0, 59));

                ActivityLog::create([
                    'clinic_id' => $this->demoClinic->id,
                    'actor_user_id' => $this->demoUser->id,
                    'entity' => $entityType,
                    'entity_id' => $entity->id,
                    'action' => $action,
                    'at' => $timestamp,
                    'ip' => $this->faker->ipv4(),
                    'meta' => [
                        'user_agent' => $this->faker->userAgent(),
                        'session_id' => 'demo_session_' . uniqid(),
                        'request_method' => ['GET', 'POST', 'PUT', 'DELETE'][array_rand(['GET', 'POST', 'PUT', 'DELETE'])],
                        'url' => 'https://demo.medinext.com/' . strtolower($entityType) . 's/' . $entity->id
                    ],
                    'before_hash' => md5(uniqid()),
                    'after_hash' => md5(uniqid())
                ]);
            }
        }
    }

    private function createDemoSettings(): void
    {
        $this->command->info('⚙️ Creating demo settings...');

        $settings = [
            [
                'key' => 'clinic_name',
                'value' => 'Demo Medical Center',
                'type' => 'string',
                'description' => 'Name of the clinic'
            ],
            [
                'key' => 'appointment_duration',
                'value' => '30',
                'type' => 'integer',
                'description' => 'Default appointment duration in minutes'
            ],
            [
                'key' => 'allow_online_booking',
                'value' => 'true',
                'type' => 'boolean',
                'description' => 'Allow patients to book appointments online'
            ],
            [
                'key' => 'require_patient_verification',
                'value' => 'true',
                'type' => 'boolean',
                'description' => 'Require patient verification for appointments'
            ],
            [
                'key' => 'max_appointments_per_day',
                'value' => '50',
                'type' => 'integer',
                'description' => 'Maximum number of appointments per day'
            ]
        ];

        foreach ($settings as $settingData) {
            Setting::create([
                'clinic_id' => $this->demoClinic->id,
                'key' => $settingData['key'],
                'value' => $settingData['value'],
                'type' => $settingData['type'],
                'description' => $settingData['description'],
                'is_active' => true,
            ]);
        }
    }

    private function displayDemoAccountInfo(): void
    {
        $this->command->info('');
        $this->command->info('🎉 SIMPLE DEMO ACCOUNT CREATED SUCCESSFULLY!');
        $this->command->info('==========================================');
        $this->command->info('');
        $this->command->info('📋 Demo Clinic: ' . $this->demoClinic->name);
        $this->command->info('🌐 Clinic ID: ' . $this->demoClinic->id);
        $this->command->info('');
        $this->command->info('👤 Demo Admin Account:');
        $this->command->info('   Email: demo@medinext.com');
        $this->command->info('   Password: demo123');
        $this->command->info('');
        $this->command->info('👨‍⚕️ Demo Staff Accounts:');
        $this->command->info('   Doctor 1: doctor1@demomedical.com (demo123)');
        $this->command->info('   Doctor 2: doctor2@demomedical.com (demo123)');
        $this->command->info('   Doctor 3: doctor3@demomedical.com (demo123)');
        $this->command->info('   Receptionist: receptionist@demomedical.com (demo123)');
        $this->command->info('');
        $this->command->info('📊 Demo Data Created:');
        $this->command->info('   • ' . count($this->demoPatients) . ' Patients');
        $this->command->info('   • ' . count($this->demoDoctors) . ' Doctors');
        $this->command->info('   • ' . count($this->demoRooms) . ' Rooms');
        $this->command->info('   • ' . Appointment::where('clinic_id', $this->demoClinic->id)->count() . ' Appointments');
        $this->command->info('   • ' . Encounter::where('clinic_id', $this->demoClinic->id)->count() . ' Encounters');
        $this->command->info('   • ' . Prescription::where('clinic_id', $this->demoClinic->id)->count() . ' Prescriptions');
        $this->command->info('   • ' . LabResult::where('clinic_id', $this->demoClinic->id)->count() . ' Lab Results');
        $this->command->info('   • ' . Bill::where('clinic_id', $this->demoClinic->id)->count() . ' Bills');
        $this->command->info('   • ' . Insurance::where('patient_id', '>', 0)->count() . ' Insurance Records');
        $this->command->info('   • ' . ActivityLog::where('clinic_id', $this->demoClinic->id)->count() . ' Activity Logs');
        $this->command->info('');
        $this->command->info('🚀 You can now log in and explore the demo environment!');
        $this->command->info('');
    }
}
