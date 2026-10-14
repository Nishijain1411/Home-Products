<?php

namespace App\Livewire\Admin\Categories;

use App\Models\Category;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class Create extends Component
{
    use WithFileUploads;

    public $editingId = null;

    public $name = '';

    public $slug = '';

    public $description = '';

    public $collection_type = 'manual';

    public $is_active = true;

    public $image = null;

    public $existingImage = null;

    public $sort_order = 0;

    public $seo_title = '';

    public $seo_description = '';

    public $parent_id = null;

    public function updatedName($value): void
    {
        if (! $this->editingId && empty($this->slug)) {
            $this->slug = Str::slug($value);
        }
    }

    protected function rules(): array
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
                Rule::unique('categories', 'slug')->ignore($this->editingId),
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
                'mimes:jpg,jpeg,png,webp',
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

    public function removeImage(): void
    {
        $this->image = null;
        $this->resetValidation('image');
    }

    public function removeExistingImage(): void
    {
        if ($this->editingId && $this->existingImage) {
            $category = Category::find($this->editingId);
            if ($category && $category->image) {
                if (Storage::disk('public')->exists($category->image)) {
                    Storage::disk('public')->delete($category->image);
                }
                $category->update(['image' => null]);
            }
        }

        $this->existingImage = null;
    }

    public function save()
    {
        $validated = $this->validate();

        if ($this->image) {
            $validated['image'] = $this->image->store(
                'categories',
                'public'
            );
        } elseif ($this->editingId) {
            $validated['image'] = $this->existingImage;
        } else {
            $validated['image'] = null;
        }

        if ($this->editingId) {
            $category = Category::findOrFail($this->editingId);

            if (
                $this->image &&
                $category->image &&
                $category->image !== $validated['image'] &&
                Storage::disk('public')->exists($category->image)
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

        return redirect()->route('admin.categories.index');
    }

    public function render()
    {
        $parentCategories = Category::query()
            ->where('is_active', true)
            ->when($this->editingId, function ($query) {
                $query->where('id', '!=', $this->editingId);
            })
            ->orderBy('name')
            ->get();

        return view('livewire.admin.categories.form', [
            'parentCategories' => $parentCategories,
            'isEditing' => (bool) $this->editingId,
        ]);
    }
}
