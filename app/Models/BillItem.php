<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasUuid;

class BillItem extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'uuid',
        'bill_id',
        'item_type',
        'item_name',
        'item_description',
        'quantity',
        'unit_price',
        'discount_percentage',
        'discount_amount',
        'tax_percentage',
        'tax_amount',
        'total',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_percentage' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    protected $attributes = [
        'quantity' => 1.00,
        'unit_price' => 0.00,
        'discount_percentage' => 0.00,
        'discount_amount' => 0.00,
        'tax_percentage' => 0.00,
        'tax_amount' => 0.00,
        'total' => 0.00,
    ];

    // Relationships
    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Accessors & Mutators
    public function getSubtotalAttribute(): float
    {
        return $this->quantity * $this->unit_price;
    }

    public function getNetAmountAttribute(): float
    {
        return $this->subtotal - $this->discount_amount;
    }

    // Methods
    public function calculateTotal(): void
    {
        $subtotal = $this->quantity * $this->unit_price;
        $this->discount_amount = $subtotal * ($this->discount_percentage / 100);
        $netAmount = $subtotal - $this->discount_amount;
        $this->tax_amount = $netAmount * ($this->tax_percentage / 100);
        $this->total = $netAmount + $this->tax_amount;
        $this->save();
    }

    protected static function boot()
    {
        parent::boot();
        
        static::saving(function ($item) {
            $item->calculateTotal();
        });
        
        static::saved(function ($item) {
            // Recalculate bill total when item is saved
            $item->bill->calculateTotal();
        });
        
        static::deleted(function ($item) {
            // Recalculate bill total when item is deleted
            $item->bill->calculateTotal();
        });
    }
}
