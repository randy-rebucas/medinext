<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Role;
use App\Models\Permission;

class UpdateAllRolePermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'roles:update-permissions {--force : Force update without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update all role permissions with comprehensive access';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Updating all role permissions...');

        // Define comprehensive role permissions
        $rolePermissions = [
            'superadmin' => [
                // System Management
                'system.admin', 'system.info', 'system.licenses',
                
                // Clinic Management
                'clinics.manage', 'clinics.view', 'clinics.create', 'clinics.edit', 'clinics.delete',
                
                // User & Staff Management
                'users.manage', 'users.view', 'users.create', 'users.edit', 'users.delete',
                'users.activate', 'users.deactivate',
                
                // Role & Permission Management
                'roles.manage', 'roles.view', 'roles.create', 'roles.edit', 'roles.delete',
                'permissions.manage', 'permissions.view', 'permissions.create', 'permissions.edit', 'permissions.delete',
                
                // Doctor Management
                'doctors.manage', 'doctors.view', 'doctors.create', 'doctors.edit', 'doctors.delete',
                
                // Patient Management
                'patients.manage', 'patients.view', 'patients.create', 'patients.edit', 'patients.delete',
                
                // Appointment Management
                'appointments.manage', 'appointments.view', 'appointments.create', 'appointments.edit', 
                'appointments.cancel', 'appointments.delete', 'appointments.checkin',
                
                // Prescription Management
                'prescriptions.manage', 'prescriptions.view', 'prescriptions.create', 'prescriptions.edit', 
                'prescriptions.delete', 'prescriptions.download',
                
                // Medical Records
                'medical_records.manage', 'medical_records.view', 'medical_records.create', 
                'medical_records.edit', 'medical_records.delete',
                
                // Encounters
                'encounters.manage', 'encounters.view', 'encounters.create', 'encounters.edit', 
                'encounters.delete', 'encounters.complete',
                
                // Lab Results
                'lab_results.manage', 'lab_results.view', 'lab_results.create', 'lab_results.edit', 'lab_results.delete',
                
                // Queue Management
                'queue.manage', 'queue.view', 'queue.add', 'queue.remove', 'queue.process',
                
                // Room Management
                'rooms.manage', 'rooms.view', 'rooms.create', 'rooms.edit', 'rooms.delete',
                
                // Schedule Management
                'schedule.manage', 'schedule.view',
                
                // Billing & Financial
                'billing.manage', 'billing.view', 'billing.create', 'billing.edit', 'billing.delete',
                
                // Reports & Analytics
                'reports.view', 'reports.export', 'reports.generate',
                'activity_logs.view', 'activity_logs.export',
                
                // File Management
                'file_assets.manage', 'file_assets.view', 'file_assets.upload', 
                'file_assets.download', 'file_assets.delete',
                
                // Insurance
                'insurance.manage', 'insurance.view', 'insurance.create', 'insurance.edit', 'insurance.delete',
                
                // Notifications
                'notifications.manage', 'notifications.view', 'notifications.create', 
                'notifications.edit', 'notifications.delete',
                
                // Settings
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
            ],
            'admin' => [
                // Clinic Management
                'clinics.manage', 'clinics.view', 'clinics.create', 'clinics.edit', 'clinics.delete',
                
                // User & Staff Management
                'users.manage', 'users.view', 'users.create', 'users.edit', 'users.delete',
                'users.activate', 'users.deactivate',
                
                // Doctor Management
                'doctors.manage', 'doctors.view', 'doctors.create', 'doctors.edit', 'doctors.delete',
                
                // Patient Management
                'patients.manage', 'patients.view', 'patients.create', 'patients.edit', 'patients.delete',
                
                // Appointment Management
                'appointments.manage', 'appointments.view', 'appointments.create', 'appointments.edit', 
                'appointments.cancel', 'appointments.delete', 'appointments.checkin',
                
                // Prescription Management
                'prescriptions.manage', 'prescriptions.view', 'prescriptions.create', 'prescriptions.edit', 
                'prescriptions.delete', 'prescriptions.download',
                
                // Medical Records
                'medical_records.manage', 'medical_records.view', 'medical_records.create', 
                'medical_records.edit', 'medical_records.delete',
                
                // Encounters
                'encounters.manage', 'encounters.view', 'encounters.create', 'encounters.edit', 
                'encounters.delete', 'encounters.complete',
                
                // Lab Results
                'lab_results.manage', 'lab_results.view', 'lab_results.create', 'lab_results.edit', 'lab_results.delete',
                
                // Queue Management
                'queue.manage', 'queue.view', 'queue.add', 'queue.remove', 'queue.process',
                
                // Room Management
                'rooms.manage', 'rooms.view', 'rooms.create', 'rooms.edit', 'rooms.delete',
                
                // Schedule Management
                'schedule.manage', 'schedule.view',
                
                // Billing & Financial
                'billing.manage', 'billing.view', 'billing.create', 'billing.edit', 'billing.delete',
                
                // Reports & Analytics
                'reports.view', 'reports.export', 'reports.generate',
                'activity_logs.view', 'activity_logs.export',
                
                // File Management
                'file_assets.manage', 'file_assets.view', 'file_assets.upload', 
                'file_assets.download', 'file_assets.delete',
                
                // Insurance
                'insurance.manage', 'insurance.view', 'insurance.create', 'insurance.edit', 'insurance.delete',
                
                // Notifications
                'notifications.manage', 'notifications.view', 'notifications.create', 
                'notifications.edit', 'notifications.delete',
                
                // Settings
                'settings.manage', 'settings.view',
                
                // Dashboard & Search
                'dashboard.view', 'dashboard.stats',
                'search.global', 'search.patients', 'search.doctors',
                
                // Profile
                'profile.view', 'profile.edit'
            ],
            'doctor' => [
                // Clinic & Doctor Info
                'clinics.view', 'doctors.view',
                
                // Patient Management
                'patients.view', 'patients.edit',
                
                // Appointment Management
                'appointments.view', 'appointments.create', 'appointments.edit', 'appointments.cancel',
                
                // Prescription Management
                'prescriptions.view', 'prescriptions.create', 'prescriptions.edit', 'prescriptions.delete', 'prescriptions.download',
                
                // Medical Records
                'medical_records.view', 'medical_records.create', 'medical_records.edit',
                
                // Encounters
                'encounters.view', 'encounters.create', 'encounters.edit', 'encounters.complete',
                
                // Lab Results
                'lab_results.view', 'lab_results.create', 'lab_results.edit',
                
                // Schedule Management
                'schedule.view', 'schedule.manage',
                
                // Queue Management
                'queue.view', 'queue.process',
                
                // Reports & Analytics
                'reports.view',
                
                // File Management
                'file_assets.view', 'file_assets.upload', 'file_assets.download',
                
                // Dashboard & Search
                'dashboard.view', 'search.patients',
                
                // Profile
                'profile.view', 'profile.edit'
            ],
            'receptionist' => [
                // Clinic & Doctor Info
                'clinics.view', 'doctors.view',
                
                // Patient Management
                'patients.view', 'patients.create', 'patients.edit',
                
                // Appointment Management
                'appointments.view', 'appointments.create', 'appointments.edit', 'appointments.cancel', 'appointments.checkin',
                
                // Billing Management
                'billing.view', 'billing.create', 'billing.edit',
                
                // Schedule Management
                'schedule.view',
                
                // Queue Management
                'queue.view', 'queue.add', 'queue.remove', 'queue.process',
                
                // Reports
                'reports.view',
                
                // File Management
                'file_assets.view', 'file_assets.upload', 'file_assets.download',
                
                // Insurance
                'insurance.view', 'insurance.create', 'insurance.edit',
                
                // Dashboard & Search
                'dashboard.view', 'search.patients', 'search.doctors',
                
                // Profile
                'profile.view', 'profile.edit'
            ],
            'patient' => [
                // Clinic & Doctor Info
                'clinics.view', 'doctors.view',
                
                // Appointment Management
                'appointments.view', 'appointments.create', 'appointments.cancel',
                
                // Prescription Management
                'prescriptions.view', 'prescriptions.download',
                
                // Medical Records
                'medical_records.view',
                
                // Lab Results
                'lab_results.view',
                
                // Billing
                'billing.view',
                
                // File Management
                'file_assets.view', 'file_assets.download',
                
                // Insurance
                'insurance.view',
                
                // Notifications
                'notifications.view',
                
                // Profile
                'profile.view', 'profile.edit'
            ],
            'medrep' => [
                // Clinic & Doctor Info
                'clinics.view', 'doctors.view',
                
                // Product Management
                'products.view', 'products.create', 'products.edit',
                
                // Meeting Management
                'meetings.view', 'meetings.create', 'meetings.edit', 'meetings.delete',
                
                // Interaction Management
                'interactions.view', 'interactions.create', 'interactions.edit',
                
                // Medical Representative Visits
                'medrep_visits.view', 'medrep_visits.create', 'medrep_visits.edit',
                
                // Schedule Management
                'schedule.view',
                
                // Reports
                'reports.view',
                
                // File Management
                'file_assets.view', 'file_assets.upload', 'file_assets.download',
                
                // Dashboard & Search
                'dashboard.view', 'search.doctors',
                
                // Profile
                'profile.view', 'profile.edit'
            ]
        ];

        $totalUpdated = 0;

        foreach ($rolePermissions as $roleName => $permissions) {
            $role = Role::where('name', $roleName)->first();
            
            if (!$role) {
                $this->error("Role '{$roleName}' not found!");
                continue;
            }

            // Get permission IDs
            $permissionIds = Permission::whereIn('slug', $permissions)->pluck('id');
            
            if ($permissionIds->isEmpty()) {
                $this->error("No permissions found for role '{$roleName}'!");
                continue;
            }

            // Update role permissions
            $role->permissions()->sync($permissionIds);
            
            $this->info("✅ Updated {$roleName} role with {$permissionIds->count()} permissions");
            $totalUpdated++;
        }

        $this->info('');
        $this->info('=== ALL ROLE PERMISSIONS UPDATE COMPLETE ===');
        $this->info('');
        $this->info("Updated {$totalUpdated} roles with comprehensive permissions");
        $this->info('');
        $this->info('Role Summary:');
        $this->info('• Superadmin: Full system access (130+ permissions)');
        $this->info('• Admin: Full clinic access (97 permissions)');
        $this->info('• Doctor: Clinical workflow (25+ permissions)');
        $this->info('• Receptionist: Front desk operations (20+ permissions)');
        $this->info('• Patient: Self-service access (15+ permissions)');
        $this->info('• Medrep: Medical representative features (18+ permissions)');

        return 0;
    }
}
