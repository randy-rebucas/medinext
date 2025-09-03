<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
}
