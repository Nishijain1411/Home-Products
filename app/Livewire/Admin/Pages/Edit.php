<?php

namespace App\Livewire\Admin\Pages;

use App\Models\Page;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Edit extends Create
{
    public Page $page;

    public function mount(Page|int|string $page): void
    {
        if (! ($page instanceof Page)) {
            $page = is_numeric($page)
                ? Page::findOrFail((int) $page)
                : Page::where('slug', $page)->orWhere('id', $page)->firstOrFail();
        }

        $this->page = $page;
        $this->editingId = $page->id;
        $this->title = $page->title;
        $this->slug = $page->slug;
        $this->content = $page->content ?? '';
        $this->template = $page->template ?? 'default';
        $this->is_published = (bool) $page->is_published;
        $this->meta_title = $page->meta_title ?? '';
        $this->meta_description = $page->meta_description ?? '';
        $this->canonical_url = $page->canonical_url ?? '';
        $this->is_indexed = (bool) $page->is_indexed;
    }
}
