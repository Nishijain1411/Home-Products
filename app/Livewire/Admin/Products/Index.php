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
use Symfony\Component\HttpFoundation\StreamedResponse;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithFileUploads, WithPagination;

    public $search = '';

    public string $statusFilter = '';

    public string $categoryFilter = '';

    public string $stockFilter = '';

    public string $featuredFilter = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'categoryFilter' => ['except' => ''],
        'stockFilter' => ['except' => ''],
        'featuredFilter' => ['except' => ''],
    ];

    public $showModal = false;

    public $showDeleteModal = false;

    public $showBulkDeleteModal = false;

    public bool $showBulkCategoryModal = false;

    public array $bulkCategoryIds = [];

    public string $bulkCategoryMode = 'append';

    public string $bulkStatusMode = 'keep';

    public array $selected = [];

    public bool $selectAll = false;

    public $showImportModal = false;

    public $importFile = null;

    public array $importErrors = [];

    public ?string $successMessage = null;

    public ?string $errorMessage = null;

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

    public function buildFilteredProductsQuery()
    {
        return Product::query()
            ->when($this->search, function ($query) {
                $query->where(function ($query) {
                    $query
                        ->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('sku', 'like', '%'.$this->search.'%')
                        ->orWhere('slug', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->statusFilter !== '', function ($query) {
                $query->where('is_active', $this->statusFilter === 'active');
            })
            ->when($this->categoryFilter !== '', function ($query) {
                $query->whereHas('categories', function ($q) {
                    $q->where('categories.id', $this->categoryFilter);
                });
            })
            ->when($this->stockFilter === 'in_stock', function ($query) {
                $query->whereHas('variants.inventory', function ($q) {
                    $q->where('quantity', '>', 0);
                });
            })
            ->when($this->stockFilter === 'out_of_stock', function ($query) {
                $query->whereDoesntHave('variants.inventory', function ($q) {
                    $q->where('quantity', '>', 0);
                });
            })
            ->when($this->featuredFilter !== '', function ($query) {
                $query->where('is_featured', $this->featuredFilter === 'featured');
            });
    }

    public function render()
    {
        $products = $this->buildFilteredProductsQuery()
            ->with(['categories', 'images', 'variants.inventory'])
            ->latest()
            ->paginate(15);

        // Keep master selectAll checkbox synchronized with the current page
        $currentPageIds = $products->pluck('id')->map(fn ($id) => (string) $id)->all();
        $this->selectAll = ! empty($currentPageIds) && empty(array_diff($currentPageIds, array_map('strval', $this->selected)));

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

    public function getCurrentPageIdsProperty(): array
    {
        return $this->buildFilteredProductsQuery()
            ->latest()
            ->paginate(15)
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->all();
    }

    public function updatedSelectAll($value): void
    {
        $currentPageIds = $this->currentPageIds;

        if ($value) {
            $this->selected = array_values(array_unique(array_merge(
                array_map('strval', $this->selected),
                $currentPageIds
            )));
        } else {
            $this->selected = array_values(array_diff(
                array_map('strval', $this->selected),
                $currentPageIds
            ));
        }

        $this->syncSelectAllState();
    }

    public function updatedSelected(): void
    {
        $this->syncSelectAllState();
    }

    public function syncSelectAllState(): void
    {
        $currentPageIds = $this->currentPageIds;
        $selectedStrings = array_map('strval', $this->selected);

        $this->selectAll = ! empty($currentPageIds) && empty(array_diff($currentPageIds, $selectedStrings));
    }

    public function clearSelection(): void
    {
        $this->selected = [];
        $this->selectAll = false;
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
        $this->syncSelectAllState();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
        $this->syncSelectAllState();
    }

    public function updatedCategoryFilter(): void
    {
        $this->resetPage();
        $this->syncSelectAllState();
    }

    public function updatedStockFilter(): void
    {
        $this->resetPage();
        $this->syncSelectAllState();
    }

    public function updatedFeaturedFilter(): void
    {
        $this->resetPage();
        $this->syncSelectAllState();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'statusFilter', 'categoryFilter', 'stockFilter', 'featuredFilter']);
        $this->resetPage();
        $this->syncSelectAllState();
    }

    public function getHasActiveFiltersProperty(): bool
    {
        return ! empty($this->search)
            || $this->statusFilter !== ''
            || $this->categoryFilter !== ''
            || $this->stockFilter !== ''
            || $this->featuredFilter !== '';
    }

    public function updatedPage(): void
    {
        $this->syncSelectAllState();
    }

    protected function authorizeBulkAction(): void
    {
        if (! auth()->check()) {
            abort(403, 'Unauthorized. Please log in.');
        }

        $user = auth()->user();

        if (! $user->canAccessAdmin()) {
            abort(403, 'Unauthorized. Access restricted to store administrators.');
        }

        if (method_exists($user, 'hasPermissionTo') && $user->roles()->exists()) {
            if (! $user->hasAnyRole(['Super Administrator', 'Store Administrator', 'Catalogue Manager']) && ! $user->can('products')) {
                abort(403, 'Unauthorized. You do not have permission to manage products.');
            }
        }
    }

    protected function getValidatedSelectedIds(): array
    {
        $this->authorizeBulkAction();

        if (empty($this->selected)) {
            $this->errorMessage = 'No products were selected for this bulk action.';
            session()->flash('error', $this->errorMessage);

            return [];
        }

        $ids = array_values(array_filter(array_map('intval', $this->selected), fn ($id) => $id > 0));

        if (empty($ids)) {
            $this->errorMessage = 'The selected product IDs are invalid.';
            session()->flash('error', $this->errorMessage);
            $this->clearSelection();

            return [];
        }

        return $ids;
    }

    public function bulkActivate(): void
    {
        $ids = $this->getValidatedSelectedIds();
        if (empty($ids)) {
            return;
        }

        $count = Product::whereIn('id', $ids)->update(['is_active' => true]);
        ProductVariant::whereIn('product_id', $ids)->update(['is_active' => true]);

        $message = "{$count} ".Str::plural('product', $count).' activated successfully.';
        $this->errorMessage = null;
        $this->successMessage = $message;
        session()->flash('success', $message);

        $this->clearSelection();
    }

    public function bulkDeactivate(): void
    {
        $ids = $this->getValidatedSelectedIds();
        if (empty($ids)) {
            return;
        }

        $count = Product::whereIn('id', $ids)->update(['is_active' => false]);
        ProductVariant::whereIn('product_id', $ids)->update(['is_active' => false]);

        $message = "{$count} ".Str::plural('product', $count).' deactivated successfully.';
        $this->errorMessage = null;
        $this->successMessage = $message;
        session()->flash('success', $message);

        $this->clearSelection();
    }

    public function openBulkCategoryModal(): void
    {
        $ids = $this->getValidatedSelectedIds();
        if (empty($ids)) {
            return;
        }

        $this->bulkCategoryIds = [];
        $this->bulkCategoryMode = 'append';
        $this->bulkStatusMode = 'keep';
        $this->resetValidation(['bulkCategoryIds', 'bulkCategoryMode', 'bulkStatusMode']);
        $this->showBulkCategoryModal = true;
    }

    public function closeBulkCategoryModal(): void
    {
        $this->showBulkCategoryModal = false;
        $this->bulkCategoryIds = [];
        $this->bulkCategoryMode = 'append';
        $this->bulkStatusMode = 'keep';
        $this->resetValidation(['bulkCategoryIds', 'bulkCategoryMode', 'bulkStatusMode']);
    }

    public function bulkAssignCategory(): void
    {
        $ids = $this->getValidatedSelectedIds();
        if (empty($ids)) {
            $this->closeBulkCategoryModal();

            return;
        }

        $this->validate([
            'bulkCategoryIds' => ['required', 'array', 'min:1'],
            'bulkCategoryIds.*' => ['integer', 'exists:categories,id'],
            'bulkCategoryMode' => ['required', 'string', Rule::in(['append', 'replace'])],
            'bulkStatusMode' => ['required', 'string', Rule::in(['keep', 'active', 'inactive'])],
        ], [
            'bulkCategoryIds.required' => 'Please select at least one category to assign.',
            'bulkCategoryIds.min' => 'Please select at least one category to assign.',
        ]);

        $categoryIds = array_values(array_map('intval', $this->bulkCategoryIds));

        DB::transaction(function () use ($ids, $categoryIds) {
            $products = Product::whereIn('id', $ids)->get();

            foreach ($products as $product) {
                if ($this->bulkCategoryMode === 'replace') {
                    $product->categories()->sync($categoryIds);
                } else {
                    $product->categories()->syncWithoutDetaching($categoryIds);
                }
            }

            if ($this->bulkStatusMode === 'active') {
                Product::whereIn('id', $ids)->update(['is_active' => true]);
                ProductVariant::whereIn('product_id', $ids)->update(['is_active' => true]);
            } elseif ($this->bulkStatusMode === 'inactive') {
                Product::whereIn('id', $ids)->update(['is_active' => false]);
                ProductVariant::whereIn('product_id', $ids)->update(['is_active' => false]);
            }
        });

        $productCount = count($ids);
        $catCount = count($categoryIds);
        $statusSuffix = match ($this->bulkStatusMode) {
            'active' => ' and set to Active',
            'inactive' => ' and set to Inactive',
            default => '',
        };

        $message = "Assigned {$catCount} ".Str::plural('category', $catCount)." to {$productCount} ".Str::plural('product', $productCount)."{$statusSuffix} successfully.";

        $this->errorMessage = null;
        $this->successMessage = $message;
        session()->flash('success', $message);

        $this->closeBulkCategoryModal();
        $this->clearSelection();
    }

    public function confirmBulkDelete(): void
    {
        $ids = $this->getValidatedSelectedIds();
        if (empty($ids)) {
            return;
        }

        $this->showBulkDeleteModal = true;
    }

    public function closeBulkDeleteModal(): void
    {
        $this->showBulkDeleteModal = false;
    }

    public function bulkDelete(): void
    {
        $ids = $this->getValidatedSelectedIds();
        if (empty($ids)) {
            $this->closeBulkDeleteModal();

            return;
        }

        DB::transaction(function () use ($ids) {
            $products = Product::with(['images', 'variants.inventory'])->whereIn('id', $ids)->get();

            $protectedNames = [];
            $deletableProducts = [];

            foreach ($products as $product) {
                $hasActiveHold = false;
                foreach ($product->variants as $variant) {
                    if ($variant->inventory && (int) $variant->inventory->reserved_quantity > 0) {
                        $hasActiveHold = true;
                        break;
                    }
                }

                if ($hasActiveHold) {
                    $protectedNames[] = $product->name;
                } else {
                    $deletableProducts[] = $product;
                }
            }

            if (empty($deletableProducts)) {
                $names = implode(', ', $protectedNames);
                $this->errorMessage = "Cannot delete selected products ({$names}) because they have active order reservations in progress.";
                session()->flash('error', $this->errorMessage);

                return;
            }

            $deletedCount = 0;
            foreach ($deletableProducts as $product) {
                $product->categories()->detach();

                foreach ($product->images as $image) {
                    if (Storage::disk('public')->exists($image->path)) {
                        Storage::disk('public')->delete($image->path);
                    }
                }

                $product->delete();
                $deletedCount++;
            }

            if (! empty($protectedNames)) {
                $skippedNames = implode(', ', $protectedNames);
                $message = "{$deletedCount} ".Str::plural('product', $deletedCount)." deleted successfully. Skipped ({$skippedNames}) due to active unfulfilled orders.";
            } else {
                $message = "{$deletedCount} ".Str::plural('product', $deletedCount).' deleted successfully.';
            }

            $this->errorMessage = null;
            $this->successMessage = $message;
            session()->flash('success', $message);
        });

        $this->closeBulkDeleteModal();
        $this->clearSelection();
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

        $msg = $this->editingId
            ? 'Product updated successfully.'
            : 'Product created successfully.';

        $this->successMessage = $msg;
        session()->flash('success', $msg);

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

        $this->successMessage = 'Product deleted successfully.';
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

    public function openImportModal(): void
    {
        $this->reset(['importFile', 'importErrors', 'successMessage']);
        $this->resetValidation();
        $this->showImportModal = true;
    }

    public function closeImportModal(): void
    {
        $this->showImportModal = false;
        $this->reset(['importFile', 'importErrors']);
        $this->resetValidation();
    }

    public function dismissAlert(): void
    {
        $this->successMessage = null;
        $this->errorMessage = null;
    }

    public function export(): StreamedResponse
    {
        $products = $this->buildFilteredProductsQuery()
            ->with(['categories', 'variants.inventory'])
            ->latest()
            ->get();

        $filename = 'products-export-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($products) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'Name',
                'Slug',
                'SKU',
                'Price',
                'Compare at Price',
                'Stock',
                'Categories',
                'Product Type',
                'Vendor',
                'Is Active',
                'Is Featured',
                'Description',
            ]);

            foreach ($products as $product) {
                $categoryNames = $product->categories->pluck('name')->implode(', ');
                $totalStock = $product->variants->sum(fn ($variant) => (int) ($variant->inventory?->quantity ?? 0));

                fputcsv($handle, [
                    $product->name,
                    $product->slug,
                    $product->sku,
                    $product->price,
                    $product->compare_at_price ?? '',
                    $totalStock,
                    $categoryNames,
                    $product->product_type ?? '',
                    $product->vendor ?? '',
                    $product->is_active ? '1' : '0',
                    $product->is_featured ? '1' : '0',
                    $product->description ?? '',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function exportSample(): StreamedResponse
    {
        $filename = 'products-sample-template.csv';

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'Name',
                'Slug',
                'SKU',
                'Price',
                'Compare at Price',
                'Stock',
                'Categories',
                'Product Type',
                'Vendor',
                'Is Active',
                'Is Featured',
                'Description',
            ]);

            fputcsv($handle, [
                'Wireless Earbuds Pro',
                'wireless-earbuds-pro',
                'EARBUD-PRO-001',
                '1499.00',
                '1999.00',
                '50',
                'Electronics, Audio',
                'Audio',
                'SoundMaster',
                '1',
                '1',
                'Premium noise-cancelling bluetooth wireless earbuds with charging case.',
            ]);

            fputcsv($handle, [
                'Classic Cotton T-Shirt',
                'classic-cotton-tshirt',
                'TSHIRT-BLK-001',
                '499.00',
                '799.00',
                '100',
                'Clothing, Apparel',
                'Apparel',
                'StyleHub',
                '1',
                '0',
                '100% breathable combed organic cotton crewneck casual t-shirt.',
            ]);

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function import(): void
    {
        $this->validate([
            'importFile' => ['required', 'file', 'mimes:csv,txt', 'max:10240'],
        ]);

        $path = $this->importFile->getRealPath();
        if (! $path || ! file_exists($path)) {
            $this->addError('importFile', 'Unable to read the uploaded CSV file.');

            return;
        }

        $handle = fopen($path, 'r');
        if ($handle === false) {
            $this->addError('importFile', 'Could not open the uploaded CSV file.');

            return;
        }

        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        $headerRow = fgetcsv($handle);
        if (! $headerRow || empty(array_filter($headerRow))) {
            fclose($handle);
            $this->addError('importFile', 'The CSV file is empty or missing headers.');

            return;
        }

        $colMap = [];
        foreach ($headerRow as $index => $colName) {
            $normalized = strtolower(trim($colName));
            $normalized = str_replace([' ', '-', '_'], '', $normalized);
            $colMap[$normalized] = $index;
        }

        $nameIdx = $colMap['name'] ?? $colMap['productname'] ?? null;
        $skuIdx = $colMap['sku'] ?? null;
        $priceIdx = $colMap['price'] ?? null;

        if ($nameIdx === null || $skuIdx === null || $priceIdx === null) {
            fclose($handle);
            $this->addError('importFile', 'Missing required CSV headers: "Name", "SKU", and "Price" must be present.');

            return;
        }

        $slugIdx = $colMap['slug'] ?? null;
        $comparePriceIdx = $colMap['compareatprice'] ?? $colMap['compareprice'] ?? null;
        $stockIdx = $colMap['stock'] ?? $colMap['quantity'] ?? $colMap['qty'] ?? null;
        $categoriesIdx = $colMap['categories'] ?? $colMap['category'] ?? null;
        $typeIdx = $colMap['producttype'] ?? $colMap['type'] ?? null;
        $vendorIdx = $colMap['vendor'] ?? $colMap['brand'] ?? null;
        $activeIdx = $colMap['isactive'] ?? $colMap['active'] ?? $colMap['status'] ?? null;
        $featuredIdx = $colMap['isfeatured'] ?? $colMap['featured'] ?? null;
        $descIdx = $colMap['description'] ?? $colMap['desc'] ?? null;

        $rows = [];
        $errors = [];
        $rowNumber = 1;
        $seenSkus = [];

        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;

            if (empty(array_filter($row, fn ($val) => trim((string) $val) !== ''))) {
                continue;
            }

            $name = trim($row[$nameIdx] ?? '');
            $sku = trim($row[$skuIdx] ?? '');
            $price = trim($row[$priceIdx] ?? '');

            if ($name === '') {
                $errors[] = "Row {$rowNumber}: Product Name is required.";
            }

            if ($sku === '') {
                $errors[] = "Row {$rowNumber}: SKU is required.";
            } elseif (isset($seenSkus[$sku])) {
                $errors[] = "Row {$rowNumber}: Duplicate SKU '{$sku}' found in CSV (already present on row {$seenSkus[$sku]}).";
            } else {
                $seenSkus[$sku] = $rowNumber;
            }

            if ($price === '' || ! is_numeric($price) || (float) $price < 0) {
                $errors[] = "Row {$rowNumber}: Price must be a valid positive number.";
            }

            $rawComparePrice = $comparePriceIdx !== null ? trim($row[$comparePriceIdx] ?? '') : '';
            if ($rawComparePrice !== '' && (! is_numeric($rawComparePrice) || (float) $rawComparePrice < 0)) {
                $errors[] = "Row {$rowNumber}: Compare at Price must be a valid positive number if provided.";
            }

            $rawStock = $stockIdx !== null ? trim($row[$stockIdx] ?? '') : '';
            if ($rawStock !== '' && (! is_numeric($rawStock) || (int) $rawStock < 0)) {
                $errors[] = "Row {$rowNumber}: Stock must be a valid positive integer if provided.";
            }

            $rows[] = [
                'name' => $name,
                'sku' => $sku,
                'slug' => $slugIdx !== null ? trim($row[$slugIdx] ?? '') : '',
                'price' => (float) $price,
                'compare_at_price' => $rawComparePrice !== '' && is_numeric($rawComparePrice) ? (float) $rawComparePrice : null,
                'stock' => $rawStock !== '' && is_numeric($rawStock) ? max(0, (int) $rawStock) : 0,
                'categories' => $categoriesIdx !== null ? trim($row[$categoriesIdx] ?? '') : '',
                'product_type' => $typeIdx !== null ? trim($row[$typeIdx] ?? '') : '',
                'vendor' => $vendorIdx !== null ? trim($row[$vendorIdx] ?? '') : '',
                'is_active' => $activeIdx !== null ? trim($row[$activeIdx] ?? '') : '1',
                'is_featured' => $featuredIdx !== null ? trim($row[$featuredIdx] ?? '') : '0',
                'description' => $descIdx !== null ? trim($row[$descIdx] ?? '') : '',
            ];
        }

        fclose($handle);

        if (empty($rows) && empty($errors)) {
            $this->addError('importFile', 'The uploaded CSV file contains no product data rows.');

            return;
        }

        if (! empty($errors)) {
            $this->importErrors = $errors;

            return;
        }

        $createdCount = 0;
        $updatedCount = 0;

        DB::transaction(function () use ($rows, &$createdCount, &$updatedCount) {
            foreach ($rows as $rowData) {
                $existingProduct = Product::where('sku', $rowData['sku'])->first();

                $baseSlug = $rowData['slug'] !== '' ? Str::slug($rowData['slug']) : Str::slug($rowData['name']);
                if ($baseSlug === '') {
                    $baseSlug = 'product-'.strtolower(Str::random(6));
                }

                $slug = $baseSlug;
                $slugConflict = Product::where('slug', $slug)
                    ->when($existingProduct, fn ($q) => $q->where('id', '!=', $existingProduct->id))
                    ->exists();

                if ($slugConflict) {
                    $slug = $baseSlug.'-'.strtolower(Str::random(5));
                }

                $isActive = in_array(strtolower((string) $rowData['is_active']), ['1', 'true', 'yes', 'active'], true);
                $isFeatured = in_array(strtolower((string) $rowData['is_featured']), ['1', 'true', 'yes', 'featured'], true);

                $payload = [
                    'name' => $rowData['name'],
                    'slug' => $slug,
                    'sku' => $rowData['sku'],
                    'price' => $rowData['price'],
                    'compare_at_price' => $rowData['compare_at_price'],
                    'description' => $rowData['description'] !== '' ? $rowData['description'] : null,
                    'product_type' => $rowData['product_type'] !== '' ? $rowData['product_type'] : null,
                    'vendor' => $rowData['vendor'] !== '' ? $rowData['vendor'] : null,
                    'is_active' => $isActive,
                    'is_featured' => $isFeatured,
                ];

                if ($existingProduct) {
                    $existingProduct->update($payload);
                    $product = $existingProduct;
                    $updatedCount++;
                } else {
                    $product = Product::create($payload);
                    $createdCount++;
                }

                // Categories
                if ($rowData['categories'] !== '') {
                    $catNames = array_filter(
                        array_map('trim', preg_split('/[,|;]/', $rowData['categories'])),
                        fn ($c) => $c !== ''
                    );

                    $categoryIds = [];
                    foreach ($catNames as $catName) {
                        $catSlug = Str::slug($catName);
                        if ($catSlug !== '') {
                            $category = Category::firstOrCreate(
                                ['slug' => $catSlug],
                                ['name' => $catName, 'is_active' => true]
                            );
                            $categoryIds[] = $category->id;
                        }
                    }

                    if (! empty($categoryIds)) {
                        $product->categories()->sync($categoryIds);
                    }
                }

                // Default Variant & Inventory
                $variant = $product->variants()->first();

                if (! $variant) {
                    $variant = $product->variants()->create([
                        'title' => 'Default Variant',
                        'sku' => $product->sku,
                        'price' => $product->price,
                        'compare_at_price' => $product->compare_at_price,
                        'is_active' => $product->is_active,
                    ]);
                } elseif ($variant->title === 'Default Variant') {
                    $variant->update([
                        'sku' => $product->sku,
                        'price' => $product->price,
                        'compare_at_price' => $product->compare_at_price,
                        'is_active' => $product->is_active,
                    ]);
                }

                $stockQuantity = max(0, (int) $rowData['stock']);
                if ($variant->inventory) {
                    $variant->inventory->update([
                        'quantity' => $stockQuantity,
                    ]);
                } else {
                    $variant->inventory()->create([
                        'quantity' => $stockQuantity,
                        'reserved_quantity' => 0,
                    ]);
                }
            }
        });

        $msg = 'Successfully imported '.($createdCount + $updatedCount)." products ({$createdCount} created, {$updatedCount} updated).";

        $this->successMessage = $msg;
        session()->flash('success', $msg);

        $this->closeImportModal();
    }
}
