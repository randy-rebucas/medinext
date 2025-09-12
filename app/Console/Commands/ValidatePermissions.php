<?php

namespace App\Console\Commands;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;

class ValidatePermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:validate {--fix : Fix permission issues automatically}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Validate the permissions and roles system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Validating Permissions and Roles System...');
        $this->newLine();

        $issues = [];
        $fixes = [];

        // 1. Check if all required permissions exist
        $this->info('1. Checking required permissions...');
        $requiredPermissions = $this->getRequiredPermissions();
        $existingPermissions = Permission::pluck('slug')->toArray();

        foreach ($requiredPermissions as $permission) {
            if (!in_array($permission, $existingPermissions)) {
                $issues[] = "Missing permission: {$permission}";
                if ($this->option('fix')) {
                    $this->createPermission($permission);
                    $fixes[] = "Created permission: {$permission}";
                }
            }
        }

        // 2. Check if all required roles exist
        $this->info('2. Checking required roles...');
        $requiredRoles = ['superadmin', 'admin', 'doctor', 'receptionist', 'patient', 'medrep'];
        $existingRoles = Role::pluck('name')->toArray();

        foreach ($requiredRoles as $role) {
            if (!in_array($role, $existingRoles)) {
                $issues[] = "Missing role: {$role}";
                if ($this->option('fix')) {
                    $this->createRole($role);
                    $fixes[] = "Created role: {$role}";
                }
            }
        }

        // 3. Validate role permissions
        $this->info('3. Validating role permissions...');
        $roles = Role::with('permissions')->get();

        foreach ($roles as $role) {
            $roleIssues = $role->validatePermissions();
            foreach ($roleIssues as $issue) {
                $issues[] = "Role '{$role->name}': {$issue}";
            }

            // Check minimum permissions
            if (!$role->hasMinimumPermissions()) {
                $issues[] = "Role '{$role->name}' lacks minimum required permissions";
            }
        }

        // 4. Check for orphaned permissions
        $this->info('4. Checking for orphaned permissions...');
        $orphanedPermissions = Permission::whereDoesntHave('roles')->get();
        foreach ($orphanedPermissions as $permission) {
            $issues[] = "Orphaned permission: {$permission->slug} (not assigned to any role)";
        }

        // 5. Check for conflicting permissions
        $this->info('5. Checking for conflicting permissions...');
        $permissions = Permission::all();
        foreach ($permissions as $permission) {
            foreach ($permissions as $other) {
                if ($permission->id !== $other->id && $permission->conflictsWith($other)) {
                    $issues[] = "Conflicting permissions: {$permission->slug} conflicts with {$other->slug}";
                }
            }
        }

        // 6. Validate user permissions
        $this->info('6. Validating user permissions...');
        $users = User::with(['userClinicRoles.role.permissions'])->get();
        foreach ($users as $user) {
            if (!$user->hasValidAccess()) {
                $issues[] = "User '{$user->email}' has no valid access (trial expired, no license)";
            }
        }

        // Display results
        $this->newLine();
        if (empty($issues)) {
            $this->info('✅ All validations passed! The permissions system is properly configured.');
        } else {
            $this->error('❌ Found ' . count($issues) . ' issues:');
            foreach ($issues as $issue) {
                $this->line("  • {$issue}");
            }
        }

        if (!empty($fixes)) {
            $this->newLine();
            $this->info('🔧 Applied ' . count($fixes) . ' fixes:');
            foreach ($fixes as $fix) {
                $this->line("  • {$fix}");
            }
        }

        // Display statistics
        $this->newLine();
        $this->info('📊 System Statistics:');
        $this->line("  • Total Permissions: " . Permission::count());
        $this->line("  • Total Roles: " . Role::count());
        $this->line("  • Total Users: " . User::count());
        $this->line("  • Active Users: " . User::where('is_active', true)->count());

        return empty($issues) ? 0 : 1;
    }

    /**
     * Get required permissions for the system
     */
    private function getRequiredPermissions(): array
    {
        return [
            // System permissions
            'system.admin', 'system.info', 'system.licenses',

            // Clinic permissions
            'clinics.manage', 'clinics.view', 'clinics.create', 'clinics.edit', 'clinics.delete',

            // User permissions
            'users.manage', 'users.view', 'users.create', 'users.edit', 'users.delete', 'users.activate', 'users.deactivate',

            // Role permissions
            'roles.manage', 'roles.view', 'roles.create', 'roles.edit', 'roles.delete',

            // Permission permissions
            'permissions.manage', 'permissions.view', 'permissions.create', 'permissions.edit', 'permissions.delete',

            // Doctor permissions
            'doctors.manage', 'doctors.view', 'doctors.create', 'doctors.edit', 'doctors.delete',

            // Patient permissions
            'patients.manage', 'patients.view', 'patients.create', 'patients.edit', 'patients.delete',

            // Appointment permissions
            'appointments.manage', 'appointments.view', 'appointments.create', 'appointments.edit', 'appointments.cancel', 'appointments.delete', 'appointments.checkin',

            // Encounter permissions
            'encounters.manage', 'encounters.view', 'encounters.create', 'encounters.edit', 'encounters.delete', 'encounters.complete',

            // Prescription permissions
            'prescriptions.manage', 'prescriptions.view', 'prescriptions.create', 'prescriptions.edit', 'prescriptions.delete', 'prescriptions.download',

            // Medical Records permissions
            'medical_records.manage', 'medical_records.view', 'medical_records.create', 'medical_records.edit', 'medical_records.delete',

            // Queue permissions
            'queue.manage', 'queue.view', 'queue.add', 'queue.remove', 'queue.process',

            // Lab Results permissions
            'lab_results.manage', 'lab_results.view', 'lab_results.create', 'lab_results.edit', 'lab_results.delete',

            // File Assets permissions
            'file_assets.manage', 'file_assets.view', 'file_assets.upload', 'file_assets.download', 'file_assets.delete',

            // Room permissions
            'rooms.manage', 'rooms.view', 'rooms.create', 'rooms.edit', 'rooms.delete',

            // Insurance permissions
            'insurance.manage', 'insurance.view', 'insurance.create', 'insurance.edit', 'insurance.delete',

            // Notification permissions
            'notifications.manage', 'notifications.view', 'notifications.create', 'notifications.edit', 'notifications.delete',

            // Activity Log permissions
            'activity_logs.view', 'activity_logs.export',

            // Schedule permissions
            'schedule.view', 'schedule.manage',

            // Billing permissions
            'billing.manage', 'billing.view', 'billing.create', 'billing.edit', 'billing.delete',

            // Report permissions
            'reports.view', 'reports.export', 'reports.generate',

            // Settings permissions
            'settings.manage', 'settings.view',

            // Profile permissions
            'profile.view', 'profile.edit',

            // Product permissions
            'products.manage', 'products.view', 'products.create', 'products.edit', 'products.delete',

            // Meeting permissions
            'meetings.manage', 'meetings.view', 'meetings.create', 'meetings.edit', 'meetings.delete',

            // Interaction permissions
            'interactions.manage', 'interactions.view', 'interactions.create', 'interactions.edit', 'interactions.delete',

            // Medrep Visit permissions
            'medrep_visits.manage', 'medrep_visits.view', 'medrep_visits.create', 'medrep_visits.edit', 'medrep_visits.delete',

            // Dashboard permissions
            'dashboard.view', 'dashboard.stats',

            // Search permissions
            'search.global', 'search.patients', 'search.doctors',
        ];
    }

    /**
     * Create a missing permission
     */
    private function createPermission(string $slug): void
    {
        $parts = explode('.', $slug);
        $module = $parts[0];
        $action = $parts[1];

        $name = ucfirst($module) . ' ' . ucfirst(str_replace('_', ' ', $action));
        $description = "Permission for {$action} action on {$module} module";

        Permission::create([
            'name' => $name,
            'slug' => $slug,
            'description' => $description,
            'module' => $module,
            'action' => $action,
        ]);
    }

    /**
     * Create a missing role
     */
    private function createRole(string $name): void
    {
        $descriptions = [
            'superadmin' => 'Full system access and management',
            'admin' => 'Full clinic access and management',
            'doctor' => 'Medical professional with clinical access',
            'receptionist' => 'Front desk staff with scheduling access',
            'patient' => 'Patient with self-service access',
            'medrep' => 'Medical representative with product management access',
        ];

        Role::create([
            'name' => $name,
            'description' => $descriptions[$name] ?? 'Custom role',
            'is_system_role' => in_array($name, ['superadmin']),
        ]);
    }
}
