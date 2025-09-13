<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Role;
use App\Models\Permission;
use App\Models\User;
use App\Models\UserClinicRole;

class UpdateAdminPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:update-permissions {--force : Force update without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update admin role with comprehensive permissions for all modules';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Updating admin role permissions...');

        // Get admin role
        $adminRole = Role::where('name', 'admin')->first();

        if (!$adminRole) {
            $this->error('Admin role not found!');
            return 1;
        }

        // Define comprehensive admin permissions
        $adminPermissions = [
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
        ];

        // Get permission IDs
        $permissionIds = Permission::whereIn('slug', $adminPermissions)->pluck('id');

        if ($permissionIds->isEmpty()) {
            $this->error('No permissions found! Please run the permission seeder first.');
            return 1;
        }

        // Update admin role permissions
        $adminRole->permissions()->sync($permissionIds);

        $this->info("✅ Updated admin role with {$permissionIds->count()} permissions");

        // Update admin role description
        $adminRole->update([
            'description' => 'Full clinic access and management. Can manage clinic operations, staff, patients, appointments, reports, analytics, room management, and schedule management.',
            'permissions_config' => $adminPermissions
        ]);

        $this->info('✅ Updated admin role description');

        // Count admin users
        $adminUsers = UserClinicRole::where('role_id', $adminRole->id)->count();
        $this->info("✅ Found {$adminUsers} users with admin role");

        // Display summary
        $this->info('');
        $this->info('=== ADMIN PERMISSIONS UPDATE COMPLETE ===');
        $this->info('');
        $this->info('Admin role now has comprehensive permissions for:');
        $this->info('• Clinic Settings Management');
        $this->info('• Staff Management (Users, Doctors)');
        $this->info('• Patient Management');
        $this->info('• Appointment Management');
        $this->info('• Reports & Analytics');
        $this->info('• Room Management');
        $this->info('• Schedule Management');
        $this->info('• File Management');
        $this->info('• Notification Management');
        $this->info('• Billing & Financial Management');
        $this->info('• Activity Logs & Audit');
        $this->info('• Dashboard & Search');
        $this->info('• Profile Management');
        $this->info('');
        $this->info("Total permissions: {$permissionIds->count()}");
        $this->info("Admin users affected: {$adminUsers}");

        return 0;
    }
}
