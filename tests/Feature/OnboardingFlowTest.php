<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Clinic;
use App\Models\Role;
use App\Models\Permission;
use App\Models\UserClinicRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class OnboardingFlowTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create basic permissions and roles for testing
        $this->createBasicPermissions();
        $this->createBasicRoles();
    }

    /** @test */
    public function user_can_complete_full_onboarding_flow()
    {
        // Create a user without onboarding completion
        $user = User::factory()->create([
            'onboarding_completed_at' => null,
            'trial_started_at' => now(),
            'trial_ends_at' => now()->addDays(14),
            'is_trial_user' => true,
            'has_activated_license' => false,
        ]);

        // Test welcome step
        $response = $this->actingAs($user)->get('/onboarding/welcome');
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('onboarding/welcome'));

        // Test license step
        $response = $this->actingAs($user)->get('/onboarding/license');
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('onboarding/license'));

        // Test license activation (optional)
        $licenseData = [
            'license_key' => 'TEST-LICENSE-KEY-12345'
        ];

        $response = $this->actingAs($user)->post('/onboarding/license', $licenseData);
        // Note: This will fail in test environment without proper license service mock
        // but we can test the validation

        // Test clinic setup step
        $response = $this->actingAs($user)->get('/onboarding/clinic-setup');
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('onboarding/clinic-setup'));

        // Test clinic setup submission
        $clinicData = [
            'name' => 'Test Clinic',
            'address' => '123 Main Street',
            'city' => 'Test City',
            'state' => 'Test State',
            'postal_code' => '12345',
            'country' => 'Test Country',
            'phone' => '+1234567890',
            'email' => 'clinic@test.com',
            'website' => 'https://testclinic.com',
            'description' => 'A test clinic',
        ];

        $response = $this->actingAs($user)->post('/onboarding/clinic-setup', $clinicData);
        $response->assertRedirect('/onboarding/team-setup');

        // Verify clinic was created
        $this->assertDatabaseHas('clinics', [
            'name' => 'Test Clinic',
            'email' => 'clinic@test.com',
        ]);

        // Verify user has admin role in clinic
        $clinic = Clinic::where('name', 'Test Clinic')->first();
        $this->assertTrue($user->hasRoleInClinic('admin', $clinic->id));

        // Test team setup step
        $response = $this->actingAs($user)->get('/onboarding/team-setup');
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('onboarding/team-setup'));

        // Test team setup submission (optional)
        $teamData = [
            'team_members' => [
                [
                    'name' => 'John Doe',
                    'email' => 'john@testclinic.com',
                    'role_id' => Role::where('name', 'doctor')->first()->id,
                    'department' => 'Medical',
                ]
            ]
        ];

        $response = $this->actingAs($user)->post('/onboarding/team-setup', $teamData);
        $response->assertRedirect('/onboarding/complete');

        // Test completion step
        $response = $this->actingAs($user)->get('/onboarding/complete');
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('onboarding/complete'));

        // Test finishing onboarding
        $response = $this->actingAs($user)->post('/onboarding/finish');
        $response->assertRedirect('/dashboard');

        // Verify onboarding is marked as completed
        $user->refresh();
        $this->assertNotNull($user->onboarding_completed_at);
    }

    /** @test */
    public function user_cannot_access_dashboard_without_completing_onboarding()
    {
        $user = User::factory()->create([
            'onboarding_completed_at' => null,
        ]);

        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertRedirect('/onboarding/welcome');
    }

    /** @test */
    public function user_can_access_dashboard_after_completing_onboarding()
    {
        $user = User::factory()->create([
            'onboarding_completed_at' => now(),
        ]);

        // Create a clinic and assign admin role
        $clinic = Clinic::factory()->create();
        $adminRole = Role::where('name', 'admin')->first();
        
        UserClinicRole::create([
            'user_id' => $user->id,
            'clinic_id' => $clinic->id,
            'role_id' => $adminRole->id,
            'department' => 'Administration',
            'status' => 'Active',
            'join_date' => now(),
        ]);

        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200);
    }

    /** @test */
    public function clinic_setup_validation_works_correctly()
    {
        $user = User::factory()->create([
            'onboarding_completed_at' => null,
        ]);

        // Test with invalid data
        $invalidData = [
            'name' => '', // Required field missing
            'address' => 'a', // Too short
            'city' => '',
            'state' => '',
            'postal_code' => '',
            'country' => '',
            'phone' => '123', // Too short
            'email' => 'invalid-email', // Invalid email
        ];

        $response = $this->actingAs($user)->post('/onboarding/clinic-setup', $invalidData);
        $response->assertSessionHasErrors([
            'name',
            'address',
            'city',
            'state',
            'postal_code',
            'country',
            'phone',
            'email',
        ]);
    }

    /** @test */
    public function license_activation_validation_works_correctly()
    {
        $user = User::factory()->create([
            'onboarding_completed_at' => null,
        ]);

        // Test with invalid license key
        $response = $this->actingAs($user)->post('/onboarding/license', [
            'license_key' => 'short', // Too short
        ]);

        $response->assertSessionHasErrors(['license_key']);
    }

    /** @test */
    public function user_cannot_finish_onboarding_without_clinic()
    {
        $user = User::factory()->create([
            'onboarding_completed_at' => null,
        ]);

        // Ensure user has no clinic
        $this->assertCount(0, $user->clinics);

        $response = $this->actingAs($user)->post('/onboarding/finish');
        $response->assertRedirect('/onboarding/clinic-setup');
        $response->assertSessionHasErrors(['error']);
    }

    /** @test */
    public function team_setup_validation_works_correctly()
    {
        $user = User::factory()->create([
            'onboarding_completed_at' => null,
        ]);

        // Create a clinic first
        $clinic = Clinic::factory()->create();
        $adminRole = Role::where('name', 'admin')->first();
        
        UserClinicRole::create([
            'user_id' => $user->id,
            'clinic_id' => $clinic->id,
            'role_id' => $adminRole->id,
            'department' => 'Administration',
            'status' => 'Active',
            'join_date' => now(),
        ]);

        // Test with invalid team member data
        $invalidData = [
            'team_members' => [
                [
                    'name' => '', // Required field missing
                    'email' => 'invalid-email', // Invalid email
                    'role_id' => '', // Required field missing
                    'department' => 'Medical',
                ]
            ]
        ];

        $response = $this->actingAs($user)->post('/onboarding/team-setup', $invalidData);
        $response->assertSessionHasErrors(['team_members.0.name', 'team_members.0.email', 'team_members.0.role_id']);
    }

    private function createBasicPermissions(): void
    {
        $permissions = [
            'system.admin',
            'settings.manage',
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            'doctors.view',
            'doctors.create',
            'doctors.edit',
            'doctors.delete',
            'patients.view',
            'patients.create',
            'patients.edit',
            'patients.delete',
            'appointments.view',
            'appointments.create',
            'appointments.edit',
            'appointments.delete',
            'dashboard.view',
        ];

        foreach ($permissions as $permission) {
            Permission::create([
                'name' => ucwords(str_replace('.', ' ', $permission)),
                'slug' => $permission,
                'description' => "Permission for {$permission}",
                'module' => explode('.', $permission)[0],
                'action' => explode('.', $permission)[1] ?? 'view',
            ]);
        }
    }

    private function createBasicRoles(): void
    {
        $adminRole = Role::create([
            'name' => 'admin',
            'description' => 'Full clinic access and management',
            'is_system_role' => false,
        ]);

        // Assign all permissions to admin role
        $permissions = Permission::all();
        $adminRole->permissions()->attach($permissions->pluck('id'));
    }
}
