<?php

namespace App\Livewire\Admin\Products;

use App\Models\Product;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Edit extends Create
{
    public Product $product;

    public function mount(Product $product): void
    {
        $this->product = $product->load([
            'categories',
            'options.values',
            'variants.optionValues.option',
            'variants.inventory',
            'images',
        ]);

        $this->editingId = $product->id;
        $this->name = $product->name;
        $this->slug = $product->slug;
        $this->sku = $product->sku;
        $this->description = $product->description ?? '';
        $this->price = (string) $product->price;
        $this->compare_at_price = $product->compare_at_price !== null ? (string) $product->compare_at_price : '';
        $this->product_type = $product->product_type ?? '';
        $this->vendor = $product->vendor ?? '';
        $this->is_active = (bool) $product->is_active;
        $this->is_featured = (bool) $product->is_featured;
        $this->sort_order = (int) $product->sort_order;
        $this->seo_title = $product->seo_title ?? '';
        $this->seo_description = $product->seo_description ?? '';

        $this->selectedCategories = $product->categories
            ->pluck('id')
            ->toArray();

        $this->productOptions = $product->options
            ->sortBy('position')
            ->map(function ($option) {
                return [
                    'id' => $option->id,
                    'name' => $option->name,
                    'values' => $option->values
                        ->sortBy('position')
                        ->pluck('value')
                        ->values()
                        ->toArray(),
                ];
            })
            ->values()
            ->toArray();

        $this->generatedVariants = $product->variants
            ->map(function ($variant) {
                return [
                    'id' => $variant->id,
                    'title' => $variant->title,
                    'sku' => $variant->sku,
                    'price' => $variant->price !== null ? (float) $variant->price : '',
                    'compare_at_price' => $variant->compare_at_price !== null ? (float) $variant->compare_at_price : '',
                    'barcode' => $variant->barcode ?? '',
                    'is_active' => (bool) $variant->is_active,
                    'quantity' => (int) ($variant->inventory?->quantity ?? 0),
                    'reserved_quantity' => (int) ($variant->inventory?->reserved_quantity ?? 0),
                    'option_values' => $variant->optionValues->map(function ($ov) {
                        return [
                            'option_name' => $ov->option?->name ?? '',
                            'value' => $ov->value,
                        ];
                    })->toArray(),
                ];
            })
            ->values()
            ->toArray();

        $this->existingProductImages = $product->images
            ->sortBy('position')
            ->values()
            ->toArray();

        $this->productImages = [];
        $this->newProductImages = [];
    }
}
