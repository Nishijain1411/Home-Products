<?php

namespace App\Livewire\Storefront;

use App\Models\Category;
use App\Models\Product;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class ProductListing extends Component
{
    use WithPagination;

    #[Url(as: 'q', history: true)]
    public string $search = '';

    #[Url(as: 'category', history: true)]
    public string|int|null $category = null;

    #[Url(as: 'sort', history: true)]
    public string $sort = 'latest';

    #[Url(as: 'min_price', history: true)]
    public string $minPrice = '';

    #[Url(as: 'max_price', history: true)]
    public string $maxPrice = '';

    #[Url(as: 'in_stock', history: true)]
    public bool $inStock = false;

    public ?Category $currentCategory = null;

    public function mount(?Category $category = null): void
    {
        if ($category && $category->exists) {
            $this->currentCategory = $category;
            $this->category = $category->id;
        } elseif ($this->category) {
            $this->currentCategory = is_numeric($this->category)
                ? Category::find($this->category)
                : Category::where('slug', $this->category)->first();

            if ($this->currentCategory) {
                $this->category = $this->currentCategory->id;
            }
        }
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCategory(): void
    {
        $this->currentCategory = $this->category ? Category::find($this->category) : null;
        $this->resetPage();
    }

    public function updatedSort(): void
    {
        $this->resetPage();
    }

    public function updatedMinPrice(): void
    {
        $this->resetPage();
    }

    public function updatedMaxPrice(): void
    {
        $this->resetPage();
    }

    public function updatedInStock(): void
    {
        $this->resetPage();
    }

    public function selectCategory(?int $categoryId): void
    {
        $this->category = $categoryId;
        $this->currentCategory = $categoryId ? Category::find($categoryId) : null;
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->category = null;
        $this->currentCategory = null;
        $this->sort = 'latest';
        $this->minPrice = '';
        $this->maxPrice = '';
        $this->inStock = false;
        $this->resetPage();
    }

    public function render()
    {
        $products = Product::query()
            ->where('is_active', true)
            ->with([
                'images' => function ($query) {
                    $query->orderBy('position');
                },
                'categories',
                'variants.inventory',
            ])
            ->when(trim($this->search) !== '', function ($query) {
                $term = '%'.trim($this->search).'%';
                $query->where(function ($q) use ($term) {
                    $q->where('name', 'like', $term)
                        ->orWhere('sku', 'like', $term)
                        ->orWhere('description', 'like', $term)
                        ->orWhere('vendor', 'like', $term);
                });
            })
            ->when($this->category, function ($query) {
                $categoryModel = Category::with('children')->find($this->category);

                if ($categoryModel) {
                    $categoryIds = $categoryModel->children->pluck('id')->push($categoryModel->id);
                    $query->whereHas('categories', fn ($q) => $q->whereIn('categories.id', $categoryIds));
                } else {
                    $query->whereHas('categories', fn ($q) => $q->where('categories.id', $this->category));
                }
            })
            ->when($this->minPrice !== '' && is_numeric($this->minPrice), function ($query) {
                $query->where('price', '>=', (float) $this->minPrice);
            })
            ->when($this->maxPrice !== '' && is_numeric($this->maxPrice), function ($query) {
                $query->where('price', '<=', (float) $this->maxPrice);
            })
            ->when($this->inStock, function ($query) {
                $query->whereHas('variants.inventory', function ($q) {
                    $q->where('quantity', '>', 0);
                });
            })
            ->when($this->sort === 'latest', function ($query) {
                $query->latest('id');
            })
            ->when($this->sort === 'price_low', function ($query) {
                $query->orderBy('price', 'asc');
            })
            ->when($this->sort === 'price_high', function ($query) {
                $query->orderBy('price', 'desc');
            })
            ->when($this->sort === 'name_asc', function ($query) {
                $query->orderBy('name', 'asc');
            })
            ->when($this->sort === 'name_desc', function ($query) {
                $query->orderBy('name', 'desc');
            })
            ->paginate(12);

        $categories = Category::query()
            ->where('is_active', true)
            ->whereNull('parent_id')
            ->with(['children' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order')])
            ->withCount('products')
            ->orderBy('sort_order')
            ->get();

        return view('livewire.storefront.product-listing', [
            'products' => $products,
            'categories' => $categories,
        ])->layout('storefront.layouts.app');
    }
}
