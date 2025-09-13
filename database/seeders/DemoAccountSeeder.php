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
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class DemoAccountSeeder extends Seeder
{
    private $faker;
    private $demoClinic;
    private $demoUser;
    private $demoDoctors = [];
    private $demoPatients = [];
    private $demoRooms = [];

    public function run(): void
    {
        try {
            $this->faker = Faker::create();

            $this->command->info('🚀 Starting Demo Account Setup...');
            $this->command->info('=====================================');

            // Use database transactions for better performance and rollback capability
            DB::transaction(function () {
                $this->createDemoClinic();
                $this->createDemoUser();
                $this->createDemoStaff();
                $this->createDemoRooms();
                $this->createDemoPatients();
                $this->createDemoAppointments();
                $this->createDemoEncounters();
                $this->createDemoPrescriptions();
                $this->createDemoLabResults();
                $this->createDemoBills();
                $this->createDemoInsurance();
                $this->createDemoQueue();
                $this->createDemoNotifications();
                $this->createDemoActivityLogs();
                $this->createDemoSettings();
            });

            $this->command->info('✅ Demo account setup completed successfully!');
            $this->displayDemoAccountInfo();

        } catch (\Exception $e) {
            $this->command->error('❌ Demo account setup failed: ' . $e->getMessage());
            throw $e;
        }
    }

    private function createDemoClinic(): void
    {
        $this->command->info('📋 Setting up demo clinic...');

        $this->demoClinic = Clinic::where('slug', 'demo-medical-center')->first();

        if (!$this->demoClinic) {
            $this->demoClinic = Clinic::create([
                'name' => 'Demo Medical Center',
                'slug' => 'demo-medical-center',
                'timezone' => 'Asia/Manila',
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
            $this->command->info('   ✓ Demo clinic created');
        } else {
            $this->command->info('   ✓ Demo clinic already exists');
        }
    }

    private function createDemoUser(): void
    {
        $this->command->info('👤 Setting up demo admin user...');

        $this->demoUser = User::where('email', 'demo@medinext.com')->first();

        if (!$this->demoUser) {
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
            $this->command->info('   ✓ Demo admin user created');
        } else {
            $this->command->info('   ✓ Demo admin user already exists');
        }

        // Assign admin role
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $existingRole = UserClinicRole::where('user_id', $this->demoUser->id)
            ->where('clinic_id', $this->demoClinic->id)
            ->where('role_id', $adminRole->id)
            ->first();

        if (!$existingRole) {
            UserClinicRole::create([
                'user_id' => $this->demoUser->id,
                'clinic_id' => $this->demoClinic->id,
                'role_id' => $adminRole->id,
                'department' => 'Administration',
                'status' => 'Active',
                'join_date' => now()->subMonths(6),
            ]);
            $this->command->info('   ✓ Admin role assigned');
        }
    }

    private function createDemoStaff(): void
    {
        $this->command->info('👨‍⚕️ Setting up demo staff...');

        $doctorRole = Role::firstOrCreate(['name' => 'doctor']);
        $receptionistRole = Role::firstOrCreate(['name' => 'receptionist']);

        $specialties = ['General Practice', 'Cardiology', 'Pediatrics'];

        // Create 3 demo doctors
        for ($i = 1; $i <= 3; $i++) {
            $email = "doctor{$i}@demomedical.com";
            $user = User::where('email', $email)->first();

            if (!$user) {
                $user = User::create([
                    'name' => "Dr. {$this->faker->firstName()} {$this->faker->lastName()}",
                    'email' => $email,
                    'password' => bcrypt('demo123'),
                    'phone' => '+63 9' . rand(10, 99) . ' ' . rand(100, 999) . ' ' . rand(1000, 9999),
                    'is_active' => true,
                ]);
            }

            $existingRole = UserClinicRole::where('user_id', $user->id)
                ->where('clinic_id', $this->demoClinic->id)
                ->where('role_id', $doctorRole->id)
                ->first();

            if (!$existingRole) {
                UserClinicRole::create([
                    'user_id' => $user->id,
                    'clinic_id' => $this->demoClinic->id,
                    'role_id' => $doctorRole->id,
                    'department' => 'Medical',
                    'status' => 'Active',
                    'join_date' => now()->subMonths(rand(1, 12)),
                ]);
            }

            $doctor = Doctor::where('user_id', $user->id)
                ->where('clinic_id', $this->demoClinic->id)
                ->first();

            if (!$doctor) {
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
            }

            $this->demoDoctors[] = $doctor;
        }

        // Create receptionist
        $receptionist = User::where('email', 'receptionist@demomedical.com')->first();

        if (!$receptionist) {
            $receptionist = User::create([
                'name' => 'Demo Receptionist',
                'email' => 'receptionist@demomedical.com',
                'password' => bcrypt('demo123'),
                'phone' => '+63 923 456 7890',
                'is_active' => true,
            ]);
        }

        $existingReceptionistRole = UserClinicRole::where('user_id', $receptionist->id)
            ->where('clinic_id', $this->demoClinic->id)
            ->where('role_id', $receptionistRole->id)
            ->first();

        if (!$existingReceptionistRole) {
            UserClinicRole::create([
                'user_id' => $receptionist->id,
                'clinic_id' => $this->demoClinic->id,
                'role_id' => $receptionistRole->id,
                'department' => 'Front Office',
                'status' => 'Active',
                'join_date' => now()->subMonths(3),
            ]);
        }

        $this->command->info('   ✓ Demo staff created');
    }

    private function createDemoRooms(): void
    {
        $this->command->info('🏥 Setting up demo rooms...');

        $roomTypes = [
            ['name' => 'Consultation Room 1', 'type' => 'consultation'],
            ['name' => 'Consultation Room 2', 'type' => 'consultation'],
            ['name' => 'Examination Room 1', 'type' => 'examination'],
        ];

        foreach ($roomTypes as $roomData) {
            $room = Room::where('clinic_id', $this->demoClinic->id)
                ->where('name', $roomData['name'])
                ->first();

            if (!$room) {
                $room = Room::create([
                    'clinic_id' => $this->demoClinic->id,
                    'name' => $roomData['name'],
                    'type' => $roomData['type'],
                ]);
            }

            $this->demoRooms[] = $room;
        }

        $this->command->info('   ✓ Demo rooms created');
    }

    private function createDemoPatients(): void
    {
        $this->command->info('👥 Setting up demo patients...');

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
            ]
        ];

        foreach ($samplePatients as $index => $patientData) {
            $patient = Patient::where('clinic_id', $this->demoClinic->id)
                ->where('code', 'DEMO-P' . str_pad($index + 1, 4, '0', STR_PAD_LEFT))
                ->first();

            if (!$patient) {
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
            }

            $this->demoPatients[] = $patient;
        }

        $this->command->info('   ✓ Demo patients created');
    }

    private function createDemoAppointments(): void
    {
        $this->command->info('📅 Setting up demo appointments...');

        $reasons = [
            'General Check-up',
            'Follow-up Consultation',
            'Symptom Evaluation',
            'Prescription Renewal'
        ];

        // Create appointments for the past 3 days and next 3 days
        $appointmentCount = 0;
        for ($day = -3; $day <= 3; $day++) {
            $date = Carbon::now()->addDays($day);

            // Skip weekends
            if ($date->isWeekend()) {
                continue;
            }

            // Create 1-2 appointments per day
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
                    $status = ['scheduled', 'confirmed', 'completed'][array_rand(['scheduled', 'confirmed', 'completed'])];
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

                $appointmentCount++;
            }
        }

        $this->command->info("   ✓ {$appointmentCount} demo appointments created");
    }

    private function createDemoEncounters(): void
    {
        $this->command->info('🩺 Setting up demo encounters...');

        $appointments = Appointment::where('clinic_id', $this->demoClinic->id)
            ->where('status', 'completed')
            ->get();

        $encounterCount = 0;
        foreach ($appointments as $appointment) {
            $encounter = Encounter::where('clinic_id', $this->demoClinic->id)
                ->where('patient_id', $appointment->patient_id)
                ->where('doctor_id', $appointment->doctor_id)
                ->where('date', $appointment->start_at)
                ->first();

            if (!$encounter) {
                Encounter::create([
                    'clinic_id' => $this->demoClinic->id,
                    'patient_id' => $appointment->patient_id,
                    'doctor_id' => $appointment->doctor_id,
                    'date' => $appointment->start_at,
                    'type' => 'consultation',
                    'status' => 'completed',
                    'chief_complaint' => $this->faker->sentence(6),
                    'assessment' => $this->faker->sentence(8),
                    'plan' => $this->faker->paragraph(2),
                    'vitals' => [
                        'blood_pressure' => rand(90, 140) . '/' . rand(60, 90),
                        'heart_rate' => rand(60, 100),
                        'temperature' => rand(36, 38) . '.' . rand(0, 9),
                        'respiratory_rate' => rand(12, 20),
                        'oxygen_saturation' => rand(95, 100)
                    ],
                    'notes_soap' => [
                        'subjective' => $this->faker->paragraph(3),
                        'objective' => $this->faker->paragraph(2),
                        'assessment' => $this->faker->sentence(8),
                        'plan' => $this->faker->paragraph(2)
                    ],
                    'visit_type' => 'established_patient',
                    'payment_status' => 'paid',
                    'billing_amount' => rand(500, 2000)
                ]);
                $encounterCount++;
            }
        }

        $this->command->info("   ✓ {$encounterCount} demo encounters created");
    }

    private function createDemoPrescriptions(): void
    {
        $this->command->info('💊 Setting up demo prescriptions...');

        $encounters = Encounter::where('clinic_id', $this->demoClinic->id)->get();
        $prescriptionCount = 0;

        foreach ($encounters as $encounter) {
            $prescription = Prescription::where('clinic_id', $this->demoClinic->id)
                ->where('patient_id', $encounter->patient_id)
                ->where('doctor_id', $encounter->doctor_id)
                ->where('encounter_id', $encounter->id)
                ->first();

            if (!$prescription) {
                $qrHash = 'RX-DEMO-' . strtoupper(uniqid()) . '-' . $encounter->patient_id;

                Prescription::create([
                    'clinic_id' => $this->demoClinic->id,
                    'patient_id' => $encounter->patient_id,
                    'doctor_id' => $encounter->doctor_id,
                    'encounter_id' => $encounter->id,
                    'issued_at' => $encounter->date,
                    'status' => ['active', 'completed'][array_rand(['active', 'completed'])],
                    'qr_hash' => $qrHash,
                ]);
                $prescriptionCount++;
            }
        }

        $this->command->info("   ✓ {$prescriptionCount} demo prescriptions created");
    }

    private function createDemoLabResults(): void
    {
        $this->command->info('🧪 Setting up demo lab results...');

        $testTypes = [
            'Complete Blood Count',
            'Blood Chemistry',
            'Urinalysis',
            'Lipid Profile'
        ];

        $labResultCount = 0;
        foreach ($this->demoPatients as $patient) {
            $labResult = LabResult::where('clinic_id', $this->demoClinic->id)
                ->where('patient_id', $patient->id)
                ->first();

            if (!$labResult) {
                LabResult::create([
                    'clinic_id' => $this->demoClinic->id,
                    'patient_id' => $patient->id,
                    'test_name' => $testTypes[array_rand($testTypes)],
                    'test_type' => 'laboratory',
                    'result_value' => $this->faker->randomFloat(2, 1, 100),
                    'unit' => 'mg/dL',
                    'reference_range' => 'Normal: 0-100',
                    'status' => ['completed', 'abnormal'][array_rand(['completed', 'abnormal'])],
                    'ordered_at' => Carbon::now()->subDays(rand(1, 7)),
                    'completed_at' => Carbon::now()->subDays(rand(1, 7)),
                    'notes' => $this->faker->optional(0.3)->sentence(),
                ]);
                $labResultCount++;
            }
        }

        $this->command->info("   ✓ {$labResultCount} demo lab results created");
    }

    private function createDemoBills(): void
    {
        $this->command->info('💰 Setting up demo bills...');

        // Check if bills already exist
        $existingBills = Bill::where('clinic_id', $this->demoClinic->id)
            ->where('bill_number', 'like', 'BILL-DEMO-%')
            ->count();

        if ($existingBills > 0) {
            $this->command->info("   ✓ Demo bills already exist ({$existingBills} bills)");
            return;
        }

        // Create 3 sample bills quickly
        $billData = [];
        $billItemData = [];

        for ($i = 1; $i <= 3; $i++) {
            $patient = $this->demoPatients[array_rand($this->demoPatients)];
            $subtotal = rand(500, 2000);
            $discount = rand(0, 200);
            $total = $subtotal - $discount;

            $billData[] = [
                'uuid' => Str::uuid(),
                'clinic_id' => $this->demoClinic->id,
                'patient_id' => $patient->id,
                'bill_number' => 'BILL-DEMO-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'bill_date' => now()->subDays(rand(1, 7))->toDateString(),
                'due_date' => now()->addDays(30)->toDateString(),
                'subtotal' => $subtotal,
                'tax_amount' => 0,
                'discount_amount' => $discount,
                'total_amount' => $total,
                'status' => ['pending', 'paid'][array_rand(['pending', 'paid'])],
                'payment_method' => ['cash', 'card'][array_rand(['cash', 'card'])],
                'created_by' => $this->demoUser->id,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Bulk insert bills
        Bill::insert($billData);

        // Get the created bills to create items
        $bills = Bill::where('clinic_id', $this->demoClinic->id)
            ->where('bill_number', 'like', 'BILL-DEMO-%')
            ->get();

        // Create bill items
        foreach ($bills as $bill) {
            $itemTypes = ['service', 'consultation'];
            $itemNames = ['Consultation Fee', 'Medical Service'];
            $itemType = $itemTypes[array_rand($itemTypes)];
            $itemName = $itemNames[array_rand($itemNames)];
            $quantity = 1;
            $unitPrice = rand(500, 1000);
            $total = $quantity * $unitPrice;

            BillItem::create([
                'uuid' => Str::uuid(),
                'bill_id' => $bill->id,
                'item_type' => $itemType,
                'item_name' => $itemName,
                'item_description' => "Demo {$itemName}",
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total' => $total,
                'created_by' => $this->demoUser->id,
            ]);
        }

        $this->command->info("   ✓ 3 demo bills created");
    }

    private function createDemoInsurance(): void
    {
        $this->command->info('🏥 Setting up demo insurance records...');

        // Check if insurance already exists
        $existingInsurance = Insurance::whereIn('patient_id', collect($this->demoPatients)->pluck('id'))
            ->count();

        if ($existingInsurance > 0) {
            $this->command->info("   ✓ Demo insurance records already exist ({$existingInsurance} records)");
            return;
        }

        $insuranceProviders = ['PhilHealth', 'Maxicare', 'Intellicare'];
        $insuranceData = [];

        // Create insurance for 2 patients only
        $patientsWithInsurance = array_slice($this->demoPatients, 0, 2);

        foreach ($patientsWithInsurance as $patient) {
            $insuranceData[] = [
                'uuid' => Str::uuid(),
                'patient_id' => $patient->id,
                'insurance_provider' => $insuranceProviders[array_rand($insuranceProviders)],
                'policy_number' => 'POL-' . strtoupper(uniqid()),
                'member_id' => 'MEM-' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT),
                'group_number' => 'GRP-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
                'policy_holder_name' => $patient->first_name . ' ' . $patient->last_name,
                'policy_holder_relationship' => 'self',
                'coverage_type' => 'health',
                'effective_date' => Carbon::now()->subMonths(rand(1, 12)),
                'expiration_date' => Carbon::now()->addMonths(rand(6, 12)),
                'copay_amount' => rand(50, 500),
                'deductible_amount' => rand(1000, 5000),
                'coverage_percentage' => 100.00,
                'is_primary' => true,
                'is_active' => true,
                'verification_status' => 'verified',
                'created_by' => $this->demoUser->id,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Bulk insert insurance records
        Insurance::insert($insuranceData);

        $this->command->info("   ✓ " . count($insuranceData) . " demo insurance records created");
    }

    private function createDemoQueue(): void
    {
        $this->command->info('📋 Setting up demo queue data...');

        $queue = Queue::where('clinic_id', $this->demoClinic->id)
            ->where('name', 'General Consultation Queue')
            ->first();

        if (!$queue) {
            $queue = Queue::create([
                'uuid' => Str::uuid(),
                'clinic_id' => $this->demoClinic->id,
                'name' => 'General Consultation Queue',
                'description' => 'Main queue for general consultations',
                'queue_type' => 'general',
                'is_active' => true,
                'created_by' => $this->demoUser->id,
            ]);
        }

        // Add only 2 patients to the queue
        $queuePatientCount = 0;
        $patients = array_slice($this->demoPatients, 0, 2);
        foreach ($patients as $index => $patient) {
            $queuePatient = QueuePatient::where('queue_id', $queue->id)
                ->where('patient_id', $patient->id)
                ->first();

            if (!$queuePatient) {
                QueuePatient::create([
                    'uuid' => Str::uuid(),
                    'queue_id' => $queue->id,
                    'patient_id' => $patient->id,
                    'priority' => rand(1, 3),
                    'status' => ['waiting', 'served'][array_rand(['waiting', 'served'])],
                    'joined_at' => now()->subMinutes(rand(5, 30)),
                    'notes' => 'Demo queue entry',
                    'created_by' => $this->demoUser->id,
                ]);
                $queuePatientCount++;
            }
        }

        $this->command->info("   ✓ Demo queue with {$queuePatientCount} patients created");
    }

    private function createDemoNotifications(): void
    {
        $this->command->info('🔔 Setting up demo notifications...');

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

        $notificationCount = 0;
        foreach ($notifications as $notificationData) {
            $notification = Notification::where('user_id', $this->demoUser->id)
                ->where('title', $notificationData['title'])
                ->first();

            if (!$notification) {
                Notification::create([
                    'uuid' => Str::uuid(),
                    'user_id' => $this->demoUser->id,
                    'title' => $notificationData['title'],
                    'message' => $notificationData['message'],
                    'type' => $notificationData['type'],
                    'priority' => $notificationData['priority'],
                    'delivery_method' => 'database',
                    'delivery_status' => 'delivered',
                    'notifiable_type' => 'App\\Models\\User',
                    'notifiable_id' => $this->demoUser->id,
                    'created_by' => $this->demoUser->id,
                ]);
                $notificationCount++;
            }
        }

        $this->command->info("   ✓ {$notificationCount} demo notifications created");
    }

    private function createDemoActivityLogs(): void
    {
        $this->command->info('📊 Setting up demo activity logs...');

        $entities = [
            'Patient' => $this->demoPatients,
            'Doctor' => $this->demoDoctors,
        ];

        $actions = ['created', 'updated', 'viewed', 'scheduled', 'completed'];

        // Generate activity logs for the past 3 days
        $logCount = 0;
        for ($day = 0; $day < 3; $day++) {
            $date = Carbon::now()->subDays($day);
            $logsPerDay = rand(3, 8);

            for ($i = 0; $i < $logsPerDay; $i++) {
                $entityType = array_rand($entities);
                $entityCollection = $entities[$entityType];

                if (empty($entityCollection)) {
                    continue;
                }

                $entity = $entityCollection[array_rand($entityCollection)];
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
                        'request_method' => ['GET', 'POST', 'PUT'][array_rand(['GET', 'POST', 'PUT'])],
                        'url' => 'https://demo.medinext.com/' . strtolower($entityType) . 's/' . $entity->id
                    ],
                    'before_hash' => md5(uniqid()),
                    'after_hash' => md5(uniqid())
                ]);
                $logCount++;
            }
        }

        $this->command->info("   ✓ {$logCount} demo activity logs created");
    }

    private function createDemoSettings(): void
    {
        $this->command->info('⚙️ Setting up demo settings...');

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
            ],
            [
                'key' => 'allow_online_booking',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'clinic',
                'description' => 'Allow patients to book appointments online'
            ],
            [
                'key' => 'require_patient_verification',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'clinic',
                'description' => 'Require patient verification for appointments'
            ],
            [
                'key' => 'max_appointments_per_day',
                'value' => '50',
                'type' => 'integer',
                'group' => 'clinic',
                'description' => 'Maximum number of appointments per day'
            ],
            [
                'key' => 'notification_email',
                'value' => 'notifications@demomedical.com',
                'type' => 'string',
                'group' => 'notifications',
                'description' => 'Email address for system notifications'
            ]
        ];

        $settingCount = 0;
        foreach ($settings as $settingData) {
            $setting = Setting::where('clinic_id', $this->demoClinic->id)
                ->where('key', $settingData['key'])
                ->first();

            if (!$setting) {
                Setting::create([
                    'clinic_id' => $this->demoClinic->id,
                    'key' => $settingData['key'],
                    'value' => $settingData['value'],
                    'type' => $settingData['type'],
                    'group' => $settingData['group'],
                    'description' => $settingData['description'],
                    'is_public' => false,
                ]);
                $settingCount++;
            }
        }

        $this->command->info("   ✓ {$settingCount} demo settings created");
    }

    private function displayDemoAccountInfo(): void
    {
        $this->command->info('');
        $this->command->info('🎉 DEMO ACCOUNT SETUP COMPLETE!');
        $this->command->info('=====================================');
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
