<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create all permissions
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
            ['name' => 'Delete Medical Records', 'slug' => 'medical_records.delete', 'description' => 'Remove medical records', 'module' => 'medical_records', 'action' => 'delete'],

            // Encounter permissions
            ['name' => 'Manage Encounters', 'slug' => 'encounters.manage', 'description' => 'Full control over encounter operations', 'module' => 'encounters', 'action' => 'manage'],
            ['name' => 'View Encounters', 'slug' => 'encounters.view', 'description' => 'View encounter information', 'module' => 'encounters', 'action' => 'view'],
            ['name' => 'Create Encounters', 'slug' => 'encounters.create', 'description' => 'Create new encounters', 'module' => 'encounters', 'action' => 'create'],
            ['name' => 'Edit Encounters', 'slug' => 'encounters.edit', 'description' => 'Modify encounters', 'module' => 'encounters', 'action' => 'edit'],
            ['name' => 'Delete Encounters', 'slug' => 'encounters.delete', 'description' => 'Remove encounters', 'module' => 'encounters', 'action' => 'delete'],
            ['name' => 'Complete Encounters', 'slug' => 'encounters.complete', 'description' => 'Mark encounters as completed', 'module' => 'encounters', 'action' => 'complete'],

            // Queue permissions
            ['name' => 'Manage Queue', 'slug' => 'queue.manage', 'description' => 'Full control over queue operations', 'module' => 'queue', 'action' => 'manage'],
            ['name' => 'View Queue', 'slug' => 'queue.view', 'description' => 'View queue information', 'module' => 'queue', 'action' => 'view'],
            ['name' => 'Add to Queue', 'slug' => 'queue.add', 'description' => 'Add patients to queue', 'module' => 'queue', 'action' => 'add'],
            ['name' => 'Remove from Queue', 'slug' => 'queue.remove', 'description' => 'Remove patients from queue', 'module' => 'queue', 'action' => 'remove'],
            ['name' => 'Process Queue', 'slug' => 'queue.process', 'description' => 'Process queue items', 'module' => 'queue', 'action' => 'process'],

            // Lab Results permissions
            ['name' => 'Manage Lab Results', 'slug' => 'lab_results.manage', 'description' => 'Full control over lab results', 'module' => 'lab_results', 'action' => 'manage'],
            ['name' => 'View Lab Results', 'slug' => 'lab_results.view', 'description' => 'View lab result information', 'module' => 'lab_results', 'action' => 'view'],
            ['name' => 'Create Lab Results', 'slug' => 'lab_results.create', 'description' => 'Create new lab results', 'module' => 'lab_results', 'action' => 'create'],
            ['name' => 'Edit Lab Results', 'slug' => 'lab_results.edit', 'description' => 'Modify lab results', 'module' => 'lab_results', 'action' => 'edit'],
            ['name' => 'Delete Lab Results', 'slug' => 'lab_results.delete', 'description' => 'Remove lab results', 'module' => 'lab_results', 'action' => 'delete'],

            // File Assets permissions
            ['name' => 'Manage File Assets', 'slug' => 'file_assets.manage', 'description' => 'Full control over file assets', 'module' => 'file_assets', 'action' => 'manage'],
            ['name' => 'View File Assets', 'slug' => 'file_assets.view', 'description' => 'View file asset information', 'module' => 'file_assets', 'action' => 'view'],
            ['name' => 'Upload File Assets', 'slug' => 'file_assets.upload', 'description' => 'Upload new file assets', 'module' => 'file_assets', 'action' => 'upload'],
            ['name' => 'Download File Assets', 'slug' => 'file_assets.download', 'description' => 'Download file assets', 'module' => 'file_assets', 'action' => 'download'],
            ['name' => 'Delete File Assets', 'slug' => 'file_assets.delete', 'description' => 'Remove file assets', 'module' => 'file_assets', 'action' => 'delete'],

            // Room permissions
            ['name' => 'Manage Rooms', 'slug' => 'rooms.manage', 'description' => 'Full control over room operations', 'module' => 'rooms', 'action' => 'manage'],
            ['name' => 'View Rooms', 'slug' => 'rooms.view', 'description' => 'View room information', 'module' => 'rooms', 'action' => 'view'],
            ['name' => 'Create Rooms', 'slug' => 'rooms.create', 'description' => 'Create new rooms', 'module' => 'rooms', 'action' => 'create'],
            ['name' => 'Edit Rooms', 'slug' => 'rooms.edit', 'description' => 'Modify room information', 'module' => 'rooms', 'action' => 'edit'],
            ['name' => 'Delete Rooms', 'slug' => 'rooms.delete', 'description' => 'Remove rooms', 'module' => 'rooms', 'action' => 'delete'],

            // Insurance permissions
            ['name' => 'Manage Insurance', 'slug' => 'insurance.manage', 'description' => 'Full control over insurance operations', 'module' => 'insurance', 'action' => 'manage'],
            ['name' => 'View Insurance', 'slug' => 'insurance.view', 'description' => 'View insurance information', 'module' => 'insurance', 'action' => 'view'],
            ['name' => 'Create Insurance', 'slug' => 'insurance.create', 'description' => 'Create new insurance records', 'module' => 'insurance', 'action' => 'create'],
            ['name' => 'Edit Insurance', 'slug' => 'insurance.edit', 'description' => 'Modify insurance information', 'module' => 'insurance', 'action' => 'edit'],
            ['name' => 'Delete Insurance', 'slug' => 'insurance.delete', 'description' => 'Remove insurance records', 'module' => 'insurance', 'action' => 'delete'],

            // Notification permissions
            ['name' => 'Manage Notifications', 'slug' => 'notifications.manage', 'description' => 'Full control over notifications', 'module' => 'notifications', 'action' => 'manage'],
            ['name' => 'View Notifications', 'slug' => 'notifications.view', 'description' => 'View notifications', 'module' => 'notifications', 'action' => 'view'],
            ['name' => 'Create Notifications', 'slug' => 'notifications.create', 'description' => 'Create new notifications', 'module' => 'notifications', 'action' => 'create'],
            ['name' => 'Edit Notifications', 'slug' => 'notifications.edit', 'description' => 'Modify notifications', 'module' => 'notifications', 'action' => 'edit'],
            ['name' => 'Delete Notifications', 'slug' => 'notifications.delete', 'description' => 'Remove notifications', 'module' => 'notifications', 'action' => 'delete'],

            // Activity Log permissions
            ['name' => 'View Activity Logs', 'slug' => 'activity_logs.view', 'description' => 'View activity logs', 'module' => 'activity_logs', 'action' => 'view'],
            ['name' => 'Export Activity Logs', 'slug' => 'activity_logs.export', 'description' => 'Export activity logs', 'module' => 'activity_logs', 'action' => 'export'],

            // Schedule permissions
            ['name' => 'View Schedule', 'slug' => 'schedule.view', 'description' => 'View appointment schedules', 'module' => 'schedule', 'action' => 'view'],
            ['name' => 'Manage Schedule', 'slug' => 'schedule.manage', 'description' => 'Manage appointment schedules', 'module' => 'schedule', 'action' => 'manage'],

            // User management permissions
            ['name' => 'Manage Users', 'slug' => 'users.manage', 'description' => 'Full control over user operations', 'module' => 'users', 'action' => 'manage'],
            ['name' => 'View Users', 'slug' => 'users.view', 'description' => 'View user information', 'module' => 'users', 'action' => 'view'],
            ['name' => 'Create Users', 'slug' => 'users.create', 'description' => 'Create new users', 'module' => 'users', 'action' => 'create'],
            ['name' => 'Edit Users', 'slug' => 'users.edit', 'description' => 'Edit user information', 'module' => 'users', 'action' => 'edit'],
            ['name' => 'Delete Users', 'slug' => 'users.delete', 'description' => 'Remove users', 'module' => 'users', 'action' => 'delete'],
            ['name' => 'Activate Users', 'slug' => 'users.activate', 'description' => 'Activate user accounts', 'module' => 'users', 'action' => 'activate'],
            ['name' => 'Deactivate Users', 'slug' => 'users.deactivate', 'description' => 'Deactivate user accounts', 'module' => 'users', 'action' => 'deactivate'],

            // Role management permissions
            ['name' => 'Manage Roles', 'slug' => 'roles.manage', 'description' => 'Full control over role operations', 'module' => 'roles', 'action' => 'manage'],
            ['name' => 'View Roles', 'slug' => 'roles.view', 'description' => 'View role information', 'module' => 'roles', 'action' => 'view'],
            ['name' => 'Create Roles', 'slug' => 'roles.create', 'description' => 'Create new roles', 'module' => 'roles', 'action' => 'create'],
            ['name' => 'Edit Roles', 'slug' => 'roles.edit', 'description' => 'Edit role information', 'module' => 'roles', 'action' => 'edit'],
            ['name' => 'Delete Roles', 'slug' => 'roles.delete', 'description' => 'Remove roles', 'module' => 'roles', 'action' => 'delete'],

            // Permission management permissions
            ['name' => 'Manage Permissions', 'slug' => 'permissions.manage', 'description' => 'Full control over permission operations', 'module' => 'permissions', 'action' => 'manage'],
            ['name' => 'View Permissions', 'slug' => 'permissions.view', 'description' => 'View permission information', 'module' => 'permissions', 'action' => 'view'],
            ['name' => 'Create Permissions', 'slug' => 'permissions.create', 'description' => 'Create new permissions', 'module' => 'permissions', 'action' => 'create'],
            ['name' => 'Edit Permissions', 'slug' => 'permissions.edit', 'description' => 'Edit permission information', 'module' => 'permissions', 'action' => 'edit'],
            ['name' => 'Delete Permissions', 'slug' => 'permissions.delete', 'description' => 'Remove permissions', 'module' => 'permissions', 'action' => 'delete'],

            // Billing permissions
            ['name' => 'Manage Billing', 'slug' => 'billing.manage', 'description' => 'Full control over billing operations', 'module' => 'billing', 'action' => 'manage'],
            ['name' => 'View Billing', 'slug' => 'billing.view', 'description' => 'View billing information', 'module' => 'billing', 'action' => 'view'],
            ['name' => 'Create Billing', 'slug' => 'billing.create', 'description' => 'Create new bills', 'module' => 'billing', 'action' => 'create'],
            ['name' => 'Edit Billing', 'slug' => 'billing.edit', 'description' => 'Modify billing information', 'module' => 'billing', 'action' => 'edit'],
            ['name' => 'Delete Billing', 'slug' => 'billing.delete', 'description' => 'Remove billing records', 'module' => 'billing', 'action' => 'delete'],

            // Report permissions
            ['name' => 'View Reports', 'slug' => 'reports.view', 'description' => 'View system reports', 'module' => 'reports', 'action' => 'view'],
            ['name' => 'Export Reports', 'slug' => 'reports.export', 'description' => 'Export reports to various formats', 'module' => 'reports', 'action' => 'export'],
            ['name' => 'Generate Reports', 'slug' => 'reports.generate', 'description' => 'Generate new reports', 'module' => 'reports', 'action' => 'generate'],

            // Settings permissions
            ['name' => 'Manage Settings', 'slug' => 'settings.manage', 'description' => 'Manage system settings', 'module' => 'settings', 'action' => 'manage'],
            ['name' => 'View Settings', 'slug' => 'settings.view', 'description' => 'View system settings', 'module' => 'settings', 'action' => 'view'],

            // Profile permissions
            ['name' => 'Edit Profile', 'slug' => 'profile.edit', 'description' => 'Edit own profile information', 'module' => 'profile', 'action' => 'edit'],
            ['name' => 'View Profile', 'slug' => 'profile.view', 'description' => 'View profile information', 'module' => 'profile', 'action' => 'view'],

            // Product permissions (for medical representatives)
            ['name' => 'Manage Products', 'slug' => 'products.manage', 'description' => 'Full control over product operations', 'module' => 'products', 'action' => 'manage'],
            ['name' => 'View Products', 'slug' => 'products.view', 'description' => 'View product information', 'module' => 'products', 'action' => 'view'],
            ['name' => 'Create Products', 'slug' => 'products.create', 'description' => 'Add new products', 'module' => 'products', 'action' => 'create'],
            ['name' => 'Edit Products', 'slug' => 'products.edit', 'description' => 'Modify product information', 'module' => 'products', 'action' => 'edit'],
            ['name' => 'Delete Products', 'slug' => 'products.delete', 'description' => 'Remove products', 'module' => 'products', 'action' => 'delete'],

            // Meeting permissions (for medical representatives)
            ['name' => 'Manage Meetings', 'slug' => 'meetings.manage', 'description' => 'Full control over meeting operations', 'module' => 'meetings', 'action' => 'manage'],
            ['name' => 'View Meetings', 'slug' => 'meetings.view', 'description' => 'View meeting information', 'module' => 'meetings', 'action' => 'view'],
            ['name' => 'Create Meetings', 'slug' => 'meetings.create', 'description' => 'Schedule new meetings', 'module' => 'meetings', 'action' => 'create'],
            ['name' => 'Edit Meetings', 'slug' => 'meetings.edit', 'description' => 'Modify meeting information', 'module' => 'meetings', 'action' => 'edit'],
            ['name' => 'Delete Meetings', 'slug' => 'meetings.delete', 'description' => 'Remove meetings', 'module' => 'meetings', 'action' => 'delete'],

            // Interaction permissions (for medical representatives)
            ['name' => 'Manage Interactions', 'slug' => 'interactions.manage', 'description' => 'Full control over interaction operations', 'module' => 'interactions', 'action' => 'manage'],
            ['name' => 'View Interactions', 'slug' => 'interactions.view', 'description' => 'View interaction records', 'module' => 'interactions', 'action' => 'view'],
            ['name' => 'Create Interactions', 'slug' => 'interactions.create', 'description' => 'Record new interactions', 'module' => 'interactions', 'action' => 'create'],
            ['name' => 'Edit Interactions', 'slug' => 'interactions.edit', 'description' => 'Modify interaction records', 'module' => 'interactions', 'action' => 'edit'],
            ['name' => 'Delete Interactions', 'slug' => 'interactions.delete', 'description' => 'Remove interaction records', 'module' => 'interactions', 'action' => 'delete'],

            // Medrep Visit permissions
            ['name' => 'Manage Medrep Visits', 'slug' => 'medrep_visits.manage', 'description' => 'Full control over medrep visit operations', 'module' => 'medrep_visits', 'action' => 'manage'],
            ['name' => 'View Medrep Visits', 'slug' => 'medrep_visits.view', 'description' => 'View medrep visit information', 'module' => 'medrep_visits', 'action' => 'view'],
            ['name' => 'Create Medrep Visits', 'slug' => 'medrep_visits.create', 'description' => 'Create new medrep visits', 'module' => 'medrep_visits', 'action' => 'create'],
            ['name' => 'Edit Medrep Visits', 'slug' => 'medrep_visits.edit', 'description' => 'Modify medrep visit information', 'module' => 'medrep_visits', 'action' => 'edit'],
            ['name' => 'Delete Medrep Visits', 'slug' => 'medrep_visits.delete', 'description' => 'Remove medrep visits', 'module' => 'medrep_visits', 'action' => 'delete'],

            // Dashboard permissions
            ['name' => 'View Dashboard', 'slug' => 'dashboard.view', 'description' => 'View dashboard information', 'module' => 'dashboard', 'action' => 'view'],
            ['name' => 'View Statistics', 'slug' => 'dashboard.stats', 'description' => 'View dashboard statistics', 'module' => 'dashboard', 'action' => 'stats'],

            // Search permissions
            ['name' => 'Global Search', 'slug' => 'search.global', 'description' => 'Perform global searches', 'module' => 'search', 'action' => 'global'],
            ['name' => 'Patient Search', 'slug' => 'search.patients', 'description' => 'Search patient records', 'module' => 'search', 'action' => 'patients'],
            ['name' => 'Doctor Search', 'slug' => 'search.doctors', 'description' => 'Search doctor records', 'module' => 'search', 'action' => 'doctors'],

            // System permissions
            ['name' => 'System Administration', 'slug' => 'system.admin', 'description' => 'Full system administration access', 'module' => 'system', 'action' => 'admin'],
            ['name' => 'View System Info', 'slug' => 'system.info', 'description' => 'View system information', 'module' => 'system', 'action' => 'info'],
            ['name' => 'Manage Licenses', 'slug' => 'system.licenses', 'description' => 'Manage system licenses', 'module' => 'system', 'action' => 'licenses'],
        ];

        foreach ($permissions as $permissionData) {
            Permission::firstOrCreate(['slug' => $permissionData['slug']], $permissionData);
        }

        // Update existing roles with descriptions and system role flags
        $roles = [
            'superadmin' => [
                'description' => 'Full system access and management. Can manage all clinics, users, and system settings.',
                'is_system_role' => true,
                'permissions' => [
                    // System Administration
                    'system.admin', 'system.info', 'system.licenses',

                    // Clinic Management
                    'clinics.manage', 'clinics.view', 'clinics.create', 'clinics.edit', 'clinics.delete',

                    // User Management
                    'users.manage', 'users.view', 'users.create', 'users.edit', 'users.delete', 'users.activate', 'users.deactivate',

                    // Role & Permission Management
                    'roles.manage', 'roles.view', 'roles.create', 'roles.edit', 'roles.delete',
                    'permissions.manage', 'permissions.view', 'permissions.create', 'permissions.edit', 'permissions.delete',

                    // Doctor Management
                    'doctors.manage', 'doctors.view', 'doctors.create', 'doctors.edit', 'doctors.delete',

                    // Patient Management
                    'patients.manage', 'patients.view', 'patients.create', 'patients.edit', 'patients.delete',

                    // Clinical Operations
                    'appointments.manage', 'appointments.view', 'appointments.create', 'appointments.edit', 'appointments.cancel', 'appointments.delete', 'appointments.checkin',
                    'encounters.manage', 'encounters.view', 'encounters.create', 'encounters.edit', 'encounters.delete', 'encounters.complete',
                    'prescriptions.manage', 'prescriptions.view', 'prescriptions.create', 'prescriptions.edit', 'prescriptions.delete', 'prescriptions.download',
                    'medical_records.manage', 'medical_records.view', 'medical_records.create', 'medical_records.edit', 'medical_records.delete',
                    'lab_results.manage', 'lab_results.view', 'lab_results.create', 'lab_results.edit', 'lab_results.delete',
                    'file_assets.manage', 'file_assets.view', 'file_assets.upload', 'file_assets.download', 'file_assets.delete',

                    // Queue Management
                    'queue.manage', 'queue.view', 'queue.add', 'queue.remove', 'queue.process',

                    // Infrastructure
                    'rooms.manage', 'rooms.view', 'rooms.create', 'rooms.edit', 'rooms.delete',
                    'insurance.manage', 'insurance.view', 'insurance.create', 'insurance.edit', 'insurance.delete',

                    // Billing & Financial
                    'billing.manage', 'billing.view', 'billing.create', 'billing.edit', 'billing.delete',

                    // Reporting & Analytics
                    'reports.view', 'reports.export', 'reports.generate',
                    'activity_logs.view', 'activity_logs.export',

                    // Scheduling
                    'schedule.view', 'schedule.manage',

                    // Notifications
                    'notifications.manage', 'notifications.view', 'notifications.create', 'notifications.edit', 'notifications.delete',

                    // System Settings
                    'settings.manage', 'settings.view',

                    // Dashboard & Search
                    'dashboard.view', 'dashboard.stats',
                    'search.global', 'search.patients', 'search.doctors',

                    // Profile
                    'profile.view', 'profile.edit',

                    // Medical Representative Features
                    'products.manage', 'products.view', 'products.create', 'products.edit', 'products.delete',
                    'meetings.manage', 'meetings.view', 'meetings.create', 'meetings.edit', 'meetings.delete',
                    'interactions.manage', 'interactions.view', 'interactions.create', 'interactions.edit', 'interactions.delete',
                    'medrep_visits.manage', 'medrep_visits.view', 'medrep_visits.create', 'medrep_visits.edit', 'medrep_visits.delete',
                ]
            ],
            'admin' => [
                'description' => 'Full clinic access and management. Can manage clinic operations, staff, and patients.',
                'is_system_role' => false,
                'permissions' => [
                    // Clinic Management (limited to own clinic)
                    'clinics.view', 'clinics.edit',

                    // User Management (clinic staff only)
                    'users.view', 'users.create', 'users.edit', 'users.activate', 'users.deactivate',

                    // Role Management (view only)
                    'roles.view',

                    // Doctor Management
                    'doctors.manage', 'doctors.view', 'doctors.create', 'doctors.edit', 'doctors.delete',

                    // Patient Management
                    'patients.manage', 'patients.view', 'patients.create', 'patients.edit', 'patients.delete',

                    // Clinical Operations
                    'appointments.manage', 'appointments.view', 'appointments.create', 'appointments.edit', 'appointments.cancel', 'appointments.delete', 'appointments.checkin',
                    'encounters.manage', 'encounters.view', 'encounters.create', 'encounters.edit', 'encounters.delete', 'encounters.complete',
                    'prescriptions.manage', 'prescriptions.view', 'prescriptions.create', 'prescriptions.edit', 'prescriptions.delete', 'prescriptions.download',
                    'medical_records.manage', 'medical_records.view', 'medical_records.create', 'medical_records.edit', 'medical_records.delete',
                    'lab_results.manage', 'lab_results.view', 'lab_results.create', 'lab_results.edit', 'lab_results.delete',
                    'file_assets.manage', 'file_assets.view', 'file_assets.upload', 'file_assets.download', 'file_assets.delete',

                    // Queue Management
                    'queue.manage', 'queue.view', 'queue.add', 'queue.remove', 'queue.process',

                    // Infrastructure
                    'rooms.manage', 'rooms.view', 'rooms.create', 'rooms.edit', 'rooms.delete',
                    'insurance.manage', 'insurance.view', 'insurance.create', 'insurance.edit', 'insurance.delete',

                    // Billing & Financial
                    'billing.manage', 'billing.view', 'billing.create', 'billing.edit', 'billing.delete',

                    // Reporting & Analytics
                    'reports.view', 'reports.export', 'reports.generate',
                    'activity_logs.view', 'activity_logs.export',

                    // Scheduling
                    'schedule.view', 'schedule.manage',

                    // Notifications
                    'notifications.manage', 'notifications.view', 'notifications.create', 'notifications.edit', 'notifications.delete',

                    // Settings (clinic level)
                    'settings.view',

                    // Dashboard & Search
                    'dashboard.view', 'dashboard.stats',
                    'search.global', 'search.patients', 'search.doctors',

                    // Profile
                    'profile.view', 'profile.edit',
                ]
            ],
            'doctor' => [
                'description' => 'Medical professional who can manage appointments, medical records, and prescriptions.',
                'is_system_role' => false,
                'permissions' => [
                    // Basic Information Access
                    'clinics.view', 'doctors.view',

                    // Patient Management (limited)
                    'patients.view', 'patients.edit',

                    // Clinical Operations (full access)
                    'appointments.view', 'appointments.create', 'appointments.edit', 'appointments.cancel',
                    'encounters.view', 'encounters.create', 'encounters.edit', 'encounters.complete',
                    'prescriptions.view', 'prescriptions.create', 'prescriptions.edit', 'prescriptions.delete', 'prescriptions.download',
                    'medical_records.view', 'medical_records.create', 'medical_records.edit',
                    'lab_results.view', 'lab_results.create', 'lab_results.edit',
                    'file_assets.view', 'file_assets.upload', 'file_assets.download',

                    // Queue Management (process only)
                    'queue.view', 'queue.process',

                    // Infrastructure (view only)
                    'rooms.view', 'insurance.view',

                    // Billing (view only)
                    'billing.view',

                    // Reporting (limited)
                    'reports.view', 'reports.export',

                    // Scheduling (personal)
                    'schedule.view', 'schedule.manage',

                    // Notifications (view only)
                    'notifications.view',

                    // Dashboard & Search
                    'dashboard.view', 'dashboard.stats',
                    'search.patients', 'search.doctors',

                    // Profile
                    'profile.view', 'profile.edit',
                ]
            ],
            'receptionist' => [
                'description' => 'Front desk staff who can schedule appointments, manage patient check-ins, and handle billing support.',
                'is_system_role' => false,
                'permissions' => [
                    // Basic Information Access
                    'clinics.view', 'doctors.view',

                    // Patient Management (registration and basic info)
                    'patients.view', 'patients.create', 'patients.edit',

                    // Appointment Management (scheduling and check-in)
                    'appointments.view', 'appointments.create', 'appointments.edit', 'appointments.cancel', 'appointments.checkin',

                    // Encounter Management (create and basic info)
                    'encounters.view', 'encounters.create',

                    // Queue Management (full access)
                    'queue.view', 'queue.add', 'queue.remove', 'queue.process',

                    // Infrastructure (view only)
                    'rooms.view', 'insurance.view',

                    // Billing (create and edit)
                    'billing.view', 'billing.create', 'billing.edit',

                    // Reporting (view only)
                    'reports.view',

                    // Scheduling (view only)
                    'schedule.view',

                    // Notifications (view only)
                    'notifications.view',

                    // Dashboard & Search
                    'dashboard.view', 'dashboard.stats',
                    'search.patients', 'search.doctors',

                    // Profile
                    'profile.view', 'profile.edit',
                ]
            ],
            'patient' => [
                'description' => 'Patient who can book appointments, view records, and download prescriptions.',
                'is_system_role' => false,
                'permissions' => [
                    // Basic Information Access
                    'clinics.view', 'doctors.view',

                    // Own Patient Information
                    'patients.view', // Limited to own records

                    // Appointment Management (own appointments only)
                    'appointments.view', 'appointments.create', 'appointments.cancel',

                    // Own Medical Records (view only)
                    'encounters.view', // Limited to own encounters
                    'prescriptions.view', 'prescriptions.download',
                    'medical_records.view', // Limited to own records
                    'lab_results.view', // Limited to own results
                    'file_assets.view', 'file_assets.download', // Limited to own files

                    // Scheduling (view only)
                    'schedule.view',

                    // Notifications (own only)
                    'notifications.view',

                    // Dashboard (own data only)
                    'dashboard.view',

                    // Profile (own only)
                    'profile.view', 'profile.edit',
                ]
            ],
            'medrep' => [
                'description' => 'Medical representative who can manage product details, schedule doctor meetings, and track interactions.',
                'is_system_role' => false,
                'permissions' => [
                    // Basic Information Access
                    'clinics.view', 'doctors.view',

                    // Product Management (full access)
                    'products.manage', 'products.view', 'products.create', 'products.edit', 'products.delete',

                    // Meeting Management (full access)
                    'meetings.manage', 'meetings.view', 'meetings.create', 'meetings.edit', 'meetings.delete',

                    // Interaction Tracking (full access)
                    'interactions.manage', 'interactions.view', 'interactions.create', 'interactions.edit', 'interactions.delete',

                    // Medrep Visit Management (full access)
                    'medrep_visits.manage', 'medrep_visits.view', 'medrep_visits.create', 'medrep_visits.edit', 'medrep_visits.delete',

                    // Scheduling (meeting schedule)
                    'schedule.view', 'schedule.manage',

                    // Reporting (interaction reports)
                    'reports.view', 'reports.export',

                    // Notifications (view only)
                    'notifications.view',

                    // Dashboard & Search
                    'dashboard.view', 'dashboard.stats',
                    'search.doctors',

                    // Profile
                    'profile.view', 'profile.edit',
                ]
            ]
        ];

        foreach ($roles as $roleName => $roleData) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $role->update([
                    'description' => $roleData['description'],
                    'is_system_role' => $roleData['is_system_role'],
                    'permissions_config' => $roleData['permissions']
                ]);

                // Assign permissions to role
                $permissionIds = Permission::whereIn('slug', $roleData['permissions'])->pluck('id');
                $role->permissions()->sync($permissionIds);
            }
        }

        $this->command->info('Permissions and roles configured successfully!');
    }
}
