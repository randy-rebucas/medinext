<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedrepVisit extends Model
{
    protected $fillable = [
        'clinic_id',
        'medrep_id',
        'doctor_id',
        'start_at',
        'end_at',
        'purpose',
        'notes',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
    ];

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function medrep(): BelongsTo
    {
        return $this->belongsTo(Medrep::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }
}
