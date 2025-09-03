<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    protected $fillable = [
        'clinic_id',
        'actor_user_id',
        'entity',
        'entity_id',
        'action',
        'at',
        'ip',
        'meta',
        'before_hash',
        'after_hash',
    ];

    protected $casts = [
        'at' => 'datetime',
        'meta' => 'array',
    ];

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }
}
