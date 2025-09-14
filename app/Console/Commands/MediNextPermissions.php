<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Role;
use App\Models\Permission;
use App\Models\User;
use App\Models\UserClinicRole;

class MediNextPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'medinext:permissions
                            {action : Action to perform (update, validate, fix, status)}
                            {--role= : Specific role to target}
                            {--force : Force update without confirmation}
                            {--fix : Fix permission issues automatically}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage MediNext EMR permissions and roles system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->argument('action');
        $roleName = $this->option('role');
        $force = $this->option('force');
        $fix = $this->option('fix');

        switch ($action) {
            case 'update':
                return $this->updatePermissions($roleName, $force);
            case 'validate':
                return $this->validatePermissions($fix);
            case 'fix':
                return $this->fixPermissions();
            case 'status':
                return $this->showStatus();
            default:
                $this->error("Invalid action: {$action}");
                $this->info('Valid actions: update, validate, fix, status');
                return Command::FAILURE;
        }
    }

    private function updatePermissions(?string $roleName, bool $force): int
    {
        if (!$force) {
            if (!$this->confirm('This will update role permissions. Are you sure?')) {
                $this->info('Operation cancelled.');
                return Command::SUCCESS;
            }
        }

        $this->info('🔄 Updating MediNext EMR permissions...');

        try {
            if ($roleName) {
                $this->updateRolePermissions($roleName);
            } else {
                $this->updateAllRolePermissions();
            }

            $this->info('✅ Permissions updated successfully!');
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Failed to update permissions: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    private function validatePermissions(bool $fix): int
    {
        $this->info('🔍 Validating MediNext EMR Permissions System...');
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
                if ($fix) {
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
                if ($fix) {
                    $this->createRole($role);
                    $fixes[] = "Created role: {$role}";
                }
            }
        }

        // 3. Validate role permissions
        $this->info('3. Validating role permissions...');
        $roles = Role::with('permissions')->get();

        foreach ($roles as $role) {
            if ($role->permissions->isEmpty()) {
                $issues[] = "Role '{$role->name}' has no permissions assigned";
            }
        }

        // 4. Check for orphaned permissions
        $this->info('4. Checking for orphaned permissions...');
        $orphanedPermissions = Permission::whereDoesntHave('roles')->get();
        foreach ($orphanedPermissions as $permission) {
            $issues[] = "Orphaned permission: {$permission->slug} (not assigned to any role)";
        }

        // 5. Validate user permissions
        $this->info('5. Validating user permissions...');
        $users = User::with(['userClinicRoles.role.permissions'])->get();
        foreach ($users as $user) {
            if ($user->userClinicRoles->isEmpty()) {
                $issues[] = "User '{$user->email}' has no role assignments";
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

        return empty($issues) ? Command::SUCCESS : Command::FAILURE;
    }

    private function fixPermissions(): int
    {
        $this->info('🔧 Fixing MediNext EMR permissions...');

        try {
            // Update all role permissions
            $this->updateAllRolePermissions();

            $this->info('✅ Permissions fixed successfully!');
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Failed to fix permissions: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    private function showStatus(): int
    {
        $this->info('📊 MediNext EMR Permissions Status');
        $this->info('==================================');
        $this->newLine();

        // Permissions
        $permissionCount = Permission::count();
        $this->info("🔐 Permissions: {$permissionCount}");
        $this->newLine();

        // Roles
        $roleCount = Role::count();
        $this->info("👥 Roles: {$roleCount}");
        if ($roleCount > 0) {
            $roles = Role::with('permissions')->get();
            foreach ($roles as $role) {
                $permissionCount = $role->permissions->count();
                $this->line("   • {$role->name} - {$permissionCount} permissions - {$role->description}");
            }
        }
        $this->newLine();

        // Users with roles
        $userCount = User::count();
        $this->info("👤 Users: {$userCount}");
        if ($userCount > 0) {
            $users = User::with('userClinicRoles.role')->get();
            foreach ($users as $user) {
                $roles = $user->userClinicRoles->pluck('role.name')->unique()->implode(', ');
                $this->line("   • {$user->name} ({$user->email}) - Roles: {$roles}");
            }
        }
        $this->newLine();

        return Command::SUCCESS;
    }

    private function updateRolePermissions(string $roleName): void
    {
        $role = Role::where('name', $roleName)->first();
        
        if (!$role) {
            throw new \Exception("Role '{$roleName}' not found!");
        }

        $permissions = $this->getRolePermissions($roleName);
        $permissionIds = Permission::whereIn('slug', $permissions)->pluck('id');
        
        if ($permissionIds->isEmpty()) {
            throw new \Exception("No permissions found for role '{$roleName}'!");
        }

        $role->permissions()->sync($permissionIds);
        $this->info("✅ Updated {$roleName} role with {$permissionIds->count()} permissions");
    }

    private function updateAllRolePermissions(): void
    {
        $rolePermissions = $this->getAllRolePermissions();
        $totalUpdated = 0;

        foreach ($rolePermissions as $roleName => $permissions) {
            $role = Role::where('name', $roleName)->first();
            
            if (!$role) {
                $this->error("Role '{$roleName}' not found!");
                continue;
            }

            $permissionIds = Permission::whereIn('slug', $permissions)->pluck('id');
            
            if ($permissionIds->isEmpty()) {
                $this->error("No permissions found for role '{$roleName}'!");
                continue;
            }

            $role->permissions()->sync($permissionIds);
            $this->info("✅ Updated {$roleName} role with {$permissionIds->count()} permissions");
            $totalUpdated++;
        }

        $this->info('');
        $this->info("Updated {$totalUpdated} roles with comprehensive permissions");
    }

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

    private function getAllRolePermissions(): array
    {
        return [
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
    }

    private function getRolePermissions(string $roleName): array
    {
        $allPermissions = $this->getAllRolePermissions();
        return $allPermissions[$roleName] ?? [];
    }

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
