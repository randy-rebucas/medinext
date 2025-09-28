<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use App\Models\User;
use App\Models\Role;
use App\Models\Clinic;
use App\Models\Setting;
use App\Services\SettingsService;
use Database\Seeders\BaseSeeder;

class InstallationController extends Controller
{
    /**
     * Show the installation welcome page
     */
    public function index(): Response|RedirectResponse
    {
        // Check if already installed
        if ($this->isInstalled()) {
            return redirect()->route('login');
        }

        return Inertia::render('installation/Welcome', [
            'system_requirements' => $this->checkSystemRequirements(),
            'database_connection' => $this->checkDatabaseConnection(),
        ]);
    }

    /**
     * Show the database configuration page
     */
    public function database(): Response|RedirectResponse
    {
        if ($this->isInstalled()) {
            return redirect()->route('login');
        }

        return Inertia::render('installation/Database');
    }

    /**
     * Handle database configuration
     */
    public function configureDatabase(Request $request)
    {
        if ($this->isInstalled()) {
            return redirect()->route('login');
        }

        $validator = Validator::make($request->all(), [
            'db_host' => 'required|string|max:255',
            'db_port' => 'required|integer|min:1|max:65535',
            'db_name' => 'required|string|max:255',
            'db_username' => 'required|string|max:255',
            'db_password' => 'nullable|string|max:255',
        ], [
            'db_host.required' => 'Database host is required',
            'db_port.required' => 'Database port is required',
            'db_name.required' => 'Database name is required',
            'db_username.required' => 'Database username is required',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            // Test database connection
            $connection = $this->testDatabaseConnection(
                $request->db_host,
                $request->db_port,
                $request->db_name,
                $request->db_username,
                $request->db_password
            );

            if (!$connection['success']) {
                return back()->withErrors(['database' => $connection['message']])->withInput();
            }

            // Update .env file
            $this->updateEnvFile([
                'DB_HOST' => $request->db_host,
                'DB_PORT' => $request->db_port,
                'DB_DATABASE' => $request->db_name,
                'DB_USERNAME' => $request->db_username,
                'DB_PASSWORD' => $request->db_password,
            ]);

            return redirect()->route('installation.admin');

        } catch (\Exception $e) {
            Log::error('Database configuration failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withErrors(['database' => 'Database configuration failed: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Show the admin user creation page
     */
    public function admin(): Response|RedirectResponse
    {
        if ($this->isInstalled()) {
            return redirect()->route('login');
        }

        return Inertia::render('installation/Admin');
    }

    /**
     * Handle admin user creation
     */
    public function createAdmin(Request $request)
    {
        if ($this->isInstalled()) {
            return redirect()->route('login');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
            'clinic_name' => 'required|string|max:255',
            'clinic_phone' => 'required|string|max:20|regex:/^[\+]?[1-9][\d]{0,15}$/',
            'clinic_email' => 'required|string|email|max:255',
            'clinic_address' => 'required|string|max:500',
        ], [
            'name.required' => 'Admin name is required',
            'email.required' => 'Admin email is required',
            'email.email' => 'Please enter a valid email address',
            'email.unique' => 'This email address is already in use',
            'password.required' => 'Password is required',
            'password.min' => 'Password must be at least 8 characters',
            'password.confirmed' => 'Password confirmation does not match',
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, and one number',
            'clinic_name.required' => 'Clinic name is required',
            'clinic_phone.required' => 'Clinic phone is required',
            'clinic_phone.regex' => 'Please enter a valid phone number',
            'clinic_email.required' => 'Clinic email is required',
            'clinic_email.email' => 'Please enter a valid clinic email address',
            'clinic_address.required' => 'Clinic address is required',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            // Create installation backup point
            $backupPoint = $this->createInstallationBackup();

            // Run database migrations
            $this->runMigrations();

            // Create admin user and clinic
            $result = $this->createAdminUserAndClinic($request->all());

            if (!$result['success']) {
                // Rollback to backup point
                $this->rollbackToBackup($backupPoint);
                return back()->withErrors(['admin' => $result['message']])->withInput();
            }

            // Mark installation as complete
            $this->markInstallationComplete();

            // Clean up backup files
            $this->cleanupBackup($backupPoint);

            // Log successful installation
            Log::info('MediNext installation completed successfully', [
                'admin_email' => $request->email,
                'clinic_name' => $request->clinic_name,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'installation_id' => $backupPoint,
            ]);

            return redirect()->route('installation.complete');

        } catch (\Exception $e) {
            // Attempt rollback if backup exists
            if (isset($backupPoint)) {
                $this->rollbackToBackup($backupPoint);
            }

            Log::error('Admin creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'backup_point' => $backupPoint ?? null,
            ]);

            return back()->withErrors(['admin' => 'Installation failed: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Show the installation complete page
     */
    public function complete(): Response|RedirectResponse
    {
        if (!$this->isInstalled()) {
            return redirect()->route('installation.index');
        }

        return Inertia::render('installation/Complete', [
            'admin_email' => User::whereHas('roles', function($query) {
                $query->where('name', 'superadmin');
            })->first()?->email,
        ]);
    }

    /**
     * Check if the system is already installed
     */
    private function isInstalled(): bool
    {
        try {
            // Check if users table exists and has data
            if (!DB::getSchemaBuilder()->hasTable('users')) {
                return false;
            }

            // Check if there's a superadmin user
            $superadminExists = User::whereHas('roles', function($query) {
                $query->where('name', 'superadmin');
            })->exists();

            return $superadminExists;

        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Check system requirements
     */
    private function checkSystemRequirements(): array
    {
        $requirements = [
            'php_version' => [
                'required' => '8.1.0',
                'current' => PHP_VERSION,
                'status' => version_compare(PHP_VERSION, '8.1.0', '>='),
            ],
            'extensions' => [
                'openssl' => extension_loaded('openssl'),
                'pdo' => extension_loaded('pdo'),
                'mbstring' => extension_loaded('mbstring'),
                'tokenizer' => extension_loaded('tokenizer'),
                'xml' => extension_loaded('xml'),
                'ctype' => extension_loaded('ctype'),
                'json' => extension_loaded('json'),
                'bcmath' => extension_loaded('bcmath'),
                'fileinfo' => extension_loaded('fileinfo'),
                'curl' => extension_loaded('curl'),
                'gd' => extension_loaded('gd'),
            ],
            'permissions' => [
                'storage_writable' => is_writable(storage_path()),
                'bootstrap_cache_writable' => is_writable(base_path('bootstrap/cache')),
            ],
        ];

        return $requirements;
    }

    /**
     * Check database connection
     */
    private function checkDatabaseConnection(): array
    {
        try {
            DB::connection()->getPdo();
            return [
                'status' => true,
                'message' => 'Database connection successful',
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => 'Database connection failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Test database connection with provided credentials
     */
    private function testDatabaseConnection(string $host, int $port, string $database, string $username, ?string $password): array
    {
        try {
            $dsn = "mysql:host={$host};port={$port};dbname={$database}";
            $pdo = new \PDO($dsn, $username, $password, [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            ]);

            return [
                'success' => true,
                'message' => 'Database connection successful',
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Database connection failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Update .env file with new values
     */
    private function updateEnvFile(array $values): void
    {
        $envPath = base_path('.env');
        $envContent = file_get_contents($envPath);

        foreach ($values as $key => $value) {
            $pattern = "/^{$key}=.*/m";
            $replacement = "{$key}={$value}";
            
            if (preg_match($pattern, $envContent)) {
                $envContent = preg_replace($pattern, $replacement, $envContent);
            } else {
                $envContent .= "\n{$replacement}";
            }
        }

        file_put_contents($envPath, $envContent);
    }

    /**
     * Run database migrations
     */
    private function runMigrations(): void
    {
        Artisan::call('migrate', ['--force' => true]);
    }

    /**
     * Create admin user and clinic
     */
    private function createAdminUserAndClinic(array $data): array
    {
        try {
            DB::beginTransaction();

            // Step 1: Run core seeder first to create permissions, roles, and settings
            $this->runInitialSeeding();

            // Step 2: Get the superadmin role (created by seeder)
            $superadminRole = Role::where('name', 'superadmin')->first();
            if (!$superadminRole) {
                throw new \Exception('Superadmin role not found. Seeder may have failed.');
            }

            // Step 3: Create admin user
            $adminUser = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'phone' => $data['clinic_phone'],
                'is_active' => true,
                'email_verified_at' => now(),
                'onboarding_completed_at' => now(),
            ]);

            // Step 4: Create clinic
            $clinic = Clinic::create([
                'name' => $data['clinic_name'],
                'phone' => $data['clinic_phone'],
                'email' => $data['clinic_email'],
                'address' => $data['clinic_address'],
            ]);

            // Step 5: Assign user to clinic with superadmin role
            $adminUser->userClinicRoles()->create([
                'clinic_id' => $clinic->id,
                'role_id' => $superadminRole->id,
            ]);

            // Step 6: Create default settings for the clinic
            $this->createDefaultClinicSettings($clinic->id, $data);

            DB::commit();

            return [
                'success' => true,
                'user_id' => $adminUser->id,
                'clinic_id' => $clinic->id,
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Create default clinic settings
     */
    private function createDefaultClinicSettings(int $clinicId, array $data): void
    {
        $defaultSettings = [
            // Clinic Information
            'clinic.name' => $data['clinic_name'],
            'clinic.phone' => $data['clinic_phone'],
            'clinic.email' => $data['clinic_email'],
            'clinic.address' => $data['clinic_address'],
            'clinic.website' => '',
            'clinic.description' => 'Medical clinic providing comprehensive healthcare services',

            // Working Hours (Default: Monday-Friday 9AM-5PM)
            'working_hours.monday' => json_encode(['start' => '09:00', 'end' => '17:00', 'closed' => false]),
            'working_hours.tuesday' => json_encode(['start' => '09:00', 'end' => '17:00', 'closed' => false]),
            'working_hours.wednesday' => json_encode(['start' => '09:00', 'end' => '17:00', 'closed' => false]),
            'working_hours.thursday' => json_encode(['start' => '09:00', 'end' => '17:00', 'closed' => false]),
            'working_hours.friday' => json_encode(['start' => '09:00', 'end' => '17:00', 'closed' => false]),
            'working_hours.saturday' => json_encode(['start' => '09:00', 'end' => '13:00', 'closed' => false]),
            'working_hours.sunday' => json_encode(['start' => '09:00', 'end' => '17:00', 'closed' => true]),

            // System Settings
            'system.timezone' => 'UTC',
            'system.date_format' => 'Y-m-d',
            'system.time_format' => 'H:i',
            'system.currency' => 'USD',
            'system.language' => 'en',

            // Security Settings
            'security.session_timeout' => 480, // 8 hours
            'security.password_min_length' => 8,
            'security.require_2fa' => false,
            'security.audit_log_retention_days' => 2555, // 7 years
            'security.patient_data_retention_days' => 2555, // 7 years

            // Appointment Settings
            'appointments.default_duration' => 30,
            'appointments.buffer_time' => 15,
            'appointments.auto_confirm' => true,
            'appointments.allow_online_booking' => true,
            'appointments.max_advance_days' => 90,
            'appointments.min_advance_hours' => 2,
            'appointments.cancellation_hours' => 24,
            'appointments.reminder_hours' => 24,
            'appointments.max_per_day' => 50,

            // Notifications
            'notifications.email_enabled' => true,
            'notifications.sms_enabled' => false,
            'appointment_reminder_hours' => 24,
            'follow_up_days' => 7,

            // Branding
            'branding.primary_color' => '#3B82F6',
            'branding.secondary_color' => '#1E40AF',
            'branding.logo_url' => '',
            'branding.favicon_url' => '',
        ];

        foreach ($defaultSettings as $key => $value) {
            Setting::create([
                'clinic_id' => $clinicId,
                'key' => $key,
                'value' => $value,
                'type' => is_bool($value) ? 'boolean' : (is_numeric($value) ? 'integer' : 'string'),
                'group' => explode('.', $key)[0],
                'description' => $this->getSettingDescription($key),
                'is_public' => in_array($key, ['clinic.name', 'clinic.phone', 'clinic.email', 'clinic.address', 'working_hours.monday', 'working_hours.tuesday', 'working_hours.wednesday', 'working_hours.thursday', 'working_hours.friday', 'working_hours.saturday', 'working_hours.sunday']),
            ]);
        }
    }

    /**
     * Get setting description
     */
    private function getSettingDescription(string $key): string
    {
        $descriptions = [
            'clinic.name' => 'Official clinic name',
            'clinic.phone' => 'Primary contact phone number',
            'clinic.email' => 'Primary contact email address',
            'clinic.address' => 'Complete clinic address',
            'clinic.website' => 'Clinic website URL',
            'clinic.description' => 'Clinic service description',
            'system.timezone' => 'Default timezone for the clinic',
            'system.date_format' => 'Date display format',
            'system.time_format' => 'Time display format',
            'system.currency' => 'Default currency',
            'system.language' => 'Default language',
            'security.session_timeout' => 'Session timeout in minutes',
            'security.password_min_length' => 'Minimum password length',
            'appointments.default_duration' => 'Default appointment duration in minutes',
            'appointments.buffer_time' => 'Buffer time between appointments in minutes',
            'notifications.email_enabled' => 'Enable email notifications',
            'notifications.sms_enabled' => 'Enable SMS notifications',
        ];

        return $descriptions[$key] ?? 'System setting';
    }

    /**
     * Run initial seeding
     */
    private function runInitialSeeding(): void
    {
        // Run only core system seeding for installation (Step 1 only)
        $seeder = new BaseSeeder();
        $seeder->setOptions([
            'skip_existing' => true,
            'validate_data' => false,
            'show_progress' => false,
            'memory_optimized' => true,
            'create_demo_data' => false,
        ]);
        $seeder->runInstallationSeeding();
    }

    /**
     * Mark installation as complete
     */
    private function markInstallationComplete(): void
    {
        // Create a flag file to indicate installation is complete
        $flagFile = storage_path('app/installation_complete.flag');
        file_put_contents($flagFile, json_encode([
            'completed_at' => now()->toISOString(),
            'version' => '1.0.0',
        ]));
    }

    /**
     * Create installation backup point
     */
    private function createInstallationBackup(): string
    {
        $backupId = 'install_' . now()->format('Y_m_d_H_i_s') . '_' . uniqid();
        $backupPath = storage_path('app/installation_backups/' . $backupId);
        
        // Create backup directory
        if (!file_exists($backupPath)) {
            mkdir($backupPath, 0755, true);
        }

        // Store current database state
        $this->backupDatabaseState($backupPath);
        
        // Store current .env file
        $envPath = base_path('.env');
        if (file_exists($envPath)) {
            copy($envPath, $backupPath . '/.env.backup');
        }

        return $backupId;
    }

    /**
     * Backup current database state
     */
    private function backupDatabaseState(string $backupPath): void
    {
        try {
            // Get list of existing tables
            $tables = DB::select('SHOW TABLES');
            $tableList = [];
            
            foreach ($tables as $table) {
                $tableName = array_values((array) $table)[0];
                $tableList[] = $tableName;
            }

            // Store table list
            file_put_contents(
                $backupPath . '/tables.json', 
                json_encode($tableList, JSON_PRETTY_PRINT)
            );

            // Store database schema
            $schema = [];
            foreach ($tableList as $tableName) {
                $createTable = DB::select("SHOW CREATE TABLE `{$tableName}`");
                $schema[$tableName] = $createTable[0]->{'Create Table'};
            }

            file_put_contents(
                $backupPath . '/schema.sql', 
                json_encode($schema, JSON_PRETTY_PRINT)
            );

        } catch (\Exception $e) {
            Log::warning('Failed to backup database state', [
                'error' => $e->getMessage(),
                'backup_path' => $backupPath
            ]);
        }
    }

    /**
     * Rollback to backup point
     */
    private function rollbackToBackup(string $backupId): void
    {
        $backupPath = storage_path('app/installation_backups/' . $backupId);
        
        if (!file_exists($backupPath)) {
            Log::error('Backup not found for rollback', ['backup_id' => $backupId]);
            return;
        }

        try {
            // Restore .env file
            $envBackup = $backupPath . '/.env.backup';
            if (file_exists($envBackup)) {
                copy($envBackup, base_path('.env'));
            }

            // Drop all tables if they exist
            $tablesFile = $backupPath . '/tables.json';
            if (file_exists($tablesFile)) {
                $tables = json_decode(file_get_contents($tablesFile), true);
                foreach ($tables as $table) {
                    try {
                        DB::statement("DROP TABLE IF EXISTS `{$table}`");
                    } catch (\Exception $e) {
                        Log::warning("Failed to drop table {$table}", ['error' => $e->getMessage()]);
                    }
                }
            }

            Log::info('Installation rollback completed', ['backup_id' => $backupId]);

        } catch (\Exception $e) {
            Log::error('Rollback failed', [
                'error' => $e->getMessage(),
                'backup_id' => $backupId
            ]);
        }
    }

    /**
     * Clean up backup files
     */
    private function cleanupBackup(string $backupId): void
    {
        $backupPath = storage_path('app/installation_backups/' . $backupId);
        
        if (file_exists($backupPath)) {
            $this->deleteDirectory($backupPath);
        }
    }

    /**
     * Recursively delete directory
     */
    private function deleteDirectory(string $dir): bool
    {
        if (!is_dir($dir)) {
            return false;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            is_dir($path) ? $this->deleteDirectory($path) : unlink($path);
        }

        return rmdir($dir);
    }
}
