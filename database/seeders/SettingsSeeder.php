<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultSettings = [
            // Clinic Information
            [
                'key' => 'clinic.name',
                'value' => 'Your Clinic Name',
                'type' => 'string',
                'group' => 'clinic',
                'description' => 'The name of your clinic',
                'is_public' => true,
            ],
            [
                'key' => 'clinic.phone',
                'value' => '+63 123 456 7890',
                'type' => 'string',
                'group' => 'clinic',
                'description' => 'Primary contact phone number',
                'is_public' => true,
            ],
            [
                'key' => 'clinic.email',
                'value' => 'info@yourclinic.com',
                'type' => 'string',
                'group' => 'clinic',
                'description' => 'Primary contact email',
                'is_public' => true,
            ],
            [
                'key' => 'clinic.address',
                'value' => [
                    'street' => '123 Main Street',
                    'city' => 'Manila',
                    'state' => 'Metro Manila',
                    'postal_code' => '1000',
                    'country' => 'Philippines'
                ],
                'type' => 'json',
                'group' => 'clinic',
                'description' => 'Clinic address information',
                'is_public' => true,
            ],
            [
                'key' => 'clinic.website',
                'value' => 'https://yourclinic.com',
                'type' => 'string',
                'group' => 'clinic',
                'description' => 'Clinic website URL',
                'is_public' => true,
            ],
            [
                'key' => 'clinic.description',
                'value' => 'Professional healthcare services for your family',
                'type' => 'string',
                'group' => 'clinic',
                'description' => 'Brief description of your clinic',
                'is_public' => true,
            ],

            // Working Hours
            [
                'key' => 'working_hours.monday',
                'value' => ['start' => '08:00', 'end' => '17:00', 'closed' => false],
                'type' => 'json',
                'group' => 'working_hours',
                'description' => 'Monday working hours',
                'is_public' => true,
            ],
            [
                'key' => 'working_hours.tuesday',
                'value' => ['start' => '08:00', 'end' => '17:00', 'closed' => false],
                'type' => 'json',
                'group' => 'working_hours',
                'description' => 'Tuesday working hours',
                'is_public' => true,
            ],
            [
                'key' => 'working_hours.wednesday',
                'value' => ['start' => '08:00', 'end' => '17:00', 'closed' => false],
                'type' => 'json',
                'group' => 'working_hours',
                'description' => 'Wednesday working hours',
                'is_public' => true,
            ],
            [
                'key' => 'working_hours.thursday',
                'value' => ['start' => '08:00', 'end' => '17:00', 'closed' => false],
                'type' => 'json',
                'group' => 'working_hours',
                'description' => 'Thursday working hours',
                'is_public' => true,
            ],
            [
                'key' => 'working_hours.friday',
                'value' => ['start' => '08:00', 'end' => '17:00', 'closed' => false],
                'type' => 'json',
                'group' => 'working_hours',
                'description' => 'Friday working hours',
                'is_public' => true,
            ],
            [
                'key' => 'working_hours.saturday',
                'value' => ['start' => '08:00', 'end' => '12:00', 'closed' => false],
                'type' => 'json',
                'group' => 'working_hours',
                'description' => 'Saturday working hours',
                'is_public' => true,
            ],
            [
                'key' => 'working_hours.sunday',
                'value' => ['start' => '00:00', 'end' => '00:00', 'closed' => true],
                'type' => 'json',
                'group' => 'working_hours',
                'description' => 'Sunday working hours',
                'is_public' => true,
            ],

            // Notifications
            [
                'key' => 'notifications.email_enabled',
                'value' => true,
                'type' => 'boolean',
                'group' => 'notifications',
                'description' => 'Enable email notifications',
                'is_public' => false,
            ],
            [
                'key' => 'notifications.sms_enabled',
                'value' => false,
                'type' => 'boolean',
                'group' => 'notifications',
                'description' => 'Enable SMS notifications',
                'is_public' => false,
            ],
            [
                'key' => 'notifications.appointment_reminder_hours',
                'value' => 24,
                'type' => 'integer',
                'group' => 'notifications',
                'description' => 'Hours before appointment to send reminder',
                'is_public' => false,
            ],
            [
                'key' => 'notifications.follow_up_days',
                'value' => 7,
                'type' => 'integer',
                'group' => 'notifications',
                'description' => 'Days after visit to send follow-up',
                'is_public' => false,
            ],

            // Branding
            [
                'key' => 'branding.primary_color',
                'value' => '#3B82F6',
                'type' => 'string',
                'group' => 'branding',
                'description' => 'Primary brand color (hex)',
                'is_public' => true,
            ],
            [
                'key' => 'branding.secondary_color',
                'value' => '#1E40AF',
                'type' => 'string',
                'group' => 'branding',
                'description' => 'Secondary brand color (hex)',
                'is_public' => true,
            ],
            [
                'key' => 'branding.logo_url',
                'value' => null,
                'type' => 'string',
                'group' => 'branding',
                'description' => 'URL to clinic logo',
                'is_public' => true,
            ],
            [
                'key' => 'branding.favicon_url',
                'value' => null,
                'type' => 'string',
                'group' => 'branding',
                'description' => 'URL to clinic favicon',
                'is_public' => true,
            ],

            // System Settings
            [
                'key' => 'system.timezone',
                'value' => 'Asia/Manila',
                'type' => 'string',
                'group' => 'system',
                'description' => 'Default timezone for the clinic',
                'is_public' => false,
            ],
            [
                'key' => 'system.date_format',
                'value' => 'Y-m-d',
                'type' => 'string',
                'group' => 'system',
                'description' => 'Date format for display',
                'is_public' => false,
            ],
            [
                'key' => 'system.time_format',
                'value' => 'H:i',
                'type' => 'string',
                'group' => 'system',
                'description' => 'Time format for display',
                'is_public' => false,
            ],
            [
                'key' => 'system.currency',
                'value' => 'PHP',
                'type' => 'string',
                'group' => 'system',
                'description' => 'Default currency for the clinic',
                'is_public' => false,
            ],
        ];

        foreach ($defaultSettings as $setting) {
            Setting::create($setting);
        }
    }
}
