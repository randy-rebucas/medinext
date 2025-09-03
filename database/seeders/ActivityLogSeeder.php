<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ActivityLog;
use App\Models\Clinic;
use App\Models\User;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Appointment;
use App\Models\Prescription;
use App\Models\LabResult;
use Carbon\Carbon;

class ActivityLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clinics = Clinic::all();
        $users = User::all();
        $patients = Patient::all();
        $doctors = Doctor::all();
        $appointments = Appointment::all();
        $prescriptions = Prescription::all();
        $labResults = LabResult::all();
        
        if ($clinics->isEmpty() || $users->isEmpty()) {
            $this->command->error('Required data not found. Please run ClinicSeeder and UserRoleSeeder first.');
            return;
        }

        $entities = [
            'Patient' => $patients,
            'Doctor' => $doctors,
            'Appointment' => $appointments,
            'Prescription' => $prescriptions,
            'LabResult' => $labResults
        ];

        $actions = [
            'created', 'updated', 'deleted', 'viewed', 'exported', 'imported',
            'approved', 'rejected', 'scheduled', 'cancelled', 'completed',
            'assigned', 'unassigned', 'logged_in', 'logged_out', 'password_changed'
        ];

        // Generate activity logs for the past 3 months
        $startDate = Carbon::now()->subMonths(3);
        
        for ($month = 0; $month < 3; $month++) {
            $currentDate = $startDate->copy()->addMonths($month);
            
            // Generate 50-150 activity logs per month
            $logsPerMonth = rand(50, 150);
            
            for ($i = 0; $i < $logsPerMonth; $i++) {
                $clinic = $clinics->random();
                $user = $users->random();
                $entity = array_rand($entities);
                $entityCollection = $entities[$entity];
                
                if ($entityCollection->isEmpty()) {
                    continue;
                }
                
                $entityInstance = $entityCollection->random();
                $action = $actions[array_rand($actions)];
                $timestamp = $currentDate->copy()->addDays(rand(1, 28))->addHours(rand(0, 23))->addMinutes(rand(0, 59));
                
                $meta = $this->generateMeta($entity, $action, $entityInstance);
                
                ActivityLog::firstOrCreate(
                    [
                        'clinic_id' => $clinic->id,
                        'actor_user_id' => $user->id,
                        'entity' => $entity,
                        'entity_id' => $entityInstance->id,
                        'action' => $action,
                        'at' => $timestamp
                    ],
                    [
                        'clinic_id' => $clinic->id,
                        'actor_user_id' => $user->id,
                        'entity' => $entity,
                        'entity_id' => $entityInstance->id,
                        'action' => $action,
                        'at' => $timestamp,
                        'ip' => $this->generateRandomIP(),
                        'meta' => $meta,
                        'before_hash' => md5(uniqid()),
                        'after_hash' => md5(uniqid())
                    ]
                );
            }
        }

        // Generate some recent activity logs for today
        $today = Carbon::now();
        for ($i = 0; $i < 20; $i++) {
            $clinic = $clinics->random();
            $user = $users->random();
            $entity = array_rand($entities);
            $entityCollection = $entities[$entity];
            
            if ($entityCollection->isEmpty()) {
                continue;
            }
            
            $entityInstance = $entityCollection->random();
            $action = $actions[array_rand($actions)];
            $timestamp = $today->copy()->addHours(rand(0, 23))->addMinutes(rand(0, 59));
            
            $meta = $this->generateMeta($entity, $action, $entityInstance);
            
            ActivityLog::firstOrCreate(
                [
                    'clinic_id' => $clinic->id,
                    'actor_user_id' => $user->id,
                    'entity' => $entity,
                    'entity_id' => $entityInstance->id,
                    'action' => $action,
                    'at' => $timestamp
                ],
                [
                    'clinic_id' => $clinic->id,
                    'actor_user_id' => $user->id,
                    'entity' => $entity,
                    'entity_id' => $entityInstance->id,
                    'action' => $action,
                    'at' => $timestamp,
                    'ip' => $this->generateRandomIP(),
                    'meta' => $meta,
                    'before_hash' => md5(uniqid()),
                    'after_hash' => md5(uniqid())
                ]
            );
        }

        $this->command->info('Activity logs seeded successfully!');
    }

    private function generateMeta($entity, $action, $entityInstance): array
    {
        $meta = [
            'user_agent' => $this->getRandomUserAgent(),
            'session_id' => 'session_' . uniqid(),
            'request_method' => $this->getRandomRequestMethod(),
            'url' => $this->generateURL($entity, $action, $entityInstance->id)
        ];

        switch ($entity) {
            case 'Patient':
                $meta['patient_name'] = $entityInstance->name;
                $meta['patient_id'] = $entityInstance->id;
                break;
            case 'Doctor':
                $meta['doctor_name'] = $entityInstance->user->name ?? 'Unknown';
                $meta['specialty'] = $entityInstance->specialty ?? 'General';
                break;
            case 'Appointment':
                $meta['appointment_date'] = $entityInstance->start_at?->format('Y-m-d H:i:s');
                $meta['patient_name'] = $entityInstance->patient->name ?? 'Unknown';
                break;
            case 'Prescription':
                $meta['prescription_date'] = $entityInstance->issued_at?->format('Y-m-d');
                $meta['patient_name'] = $entityInstance->patient->name ?? 'Unknown';
                break;
            case 'LabResult':
                $meta['test_name'] = $entityInstance->test_name;
                $meta['test_type'] = $entityInstance->test_type;
                break;
        }

        return $meta;
    }

    private function generateRandomIP(): string
    {
        return rand(1, 255) . '.' . rand(0, 255) . '.' . rand(0, 255) . '.' . rand(1, 255);
    }

    private function getRandomUserAgent(): string
    {
        $userAgents = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.1 Safari/605.1.15'
        ];
        
        return $userAgents[array_rand($userAgents)];
    }

    private function getRandomRequestMethod(): string
    {
        $methods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'];
        return $methods[array_rand($methods)];
    }

    private function generateURL($entity, $action, $entityId): string
    {
        $baseURL = 'https://medinext.local';
        
        switch ($entity) {
            case 'Patient':
                return $baseURL . '/patients/' . $entityId;
            case 'Doctor':
                return $baseURL . '/doctors/' . $entityId;
            case 'Appointment':
                return $baseURL . '/appointments/' . $entityId;
            case 'Prescription':
                return $baseURL . '/prescriptions/' . $entityId;
            case 'LabResult':
                return $baseURL . '/lab-results/' . $entityId;
            default:
                return $baseURL . '/dashboard';
        }
    }
}
