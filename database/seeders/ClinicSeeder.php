<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Clinic;
use App\Models\User;
use App\Models\Role;
use App\Models\UserClinicRole;
use Illuminate\Support\Str;

class ClinicSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample clinics
        $clinics = [
            [
                'name' => 'City Medical Center',
                'slug' => 'city-medical-center',
                'timezone' => 'America/New_York',
                'address' => [
                    'street' => '123 Main Street',
                    'city' => 'New York',
                    'state' => 'NY',
                    'postal_code' => '10001',
                    'country' => 'United States',
                ],
                'phone' => '+1 (555) 123-4567',
                'email' => 'info@citymedical.com',
                'website' => 'https://citymedical.com',
                'description' => 'Full-service medical center providing comprehensive healthcare services.',
            ],
            [
                'name' => 'Downtown Family Clinic',
                'slug' => 'downtown-family-clinic',
                'timezone' => 'America/Chicago',
                'address' => [
                    'street' => '456 Oak Avenue',
                    'city' => 'Chicago',
                    'state' => 'IL',
                    'postal_code' => '60601',
                    'country' => 'United States',
                ],
                'phone' => '+1 (555) 987-6543',
                'email' => 'contact@downtownfamily.com',
                'website' => 'https://downtownfamily.com',
                'description' => 'Family-focused healthcare with a personal touch.',
            ],
            [
                'name' => 'Westside Specialty Clinic',
                'slug' => 'westside-specialty-clinic',
                'timezone' => 'America/Los_Angeles',
                'address' => [
                    'street' => '789 Pine Street',
                    'city' => 'Los Angeles',
                    'state' => 'CA',
                    'postal_code' => '90210',
                    'country' => 'United States',
                ],
                'phone' => '+1 (555) 456-7890',
                'email' => 'appointments@westsideclinic.com',
                'website' => 'https://westsideclinic.com',
                'description' => 'Specialized medical services and advanced treatments.',
            ],
        ];

        $createdClinics = [];

        foreach ($clinics as $clinicData) {
            $clinic = Clinic::create($clinicData);
            $createdClinics[] = $clinic;
        }

        // Get or create roles
        $adminRole = Role::firstOrCreate(['name' => 'admin'], [
            'name' => 'admin',
            'display_name' => 'Administrator',
            'description' => 'Full system access',
        ]);

        $doctorRole = Role::firstOrCreate(['name' => 'doctor'], [
            'name' => 'doctor',
            'display_name' => 'Doctor',
            'description' => 'Medical professional access',
        ]);

        $receptionistRole = Role::firstOrCreate(['name' => 'receptionist'], [
            'name' => 'receptionist',
            'display_name' => 'Receptionist',
            'description' => 'Front desk and appointment management',
        ]);

        // Get existing users
        $users = User::all();

        if ($users->count() > 0) {
            // Assign first user as admin of first clinic
            $firstUser = $users->first();
            $firstClinic = $createdClinics[0];

            UserClinicRole::firstOrCreate([
                'user_id' => $firstUser->id,
                'clinic_id' => $firstClinic->id,
            ], [
                'user_id' => $firstUser->id,
                'clinic_id' => $firstClinic->id,
                'role_id' => $adminRole->id,
                'assigned_at' => now(),
            ]);

            // Set first clinic as current for first user
            $firstUser->update(['current_clinic_id' => $firstClinic->id]);

            // If there are more users, assign them to different clinics
            if ($users->count() > 1) {
                $secondUser = $users->skip(1)->first();
                $secondClinic = $createdClinics[1] ?? $createdClinics[0];

                UserClinicRole::firstOrCreate([
                    'user_id' => $secondUser->id,
                    'clinic_id' => $secondClinic->id,
                ], [
                    'user_id' => $secondUser->id,
                    'clinic_id' => $secondClinic->id,
                    'role_id' => $doctorRole->id,
                    'assigned_at' => now(),
                ]);

                $secondUser->update(['current_clinic_id' => $secondClinic->id]);
            }

            // If there are even more users, assign them to the third clinic
            if ($users->count() > 2) {
                $thirdUser = $users->skip(2)->first();
                $thirdClinic = $createdClinics[2] ?? $createdClinics[0];

                UserClinicRole::firstOrCreate([
                    'user_id' => $thirdUser->id,
                    'clinic_id' => $thirdClinic->id,
                ], [
                    'user_id' => $thirdUser->id,
                    'clinic_id' => $thirdClinic->id,
                    'role_id' => $receptionistRole->id,
                    'assigned_at' => now(),
                ]);

                $thirdUser->update(['current_clinic_id' => $thirdClinic->id]);
            }
        }

        $this->command->info('Created ' . count($createdClinics) . ' sample clinics and assigned users.');
    }
}