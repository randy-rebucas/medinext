<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class InstallCleanup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'install:cleanup {--force : Force cleanup without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove installation files and routes for security';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧹 MediNext Installation Cleanup');
        $this->info('================================');
        $this->newLine();

        // Check if system is installed
        if (!$this->isSystemInstalled()) {
            $this->error('❌ System is not installed. Cannot perform cleanup.');
            return Command::FAILURE;
        }

        // Confirm cleanup
        if (!$this->option('force')) {
            if (!$this->confirm('This will remove installation files and routes. Continue?')) {
                $this->info('Cleanup cancelled.');
                return Command::SUCCESS;
            }
        }

        try {
            $this->info('🔧 Starting cleanup process...');

            // Remove installation routes
            $this->removeInstallationRoutes();

            // Remove installation files
            $this->removeInstallationFiles();

            // Remove installation backups
            $this->removeInstallationBackups();

            // Clear caches
            $this->clearCaches();

            $this->newLine();
            $this->info('✅ Installation cleanup completed successfully!');
            $this->newLine();

            $this->displayCleanupSummary();

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Cleanup failed: ' . $e->getMessage());
            Log::error('Installation cleanup failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return Command::FAILURE;
        }
    }

    /**
     * Check if system is installed
     */
    private function isSystemInstalled(): bool
    {
        try {
            $flagFile = storage_path('app/installation_complete.flag');
            return file_exists($flagFile);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Remove installation routes
     */
    private function removeInstallationRoutes(): void
    {
        $this->info('📝 Removing installation routes...');

        $routesFile = base_path('routes/installation.php');
        if (file_exists($routesFile)) {
            // Comment out the installation routes instead of deleting the file
            $content = file_get_contents($routesFile);
            $commentedContent = preg_replace('/^Route::/m', '// Route::', $content);
            file_put_contents($routesFile, $commentedContent);
            $this->line('   ✓ Installation routes disabled');
        }

        // Remove installation route registration from web.php
        $webRoutesFile = base_path('routes/web.php');
        if (file_exists($webRoutesFile)) {
            $content = file_get_contents($webRoutesFile);
            $updatedContent = preg_replace('/require.*installation\.php.*;/', '// Installation routes disabled for security', $content);
            file_put_contents($webRoutesFile, $updatedContent);
            $this->line('   ✓ Installation route registration removed');
        }
    }

    /**
     * Remove installation files
     */
    private function removeInstallationFiles(): void
    {
        $this->info('🗂️  Removing installation files...');

        $filesToRemove = [
            'app/Http/Controllers/InstallationController.php',
            'app/Console/Commands/InstallMediNext.php',
            'app/Console/Commands/InstallCleanup.php', // Remove this command itself
        ];

        foreach ($filesToRemove as $file) {
            $filePath = base_path($file);
            if (file_exists($filePath)) {
                File::delete($filePath);
                $this->line("   ✓ Removed {$file}");
            }
        }

        // Remove installation views
        $viewsPath = resource_path('js/pages/installation');
        if (File::exists($viewsPath)) {
            File::deleteDirectory($viewsPath);
            $this->line('   ✓ Removed installation views');
        }

        // Remove installation layout
        $layoutFile = resource_path('js/components/InstallationLayout.tsx');
        if (file_exists($layoutFile)) {
            File::delete($layoutFile);
            $this->line('   ✓ Removed installation layout');
        }
    }

    /**
     * Remove installation backups
     */
    private function removeInstallationBackups(): void
    {
        $this->info('💾 Removing installation backups...');

        $backupsPath = storage_path('app/installation_backups');
        if (File::exists($backupsPath)) {
            File::deleteDirectory($backupsPath);
            $this->line('   ✓ Removed installation backups');
        }
    }

    /**
     * Clear caches
     */
    private function clearCaches(): void
    {
        $this->info('🧹 Clearing caches...');

        try {
            $this->call('route:clear');
            $this->line('   ✓ Route cache cleared');

            $this->call('config:clear');
            $this->line('   ✓ Config cache cleared');

            $this->call('view:clear');
            $this->line('   ✓ View cache cleared');

        } catch (\Exception $e) {
            $this->warn('   ⚠️  Some caches could not be cleared: ' . $e->getMessage());
        }
    }

    /**
     * Display cleanup summary
     */
    private function displayCleanupSummary(): void
    {
        $this->info('📋 Cleanup Summary:');
        $this->line('   • Installation routes disabled');
        $this->line('   • Installation files removed');
        $this->line('   • Installation backups removed');
        $this->line('   • Caches cleared');
        $this->newLine();

        $this->info('🔒 Security Notice:');
        $this->line('   • Installation wizard is no longer accessible');
        $this->line('   • System is now secure from unauthorized installations');
        $this->line('   • Admin access is required for system management');
        $this->newLine();

        $this->info('📚 Next Steps:');
        $this->line('   • Access your system at: /login');
        $this->line('   • Configure additional settings in admin panel');
        $this->line('   • Set up your clinic operations');
        $this->line('   • Add staff members and patients');
    }
}
