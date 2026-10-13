<?php

namespace App\Services;

use App\Models\ProductVariant;

class CartService
{
    protected string $sessionKey = 'cart';

    public function get(): array
    {
        $cart = session()->get($this->sessionKey, []);

        foreach ($cart as $variantId => &$item) {

            if (! isset($item['product_name'])) {
                $variant = ProductVariant::with('product.images')
                    ->find($item['variant_id'] ?? $variantId);

                if ($variant) {
                    $image = $variant->product->images()
                        ->orderByDesc('is_primary')
                        ->orderBy('position')
                        ->first();

                    $item['product_name'] = $variant->product->name;
                    $item['image'] = $image?->path;
                }
            }

            $item['product_name'] ??= 'Product';
            $item['image'] ??= null;
        }

        unset($item);

        session()->put($this->sessionKey, $cart);

        return $cart;
    }

    public function add(ProductVariant $variant, int $quantity = 1): void
    {
        $cart = $this->get();

        $variantId = (string) $variant->id;

        $existingQuantity = $cart[$variantId]['quantity'] ?? 0;

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
            'quantity' => $existingQuantity + $quantity,
            'price' => (float) $variant->price,
            'image' => $image?->path,
        ];

        session()->put($this->sessionKey, $cart);
    }

    public function updateQuantity(int $variantId, int $quantity): void
    {
        $cart = $this->get();

        if (! isset($cart[$variantId])) {
            return;
        }

        if ($quantity <= 0) {
            unset($cart[$variantId]);
        } else {
            $cart[$variantId]['quantity'] = $quantity;
        }

        session()->put($this->sessionKey, $cart);
    }

    public function remove(int $variantId): void
    {
        $cart = $this->get();

        unset($cart[$variantId]);

        session()->put($this->sessionKey, $cart);
    }

    public function count(): int
    {
        return collect($this->get())
            ->sum('quantity');
    }

    public function subtotal(): float
    {
        return collect($this->get())
            ->sum(function ($item) {
                return $item['price'] * $item['quantity'];
            });
    }

    public function clear(): void
    {
        session()->forget($this->sessionKey);
    }
}
