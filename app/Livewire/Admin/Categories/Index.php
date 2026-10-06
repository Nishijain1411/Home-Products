<?php

namespace App\Livewire\Admin\Categories;

use App\Models\Category;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;


#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination ,WithFileUploads;

    public $search = '';

    public $showModal = false;
    public $showDeleteModal = false;

    public $editingId = null;
    public $deleteId = null;

    public $name = '';
    public $slug = '';
    public $description = '';
    public $collection_type = 'manual';
    public $is_active = true;
    public $image = null;
    public $sort_order = 0;
    public $seo_title = '';
    public $seo_description = '';
    public $parent_id = null;

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedName($value)
    {
        if (!$this->editingId && empty($this->slug)) {
            $this->slug = Str::slug($value);
        }
    }

    public function openCreate()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEdit($id)
    {
        $category = Category::findOrFail($id);

        $this->editingId = $category->id;

        $this->name = $category->name;
        $this->slug = $category->slug;
        $this->description = $category->description;
        $this->collection_type = $category->collection_type;
        $this->image = null;
        $this->sort_order = $category->sort_order ?? 0;
        $this->is_active = $category->is_active;
        $this->seo_title = $category->seo_title ?? '';
        $this->seo_description = $category->seo_description ?? '';
        $this->parent_id = $category->parent_id;
        $this->resetValidation();

        $this->showModal = true;
    }

    protected function rules()
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'slug' => [
                'required',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique('categories', 'slug')
                    ->ignore($this->editingId),
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'collection_type' => [
                'required',
                Rule::in(['manual', 'automated']),
            ],

            'image' => [
                'nullable',
                'image',
                'max:2048',
            ],

            'sort_order' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'is_active' => [
                'boolean',
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

            'parent_id' => [
                'nullable',
                'integer',
                'exists:categories,id',
            ],
        ];
    }

    public function save()
    {
        $validated = $this->validate();

        if ($this->image) {
            $validated['image'] = $this->image->store(
                'categories',
                'public'
            );
        }

        if ($this->editingId) {

            $category = Category::findOrFail($this->editingId);

            // Remove old image if a new image was uploaded
            if (
                $this->image &&
                $category->image
            ) {
                Storage::disk('public')->delete(
                    $category->image
                );
            }

            $category->update($validated);

            session()->flash(
                'success',
                'Category updated successfully.'
            );

        } else {

            Category::create($validated);

            session()->flash(
                'success',
                'Category created successfully.'
            );
        }

        $this->closeModal();
    } 

    public function confirmDelete($id)
    {
        $this->deleteId = $id;
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        $category = Category::findOrFail($this->deleteId);

        if ($category->products()->exists()) {

            session()->flash(
                'error',
                'This category cannot be deleted because products are assigned to it.'
            );

            $this->closeDeleteModal();

            return;
        }

        $category->delete();

        session()->flash(
            'success',
            'Category deleted successfully.'
        );

        $this->closeDeleteModal();
    }

    public function toggleStatus($id)
    {
        $category = Category::findOrFail($id);

        $category->update([
            'is_active' => !$category->is_active,
        ]);

        session()->flash(
            'success',
            'Category status updated.'
        );
    }

    public function closeModal()
    {
        $this->showModal = false;

        $this->resetForm();
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->deleteId = null;
    }

    public function resetForm()
    {
        $this->reset([
            'editingId',
            'name',
            'slug',
            'description',
            'image',
            'parent_id',
            'seo_title',
            'seo_description',
        ]);

        $this->collection_type = 'manual';
        $this->sort_order = 0;
        $this->is_active = true;

        $this->resetValidation();
    }

  
    public function render()
    {
        $categories = Category::query()
            ->when($this->search, function ($query) {
                $query->where(function ($query) {
                    $query->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('slug', 'like', '%' . $this->search . '%');
                });
            })
            ->with('parent')
            ->latest()
            ->paginate(15);

        $parentCategories = Category::query()
            ->where('is_active', true)
            ->when($this->editingId, function ($query) {
                $query->where('id', '!=', $this->editingId);
            })
            ->orderBy('name')
            ->get();

        return view('livewire.admin.categories.index', [
            'categories' => $categories,
            'parentCategories' => $parentCategories,
        ]);
    }

    
}