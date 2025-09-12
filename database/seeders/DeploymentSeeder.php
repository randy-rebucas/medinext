<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DeploymentSeeder extends Seeder
{
    /**
     * Run the deployment seeders.
     * This is specifically designed for production deployment.
     */
    public function run(): void
    {
        $this->command->info('Starting deployment seeding...');

        // Always run the initial seeder for deployment
        $this->call(InitialSeeder::class);

        $this->command->info('Deployment seeding completed successfully!');
        $this->command->info('');
        $this->command->info('To add demo data later, run: php artisan db:seed --class=DemoAccountSeeder');
    }
}
