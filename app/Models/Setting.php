<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Setting extends Model
{
    protected $fillable = [
        'clinic_id',
        'key',
        'value',
        'type',
        'group',
        'description',
        'is_public',
    ];

    protected $casts = [
        'value' => 'json',
        'is_public' => 'boolean',
    ];

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public static function getValue(string $key, $default = null, ?int $clinicId = null)
    {
        $setting = static::where('key', $key)
            ->when($clinicId, fn($query) => $query->where('clinic_id', $clinicId))
            ->first();

        return $setting ? $setting->value : $default;
    }

    public static function setValue(string $key, $value, ?int $clinicId = null, string $type = 'string', string $group = 'general', string $description = '', bool $isPublic = false)
    {
        return static::updateOrCreate(
            [
                'key' => $key,
                'clinic_id' => $clinicId,
            ],
            [
                'value' => $value,
                'type' => $type,
                'group' => $group,
                'description' => $description,
                'is_public' => $isPublic,
            ]
        );
    }
}
