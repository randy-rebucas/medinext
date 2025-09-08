<?php

namespace Database\Factories;

use App\Models\License;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\License>
 */
class LicenseFactory extends Factory
{
    protected $model = License::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => $this->faker->uuid(),
            'license_key' => $this->faker->unique()->regexify('[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}'),
            'license_type' => 'standard',
            'status' => 'active',
            'name' => $this->faker->company() . ' License',
            'description' => 'Test license for development',
            'customer_name' => $this->faker->company(),
            'customer_email' => $this->faker->safeEmail(),
            'max_users' => $this->faker->numberBetween(1, 100),
            'max_clinics' => $this->faker->numberBetween(1, 10),
            'features' => json_encode([
                'appointments' => true,
                'prescriptions' => true,
                'patients' => true,
                'reports' => true,
            ]),
            'starts_at' => now(),
            'expires_at' => $this->faker->dateTimeBetween('now', '+1 year'),
            'activated_at' => now(),
            'activation_code' => $this->faker->regexify('[A-Z0-9]{8}'),
        ];
    }

    /**
     * Indicate that the license is expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => $this->faker->dateTimeBetween('-1 year', '-1 day'),
            'status' => 'expired',
        ]);
    }

    /**
     * Indicate that the license is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'suspended',
        ]);
    }
}
