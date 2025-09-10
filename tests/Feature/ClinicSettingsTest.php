<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\Setting;
use App\Models\User;
use App\Models\Role;
use App\Models\UserClinicRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClinicSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test clinic
        $this->clinic = Clinic::create([
            'name' => 'Test Clinic',
            'slug' => 'test-clinic',
            'timezone' => 'Asia/Manila',
            'phone' => '+63 912 345 6789',
            'email' => 'test@clinic.com',
            'website' => 'https://testclinic.com',
            'description' => 'Test clinic description',
        ]);

        // Create admin role
        $adminRole = Role::create(['name' => 'admin']);

        // Create test user
        $this->user = User::create([
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        // Assign admin role to user in clinic
        UserClinicRole::create([
            'user_id' => $this->user->id,
            'clinic_id' => $this->clinic->id,
            'role_id' => $adminRole->id,
        ]);
    }

    public function test_can_get_clinic_settings()
    {
        $response = $this->actingAs($this->user)
            ->get('/api/v1/settings/clinic');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'clinic' => [
                        'id',
                        'name',
                        'phone',
                        'email',
                        'website',
                        'description',
                    ],
                    'settings' => [
                        'clinic_name',
                        'clinic_code',
                        'description',
                        'phone',
                        'email',
                        'address',
                        'website',
                        'license',
                        'opening_time',
                        'closing_time',
                        'working_days',
                        'email_notifications',
                        'sms_notifications',
                        'online_booking',
                        'patient_portal',
                    ]
                ]
            ]);
    }

    public function test_can_update_clinic_settings()
    {
        $settingsData = [
            'clinic_name' => 'Updated Clinic Name',
            'clinic_code' => 'UCN001',
            'description' => 'Updated description',
            'phone' => '+63 912 345 6789',
            'email' => 'updated@clinic.com',
            'address' => 'Updated Address',
            'website' => 'https://updatedclinic.com',
            'license' => 'LIC-2024-001',
            'opening_time' => '09:00',
            'closing_time' => '17:00',
            'working_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            'email_notifications' => true,
            'sms_notifications' => false,
            'online_booking' => true,
            'patient_portal' => false,
        ];

        $response = $this->actingAs($this->user)
            ->putJson('/api/v1/settings/clinic', $settingsData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Clinic settings updated successfully'
            ]);

        // Verify clinic was updated
        $this->clinic->refresh();
        $this->assertEquals('Updated Clinic Name', $this->clinic->name);
        $this->assertEquals('Updated description', $this->clinic->description);
        $this->assertEquals('+63 912 345 6789', $this->clinic->phone);
        $this->assertEquals('updated@clinic.com', $this->clinic->email);
        $this->assertEquals('https://updatedclinic.com', $this->clinic->website);

        // Verify settings were saved
        $this->assertDatabaseHas('settings', [
            'clinic_id' => $this->clinic->id,
            'key' => 'clinic.name',
            'value' => json_encode('Updated Clinic Name')
        ]);

        $this->assertDatabaseHas('settings', [
            'clinic_id' => $this->clinic->id,
            'key' => 'clinic.code',
            'value' => json_encode('UCN001')
        ]);
    }

    public function test_validation_works_for_clinic_settings()
    {
        $invalidData = [
            'clinic_name' => '', // Required field
            'email' => 'invalid-email', // Invalid email format
            'website' => 'not-a-url', // Invalid URL
        ];

        $response = $this->actingAs($this->user)
            ->putJson('/api/v1/settings/clinic', $invalidData);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'message',
                'errors'
            ]);
    }

    public function test_unauthorized_user_cannot_access_settings()
    {
        // Create a user without admin role
        $regularUser = User::create([
            'name' => 'Regular User',
            'email' => 'regular@test.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $response = $this->actingAs($regularUser)
            ->get('/api/v1/settings/clinic');

        $response->assertStatus(403);
    }
}
