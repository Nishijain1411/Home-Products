<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\InventoryAdjustment;
use App\Models\Order;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class InventoryService
{
    /**
     * Atomically reserve and deduct inventory for an order item using row locking.
     * Prevents race conditions and overselling when multiple customers checkout simultaneously.
     */
    public function reserveAndDeduct(
        ProductVariant $variant,
        int $quantity,
        ?string $orderNumber = null,
        ?int $userId = null
    ): Inventory {
        if ($quantity <= 0) {
            throw new RuntimeException('Quantity must be greater than zero.');
        }

        $inventory = Inventory::where('product_variant_id', $variant->id)
            ->lockForUpdate()
            ->first();

        if (! $inventory) {
            throw new RuntimeException("Inventory record not found for variant [{$variant->sku}].");
        }

        $available = (int) $inventory->quantity;

        if ($available < $quantity) {
            $productName = $variant->product?->name ?? 'Product';
            throw new RuntimeException("Insufficient stock for {$productName} ({$variant->title}). Only {$available} remaining.");
        }

        $before = $available;
        $after = $before - $quantity;

        $inventory->quantity = $after;
        $inventory->reserved_quantity = (int) $inventory->reserved_quantity + $quantity;
        $inventory->save();

        InventoryAdjustment::create([
            'inventory_id' => $inventory->id,
            'user_id' => $userId,
            'quantity_before' => $before,
            'quantity_changed' => $quantity,
            'quantity_after' => $after,
            'type' => 'decrease',
            'reason' => 'stock_correction',
            'notes' => "Reserved and deducted for Order #{$orderNumber}",
        ]);

        return $inventory;
    }

    /**
     * Release reserved inventory when an order is fulfilled (shipped/delivered).
     */
    public function releaseReservation(Order $order, ?int $userId = null): void
    {
        DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                if (! $item->product_variant_id) {
                    continue;
                }

                $inventory = Inventory::where('product_variant_id', $item->product_variant_id)
                    ->lockForUpdate()
                    ->first();

                if (! $inventory) {
                    continue;
                }

                $currentReserved = (int) $inventory->reserved_quantity;
                $toRelease = min($currentReserved, (int) $item->quantity);

                if ($toRelease > 0) {
                    $inventory->reserved_quantity = $currentReserved - $toRelease;
                    $inventory->save();
                }
            }
        });
    }

    /**
     * Safely and idempotently restore inventory when an order is cancelled or refunded.
     * Prevents double-restoration or stock corruption.
     */
    public function restoreStock(Order $order, string $reason = 'return', ?int $userId = null): bool
    {
        // Idempotency check: Do not restore if already restored
        if ($order->inventory_restored_at !== null) {
            return false;
        }

        return DB::transaction(function () use ($order, $reason, $userId) {
            // Re-check order with lock to prevent concurrent double-restores
            $lockedOrder = Order::where('id', $order->id)
                ->lockForUpdate()
                ->first();

            if (! $lockedOrder || $lockedOrder->inventory_restored_at !== null) {
                return false;
            }

            foreach ($lockedOrder->items as $item) {
                if (! $item->product_variant_id) {
                    continue;
                }

                $inventory = Inventory::where('product_variant_id', $item->product_variant_id)
                    ->lockForUpdate()
                    ->first();

                if (! $inventory) {
                    continue;
                }

                $before = (int) $inventory->quantity;
                $qty = (int) $item->quantity;
                $after = $before + $qty;

                $inventory->quantity = $after;

                // If unfulfilled, also release reservation
                if ($inventory->reserved_quantity >= $qty) {
                    $inventory->reserved_quantity = (int) $inventory->reserved_quantity - $qty;
                } else {
                    $inventory->reserved_quantity = 0;
                }

                $inventory->save();

                $allowedReasons = ['new_stock', 'damaged_goods', 'stock_correction', 'return', 'other'];
                $adjustmentReason = in_array($reason, $allowedReasons, true) ? $reason : 'return';

                InventoryAdjustment::create([
                    'inventory_id' => $inventory->id,
                    'user_id' => $userId,
                    'quantity_before' => $before,
                    'quantity_changed' => $qty,
                    'quantity_after' => $after,
                    'type' => 'increase',
                    'reason' => $adjustmentReason,
                    'notes' => "Restored stock from Order #{$lockedOrder->order_number} ({$reason})",
                ]);
            }

            $lockedOrder->update([
                'inventory_restored_at' => now(),
            ]);

            return true;
        });
    }
}
