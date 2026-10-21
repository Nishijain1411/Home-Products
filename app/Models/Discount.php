<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Discount extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'type',
        'customer_eligibility',
        'applies_to',
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

    public function customerSegments(): BelongsToMany
    {
        return $this->belongsToMany(CustomerSegment::class, 'customer_segment_discount')
            ->withTimestamps();
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_discount')
            ->withTimestamps();
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'discount_product')
            ->withTimestamps();
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

    /**
     * Determine if customer is eligible under the discount's customer_eligibility rule.
     */
    public function isCustomerEligible(?int $customerId = null, ?string $customerEmail = null): bool
    {
        if ($this->customer_eligibility !== 'segments') {
            return true;
        }

        $targetSegmentIds = $this->customerSegments()->pluck('customer_segments.id')->all();
        if (empty($targetSegmentIds)) {
            return true;
        }

        $user = null;
        if ($customerId) {
            $user = User::find($customerId);
        } elseif ($customerEmail) {
            $user = User::where('email', strtolower(trim($customerEmail)))->first();
        }

        if (! $user) {
            return false;
        }

        return $user->segments()->whereIn('customer_segments.id', $targetSegmentIds)->exists();
    }

    /**
     * Calculate eligible subtotal when discount is scoped to specific products or categories.
     */
    public function calculateEligibleSubtotal(?array $cartItems, float $fallbackSubtotal): float
    {
        if (empty($cartItems) || $this->applies_to === 'all') {
            return $fallbackSubtotal;
        }

        $variantIds = array_filter(array_map(fn ($item) => $item['variant_id'] ?? null, $cartItems));
        if (empty($variantIds)) {
            return $fallbackSubtotal;
        }

        if ($this->applies_to === 'products') {
            $targetProductIds = $this->products()->pluck('products.id')->all();
            if (empty($targetProductIds)) {
                return $fallbackSubtotal;
            }

            $variants = ProductVariant::whereIn('id', $variantIds)->pluck('product_id', 'id')->all();
            $eligibleTotal = 0.0;

            foreach ($cartItems as $item) {
                $vId = $item['variant_id'] ?? null;
                $pId = $item['product_id'] ?? ($variants[$vId] ?? null);
                if ($pId && in_array($pId, $targetProductIds)) {
                    $eligibleTotal += ((float) ($item['price'] ?? 0)) * ((int) ($item['quantity'] ?? 1));
                }
            }

            return round($eligibleTotal, 2);
        }

        if ($this->applies_to === 'categories') {
            $targetCategoryIds = $this->categories()->pluck('categories.id')->all();
            if (empty($targetCategoryIds)) {
                return $fallbackSubtotal;
            }

            $variants = ProductVariant::with('product.categories')->whereIn('id', $variantIds)->get();
            $variantCatMap = [];
            foreach ($variants as $v) {
                $variantCatMap[$v->id] = $v->product?->categories->pluck('id')->all() ?? [];
            }

            $eligibleTotal = 0.0;
            foreach ($cartItems as $item) {
                $vId = $item['variant_id'] ?? null;
                $catIds = $variantCatMap[$vId] ?? [];
                if (! empty(array_intersect($catIds, $targetCategoryIds))) {
                    $eligibleTotal += ((float) ($item['price'] ?? 0)) * ((int) ($item['quantity'] ?? 1));
                }
            }

            return round($eligibleTotal, 2);
        }

        return $fallbackSubtotal;
    }

    /**
     * Calculate the net discount amount deducted from the subtotal.
     */
    public function calculateDiscountAmount(float $subtotal, ?array $cartItems = null): float
    {
        if ($subtotal <= 0) {
            return 0.0;
        }

        $applicableSubtotal = $this->calculateEligibleSubtotal($cartItems, $subtotal);

        if ($applicableSubtotal <= 0) {
            return 0.0;
        }

        if ($this->type === 'percentage') {
            $discount = ($applicableSubtotal * ((float) $this->value / 100));

            if ($this->max_discount_amount !== null && (float) $this->max_discount_amount > 0) {
                $discount = min($discount, (float) $this->max_discount_amount);
            }
        } else {
            $discount = min($applicableSubtotal, (float) $this->value);
        }

        return max(0.0, round($discount, 2));
    }

    /**
     * Check if this discount can be applied to the given subtotal, customer, and cart items.
     *
     * @return array{eligible: bool, message: ?string}
     */
    public function checkEligibility(float $subtotal, ?int $customerId = null, ?string $customerEmail = null, ?array $cartItems = null): array
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

        // Customer Segment eligibility check
        if ($this->customer_eligibility === 'segments') {
            if (! $this->isCustomerEligible($customerId, $customerEmail)) {
                $segmentNames = $this->customerSegments()->pluck('name')->implode(', ');
                $suffix = $segmentNames ? " ({$segmentNames})" : '';

                return [
                    'eligible' => false,
                    'message' => "This coupon is exclusively available to members of the selected customer segment{$suffix}.",
                ];
            }
        }

        // Product / Category applies_to check
        if (! empty($cartItems) && in_array($this->applies_to, ['products', 'categories'])) {
            $eligibleSubtotal = $this->calculateEligibleSubtotal($cartItems, 0.0);
            if ($eligibleSubtotal <= 0) {
                if ($this->applies_to === 'products') {
                    $prodNames = $this->products()->pluck('name')->implode(', ');
                    $msg = $prodNames
                        ? "This coupon is only valid for: {$prodNames}."
                        : 'This coupon is only valid on select products.';

                    return [
                        'eligible' => false,
                        'message' => $msg,
                    ];
                }

                if ($this->applies_to === 'categories') {
                    $catNames = $this->categories()->pluck('name')->implode(', ');
                    $msg = $catNames
                        ? "This coupon is only valid on the following categories: {$catNames}."
                        : 'This coupon is only valid on select categories.';

                    return [
                        'eligible' => false,
                        'message' => $msg,
                    ];
                }
            }
        }

        return [
            'eligible' => true,
            'message' => null,
        ];
    }
}
