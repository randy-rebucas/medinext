<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Role;
use App\Models\Clinic;
use App\Models\UserClinicRole;
use App\Models\Doctor;
use App\Models\License;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'is_active',
        'trial_started_at',
        'trial_ends_at',
        'license_key',
        'is_trial_user',
        'has_activated_license',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'phone',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'phone' => 'string',
            'is_active' => 'boolean',
            'trial_started_at' => 'datetime',
            'trial_ends_at' => 'datetime',
            'is_trial_user' => 'boolean',
            'has_activated_license' => 'boolean',
        ];
    }

    /**
     * Get the roles associated with the user.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_clinic_roles')
            ->withPivot('clinic_id')
            ->withTimestamps();
    }

    /**
     * Get the user clinic roles.
     */
    public function userClinicRoles(): HasMany
    {
        return $this->hasMany(UserClinicRole::class);
    }

    /**
     * Get the clinics associated with the user.
     */
    public function clinics(): BelongsToMany
    {
        return $this->belongsToMany(Clinic::class, 'user_clinic_roles')
            ->withPivot('role_id')
            ->withTimestamps();
    }

    /**
     * Check if user has a specific role
     */
    public function hasRole(string $roleName): bool
    {
        return $this->roles()->where('name', $roleName)->exists();
    }

    /**
     * Check if user has a specific role in a clinic
     */
    public function hasRoleInClinic(string $roleName, int $clinicId): bool
    {
        return $this->userClinicRoles()
            ->where('clinic_id', $clinicId)
            ->whereHas('role', function ($query) use ($roleName) {
                $query->where('name', $roleName);
            })
            ->exists();
    }

    /**
     * Check if user has a specific permission in a clinic
     */
    public function hasPermissionInClinic(string $permission, int $clinicId): bool
    {
        return $this->userClinicRoles()
            ->where('clinic_id', $clinicId)
            ->whereHas('role.permissions', function ($query) use ($permission) {
                $query->where('slug', $permission)
                    ->orWhereRaw("CONCAT(module, '.', action) = ?", [$permission]);
            })
            ->exists();
    }

    /**
     * Check if user has a specific permission (checks in current clinic or any clinic)
     */
    public function hasPermission(string $permission): bool
    {
        // First try to get current clinic from session or user's first clinic
        $currentClinic = $this->getCurrentClinic();

        if ($currentClinic) {
            // Check permission in current clinic
            return $this->hasPermissionInClinic($permission, $currentClinic->id);
        }

        // If no current clinic, check if user has permission in any clinic
        return $this->userClinicRoles()
            ->whereHas('role.permissions', function ($query) use ($permission) {
                $query->where('slug', $permission)
                    ->orWhereRaw("CONCAT(module, '.', action) = ?", [$permission]);
            })
            ->exists();
    }

    /**
     * Check if user has any of the given permissions
     */
    public function hasAnyPermission(array $permissions): bool
    {
        $currentClinic = $this->getCurrentClinic();

        if ($currentClinic) {
            return $this->userClinicRoles()
                ->where('clinic_id', $currentClinic->id)
                ->whereHas('role.permissions', function ($query) use ($permissions) {
                    foreach ($permissions as $permission) {
                        $query->orWhere('slug', $permission)
                              ->orWhereRaw("CONCAT(module, '.', action) = ?", [$permission]);
                    }
                })->exists();
        }

        return $this->userClinicRoles()
            ->whereHas('role.permissions', function ($query) use ($permissions) {
                foreach ($permissions as $permission) {
                    $query->orWhere('slug', $permission)
                          ->orWhereRaw("CONCAT(module, '.', action) = ?", [$permission]);
                }
            })->exists();
    }

    /**
     * Check if user has all of the given permissions
     */
    public function hasAllPermissions(array $permissions): bool
    {
        $currentClinic = $this->getCurrentClinic();

        if ($currentClinic) {
            $permissionCount = $this->userClinicRoles()
                ->where('clinic_id', $currentClinic->id)
                ->whereHas('role.permissions', function ($query) use ($permissions) {
                    foreach ($permissions as $permission) {
                        $query->orWhere('slug', $permission)
                              ->orWhereRaw("CONCAT(module, '.', action) = ?", [$permission]);
                    }
                })->count();
        } else {
            $permissionCount = $this->userClinicRoles()
                ->whereHas('role.permissions', function ($query) use ($permissions) {
                    foreach ($permissions as $permission) {
                        $query->orWhere('slug', $permission)
                              ->orWhereRaw("CONCAT(module, '.', action) = ?", [$permission]);
                    }
                })->count();
        }

        return $permissionCount >= count($permissions);
    }

    /**
     * Get all permissions for the user
     */
    public function getAllPermissions(): array
    {
        $currentClinic = $this->getCurrentClinic();

        if ($currentClinic) {
            return $this->userClinicRoles()
                ->where('clinic_id', $currentClinic->id)
                ->with('role.permissions')
                ->get()
                ->pluck('role.permissions')
                ->flatten()
                ->unique('id')
                ->values()
                ->toArray();
        }

        return $this->userClinicRoles()
            ->with('role.permissions')
            ->get()
            ->pluck('role.permissions')
            ->flatten()
            ->unique('id')
            ->values()
            ->toArray();
    }

    /**
     * Get permissions for user in specific clinic
     */
    public function getPermissionsInClinic(int $clinicId): array
    {
        return $this->userClinicRoles()
            ->where('clinic_id', $clinicId)
            ->with('role.permissions')
            ->get()
            ->pluck('role.permissions')
            ->flatten()
            ->unique('id')
            ->values()
            ->toArray();
    }

    /**
     * Check if user is a doctor in a specific clinic
     */
    public function isDoctorInClinic(int $clinicId): bool
    {
        return $this->doctors()->where('clinic_id', $clinicId)->exists();
    }

    /**
     * Check if user is admin in clinic
     */
    public function isAdminInClinic(int $clinicId): bool
    {
        return $this->hasRoleInClinic('admin', $clinicId) || $this->hasRoleInClinic('superadmin', $clinicId);
    }

    /**
     * Check if user is receptionist in clinic
     */
    public function isReceptionistInClinic(int $clinicId): bool
    {
        return $this->hasRoleInClinic('receptionist', $clinicId);
    }

    /**
     * Check if user is patient in clinic
     */
    public function isPatientInClinic(int $clinicId): bool
    {
        return $this->hasRoleInClinic('patient', $clinicId);
    }

    /**
     * Check if user is medical representative in clinic
     */
    public function isMedrepInClinic(int $clinicId): bool
    {
        return $this->hasRoleInClinic('medrep', $clinicId);
    }

    /**
     * Check if user has valid access (trial or license)
     */
    public function hasValidAccess(): bool
    {
        // Check if user has activated license
        if ($this->has_activated_license) {
            return true;
        }

        // Check if user is in trial period
        if ($this->is_trial_user && $this->trial_ends_at && $this->trial_ends_at->isFuture()) {
            return true;
        }

        return false;
    }

    /**
     * Get the current clinic context for the user
     */
    public function getCurrentClinic(): ?Clinic
    {
        // This should be implemented based on your session/request logic
        return session('current_clinic_id') ? Clinic::find(session('current_clinic_id')) : null;
    }

    /**
     * Get the doctors associated with this user
     */
    public function doctors(): HasMany
    {
        return $this->hasMany(Doctor::class);
    }

    /**
     * Get the notifications for this user
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Get the bills created by this user
     */
    public function createdBills(): HasMany
    {
        return $this->hasMany(Bill::class, 'created_by');
    }

    /**
     * Get the bills updated by this user
     */
    public function updatedBills(): HasMany
    {
        return $this->hasMany(Bill::class, 'updated_by');
    }

    /**
     * Get the bill items created by this user
     */
    public function createdBillItems(): HasMany
    {
        return $this->hasMany(BillItem::class, 'created_by');
    }

    /**
     * Get the bill items updated by this user
     */
    public function updatedBillItems(): HasMany
    {
        return $this->hasMany(BillItem::class, 'updated_by');
    }

    /**
     * Get the insurance records created by this user
     */
    public function createdInsurance(): HasMany
    {
        return $this->hasMany(Insurance::class, 'created_by');
    }

    /**
     * Get the insurance records updated by this user
     */
    public function updatedInsurance(): HasMany
    {
        return $this->hasMany(Insurance::class, 'updated_by');
    }

    /**
     * Get the queues created by this user
     */
    public function createdQueues(): HasMany
    {
        return $this->hasMany(Queue::class, 'created_by');
    }

    /**
     * Get the queues updated by this user
     */
    public function updatedQueues(): HasMany
    {
        return $this->hasMany(Queue::class, 'updated_by');
    }

    /**
     * Get the queue patients created by this user
     */
    public function createdQueuePatients(): HasMany
    {
        return $this->hasMany(QueuePatient::class, 'created_by');
    }

    /**
     * Get the notifications created by this user
     */
    public function createdNotifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'created_by');
    }

    /**
     * Get the displayable singular label of the model.
     * This method is required for Nova MorphTo relationships.
     *
     * @return string
     */
    public static function singularLabel(): string
    {
        return 'User';
    }

    /**
     * Get the URI key for the model.
     * This method is required for Nova MorphTo relationships.
     *
     * @return string
     */
    public static function uriKey(): string
    {
        return 'users';
    }

    /**
     * Start a 14-day free trial for the user
     */
    public function startTrial(): void
    {
        $this->update([
            'trial_started_at' => now(),
            'trial_ends_at' => now()->addDays(14),
            'is_trial_user' => true,
            'has_activated_license' => false,
        ]);
    }

    /**
     * Check if the user is currently on trial
     */
    public function isOnTrial(): bool
    {
        return $this->is_trial_user &&
               $this->trial_ends_at &&
               $this->trial_ends_at->isFuture() &&
               !$this->has_activated_license;
    }

    /**
     * Check if the user's trial has expired
     */
    public function isTrialExpired(): bool
    {
        return $this->is_trial_user &&
               $this->trial_ends_at &&
               $this->trial_ends_at->isPast() &&
               !$this->has_activated_license;
    }

    /**
     * Get the number of days remaining in the trial
     */
    public function getTrialDaysRemaining(): int
    {
        if (!$this->isOnTrial()) {
            return 0;
        }

        return max(0, round(now()->diffInDays($this->trial_ends_at, false)));
    }

    /**
     * Activate a license for the user
     */
    public function activateLicense(string $licenseKey): bool
    {
        $this->update([
            'license_key' => $licenseKey,
            'has_activated_license' => true,
            'is_trial_user' => false,
        ]);

        return true;
    }


    /**
     * Get the user's license relationship
     */
    public function license()
    {
        return $this->belongsTo(License::class, 'license_key', 'license_key');
    }

    /**
     * Get the user's access status
     */
    public function getAccessStatus(): array
    {
        if ($this->has_activated_license) {
            return [
                'type' => 'licensed',
                'status' => 'active',
                'message' => 'Full access with license',
                'expires_at' => $this->license?->expires_at,
            ];
        }

        if ($this->isOnTrial()) {
            return [
                'type' => 'trial',
                'status' => 'active',
                'message' => 'Free trial active',
                'expires_at' => $this->trial_ends_at,
                'days_remaining' => $this->getTrialDaysRemaining(),
            ];
        }

        if ($this->isTrialExpired()) {
            $daysExpired = $this->trial_ends_at ? now()->diffInDays($this->trial_ends_at, false) : 0;
            return [
                'type' => 'trial',
                'status' => 'expired',
                'message' => 'Free trial expired',
                'expires_at' => $this->trial_ends_at,
                'days_expired' => abs(round($daysExpired)),
            ];
        }

        return [
            'type' => 'none',
            'status' => 'inactive',
            'message' => 'No access',
        ];
    }
}
