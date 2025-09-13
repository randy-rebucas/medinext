<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Clinic;
use App\Models\Patient;
use App\Models\Appointment;
use App\Services\CacheService;
use App\Services\MonitoringService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

class PerformanceOptimizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test data
        $this->clinic = Clinic::factory()->create();
        $this->user = User::factory()->create();
        $this->user->clinics()->attach($this->clinic->id, ['role_id' => 1]);

        // Create test patients and appointments
        $this->patients = Patient::factory()->count(50)->create(['clinic_id' => $this->clinic->id]);
        $this->appointments = Appointment::factory()->count(100)->create(['clinic_id' => $this->clinic->id]);
    }

    /** @test */
    public function it_caches_user_permissions()
    {
        $permissions = ['appointments.view', 'patients.create'];

        CacheService::cacheUserPermissions($this->user->id, $this->clinic->id, $permissions);

        $cachedPermissions = CacheService::getCachedUserPermissions($this->user->id, $this->clinic->id);

        $this->assertEquals($permissions, $cachedPermissions);
    }

    /** @test */
    public function it_caches_clinic_settings()
    {
        $settings = [
            'appointments.max_per_day' => 50,
            'appointments.default_duration' => 30
        ];

        CacheService::cacheClinicSettings($this->clinic->id, $settings);

        $cachedSettings = CacheService::getCachedClinicSettings($this->clinic->id);

        $this->assertEquals($settings, $cachedSettings);
    }

    /** @test */
    public function it_caches_appointment_availability()
    {
        $availability = ['09:00', '09:30', '10:00', '10:30'];
        $date = '2024-01-15';

        CacheService::cacheAppointmentAvailability(1, $date, $availability);

        $cachedAvailability = CacheService::getCachedAppointmentAvailability(1, $date);

        $this->assertEquals($availability, $cachedAvailability);
    }

    /** @test */
    public function it_caches_patient_search_results()
    {
        $query = 'John';
        $results = [
            ['id' => 1, 'name' => 'John Doe'],
            ['id' => 2, 'name' => 'John Smith']
        ];

        CacheService::cachePatientSearch($query, $this->clinic->id, $results);

        $cachedResults = CacheService::getCachedPatientSearch($query, $this->clinic->id);

        $this->assertEquals($results, $cachedResults);
    }

    /** @test */
    public function it_invalidates_user_cache()
    {
        // Cache some data
        CacheService::cacheUserPermissions($this->user->id, $this->clinic->id, ['test']);

        // Invalidate user cache
        CacheService::invalidateUserCache($this->user->id);

        // Verify cache is cleared
        $cachedPermissions = CacheService::getCachedUserPermissions($this->user->id, $this->clinic->id);
        $this->assertNull($cachedPermissions);
    }

    /** @test */
    public function it_invalidates_clinic_cache()
    {
        // Cache some data
        CacheService::cacheClinicSettings($this->clinic->id, ['test' => 'value']);

        // Invalidate clinic cache
        CacheService::invalidateClinicCache($this->clinic->id);

        // Verify cache is cleared
        $cachedSettings = CacheService::getCachedClinicSettings($this->clinic->id);
        $this->assertNull($cachedSettings);
    }

    /** @test */
    public function it_clears_all_cache()
    {
        // Cache some data
        CacheService::cacheUserPermissions($this->user->id, $this->clinic->id, ['test']);
        CacheService::cacheClinicSettings($this->clinic->id, ['test' => 'value']);

        // Clear all cache
        CacheService::clearAllCache();

        // Verify all cache is cleared
        $this->assertNull(CacheService::getCachedUserPermissions($this->user->id, $this->clinic->id));
        $this->assertNull(CacheService::getCachedClinicSettings($this->clinic->id));
    }

    /** @test */
    public function it_gets_cache_statistics()
    {
        $stats = CacheService::getCacheStats();

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('memory_used', $stats);
        $this->assertArrayHasKey('connected_clients', $stats);
        $this->assertArrayHasKey('hit_rate', $stats);
    }

    /** @test */
    public function it_checks_system_health()
    {
        $health = MonitoringService::checkSystemHealth();

        $this->assertIsArray($health);
        $this->assertArrayHasKey('status', $health);
        $this->assertArrayHasKey('timestamp', $health);
        $this->assertArrayHasKey('checks', $health);
        $this->assertContains($health['status'], ['healthy', 'warning', 'unhealthy']);
    }

    /** @test */
    public function it_gets_performance_metrics()
    {
        $metrics = MonitoringService::getPerformanceMetrics();

        $this->assertIsArray($metrics);
        $this->assertArrayHasKey('timestamp', $metrics);
        $this->assertArrayHasKey('database', $metrics);
        $this->assertArrayHasKey('cache', $metrics);
        $this->assertArrayHasKey('application', $metrics);
    }

    /** @test */
    public function it_gets_security_metrics()
    {
        $metrics = MonitoringService::getSecurityMetrics();

        $this->assertIsArray($metrics);
        $this->assertArrayHasKey('timestamp', $metrics);
        $this->assertArrayHasKey('failed_logins', $metrics);
        $this->assertArrayHasKey('suspicious_activities', $metrics);
        $this->assertArrayHasKey('permission_denied', $metrics);
    }

    /** @test */
    public function it_gets_business_metrics()
    {
        $metrics = MonitoringService::getBusinessMetrics();

        $this->assertIsArray($metrics);
        $this->assertArrayHasKey('timestamp', $metrics);
        $this->assertArrayHasKey('appointments', $metrics);
        $this->assertArrayHasKey('patients', $metrics);
        $this->assertArrayHasKey('users', $metrics);
    }

    /** @test */
    public function it_checks_system_alerts()
    {
        $alerts = MonitoringService::checkAlerts();

        $this->assertIsArray($alerts);

        // Each alert should have required fields
        foreach ($alerts as $alert) {
            $this->assertArrayHasKey('type', $alert);
            $this->assertArrayHasKey('severity', $alert);
            $this->assertArrayHasKey('message', $alert);
            $this->assertArrayHasKey('timestamp', $alert);
            $this->assertContains($alert['severity'], ['low', 'medium', 'high', 'critical']);
        }
    }

    /** @test */
    public function it_handles_cache_errors_gracefully()
    {
        // Mock cache failure
        Cache::shouldReceive('put')->andThrow(new \Exception('Cache error'));

        // Should not throw exception
        CacheService::cacheUserPermissions($this->user->id, $this->clinic->id, ['test']);

        $this->assertTrue(true); // Test passes if no exception is thrown
    }

    /** @test */
    public function it_handles_monitoring_errors_gracefully()
    {
        // Mock database failure
        $this->mock(\Illuminate\Database\Connection::class, function ($mock) {
            $mock->shouldReceive('getPdo')->andThrow(new \Exception('Database error'));
        });

        // Should return unhealthy status
        $health = MonitoringService::checkSystemHealth();

        $this->assertEquals('unhealthy', $health['status']);
        $this->assertArrayHasKey('error', $health['checks']['database']);
    }
}
