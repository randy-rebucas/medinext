<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Role;
use App\Models\Clinic;
use App\Models\UserClinicRole;
use App\Models\Doctor;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

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
