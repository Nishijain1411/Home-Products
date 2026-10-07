<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{

    protected $fillable = [
        'product_variant_id',
        'quantity',
        'reserved_quantity',
    ];
    protected $casts = [
        'quantity' => 'integer',
        'reserved_quantity' => 'integer',
    ];
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
