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

            // Run migrations with timeout handling
            Log::info('Starting database migrations...');
            
            // Set longer timeout for migrations
            set_time_limit(300); // 5 minutes
            ini_set('max_execution_time', 300);
            
            // Disable output buffering to prevent timeouts
            if (ob_get_level()) {
                ob_end_flush();
            }
            
            $this->runMigrations();
            Log::info('Database migrations completed successfully');
            
            // Store database setup completion in session
            session(['database_configured' => true]);
            
            return redirect()->route('installation.admin')->with('success', 'Database configured successfully!');

        } catch (\Exception $e) {
            Log::error('Database configuration failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withErrors(['database' => 'Database configuration failed: ' . $e->getMessage()])->withInput();
        }
    }



    /**
     * Show system data setup page
     */
    public function setupData(): Response|RedirectResponse
    {
        if ($this->isInstalled()) {
            return redirect()->route('login');
        }

        // Check if database is configured
        if (!session('database_configured', false)) {
            return redirect()->route('installation.database');
        }

        // Check if system data is already created
        if (session('system_data_created', false)) {
            return redirect()->route('installation.admin');
        }

        return Inertia::render('installation/SetupData');
    }

    /**
     * Handle system data creation
     */
    public function createSystemData(): RedirectResponse
    {
        if ($this->isInstalled()) {
            return redirect()->route('login');
        }

        // Check if database is configured
        if (!session('database_configured', false)) {
            return redirect()->route('installation.database');
        }

        // Check if system data is already created
        if (session('system_data_created', false)) {
            return redirect()->route('installation.admin');
        }

        try {
            // Set timeout for data creation
            set_time_limit(300); // 5 minutes
            ini_set('max_execution_time', 300);
            
            // Disable output buffering
            if (ob_get_level()) {
                ob_end_flush();
            }

            Log::info('Creating essential system data...');
            $this->createEssentialSystemData();
            Log::info('Essential system data created successfully');

            // Mark system data as created
            session(['system_data_created' => true]);

            return redirect()->route('installation.admin')->with('success', 'System data created successfully!');

        } catch (\Exception $e) {
            Log::error('Failed to create essential system data', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('installation.setup-data')->withErrors([
                'setup' => 'System setup failed: ' . $e->getMessage()
            ]);
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

        // Check if database is configured
        if (!session('database_configured', false)) {
            return redirect()->route('installation.database');
        }

        // Check if system data is created
        if (!session('system_data_created', false)) {
            return redirect()->route('installation.setup-data');
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
            // Create admin user and clinic
            $result = $this->createAdminUserAndClinic($request->all());

            if (!$result['success']) {
                return back()->withErrors(['admin' => $result['message']])->withInput();
            }

            // Mark installation as complete
            $this->markInstallationComplete();

            // Log successful installation
            Log::info('MediNext installation completed successfully', [
                'admin_email' => $request->email,
                'clinic_name' => $request->clinic_name,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return redirect()->route('installation.complete');

        } catch (\Exception $e) {
            Log::error('Admin creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
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
        // Run migrations with optimized settings
        Artisan::call('migrate', [
            '--force' => true,
            '--no-interaction' => true,
            '--quiet' => true
        ]);
        
        // Clear any cached configurations
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
    }

    /**
     * Create essential system data using SQL script
     */
    private function createEssentialSystemData(): void
    {
        try {
            Log::info('Starting essential system data creation using SQL script...');

            // Check if data already exists
            if (\App\Models\Permission::count() > 0) {
                Log::info('System data already exists, skipping creation');
                return;
            }

            // Execute SQL script
            $this->executeInitialDataScript();
            Log::info('Initial data script executed successfully');

            // Finalize setup
            Log::info('Finalizing system setup...');
            $this->finalizeSetup();
            Log::info('System setup finalized');

            Log::info('Essential system data created successfully');

        } catch (\Exception $e) {
            Log::error('Essential system data creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new \Exception('System setup failed: ' . $e->getMessage());
        }
    }

    /**
     * Execute initial data SQL script
     */
    private function executeInitialDataScript(): void
    {
        try {
            Log::info('Creating system data using BaseSeeder installation seeding...');
            
            // Use the BaseSeeder installation seeding method (core system only)
            $seeder = new \Database\Seeders\BaseSeeder();
            $seeder->runInstallationSeeding();
            
            Log::info('System data created successfully using BaseSeeder installation seeding');
            
        } catch (\Exception $e) {
            Log::error('Failed to create system data using BaseSeeder', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Fallback to SQL script if seeder fails
            Log::info('Falling back to SQL script execution...');
            $this->executeSQLScript();
        }
    }
    
    /**
     * Execute SQL script as fallback
     */
    private function executeSQLScript(): void
    {
        $sqlFile = database_path('sql/initial_data.sql');
        
        if (!file_exists($sqlFile)) {
            throw new \Exception('Initial data SQL script not found: ' . $sqlFile);
        }

        $sql = file_get_contents($sqlFile);
        
        if (empty($sql)) {
            throw new \Exception('Initial data SQL script is empty');
        }

        // Remove comments and split by semicolon more carefully
        $sql = preg_replace('/--.*$/m', '', $sql); // Remove line comments
        $sql = preg_replace('/\/\*.*?\*\//s', '', $sql); // Remove block comments
        
        // Split by semicolon, but be more careful about it
        $statements = array_filter(
            array_map('trim', preg_split('/;(?=(?:[^\']*\'[^\']*\')*[^\']*$)/', $sql)),
            function($statement) {
                return !empty($statement) && strlen(trim($statement)) > 0;
            }
        );

        Log::info('Executing ' . count($statements) . ' SQL statements from initial data script');

        foreach ($statements as $index => $statement) {
            $statement = trim($statement);
            if (empty($statement)) continue;
            
            try {
                DB::statement($statement);
                Log::debug("Executed SQL statement " . ($index + 1) . " successfully");
            } catch (\Exception $e) {
                Log::error("Failed to execute SQL statement " . ($index + 1), [
                    'statement' => substr($statement, 0, 200) . '...',
                    'error' => $e->getMessage()
                ]);
                throw new \Exception("SQL execution failed at statement " . ($index + 1) . ": " . $e->getMessage());
            }
        }

        Log::info('All SQL statements executed successfully');
    }


    /**
     * Get permissions data (minimal set for faster installation)
     */
    private function getPermissionsData(): array
    {
        return [
            // Essential permissions only - minimal set for faster installation
            ['name' => 'System Admin', 'slug' => 'system.admin', 'description' => 'System administration access', 'module' => 'system', 'action' => 'admin'],
            ['name' => 'Manage Users', 'slug' => 'users.manage', 'description' => 'Full control over user operations', 'module' => 'users', 'action' => 'manage'],
            ['name' => 'View Users', 'slug' => 'users.view', 'description' => 'View user information', 'module' => 'users', 'action' => 'view'],
            ['name' => 'Create Users', 'slug' => 'users.create', 'description' => 'Create new users', 'module' => 'users', 'action' => 'create'],
            ['name' => 'Edit Users', 'slug' => 'users.edit', 'description' => 'Edit user information', 'module' => 'users', 'action' => 'edit'],
            ['name' => 'Delete Users', 'slug' => 'users.delete', 'description' => 'Remove users', 'module' => 'users', 'action' => 'delete'],
            
            ['name' => 'Manage Clinics', 'slug' => 'clinics.manage', 'description' => 'Full control over clinic operations', 'module' => 'clinics', 'action' => 'manage'],
            ['name' => 'View Clinics', 'slug' => 'clinics.view', 'description' => 'View clinic information', 'module' => 'clinics', 'action' => 'view'],
            ['name' => 'Create Clinics', 'slug' => 'clinics.create', 'description' => 'Create new clinics', 'module' => 'clinics', 'action' => 'create'],
            ['name' => 'Edit Clinics', 'slug' => 'clinics.edit', 'description' => 'Edit clinic information', 'module' => 'clinics', 'action' => 'edit'],
            ['name' => 'Delete Clinics', 'slug' => 'clinics.delete', 'description' => 'Delete clinics', 'module' => 'clinics', 'action' => 'delete'],

            ['name' => 'Manage Patients', 'slug' => 'patients.manage', 'description' => 'Full control over patient operations', 'module' => 'patients', 'action' => 'manage'],
            ['name' => 'View Patients', 'slug' => 'patients.view', 'description' => 'View patient information', 'module' => 'patients', 'action' => 'view'],
            ['name' => 'Create Patients', 'slug' => 'patients.create', 'description' => 'Add new patients', 'module' => 'patients', 'action' => 'create'],
            ['name' => 'Edit Patients', 'slug' => 'patients.edit', 'description' => 'Edit patient information', 'module' => 'patients', 'action' => 'edit'],
            ['name' => 'Delete Patients', 'slug' => 'patients.delete', 'description' => 'Remove patients', 'module' => 'patients', 'action' => 'delete'],

            ['name' => 'Manage Appointments', 'slug' => 'appointments.manage', 'description' => 'Full control over appointment operations', 'module' => 'appointments', 'action' => 'manage'],
            ['name' => 'View Appointments', 'slug' => 'appointments.view', 'description' => 'View appointment information', 'module' => 'appointments', 'action' => 'view'],
            ['name' => 'Create Appointments', 'slug' => 'appointments.create', 'description' => 'Schedule new appointments', 'module' => 'appointments', 'action' => 'create'],
            ['name' => 'Edit Appointments', 'slug' => 'appointments.edit', 'description' => 'Modify appointments', 'module' => 'appointments', 'action' => 'edit'],
            ['name' => 'Delete Appointments', 'slug' => 'appointments.delete', 'description' => 'Remove appointments', 'module' => 'appointments', 'action' => 'delete'],

            ['name' => 'View Dashboard', 'slug' => 'dashboard.view', 'description' => 'View dashboard', 'module' => 'dashboard', 'action' => 'view'],
            ['name' => 'Manage Settings', 'slug' => 'settings.manage', 'description' => 'Manage system settings', 'module' => 'settings', 'action' => 'manage'],
            ['name' => 'View Settings', 'slug' => 'settings.view', 'description' => 'View system settings', 'module' => 'settings', 'action' => 'view'],
            ['name' => 'Delete Patients', 'slug' => 'patients.delete', 'description' => 'Remove patients', 'module' => 'patients', 'action' => 'delete'],

            // Appointment permissions
            ['name' => 'Manage Appointments', 'slug' => 'appointments.manage', 'description' => 'Full control over appointment operations', 'module' => 'appointments', 'action' => 'manage'],
            ['name' => 'View Appointments', 'slug' => 'appointments.view', 'description' => 'View appointment information', 'module' => 'appointments', 'action' => 'view'],
            ['name' => 'Create Appointments', 'slug' => 'appointments.create', 'description' => 'Schedule new appointments', 'module' => 'appointments', 'action' => 'create'],
            ['name' => 'Edit Appointments', 'slug' => 'appointments.edit', 'description' => 'Modify appointments', 'module' => 'appointments', 'action' => 'edit'],
            ['name' => 'Cancel Appointments', 'slug' => 'appointments.cancel', 'description' => 'Cancel appointments', 'module' => 'appointments', 'action' => 'cancel'],
            ['name' => 'Delete Appointments', 'slug' => 'appointments.delete', 'description' => 'Remove appointments', 'module' => 'appointments', 'action' => 'delete'],
            ['name' => 'Check-in Patients', 'slug' => 'appointments.checkin', 'description' => 'Check-in patients for appointments', 'module' => 'appointments', 'action' => 'checkin'],

            // Prescription permissions
            ['name' => 'Manage Prescriptions', 'slug' => 'prescriptions.manage', 'description' => 'Full control over prescription operations', 'module' => 'prescriptions', 'action' => 'manage'],
            ['name' => 'View Prescriptions', 'slug' => 'prescriptions.view', 'description' => 'View prescription information', 'module' => 'prescriptions', 'action' => 'view'],
            ['name' => 'Create Prescriptions', 'slug' => 'prescriptions.create', 'description' => 'Write new prescriptions', 'module' => 'prescriptions', 'action' => 'create'],
            ['name' => 'Edit Prescriptions', 'slug' => 'prescriptions.edit', 'description' => 'Modify prescriptions', 'module' => 'prescriptions', 'action' => 'edit'],
            ['name' => 'Delete Prescriptions', 'slug' => 'prescriptions.delete', 'description' => 'Remove prescriptions', 'module' => 'prescriptions', 'action' => 'delete'],
            ['name' => 'Download Prescriptions', 'slug' => 'prescriptions.download', 'description' => 'Download prescription PDFs', 'module' => 'prescriptions', 'action' => 'download'],

            // Medical Records permissions
            ['name' => 'Manage Medical Records', 'slug' => 'medical_records.manage', 'description' => 'Full control over medical records', 'module' => 'medical_records', 'action' => 'manage'],
            ['name' => 'View Medical Records', 'slug' => 'medical_records.view', 'description' => 'View patient medical records', 'module' => 'medical_records', 'action' => 'view'],
            ['name' => 'Create Medical Records', 'slug' => 'medical_records.create', 'description' => 'Create new medical records', 'module' => 'medical_records', 'action' => 'create'],
            ['name' => 'Edit Medical Records', 'slug' => 'medical_records.edit', 'description' => 'Modify medical records', 'module' => 'medical_records', 'action' => 'edit'],
            ['name' => 'Delete Medical Records', 'slug' => 'medical_records.delete', 'description' => 'Delete medical records', 'module' => 'medical_records', 'action' => 'delete'],

            // User management permissions
            ['name' => 'Manage Users', 'slug' => 'users.manage', 'description' => 'Full control over user operations', 'module' => 'users', 'action' => 'manage'],
            ['name' => 'View Users', 'slug' => 'users.view', 'description' => 'View user information', 'module' => 'users', 'action' => 'view'],
            ['name' => 'Create Users', 'slug' => 'users.create', 'description' => 'Create new users', 'module' => 'users', 'action' => 'create'],
            ['name' => 'Edit Users', 'slug' => 'users.edit', 'description' => 'Edit user information', 'module' => 'users', 'action' => 'edit'],
            ['name' => 'Delete Users', 'slug' => 'users.delete', 'description' => 'Remove users', 'module' => 'users', 'action' => 'delete'],

            // Role management permissions
            ['name' => 'Manage Roles', 'slug' => 'roles.manage', 'description' => 'Full control over role operations', 'module' => 'roles', 'action' => 'manage'],
            ['name' => 'View Roles', 'slug' => 'roles.view', 'description' => 'View role information', 'module' => 'roles', 'action' => 'view'],
            ['name' => 'Create Roles', 'slug' => 'roles.create', 'description' => 'Create new roles', 'module' => 'roles', 'action' => 'create'],
            ['name' => 'Edit Roles', 'slug' => 'roles.edit', 'description' => 'Edit role information', 'module' => 'roles', 'action' => 'edit'],
            ['name' => 'Delete Roles', 'slug' => 'roles.delete', 'description' => 'Remove roles', 'module' => 'roles', 'action' => 'delete'],

            // Settings permissions
            ['name' => 'Manage Settings', 'slug' => 'settings.manage', 'description' => 'Manage system settings', 'module' => 'settings', 'action' => 'manage'],
            ['name' => 'View Settings', 'slug' => 'settings.view', 'description' => 'View system settings', 'module' => 'settings', 'action' => 'view'],

            // Dashboard permissions
            ['name' => 'View Dashboard', 'slug' => 'dashboard.view', 'description' => 'View dashboard', 'module' => 'dashboard', 'action' => 'view'],
            ['name' => 'View Dashboard Stats', 'slug' => 'dashboard.stats', 'description' => 'View dashboard statistics', 'module' => 'dashboard', 'action' => 'stats'],

            // System permissions
            ['name' => 'System Admin', 'slug' => 'system.admin', 'description' => 'System administration access', 'module' => 'system', 'action' => 'admin'],
            ['name' => 'System Info', 'slug' => 'system.info', 'description' => 'View system information', 'module' => 'system', 'action' => 'info'],

            // MedRep permissions
            ['name' => 'Manage MedRep Visits', 'slug' => 'medrep_visits.manage', 'description' => 'Full control over medrep visit operations', 'module' => 'medrep', 'action' => 'manage'],
            ['name' => 'View MedRep Visits', 'slug' => 'medrep_visits.view', 'description' => 'View medrep visit information', 'module' => 'medrep', 'action' => 'view'],
            ['name' => 'View Interactions', 'slug' => 'interactions.view', 'description' => 'View interaction information', 'module' => 'medrep', 'action' => 'view'],
            ['name' => 'View Products', 'slug' => 'products.view', 'description' => 'View product information', 'module' => 'medrep', 'action' => 'view'],
            ['name' => 'Create Meetings', 'slug' => 'meetings.create', 'description' => 'Schedule new meetings', 'module' => 'medrep', 'action' => 'create'],

            // Reports permissions
            ['name' => 'View Reports', 'slug' => 'reports.view', 'description' => 'View reports and analytics', 'module' => 'reports', 'action' => 'view'],

            // Profile permissions
            ['name' => 'View Profile', 'slug' => 'profile.view', 'description' => 'View user profile information', 'module' => 'profile', 'action' => 'view'],

            // Billing permissions
            ['name' => 'View Billing', 'slug' => 'billing.view', 'description' => 'View billing information', 'module' => 'billing', 'action' => 'view'],

            // File Assets permissions
            ['name' => 'Download File Assets', 'slug' => 'file_assets.download', 'description' => 'Download file assets and documents', 'module' => 'file_assets', 'action' => 'download'],

            // Encounters permissions
            ['name' => 'View Encounters', 'slug' => 'encounters.view', 'description' => 'View encounter information', 'module' => 'encounters', 'action' => 'view'],

            // Insurance permissions
            ['name' => 'View Insurance', 'slug' => 'insurance.view', 'description' => 'View insurance information', 'module' => 'insurance', 'action' => 'view'],

            // Lab Results permissions
            ['name' => 'View Lab Results', 'slug' => 'lab_results.view', 'description' => 'View lab results', 'module' => 'lab_results', 'action' => 'view'],

            // Notifications permissions
            ['name' => 'View Notifications', 'slug' => 'notifications.view', 'description' => 'View notifications', 'module' => 'notifications', 'action' => 'view'],

            // Queue permissions
            ['name' => 'Manage Queue', 'slug' => 'queue.manage', 'description' => 'Full control over queue operations', 'module' => 'queue', 'action' => 'manage'],
            ['name' => 'View Queue', 'slug' => 'queue.view', 'description' => 'View queue information', 'module' => 'queue', 'action' => 'view'],

            // Room permissions
            ['name' => 'Manage Rooms', 'slug' => 'rooms.manage', 'description' => 'Full control over room operations', 'module' => 'rooms', 'action' => 'manage'],
            ['name' => 'View Rooms', 'slug' => 'rooms.view', 'description' => 'View room information', 'module' => 'rooms', 'action' => 'view'],
            ['name' => 'Create Rooms', 'slug' => 'rooms.create', 'description' => 'Create new rooms', 'module' => 'rooms', 'action' => 'create'],
            ['name' => 'Edit Rooms', 'slug' => 'rooms.edit', 'description' => 'Edit room information', 'module' => 'rooms', 'action' => 'edit'],
            ['name' => 'Delete Rooms', 'slug' => 'rooms.delete', 'description' => 'Delete rooms', 'module' => 'rooms', 'action' => 'delete'],

            // Schedule permissions
            ['name' => 'Manage Schedules', 'slug' => 'schedules.manage', 'description' => 'Full control over schedule operations', 'module' => 'schedules', 'action' => 'manage'],
            ['name' => 'View Schedules', 'slug' => 'schedules.view', 'description' => 'View schedule information', 'module' => 'schedules', 'action' => 'view'],
            ['name' => 'Create Schedules', 'slug' => 'schedules.create', 'description' => 'Create new schedules', 'module' => 'schedules', 'action' => 'create'],
            ['name' => 'Edit Schedules', 'slug' => 'schedules.edit', 'description' => 'Edit schedule information', 'module' => 'schedules', 'action' => 'edit'],
            ['name' => 'Delete Schedules', 'slug' => 'schedules.delete', 'description' => 'Delete schedules', 'module' => 'schedules', 'action' => 'delete'],

            // System monitoring permissions
            ['name' => 'View System Status', 'slug' => 'system.status', 'description' => 'View system status and monitoring', 'module' => 'system', 'action' => 'status'],
            ['name' => 'View Activity Logs', 'slug' => 'activity_logs.view', 'description' => 'View system activity logs', 'module' => 'activity_logs', 'action' => 'view'],
            ['name' => 'Export Activity Logs', 'slug' => 'activity_logs.export', 'description' => 'Export system activity logs', 'module' => 'activity_logs', 'action' => 'export'],
            ['name' => 'Manage Backups', 'slug' => 'backups.manage', 'description' => 'Create and manage system backups', 'module' => 'backups', 'action' => 'manage'],
            ['name' => 'System Monitor', 'slug' => 'system.monitor', 'description' => 'Monitor system performance and health', 'module' => 'system', 'action' => 'monitor'],

            // User management additional permissions
            ['name' => 'Activate Users', 'slug' => 'users.activate', 'description' => 'Activate user accounts', 'module' => 'users', 'action' => 'activate'],
            ['name' => 'Deactivate Users', 'slug' => 'users.deactivate', 'description' => 'Deactivate user accounts', 'module' => 'users', 'action' => 'deactivate'],

            // Permission management
            ['name' => 'Manage Permissions', 'slug' => 'permissions.manage', 'description' => 'Full control over permission operations', 'module' => 'permissions', 'action' => 'manage'],
            ['name' => 'View Permissions', 'slug' => 'permissions.view', 'description' => 'View permission information', 'module' => 'permissions', 'action' => 'view'],
            ['name' => 'Create Permissions', 'slug' => 'permissions.create', 'description' => 'Create new permissions', 'module' => 'permissions', 'action' => 'create'],
            ['name' => 'Edit Permissions', 'slug' => 'permissions.edit', 'description' => 'Edit permission information', 'module' => 'permissions', 'action' => 'edit'],
            ['name' => 'Delete Permissions', 'slug' => 'permissions.delete', 'description' => 'Delete permissions', 'module' => 'permissions', 'action' => 'delete'],

            // File assets additional permissions
            ['name' => 'Upload File Assets', 'slug' => 'file_assets.upload', 'description' => 'Upload file assets and documents', 'module' => 'file_assets', 'action' => 'upload'],

            // Encounters additional permissions
            ['name' => 'Complete Encounters', 'slug' => 'encounters.complete', 'description' => 'Complete encounter sessions', 'module' => 'encounters', 'action' => 'complete'],

            // Queue additional permissions
            ['name' => 'Add to Queue', 'slug' => 'queue.add', 'description' => 'Add patients to queue', 'module' => 'queue', 'action' => 'add'],
            ['name' => 'Remove from Queue', 'slug' => 'queue.remove', 'description' => 'Remove patients from queue', 'module' => 'queue', 'action' => 'remove'],
            ['name' => 'Process Queue', 'slug' => 'queue.process', 'description' => 'Process queue items', 'module' => 'queue', 'action' => 'process'],

            // Search permissions
            ['name' => 'Search Patients', 'slug' => 'search.patients', 'description' => 'Search patient records', 'module' => 'search', 'action' => 'patients'],
            ['name' => 'Search Doctors', 'slug' => 'search.doctors', 'description' => 'Search doctor records', 'module' => 'search', 'action' => 'doctors'],
            ['name' => 'Global Search', 'slug' => 'search.global', 'description' => 'Perform global searches', 'module' => 'search', 'action' => 'global'],
        ];
    }

    /**
     * Create user roles (deprecated - now handled by SQL script)
     */
    private function createRoles(): void
    {
        // Check if roles already exist
        if (\App\Models\Role::count() > 0) {
            Log::info('Roles already exist, skipping creation');
            return;
        }

        $roles = [
            'superadmin' => [
                'description' => 'Full system access and management. Can manage all clinics, users, and system settings.',
                'is_system_role' => true,
                'permissions' => ['*'] // All permissions
            ],
            'admin' => [
                'description' => 'Full clinic access and management. Can manage clinic operations, staff, and patients.',
                'is_system_role' => false,
                'permissions' => [
                    'clinics.manage', 'clinics.view', 'clinics.edit',
                    'users.manage', 'users.view', 'users.create', 'users.edit', 'users.delete',
                    'roles.view', 'roles.create', 'roles.edit',
                    'doctors.manage', 'doctors.view', 'doctors.create', 'doctors.edit', 'doctors.delete',
                    'staff.manage', 'staff.view', 'staff.create', 'staff.edit', 'staff.delete',
                    'patients.manage', 'patients.view', 'patients.create', 'patients.edit', 'patients.delete',
                    'appointments.manage', 'appointments.view', 'appointments.create', 'appointments.edit', 'appointments.cancel', 'appointments.delete', 'appointments.checkin',
                    'prescriptions.manage', 'prescriptions.view', 'prescriptions.create', 'prescriptions.edit', 'prescriptions.delete', 'prescriptions.download',
                    'medical_records.manage', 'medical_records.view', 'medical_records.create', 'medical_records.edit', 'medical_records.delete',
                    'settings.manage', 'settings.view',
                    'dashboard.view', 'dashboard.stats',
                    // Reports permissions
                    'reports.view',
                    // Profile permissions
                    'profile.view',
                    // Billing permissions
                    'billing.view',
                    // File Assets permissions
                    'file_assets.download',
                    // Encounters permissions
                    'encounters.view',
                    // Insurance permissions
                    'insurance.view',
                    // Lab Results permissions
                    'lab_results.view',
                    // Notifications permissions
                    'notifications.view',
                    // Queue permissions
                    'queue.manage', 'queue.view',
                    // Room permissions
                    'rooms.manage', 'rooms.view', 'rooms.create', 'rooms.edit', 'rooms.delete',
                    // Schedule permissions
                    'schedules.manage', 'schedules.view', 'schedules.create', 'schedules.edit', 'schedules.delete',
                    // System monitoring permissions
                    'system.status', 'activity_logs.view', 'activity_logs.export', 'backups.manage', 'system.monitor',
                    // User management additional permissions
                    'users.activate', 'users.deactivate',
                    // Permission management
                    'permissions.manage', 'permissions.view', 'permissions.create', 'permissions.edit', 'permissions.delete',
                    // File assets additional permissions
                    'file_assets.upload',
                    // Encounters additional permissions
                    'encounters.complete',
                    // Queue additional permissions
                    'queue.add', 'queue.remove', 'queue.process',
                    // Search permissions
                    'search.patients', 'search.doctors', 'search.global',
                ]
            ],
            'doctor' => [
                'description' => 'Medical professional who can manage appointments, medical records, and prescriptions.',
                'is_system_role' => false,
                'permissions' => [
                    'clinics.view', 'doctors.view',
                    'patients.view', 'patients.edit',
                    'appointments.view', 'appointments.create', 'appointments.edit', 'appointments.cancel',
                    'prescriptions.view', 'prescriptions.create', 'prescriptions.edit', 'prescriptions.delete', 'prescriptions.download',
                    'medical_records.view', 'medical_records.create', 'medical_records.edit',
                    'dashboard.view', 'dashboard.stats',
                    // Profile permissions
                    'profile.view',
                    // Encounters permissions
                    'encounters.view',
                    // Lab Results permissions
                    'lab_results.view',
                    // Queue permissions
                    'queue.view',
                ]
            ],
            'receptionist' => [
                'description' => 'Front desk staff who can schedule appointments, manage patient check-ins, and handle basic operations.',
                'is_system_role' => false,
                'permissions' => [
                    'clinics.view', 'doctors.view',
                    'patients.view', 'patients.create', 'patients.edit',
                    'appointments.view', 'appointments.create', 'appointments.edit', 'appointments.cancel', 'appointments.checkin',
                    'dashboard.view',
                    // Profile permissions
                    'profile.view',
                    // Encounters permissions
                    'encounters.view',
                    // Insurance permissions
                    'insurance.view',
                    // Queue permissions
                    'queue.view', 'queue.add', 'queue.remove', 'queue.process',
                    // Room permissions
                    'rooms.view',
                    // Schedule permissions
                    'schedules.view',
                    // Search permissions
                    'search.patients', 'search.doctors',
                ]
            ],
            'patient' => [
                'description' => 'Patient who can book appointments, view records, and download prescriptions.',
                'is_system_role' => false,
                'permissions' => [
                    'clinics.view', 'doctors.view',
                    'appointments.view', 'appointments.create', 'appointments.cancel',
                    'prescriptions.view', 'prescriptions.download',
                    'medical_records.view',
                    'dashboard.view',
                    // Schedule permissions
                    'schedules.view',
                    // Profile permissions
                    'profile.view',
                    // Billing permissions
                    'billing.view',
                    // File Assets permissions
                    'file_assets.download',
                    // Encounters permissions
                    'encounters.view',
                    // Insurance permissions
                    'insurance.view',
                    // Lab Results permissions
                    'lab_results.view',
                    // Notifications permissions
                    'notifications.view',
                ]
            ],
            'medrep' => [
                'description' => 'Medical representative who can manage visits, interactions, and product information.',
                'is_system_role' => false,
                'permissions' => [
                    'dashboard.view', 'dashboard.stats',
                    // MedRep permissions
                    'medrep_visits.manage', 'medrep_visits.view', 'interactions.view', 'products.view', 'meetings.create',
                    // Doctor permissions (required for medrep routes)
                    'doctors.view',
                    // Schedule permissions
                    'schedules.view',
                    // Reports permissions
                    'reports.view',
                    // Profile permissions
                    'profile.view',
                ]
            ],
        ];

        foreach ($roles as $roleName => $roleData) {
            $role = \App\Models\Role::firstOrCreate(
                ['name' => $roleName],
                [
                    'description' => $roleData['description'],
                    'is_system_role' => $roleData['is_system_role'],
                    'permissions_config' => $roleData['permissions']
                ]
            );

            // Assign permissions to role
            if (isset($roleData['permissions']) && is_array($roleData['permissions'])) {
                if (in_array('*', $roleData['permissions'])) {
                    // Superadmin gets all permissions
                    $permissions = \App\Models\Permission::all();
                } else {
                    $permissions = \App\Models\Permission::whereIn('slug', $roleData['permissions'])->get();
                }
                $role->permissions()->sync($permissions->pluck('id'));
            }
        }
    }

    /**
     * Create default settings in batches (deprecated - now handled by SQL script)
     */
    private function createDefaultSettingsInBatches(): void
    {
        // Check if settings already exist
        if (\App\Models\Setting::count() > 0) {
            Log::info('Settings already exist, skipping creation');
            return;
        }

        $settings = $this->getDefaultSettingsData();
        $batchSize = 15; // Process 15 settings at a time
        $batches = array_chunk($settings, $batchSize);

        foreach ($batches as $index => $batch) {
            Log::info("Processing settings batch " . ($index + 1) . " of " . count($batches));
            
            $settingData = [];
            $now = now();

            foreach ($batch as $setting) {
                $settingData[] = array_merge($setting, [
                    'clinic_id' => null, // Global settings
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            \App\Models\Setting::insert($settingData);
            
            // Small delay to prevent overwhelming the database
            usleep(100000); // 0.1 second delay
        }
    }

    /**
     * Get default settings data
     */
    private function getDefaultSettingsData(): array
    {
        return [
            // Essential settings only - minimal set for faster installation
            ['key' => 'system.name', 'value' => 'MediNext EMR', 'type' => 'string', 'group' => 'system', 'description' => 'System name', 'is_public' => true],
            ['key' => 'system.version', 'value' => '1.0.0', 'type' => 'string', 'group' => 'system', 'description' => 'System version', 'is_public' => false],
            ['key' => 'system.timezone', 'value' => 'UTC', 'type' => 'string', 'group' => 'system', 'description' => 'Default timezone', 'is_public' => true],
            ['key' => 'system.date_format', 'value' => 'Y-m-d', 'type' => 'string', 'group' => 'system', 'description' => 'Date format', 'is_public' => true],
            ['key' => 'security.session_timeout', 'value' => '120', 'type' => 'integer', 'group' => 'security', 'description' => 'Session timeout in minutes', 'is_public' => false],
            ['key' => 'appointments.default_duration', 'value' => '30', 'type' => 'integer', 'group' => 'appointments', 'description' => 'Default appointment duration in minutes', 'is_public' => true],
            ['key' => 'performance.query_logging', 'value' => 'false', 'type' => 'boolean', 'group' => 'performance', 'description' => 'Enable query logging', 'is_public' => false],
        ];
    }

    /**
     * Setup activity logging (deprecated - now handled by SQL script)
     */
    private function setupActivityLogging(): void
    {
        // Create initial activity log entry for system setup
        \App\Models\ActivityLog::create([
            'log_name' => 'system',
            'description' => 'System installation completed',
            'subject_type' => 'system',
            'subject_id' => 1,
            'causer_type' => 'system',
            'causer_id' => null,
            'properties' => json_encode([
                'version' => '1.0.0',
                'installation_date' => now()->toISOString(),
            ]),
        ]);
    }

    /**
     * Finalize system setup
     */
    private function finalizeSetup(): void
    {
        // Validate system setup
        $this->validateSystemSetup();
        
        // Create any final system configurations
        // This could include creating default file directories, cache clearing, etc.
        
        // Clear any cached configurations
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        
        Log::info('System setup finalized successfully');
    }

    /**
     * Validate system setup
     */
    private function validateSystemSetup(): void
    {
        Log::info('Validating system setup...');
        
        // Check if permissions exist
        $permissionCount = \App\Models\Permission::count();
        Log::info("Found {$permissionCount} permissions in database");
        
        if ($permissionCount === 0) {
            throw new \Exception('No permissions found in database. System data creation may have failed.');
        }
        
        // Check if roles exist
        $roleCount = \App\Models\Role::count();
        Log::info("Found {$roleCount} roles in database");
        
        if ($roleCount === 0) {
            throw new \Exception('No roles found in database. System data creation may have failed.');
        }
        
        // Check if superadmin role exists
        $superadminRole = \App\Models\Role::where('name', 'superadmin')->first();
        if (!$superadminRole) {
            $availableRoles = \App\Models\Role::pluck('name')->toArray();
            Log::error('Superadmin role not found. Available roles: ' . implode(', ', $availableRoles));
            throw new \Exception('Superadmin role not created. Available roles: ' . implode(', ', $availableRoles));
        }
        
        Log::info('Superadmin role found with ID: ' . $superadminRole->id);

        // Check if superadmin has permissions
        $superadminPermissions = $superadminRole->permissions()->count();
        Log::info("Superadmin role has {$superadminPermissions} permissions assigned");
        
        if ($superadminPermissions === 0) {
            throw new \Exception('Superadmin role has no permissions assigned');
        }
        
        // Check if role_permissions table has data
        $rolePermissionCount = DB::table('role_permissions')->count();
        Log::info("Found {$rolePermissionCount} role-permission assignments");
        
        if ($rolePermissionCount === 0) {
            throw new \Exception('No role-permission assignments found. Permission assignment may have failed.');
        }

        // Check if essential permissions exist
        $essentialPermissions = [
            'system.admin',
            'users.manage',
            'clinics.manage',
            'patients.manage',
            'appointments.manage'
        ];

        foreach ($essentialPermissions as $permissionSlug) {
            $permission = \App\Models\Permission::where('slug', $permissionSlug)->first();
            if (!$permission) {
                throw new \Exception("Essential permission '{$permissionSlug}' not found");
            }
        }

        // Check if essential settings exist
        $essentialSettings = [
            'system.name',
            'system.version',
            'security.session_timeout',
            'appointments.default_duration'
        ];

        foreach ($essentialSettings as $settingKey) {
            $setting = \App\Models\Setting::where('key', $settingKey)->first();
            if (!$setting) {
                throw new \Exception("Essential setting '{$settingKey}' not found");
            }
        }

        Log::info('System setup validation completed successfully');
    }

    /**
     * Create admin user and clinic
     */
    private function createAdminUserAndClinic(array $data): array
    {
        try {
            DB::beginTransaction();

            // Get the superadmin role (created by base seeder)
            $superadminRole = Role::where('name', 'superadmin')->first();
            if (!$superadminRole) {
                throw new \Exception('Superadmin role not found. Please ensure the seeding process completed successfully.');
            }

            // Step 1: Create admin user
            $adminUser = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'phone' => $data['clinic_phone'],
                'is_active' => true,
                'email_verified_at' => now(),
                'onboarding_completed_at' => now(),
            ]);

            // Step 2: Create clinic
            $clinic = Clinic::create([
                'name' => $data['clinic_name'],
                'phone' => $data['clinic_phone'],
                'email' => $data['clinic_email'],
                'address' => $data['clinic_address'],
            ]);

            // Step 3: Assign user to clinic with superadmin role
            $adminUser->userClinicRoles()->create([
                'clinic_id' => $clinic->id,
                'role_id' => $superadminRole->id,
            ]);

            // Step 4: Create default settings for the clinic
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

}
