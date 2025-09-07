<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserClinicRole extends Model
{
    protected $fillable = [
        'user_id',
        'clinic_id',
        'role_id',
        'department',
        'status',
        'address',
        'emergency_contact',
        'emergency_phone',
        'notes',
        'join_date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }
}
