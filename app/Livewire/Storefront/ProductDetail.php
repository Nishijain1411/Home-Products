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

    public function addToCart(CartService $cartService): void
    {
        if (! $this->selectedVariant) {
            session()->flash('cart_error', 'Please select an available variant combination.');

            return;
        }

        $inventory = $this->selectedVariant->inventory;
        $availableQuantity = $inventory?->quantity ?? 0;

        if ($availableQuantity < $this->quantity) {
            session()->flash('cart_error', 'Sorry, the requested quantity is not available in stock.');

            return;
        }

        $cartService->add(
            $this->selectedVariant,
            $this->quantity
        );

        $this->dispatch('cart-updated');

        session()->flash(
            'cart_success',
            "Added {$this->quantity} × {$this->product->name} to your cart!"
        );
    }

    public function render()
    {
        $categoryIds = $this->product->categories->pluck('id');

        $relatedProducts = Product::query()
            ->where('is_active', true)
            ->where('id', '!=', $this->product->id)
            ->when($categoryIds->isNotEmpty(), function ($query) use ($categoryIds) {
                $query->whereHas('categories', fn ($q) => $q->whereIn('categories.id', $categoryIds));
            })
            ->with(['images' => fn ($q) => $q->orderBy('position')])
            ->latest('id')
            ->take(4)
            ->get();

        return view('livewire.storefront.product-detail', [
            'relatedProducts' => $relatedProducts,
        ])->layout('storefront.layouts.app');
    }
}
