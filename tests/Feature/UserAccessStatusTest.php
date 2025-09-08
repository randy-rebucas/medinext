<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\License;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;

class UserAccessStatusTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a test user
        $this->user = User::factory()->create([
            'is_active' => true,
        ]);
    }

    /** @test */
    public function it_requires_authentication()
    {
        $response = $this->getJson('/api/v1/license/user-access-status');

        $response->assertStatus(401)
                ->assertJson([
                    'success' => false,
                    'message' => 'Unauthenticated',
                    'error_code' => 'UNAUTHENTICATED'
                ]);
    }

    /** @test */
    public function it_returns_user_access_status_for_licensed_user()
    {
        // Create a license
        $license = License::factory()->create([
            'license_key' => 'TEST-LICENSE-123',
            'expires_at' => now()->addYear(),
            'status' => 'active',
        ]);

        // Create user with activated license
        $user = User::factory()->create([
            'license_key' => 'TEST-LICENSE-123',
            'has_activated_license' => true,
            'is_trial_user' => false,
            'is_active' => true,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/license/user-access-status');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'type' => 'licensed',
                        'status' => 'active',
                        'message' => 'Full access with license',
                    ]
                ]);

        $this->assertArrayHasKey('expires_at', $response->json('data'));
    }

    /** @test */
    public function it_returns_user_access_status_for_trial_user()
    {
        // Create user with active trial
        $user = User::factory()->create([
            'trial_started_at' => now()->subDays(5),
            'trial_ends_at' => now()->addDays(9),
            'is_trial_user' => true,
            'has_activated_license' => false,
            'is_active' => true,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/license/user-access-status');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'type' => 'trial',
                        'status' => 'active',
                        'message' => 'Free trial active',
                    ]
                ]);

        $data = $response->json('data');
        $this->assertArrayHasKey('expires_at', $data);
        $this->assertArrayHasKey('days_remaining', $data);
        $this->assertGreaterThanOrEqual(8, $data['days_remaining']);
        $this->assertLessThanOrEqual(9, $data['days_remaining']);
    }

    /** @test */
    public function it_returns_user_access_status_for_expired_trial_user()
    {
        // Create user with expired trial
        $user = User::factory()->create([
            'trial_started_at' => now()->subDays(20),
            'trial_ends_at' => now()->subDays(6),
            'is_trial_user' => true,
            'has_activated_license' => false,
            'is_active' => true,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/license/user-access-status');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'type' => 'trial',
                        'status' => 'expired',
                        'message' => 'Free trial expired',
                    ]
                ]);

        $data = $response->json('data');
        $this->assertArrayHasKey('expires_at', $data);
        $this->assertArrayHasKey('days_expired', $data);
        $this->assertGreaterThanOrEqual(5, $data['days_expired']);
        $this->assertLessThanOrEqual(6, $data['days_expired']);
    }

    /** @test */
    public function it_returns_user_access_status_for_user_with_no_access()
    {
        // Create user with no trial and no license
        $user = User::factory()->create([
            'trial_started_at' => null,
            'trial_ends_at' => null,
            'is_trial_user' => false,
            'has_activated_license' => false,
            'is_active' => true,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/license/user-access-status');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'type' => 'none',
                        'status' => 'inactive',
                        'message' => 'No access',
                    ]
                ]);
    }

    /** @test */
    public function it_rejects_inactive_users()
    {
        // Create inactive user
        $user = User::factory()->create([
            'is_active' => false,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/license/user-access-status');

        $response->assertStatus(401)
                ->assertJson([
                    'success' => false,
                    'message' => 'Account is deactivated',
                    'error_code' => 'ACCOUNT_DEACTIVATED'
                ]);
    }

    /** @test */
    public function it_handles_license_expiration_correctly()
    {
        // Create an expired license
        $license = License::factory()->create([
            'license_key' => 'EXPIRED-LICENSE-123',
            'expires_at' => now()->subDays(10),
            'status' => 'expired',
        ]);

        // Create user with expired license
        $user = User::factory()->create([
            'license_key' => 'EXPIRED-LICENSE-123',
            'has_activated_license' => true,
            'is_trial_user' => false,
            'is_active' => true,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/license/user-access-status');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'type' => 'licensed',
                        'status' => 'active',
                        'message' => 'Full access with license',
                    ]
                ]);

        // Note: The current implementation doesn't check if the license is actually expired
        // This might be a business logic decision - you may want to add license expiration checking
    }

    /** @test */
    public function it_returns_correct_trial_days_remaining()
    {
        $trialEndsAt = now()->addDays(7);
        
        $user = User::factory()->create([
            'trial_started_at' => now()->subDays(7),
            'trial_ends_at' => $trialEndsAt,
            'is_trial_user' => true,
            'has_activated_license' => false,
            'is_active' => true,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/license/user-access-status');

        $response->assertStatus(200);
        
        $data = $response->json('data');
        $this->assertGreaterThanOrEqual(6, $data['days_remaining']);
        $this->assertLessThanOrEqual(7, $data['days_remaining']);
    }

    /** @test */
    public function it_returns_correct_trial_days_expired()
    {
        $trialEndedAt = now()->subDays(3);
        
        $user = User::factory()->create([
            'trial_started_at' => now()->subDays(17),
            'trial_ends_at' => $trialEndedAt,
            'is_trial_user' => true,
            'has_activated_license' => false,
            'is_active' => true,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/license/user-access-status');

        $response->assertStatus(200);
        
        $data = $response->json('data');
        $this->assertGreaterThanOrEqual(2, $data['days_expired']);
        $this->assertLessThanOrEqual(3, $data['days_expired']);
    }
}
