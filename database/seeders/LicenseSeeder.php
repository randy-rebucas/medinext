<?php

namespace Database\Seeders;

use App\Models\License;
use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class LicenseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a demo license for development
        $license = License::create([
            'name' => 'MediNext Demo License',
            'description' => 'Demo license for development and testing',
            'license_type' => 'premium',
            'status' => 'active',
            'customer_name' => 'Demo Clinic',
            'customer_email' => 'demo@medinext.com',
            'customer_company' => 'MediNext Demo Clinic',
            'customer_phone' => '+1-555-0123',
            'max_users' => 50,
            'max_clinics' => 5,
            'max_patients' => 10000,
            'max_appointments_per_month' => 5000,
            'features' => [
                'basic_appointments',
                'patient_management',
                'prescription_management',
                'basic_reporting',
                'advanced_reporting',
                'lab_results',
                'medrep_management',
                'multi_clinic',
                'email_notifications',
            ],
            'starts_at' => now(),
            'expires_at' => now()->addYear(),
            'grace_period_days' => 7,
            'auto_renew' => true,
            'monthly_fee' => 299.99,
            'billing_cycle' => 'monthly',
            'support_level' => 'premium',
            'created_by' => 'system',
        ]);

        // Add license status to application settings
        Setting::setValue(
            'license.status',
            'active',
            null,
            'string',
            'system',
            'Current license status',
            false
        );

        Setting::setValue(
            'license.type',
            'premium',
            null,
            'string',
            'system',
            'Current license type',
            false
        );

        Setting::setValue(
            'license.expires_at',
            $license->expires_at->toDateString(),
            null,
            'string',
            'system',
            'License expiration date',
            false
        );

        Setting::setValue(
            'license.features',
            $license->features,
            null,
            'json',
            'system',
            'Available license features',
            false
        );

        Setting::setValue(
            'license.usage.users',
            [
                'current' => 0,
                'limit' => $license->max_users,
                'percentage' => 0
            ],
            null,
            'json',
            'system',
            'User usage statistics',
            false
        );

        Setting::setValue(
            'license.usage.clinics',
            [
                'current' => 0,
                'limit' => $license->max_clinics,
                'percentage' => 0
            ],
            null,
            'json',
            'system',
            'Clinic usage statistics',
            false
        );

        Setting::setValue(
            'license.usage.patients',
            [
                'current' => 0,
                'limit' => $license->max_patients,
                'percentage' => 0
            ],
            null,
            'json',
            'system',
            'Patient usage statistics',
            false
        );

        Setting::setValue(
            'license.usage.appointments',
            [
                'current' => 0,
                'limit' => $license->max_appointments_per_month,
                'percentage' => 0
            ],
            null,
            'json',
            'system',
            'Appointment usage statistics',
            false
        );

        $this->command->info('Demo license created successfully!');
        $this->command->info('License Key: ' . $license->license_key);
        $this->command->info('Activation Code: ' . $license->activation_code);
    }
}