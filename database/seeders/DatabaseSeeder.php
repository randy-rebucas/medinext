<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * This seeder now uses the unified BaseSeeder which consolidates all seeding functionality
     * into a single, organized, and efficient seeder.
     *
     * Usage:
     * - php artisan db:seed (runs BaseSeeder with full demo data)
     * - php artisan db:seed --class=BaseSeeder (explicit BaseSeeder call)
     */
    public function run(): void
    {
        $this->command->info('🚀 Starting unified database seeding...');
        $this->command->info('Using BaseSeeder - consolidated and optimized seeding');
        $this->command->info('');

        // Use the unified BaseSeeder
        $this->call(BaseSeeder::class);
        
        // Add clinic-specific seeding
        $this->call(ClinicSeeder::class);
    }
}
