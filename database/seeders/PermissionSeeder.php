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
        // Create all permissions with fine-grained scopes
        $permissions = [
            // Patient permissions (read/write scopes)
            ['name' => 'Patient Read', 'slug' => 'patient.read', 'description' => 'Read patient information and records', 'module' => 'patient', 'action' => 'read'],
            ['name' => 'Patient Write', 'slug' => 'patient.write', 'description' => 'Create and modify patient information', 'module' => 'patient', 'action' => 'write'],
            ['name' => 'Patient Delete', 'slug' => 'patient.delete', 'description' => 'Delete patient records', 'module' => 'patient', 'action' => 'delete'],
            ['name' => 'Patient Manage', 'slug' => 'patient.manage', 'description' => 'Full patient management access', 'module' => 'patient', 'action' => 'manage'],

            // EMR permissions (read/write scopes)
            ['name' => 'EMR Read', 'slug' => 'emr.read', 'description' => 'Read electronic medical records', 'module' => 'emr', 'action' => 'read'],
            ['name' => 'EMR Write', 'slug' => 'emr.write', 'description' => 'Create and modify medical records', 'module' => 'emr', 'action' => 'write'],
            ['name' => 'EMR Delete', 'slug' => 'emr.delete', 'description' => 'Delete medical records', 'module' => 'emr', 'action' => 'delete'],
            ['name' => 'EMR Manage', 'slug' => 'emr.manage', 'description' => 'Full EMR management access', 'module' => 'emr', 'action' => 'manage'],

            // Schedule permissions
            ['name' => 'Schedule View', 'slug' => 'schedule.view', 'description' => 'View appointment schedules', 'module' => 'schedule', 'action' => 'view'],
            ['name' => 'Schedule Manage', 'slug' => 'schedule.manage', 'description' => 'Manage appointment schedules and availability', 'module' => 'schedule', 'action' => 'manage'],

            // Prescription permissions
            ['name' => 'Prescription Issue', 'slug' => 'rx.issue', 'description' => 'Issue prescriptions to patients', 'module' => 'rx', 'action' => 'issue'],
            ['name' => 'Prescription View', 'slug' => 'rx.view', 'description' => 'View prescription information', 'module' => 'rx', 'action' => 'view'],
            ['name' => 'Prescription Edit', 'slug' => 'rx.edit', 'description' => 'Modify prescriptions', 'module' => 'rx', 'action' => 'edit'],
            ['name' => 'Prescription Download', 'slug' => 'rx.download', 'description' => 'Download prescription documents', 'module' => 'rx', 'action' => 'download'],

            // Billing permissions
            ['name' => 'Billing View', 'slug' => 'billing.view', 'description' => 'View billing information and invoices', 'module' => 'billing', 'action' => 'view'],
            ['name' => 'Billing Create', 'slug' => 'billing.create', 'description' => 'Create billing records', 'module' => 'billing', 'action' => 'create'],
            ['name' => 'Billing Edit', 'slug' => 'billing.edit', 'description' => 'Modify billing information', 'module' => 'billing', 'action' => 'edit'],
            ['name' => 'Billing Manage', 'slug' => 'billing.manage', 'description' => 'Full billing management access', 'module' => 'billing', 'action' => 'manage'],

            // Settings permissions
            ['name' => 'Settings Manage', 'slug' => 'settings.manage', 'description' => 'Manage system and clinic settings', 'module' => 'settings', 'action' => 'manage'],
            ['name' => 'Settings View', 'slug' => 'settings.view', 'description' => 'View system settings', 'module' => 'settings', 'action' => 'view'],

            // Staff management permissions
            ['name' => 'Staff Manage', 'slug' => 'staff.manage', 'description' => 'Manage clinic staff and roles', 'module' => 'staff', 'action' => 'manage'],
            ['name' => 'Staff View', 'slug' => 'staff.view', 'description' => 'View staff information', 'module' => 'staff', 'action' => 'view'],
            ['name' => 'Staff Create', 'slug' => 'staff.create', 'description' => 'Add new staff members', 'module' => 'staff', 'action' => 'create'],
            ['name' => 'Staff Edit', 'slug' => 'staff.edit', 'description' => 'Edit staff information', 'module' => 'staff', 'action' => 'edit'],

            // Clinical notes permissions
            ['name' => 'Clinical Notes Read', 'slug' => 'clinical_notes.read', 'description' => 'Read clinical notes and documentation', 'module' => 'clinical_notes', 'action' => 'read'],
            ['name' => 'Clinical Notes Write', 'slug' => 'clinical_notes.write', 'description' => 'Create and modify clinical notes', 'module' => 'clinical_notes', 'action' => 'write'],

            // MedRep specific permissions
            ['name' => 'MedRep Schedule', 'slug' => 'medrep.schedule', 'description' => 'Schedule visits with doctors', 'module' => 'medrep', 'action' => 'schedule'],
            ['name' => 'MedRep Upload', 'slug' => 'medrep.upload', 'description' => 'Upload product sheets and materials', 'module' => 'medrep', 'action' => 'upload'],
            ['name' => 'MedRep View', 'slug' => 'medrep.view', 'description' => 'View medical representative information', 'module' => 'medrep', 'action' => 'view'],

            // Super Admin permissions
            ['name' => 'Tenants Manage', 'slug' => 'tenants.manage', 'description' => 'Manage platform tenants and clinics', 'module' => 'tenants', 'action' => 'manage'],
            ['name' => 'Plans Manage', 'slug' => 'plans.manage', 'description' => 'Manage subscription plans', 'module' => 'plans', 'action' => 'manage'],
            ['name' => 'Global Settings', 'slug' => 'global.settings', 'description' => 'Manage global platform settings', 'module' => 'global', 'action' => 'settings'],

            // Legacy permissions for backward compatibility
            ['name' => 'Manage Clinics', 'slug' => 'clinics.manage', 'description' => 'Full control over clinic operations', 'module' => 'clinics', 'action' => 'manage'],
            ['name' => 'View Clinics', 'slug' => 'clinics.view', 'description' => 'View clinic information', 'module' => 'clinics', 'action' => 'view'],
            ['name' => 'Create Clinics', 'slug' => 'clinics.create', 'description' => 'Create new clinics', 'module' => 'clinics', 'action' => 'create'],
            ['name' => 'Edit Clinics', 'slug' => 'clinics.edit', 'description' => 'Edit clinic information', 'module' => 'clinics', 'action' => 'edit'],
            ['name' => 'Delete Clinics', 'slug' => 'clinics.delete', 'description' => 'Delete clinics', 'module' => 'clinics', 'action' => 'delete'],

            ['name' => 'Manage Doctors', 'slug' => 'doctors.manage', 'description' => 'Full control over doctor operations', 'module' => 'doctors', 'action' => 'manage'],
            ['name' => 'View Doctors', 'slug' => 'doctors.view', 'description' => 'View doctor information', 'module' => 'doctors', 'action' => 'view'],
            ['name' => 'Create Doctors', 'slug' => 'doctors.create', 'description' => 'Add new doctors', 'module' => 'doctors', 'action' => 'create'],
            ['name' => 'Edit Doctors', 'slug' => 'doctors.edit', 'description' => 'Edit doctor information', 'module' => 'doctors', 'action' => 'edit'],
            ['name' => 'Delete Doctors', 'slug' => 'doctors.delete', 'description' => 'Remove doctors', 'module' => 'doctors', 'action' => 'delete'],

            ['name' => 'Manage Appointments', 'slug' => 'appointments.manage', 'description' => 'Full control over appointment operations', 'module' => 'appointments', 'action' => 'manage'],
            ['name' => 'View Appointments', 'slug' => 'appointments.view', 'description' => 'View appointment information', 'module' => 'appointments', 'action' => 'view'],
            ['name' => 'Create Appointments', 'slug' => 'appointments.create', 'description' => 'Schedule new appointments', 'module' => 'appointments', 'action' => 'create'],
            ['name' => 'Edit Appointments', 'slug' => 'appointments.edit', 'description' => 'Modify appointments', 'module' => 'appointments', 'action' => 'edit'],
            ['name' => 'Cancel Appointments', 'slug' => 'appointments.cancel', 'description' => 'Cancel appointments', 'module' => 'appointments', 'action' => 'cancel'],
            ['name' => 'Delete Appointments', 'slug' => 'appointments.delete', 'description' => 'Remove appointments', 'module' => 'appointments', 'action' => 'delete'],
            ['name' => 'Check-in Patients', 'slug' => 'appointments.checkin', 'description' => 'Check-in patients for appointments', 'module' => 'appointments', 'action' => 'checkin'],

            ['name' => 'Manage Prescriptions', 'slug' => 'prescriptions.manage', 'description' => 'Full control over prescription operations', 'module' => 'prescriptions', 'action' => 'manage'],
            ['name' => 'View Prescriptions', 'slug' => 'prescriptions.view', 'description' => 'View prescription information', 'module' => 'prescriptions', 'action' => 'view'],
            ['name' => 'Create Prescriptions', 'slug' => 'prescriptions.create', 'description' => 'Write new prescriptions', 'module' => 'prescriptions', 'action' => 'create'],
            ['name' => 'Edit Prescriptions', 'slug' => 'prescriptions.edit', 'description' => 'Modify prescriptions', 'module' => 'prescriptions', 'action' => 'edit'],
            ['name' => 'Delete Prescriptions', 'slug' => 'prescriptions.delete', 'description' => 'Remove prescriptions', 'module' => 'prescriptions', 'action' => 'delete'],
            ['name' => 'Download Prescriptions', 'slug' => 'prescriptions.download', 'description' => 'Download prescription PDFs', 'module' => 'prescriptions', 'action' => 'download'],

            ['name' => 'View Medical Records', 'slug' => 'medical_records.view', 'description' => 'View patient medical records', 'module' => 'medical_records', 'action' => 'view'],
            ['name' => 'Create Medical Records', 'slug' => 'medical_records.create', 'description' => 'Create new medical records', 'module' => 'medical_records', 'action' => 'create'],
            ['name' => 'Edit Medical Records', 'slug' => 'medical_records.edit', 'description' => 'Modify medical records', 'module' => 'medical_records', 'action' => 'edit'],

            ['name' => 'Manage Users', 'slug' => 'users.manage', 'description' => 'Full control over user operations', 'module' => 'users', 'action' => 'manage'],
            ['name' => 'View Users', 'slug' => 'users.view', 'description' => 'View user information', 'module' => 'users', 'action' => 'view'],
            ['name' => 'Create Users', 'slug' => 'users.create', 'description' => 'Create new users', 'module' => 'users', 'action' => 'create'],
            ['name' => 'Edit Users', 'slug' => 'users.edit', 'description' => 'Edit user information', 'module' => 'users', 'action' => 'edit'],
            ['name' => 'Delete Users', 'slug' => 'users.delete', 'description' => 'Remove users', 'module' => 'users', 'action' => 'delete'],

            ['name' => 'Manage Roles', 'slug' => 'roles.manage', 'description' => 'Full control over role operations', 'module' => 'roles', 'action' => 'manage'],
            ['name' => 'View Roles', 'slug' => 'roles.view', 'description' => 'View role information', 'module' => 'roles', 'action' => 'view'],
            ['name' => 'Create Roles', 'slug' => 'roles.create', 'description' => 'Create new roles', 'module' => 'roles', 'action' => 'create'],
            ['name' => 'Edit Roles', 'slug' => 'roles.edit', 'description' => 'Edit role information', 'module' => 'roles', 'action' => 'edit'],
            ['name' => 'Delete Roles', 'slug' => 'roles.delete', 'description' => 'Remove roles', 'module' => 'roles', 'action' => 'delete'],

            ['name' => 'View Reports', 'slug' => 'reports.view', 'description' => 'View system reports', 'module' => 'reports', 'action' => 'view'],
            ['name' => 'Export Reports', 'slug' => 'reports.export', 'description' => 'Export reports to various formats', 'module' => 'reports', 'action' => 'export'],

            ['name' => 'Edit Profile', 'slug' => 'profile.edit', 'description' => 'Edit own profile information', 'module' => 'profile', 'action' => 'edit'],

            ['name' => 'View Products', 'slug' => 'products.view', 'description' => 'View product information', 'module' => 'products', 'action' => 'view'],
            ['name' => 'Create Products', 'slug' => 'products.create', 'description' => 'Add new products', 'module' => 'products', 'action' => 'create'],
            ['name' => 'Edit Products', 'slug' => 'products.edit', 'description' => 'Modify product information', 'module' => 'products', 'action' => 'edit'],

            ['name' => 'View Meetings', 'slug' => 'meetings.view', 'description' => 'View meeting information', 'module' => 'meetings', 'action' => 'view'],
            ['name' => 'Create Meetings', 'slug' => 'meetings.create', 'description' => 'Schedule new meetings', 'module' => 'meetings', 'action' => 'create'],
            ['name' => 'Edit Meetings', 'slug' => 'meetings.edit', 'description' => 'Modify meeting information', 'module' => 'meetings', 'action' => 'edit'],
            ['name' => 'Delete Meetings', 'slug' => 'meetings.delete', 'description' => 'Remove meetings', 'module' => 'meetings', 'action' => 'delete'],

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
                'description' => 'Platform administrator with full system access. Manages tenants, clinics, plans, and global settings.',
                'is_system_role' => true,
                'permissions' => [
                    // Platform management
                    'tenants.manage', 'plans.manage', 'global.settings',
                    // Full access to all modules
                    'patient.manage', 'emr.manage', 'schedule.manage', 'rx.issue', 'rx.view', 'rx.edit', 'rx.download',
                    'billing.manage', 'settings.manage', 'staff.manage', 'clinical_notes.read', 'clinical_notes.write',
                    // Legacy permissions for backward compatibility
                    'clinics.manage', 'doctors.manage', 'patients.manage', 'appointments.manage',
                    'prescriptions.manage', 'users.manage', 'roles.manage', 'billing.manage',
                    'reports.view', 'reports.export', 'settings.manage'
                ]
            ],
            'admin' => [
                'description' => 'Clinic owner/manager with full access within clinic. Can manage staff, billing, and settings.',
                'is_system_role' => false,
                'permissions' => [
                    // Full clinic management
                    'patient.manage', 'emr.manage', 'schedule.manage', 'rx.issue', 'rx.view', 'rx.edit', 'rx.download',
                    'billing.manage', 'settings.manage', 'staff.manage', 'clinical_notes.read', 'clinical_notes.write',
                    // Legacy permissions
                    'clinics.manage', 'doctors.manage', 'patients.manage', 'appointments.manage',
                    'prescriptions.manage', 'users.manage', 'billing.manage', 'reports.view',
                    'reports.export'
                ]
            ],
            'doctor' => [
                'description' => 'Medical professional who can view own schedule, manage assigned patients\' EMR, issue prescriptions, and view med samples.',
                'is_system_role' => false,
                'permissions' => [
                    // Core doctor permissions
                    'patient.read', 'patient.write', 'emr.read', 'emr.write', 'schedule.view', 'schedule.manage',
                    'rx.issue', 'rx.view', 'rx.edit', 'rx.download', 'clinical_notes.read', 'clinical_notes.write',
                    // Legacy permissions
                    'clinics.view', 'doctors.view', 'patients.view', 'patients.edit',
                    'appointments.view', 'appointments.create', 'appointments.edit', 'appointments.cancel',
                    'prescriptions.view', 'prescriptions.create', 'prescriptions.edit', 'prescriptions.delete',
                    'medical_records.view', 'medical_records.create', 'medical_records.edit',
                    'schedule.view', 'schedule.manage', 'reports.view'
                ]
            ],
            'receptionist' => [
                'description' => 'Front desk staff who can manage calendar, patients, visits, and billing support. No access to clinical notes by default.',
                'is_system_role' => false,
                'permissions' => [
                    // Receptionist permissions (no clinical notes access)
                    'patient.read', 'patient.write', 'schedule.view', 'schedule.manage', 'billing.view', 'billing.create', 'billing.edit',
                    // Legacy permissions
                    'clinics.view', 'doctors.view', 'patients.view', 'patients.create', 'patients.edit',
                    'appointments.view', 'appointments.create', 'appointments.edit', 'appointments.cancel',
                    'appointments.checkin', 'billing.view', 'billing.create', 'billing.edit',
                    'schedule.view', 'reports.view'
                ]
            ],
            'patient' => [
                'description' => 'Self-service portal access. Can book, reschedule, cancel appointments, view summary, and download prescriptions.',
                'is_system_role' => false,
                'permissions' => [
                    // Patient self-service permissions
                    'patient.read', 'schedule.view', 'rx.view', 'rx.download', 'profile.edit',
                    // Legacy permissions
                    'clinics.view', 'doctors.view', 'appointments.view', 'appointments.create',
                    'appointments.cancel', 'prescriptions.view', 'prescriptions.download',
                    'medical_records.view', 'profile.edit'
                ]
            ],
            'medrep' => [
                'description' => 'Medical representative who can schedule visits with doctors and upload product sheets. No access to patient data.',
                'is_system_role' => false,
                'permissions' => [
                    // MedRep specific permissions (no patient data access)
                    'medrep.schedule', 'medrep.upload', 'medrep.view', 'schedule.view', 'schedule.manage',
                    // Legacy permissions
                    'clinics.view', 'doctors.view', 'products.view', 'products.create', 'products.edit',
                    'meetings.view', 'meetings.create', 'meetings.edit', 'meetings.delete',
                    'interactions.view', 'interactions.create', 'interactions.edit',
                    'schedule.view', 'reports.view'
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
