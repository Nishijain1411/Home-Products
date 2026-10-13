<?php

namespace App\Livewire\Storefront;

use App\Models\Category;
use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;

class ProductListing extends Component
{
    use WithPagination;

    public string $search = '';

    public ?int $category = null;

    public string $sort = 'latest';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCategory(): void
    {
        $this->resetPage();
    }

    public function updatedSort(): void
    {
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
            ])
            ->when($this->search !== '', function ($query) {
                $query->where(function ($query) {
                    $query
                        ->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('sku', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->category, function ($query) {
                $query->whereHas('categories', function ($query) {
                    $query->where('categories.id', $this->category);
                });
            })
            ->when($this->sort === 'latest', function ($query) {
                $query->latest();
            })
            ->when($this->sort === 'price_low', function ($query) {
                $query->orderBy('price');
            })
            ->when($this->sort === 'price_high', function ($query) {
                $query->orderByDesc('price');
            })
            ->paginate(12);

        $categories = Category::query()
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->get();

        return view('livewire.storefront.product-listing', [
            'products' => $products,
            'categories' => $categories,
        ])->layout('storefront.layouts.app');
    }
}
