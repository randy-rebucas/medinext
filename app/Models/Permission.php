<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;

class Permission extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'module',
        'action',
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
     * Scope to filter by module
     */
    public function scopeByModule(Builder $query, string $module): Builder
    {
        return $query->where('module', $module);
    }

    /**
     * Scope to filter by action
     */
    public function scopeByAction(Builder $query, string $action): Builder
    {
        return $query->where('action', $action);
    }

    /**
     * Scope to filter by permission type
     */
    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('action', $type);
    }

    /**
     * Get the roles that have this permission
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permissions');
    }

    /**
     * Get the full permission identifier
     */
    public function getFullPermissionAttribute(): string
    {
        return "{$this->module}.{$this->action}";
    }

    /**
     * Check if permission matches a pattern
     */
    public function matches(string $permission): bool
    {
        return $this->full_permission === $permission ||
               $this->slug === $permission ||
               $this->name === $permission;
    }

    /**
     * Get permission category
     */
    public function getCategoryAttribute(): string
    {
        $categories = [
            'clinics' => 'Clinic Management',
            'doctors' => 'Doctor Management',
            'patients' => 'Patient Management',
            'appointments' => 'Appointment Management',
            'prescriptions' => 'Prescription Management',
            'medical_records' => 'Medical Records',
            'users' => 'User Management',
            'roles' => 'Role Management',
            'billing' => 'Billing Management',
            'reports' => 'Reporting',
            'settings' => 'System Settings',
            'schedule' => 'Scheduling',
            'products' => 'Product Management',
            'meetings' => 'Meeting Management',
            'interactions' => 'Interaction Tracking',
            'profile' => 'Profile Management',
        ];

        return $categories[$this->module] ?? 'Other';
    }

    /**
     * Get permission risk level
     */
    public function getRiskLevelAttribute(): string
    {
        $riskLevels = [
            'delete' => 'High',
            'manage' => 'High',
            'create' => 'Medium',
            'edit' => 'Medium',
            'export' => 'Medium',
            'view' => 'Low',
            'download' => 'Low',
            'checkin' => 'Low',
            'cancel' => 'Low',
        ];

        return $riskLevels[$this->action] ?? 'Medium';
    }

    /**
     * Get permission description with context
     */
    public function getContextualDescriptionAttribute(): string
    {
        $contexts = [
            'clinics.manage' => 'Full control over clinic operations including creation, modification, and deletion',
            'clinics.view' => 'View clinic information and basic details',
            'doctors.manage' => 'Full control over doctor management including hiring and termination',
            'doctors.view' => 'View doctor profiles and availability',
            'patients.manage' => 'Full control over patient records and information',
            'patients.view' => 'View patient information and medical history',
            'patients.create' => 'Create new patient records',
            'patients.edit' => 'Modify existing patient information',
            'patients.delete' => 'Remove patient records (use with caution)',
            'appointments.manage' => 'Full control over appointment scheduling and management',
            'appointments.view' => 'View appointment schedules and details',
            'appointments.create' => 'Schedule new appointments',
            'appointments.edit' => 'Modify existing appointments',
            'appointments.cancel' => 'Cancel scheduled appointments',
            'appointments.checkin' => 'Check-in patients for appointments',
            'prescriptions.manage' => 'Full control over prescription management',
            'prescriptions.view' => 'View prescription information',
            'prescriptions.create' => 'Create new prescriptions',
            'prescriptions.edit' => 'Modify existing prescriptions',
            'prescriptions.download' => 'Download prescription documents',
            'medical_records.view' => 'View patient medical records',
            'medical_records.create' => 'Create new medical records',
            'medical_records.edit' => 'Modify existing medical records',
            'users.manage' => 'Full control over user accounts and access',
            'users.view' => 'View user information and profiles',
            'users.create' => 'Create new user accounts',
            'users.edit' => 'Modify user information and settings',
            'users.delete' => 'Remove user accounts (use with caution)',
            'roles.manage' => 'Full control over role definitions and permissions',
            'roles.view' => 'View role information and permissions',
            'roles.create' => 'Create new roles',
            'roles.edit' => 'Modify role permissions and settings',
            'roles.delete' => 'Remove roles (use with caution)',
            'billing.manage' => 'Full control over billing operations',
            'billing.view' => 'View billing information and invoices',
            'billing.create' => 'Create new billing records',
            'billing.edit' => 'Modify billing information',
            'reports.view' => 'View system reports and analytics',
            'reports.export' => 'Export reports to various formats',
            'settings.manage' => 'Manage system-wide settings and configuration',
            'schedule.view' => 'View appointment schedules and availability',
            'schedule.manage' => 'Manage scheduling and availability',
            'products.view' => 'View product information and details',
            'products.create' => 'Add new products to the system',
            'products.edit' => 'Modify product information',
            'meetings.view' => 'View meeting schedules and details',
            'meetings.create' => 'Schedule new meetings',
            'meetings.edit' => 'Modify meeting details',
            'meetings.delete' => 'Remove meeting records',
            'interactions.view' => 'View interaction records and history',
            'interactions.create' => 'Record new interactions',
            'interactions.edit' => 'Modify interaction records',
            'profile.edit' => 'Edit own profile information',
        ];

        return $contexts[$this->full_permission] ?? $this->description;
    }

    /**
     * Check if permission is critical
     */
    public function isCritical(): bool
    {
        return $this->risk_level === 'High';
    }

    /**
     * Check if permission is safe
     */
    public function isSafe(): bool
    {
        return $this->risk_level === 'Low';
    }

    /**
     * Get permission usage statistics
     */
    public function getUsageStatisticsAttribute(): array
    {
        return [
            'total_roles' => $this->roles()->count(),
            'total_users' => $this->roles()->withCount('userClinicRoles')->get()->sum('user_clinic_roles_count'),
            'risk_level' => $this->risk_level,
            'category' => $this->category,
        ];
    }

    /**
     * Get related permissions
     */
    public function getRelatedPermissionsAttribute()
    {
        return static::where('module', $this->module)
            ->where('id', '!=', $this->id)
            ->get();
    }

    /**
     * Check if permission conflicts with another
     */
    public function conflictsWith(Permission $other): bool
    {
        $conflicts = [
            'delete' => ['view'],
            'manage' => ['view', 'create', 'edit'],
            'edit' => ['view'],
        ];

        if ($this->module === $other->module) {
            $action1 = $this->action;
            $action2 = $other->action;

            foreach ($conflicts as $conflicting => $required) {
                if (($action1 === $conflicting && in_array($action2, $required)) ||
                    ($action2 === $conflicting && in_array($action1, $required))) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Get permission dependencies
     */
    public function getDependenciesAttribute(): array
    {
        $dependencies = [
            'delete' => ['view'],
            'edit' => ['view'],
            'manage' => ['view', 'create', 'edit'],
        ];

        return $dependencies[$this->action] ?? [];
    }

    /**
     * Check if permission has required dependencies
     */
    public function hasDependencies(): bool
    {
        return !empty($this->dependencies);
    }
}
