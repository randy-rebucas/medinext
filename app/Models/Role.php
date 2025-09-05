<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;

class Role extends Model
{
    protected $fillable = [
        'name',
        'description',
        'is_system_role',
        'permissions_config',
    ];

    protected $casts = [
        'is_system_role' => 'boolean',
        'permissions_config' => 'array',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        // Add any global scopes if needed
    }

    /**
     * Scope to filter by system roles
     */
    public function scopeSystemRoles(Builder $query): Builder
    {
        return $query->where('is_system_role', true);
    }

    /**
     * Scope to filter by non-system roles
     */
    public function scopeNonSystemRoles(Builder $query): Builder
    {
        return $query->where('is_system_role', false);
    }

    /**
     * Scope to filter by role name
     */
    public function scopeByName(Builder $query, string $name): Builder
    {
        return $query->where('name', $name);
    }

    public function userClinicRoles(): HasMany
    {
        return $this->hasMany(UserClinicRole::class);
    }

    /**
     * Get the permissions for this role
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permissions');
    }

    /**
     * Check if role has a specific permission
     */
    public function hasPermission(string $permission): bool
    {
        return $this->permissions()->where(function ($query) use ($permission) {
            $query->where('slug', $permission)
                  ->orWhere('name', $permission)
                  ->orWhereRaw("module || '.' || action = ?", [$permission]);
        })->exists();
    }

    /**
     * Check if role has any of the given permissions
     */
    public function hasAnyPermission(array $permissions): bool
    {
        return $this->permissions()->where(function ($query) use ($permissions) {
            foreach ($permissions as $permission) {
                $query->orWhere('slug', $permission)
                      ->orWhere('name', $permission)
                      ->orWhereRaw("module || '.' || action = ?", [$permission]);
            }
        })->exists();
    }

    /**
     * Check if role has all of the given permissions
     */
    public function hasAllPermissions(array $permissions): bool
    {
        $permissionCount = $this->permissions()->where(function ($query) use ($permissions) {
            foreach ($permissions as $permission) {
                $query->orWhere('slug', $permission)
                      ->orWhere('name', $permission)
                      ->orWhereRaw("module || '.' || action = ?", [$permission]);
            }
        })->count();

        return $permissionCount === count($permissions);
    }

    /**
     * Get role display name
     */
    public function getDisplayNameAttribute(): string
    {
        return ucfirst(str_replace('_', ' ', $this->name));
    }

    /**
     * Check if this is a system role (cannot be deleted/modified)
     */
    public function isSystemRole(): bool
    {
        return $this->is_system_role;
    }

    /**
     * Get default permissions for this role
     */
    public function getDefaultPermissions(): array
    {
        $defaultPermissions = [
            'superadmin' => [
                // Platform management
                'tenants.manage', 'plans.manage', 'global.settings',
                // Full access to all modules
                'patient.manage', 'emr.manage', 'schedule.manage', 'rx.issue', 'rx.view', 'rx.edit', 'rx.download',
                'billing.manage', 'settings.manage', 'staff.manage', 'clinical_notes.read', 'clinical_notes.write',
                // Legacy permissions for backward compatibility
                'clinics.manage', 'doctors.manage', 'patients.manage', 'appointments.manage',
                'prescriptions.manage', 'users.manage', 'roles.manage', 'billing.manage',
                'reports.view', 'reports.export', 'settings.manage'
            ],
            'admin' => [
                // Full clinic management
                'patient.manage', 'emr.manage', 'schedule.manage', 'rx.issue', 'rx.view', 'rx.edit', 'rx.download',
                'billing.manage', 'settings.manage', 'staff.manage', 'clinical_notes.read', 'clinical_notes.write',
                // Legacy permissions
                'clinics.manage', 'doctors.manage', 'patients.manage', 'appointments.manage',
                'prescriptions.manage', 'users.manage', 'billing.manage', 'reports.view',
                'reports.export'
            ],
            'doctor' => [
                // Core doctor permissions
                'patient.read', 'patient.write', 'emr.read', 'emr.write', 'schedule.view', 'schedule.manage',
                'rx.issue', 'rx.view', 'rx.edit', 'rx.download', 'clinical_notes.read', 'clinical_notes.write',
                // Legacy permissions
                'clinics.view', 'doctors.view', 'patients.view', 'patients.edit',
                'appointments.view', 'appointments.create', 'appointments.edit', 'appointments.cancel',
                'prescriptions.view', 'prescriptions.create', 'prescriptions.edit', 'prescriptions.delete',
                'medical_records.view', 'medical_records.create', 'medical_records.edit',
                'schedule.view', 'schedule.manage', 'reports.view'
            ],
            'receptionist' => [
                // Receptionist permissions (no clinical notes access)
                'patient.read', 'patient.write', 'schedule.view', 'schedule.manage', 'billing.view', 'billing.create', 'billing.edit',
                // Legacy permissions
                'clinics.view', 'doctors.view', 'patients.view', 'patients.create', 'patients.edit',
                'appointments.view', 'appointments.create', 'appointments.edit', 'appointments.cancel',
                'appointments.checkin', 'billing.view', 'billing.create', 'billing.edit',
                'schedule.view', 'reports.view'
            ],
            'patient' => [
                // Patient self-service permissions
                'patient.read', 'schedule.view', 'rx.view', 'rx.download', 'profile.edit',
                // Legacy permissions
                'clinics.view', 'doctors.view', 'appointments.view', 'appointments.create',
                'appointments.cancel', 'prescriptions.view', 'prescriptions.download',
                'medical_records.view', 'profile.edit'
            ],
            'medrep' => [
                // MedRep specific permissions (no patient data access)
                'medrep.schedule', 'medrep.upload', 'medrep.view', 'schedule.view', 'schedule.manage',
                // Legacy permissions
                'clinics.view', 'doctors.view', 'products.view', 'products.create', 'products.edit',
                'meetings.view', 'meetings.create', 'meetings.edit', 'meetings.delete',
                'interactions.view', 'interactions.create', 'interactions.edit',
                'schedule.view', 'reports.view'
            ],
        ];

        return $defaultPermissions[$this->name] ?? [];
    }

