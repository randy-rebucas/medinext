<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Faker\Factory as Faker;

// Models
use App\Models\Role;
use App\Models\User;
use App\Models\Permission;
use App\Models\Setting;
use App\Models\Clinic;
use App\Models\UserClinicRole;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Appointment;
use App\Models\Prescription;
use App\Models\Encounter;
use App\Models\Room;
use App\Models\LabResult;
use App\Models\Bill;
use App\Models\BillItem;
use App\Models\Insurance;
use App\Models\Queue;
use App\Models\QueuePatient;
use App\Models\ActivityLog;
use App\Models\Notification;

class BaseSeeder extends Seeder
{
    private $faker;
    private $options = [
        'skip_existing' => true,
        'validate_data' => true,
        'show_progress' => true,
        'memory_optimized' => true,
        'create_demo_data' => false,
    ];

    /**
     * Set seeder options
     */
    public function setOptions(array $options): void
    {
        $this->options = array_merge($this->options, $options);
    }

    /**
     * Set command instance for logging
     */
    public function setCommand($command): void
    {
        $this->command = $command;
    }

    /**
     * Log info message
     */
    private function logInfo(string $message): void
    {
        if ($this->command) {
            $this->command->info($message);
        } else {
            echo $message . PHP_EOL;
        }
    }

    /**
     * Log error message
     */
    private function logError(string $message): void
    {
        if ($this->command) {
            $this->command->error($message);
        } else {
            echo "ERROR: " . $message . PHP_EOL;
        }
    }

