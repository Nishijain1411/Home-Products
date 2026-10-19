<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentTransaction extends Model
{
    protected $fillable = [
        'order_id',
        'gateway',
        'transaction_id',
        'event_id',
        'event_type',
        'amount',
        'currency',
        'status',
        'signature_verified',
        'metadata',
        'raw_payload',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'signature_verified' => 'boolean',
        'metadata' => 'array',
        'raw_payload' => 'array',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
