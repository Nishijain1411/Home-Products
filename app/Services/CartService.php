<?php

namespace App\Services;

use App\Models\ProductVariant;
use RuntimeException;

class CartService
{
    protected string $sessionKey = 'cart';

    public function get(): array
    {
        $cart = session()->get($this->sessionKey, []);

        foreach ($cart as $variantId => &$item) {
            $variant = ProductVariant::with(['product.images', 'inventory'])
                ->find($item['variant_id'] ?? $variantId);

            if ($variant) {
                $image = $variant->product->images()
                    ->orderByDesc('is_primary')
                    ->orderBy('position')
                    ->first();

                $item['product_name'] = $variant->product->name;
                $item['image'] = $image?->path;
                $item['available_stock'] = (int) ($variant->inventory?->quantity ?? 0);
            } else {
                $item['product_name'] ??= 'Product';
                $item['image'] ??= null;
                $item['available_stock'] = 0;
            }

            $item['is_out_of_stock'] = $item['available_stock'] <= 0;
            $item['exceeds_stock'] = ! $item['is_out_of_stock'] && ($item['quantity'] > $item['available_stock']);
        }

        unset($item);

        session()->put($this->sessionKey, $cart);

        return $cart;
    }

    public function add(ProductVariant $variant, int $quantity = 1): void
    {
        if ($quantity <= 0) {
            throw new RuntimeException('Quantity must be at least 1.');
        }

        $variant->loadMissing(['product.images', 'inventory']);

        $inventory = $variant->inventory;
        $availableStock = (int) ($inventory?->quantity ?? 0);

        if ($availableStock <= 0) {
            throw new RuntimeException("Sorry, {$variant->product->name} is currently out of stock.");
        }

        $cart = session()->get($this->sessionKey, []);
        $variantId = (string) $variant->id;

        $existingQuantity = (int) ($cart[$variantId]['quantity'] ?? 0);
        $totalQuantity = $existingQuantity + $quantity;

        if ($totalQuantity > $availableStock) {
            $canAdd = max(0, $availableStock - $existingQuantity);
            if ($canAdd === 0) {
                throw new RuntimeException("You already have all {$availableStock} available item(s) in your cart.");
            }

            throw new RuntimeException("Only {$availableStock} available in stock. You can only add {$canAdd} more to your cart.");
        }

        $product = $variant->product;

        $image = $product->images()
            ->orderByDesc('is_primary')
            ->orderBy('position')
            ->first();

        $cart[$variantId] = [
            'variant_id' => $variant->id,
            'product_id' => $variant->product_id,
            'product_name' => $product->name,
            'title' => $variant->title,
            'quantity' => $totalQuantity,
            'price' => (float) ($variant->effective_price ?? $variant->price ?? $product->price),
            'image' => $image?->path,
            'available_stock' => $availableStock,
            'is_out_of_stock' => false,
            'exceeds_stock' => false,
        ];

        session()->put($this->sessionKey, $cart);
    }

    public function updateQuantity(int $variantId, int $quantity): void
    {
        $cart = session()->get($this->sessionKey, []);
        $key = (string) $variantId;

        if (! isset($cart[$key])) {
            return;
        }

        if ($quantity <= 0) {
            unset($cart[$key]);
            session()->put($this->sessionKey, $cart);

            return;
        }

        $variant = ProductVariant::with('inventory')->find($variantId);
        $availableStock = (int) ($variant?->inventory?->quantity ?? 0);

        if ($quantity > $availableStock) {
            $cart[$key]['quantity'] = max(1, $availableStock);
            $cart[$key]['available_stock'] = $availableStock;
            session()->put($this->sessionKey, $cart);

            throw new RuntimeException("Only {$availableStock} item(s) available in stock. Quantity adjusted.");
        }

        $cart[$key]['quantity'] = $quantity;
        $cart[$key]['available_stock'] = $availableStock;
        session()->put($this->sessionKey, $cart);
    }

    public function remove(int $variantId): void
    {
        $cart = session()->get($this->sessionKey, []);
        $key = (string) $variantId;

        unset($cart[$key]);

        session()->put($this->sessionKey, $cart);
    }

    public function count(): int
    {
        return collect(session()->get($this->sessionKey, []))
            ->sum('quantity');
    }

    public function subtotal(): float
    {
        return collect(session()->get($this->sessionKey, []))
            ->sum(function ($item) {
                return ((float) $item['price']) * ((int) $item['quantity']);
            });
    }

    public function clear(): void
    {
        session()->forget($this->sessionKey);
    }

    /**
     * Validate current cart items against real-time database inventory.
     */
    public function validateStock(): array
    {
        $cart = $this->get();
        $issues = [];

        foreach ($cart as $variantId => $item) {
            $variant = ProductVariant::with(['product', 'inventory'])->find($item['variant_id'] ?? $variantId);

            if (! $variant || ! $variant->product?->is_active || ! $variant->is_active) {
                $issues[] = [
                    'variant_id' => (int) $variantId,
                    'product_name' => $item['product_name'] ?? 'Product',
                    'title' => $item['title'] ?? '',
                    'requested_quantity' => (int) $item['quantity'],
                    'available_quantity' => 0,
                    'type' => 'unavailable',
                    'message' => ($item['product_name'] ?? 'An item').' is no longer available.',
                ];

                continue;
            }

            $available = (int) ($variant->inventory?->quantity ?? 0);
            $requested = (int) $item['quantity'];

            if ($available <= 0) {
                $issues[] = [
                    'variant_id' => (int) $variantId,
                    'product_name' => $variant->product->name,
                    'title' => $variant->title,
                    'requested_quantity' => $requested,
                    'available_quantity' => 0,
                    'type' => 'out_of_stock',
                    'message' => "{$variant->product->name} is out of stock.",
                ];
            } elseif ($requested > $available) {
                $issues[] = [
                    'variant_id' => (int) $variantId,
                    'product_name' => $variant->product->name,
                    'title' => $variant->title,
                    'requested_quantity' => $requested,
                    'available_quantity' => $available,
                    'type' => 'exceeds_stock',
                    'message' => "{$variant->product->name} only has {$available} left in stock (you have {$requested} in cart).",
                ];
            }
        }

        return [
            'has_issues' => ! empty($issues),
            'issues' => $issues,
        ];
    }

    /**
     * Auto-adjust cart items with stock issues (caps quantities to available stock and removes out-of-stock items).
     */
    public function autoAdjustCartStock(): array
    {
        $validation = $this->validateStock();
        $adjusted = [];

        foreach ($validation['issues'] as $issue) {
            $variantId = $issue['variant_id'];
            $available = $issue['available_quantity'];

            if ($available <= 0) {
                $this->remove($variantId);
                $adjusted[] = "Removed {$issue['product_name']} because it is out of stock.";
            } else {
                $cart = session()->get($this->sessionKey, []);
                $key = (string) $variantId;
                if (isset($cart[$key])) {
                    $cart[$key]['quantity'] = $available;
                    session()->put($this->sessionKey, $cart);
                    $adjusted[] = "Adjusted {$issue['product_name']} quantity to {$available}.";
                }
            }
        }

        return $adjusted;
    }
}