    public function run(): void
    {
        $startTime = microtime(true);
        
        // Increase memory limit and enable garbage collection
        ini_set('memory_limit', '4G');
        gc_enable();

        $this->faker = Faker::create();

        $this->logInfo('🚀 Starting BaseSeeder - Unified Database Seeding...');
        $this->logInfo('================================================');

        try {
            // Use database transaction for rollback capability
            DB::transaction(function () {
                $this->seedCoreSystem();
                gc_collect_cycles();
                
                $this->seedInfrastructure();
                gc_collect_cycles();
                
                $this->seedUsersAndRoles();
                gc_collect_cycles();
                
                $this->seedBusinessData();
                gc_collect_cycles();
                
                $this->seedActivityLogs();
                gc_collect_cycles();
            });

            // Force garbage collection after transaction
            gc_collect_cycles();

            $endTime = microtime(true);
            $executionTime = round($endTime - $startTime, 2);

            $this->logInfo('✅ BaseSeeder completed successfully!');
            $this->logInfo("⏱️  Execution time: {$executionTime} seconds");
            $this->displaySummary();

        } catch (\Exception $e) {
            $this->logError('❌ BaseSeeder failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Run only essential seeding for installation (Step 1 only)
     */
    public function runInstallationSeeding(): void
    {
        $startTime = microtime(true);
        
        // Increase memory limit and enable garbage collection
        ini_set('memory_limit', '1G');
        gc_enable();

        $this->faker = Faker::create();

        $this->logInfo('🚀 Starting Installation Seeding - Core System Only...');
        $this->logInfo('====================================================');

        try {
            // Use database transaction for rollback capability
            DB::transaction(function () {
                // Step 1: Core System (permissions, roles, settings) only
                $this->seedCoreSystem();
                gc_collect_cycles();
            });

            // Force garbage collection after transaction
            gc_collect_cycles();

            $endTime = microtime(true);
            $executionTime = round($endTime - $startTime, 2);

            $this->logInfo('✅ Installation seeding completed successfully!');
            $this->logInfo("⏱️  Execution time: {$executionTime} seconds");

        } catch (\Exception $e) {
            $this->logError('❌ Installation seeding failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Seed core system data (permissions, roles, settings)
     */
    public function seedCoreSystem(): void
    {
        $this->logInfo('📋 Step 1/5: Seeding Core System...');
        
        $this->createPermissions();
        $this->createRoles();
        $this->createDefaultSettings();
        
        $this->logInfo('   ✓ Core system data created');
    }

    /**
     * Seed infrastructure (clinics, rooms)
     */
    public function seedInfrastructure(): void
    {
        $this->logInfo('🏥 Step 2/5: Seeding Infrastructure...');
        
        $this->createClinics();
        $this->createRooms();
        
        $this->logInfo('   ✓ Infrastructure created');
    }

    /**
     * Seed users and roles
     */
    public function seedUsersAndRoles(): void
    {
        $this->logInfo('👥 Step 3/5: Seeding Users and Roles...');
        
        $this->createNovaAdmin();
        $this->createDemoUsers();
        $this->createDoctors();
        
        $this->logInfo('   ✓ Users and roles created');
    }

    /**
     * Seed business data (patients, appointments, etc.)
     */
    public function seedBusinessData(): void
    {
        $this->logInfo('💼 Step 4/5: Seeding Business Data...');
        
        $this->createPatients();
        $this->createAppointments();
        $this->createEncounters();
        $this->createPrescriptions();
        $this->createLabResults();
        $this->createBills();
        $this->createInsurance();
        $this->createQueue();
        $this->createNotifications();
        
        $this->logInfo('   ✓ Business data created');
    }

    /**
     * Seed activity logs
     */
    public function seedActivityLogs(): void
    {
        $this->logInfo('📊 Step 5/5: Seeding Activity Logs...');
        
        $this->createActivityLogs();
        
        $this->logInfo('   ✓ Activity logs created');
    }

    /**
     * Create all system permissions
     */
    public function createPermissions(): void
    {
        $permissions = [
            // Clinic permissions
            ['name' => 'Manage Clinics', 'slug' => 'clinics.manage', 'description' => 'Full control over clinic operations', 'module' => 'clinics', 'action' => 'manage'],
            ['name' => 'View Clinics', 'slug' => 'clinics.view', 'description' => 'View clinic information', 'module' => 'clinics', 'action' => 'view'],
            ['name' => 'Create Clinics', 'slug' => 'clinics.create', 'description' => 'Create new clinics', 'module' => 'clinics', 'action' => 'create'],
            ['name' => 'Edit Clinics', 'slug' => 'clinics.edit', 'description' => 'Edit clinic information', 'module' => 'clinics', 'action' => 'edit'],
            ['name' => 'Delete Clinics', 'slug' => 'clinics.delete', 'description' => 'Delete clinics', 'module' => 'clinics', 'action' => 'delete'],

            // Doctor permissions
            ['name' => 'Manage Doctors', 'slug' => 'doctors.manage', 'description' => 'Full control over doctor operations', 'module' => 'doctors', 'action' => 'manage'],
            ['name' => 'View Doctors', 'slug' => 'doctors.view', 'description' => 'View doctor information', 'module' => 'doctors', 'action' => 'view'],
            ['name' => 'Create Doctors', 'slug' => 'doctors.create', 'description' => 'Add new doctors', 'module' => 'doctors', 'action' => 'create'],
            ['name' => 'Edit Doctors', 'slug' => 'doctors.edit', 'description' => 'Edit doctor information', 'module' => 'doctors', 'action' => 'edit'],
            ['name' => 'Delete Doctors', 'slug' => 'doctors.delete', 'description' => 'Remove doctors', 'module' => 'doctors', 'action' => 'delete'],

            // Staff permissions
            ['name' => 'Manage Staff', 'slug' => 'staff.manage', 'description' => 'Full control over staff operations', 'module' => 'staff', 'action' => 'manage'],
            ['name' => 'View Staff', 'slug' => 'staff.view', 'description' => 'View staff information', 'module' => 'staff', 'action' => 'view'],
            ['name' => 'Create Staff', 'slug' => 'staff.create', 'description' => 'Add new staff members', 'module' => 'staff', 'action' => 'create'],
            ['name' => 'Edit Staff', 'slug' => 'staff.edit', 'description' => 'Edit staff information', 'module' => 'staff', 'action' => 'edit'],
            ['name' => 'Delete Staff', 'slug' => 'staff.delete', 'description' => 'Remove staff members', 'module' => 'staff', 'action' => 'delete'],

            // Patient permissions
            ['name' => 'Manage Patients', 'slug' => 'patients.manage', 'description' => 'Full control over patient operations', 'module' => 'patients', 'action' => 'manage'],
            ['name' => 'View Patients', 'slug' => 'patients.view', 'description' => 'View patient information', 'module' => 'patients', 'action' => 'view'],
            ['name' => 'Create Patients', 'slug' => 'patients.create', 'description' => 'Add new patients', 'module' => 'patients', 'action' => 'create'],
            ['name' => 'Edit Patients', 'slug' => 'patients.edit', 'description' => 'Edit patient information', 'module' => 'patients', 'action' => 'edit'],
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

        foreach ($permissions as $permissionData) {
            Permission::firstOrCreate(['slug' => $permissionData['slug']], $permissionData);
        }
    }

    /**
     * Create system roles with permissions
     */
    public function createRoles(): void
    {
        $roles = [
            'superadmin' => [
                'description' => 'Full system access and management. Can manage all clinics, users, and system settings.',
                'is_system_role' => true,
                'permissions' => [
                    'system.admin', 'system.info',
                    'clinics.manage', 'clinics.view', 'clinics.create', 'clinics.edit', 'clinics.delete',
                    'users.manage', 'users.view', 'users.create', 'users.edit', 'users.delete',
                    'roles.manage', 'roles.view', 'roles.create', 'roles.edit', 'roles.delete',
                    'doctors.manage', 'doctors.view', 'doctors.create', 'doctors.edit', 'doctors.delete',
                    'staff.manage', 'staff.view', 'staff.create', 'staff.edit', 'staff.delete',
                    'patients.manage', 'patients.view', 'patients.create', 'patients.edit', 'patients.delete',
                    'appointments.manage', 'appointments.view', 'appointments.create', 'appointments.edit', 'appointments.cancel', 'appointments.delete', 'appointments.checkin',
                    'prescriptions.manage', 'prescriptions.view', 'prescriptions.create', 'prescriptions.edit', 'prescriptions.delete', 'prescriptions.download',
                    'medical_records.manage', 'medical_records.view', 'medical_records.create', 'medical_records.edit', 'medical_records.delete',
                    'settings.manage', 'settings.view',
                    'dashboard.view', 'dashboard.stats',
                    // MedRep permissions
                    'medrep_visits.manage', 'medrep_visits.view', 'interactions.view', 'products.view', 'meetings.create',
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
                'description' => 'Front desk staff who can schedule appointments, manage patient check-ins, and handle billing support.',
                'is_system_role' => false,
                'permissions' => [
                    'clinics.view', 'doctors.view',
                    'patients.view', 'patients.create', 'patients.edit',
                    'appointments.view', 'appointments.create', 'appointments.edit', 'appointments.cancel', 'appointments.checkin',
                    'dashboard.view', 'dashboard.stats',
                    // Profile permissions
                    'profile.view',
                    // Encounters permissions
                    'encounters.view',
                    // Insurance permissions
                    'insurance.view',
                    // Reports permissions
                    'reports.view',
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
            $role = Role::firstOrCreate(
                ['name' => $roleName],
                [
                    'description' => $roleData['description'],
                    'is_system_role' => $roleData['is_system_role'],
                    'permissions_config' => $roleData['permissions']
                ]
            );

            // Assign permissions to role
            $permissionIds = Permission::whereIn('slug', $roleData['permissions'])->pluck('id');
            $role->permissions()->sync($permissionIds);
        }
    }

    /**
     * Create default system settings
     */
    public function createDefaultSettings(): void
    {
        $defaultSettings = [
            // Clinic Information
            [
                'key' => 'clinic.name',
                'value' => 'MediNext EMR Clinic',
                'type' => 'string',
                'group' => 'clinic',
                'description' => 'The name of your clinic',
                'is_public' => true,
            ],
            [
                'key' => 'clinic.phone',
                'value' => '+63 123 456 7890',
                'type' => 'string',
                'group' => 'clinic',
                'description' => 'Primary contact phone number',
                'is_public' => true,
            ],
            [
                'key' => 'clinic.email',
                'value' => 'info@yourclinic.com',
                'type' => 'string',
                'group' => 'clinic',
                'description' => 'Primary contact email',
                'is_public' => true,
            ],
            [
                'key' => 'clinic.address',
                'value' => [
                    'street' => '123 Main Street',
                    'city' => 'Manila',
                    'state' => 'Metro Manila',
                    'postal_code' => '1000',
                    'country' => 'Philippines'
                ],
                'type' => 'json',
                'group' => 'clinic',
                'description' => 'Clinic address information',
                'is_public' => true,
            ],

            // System Settings
            [
                'key' => 'system.timezone',
                'value' => 'Asia/Manila',
                'type' => 'string',
                'group' => 'system',
                'description' => 'Default timezone for the clinic',
                'is_public' => false,
            ],
            [
                'key' => 'system.date_format',
                'value' => 'Y-m-d',
                'type' => 'string',
                'group' => 'system',
                'description' => 'Date format for display',
                'is_public' => false,
            ],
            [
                'key' => 'system.time_format',
                'value' => 'H:i',
                'type' => 'string',
                'group' => 'system',
                'description' => 'Time format for display',
                'is_public' => false,
            ],
            [
                'key' => 'system.currency',
                'value' => 'PHP',
                'type' => 'string',
                'group' => 'system',
                'description' => 'Default currency for the clinic',
                'is_public' => false,
            ],

            // Notifications
            [
                'key' => 'notifications.email_enabled',
                'value' => true,
                'type' => 'boolean',
                'group' => 'notifications',
                'description' => 'Enable email notifications',
                'is_public' => false,
            ],
            [
                'key' => 'notifications.sms_enabled',
                'value' => false,
                'type' => 'boolean',
                'group' => 'notifications',
                'description' => 'Enable SMS notifications',
                'is_public' => false,
            ],
            [
                'key' => 'notifications.appointment_reminder_hours',
                'value' => 24,
                'type' => 'integer',
                'group' => 'notifications',
                'description' => 'Hours before appointment to send reminder',
                'is_public' => false,
            ],

            // Branding
            [
                'key' => 'branding.primary_color',
                'value' => '#3B82F6',
                'type' => 'string',
                'group' => 'branding',
                'description' => 'Primary brand color (hex)',
                'is_public' => true,
            ],
            [
                'key' => 'branding.secondary_color',
                'value' => '#1E40AF',
                'type' => 'string',
                'group' => 'branding',
                'description' => 'Secondary brand color (hex)',
                'is_public' => true,
            ],
        ];

        foreach ($defaultSettings as $setting) {
            Setting::firstOrCreate(['key' => $setting['key']], $setting);
        }
    }

    /**
     * Create sample clinics
     */
    public function createClinics(): void
    {
        $clinics = [
            [
                'name' => 'Main Medical Center',
                'slug' => 'main-medical-center',
                'timezone' => 'Asia/Manila',
                'phone' => '+63 2 1234 5678',
                'email' => 'info@mainmedical.com',
                'website' => 'https://mainmedical.com',
                'description' => 'Primary medical center for comprehensive healthcare services',
                'address' => json_encode([
                    'street' => '123 Medical Plaza',
                    'city' => 'Manila',
                    'state' => 'Metro Manila',
                    'postal_code' => '1000',
                    'country' => 'Philippines'
                ]),
                'settings' => json_encode([
                    'working_hours' => [
                        'monday' => ['08:00', '17:00'],
                        'tuesday' => ['08:00', '17:00'],
                        'wednesday' => ['08:00', '17:00'],
                        'thursday' => ['08:00', '17:00'],
                        'friday' => ['08:00', '17:00'],
                        'saturday' => ['08:00', '12:00'],
                        'sunday' => ['closed']
                    ],
                    'appointment_duration' => 30,
                    'max_appointments_per_day' => 50,
                    'allow_online_booking' => true,
                    'require_patient_verification' => true
                ])
            ],
            [
                'name' => 'Demo Medical Center',
                'slug' => 'demo-medical-center',
                'timezone' => 'Asia/Manila',
                'phone' => '+63 2 8765 4321',
                'email' => 'info@demomedical.com',
                'website' => 'https://demomedical.com',
                'description' => 'Demo clinic for testing and demonstration purposes',
                'address' => json_encode([
                    'street' => '456 Demo Street',
                    'city' => 'Quezon City',
                    'state' => 'Metro Manila',
                    'postal_code' => '1100',
                    'country' => 'Philippines'
                ]),
                'settings' => json_encode([
                    'working_hours' => [
                        'monday' => ['08:00', '17:00'],
                        'tuesday' => ['08:00', '17:00'],
                        'wednesday' => ['08:00', '17:00'],
                        'thursday' => ['08:00', '17:00'],
                        'friday' => ['08:00', '17:00'],
                        'saturday' => ['08:00', '12:00'],
                        'sunday' => ['closed']
                    ],
                    'appointment_duration' => 30,
                    'max_appointments_per_day' => 30,
                    'allow_online_booking' => true,
                    'require_patient_verification' => false
                ])
            ]
        ];

        foreach ($clinics as $clinicData) {
            Clinic::firstOrCreate(['slug' => $clinicData['slug']], $clinicData);
        }
    }

    /**
     * Create rooms for clinics
     */
    public function createRooms(): void
    {
        $clinics = Clinic::all();
        
        foreach ($clinics as $clinic) {
            $roomTypes = [
                ['name' => 'Consultation Room 1', 'type' => 'consultation'],
                ['name' => 'Consultation Room 2', 'type' => 'consultation'],
                ['name' => 'Examination Room 1', 'type' => 'examination'],
                ['name' => 'Procedure Room', 'type' => 'procedure'],
            ];

            foreach ($roomTypes as $roomData) {
                Room::firstOrCreate([
                    'clinic_id' => $clinic->id,
                    'name' => $roomData['name']
                ], [
                    'clinic_id' => $clinic->id,
                    'name' => $roomData['name'],
                    'type' => $roomData['type'],
                ]);
            }
        }
    }

    /**
     * Create Nova super admin user
     */
    public function createNovaAdmin(): void
    {
        $superadminRole = Role::where('name', 'superadmin')->first();

        if (!$superadminRole) {
            $this->command->error('Superadmin role not found. Please ensure permissions and roles are created first.');
            return;
        }

        // Create Nova admin user
        $novaUser = User::firstOrCreate(
            ['email' => 'nova@medinext.com'],
            [
                'name' => 'Nova Administrator',
                'email' => 'nova@medinext.com',
                'password' => Hash::make('nova123'),
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );

        // Assign superadmin role to all clinics
        $clinics = Clinic::all();
        foreach ($clinics as $clinic) {
            UserClinicRole::firstOrCreate([
                'user_id' => $novaUser->id,
                'clinic_id' => $clinic->id,
                'role_id' => $superadminRole->id,
            ]);
        }
    }

    /**
     * Create demo users for testing
     */
    public function createDemoUsers(): void
    {
        $adminRole = Role::where('name', 'admin')->first();
        $clinics = Clinic::all();

        if (!$adminRole || $clinics->isEmpty()) {
            return;
        }

        // Create demo admin user
        $demoUser = User::firstOrCreate(
            ['email' => 'demo@medinext.com'],
            [
                'name' => 'Demo Administrator',
                'email' => 'demo@medinext.com',
                'password' => Hash::make('demo123'),
                'phone' => '+63 912 345 6789',
                'is_active' => true,
                'trial_started_at' => now(),
                'trial_ends_at' => now()->addDays(14),
                'is_trial_user' => true,
                'has_activated_license' => false,
            ]
        );

        // Assign admin role to demo user
        foreach ($clinics as $clinic) {
            UserClinicRole::firstOrCreate([
                'user_id' => $demoUser->id,
                'clinic_id' => $clinic->id,
                'role_id' => $adminRole->id,
            ]);
        }
    }

    /**
     * Create sample doctors
     */
    public function createDoctors(): void
    {
        $doctorRole = Role::where('name', 'doctor')->first();
        $clinics = Clinic::all();

        if (!$doctorRole || $clinics->isEmpty()) {
            return;
        }

        $specialties = [
            'General Practice', 'Cardiology', 'Pediatrics', 'Internal Medicine',
            'Dermatology', 'Orthopedics', 'Neurology', 'Obstetrics & Gynecology'
        ];

        $doctors = [
            ['name' => 'Dr. Maria Santos', 'email' => 'maria.santos@example.com', 'specialty' => 'Cardiology'],
            ['name' => 'Dr. Juan Dela Cruz', 'email' => 'juan.delacruz@example.com', 'specialty' => 'General Practice'],
            ['name' => 'Dr. Ana Reyes', 'email' => 'ana.reyes@example.com', 'specialty' => 'Pediatrics'],
            ['name' => 'Dr. Carlos Mendoza', 'email' => 'carlos.mendoza@example.com', 'specialty' => 'Internal Medicine'],
            ['name' => 'Dr. Sofia Garcia', 'email' => 'sofia.garcia@example.com', 'specialty' => 'Dermatology'],
            ['name' => 'Dr. Roberto Aquino', 'email' => 'roberto.aquino@example.com', 'specialty' => 'Orthopedics'],
        ];

        foreach ($doctors as $index => $doctorData) {
            $user = User::firstOrCreate(
                ['email' => $doctorData['email']],
                [
                    'name' => $doctorData['name'],
                    'email' => $doctorData['email'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'is_active' => true,
                ]
            );

            // Assign to clinic (distribute across available clinics)
            $clinic = $clinics[$index % $clinics->count()];

            // Assign doctor role
            UserClinicRole::firstOrCreate([
                'user_id' => $user->id,
                'clinic_id' => $clinic->id,
                'role_id' => $doctorRole->id,
            ]);

            // Create doctor record
            Doctor::firstOrCreate(
                ['user_id' => $user->id, 'clinic_id' => $clinic->id],
                [
                    'user_id' => $user->id,
                    'clinic_id' => $clinic->id,
                    'specialty' => $doctorData['specialty'],
                    'license_no' => 'MD-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                    'is_active' => true,
                    'consultation_fee' => rand(500, 2000),
                ]
            );
        }
    }

    /**
     * Create sample patients
     */
    public function createPatients(): void
    {
        $clinics = Clinic::all();

        if ($clinics->isEmpty()) {
            return;
        }

        $samplePatients = [
            [
                'first_name' => 'Maria',
                'last_name' => 'Santos',
                'dob' => '1985-03-15',
                'sex' => 'F',
                'contact' => json_encode([
                    'phone' => '+63 912 345 6789',
                    'email' => 'maria.santos@email.com',
                    'address' => '123 Rizal Street, Manila'
                ]),
                'allergies' => json_encode(['Penicillin', 'Sulfa drugs']),
                'consents' => json_encode(['treatment', 'privacy', 'data_sharing'])
            ],
            [
                'first_name' => 'Juan',
                'last_name' => 'Dela Cruz',
                'dob' => '1990-07-22',
                'sex' => 'M',
                'contact' => json_encode([
                    'phone' => '+63 923 456 7890',
                    'email' => 'juan.delacruz@email.com',
                    'address' => '456 Bonifacio Avenue, Quezon City'
                ]),
                'allergies' => json_encode(['None']),
                'consents' => json_encode(['treatment', 'privacy', 'data_sharing'])
            ],
            [
                'first_name' => 'Ana',
                'last_name' => 'Garcia',
                'dob' => '1978-11-08',
                'sex' => 'F',
                'contact' => json_encode([
                    'phone' => '+63 934 567 8901',
                    'email' => 'ana.garcia@email.com',
                    'address' => '789 Mabini Street, Makati'
                ]),
                'allergies' => json_encode(['Latex', 'Shellfish']),
                'consents' => json_encode(['treatment', 'privacy'])
            ],
            [
                'first_name' => 'Pedro',
                'last_name' => 'Martinez',
                'dob' => '1992-04-30',
                'sex' => 'M',
                'contact' => json_encode([
                    'phone' => '+63 945 678 9012',
                    'email' => 'pedro.martinez@email.com',
                    'address' => '321 Aguinaldo Road, Taguig'
                ]),
                'allergies' => json_encode(['Aspirin']),
                'consents' => json_encode(['treatment', 'privacy', 'data_sharing', 'research'])
            ],
            [
                'first_name' => 'Luis',
                'last_name' => 'Reyes',
                'dob' => '1982-09-14',
                'sex' => 'M',
                'contact' => json_encode([
                    'phone' => '+63 956 789 0123',
                    'email' => 'luis.reyes@email.com',
                    'address' => '654 Roxas Boulevard, Pasay'
                ]),
                'allergies' => json_encode(['None']),
                'consents' => json_encode(['treatment', 'privacy'])
            ]
        ];

        foreach ($samplePatients as $index => $patientData) {
            $clinic = $clinics[$index % $clinics->count()];

            Patient::firstOrCreate([
                'clinic_id' => $clinic->id,
                'code' => 'P' . str_pad($index + 1, 4, '0', STR_PAD_LEFT)
            ], [
                'clinic_id' => $clinic->id,
                'code' => 'P' . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                'first_name' => $patientData['first_name'],
                'last_name' => $patientData['last_name'],
                'dob' => $patientData['dob'],
                'sex' => $patientData['sex'],
                'contact' => $patientData['contact'],
                'allergies' => $patientData['allergies'],
                'consents' => $patientData['consents'],
            ]);
        }
    }

    /**
     * Create sample appointments
     */
    public function createAppointments(): void
    {
        $patients = Patient::all();
        $doctors = Doctor::all();
        $rooms = Room::all();

        if ($patients->isEmpty() || $doctors->isEmpty() || $rooms->isEmpty()) {
            return;
        }

        $reasons = [
            'General Check-up', 'Follow-up Consultation', 'Symptom Evaluation',
            'Prescription Renewal', 'Lab Results Review', 'Vaccination'
        ];

        $appointmentCount = 0;
        for ($day = -3; $day <= 7; $day++) {
            $date = Carbon::now()->addDays($day);

            // Skip weekends
            if ($date->isWeekend()) {
                continue;
            }

            // Create 2-4 appointments per day
            $appointmentsPerDay = rand(2, 4);

            for ($i = 0; $i < $appointmentsPerDay; $i++) {
                $patient = $patients->random();
                $doctor = $doctors->random();
                $room = $rooms->where('clinic_id', $doctor->clinic_id)->first();

                if (!$room) {
                    continue;
                }

                $hour = rand(8, 16);
                $minute = rand(0, 3) * 15;
                $startTime = $date->copy()->setTime($hour, $minute);
                $endTime = $startTime->copy()->addMinutes(30);

                $status = 'scheduled';
                if ($date->isPast()) {
                    $status = ['scheduled', 'confirmed', 'completed'][array_rand(['scheduled', 'confirmed', 'completed'])];
                }

                Appointment::firstOrCreate([
                    'clinic_id' => $doctor->clinic_id,
                    'patient_id' => $patient->id,
                    'doctor_id' => $doctor->id,
                    'start_at' => $startTime,
                    'end_at' => $endTime,
                    'room_id' => $room->id
                ], [
                    'clinic_id' => $doctor->clinic_id,
                    'patient_id' => $patient->id,
                    'doctor_id' => $doctor->id,
                    'start_at' => $startTime,
                    'end_at' => $endTime,
                    'status' => $status,
                    'room_id' => $room->id,
                    'reason' => $reasons[array_rand($reasons)],
                    'source' => ['walk_in', 'phone', 'online'][array_rand(['walk_in', 'phone', 'online'])],
                ]);

                $appointmentCount++;
            }
        }
    }

    /**
     * Create sample encounters
     */
    public function createEncounters(): void
    {
        $appointments = Appointment::where('status', 'completed')->get();

        foreach ($appointments as $appointment) {
            Encounter::firstOrCreate([
                'clinic_id' => $appointment->clinic_id,
                'patient_id' => $appointment->patient_id,
                'doctor_id' => $appointment->doctor_id,
                'date' => $appointment->start_at,
            ], [
                'clinic_id' => $appointment->clinic_id,
                'patient_id' => $appointment->patient_id,
                'doctor_id' => $appointment->doctor_id,
                'date' => $appointment->start_at,
                'type' => 'consultation',
                'status' => 'completed',
                'chief_complaint' => $this->faker->sentence(6),
                'assessment' => $this->faker->sentence(8),
                'plan' => $this->faker->paragraph(2),
                'vitals' => json_encode([
                    'blood_pressure' => rand(90, 140) . '/' . rand(60, 90),
                    'heart_rate' => rand(60, 100),
                    'temperature' => rand(36, 38) . '.' . rand(0, 9),
                    'respiratory_rate' => rand(12, 20),
                    'oxygen_saturation' => rand(95, 100)
                ]),
                'visit_type' => 'established_patient',
                'payment_status' => 'paid',
                'billing_amount' => rand(500, 2000)
            ]);
        }
    }

    /**
     * Create sample prescriptions
     */
    public function createPrescriptions(): void
    {
        $encounters = Encounter::all();

        foreach ($encounters as $encounter) {
            Prescription::firstOrCreate([
                'clinic_id' => $encounter->clinic_id,
                'patient_id' => $encounter->patient_id,
                'doctor_id' => $encounter->doctor_id,
                'encounter_id' => $encounter->id,
            ], [
                'clinic_id' => $encounter->clinic_id,
                'patient_id' => $encounter->patient_id,
                'doctor_id' => $encounter->doctor_id,
                'encounter_id' => $encounter->id,
                'issued_at' => $encounter->date,
                'status' => ['active', 'completed'][array_rand(['active', 'completed'])],
                'qr_hash' => 'RX-' . strtoupper(uniqid()) . '-' . $encounter->patient_id,
            ]);
        }
    }

    /**
     * Create sample lab results
     */
    public function createLabResults(): void
    {
        $patients = Patient::all();
        $testTypes = [
            'Complete Blood Count', 'Blood Chemistry', 'Urinalysis', 'Lipid Profile'
        ];

        foreach ($patients as $patient) {
            LabResult::firstOrCreate([
                'clinic_id' => $patient->clinic_id,
                'patient_id' => $patient->id,
            ], [
                'clinic_id' => $patient->clinic_id,
                'patient_id' => $patient->id,
                'test_name' => $testTypes[array_rand($testTypes)],
                'test_type' => 'laboratory',
                'result_value' => $this->faker->randomFloat(2, 1, 100),
                'unit' => 'mg/dL',
                'reference_range' => 'Normal: 0-100',
                'status' => ['completed', 'abnormal'][array_rand(['completed', 'abnormal'])],
                'ordered_at' => Carbon::now()->subDays(rand(1, 7)),
                'completed_at' => Carbon::now()->subDays(rand(1, 7)),
            ]);
        }
    }

    /**
     * Create sample bills
     */
    public function createBills(): void
    {
        $patients = Patient::all();

        foreach ($patients as $index => $patient) {
            $subtotal = rand(500, 2000);
            $discount = rand(0, 200);
            $total = $subtotal - $discount;

            $bill = Bill::firstOrCreate([
                'clinic_id' => $patient->clinic_id,
                'patient_id' => $patient->id,
                'bill_number' => 'BILL-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
            ], [
                'uuid' => Str::uuid(),
                'clinic_id' => $patient->clinic_id,
                'patient_id' => $patient->id,
                'bill_number' => 'BILL-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                'bill_date' => now()->subDays(rand(1, 7))->toDateString(),
                'due_date' => now()->addDays(30)->toDateString(),
                'subtotal' => $subtotal,
                'tax_amount' => 0,
                'discount_amount' => $discount,
                'total_amount' => $total,
                'status' => ['pending', 'paid'][array_rand(['pending', 'paid'])],
                'payment_method' => ['cash', 'card'][array_rand(['cash', 'card'])],
            ]);

            // Create bill item
            BillItem::firstOrCreate([
                'bill_id' => $bill->id,
            ], [
                'uuid' => Str::uuid(),
                'bill_id' => $bill->id,
                'item_type' => 'consultation',
                'item_name' => 'Consultation Fee',
                'item_description' => 'Medical consultation service',
                'quantity' => 1,
                'unit_price' => $subtotal,
                'total' => $subtotal,
            ]);
        }
    }

    /**
     * Create sample insurance records
     */
    public function createInsurance(): void
    {
        $patients = Patient::all();
        $insuranceProviders = ['PhilHealth', 'Maxicare', 'Intellicare'];

        foreach ($patients as $index => $patient) {
            if ($index % 2 == 0) { // Create insurance for every other patient
                Insurance::firstOrCreate([
                    'patient_id' => $patient->id,
                ], [
                    'uuid' => Str::uuid(),
                    'patient_id' => $patient->id,
                    'insurance_provider' => $insuranceProviders[array_rand($insuranceProviders)],
                    'policy_number' => 'POL-' . strtoupper(uniqid()),
                    'member_id' => 'MEM-' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT),
                    'group_number' => 'GRP-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
                    'policy_holder_name' => $patient->first_name . ' ' . $patient->last_name,
                    'policy_holder_relationship' => 'self',
                    'coverage_type' => 'health',
                    'effective_date' => Carbon::now()->subMonths(rand(1, 12)),
                    'expiration_date' => Carbon::now()->addMonths(rand(6, 12)),
                    'copay_amount' => rand(50, 500),
                    'deductible_amount' => rand(1000, 5000),
                    'coverage_percentage' => 100.00,
                    'is_primary' => true,
                    'is_active' => true,
                    'verification_status' => 'verified',
                ]);
            }
        }
    }

    /**
     * Create sample queue data
     */
    public function createQueue(): void
    {
        $clinics = Clinic::all();

        foreach ($clinics as $clinic) {
            $queue = Queue::firstOrCreate([
                'clinic_id' => $clinic->id,
                'name' => 'General Consultation Queue',
            ], [
                'uuid' => Str::uuid(),
                'clinic_id' => $clinic->id,
                'name' => 'General Consultation Queue',
                'description' => 'Main queue for general consultations',
                'queue_type' => 'general',
                'is_active' => true,
            ]);

            // Add some patients to the queue
            $patients = Patient::where('clinic_id', $clinic->id)->take(2)->get();
            foreach ($patients as $index => $patient) {
                QueuePatient::firstOrCreate([
                    'queue_id' => $queue->id,
                    'patient_id' => $patient->id,
                ], [
                    'uuid' => Str::uuid(),
                    'queue_id' => $queue->id,
                    'patient_id' => $patient->id,
                    'priority' => rand(1, 3),
                    'status' => ['waiting', 'served'][array_rand(['waiting', 'served'])],
                    'joined_at' => now()->subMinutes(rand(5, 30)),
                    'notes' => 'Sample queue entry',
                ]);
            }
        }
    }

    /**
     * Create sample notifications
     */
    public function createNotifications(): void
    {
        $users = User::whereIn('email', ['nova@medinext.com', 'demo@medinext.com'])->get();

        $notifications = [
            [
                'title' => 'Welcome to MediNext EMR',
                'message' => 'Your account has been set up successfully. Explore all the features!',
                'type' => 'info',
                'priority' => 'normal'
            ],
            [
                'title' => 'System Update',
                'message' => 'The system has been updated with new features and improvements.',
                'type' => 'success',
                'priority' => 'normal'
            ],
            [
                'title' => 'Appointment Reminder',
                'message' => 'You have upcoming appointments today.',
                'type' => 'warning',
                'priority' => 'high'
            ]
        ];

        foreach ($users as $user) {
            foreach ($notifications as $notificationData) {
                Notification::firstOrCreate([
                    'user_id' => $user->id,
                    'title' => $notificationData['title'],
                ], [
                    'uuid' => Str::uuid(),
                    'user_id' => $user->id,
                    'title' => $notificationData['title'],
                    'message' => $notificationData['message'],
                    'type' => $notificationData['type'],
                    'priority' => $notificationData['priority'],
                    'delivery_method' => 'database',
                    'delivery_status' => 'delivered',
                    'notifiable_type' => 'App\\Models\\User',
                    'notifiable_id' => $user->id,
                ]);
            }
        }
    }

    /**
     * Create sample activity logs
     */
    public function createActivityLogs(): void
    {
        $users = User::whereIn('email', ['nova@medinext.com', 'demo@medinext.com'])->get();
        $patients = Patient::all();
        $doctors = Doctor::all();

        if ($users->isEmpty() || $patients->isEmpty() || $doctors->isEmpty()) {
            return;
        }

        $actions = ['created', 'updated', 'viewed', 'scheduled', 'completed'];
        $logCount = 0;

        // Generate activity logs for the past 3 days
        for ($day = 0; $day < 3; $day++) {
            $date = Carbon::now()->subDays($day);
            $logsPerDay = rand(3, 8);

            for ($i = 0; $i < $logsPerDay; $i++) {
                $user = $users->random();
                $entity = $patients->random();
                $action = $actions[array_rand($actions)];
                $timestamp = $date->copy()->addHours(rand(0, 23))->addMinutes(rand(0, 59));

                ActivityLog::create([
                    'clinic_id' => $entity->clinic_id,
                    'actor_user_id' => $user->id,
                    'entity' => 'Patient',
                    'entity_id' => $entity->id,
                    'action' => $action,
                    'at' => $timestamp,
                    'ip' => $this->faker->ipv4(),
                    'meta' => json_encode([
                        'user_agent' => $this->faker->userAgent(),
                        'session_id' => 'session_' . uniqid(),
                        'request_method' => ['GET', 'POST', 'PUT'][array_rand(['GET', 'POST', 'PUT'])],
                        'url' => 'https://medinext.com/patients/' . $entity->id
                    ]),
                    'before_hash' => md5(uniqid()),
                    'after_hash' => md5(uniqid()),
                ]);

                $logCount++;
            }
        }
    }

    /**
     * Display summary of seeded data
     */
    private function displaySummary(): void
    {
        $this->logInfo('');
        $this->logInfo('📊 SEEDING SUMMARY');
        $this->logInfo('==================');
        $this->logInfo('✅ Permissions: ' . Permission::count());
        $this->logInfo('✅ Roles: ' . Role::count());
        $this->logInfo('✅ Settings: ' . Setting::count());
        $this->logInfo('✅ Clinics: ' . Clinic::count());
        $this->logInfo('✅ Rooms: ' . Room::count());
        $this->logInfo('✅ Users: ' . User::count());
        $this->logInfo('✅ Doctors: ' . Doctor::count());
        $this->logInfo('✅ Patients: ' . Patient::count());
        $this->logInfo('✅ Appointments: ' . Appointment::count());
        $this->logInfo('✅ Encounters: ' . Encounter::count());
        $this->logInfo('✅ Prescriptions: ' . Prescription::count());
        $this->logInfo('✅ Lab Results: ' . LabResult::count());
        $this->logInfo('✅ Bills: ' . Bill::count());
        $this->logInfo('✅ Insurance Records: ' . Insurance::count());
        $this->logInfo('✅ Queues: ' . Queue::count());
        $this->logInfo('✅ Notifications: ' . Notification::count());
        $this->logInfo('✅ Activity Logs: ' . ActivityLog::count());
        $this->logInfo('');
        $this->logInfo('🔑 DEFAULT ACCOUNTS');
        $this->logInfo('==================');
        $this->logInfo('Nova Admin: nova@medinext.com (nova123)');
        $this->logInfo('Demo Admin: demo@medinext.com (demo123)');
        $this->logInfo('');
        $this->logInfo('🚀 System is ready for use!');
    }
}
