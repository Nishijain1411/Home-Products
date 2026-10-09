<?php

namespace App\Services;

use App\Models\Order;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class OrderService
{
    public function createOrder(
        array $customerData,
        array $shippingAddress,
        array $billingAddress,
        array $cart
    ): Order {
        return DB::transaction(function () use (
            $customerData,
            $shippingAddress,
            $billingAddress,
            $cart
        ) {
            if (empty($cart)) {
                throw new RuntimeException('Cart is empty.');
            }

            $subtotal = 0;
            $items = [];

            foreach ($cart as $item) {

                $variant = ProductVariant::with([
                    'product',
                    'inventory',
                ])->find($item['variant_id']);

                if (!$variant) {
                    throw new RuntimeException(
                        'A product in your cart is no longer available.'
                    );
                }

                $inventory = $variant->inventory;

                if (!$inventory) {
                    throw new RuntimeException(
                        "Inventory is unavailable for {$variant->product->name}."
                    );
                }

                $quantity = (int) $item['quantity'];

                if ($quantity <= 0) {
                    throw new RuntimeException(
                        'Invalid product quantity.'
                    );
                }

                if ($inventory->quantity < $quantity) {
                    throw new RuntimeException(
                        "Insufficient stock for {$variant->product->name}."
                    );
                }

                $unitPrice = (float) $variant->price;
                $lineTotal = $unitPrice * $quantity;

                $subtotal += $lineTotal;

                $items[] = [
                    'variant' => $variant,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total' => $lineTotal,
                ];
            }

            $shippingAmount = 0;
            $taxAmount = 0;
            $total = $subtotal + $shippingAmount + $taxAmount;

            $order = Order::create([
                'order_number' => $this->generateOrderNumber(),
                'customer_id' => $customerData['customer_id'] ?? null,
                'status' => 'pending',
                'payment_status' => 'pending',
                'subtotal' => $subtotal,
                'shipping_amount' => $shippingAmount,
                'tax_amount' => $taxAmount,
                'total' => $total,
                'shipping_address' => $shippingAddress,
                'billing_address' => $billingAddress,
            ]);

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

                /*
                 * Reserve inventory.
                 */
                $variant->inventory()->decrement(
                    'quantity',
                    $item['quantity']
                );

                $variant->inventory()->increment(
                    'reserved_quantity',
                    $item['quantity']
                );
            }

            return $order;
        });
    }

    protected function generateOrderNumber(): string
    {
        do {
            $number = 'ORD-' . now()->format('Ymd') . '-' . strtoupper(
                Str::random(6)
            );
        } while (Order::where('order_number', $number)->exists());

        return $number;
    }
}