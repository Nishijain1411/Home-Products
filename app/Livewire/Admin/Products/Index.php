<?php

namespace App\Livewire\Admin\Products;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithFileUploads, WithPagination;

    public $search = '';

    public $showModal = false;

    public $showDeleteModal = false;

    public $editingId = null;

    public $deleteId = null;

    public $name = '';

    public $slug = '';

    public $sku = '';

    public $description = '';

    public $price = '';

    public $compare_at_price = '';

    public $product_type = '';

    public $vendor = '';

    public $is_active = true;

    public $is_featured = false;

    public $sort_order = 0;

    public $seo_title = '';

    public $seo_description = '';

    public $productOptions = [];

    public $selectedCategories = [];

    public $generatedVariants = [];

    public $productImages = [];

    public $newProductImages = [];

    public $existingProductImages = [];

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],

            'slug' => [
                'required',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique('products', 'slug')->ignore($this->editingId),
            ],

            'sku' => [
                'required',
                'string',
                'max:255',
                Rule::unique('products', 'sku')->ignore($this->editingId),
            ],

            'description' => ['nullable', 'string'],

            'price' => [
                'required',
                'numeric',
                'min:0',
            ],

            'compare_at_price' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'product_type' => [
                'nullable',
                'string',
                'max:255',
            ],

            'vendor' => [
                'nullable',
                'string',
                'max:255',
            ],

            'is_active' => ['boolean'],

            'is_featured' => ['boolean'],

            'sort_order' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'seo_title' => [
                'nullable',
                'string',
                'max:255',
            ],

            'seo_description' => [
                'nullable',
                'string',
                'max:500',
            ],

            'selectedCategories' => ['array'],

            'selectedCategories.*' => [
                'integer',
                'exists:categories,id',
            ],

            'productOptions' => ['array'],
            'productOptions.*.name' => ['required', 'string', 'max:255'],
            'productOptions.*.values' => ['array', 'min:1'],
            'productOptions.*.values.*' => ['required', 'string', 'max:255'],

            'generatedVariants' => ['array'],
            'generatedVariants.*.title' => ['required', 'string', 'max:255'],
            'generatedVariants.*.sku' => ['nullable', 'string', 'max:255'],
            'generatedVariants.*.price' => ['nullable', 'numeric', 'min:0'],
            'generatedVariants.*.compare_at_price' => ['nullable', 'numeric', 'min:0'],
            'generatedVariants.*.quantity' => ['nullable', 'integer', 'min:0'],
            'generatedVariants.*.barcode' => ['nullable', 'string', 'max:255'],
            'generatedVariants.*.is_active' => ['boolean'],

            'productImages.*' => [
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],

            'newProductImages.*' => [
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
        ];
    }

    public function render()
    {
        $products = Product::query()
            ->when($this->search, function ($query) {
                $query->where(function ($query) {
                    $query
                        ->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('sku', 'like', '%'.$this->search.'%')
                        ->orWhere('slug', 'like', '%'.$this->search.'%');
                });
            })
            ->with(['categories', 'images', 'variants.inventory'])
            ->latest()
            ->paginate(15);

        $categories = Category::query()
            ->where('is_active', true)
            ->whereNull('parent_id')
            ->with([
                'children' => function ($query) {
                    $query->where('is_active', true)
                        ->orderBy('name');
                },
            ])
            ->orderBy('name')
            ->get();

        return view(
            'livewire.admin.products.index',
            compact('products', 'categories')
        );
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEdit($id): void
    {
        $product = Product::with([
            'categories',
            'options.values',
            'variants.optionValues',
            'variants.inventory',
            'images',
        ])->findOrFail($id);

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

        $this->selectedCategories = $product
            ->categories
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

        $this->showModal = true;
    }

    public function save(): void
    {
        $validated = $this->validate();

        DB::transaction(function () use ($validated) {
            $selectedCategories = $validated['selectedCategories'] ?? [];

            $productData = [
                'name' => $validated['name'],
                'slug' => $validated['slug'],
                'sku' => $validated['sku'],
                'description' => $validated['description'] ?? null,
                'price' => (float) $validated['price'],
                'compare_at_price' => ! empty($validated['compare_at_price']) ? (float) $validated['compare_at_price'] : null,
                'product_type' => $validated['product_type'] ?? null,
                'vendor' => $validated['vendor'] ?? null,
                'is_active' => (bool) ($validated['is_active'] ?? true),
                'is_featured' => (bool) ($validated['is_featured'] ?? false),
                'sort_order' => (int) ($validated['sort_order'] ?? 0),
                'seo_title' => $validated['seo_title'] ?? null,
                'seo_description' => $validated['seo_description'] ?? null,
            ];

            if ($this->editingId) {
                $product = Product::findOrFail($this->editingId);
                $product->update($productData);
            } else {
                $product = Product::create($productData);
            }

            /*
             * Save categories
             */
            $product->categories()->sync($selectedCategories);

            /*
             * Save options and option values
             */
            $optionValueMap = $this->saveProductOptions($product);

            /*
             * Products without options still require a default variant.
             */
            if (empty($this->generatedVariants)) {
                $this->generateVariants();
            }

            /*
             * Save variants and inventory.
             */
            $this->saveProductVariants($product, $optionValueMap);

            /*
             * Save product images.
             */
            $this->saveProductImages($product);
        });

        session()->flash(
            'success',
            $this->editingId
                ? 'Product updated successfully.'
                : 'Product created successfully.'
        );

        $this->closeModal();
    }

    private function saveProductOptions(Product $product): array
    {
        $product->options()->delete();

        $optionValueMap = [];

        foreach ($this->productOptions as $optionIndex => $optionData) {
            $name = trim($optionData['name'] ?? '');
            if ($name === '') {
                continue;
            }

            $option = $product->options()->create([
                'name' => $name,
                'position' => $optionIndex,
            ]);

            foreach ($optionData['values'] as $valueIndex => $value) {
                $trimmedVal = trim($value);
                if ($trimmedVal === '') {
                    continue;
                }

                $optionValue = $option->values()->create([
                    'value' => $trimmedVal,
                    'position' => $valueIndex,
                ]);

                $optionValueMap[$optionIndex][$valueIndex] = $optionValue->id;
                $optionValueMap['by_text'][$name][$trimmedVal] = $optionValue->id;
            }
        }

        return $optionValueMap;
    }

    public function toggleStatus($id): void
    {
        $product = Product::findOrFail($id);

        $product->update([
            'is_active' => ! $product->is_active,
        ]);
    }

    public function confirmDelete($id): void
    {
        $this->deleteId = $id;

        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        $product = Product::findOrFail($this->deleteId);

        $product->categories()->detach();

        // Delete associated image files
        foreach ($product->images as $image) {
            if (Storage::disk('public')->exists($image->path)) {
                Storage::disk('public')->delete($image->path);
            }
        }

        $product->delete();

        session()->flash(
            'success',
            'Product deleted successfully.'
        );

        $this->closeDeleteModal();
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->deleteId = null;
    }

    public function closeModal(): void
    {
        $this->showModal = false;

        $this->resetForm();
        $this->productOptions = [];
        $this->resetValidation();
    }

    private function resetForm(): void
    {
        $this->reset([
            'editingId',
            'name',
            'slug',
            'sku',
            'description',
            'price',
            'compare_at_price',
            'product_type',
            'vendor',
            'seo_title',
            'seo_description',
            'selectedCategories',
            'productOptions',
            'generatedVariants',
            'productImages',
            'newProductImages',
            'existingProductImages',
        ]);

        $this->is_active = true;
        $this->is_featured = false;
        $this->sort_order = 0;
    }

    public function updatedName($value): void
    {
        if (! $this->editingId) {
            $this->slug = Str::slug($value);
        }
    }

    public function addOption(): void
    {
        $this->productOptions[] = [
            'name' => '',
            'values' => [''],
        ];
    }

    public function removeOption($optionIndex): void
    {
        unset($this->productOptions[$optionIndex]);

        $this->productOptions = array_values($this->productOptions);
    }

    public function addOptionValue($optionIndex): void
    {
        $this->productOptions[$optionIndex]['values'][] = '';
    }

    public function removeOptionValue($optionIndex, $valueIndex): void
    {
        unset($this->productOptions[$optionIndex]['values'][$valueIndex]);

        $this->productOptions[$optionIndex]['values'] =
            array_values($this->productOptions[$optionIndex]['values']);
    }

    public function generateVariants(): void
    {
        $options = collect($this->productOptions)
            ->map(function ($option, $optionIndex) {
                return collect($option['values'] ?? [])
                    ->filter(fn ($value) => trim($value) !== '')
                    ->map(function ($value, $valueIndex) use ($optionIndex, $option) {
                        return [
                            'option_index' => $optionIndex,
                            'value_index' => $valueIndex,
                            'option_name' => $option['name'],
                            'value' => trim($value),
                        ];
                    })
                    ->values()
                    ->toArray();
            })
            ->filter(fn ($values) => count($values) > 0)
            ->values()
            ->toArray();

        $existingByTitle = collect($this->generatedVariants)->keyBy('title');

        if (empty($options)) {
            $existingDefault = $existingByTitle->get('Default Variant') ?? collect($this->generatedVariants)->first();

            $this->generatedVariants = [
                [
                    'id' => $existingDefault['id'] ?? null,
                    'title' => 'Default Variant',
                    'option_values' => [],
                    'sku' => ! empty($existingDefault['sku']) ? $existingDefault['sku'] : ($this->sku ?: ''),
                    'price' => ! empty($existingDefault['price']) ? $existingDefault['price'] : $this->price,
                    'compare_at_price' => ! empty($existingDefault['compare_at_price']) ? $existingDefault['compare_at_price'] : $this->compare_at_price,
                    'barcode' => $existingDefault['barcode'] ?? '',
                    'is_active' => $existingDefault['is_active'] ?? true,
                    'quantity' => $existingDefault['quantity'] ?? 0,
                    'reserved_quantity' => $existingDefault['reserved_quantity'] ?? 0,
                ],
            ];

            return;
        }

        $combinations = [[]];

        foreach ($options as $optionValues) {
            $newCombinations = [];

            foreach ($combinations as $combination) {
                foreach ($optionValues as $value) {
                    $newCombinations[] = [
                        ...$combination,
                        $value,
                    ];
                }
            }

            $combinations = $newCombinations;
        }

        $this->generatedVariants = collect($combinations)
            ->map(function ($combination) use ($existingByTitle) {
                $title = collect($combination)
                    ->pluck('value')
                    ->implode(' / ');

                $existing = $existingByTitle->get($title);

                return [
                    'id' => $existing['id'] ?? null,
                    'title' => $title,
                    'option_values' => $combination,
                    'sku' => $existing['sku'] ?? '',
                    'price' => ! empty($existing['price']) ? $existing['price'] : $this->price,
                    'compare_at_price' => ! empty($existing['compare_at_price']) ? $existing['compare_at_price'] : $this->compare_at_price,
                    'barcode' => $existing['barcode'] ?? '',
                    'is_active' => $existing['is_active'] ?? true,
                    'quantity' => $existing['quantity'] ?? 0,
                    'reserved_quantity' => $existing['reserved_quantity'] ?? 0,
                ];
            })
            ->toArray();
    }

    public function removeProductImage($index): void
    {
        unset($this->productImages[$index]);

        $this->productImages = array_values($this->productImages);
    }

    public function removeExistingProductImage($imageId): void
    {
        $image = ProductImage::where('id', $imageId)
            ->where('product_id', $this->editingId)
            ->firstOrFail();

        $wasPrimary = (bool) $image->is_primary;

        if (Storage::disk('public')->exists($image->path)) {
            Storage::disk('public')->delete($image->path);
        }

        $image->delete();

        if ($wasPrimary) {
            $firstRemaining = ProductImage::where('product_id', $this->editingId)
                ->orderBy('position')
                ->first();
            $firstRemaining?->update(['is_primary' => true]);
        }

        $this->refreshExistingProductImages();
    }

    public function updatedNewProductImages(): void
    {
        $this->validate([
            'newProductImages.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        foreach ($this->newProductImages as $image) {
            $this->productImages[] = $image;
        }

        $this->newProductImages = [];
    }

    public function setPrimaryImage($imageId): void
    {
        $image = ProductImage::where('id', $imageId)
            ->where('product_id', $this->editingId)
            ->firstOrFail();

        ProductImage::where('product_id', $image->product_id)
            ->update(['is_primary' => false]);

        $image->update([
            'is_primary' => true,
        ]);

        $this->refreshExistingProductImages();
    }

    public function moveImageUp($imageId): void
    {
        $image = ProductImage::where('id', $imageId)
            ->where('product_id', $this->editingId)
            ->firstOrFail();

        $previousImage = ProductImage::where('product_id', $this->editingId)
            ->where('position', '<', $image->position)
            ->orderByDesc('position')
            ->first();

        if (! $previousImage) {
            return;
        }

        $currentPosition = $image->position;

        $image->update([
            'position' => $previousImage->position,
        ]);

        $previousImage->update([
            'position' => $currentPosition,
        ]);

        $this->refreshExistingProductImages();
    }

    public function moveImageDown($imageId): void
    {
        $image = ProductImage::where('id', $imageId)
            ->where('product_id', $this->editingId)
            ->firstOrFail();

        $nextImage = ProductImage::where('product_id', $this->editingId)
            ->where('position', '>', $image->position)
            ->orderBy('position')
            ->first();

        if (! $nextImage) {
            return;
        }

        $currentPosition = $image->position;

        $image->update([
            'position' => $nextImage->position,
        ]);

        $nextImage->update([
            'position' => $currentPosition,
        ]);

        $this->refreshExistingProductImages();
    }

    private function refreshExistingProductImages(): void
    {
        $this->existingProductImages = ProductImage::where('product_id', $this->editingId)
            ->orderBy('position')
            ->get()
            ->toArray();
    }

    private function saveProductVariants(Product $product, array $optionValueMap): void
    {
        $existingVariants = $product->variants()->with('inventory')->get()->keyBy('id');
        $existingByTitle = $product->variants()->with('inventory')->get()->keyBy('title');
        $persistedVariantIds = [];

        foreach ($this->generatedVariants as $variantData) {
            $title = trim($variantData['title'] ?? '');
            if ($title === '') {
                continue;
            }

            $targetVariant = null;
            if (! empty($variantData['id']) && $existingVariants->has($variantData['id'])) {
                $targetVariant = $existingVariants->get($variantData['id']);
            } elseif ($existingByTitle->has($title)) {
                $targetVariant = $existingByTitle->get($title);
            }

            $sku = ! empty(trim($variantData['sku'] ?? ''))
                ? trim($variantData['sku'])
                : ($targetVariant?->sku ?: $this->generateVariantSku($product, $title));

            $price = isset($variantData['price']) && $variantData['price'] !== '' && (float) $variantData['price'] >= 0
                ? (float) $variantData['price']
                : null;

            $compareAtPrice = isset($variantData['compare_at_price']) && $variantData['compare_at_price'] !== '' && (float) $variantData['compare_at_price'] >= 0
                ? (float) $variantData['compare_at_price']
                : null;

            $barcode = ! empty(trim($variantData['barcode'] ?? ''))
                ? trim($variantData['barcode'])
                : null;

            $isActive = isset($variantData['is_active']) ? (bool) $variantData['is_active'] : true;

            $payload = [
                'title' => $title,
                'sku' => $sku,
                'price' => $price,
                'compare_at_price' => $compareAtPrice,
                'barcode' => $barcode,
                'is_active' => $isActive,
            ];

            if ($targetVariant) {
                $targetVariant->update($payload);
                $variant = $targetVariant;
            } else {
                $variant = $product->variants()->create($payload);
            }

            // Sync option values
            $optionValueIds = [];
            foreach ($variantData['option_values'] ?? [] as $ov) {
                $optIndex = $ov['option_index'] ?? null;
                $valIndex = $ov['value_index'] ?? null;
                if ($optIndex !== null && $valIndex !== null && isset($optionValueMap[$optIndex][$valIndex])) {
                    $optionValueIds[] = $optionValueMap[$optIndex][$valIndex];
                } elseif (! empty($ov['option_name']) && ! empty($ov['value']) && isset($optionValueMap['by_text'][$ov['option_name']][$ov['value']])) {
                    $optionValueIds[] = $optionValueMap['by_text'][$ov['option_name']][$ov['value']];
                }
            }

            if (empty($optionValueIds) && $product->options()->count() > 0) {
                $parts = array_map('trim', explode('/', $title));
                foreach ($product->options()->with('values')->get() as $opt) {
                    $matchingVal = $opt->values->first(fn ($v) => in_array($v->value, $parts));
                    if ($matchingVal) {
                        $optionValueIds[] = $matchingVal->id;
                    }
                }
            }

            $variant->optionValues()->sync(array_values(array_unique($optionValueIds)));

            // Manage inventory
            $targetQty = max(0, (int) ($variantData['quantity'] ?? 0));
            $inventory = $variant->inventory;

            if ($inventory) {
                $inventory->update([
                    'quantity' => $targetQty,
                ]);
            } else {
                $variant->inventory()->create([
                    'quantity' => $targetQty,
                    'reserved_quantity' => 0,
                ]);
            }

            $persistedVariantIds[] = $variant->id;
        }

        if (! empty($persistedVariantIds)) {
            $product->variants()->whereNotIn('id', $persistedVariantIds)->delete();
        }
    }

    private function generateVariantSku(Product $product, string $title): string
    {
        $baseSku = $product->sku ?: 'PROD-'.$product->id;
        $slug = strtoupper(Str::slug($title, '-'));
        $candidate = $baseSku.($slug ? '-'.$slug : '-VAR');

        $count = 1;
        while (ProductVariant::where('sku', $candidate)->where('product_id', '!=', $product->id)->exists()) {
            $candidate = $baseSku.($slug ? '-'.$slug : '-VAR').'-'.$count;
            $count++;
        }

        return $candidate;
    }

    private function saveProductImages(Product $product): void
    {
        if (empty($this->productImages)) {
            return;
        }

        $existingCount = $product->images()->count();
        $hasPrimary = $product->images()->where('is_primary', true)->exists();

        foreach ($this->productImages as $index => $image) {
            $path = $image->store(
                'products/'.$product->id,
                'public'
            );

            $product->images()->create([
                'path' => $path,
                'alt_text' => $product->name,
                'position' => $existingCount + $index,
                'is_primary' => (! $hasPrimary && $index === 0),
            ]);
        }

        $this->productImages = [];
        $this->newProductImages = [];
    }
}
