<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
}
