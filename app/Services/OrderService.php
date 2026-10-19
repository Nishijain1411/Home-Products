<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\PaymentTransaction;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;

class OrderService
{
    public function createOrder(
        array $customerData,
        array $shippingAddress,
        array $billingAddress,
        array $cart,
        array $paymentData = [],
        ?string $couponCode = null
    ): Order {
        $order = DB::transaction(function () use (
            $customerData,
            $shippingAddress,
            $billingAddress,
            $cart,
            $paymentData,
            $couponCode
        ) {
            if (empty($cart)) {
                throw new RuntimeException('Cart is empty.');
            }

            $subtotal = 0;
            $items = [];

            // 1. Recheck stock atomically using row locking to prevent overselling under high concurrency
            foreach ($cart as $item) {
                $variant = ProductVariant::with(['product'])->find($item['variant_id']);

                if (! $variant) {
                    throw new RuntimeException(
                        'A product in your cart is no longer available.'
                    );
                }

                $quantity = (int) $item['quantity'];

                if ($quantity <= 0) {
                    throw new RuntimeException(
                        'Invalid product quantity.'
                    );
                }

                // Row lock inventory to prevent simultaneous checkout race conditions
                $inventory = Inventory::where('product_variant_id', $variant->id)
                    ->lockForUpdate()
                    ->first();

                if (! $inventory) {
                    throw new RuntimeException(
                        "Inventory is unavailable for {$variant->product->name}."
                    );
                }

                if ($inventory->quantity < $quantity) {
                    throw new RuntimeException(
                        "Insufficient stock for {$variant->product->name}. Only {$inventory->quantity} item(s) left."
                    );
                }

                $unitPrice = (float) ($variant->effective_price ?? $variant->price ?? $variant->product->price);
                $lineTotal = $unitPrice * $quantity;

                $subtotal += $lineTotal;

                $items[] = [
                    'variant' => $variant,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total' => $lineTotal,
                ];
            }

            $customerId = $customerData['customer_id'] ?? null;

            if (! $customerId) {
                $existingCustomer = User::where('email', $customerData['email'])->first();

                if ($existingCustomer) {
                    // Reuse only an existing customer account.
                    if ($existingCustomer->hasRole('Customer')) {
                        $customerId = $existingCustomer->id;
                    }
                } else {
                    $customer = User::create([
                        'name' => trim(
                            $customerData['first_name'].' '.
                            $customerData['last_name']
                        ),
                        'email' => $customerData['email'],
                        'password' => Hash::make(
                            Str::random(48)
                        ),
                        'is_active' => true,
                    ]);

                    $customer->assignRole('Customer');

                    $customerId = $customer->id;
                }
            }

            $discountService = app(DiscountService::class);
            $discount = null;
            $discountAmount = 0.0;
            $codeToApply = $couponCode ?? ($paymentData['coupon_code'] ?? ($customerData['coupon_code'] ?? ($discountService->getAppliedCoupon()['code'] ?? null)));

            if ($codeToApply) {
                $validation = $discountService->validateCode(
                    $codeToApply,
                    $subtotal,
                    $customerId,
                    $customerData['email'] ?? null
                );

                if (! $validation['valid']) {
                    throw new RuntimeException($validation['message'] ?? 'Invalid coupon code.');
                }

                $discount = $validation['discount'];
                $discountAmount = (float) $validation['discount_amount'];
            }

            $settingService = app(SettingService::class);
            $totals = $settingService->calculateTotals($subtotal, $discountAmount);
            $shippingAmount = $totals['shipping'];
            $taxAmount = $totals['tax'];
            $total = $totals['total'];
            $paymentMethod = $paymentData['method'] ?? 'cod';
            $paymentStatus = $paymentData['status'] ?? 'pending';
            $paymentReference = $paymentData['reference'] ?? null;
            $paidAt = $paymentData['paid_at'] ?? null;

            $billingAddress['payment'] = [
                'method' => $paymentMethod,
                'status' => $paymentStatus,
                'reference' => $paymentReference,
                'paid_at' => $paidAt ? (is_string($paidAt) ? $paidAt : (method_exists($paidAt, 'toDateTimeString') ? $paidAt->toDateTimeString() : (string) $paidAt)) : null,
                'card_last4' => $paymentData['card_last4'] ?? null,
                'card_name' => $paymentData['card_name'] ?? null,
                'upi_id' => $paymentData['upi_id'] ?? null,
            ];

            $orderAttributes = [
                'order_number' => $this->generateOrderNumber(),

                'customer_id' => $customerId,
                'customer_email' => $customerData['email'],
                'customer_phone' => $customerData['phone'],
                'customer_first_name' => $customerData['first_name'],
                'customer_last_name' => $customerData['last_name'],

                'status' => 'pending',
                'payment_status' => $paymentStatus,
                'subtotal' => $subtotal,
                'discount_id' => $discount?->id,
                'coupon_code' => $discount?->code,
                'discount_amount' => $discountAmount,
                'shipping_amount' => $shippingAmount,
                'tax_amount' => $taxAmount,
                'total' => $total,
                'shipping_address' => $shippingAddress,
                'billing_address' => $billingAddress,
            ];

            if (Schema::hasColumn('orders', 'payment_method')) {
                $orderAttributes['payment_method'] = $paymentMethod;
                $orderAttributes['payment_reference'] = $paymentReference;
                $orderAttributes['paid_at'] = $paidAt;
            }

            $order = Order::create($orderAttributes);

            // Record initial order status history
            OrderStatusHistory::create([
                'order_id' => $order->id,
                'from_status' => null,
                'to_status' => $order->status,
                'user_id' => $customerId,
                'user_name' => trim(($customerData['first_name'] ?? '').' '.($customerData['last_name'] ?? '')) ?: 'Customer',
                'reason' => 'Order placed',
            ]);

            if ($paymentReference || $paymentStatus) {
                PaymentTransaction::firstOrCreate(
                    ['transaction_id' => $paymentReference ?: ('TXN-'.$order->order_number)],
                    [
                        'order_id' => $order->id,
                        'gateway' => $paymentMethod,
                        'amount' => $total,
                        'currency' => 'INR',
                        'status' => $paymentStatus,
                        'signature_verified' => (bool) ($paymentData['signature_verified'] ?? ($paymentStatus === 'paid')),
                        'metadata' => [
                            'card_last4' => $paymentData['card_last4'] ?? null,
                            'card_name' => $paymentData['card_name'] ?? null,
                            'upi_id' => $paymentData['upi_id'] ?? null,
                        ],
                        'raw_payload' => $paymentData,
                    ]
                );
            }

            if ($discount) {
                $discountService->recordRedemption(
                    $discount,
                    $order,
                    $discountAmount,
                    $customerId,
                    $customerData['email'] ?? null
                );

                $discountService->removeAppliedCoupon();
            }

            $inventoryService = app(InventoryService::class);

            foreach ($items as $item) {
                $variant = $item['variant'];

                $order->items()->create([
                    'product_id' => $variant->product_id,
                    'product_variant_id' => $variant->id,
                    'product_name' => $variant->product->name,
                    'variant_name' => $variant->title,
                    'sku' => $variant->sku,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total' => $item['total'],
                ]);

                // Atomically reserve and deduct stock via InventoryService
                $inventoryService->reserveAndDeduct(
                    $variant,
                    $item['quantity'],
                    $order->order_number,
                    $customerId
                );
            }

            return $order;
        });

        // Trigger order notifications and stock checks safely outside the database transaction
        try {
            $settingService = app(SettingService::class);
            $settingService->notifyOrderPlaced($order);

            foreach ($cart as $item) {
                $variant = ProductVariant::find($item['variant_id']);
                if ($variant) {
                    $settingService->checkAndNotifyLowStock($variant);
                }
            }
        } catch (\Throwable) {
            // Silently avoid interrupting successful order placement
        }

        return $order;
    }

    protected function generateOrderNumber(): string
    {
        $settingService = app(SettingService::class);
        $prefix = (string) $settingService->get('order_number_prefix', 'ORD-');
        $digits = max(4, min(12, (int) $settingService->get('order_number_digits', 6)));

        do {
            $number = $prefix.now()->format('Ymd').'-'.strtoupper(
                Str::random($digits)
            );
        } while (Order::where('order_number', $number)->exists());

        return $number;
    }
}
