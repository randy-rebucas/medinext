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
     *
     * @param string $permission The permission to check (e.g., 'patient.read')
     * @param int $clinicId The clinic ID to check permission in
     * @return bool True if user has the permission in the clinic
     */
    public function hasPermissionInClinic(string $permission, int $clinicId): bool
    {
        return $this->userClinicRoles()
            ->where('clinic_id', $clinicId)
            ->whereHas('role.permissions', function ($query) use ($permission) {
                $query->where('slug', $permission)
                    ->orWhere(function ($subQuery) use ($permission) {
                        $subQuery->whereRaw("module || '.' || action = ?", [$permission]);
                    });
            })
            ->exists();
    }

    /**
     * Check if user has any of the given permissions in a clinic
     */
    public function hasAnyPermissionInClinic(array $permissions, int $clinicId): bool
    {
        return $this->userClinicRoles()
            ->where('clinic_id', $clinicId)
            ->whereHas('role.permissions', function ($query) use ($permissions) {
                $query->where(function ($subQuery) use ($permissions) {
                    foreach ($permissions as $permission) {
                        $subQuery->orWhere('slug', $permission)
                            ->orWhere(function ($permissionQuery) use ($permission) {
                                $permissionQuery->whereRaw("module || '.' || action = ?", [$permission]);
                            });
                    }
                });
            })
            ->exists();
    }

    /**
     * Check if user has all of the given permissions in a clinic
     */
    public function hasAllPermissionsInClinic(array $permissions, int $clinicId): bool
    {
        foreach ($permissions as $permission) {
            if (!$this->hasPermissionInClinic($permission, $clinicId)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Get all permissions for user in a specific clinic
     */
    public function getPermissionsInClinic(int $clinicId): array
    {
        return $this->userClinicRoles()
            ->where('clinic_id', $clinicId)
            ->with('role.permissions')
            ->get()
            ->pluck('role.permissions')
            ->flatten()
            ->pluck('slug')
            ->unique()
            ->toArray();
    }

    /**
     * Check if user is a super admin
     *
     * @return bool True if user has the superadmin role
     */
    public function isSuperAdmin(): bool
    {
        return $this->userClinicRoles()
            ->whereHas('role', function ($query) {
                $query->where('name', 'superadmin');
            })
            ->exists();
    }

    /**
     * Check if user is an admin in any clinic
     */
    public function isAdmin(): bool
    {
        return $this->userClinicRoles()
            ->whereHas('role', function ($query) {
                $query->where('name', 'admin');
            })
            ->exists();
    }

    /**
     * Check if user is a doctor in any clinic
     */
    public function isDoctor(): bool
    {
        return $this->userClinicRoles()
            ->whereHas('role', function ($query) {
                $query->where('name', 'doctor');
            })
            ->exists();
    }

    /**
     * Check if user is a patient
     */
    public function isPatient(): bool
    {
        return $this->userClinicRoles()
            ->whereHas('role', function ($query) {
                $query->where('name', 'patient');
            })
            ->exists();
    }

    /**
     * Check if user is a receptionist
     */
    public function isReceptionist(): bool
    {
        return $this->userClinicRoles()
            ->whereHas('role', function ($query) {
                $query->where('name', 'receptionist');
            })
            ->exists();
    }

    /**
     * Check if user is a medical representative
     */
    public function isMedRep(): bool
    {
        return $this->userClinicRoles()
            ->whereHas('role', function ($query) {
                $query->where('name', 'medrep');
            })
            ->exists();
    }

    /**
     * Get user's primary role (highest privilege)
     */
    public function getPrimaryRole(): ?string
    {
        $roleHierarchy = ['superadmin', 'admin', 'doctor', 'receptionist', 'medrep', 'patient'];

        foreach ($roleHierarchy as $roleName) {
            if ($this->userClinicRoles()
                ->whereHas('role', function ($query) use ($roleName) {
                    $query->where('name', $roleName);
                })
                ->exists()) {
                return $roleName;
            }
        }

        return null;
    }

    /**
     * Check if user can access clinical notes
     */
    public function canAccessClinicalNotes(int $clinicId): bool
    {
        return $this->hasAnyPermissionInClinic([
            'clinical_notes.read',
            'clinical_notes.write',
            'emr.read',
            'emr.write',
            'medical_records.view',
            'medical_records.create',
            'medical_records.edit'
        ], $clinicId);
    }

    /**
     * Check if user can access patient data
     */
    public function canAccessPatientData(int $clinicId): bool
    {
        return $this->hasAnyPermissionInClinic([
            'patient.read',
            'patient.write',
            'patient.manage',
            'patients.view',
            'patients.create',
            'patients.edit',
            'patients.manage'
        ], $clinicId);
    }

    /**
     * Check if user is a doctor in a specific clinic
     */
    public function isDoctorInClinic(int $clinicId): bool
    {
        return $this->doctors()->where('clinic_id', $clinicId)->exists();
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
}
