<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Discount extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'type',
        'value',
        'min_order_amount',
        'max_discount_amount',
        'usage_limit',
        'usage_limit_per_customer',
        'times_used',
        'starts_at',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
        'usage_limit' => 'integer',
        'usage_limit_per_customer' => 'integer',
        'times_used' => 'integer',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    protected function code(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => strtoupper(trim((string) $value)),
        );
    }

    public function redemptions(): HasMany
    {
        return $this->hasMany(DiscountRedemption::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && now()->isAfter($this->expires_at);
    }

    public function hasStarted(): bool
    {
        if ($this->starts_at === null) {
            return true;
        }

        // Allow up to a 60-second grace window to absorb slight clock drift between browser and server
        return now()->addSeconds(60)->gte($this->starts_at);
    }

    public function hasReachedUsageLimit(): bool
    {
        return $this->usage_limit !== null && $this->times_used >= $this->usage_limit;
    }

    public function hasReachedCustomerLimit(?int $customerId = null, ?string $customerEmail = null): bool
    {
        if ($this->usage_limit_per_customer === null) {
            return false;
        }

        if (! $customerId && ! $customerEmail) {
            return false;
        }

        $customerRedemptions = $this->redemptions()
            ->where(function ($query) use ($customerId, $customerEmail) {
                if ($customerId) {
                    $query->where('customer_id', $customerId);
                }
                if ($customerEmail) {
                    if ($customerId) {
                        $query->orWhere('customer_email', strtolower(trim($customerEmail)));
                    } else {
                        $query->where('customer_email', strtolower(trim($customerEmail)));
                    }
                }
            })
            ->count();

        return $customerRedemptions >= $this->usage_limit_per_customer;
    }

    public function calculateDiscountAmount(float $subtotal): float
    {
        if ($subtotal <= 0) {
            return 0.0;
        }

        if ($this->type === 'percentage') {
            $discount = ($subtotal * ((float) $this->value / 100));

            if ($this->max_discount_amount !== null && (float) $this->max_discount_amount > 0) {
                $discount = min($discount, (float) $this->max_discount_amount);
            }
        } else {
            $discount = min($subtotal, (float) $this->value);
        }

        return max(0.0, round($discount, 2));
    }

    /**
     * Check if this discount can be applied to the given subtotal and customer.
     *
     * @return array{eligible: bool, message: ?string}
     */
    public function checkEligibility(float $subtotal, ?int $customerId = null, ?string $customerEmail = null): array
    {
        if (! $this->is_active) {
            return [
                'eligible' => false,
                'message' => 'This discount code is currently inactive.',
            ];
        }

        if (! $this->hasStarted()) {
            return [
                'eligible' => false,
                'message' => 'This discount code is not yet active.',
            ];
        }

        if ($this->isExpired()) {
            return [
                'eligible' => false,
                'message' => 'This discount code has expired.',
            ];
        }

        if ($this->hasReachedUsageLimit()) {
            return [
                'eligible' => false,
                'message' => 'This discount code has reached its maximum usage limit.',
            ];
        }

        if ($this->min_order_amount !== null && $subtotal < (float) $this->min_order_amount) {
            $required = number_format((float) $this->min_order_amount, 2);

            return [
                'eligible' => false,
                'message' => "A minimum subtotal of ₹{$required} is required to use this coupon.",
            ];
        }

        if ($this->hasReachedCustomerLimit($customerId, $customerEmail)) {
            return [
                'eligible' => false,
                'message' => 'You have already reached the redemption limit for this coupon code.',
            ];
        }

        return [
            'eligible' => true,
            'message' => null,
        ];
    }
}
