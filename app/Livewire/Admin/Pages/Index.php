<?php

namespace App\Livewire\Admin\Pages;

use App\Models\Page;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public string $statusFilter = 'all';

    public bool $showDeleteModal = false;

    public ?int $deleteId = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => 'all'],
    ];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function togglePublish(int $id): void
    {
        $page = Page::findOrFail($id);
        $page->is_published = ! $page->is_published;
        $page->published_at = $page->is_published ? now() : null;
        $page->save();

        $status = $page->is_published ? 'published' : 'unpublished';
        session()->flash('success', "Page '{$page->title}' {$status} successfully.");
    }

    public function confirmDelete(int $id): void
    {
        $this->deleteId = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        if ($this->deleteId) {
            $page = Page::findOrFail($this->deleteId);
            $title = $page->title;
            $page->delete();

            $this->showDeleteModal = false;
            $this->deleteId = null;
            session()->flash('success', "Page '{$title}' deleted successfully.");
        }
    }

    public function render()
    {
        $query = Page::query();

        if ($this->search !== '') {
            $search = trim($this->search);
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if ($this->statusFilter === 'published') {
            $query->where('is_published', true);
        } elseif ($this->statusFilter === 'draft') {
            $query->where('is_published', false);
        }

        $pages = $query->latest()->paginate(10);

        return view('livewire.admin.pages.index', [
            'pages' => $pages,
            'totalPages' => Page::count(),
            'publishedPages' => Page::where('is_published', true)->count(),
            'draftPages' => Page::where('is_published', false)->count(),
        ]);
    }
}
