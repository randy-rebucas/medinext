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
            ['name' => 'View Medical Records', 'slug' => 'medical_records.view', 'description' => 'View patient medical records', 'module' => 'medical_records', 'action' => 'view'],
            ['name' => 'Create Medical Records', 'slug' => 'medical_records.create', 'description' => 'Create new medical records', 'module' => 'medical_records', 'action' => 'create'],
            ['name' => 'Edit Medical Records', 'slug' => 'medical_records.edit', 'description' => 'Modify medical records', 'module' => 'medical_records', 'action' => 'edit'],

            // Schedule permissions
            ['name' => 'View Schedule', 'slug' => 'schedule.view', 'description' => 'View appointment schedules', 'module' => 'schedule', 'action' => 'view'],
            ['name' => 'Manage Schedule', 'slug' => 'schedule.manage', 'description' => 'Manage appointment schedules', 'module' => 'schedule', 'action' => 'manage'],

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

            // Billing permissions
            ['name' => 'Manage Billing', 'slug' => 'billing.manage', 'description' => 'Full control over billing operations', 'module' => 'billing', 'action' => 'manage'],
            ['name' => 'View Billing', 'slug' => 'billing.view', 'description' => 'View billing information', 'module' => 'billing', 'action' => 'view'],
            ['name' => 'Create Billing', 'slug' => 'billing.create', 'description' => 'Create new bills', 'module' => 'billing', 'action' => 'create'],
            ['name' => 'Edit Billing', 'slug' => 'billing.edit', 'description' => 'Modify billing information', 'module' => 'billing', 'action' => 'edit'],
            ['name' => 'Delete Billing', 'slug' => 'billing.delete', 'description' => 'Remove billing records', 'module' => 'billing', 'action' => 'delete'],

            // Report permissions
            ['name' => 'View Reports', 'slug' => 'reports.view', 'description' => 'View system reports', 'module' => 'reports', 'action' => 'view'],
            ['name' => 'Export Reports', 'slug' => 'reports.export', 'description' => 'Export reports to various formats', 'module' => 'reports', 'action' => 'export'],

            // Settings permissions
            ['name' => 'Manage Settings', 'slug' => 'settings.manage', 'description' => 'Manage system settings', 'module' => 'settings', 'action' => 'manage'],

            // Profile permissions
            ['name' => 'Edit Profile', 'slug' => 'profile.edit', 'description' => 'Edit own profile information', 'module' => 'profile', 'action' => 'edit'],

            // Product permissions (for medical representatives)
            ['name' => 'View Products', 'slug' => 'products.view', 'description' => 'View product information', 'module' => 'products', 'action' => 'view'],
            ['name' => 'Create Products', 'slug' => 'products.create', 'description' => 'Add new products', 'module' => 'products', 'action' => 'create'],
            ['name' => 'Edit Products', 'slug' => 'products.edit', 'description' => 'Modify product information', 'module' => 'products', 'action' => 'edit'],

            // Meeting permissions (for medical representatives)
            ['name' => 'View Meetings', 'slug' => 'meetings.view', 'description' => 'View meeting information', 'module' => 'meetings', 'action' => 'view'],
            ['name' => 'Create Meetings', 'slug' => 'meetings.create', 'description' => 'Schedule new meetings', 'module' => 'meetings', 'action' => 'create'],
            ['name' => 'Edit Meetings', 'slug' => 'meetings.edit', 'description' => 'Modify meeting information', 'module' => 'meetings', 'action' => 'edit'],
            ['name' => 'Delete Meetings', 'slug' => 'meetings.delete', 'description' => 'Remove meetings', 'module' => 'meetings', 'action' => 'delete'],

            // Interaction permissions (for medical representatives)
            ['name' => 'View Interactions', 'slug' => 'interactions.view', 'description' => 'View interaction records', 'module' => 'interactions', 'action' => 'view'],
            ['name' => 'Create Interactions', 'slug' => 'interactions.create', 'description' => 'Record new interactions', 'module' => 'interactions', 'action' => 'create'],
            ['name' => 'Edit Interactions', 'slug' => 'interactions.edit', 'description' => 'Modify interaction records', 'module' => 'interactions', 'action' => 'edit'],
        ];

        foreach ($permissions as $permissionData) {
            Permission::firstOrCreate(['slug' => $permissionData['slug']], $permissionData);
        }

        // Update existing roles with descriptions and system role flags
        $roles = [
            'superadmin' => [
                'description' => 'Full system access and management. Can manage all clinics, users, and system settings.',
                'is_system_role' => true,
                'permissions' => ['clinics.manage', 'doctors.manage', 'patients.manage', 'appointments.manage', 
                                'prescriptions.manage', 'users.manage', 'roles.manage', 'billing.manage', 
                                'reports.view', 'reports.export', 'settings.manage']
            ],
            'admin' => [
                'description' => 'Full clinic access and management. Can manage clinic operations, staff, and patients.',
                'is_system_role' => false,
                'permissions' => ['clinics.manage', 'doctors.manage', 'patients.manage', 'appointments.manage', 
                                'prescriptions.manage', 'users.manage', 'billing.manage', 'reports.view', 
                                'reports.export']
            ],
            'doctor' => [
                'description' => 'Medical professional who can manage appointments, medical records, and prescriptions.',
                'is_system_role' => false,
                'permissions' => ['clinics.view', 'doctors.view', 'patients.view', 'patients.edit', 
                                'appointments.view', 'appointments.create', 'appointments.edit', 'appointments.cancel',
                                'prescriptions.view', 'prescriptions.create', 'prescriptions.edit', 'prescriptions.delete',
                                'medical_records.view', 'medical_records.create', 'medical_records.edit',
                                'schedule.view', 'schedule.manage', 'reports.view']
            ],
            'receptionist' => [
                'description' => 'Front desk staff who can schedule appointments, manage patient check-ins, and handle billing support.',
                'is_system_role' => false,
                'permissions' => ['clinics.view', 'doctors.view', 'patients.view', 'patients.create', 'patients.edit',
                                'appointments.view', 'appointments.create', 'appointments.edit', 'appointments.cancel',
                                'appointments.checkin', 'billing.view', 'billing.create', 'billing.edit',
                                'schedule.view', 'reports.view']
            ],
            'patient' => [
                'description' => 'Patient who can book appointments, view records, and download prescriptions.',
                'is_system_role' => false,
                'permissions' => ['clinics.view', 'doctors.view', 'appointments.view', 'appointments.create', 
                                'appointments.cancel', 'prescriptions.view', 'prescriptions.download',
                                'medical_records.view', 'profile.edit']
            ],
            'medrep' => [
                'description' => 'Medical representative who can manage product details, schedule doctor meetings, and track interactions.',
                'is_system_role' => false,
                'permissions' => ['clinics.view', 'doctors.view', 'products.view', 'products.create', 'products.edit',
                                'meetings.view', 'meetings.create', 'meetings.edit', 'meetings.delete',
                                'interactions.view', 'interactions.create', 'interactions.edit',
                                'schedule.view', 'reports.view']
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
