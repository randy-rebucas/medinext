<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

class RemoveInstallationFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'install:cleanup
                            {--force : Force removal without confirmation}
                            {--keep-routes : Keep installation routes for debugging}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove installation files and routes after successful installation';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧹 Cleaning up installation files...');
        $this->newLine();

        // Check if installation is complete
        if (!$this->isInstallationComplete()) {
            $this->error('❌ Installation is not complete. Cannot remove installation files.');
            return Command::FAILURE;
        }

        if (!$this->option('force') && !$this->confirm('Are you sure you want to remove installation files? This action cannot be undone.')) {
            $this->info('Installation cleanup cancelled.');
            return Command::SUCCESS;
        }

        try {
            $removedFiles = [];
            $removedRoutes = [];

            // Remove installation controller
            $controllerPath = app_path('Http/Controllers/InstallationController.php');
            if (File::exists($controllerPath)) {
                File::delete($controllerPath);
                $removedFiles[] = 'InstallationController.php';
                $this->line('   ✓ Removed InstallationController.php');
            }

            // Remove installation middleware
            $middlewarePath = app_path('Http/Middleware/CheckInstallationStatus.php');
            if (File::exists($middlewarePath)) {
                File::delete($middlewarePath);
                $removedFiles[] = 'CheckInstallationStatus.php';
                $this->line('   ✓ Removed CheckInstallationStatus.php');
            }

            // Remove installation service
            $servicePath = app_path('Services/InstallationService.php');
            if (File::exists($servicePath)) {
                File::delete($servicePath);
                $removedFiles[] = 'InstallationService.php';
                $this->line('   ✓ Removed InstallationService.php');
            }

            // Remove installation routes
            if (!$this->option('keep-routes')) {
                $routesPath = base_path('routes/installation.php');
                if (File::exists($routesPath)) {
                    File::delete($routesPath);
                    $removedRoutes[] = 'installation.php';
                    $this->line('   ✓ Removed installation routes');
                }
            }

            // Remove installation views
            $viewsPath = resource_path('js/Pages/installation');
            if (File::isDirectory($viewsPath)) {
                File::deleteDirectory($viewsPath);
                $removedFiles[] = 'installation views directory';
                $this->line('   ✓ Removed installation views');
            }

            // Remove this command file
            $commandPath = app_path('Console/Commands/RemoveInstallationFiles.php');
            if (File::exists($commandPath)) {
                File::delete($commandPath);
                $removedFiles[] = 'RemoveInstallationFiles.php';
                $this->line('   ✓ Removed RemoveInstallationFiles.php');
            }

            // Update bootstrap/app.php to remove installation middleware
            $this->removeInstallationMiddleware();

            // Clear route cache
            Artisan::call('route:clear');
            $this->line('   ✓ Cleared route cache');

            // Clear config cache
            Artisan::call('config:clear');
            $this->line('   ✓ Cleared config cache');

            $this->newLine();
            $this->info('✅ Installation cleanup completed successfully!');
            $this->newLine();

            if (!empty($removedFiles)) {
                $this->info('📁 Removed files:');
                foreach ($removedFiles as $file) {
                    $this->line("   • {$file}");
                }
                $this->newLine();
            }

            if (!empty($removedRoutes)) {
                $this->info('🛣️  Removed routes:');
                foreach ($removedRoutes as $route) {
                    $this->line("   • {$route}");
                }
                $this->newLine();
            }

            $this->info('🎉 MediNext EMR is now ready for production use!');
            $this->newLine();
            $this->line('Next steps:');
            $this->line('1. Configure your web server');
            $this->line('2. Set up SSL certificate');
            $this->line('3. Configure email settings');
            $this->line('4. Set up backup procedures');
            $this->line('5. Review security settings');
            $this->newLine();

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Error during cleanup: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Check if installation is complete
     */
    private function isInstallationComplete(): bool
    {
        try {
            // Check if installation flag file exists
            $flagFile = storage_path('app/installation_complete.flag');
            if (!file_exists($flagFile)) {
                return false;
            }

            // Check if database is properly set up
            if (!\Illuminate\Support\Facades\DB::getSchemaBuilder()->hasTable('users')) {
                return false;
            }

            // Check if there's at least one user with superadmin role
            $superadminExists = \Illuminate\Support\Facades\DB::table('users')
                ->join('user_roles', 'users.id', '=', 'user_roles.user_id')
                ->join('roles', 'user_roles.role_id', '=', 'roles.id')
                ->where('roles.name', 'superadmin')
                ->exists();

            return $superadminExists;

        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Remove installation middleware from bootstrap/app.php
     */
    private function removeInstallationMiddleware(): void
    {
        $bootstrapPath = base_path('bootstrap/app.php');
        $content = File::get($bootstrapPath);

        // Remove the installation middleware alias
        $content = preg_replace(
            "/\s*'installation\.check'\s*=>\s*\\\\App\\\\Http\\\\Middleware\\\\CheckInstallationStatus::class,\s*/",
            '',
            $content
        );

        File::put($bootstrapPath, $content);
        $this->line('   ✓ Removed installation middleware from bootstrap/app.php');
    }
}
