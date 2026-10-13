<?php

namespace App\Livewire\Storefront;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CartService;
use Livewire\Component;

class ProductDetail extends Component
{
    public Product $product;

    public ?int $selectedImage = null;

    public int $quantity = 1;

    public array $selectedOptions = [];

    public ?ProductVariant $selectedVariant = null;

    public function mount(Product $product): void
    {
        abort_unless($product->is_active, 404);

        $this->product = $product->load([
            'images' => function ($query) {
                $query->orderBy('position');
            },
            'categories',
            'options.values',
            'variants.optionValues',
            'variants.inventory',
        ]);

        $this->selectedImage = $this->product->images->first()?->id;

        /* Select first value of every option by default */

        foreach ($this->product->options as $option) {
            $firstValue = $option->values->first();

            if ($firstValue) {
                $this->selectedOptions[$option->id] = $firstValue->id;
            }
        }

        $this->findSelectedVariant();
    }

    public function selectImage(int $imageId): void
    {
        $this->selectedImage = $imageId;
    }

    public function selectOption(int $optionId, int $valueId): void
    {
        $this->selectedOptions[$optionId] = $valueId;

        $this->quantity = 1;

        $this->findSelectedVariant();
    }

    protected function findSelectedVariant(): void
    {
        if ($this->product->options->isEmpty()) {
            $this->selectedVariant = $this->product->variants->first();

            return;
        }

        if (count($this->selectedOptions) !== $this->product->options->count()) {
            $this->selectedVariant = null;

            return;
        }

        $selectedValueIds = collect($this->selectedOptions)
            ->map(fn ($id) => (int) $id)
            ->sort()
            ->values()
            ->all();

        $this->selectedVariant = $this->product->variants
            ->first(function ($variant) use ($selectedValueIds) {

                $variantValueIds = $variant->optionValues
                    ->pluck('id')
                    ->map(fn ($id) => (int) $id)
                    ->sort()
                    ->values()
                    ->all();

                return $variantValueIds === $selectedValueIds;
            });
    }

    public function incrementQuantity(): void
    {
        if (! $this->selectedVariant) {
            return;
        }

        $availableQuantity = $this->selectedVariant->inventory?->quantity ?? 0;

        if ($this->quantity < $availableQuantity) {
            $this->quantity++;
        }
    }

    public function decrementQuantity(): void
    {
        if ($this->quantity > 1) {
            $this->quantity--;
        }
    }

    public function render()
    {
        return view('livewire.storefront.product-detail')
            ->layout('storefront.layouts.app');
    }

    public function addToCart(CartService $cartService): void
    {
        if (! $this->selectedVariant) {
            return;
        }

        $inventory = $this->selectedVariant->inventory;

        if (! $inventory || $inventory->quantity < $this->quantity) {
            return;
        }

        $cartService->add(
            $this->selectedVariant,
            $this->quantity
        );

        session()->flash(
            'cart_success',
            'Product added to cart successfully.'
        );
    }
}
