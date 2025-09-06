<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class FileAsset extends Model
{
    protected $fillable = [
        'clinic_id',
        'owner_type',
        'owner_id',
        'url',
        'mime',
        'size',
        'checksum',
        'category',
        'description',
        'file_name',
        'original_name',
    ];

    protected $casts = [
        'size' => 'integer',
    ];

    /**
     * Get the owning model
     */
    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the clinic this file belongs to
     */
    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    /**
     * Get the file extension
     */
    public function getExtensionAttribute(): string
    {
        return $this->file_name ? pathinfo($this->file_name, PATHINFO_EXTENSION) : '';
    }

    /**
     * Get the file size in human readable format
     */
    public function getHumanSizeAttribute(): string
    {
        if (!$this->size) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->size;
        $unit = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return round($size, 2) . ' ' . $units[$unit];
    }

    /**
     * Check if file is an image
     */
    public function isImage(): bool
    {
        return $this->mime && str_starts_with($this->mime, 'image/');
    }

    /**
     * Check if file is a PDF
     */
    public function isPdf(): bool
    {
        return $this->mime === 'application/pdf';
    }

    /**
     * Check if file is a document
     */
    public function isDocument(): bool
    {
        return $this->mime && in_array($this->mime, [
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
