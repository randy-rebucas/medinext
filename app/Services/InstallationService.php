<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Role;
use App\Models\Clinic;
use App\Models\Setting;
use App\Models\Permission;
use Database\Seeders\BaseSeeder;

class InstallationService
{
    /**
     * Run the complete installation process
     */
    public function runInstallation(array $config): array
    {
        try {
            DB::beginTransaction();

            // Step 1: Run migrations
            $this->runMigrations();

            // Step 2: Create core system data
            $this->createCoreSystemData();

            // Step 3: Create admin user
            $adminUser = $this->createAdminUser($config['admin']);

            // Step 4: Create clinic
            $clinic = $this->createClinic($config['clinic'], $adminUser->id);

            // Step 5: Assign user to clinic
            $this->assignUserToClinic($adminUser, $clinic);

            // Step 6: Create default settings
            $this->createDefaultSettings($clinic->id, $config);

            // Step 7: Run additional seeding
            $this->runAdditionalSeeding();

            // Step 8: Mark installation complete
            $this->markInstallationComplete();

            DB::commit();

            return [
                'success' => true,
                'message' => 'Installation completed successfully',
                'admin_user' => $adminUser,
                'clinic' => $clinic,
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Installation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'config' => $config
            ]);

            return [
                'success' => false,
                'message' => 'Installation failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Run database migrations
     */
    private function runMigrations(): void
    {
        Artisan::call('migrate', ['--force' => true]);
    }

    /**
     * Create core system data (permissions, roles, settings)
     */
    private function createCoreSystemData(): void
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
     * Create system permissions
     */
    private function createPermissions(): void
    {
        $permissions = [
            // System permissions
            ['name' => 'System Administration', 'slug' => 'system.admin', 'description' => 'Full system administration access', 'module' => 'system', 'action' => 'admin'],
            ['name' => 'System Information', 'slug' => 'system.info', 'description' => 'View system information', 'module' => 'system', 'action' => 'info'],
            ['name' => 'System Status', 'slug' => 'system.status', 'description' => 'View system status', 'module' => 'system', 'action' => 'status'],
            ['name' => 'System Monitor', 'slug' => 'system.monitor', 'description' => 'Monitor system performance', 'module' => 'system', 'action' => 'monitor'],

            // Clinic permissions
            ['name' => 'View Clinics', 'slug' => 'clinics.view', 'description' => 'View clinic information', 'module' => 'clinics', 'action' => 'view'],
            ['name' => 'Create Clinics', 'slug' => 'clinics.create', 'description' => 'Create new clinics', 'module' => 'clinics', 'action' => 'create'],
            ['name' => 'Edit Clinics', 'slug' => 'clinics.edit', 'description' => 'Edit clinic information', 'module' => 'clinics', 'action' => 'edit'],
            ['name' => 'Delete Clinics', 'slug' => 'clinics.delete', 'description' => 'Delete clinics', 'module' => 'clinics', 'action' => 'delete'],
            ['name' => 'Manage Clinics', 'slug' => 'clinics.manage', 'description' => 'Full clinic management', 'module' => 'clinics', 'action' => 'manage'],

            // User permissions
            ['name' => 'View Users', 'slug' => 'users.view', 'description' => 'View user information', 'module' => 'users', 'action' => 'view'],
            ['name' => 'Create Users', 'slug' => 'users.create', 'description' => 'Create new users', 'module' => 'users', 'action' => 'create'],
            ['name' => 'Edit Users', 'slug' => 'users.edit', 'description' => 'Edit user information', 'module' => 'users', 'action' => 'edit'],
            ['name' => 'Delete Users', 'slug' => 'users.delete', 'description' => 'Delete users', 'module' => 'users', 'action' => 'delete'],
            ['name' => 'Activate Users', 'slug' => 'users.activate', 'description' => 'Activate user accounts', 'module' => 'users', 'action' => 'activate'],
            ['name' => 'Deactivate Users', 'slug' => 'users.deactivate', 'description' => 'Deactivate user accounts', 'module' => 'users', 'action' => 'deactivate'],

            // Role permissions
            ['name' => 'View Roles', 'slug' => 'roles.view', 'description' => 'View role information', 'module' => 'roles', 'action' => 'view'],
            ['name' => 'Create Roles', 'slug' => 'roles.create', 'description' => 'Create new roles', 'module' => 'roles', 'action' => 'create'],
            ['name' => 'Edit Roles', 'slug' => 'roles.edit', 'description' => 'Edit role information', 'module' => 'roles', 'action' => 'edit'],
            ['name' => 'Delete Roles', 'slug' => 'roles.delete', 'description' => 'Delete roles', 'module' => 'roles', 'action' => 'delete'],

            // Permission permissions
            ['name' => 'View Permissions', 'slug' => 'permissions.view', 'description' => 'View permission information', 'module' => 'permissions', 'action' => 'view'],
            ['name' => 'Create Permissions', 'slug' => 'permissions.create', 'description' => 'Create new permissions', 'module' => 'permissions', 'action' => 'create'],
            ['name' => 'Edit Permissions', 'slug' => 'permissions.edit', 'description' => 'Edit permission information', 'module' => 'permissions', 'action' => 'edit'],
            ['name' => 'Delete Permissions', 'slug' => 'permissions.delete', 'description' => 'Delete permissions', 'module' => 'permissions', 'action' => 'delete'],

            // Dashboard permissions
            ['name' => 'View Dashboard', 'slug' => 'dashboard.view', 'description' => 'View dashboard', 'module' => 'dashboard', 'action' => 'view'],
            ['name' => 'Dashboard Statistics', 'slug' => 'dashboard.stats', 'description' => 'View dashboard statistics', 'module' => 'dashboard', 'action' => 'stats'],

            // Settings permissions
            ['name' => 'View Settings', 'slug' => 'settings.view', 'description' => 'View system settings', 'module' => 'settings', 'action' => 'view'],
            ['name' => 'Manage Settings', 'slug' => 'settings.manage', 'description' => 'Manage system settings', 'module' => 'settings', 'action' => 'manage'],

            // Patient permissions
            ['name' => 'View Patients', 'slug' => 'patients.view', 'description' => 'View patient information', 'module' => 'patients', 'action' => 'view'],
            ['name' => 'Create Patients', 'slug' => 'patients.create', 'description' => 'Create new patients', 'module' => 'patients', 'action' => 'create'],
            ['name' => 'Edit Patients', 'slug' => 'patients.edit', 'description' => 'Edit patient information', 'module' => 'patients', 'action' => 'edit'],
            ['name' => 'Delete Patients', 'slug' => 'patients.delete', 'description' => 'Delete patients', 'module' => 'patients', 'action' => 'delete'],

            // Doctor permissions
            ['name' => 'View Doctors', 'slug' => 'doctors.view', 'description' => 'View doctor information', 'module' => 'doctors', 'action' => 'view'],
            ['name' => 'Create Doctors', 'slug' => 'doctors.create', 'description' => 'Create new doctors', 'module' => 'doctors', 'action' => 'create'],
            ['name' => 'Edit Doctors', 'slug' => 'doctors.edit', 'description' => 'Edit doctor information', 'module' => 'doctors', 'action' => 'edit'],
            ['name' => 'Delete Doctors', 'slug' => 'doctors.delete', 'description' => 'Delete doctors', 'module' => 'doctors', 'action' => 'delete'],

            // Appointment permissions
            ['name' => 'View Appointments', 'slug' => 'appointments.view', 'description' => 'View appointments', 'module' => 'appointments', 'action' => 'view'],
            ['name' => 'Create Appointments', 'slug' => 'appointments.create', 'description' => 'Create new appointments', 'module' => 'appointments', 'action' => 'create'],
            ['name' => 'Edit Appointments', 'slug' => 'appointments.edit', 'description' => 'Edit appointments', 'module' => 'appointments', 'action' => 'edit'],
            ['name' => 'Delete Appointments', 'slug' => 'appointments.delete', 'description' => 'Delete appointments', 'module' => 'appointments', 'action' => 'delete'],
            ['name' => 'Check-in Appointments', 'slug' => 'appointments.checkin', 'description' => 'Check-in patients for appointments', 'module' => 'appointments', 'action' => 'checkin'],
            ['name' => 'Cancel Appointments', 'slug' => 'appointments.cancel', 'description' => 'Cancel appointments', 'module' => 'appointments', 'action' => 'cancel'],

            // Activity log permissions
            ['name' => 'View Activity Logs', 'slug' => 'activity_logs.view', 'description' => 'View activity logs', 'module' => 'activity_logs', 'action' => 'view'],
            ['name' => 'Export Activity Logs', 'slug' => 'activity_logs.export', 'description' => 'Export activity logs', 'module' => 'activity_logs', 'action' => 'export'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['slug' => $permission['slug']],
                $permission
            );
        }
    }

    /**
     * Create system roles
     */
    private function createRoles(): void
    {
        $roles = [
            [
                'name' => 'superadmin',
                'description' => 'Super Administrator with full system access',
                'is_system_role' => true,
                'permissions_config' => json_encode(['*']), // All permissions
            ],
            [
                'name' => 'admin',
                'description' => 'Clinic Administrator with full clinic access',
                'is_system_role' => true,
                'permissions_config' => json_encode([
                    'clinics.manage', 'users.manage', 'patients.manage', 'doctors.manage',
                    'appointments.manage', 'dashboard.view', 'settings.manage', 'activity_logs.view'
                ]),
            ],
            [
                'name' => 'doctor',
                'description' => 'Medical professional with clinical access',
                'is_system_role' => true,
                'permissions_config' => json_encode([
                    'patients.view', 'appointments.view', 'appointments.edit', 'dashboard.view'
                ]),
            ],
            [
                'name' => 'receptionist',
                'description' => 'Front desk staff with patient management access',
                'is_system_role' => true,
                'permissions_config' => json_encode([
                    'patients.view', 'patients.create', 'patients.edit', 'appointments.view',
                    'appointments.create', 'appointments.edit', 'appointments.checkin', 'dashboard.view'
                ]),
            ],
            [
                'name' => 'patient',
                'description' => 'Patient with self-service access',
                'is_system_role' => true,
                'permissions_config' => json_encode([
                    'patients.view', 'appointments.view', 'appointments.create', 'dashboard.view'
                ]),
            ],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(
                ['name' => $role['name']],
                $role
            );
        }
    }

    /**
     * Create admin user
     */
    private function createAdminUser(array $adminData): User
    {
        return User::create([
            'name' => $adminData['name'],
            'email' => $adminData['email'],
            'password' => Hash::make($adminData['password']),
            'phone' => $adminData['phone'] ?? null,
            'is_active' => true,
            'email_verified_at' => now(),
            'onboarding_completed_at' => now(),
        ]);
    }

    /**
     * Create clinic
     */
    private function createClinic(array $clinicData, int $createdBy): Clinic
    {
        return Clinic::create([
            'name' => $clinicData['name'],
            'phone' => $clinicData['phone'],
            'email' => $clinicData['email'],
            'address' => $clinicData['address'],
            'website' => $clinicData['website'] ?? null,
            'description' => $clinicData['description'] ?? 'Medical clinic providing comprehensive healthcare services',
            'is_active' => true,
            'created_by' => $createdBy,
        ]);
    }

    /**
     * Assign user to clinic with superadmin role
     */
    private function assignUserToClinic(User $user, Clinic $clinic): void
    {
        $superadminRole = Role::where('name', 'superadmin')->first();
        
        if (!$superadminRole) {
            throw new \Exception('Superadmin role not found. Seeder may have failed.');
        }

        // Assign user to clinic with superadmin role
        $user->userClinicRoles()->create([
            'clinic_id' => $clinic->id,
            'role_id' => $superadminRole->id,
        ]);
    }

    /**
     * Create default clinic settings
     */
    private function createDefaultSettings(int $clinicId, array $config): void
    {
        $defaultSettings = [
            // Clinic Information
            'clinic.name' => $config['clinic']['name'],
            'clinic.phone' => $config['clinic']['phone'],
            'clinic.email' => $config['clinic']['email'],
            'clinic.address' => $config['clinic']['address'],
            'clinic.website' => $config['clinic']['website'] ?? '',
            'clinic.description' => $config['clinic']['description'] ?? 'Medical clinic providing comprehensive healthcare services',

            // Working Hours (Default: Monday-Friday 9AM-5PM)
            'working_hours.monday' => json_encode(['start' => '09:00', 'end' => '17:00', 'closed' => false]),
            'working_hours.tuesday' => json_encode(['start' => '09:00', 'end' => '17:00', 'closed' => false]),
            'working_hours.wednesday' => json_encode(['start' => '09:00', 'end' => '17:00', 'closed' => false]),
            'working_hours.thursday' => json_encode(['start' => '09:00', 'end' => '17:00', 'closed' => false]),
            'working_hours.friday' => json_encode(['start' => '09:00', 'end' => '17:00', 'closed' => false]),
            'working_hours.saturday' => json_encode(['start' => '09:00', 'end' => '13:00', 'closed' => false]),
            'working_hours.sunday' => json_encode(['start' => '09:00', 'end' => '17:00', 'closed' => true]),

            // System Settings
            'system.timezone' => $config['system']['timezone'] ?? 'UTC',
            'system.date_format' => $config['system']['date_format'] ?? 'Y-m-d',
            'system.time_format' => $config['system']['time_format'] ?? 'H:i',
            'system.currency' => $config['system']['currency'] ?? 'USD',
            'system.language' => $config['system']['language'] ?? 'en',

            // Security Settings
            'security.session_timeout' => $config['security']['session_timeout'] ?? 480, // 8 hours
            'security.password_min_length' => $config['security']['password_min_length'] ?? 8,
            'security.require_2fa' => $config['security']['require_2fa'] ?? false,
            'security.audit_log_retention_days' => $config['security']['audit_log_retention_days'] ?? 2555, // 7 years
            'security.patient_data_retention_days' => $config['security']['patient_data_retention_days'] ?? 2555, // 7 years

            // Appointment Settings
            'appointments.default_duration' => $config['appointments']['default_duration'] ?? 30,
            'appointments.buffer_time' => $config['appointments']['buffer_time'] ?? 15,
            'appointments.auto_confirm' => $config['appointments']['auto_confirm'] ?? true,
            'appointments.allow_online_booking' => $config['appointments']['allow_online_booking'] ?? true,
            'appointments.max_advance_days' => $config['appointments']['max_advance_days'] ?? 90,
            'appointments.min_advance_hours' => $config['appointments']['min_advance_hours'] ?? 2,
            'appointments.cancellation_hours' => $config['appointments']['cancellation_hours'] ?? 24,
            'appointments.reminder_hours' => $config['appointments']['reminder_hours'] ?? 24,
            'appointments.max_per_day' => $config['appointments']['max_per_day'] ?? 50,

            // Notifications
            'notifications.email_enabled' => $config['notifications']['email_enabled'] ?? true,
            'notifications.sms_enabled' => $config['notifications']['sms_enabled'] ?? false,
            'appointment_reminder_hours' => $config['notifications']['appointment_reminder_hours'] ?? 24,
            'follow_up_days' => $config['notifications']['follow_up_days'] ?? 7,

            // Branding
            'branding.primary_color' => $config['branding']['primary_color'] ?? '#3B82F6',
            'branding.secondary_color' => $config['branding']['secondary_color'] ?? '#1E40AF',
            'branding.logo_url' => $config['branding']['logo_url'] ?? '',
            'branding.favicon_url' => $config['branding']['favicon_url'] ?? '',
        ];

        foreach ($defaultSettings as $key => $value) {
            Setting::create([
                'clinic_id' => $clinicId,
                'key' => $key,
                'value' => $value,
                'type' => is_bool($value) ? 'boolean' : (is_numeric($value) ? 'integer' : 'string'),
                'group' => explode('.', $key)[0],
                'description' => $this->getSettingDescription($key),
                'is_public' => in_array($key, [
                    'clinic.name', 'clinic.phone', 'clinic.email', 'clinic.address',
                    'working_hours.monday', 'working_hours.tuesday', 'working_hours.wednesday',
                    'working_hours.thursday', 'working_hours.friday', 'working_hours.saturday', 'working_hours.sunday'
                ]),
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
     * Run additional seeding
     */
    private function runAdditionalSeeding(): void
    {
        // Run the BaseSeeder to create additional data
        $seeder = new BaseSeeder();
        $seeder->run();
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
     * Check if installation is complete
     */
    public function isInstallationComplete(): bool
    {
        try {
            // Check if installation flag file exists
            $flagFile = storage_path('app/installation_complete.flag');
            if (!file_exists($flagFile)) {
                return false;
            }

            // Check if database is properly set up
            if (!DB::getSchemaBuilder()->hasTable('users')) {
                return false;
            }

            // Check if there's at least one user with superadmin role
            $superadminExists = DB::table('users')
                ->join('user_clinic_roles', 'users.id', '=', 'user_clinic_roles.user_id')
                ->join('roles', 'user_clinic_roles.role_id', '=', 'roles.id')
                ->where('roles.name', 'superadmin')
                ->exists();

            return $superadminExists;

        } catch (\Exception $e) {
            // If there's any error checking the installation status,
            // assume installation is not complete
            return false;
        }
    }
}