    /**
     * Get role capabilities description
     */
    public function getCapabilitiesDescriptionAttribute(): string
    {
        $capabilities = [
            'superadmin' => 'Platform administrator with full system access. Manages tenants, clinics, plans, and global settings.',
            'admin' => 'Clinic owner/manager with full access within clinic. Can manage staff, billing, and settings.',
            'doctor' => 'Medical professional who can view own schedule, manage assigned patients\' EMR, issue prescriptions, and view med samples.',
            'receptionist' => 'Front desk staff who can manage calendar, patients, visits, and billing support. No access to clinical notes by default.',
            'patient' => 'Self-service portal access. Can book, reschedule, cancel appointments, view summary, and download prescriptions.',
            'medrep' => 'Medical representative who can schedule visits with doctors and upload product sheets. No access to patient data.',
        ];

        return $capabilities[$this->name] ?? 'Custom role with specific permissions';
    }

    /**
     * Get role security level
     */
    public function getSecurityLevelAttribute(): string
    {
        $levels = [
            'superadmin' => 'Critical',
            'admin' => 'High',
            'doctor' => 'Medium-High',
            'receptionist' => 'Medium',
            'medrep' => 'Medium',
            'patient' => 'Low',
        ];

        return $levels[$this->name] ?? 'Custom';
    }

    /**
     * Check if role can be modified
     */
    public function canBeModified(): bool
    {
        return !$this->is_system_role;
    }

    /**
     * Check if role can be deleted
     */
    public function canBeDeleted(): bool
    {
        return !$this->is_system_role && !$this->userClinicRoles()->exists();
    }

    /**
     * Get role usage statistics
     */
    public function getUsageStatisticsAttribute(): array
    {
        return [
            'total_users' => $this->userClinicRoles()->count(),
            'total_clinics' => $this->userClinicRoles()->distinct('clinic_id')->count(),
            'is_system_role' => $this->is_system_role,
            'permission_count' => $this->permissions()->count(),
        ];
    }

    /**
     * Validate role permissions
     */
    public function validatePermissions(): array
    {
        $errors = [];
        $permissions = $this->permissions;

        // Check for conflicting permissions
        $conflicts = [
            'patients.delete' => ['patients.view'],
            'appointments.delete' => ['appointments.view'],
            'prescriptions.delete' => ['prescriptions.view'],
        ];

        foreach ($conflicts as $permission => $required) {
            if ($this->hasPermission($permission)) {
                foreach ($required as $req) {
                    if (!$this->hasPermission($req)) {
                        $errors[] = "Permission '{$permission}' requires '{$req}'";
                    }
                }
            }
        }

        return $errors;
    }

    /**
     * Get recommended permissions for this role
     */
    public function getRecommendedPermissionsAttribute(): array
    {
        $recommendations = [
            'doctor' => [
                'schedule.manage' => 'Manage personal schedule',
                'reports.export' => 'Export patient reports',
                'billing.view' => 'View billing information',
            ],
            'receptionist' => [
                'patients.delete' => 'Remove patient records',
                'reports.export' => 'Export appointment reports',
                'settings.view' => 'View clinic settings',
            ],
            'medrep' => [
                'reports.export' => 'Export interaction reports',
                'schedule.manage' => 'Manage meeting schedule',
                'products.delete' => 'Remove product records',
            ],
        ];

        return $recommendations[$this->name] ?? [];
    }

    /**
     * Check if role has minimum required permissions
     */
    public function hasMinimumPermissions(): bool
    {
        $minimumPermissions = [
            'superadmin' => ['tenants.manage', 'global.settings'],
            'admin' => ['patient.manage', 'billing.manage'],
            'doctor' => ['patient.read', 'emr.read', 'rx.issue'],
            'patient' => ['patient.read', 'schedule.view'],
            'receptionist' => ['patient.read', 'schedule.view'],
            'medrep' => ['medrep.schedule', 'schedule.view'],
        ];

        $required = $minimumPermissions[$this->name] ?? [];
        return $this->hasAllPermissions($required);
    }
}
