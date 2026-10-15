<?php

namespace App\Livewire\Admin\Pages;

use App\Models\Page;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Create extends Component
{
    public ?int $editingId = null;

    public string $title = '';

    public string $slug = '';

    public string $content = '';

    public string $template = 'default';

    public bool $is_published = true;

    // SEO Fields
    public string $meta_title = '';

    public string $meta_description = '';

    public string $canonical_url = '';

    public bool $is_indexed = true;

    public function updatedTitle($value): void
    {
        if (! $this->editingId && empty($this->slug)) {
            $this->slug = Str::slug($value);
        }
    }

    public function generateSlug(): void
    {
        $this->slug = Str::slug($this->title);
    }

    protected function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('pages', 'slug')->ignore($this->editingId),
            ],
            'content' => ['nullable', 'string'],
            'template' => ['required', 'in:default,legal,contact,faq'],
            'is_published' => ['boolean'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:1000'],
            'canonical_url' => ['nullable', 'url', 'max:500'],
            'is_indexed' => ['boolean'],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'meta_title' => 'page SEO title',
            'meta_description' => 'meta description',
            'canonical_url' => 'canonical URL',
        ];
    }

    public function save()
    {
        $validated = $this->validate();

        $validated['slug'] = Str::slug($validated['slug']);
        $validated['meta_title'] = ! empty($validated['meta_title']) ? trim($validated['meta_title']) : null;
        $validated['meta_description'] = ! empty($validated['meta_description']) ? trim($validated['meta_description']) : null;
        $validated['canonical_url'] = ! empty($validated['canonical_url']) ? trim($validated['canonical_url']) : null;

        if ($this->editingId) {
            $page = Page::findOrFail($this->editingId);
            if ($validated['is_published']) {
                $validated['published_at'] = $page->published_at ?? now();
            } else {
                $validated['published_at'] = null;
            }
            $page->update($validated);
            session()->flash('success', "Page '{$page->title}' updated successfully.");
        } else {
            $validated['published_at'] = $validated['is_published'] ? now() : null;
            $page = Page::create($validated);
            session()->flash('success', "Page '{$page->title}' created successfully.");
        }

        return redirect()->route('admin.pages.index');
    }

    public function render()
    {
        return view('livewire.admin.pages.form', [
            'isEditing' => (bool) $this->editingId,
        ]);
    }
}
