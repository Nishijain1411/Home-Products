<?php

namespace App\Livewire\Admin\Products;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
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
    use WithFileUploads , WithPagination;

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

    protected function rules()
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

            'productImages.*' => [
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
            ->with('categories')
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

    public function openCreate()
    {
        $this->resetForm();

        $this->showModal = true;
    }

    public function openEdit($id)
    {
        $product = Product::with('categories')->findOrFail($id);

        $this->editingId = $product->id;

        $this->name = $product->name;
        $this->slug = $product->slug;
        $this->sku = $product->sku;
        $this->description = $product->description;
        $this->price = $product->price;
        $this->compare_at_price = $product->compare_at_price;
        $this->product_type = $product->product_type;
        $this->vendor = $product->vendor;
        $this->is_active = $product->is_active;
        $this->is_featured = $product->is_featured;
        $this->sort_order = $product->sort_order;
        $this->seo_title = $product->seo_title;
        $this->seo_description = $product->seo_description;

        $this->productOptions = $product->options()
            ->with('values')
            ->orderBy('position')
            ->get()
            ->map(function ($option) {
                return [
                    'name' => $option->name,
                    'values' => $option->values
                        ->sortBy('position')
                        ->pluck('value')
                        ->values()
                        ->toArray(),
                ];
            })
            ->toArray();
        $this->selectedCategories = $product
            ->categories
            ->pluck('id')
            ->toArray();
        $this->existingProductImages = $product->images()
            ->orderBy('position')
            ->get()
            ->toArray();

        $this->showModal = true;
    }

    public function save()
    {
        $validated = $this->validate();

        DB::transaction(function () use ($validated) {

            $selectedCategories = $validated['selectedCategories'] ?? [];

            unset($validated['selectedCategories']);
            unset($validated['productOptions']);

            if ($this->editingId) {

                $product = Product::findOrFail($this->editingId);

                $product->update($validated);

            } else {

                $product = Product::create($validated);
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
            $this->saveProductVariants(
                $product,
                $optionValueMap
            ); /*
            * Products without options still require a default variant.
            */
            if (empty($this->generatedVariants)) {
                $this->generateVariants();
            }

            /*
            * Save variants and inventory.
            */
            $this->saveProductVariants(
                $product,
                $optionValueMap
            );
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

    private function saveProductOptions(Product $product)
    {
        $product->options()->delete();

        $optionValueMap = [];

        foreach ($this->productOptions as $optionIndex => $optionData) {

            if (empty(trim($optionData['name'] ?? ''))) {
                continue;
            }

            $option = $product->options()->create([
                'name' => trim($optionData['name']),
                'position' => $optionIndex,
            ]);

            foreach ($optionData['values'] as $valueIndex => $value) {

                if (empty(trim($value))) {
                    continue;
                }

                $optionValue = $option->values()->create([
                    'value' => trim($value),
                    'position' => $valueIndex,
                ]);

                $optionValueMap[$optionIndex][$valueIndex] = $optionValue->id;
            }
        }

        return $optionValueMap;
    }

    public function toggleStatus($id)
    {
        $product = Product::findOrFail($id);

        $product->update([
            'is_active' => ! $product->is_active,
        ]);
    }

    public function confirmDelete($id)
    {
        $this->deleteId = $id;

        $this->showDeleteModal = true;
    }

    public function delete()
    {
        $product = Product::findOrFail($this->deleteId);

        $product->categories()->detach();

        $product->delete();

        session()->flash(
            'success',
            'Product deleted successfully.'
        );

        $this->closeDeleteModal();
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->deleteId = null;
    }

    public function closeModal()
    {
        $this->showModal = false;

        $this->resetForm();
        $this->productOptions = [];
        $this->resetValidation();
    }

    private function resetForm()
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
            'existingProductImages',
        ]);

        $this->is_active = true;
        $this->is_featured = false;
        $this->sort_order = 0;
    }

    public function updatedName($value)
    {
        if (! $this->editingId) {
            $this->slug = Str::slug($value);
        }
    }

    public function addOption()
    {
        $this->productOptions[] = [
            'name' => '',
            'values' => [''],
        ];
    }

    public function removeOption($optionIndex)
    {
        unset($this->productOptions[$optionIndex]);

        $this->productOptions = array_values($this->productOptions);
    }

    public function addOptionValue($optionIndex)
    {
        $this->productOptions[$optionIndex]['values'][] = '';
    }

    public function removeOptionValue($optionIndex, $valueIndex)
    {
        unset($this->productOptions[$optionIndex]['values'][$valueIndex]);

        $this->productOptions[$optionIndex]['values'] =
            array_values($this->productOptions[$optionIndex]['values']);
    }

    public function generateVariants()
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

        if (empty($options)) {
            $this->generatedVariants = [
                [
                    'title' => 'Default Variant',
                    'option_values' => [],
                    'sku' => '',
                    'price' => $this->price,
                    'compare_at_price' => $this->compare_at_price,
                    'barcode' => '',
                    'is_active' => true,
                    'quantity' => 0,
                    'reserved_quantity' => 0,
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
            ->map(function ($combination) {

                $title = collect($combination)
                    ->pluck('value')
                    ->implode(' / ');

                return [
                    'title' => $title,
                    'option_values' => $combination,
                    'sku' => '',
                    'price' => $this->price,
                    'compare_at_price' => $this->compare_at_price,
                    'barcode' => '',
                    'is_active' => true,
                    'quantity' => 0,
                    'reserved_quantity' => 0,
                ];
            })
            ->toArray();
    }

    public function removeProductImage($index)
    {
        unset($this->productImages[$index]);

        $this->productImages = array_values($this->productImages);
    }

    public function removeExistingProductImage($imageId)
    {
        $image = ProductImage::where('id', $imageId)
            ->where('product_id', $this->editingId)
            ->firstOrFail();

        if (Storage::disk('public')->exists($image->path)) {
            Storage::disk('public')->delete($image->path);
        }

        $image->delete();

        $this->existingProductImages = ProductImage::where('product_id', $this->editingId)
            ->orderBy('position')
            ->get()
            ->toArray();
    }

    public function updatedNewProductImages()
    {
        foreach ($this->newProductImages as $image) {
            $this->productImages[] = $image;
        }

        $this->newProductImages = [];
    }

    public function setPrimaryImage($imageId)
    {
        $image = ProductImage::findOrFail($imageId);

        ProductImage::where('product_id', $image->product_id)
            ->update(['is_primary' => false]);

        $image->update([
            'is_primary' => true,
        ]);

        $this->existingProductImages = ProductImage::where('product_id', $image->product_id)
            ->orderBy('position')
            ->get()
            ->toArray();
    }

    public function moveImageUp($imageId)
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

    public function moveImageDown($imageId)
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

    private function refreshExistingProductImages()
    {
        $this->existingProductImages = ProductImage::where('product_id', $this->editingId)
            ->orderBy('position')
            ->get()
            ->toArray();
    }

    private function saveProductVariants(Product $product, array $optionValueMap)
    {
        /*
        * Variants are recreated whenever the product is saved.
        */
        $product->variants()->delete();

        foreach ($this->generatedVariants as $variantData) {

            $variant = $product->variants()->create([
                'title' => $variantData['title'],

                'sku' => ! empty(trim($variantData['sku'] ?? ''))
                    ? trim($variantData['sku'])
                    : $this->generateVariantSku(
                        $product,
                        $variantData['title']
                    ),

                'price' => $variantData['price'] ?: null,

                'compare_at_price' => $variantData['compare_at_price'] ?: null,

                'barcode' => ! empty(trim($variantData['barcode'] ?? ''))
                    ? trim($variantData['barcode'])
                    : null,

                'is_active' => $variantData['is_active'] ?? true,
            ]);

            /*
            * Connect option values to this variant.
            */
            $optionValueIds = [];

            foreach ($variantData['option_values'] ?? [] as $optionValue) {

                $optionIndex = $optionValue['option_index'] ?? null;
                $valueIndex = $optionValue['value_index'] ?? null;

                if (
                    $optionIndex !== null &&
                    $valueIndex !== null &&
                    isset($optionValueMap[$optionIndex][$valueIndex])
                ) {
                    $optionValueIds[] =
                        $optionValueMap[$optionIndex][$valueIndex];
                }
            }

            /*
            * Sync variant <-> option value relationship.
            */
            $variant->optionValues()->sync(
                array_values(array_unique($optionValueIds))
            );

            /*
            * Create inventory record.
            */
            $variant->inventory()->create([
                'quantity' => max(
                    0,
                    (int) ($variantData['quantity'] ?? 0)
                ),

                'reserved_quantity' => max(
                    0,
                    (int) ($variantData['reserved_quantity'] ?? 0)
                ),
            ]);
        }
    }

    private function generateVariantSku(Product $product, string $title)
    {
        return $product->sku.'-'.strtoupper(
            Str::slug($title, '-')
        );
    }

    private function saveProductImages(Product $product)
    {
        if (empty($this->productImages)) {
            return;
        }

        $existingCount = $product->images()->count();

        foreach ($this->productImages as $index => $image) {
            $path = $image->store(
                'products/'.$product->id,
                'public'
            );

            $product->images()->create([
                'path' => $path,
                'alt_text' => $product->name,
                'position' => $existingCount + $index,
                'is_primary' => ($existingCount + $index) === 0,
            ]);
        }
    }
}
