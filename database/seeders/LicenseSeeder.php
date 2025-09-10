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
        // Create multiple demo licenses for different scenarios
        // Check if licenses already exist to prevent duplicates

        // 1. Premium License - Main Demo License
        $premiumLicense = $this->createOrUpdateLicense('premium', [
            'name' => 'MediNext Premium Demo License',
            'description' => 'Premium demo license for development and testing with full features',
            'license_type' => 'premium',
            'status' => 'active',
            'customer_name' => 'Dr. Sarah Johnson',
            'customer_email' => 'sarah.johnson@medinext.com',
            'customer_company' => 'MediNext Demo Clinic',
            'customer_phone' => '+1-555-0123',
            'max_users' => 50,
            'max_clinics' => 5,
            'max_patients' => 10000,
            'max_appointments_per_month' => 5000,
            'current_users' => 25, // 50% usage
            'current_clinics' => 3, // 60% usage
            'current_patients' => 5000, // 50% usage
            'appointments_this_month' => 2500, // 50% usage
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

        // 2. Standard License - Basic Clinic
        $standardLicense = $this->createOrUpdateLicense('standard', [
            'name' => 'MediNext Standard License',
            'description' => 'Standard license for small clinics with basic features',
            'license_type' => 'standard',
            'status' => 'active',
            'customer_name' => 'Dr. Michael Chen',
            'customer_email' => 'michael.chen@familyclinic.com',
            'customer_company' => 'Family Care Clinic',
            'customer_phone' => '+1-555-0456',
            'max_users' => 10,
            'max_clinics' => 1,
            'max_patients' => 2000,
            'max_appointments_per_month' => 1000,
            'current_users' => 8, // 80% usage (close to limit)
            'current_clinics' => 1, // 100% usage (at limit)
            'current_patients' => 1500, // 75% usage
            'appointments_this_month' => 750, // 75% usage
            'features' => [
                'basic_appointments',
                'patient_management',
                'prescription_management',
                'basic_reporting',
            ],
            'starts_at' => now(),
            'expires_at' => now()->addMonths(6),
            'grace_period_days' => 3,
            'auto_renew' => false,
            'monthly_fee' => 99.99,
            'billing_cycle' => 'monthly',
            'support_level' => 'standard',
            'created_by' => 'system',
        ]);

        // 3. Enterprise License - Large Healthcare System
        $enterpriseLicense = $this->createOrUpdateLicense('enterprise', [
            'name' => 'MediNext Enterprise License',
            'description' => 'Enterprise license for large healthcare systems with all features',
            'license_type' => 'enterprise',
            'status' => 'active',
            'customer_name' => 'Dr. Emily Rodriguez',
            'customer_email' => 'emily.rodriguez@healthsystem.com',
            'customer_company' => 'Metro Health System',
            'customer_phone' => '+1-555-0789',
            'max_users' => 500,
            'max_clinics' => 25,
            'max_patients' => 100000,
            'max_appointments_per_month' => 50000,
            'current_users' => 150, // 30% usage (growing enterprise)
            'current_clinics' => 8, // 32% usage
            'current_patients' => 30000, // 30% usage
            'appointments_this_month' => 15000, // 30% usage
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
                'sms_notifications',
                'api_access',
                'custom_branding',
                'priority_support',
                'advanced_analytics',
                'backup_restore',
            ],
            'starts_at' => now(),
            'expires_at' => now()->addYears(2),
            'grace_period_days' => 14,
            'auto_renew' => true,
            'monthly_fee' => 1999.99,
            'billing_cycle' => 'annual',
            'support_level' => 'enterprise',
            'created_by' => 'system',
        ]);

        // 4. Expired License - For testing expired scenarios
        $expiredLicense = $this->createOrUpdateLicense('expired', [
            'name' => 'MediNext Expired License',
            'description' => 'Expired license for testing expiration scenarios',
            'license_type' => 'premium',
            'status' => 'expired',
            'customer_name' => 'Dr. James Wilson',
            'customer_email' => 'james.wilson@oldclinic.com',
            'customer_company' => 'Old Medical Center',
            'customer_phone' => '+1-555-0321',
            'max_users' => 25,
            'max_clinics' => 2,
            'max_patients' => 5000,
            'max_appointments_per_month' => 2500,
            'current_users' => 23, // 92% usage (near limit when expired)
            'current_clinics' => 2, // 100% usage (at limit)
            'current_patients' => 4500, // 90% usage
            'appointments_this_month' => 2250, // 90% usage
            'features' => [
                'basic_appointments',
                'patient_management',
                'prescription_management',
                'basic_reporting',
                'advanced_reporting',
                'lab_results',
            ],
            'starts_at' => now()->subYear(),
            'expires_at' => now()->subDays(30),
            'grace_period_days' => 7,
            'auto_renew' => false,
            'monthly_fee' => 199.99,
            'billing_cycle' => 'monthly',
            'support_level' => 'standard',
            'created_by' => 'system',
        ]);

        // Use the premium license as the primary license for settings
        $license = $premiumLicense;

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
                'current' => $license->current_users,
                'limit' => $license->max_users,
                'percentage' => $license->getUsagePercentage('users')
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
                'current' => $license->current_clinics,
                'limit' => $license->max_clinics,
                'percentage' => $license->getUsagePercentage('clinics')
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
                'current' => $license->current_patients,
                'limit' => $license->max_patients,
                'percentage' => $license->getUsagePercentage('patients')
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
                'current' => $license->appointments_this_month,
                'limit' => $license->max_appointments_per_month,
                'percentage' => $license->getUsagePercentage('appointments')
            ],
            null,
            'json',
            'system',
            'Appointment usage statistics',
            false
        );

        $this->command->info('Multiple demo licenses created successfully!');
        $this->command->info('');
        $this->command->info('=== PREMIUM LICENSE (Primary) ===');
        $this->command->info('License Key: ' . $premiumLicense->license_key);
        $this->command->info('Activation Code: ' . $premiumLicense->activation_code);
        $this->command->info('Customer: ' . $premiumLicense->customer_name . ' (' . $premiumLicense->customer_company . ')');
        $this->command->info('Usage: ' . $premiumLicense->current_users . '/' . $premiumLicense->max_users . ' users, ' .
                           $premiumLicense->current_clinics . '/' . $premiumLicense->max_clinics . ' clinics, ' .
                           $premiumLicense->current_patients . '/' . $premiumLicense->max_patients . ' patients, ' .
                           $premiumLicense->appointments_this_month . '/' . $premiumLicense->max_appointments_per_month . ' appointments');
        $this->command->info('');
        $this->command->info('=== STANDARD LICENSE ===');
        $this->command->info('License Key: ' . $standardLicense->license_key);
        $this->command->info('Activation Code: ' . $standardLicense->activation_code);
        $this->command->info('Customer: ' . $standardLicense->customer_name . ' (' . $standardLicense->customer_company . ')');
        $this->command->info('Usage: ' . $standardLicense->current_users . '/' . $standardLicense->max_users . ' users, ' .
                           $standardLicense->current_clinics . '/' . $standardLicense->max_clinics . ' clinics, ' .
                           $standardLicense->current_patients . '/' . $standardLicense->max_patients . ' patients, ' .
                           $standardLicense->appointments_this_month . '/' . $standardLicense->max_appointments_per_month . ' appointments');
        $this->command->info('');
        $this->command->info('=== ENTERPRISE LICENSE ===');
        $this->command->info('License Key: ' . $enterpriseLicense->license_key);
        $this->command->info('Activation Code: ' . $enterpriseLicense->activation_code);
        $this->command->info('Customer: ' . $enterpriseLicense->customer_name . ' (' . $enterpriseLicense->customer_company . ')');
        $this->command->info('Usage: ' . $enterpriseLicense->current_users . '/' . $enterpriseLicense->max_users . ' users, ' .
                           $enterpriseLicense->current_clinics . '/' . $enterpriseLicense->max_clinics . ' clinics, ' .
                           $enterpriseLicense->current_patients . '/' . $enterpriseLicense->max_patients . ' patients, ' .
                           $enterpriseLicense->appointments_this_month . '/' . $enterpriseLicense->max_appointments_per_month . ' appointments');
        $this->command->info('');
        $this->command->info('=== EXPIRED LICENSE (For Testing) ===');
        $this->command->info('License Key: ' . $expiredLicense->license_key);
        $this->command->info('Activation Code: ' . $expiredLicense->activation_code);
        $this->command->info('Customer: ' . $expiredLicense->customer_name . ' (' . $expiredLicense->customer_company . ')');
        $this->command->info('Status: ' . $expiredLicense->status . ' (Expired for testing)');
        $this->command->info('Usage: ' . $expiredLicense->current_users . '/' . $expiredLicense->max_users . ' users, ' .
                           $expiredLicense->current_clinics . '/' . $expiredLicense->max_clinics . ' clinics, ' .
                           $expiredLicense->current_patients . '/' . $expiredLicense->max_patients . ' patients, ' .
                           $expiredLicense->appointments_this_month . '/' . $expiredLicense->max_appointments_per_month . ' appointments');
        $this->command->info('');
        $this->command->info('Total licenses created: 4');
        $this->command->info('Primary license type: ' . $license->license_type);
    }

    /**
     * Create or update a license with uniqueness checks
     */
    private function createOrUpdateLicense(string $identifier, array $data): License
    {
        // Check if a license with this name already exists
        $existingLicense = License::where('name', $data['name'])->first();

        if ($existingLicense) {
            $this->command->info("License '{$data['name']}' already exists. Updating existing license...");

            // Update the existing license with new data (excluding auto-generated fields)
            $updateData = $data;
            unset($updateData['license_key'], $updateData['activation_code'], $updateData['uuid']);

            $existingLicense->update($updateData);

            return $existingLicense;
        }

        // Additional uniqueness checks before creating
        $this->ensureUniqueLicenseData($data);

        // Create new license
        $license = License::create($data);
        $this->command->info("Created new license: {$data['name']}");

        return $license;
    }

    /**
     * Ensure license data is unique
     */
    private function ensureUniqueLicenseData(array &$data): void
    {
        // Ensure unique customer email
        if (isset($data['customer_email'])) {
            $existingEmail = License::where('customer_email', $data['customer_email'])->first();
            if ($existingEmail) {
                $this->command->warn("Customer email '{$data['customer_email']}' already exists. Generating unique email...");
                $data['customer_email'] = $this->generateUniqueEmail($data['customer_email']);
            }
        }

        // Ensure unique customer phone
        if (isset($data['customer_phone'])) {
            $existingPhone = License::where('customer_phone', $data['customer_phone'])->first();
            if ($existingPhone) {
                $this->command->warn("Customer phone '{$data['customer_phone']}' already exists. Generating unique phone...");
                $data['customer_phone'] = $this->generateUniquePhone($data['customer_phone']);
            }
        }
    }

    /**
     * Generate unique email address
     */
    private function generateUniqueEmail(string $baseEmail): string
    {
        $counter = 1;
        do {
            $uniqueEmail = str_replace('@', "+demo{$counter}@", $baseEmail);
            $counter++;
        } while (License::where('customer_email', $uniqueEmail)->exists());

        return $uniqueEmail;
    }

    /**
     * Generate unique phone number
     */
    private function generateUniquePhone(string $basePhone): string
    {
        $counter = 1;
        do {
            $uniquePhone = str_replace('-', "-{$counter}-", $basePhone);
            $counter++;
        } while (License::where('customer_phone', $uniquePhone)->exists());

        return $uniquePhone;
    }
}
