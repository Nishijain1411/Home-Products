<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'order_number',
        'customer_id',
        'status',
        'payment_status',
        'payment_method',
        'payment_reference',
        'paid_at',
        'subtotal',
        'shipping_amount',
        'tax_amount',
        'total',
        'shipping_address',
        'billing_address',
        'customer_email',
        'customer_phone',
        'customer_first_name',
        'customer_last_name',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'shipping_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'shipping_address' => 'array',
        'billing_address' => 'array',
        'paid_at' => 'datetime',
    ];

    public function getPaymentMethodAttribute($value): ?string
    {
        return $value ?? ($this->billing_address['payment']['method'] ?? 'cod');
    }

    public function getPaymentReferenceAttribute($value): ?string
    {
        return $value ?? ($this->billing_address['payment']['reference'] ?? null);
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        $method = $this->payment_method;

        return match ($method) {
            'cod' => 'Cash on Delivery',
            'card' => 'Credit / Debit Card',
            'upi' => 'UPI',
            default => ucfirst((string) $method),
        };
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }
}
