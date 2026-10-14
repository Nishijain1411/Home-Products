<?php

namespace App\Livewire\Admin\Categories;

use App\Models\Category;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Edit extends Create
{
    public Category $category;

    public function mount(Category $category): void
    {
        $this->category = $category;
        $this->editingId = $category->id;
        $this->name = $category->name;
        $this->slug = $category->slug;
        $this->description = $category->description ?? '';
        $this->collection_type = $category->collection_type ?? 'manual';
        $this->image = null;
        $this->existingImage = $category->image;
        $this->sort_order = $category->sort_order ?? 0;
        $this->is_active = (bool) $category->is_active;
        $this->seo_title = $category->seo_title ?? '';
        $this->seo_description = $category->seo_description ?? '';
        $this->parent_id = $category->parent_id;
    }
}
