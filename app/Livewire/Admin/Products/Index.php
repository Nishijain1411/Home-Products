<?php

namespace App\Livewire\Admin\Products;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    public $search = '';

    public $showModal = false;
    public $showDeleteModal = false;

    public $editingId = null;
    public $deleteId = null;

    // Product fields
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

    // Categories
    public $selectedCategories = [];

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
        ];
    }

    public function render()
    {
        $products = Product::query()
            ->when($this->search, function ($query) {
                $query->where(function ($query) {
                    $query
                        ->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('sku', 'like', '%' . $this->search . '%')
                        ->orWhere('slug', 'like', '%' . $this->search . '%');
                });
            })
            ->with('categories')
            ->latest()
            ->paginate(15);

        $categories = Category::query()
            ->where('is_active', true)
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

        $this->selectedCategories = $product
            ->categories
            ->pluck('id')
            ->toArray();

        $this->showModal = true;
    }

    public function save()
    {
        $validated = $this->validate();

        unset($validated['selectedCategories']);

        if ($this->editingId) {

            $product = Product::findOrFail($this->editingId);

            $product->update($validated);

            $product->categories()->sync($this->selectedCategories);

            session()->flash(
                'success',
                'Product updated successfully.'
            );

        } else {

            $product = Product::create($validated);

            $product->categories()->sync($this->selectedCategories);

            session()->flash(
                'success',
                'Product created successfully.'
            );
        }

        $this->closeModal();
    }

    public function toggleStatus($id)
    {
        $product = Product::findOrFail($id);

        $product->update([
            'is_active' => !$product->is_active,
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
        ]);

        $this->is_active = true;
        $this->is_featured = false;
        $this->sort_order = 0;
    }

    public function updatedName($value)
    {
        if (!$this->editingId) {
            $this->slug = Str::slug($value);
        }
    }
}