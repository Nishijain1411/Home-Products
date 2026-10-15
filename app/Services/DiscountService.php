<?php

namespace App\Services;

use App\Models\Discount;
use App\Models\DiscountRedemption;
use App\Models\Order;
use RuntimeException;

class DiscountService
{
    protected string $sessionKey = 'applied_coupon';

    public function findByCode(string $code): ?Discount
    {
        $normalized = strtoupper(trim($code));

        if ($normalized === '') {
            return null;
        }

        return Discount::where('code', $normalized)->first();
    }

    /**
     * Validate a coupon code against current subtotal and customer.
     *
     * @return array{valid: bool, discount: ?Discount, discount_amount: float, message: ?string}
     */
    public function validateCode(string $code, float $subtotal, ?int $customerId = null, ?string $customerEmail = null): array
    {
        $code = trim($code);

        if ($code === '') {
            return [
                'valid' => false,
                'discount' => null,
                'discount_amount' => 0.0,
                'message' => 'Please enter a coupon code.',
            ];
        }

        $discount = $this->findByCode($code);

        if (! $discount) {
            return [
                'valid' => false,
                'discount' => null,
                'discount_amount' => 0.0,
                'message' => 'Invalid coupon code.',
            ];
        }

        $eligibility = $discount->checkEligibility($subtotal, $customerId, $customerEmail);

        if (! $eligibility['eligible']) {
            return [
                'valid' => false,
                'discount' => $discount,
                'discount_amount' => 0.0,
                'message' => $eligibility['message'],
            ];
        }

        $discountAmount = $discount->calculateDiscountAmount($subtotal);

        return [
            'valid' => true,
            'discount' => $discount,
            'discount_amount' => $discountAmount,
            'message' => null,
        ];
    }

    /**
     * Atomically redeem a discount against an order and prevent concurrency over-redemption.
     */
    public function recordRedemption(
        Discount $discount,
        Order $order,
        float $discountAmount,
        ?int $customerId = null,
        ?string $customerEmail = null
    ): DiscountRedemption {
        // Lock discount record to prevent race conditions during concurrent checkouts
        $lockedDiscount = Discount::where('id', $discount->id)
            ->lockForUpdate()
            ->firstOrFail();

        $eligibility = $lockedDiscount->checkEligibility((float) $order->subtotal, $customerId, $customerEmail);

        if (! $eligibility['eligible']) {
            throw new RuntimeException(
                $eligibility['message'] ?? 'Coupon is no longer eligible for this order.'
            );
        }

        $redemption = DiscountRedemption::create([
            'discount_id' => $lockedDiscount->id,
            'order_id' => $order->id,
            'customer_id' => $customerId,
            'customer_email' => $customerEmail ? strtolower(trim($customerEmail)) : null,
            'amount' => round($discountAmount, 2),
        ]);

        $lockedDiscount->increment('times_used');

        return $redemption;
    }

    /**
     * Get the currently applied coupon from session.
     *
     * @return array{id: int, code: string, name: string, type: string, value: float, amount: float}|null
     */
    public function getAppliedCoupon(): ?array
    {
        return session()->get($this->sessionKey);
    }

    /**
     * Store applied coupon in session.
     */
    public function setAppliedCoupon(Discount $discount, float $calculatedAmount): void
    {
        session()->put($this->sessionKey, [
            'id' => $discount->id,
            'code' => $discount->code,
            'name' => $discount->name,
            'type' => $discount->type,
            'value' => (float) $discount->value,
            'amount' => round($calculatedAmount, 2),
        ]);
    }

    /**
     * Remove the applied coupon from session.
     */
    public function removeAppliedCoupon(): void
    {
        session()->forget($this->sessionKey);
    }
}
