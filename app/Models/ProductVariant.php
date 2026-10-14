<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id',
        'title',
        'sku',
        'price',
        'compare_at_price',
        'barcode',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'compare_at_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function optionValues(): BelongsToMany
    {
        return $this->belongsToMany(
            ProductOptionValue::class,
            'product_variant_option_value',
            'product_variant_id',
            'product_option_value_id'
        );
    }

    public function inventory(): HasOne
    {
        return $this->hasOne(Inventory::class);
    }

    public function getEffectivePriceAttribute(): float
    {
        if ($this->price !== null && (float) $this->price > 0) {
            return (float) $this->price;
        }

        return (float) ($this->product?->price ?? 0);
    }

    public function getEffectiveCompareAtPriceAttribute(): ?float
    {
        if ($this->compare_at_price !== null) {
            return (float) $this->compare_at_price;
        }

        return $this->product?->compare_at_price ? (float) $this->product->compare_at_price : null;
    }

    public function getAvailableStockAttribute(): int
    {
        return (int) ($this->inventory?->quantity ?? 0);
    }

    public function getIsInStockAttribute(): bool
    {
        return $this->available_stock > 0;
    }
}
