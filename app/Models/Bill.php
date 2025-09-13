<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Services\SettingsService;
class Bill extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'patient_id',
        'encounter_id',
        'clinic_id',
        'bill_number',
        'bill_date',
        'due_date',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'paid_amount',
        'balance_amount',
        'status',
        'payment_method',
        'payment_reference',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'bill_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance_amount' => 'decimal:2',
    ];

    protected $attributes = [
        'status' => 'pending',
        'subtotal' => 0.00,
        'tax_amount' => 0.00,
        'discount_amount' => 0.00,
        'total_amount' => 0.00,
        'paid_amount' => 0.00,
        'balance_amount' => 0.00,
    ];


    // Relationships
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function encounter(): BelongsTo
    {
        return $this->belongsTo(Encounter::class);
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(BillItem::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'pending')
                    ->where('due_date', '<', now());
    }

    public function scopeOutstanding($query)
    {
        return $query->where('status', 'pending')
                    ->where('balance_amount', '>', 0);
    }

    // Accessors & Mutators
    public function getIsPaidAttribute(): bool
    {
        return $this->status === 'paid';
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->status === 'pending' && $this->due_date < now();
    }

    public function getBalanceAttribute(): float
    {
        return $this->total_amount - $this->paid_amount;
    }

    // Methods
    public function calculateTotal(): void
    {
        $this->subtotal = $this->items()->sum('total');

        // Apply default tax rate from settings if not set
        if ($this->tax_amount == 0) {
            $settingsService = app(SettingsService::class);
            $taxRate = $settingsService->get('billing.tax_rate', 12.0, $this->clinic_id);
            $this->tax_amount = $this->subtotal * ($taxRate / 100);
        }

        $this->total_amount = $this->subtotal + $this->tax_amount - $this->discount_amount;
        $this->balance_amount = $this->total_amount - $this->paid_amount;
        $this->save();
    }

    public function markAsPaid(float $amount, string $method = null, string $reference = null): void
    {
        $this->paid_amount += $amount;
        $this->balance_amount = $this->total_amount - $this->paid_amount;

        if ($method) {
            $this->payment_method = $method;
        }

        if ($reference) {
            $this->payment_reference = $reference;
        }

        if ($this->balance_amount <= 0) {
            $this->status = 'paid';
        }

        $this->save();
    }

    public function generateBillNumber(): string
    {
        $prefix = 'BILL';
        $year = now()->year;
        $month = now()->format('m');

        $lastBill = static::whereYear('created_at', $year)
                         ->whereMonth('created_at', $month)
                         ->orderBy('id', 'desc')
                         ->first();

        $sequence = $lastBill ? (int) substr($lastBill->bill_number, -4) + 1 : 1;

        return $prefix . $year . $month . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($bill) {
            if (empty($bill->uuid)) {
                $bill->uuid = (string) \Illuminate\Support\Str::uuid();
            }
            if (empty($bill->bill_number)) {
                $bill->bill_number = $bill->generateBillNumber();
            }

            // Set default due date from settings
            if (empty($bill->due_date)) {
                $settingsService = app(SettingsService::class);
                $paymentTermsDays = $settingsService->get('billing.payment_terms_days', 30, $bill->clinic_id);
                $bill->due_date = now()->addDays($paymentTermsDays);
            }
        });
    }
}
