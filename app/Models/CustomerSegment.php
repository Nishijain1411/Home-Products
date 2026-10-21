<?php

namespace App\Models;

use Database\Factories\CustomerSegmentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CustomerSegment extends Model
{
    /** @use HasFactory<CustomerSegmentFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'color',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Customers belonging to this segment.
     */
    public function customers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'customer_segment_user')
            ->withTimestamps();
    }

    /**
     * Discounts targeted to this customer segment.
     */
    public function discounts(): BelongsToMany
    {
        return $this->belongsToMany(Discount::class, 'customer_segment_discount');
    }
}
