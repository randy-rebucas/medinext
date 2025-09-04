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
                  ->orWhereRaw("CONCAT(module, '.', action) = ?", [$permission]);
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
                      ->orWhereRaw("CONCAT(module, '.', action) = ?", [$permission]);
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
                      ->orWhereRaw("CONCAT(module, '.', action) = ?", [$permission]);
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
            'admin' => [
                'clinics.manage',
                'clinics.view',
                'clinics.create',
                'clinics.edit',
                'clinics.delete',
                'doctors.manage',
                'doctors.view',
                'doctors.create',
                'doctors.edit',
                'doctors.delete',
                'patients.manage',
                'patients.view',
                'patients.create',
                'patients.edit',
                'patients.delete',
                'appointments.manage',
                'appointments.view',
                'appointments.create',
                'appointments.edit',
                'appointments.delete',
                'prescriptions.manage',
                'prescriptions.view',
                'prescriptions.create',
                'prescriptions.edit',
                'prescriptions.delete',
                'users.manage',
                'users.view',
                'users.create',
                'users.edit',
                'users.delete',
                'roles.manage',
                'roles.view',
                'roles.create',
                'roles.edit',
                'roles.delete',
                'billing.manage',
                'billing.view',
                'billing.create',
                'billing.edit',
                'billing.delete',
                'reports.view',
                'reports.export',
                'settings.manage',
            ],
            'doctor' => [
                'clinics.view',
                'doctors.view',
                'patients.view',
                'patients.edit',
                'appointments.view',
                'appointments.create',
                'appointments.edit',
                'appointments.cancel',
                'prescriptions.view',
                'prescriptions.create',
                'prescriptions.edit',
                'prescriptions.delete',
                'medical_records.view',
                'medical_records.create',
                'medical_records.edit',
                'schedule.view',
                'schedule.manage',
                'reports.view',
            ],
            'patient' => [
                'clinics.view',
                'doctors.view',
                'appointments.view',
                'appointments.create',
                'appointments.cancel',
                'prescriptions.view',
                'prescriptions.download',
                'medical_records.view',
                'profile.edit',
            ],
            'receptionist' => [
                'clinics.view',
                'doctors.view',
                'patients.view',
                'patients.create',
                'patients.edit',
                'appointments.view',
                'appointments.create',
                'appointments.edit',
                'appointments.cancel',
                'appointments.checkin',
                'billing.view',
                'billing.create',
                'billing.edit',
                'schedule.view',
                'reports.view',
            ],
            'medrep' => [
                'clinics.view',
                'doctors.view',
                'products.view',
                'products.create',
                'products.edit',
                'meetings.view',
                'meetings.create',
                'meetings.edit',
                'meetings.delete',
                'interactions.view',
                'interactions.create',
                'interactions.edit',
                'schedule.view',
                'reports.view',
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
            'admin' => 'Full system access and management - can manage all clinics, users, and system settings',
            'doctor' => 'Manage appointments, medical records, prescriptions - full clinical workflow access',
            'patient' => 'Book appointments, view records, download prescriptions - patient self-service access',
            'receptionist' => 'Schedule appointments, manage patient check-ins, handle billing support - front desk operations',
            'medrep' => 'Manage product details, schedule doctor meetings, track interactions - medical representative operations',
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
            'admin' => ['clinics.view', 'users.view'],
            'doctor' => ['patients.view', 'appointments.view'],
            'patient' => ['appointments.view', 'profile.edit'],
            'receptionist' => ['patients.view', 'appointments.view'],
            'medrep' => ['doctors.view', 'schedule.view'],
        ];

        $required = $minimumPermissions[$this->name] ?? [];
        return $this->hasAllPermissions($required);
    }
}
