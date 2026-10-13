<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryAdjustment extends Model
{
    protected $fillable = [
        'inventory_id',
        'user_id',
        'quantity_before',
        'quantity_changed',
        'quantity_after',
        'type',
        'reason',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity_before' => 'integer',
            'quantity_changed' => 'integer',
            'quantity_after' => 'integer',
        ];
    }

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
