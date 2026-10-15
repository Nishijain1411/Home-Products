<?php

namespace App\Livewire\Admin\Navigation;

use App\Models\Category;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Index extends Component
{
    public string $activeLocation = 'header';

    // Item form modal state
    public bool $showItemModal = false;

    public ?int $editingItemId = null;

    public string $itemTitle = '';

    public string $itemUrl = '';

    public string $itemTarget = '_self';

    public ?int $itemParentId = null;

    public int $itemSortOrder = 0;

    public bool $itemIsActive = true;

    // Quick link helpers
    public string $quickLinkType = 'custom';

    public ?int $selectedPageId = null;

    public ?int $selectedCategoryId = null;

    public string $selectedStoreLink = '';

    // Delete confirmation
    public bool $showDeleteModal = false;

    public ?int $deleteItemId = null;

    public function selectLocation(string $location): void
    {
        $this->activeLocation = $location;
        $this->resetItemForm();
    }

    public function updatedQuickLinkType(): void
    {
        $this->selectedPageId = null;
        $this->selectedCategoryId = null;
        $this->selectedStoreLink = '';
    }

    public function updatedSelectedPageId($pageId): void
    {
        if ($pageId) {
            $page = Page::find($pageId);
            if ($page) {
                $this->itemTitle = $page->title;
                $this->itemUrl = '/page/'.$page->slug;
            }
        }
    }

    public function updatedSelectedCategoryId($categoryId): void
    {
        if ($categoryId) {
            $category = Category::find($categoryId);
            if ($category) {
                $this->itemTitle = $category->name;
                $this->itemUrl = '/storefront/products?category='.$category->slug;
            }
        }
    }

    public function updatedSelectedStoreLink($link): void
    {
        if ($link) {
            $links = [
                '/storefront/home' => 'Home',
                '/storefront/shop' => 'All Products',
                '/storefront/home#categories' => 'Categories',
                '/storefront/cart' => 'Shopping Cart',
                '/my-orders' => 'My Orders',
            ];

            if (isset($links[$link])) {
                $this->itemTitle = $links[$link];
                $this->itemUrl = $link;
            }
        }
    }

    public function openCreateItem(?int $parentId = null): void
    {
        $this->resetItemForm();
        $this->itemParentId = $parentId;

        $menu = $this->getActiveMenu();
        $maxOrder = MenuItem::where('menu_id', $menu->id)->max('sort_order') ?? 0;
        $this->itemSortOrder = $maxOrder + 1;

        $this->showItemModal = true;
    }

    public function openEditItem(int $id): void
    {
        $item = MenuItem::findOrFail($id);
        $this->editingItemId = $item->id;
        $this->itemTitle = $item->title;
        $this->itemUrl = $item->url;
        $this->itemTarget = $item->target;
        $this->itemParentId = $item->parent_id;
        $this->itemSortOrder = $item->sort_order;
        $this->itemIsActive = (bool) $item->is_active;

        $this->showItemModal = true;
    }

    public function resetItemForm(): void
    {
        $this->editingItemId = null;
        $this->itemTitle = '';
        $this->itemUrl = '';
        $this->itemTarget = '_self';
        $this->itemParentId = null;
        $this->itemSortOrder = 0;
        $this->itemIsActive = true;
        $this->quickLinkType = 'custom';
        $this->selectedPageId = null;
        $this->selectedCategoryId = null;
        $this->selectedStoreLink = '';
        $this->resetValidation();
    }

    public function saveItem(): void
    {
        $this->validate([
            'itemTitle' => ['required', 'string', 'max:100'],
            'itemUrl' => ['required', 'string', 'max:500'],
            'itemTarget' => ['required', 'in:_self,_blank'],
            'itemParentId' => ['nullable', 'exists:menu_items,id'],
            'itemSortOrder' => ['required', 'integer'],
            'itemIsActive' => ['boolean'],
        ]);

        $menu = $this->getActiveMenu();

        if ($this->editingItemId) {
            $item = MenuItem::where('menu_id', $menu->id)->findOrFail($this->editingItemId);
            $item->update([
                'parent_id' => $this->itemParentId,
                'title' => trim($this->itemTitle),
                'url' => trim($this->itemUrl),
                'target' => $this->itemTarget,
                'sort_order' => $this->itemSortOrder,
                'is_active' => $this->itemIsActive,
            ]);
            session()->flash('success', "Menu item '{$item->title}' updated successfully.");
        } else {
            $item = MenuItem::create([
                'menu_id' => $menu->id,
                'parent_id' => $this->itemParentId,
                'title' => trim($this->itemTitle),
                'url' => trim($this->itemUrl),
                'target' => $this->itemTarget,
                'sort_order' => $this->itemSortOrder,
                'is_active' => $this->itemIsActive,
            ]);
            session()->flash('success', "Menu item '{$item->title}' created successfully.");
        }

        $this->showItemModal = false;
        $this->resetItemForm();
    }

    public function moveUp(int $id): void
    {
        $item = MenuItem::findOrFail($id);
        $previousItem = MenuItem::where('menu_id', $item->menu_id)
            ->where('parent_id', $item->parent_id)
            ->where('sort_order', '<', $item->sort_order)
            ->orderByDesc('sort_order')
            ->first();

        if ($previousItem) {
            $currentOrder = $item->sort_order;
            $item->sort_order = $previousItem->sort_order;
            $previousItem->sort_order = $currentOrder;
            $item->save();
            $previousItem->save();
        }
    }

    public function moveDown(int $id): void
    {
        $item = MenuItem::findOrFail($id);
        $nextItem = MenuItem::where('menu_id', $item->menu_id)
            ->where('parent_id', $item->parent_id)
            ->where('sort_order', '>', $item->sort_order)
            ->orderBy('sort_order')
            ->first();

        if ($nextItem) {
            $currentOrder = $item->sort_order;
            $item->sort_order = $nextItem->sort_order;
            $nextItem->sort_order = $currentOrder;
            $item->save();
            $nextItem->save();
        }
    }

    public function toggleItemStatus(int $id): void
    {
        $item = MenuItem::findOrFail($id);
        $item->is_active = ! $item->is_active;
        $item->save();

        $status = $item->is_active ? 'activated' : 'deactivated';
        session()->flash('success', "Item '{$item->title}' {$status}.");
    }

    public function confirmDeleteItem(int $id): void
    {
        $this->deleteItemId = $id;
        $this->showDeleteModal = true;
    }

    public function deleteItem(): void
    {
        if ($this->deleteItemId) {
            $item = MenuItem::findOrFail($this->deleteItemId);
            $title = $item->title;
            $item->delete();

            $this->showDeleteModal = false;
            $this->deleteItemId = null;
            session()->flash('success', "Menu item '{$title}' removed.");
        }
    }

    protected function getActiveMenu(): Menu
    {
        $locations = [
            'header' => 'Main Header Navigation',
            'footer_store' => 'Footer Store Links',
            'footer_legal' => 'Footer Support & Legal Links',
        ];

        return Menu::firstOrCreate(
            ['location' => $this->activeLocation],
            [
                'name' => $locations[$this->activeLocation] ?? ucfirst(str_replace('_', ' ', $this->activeLocation)),
                'is_active' => true,
            ]
        );
    }

    public function render()
    {
        $menu = $this->getActiveMenu();

        $rootItems = MenuItem::where('menu_id', $menu->id)
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->with(['children' => function ($q) {
                $q->orderBy('sort_order')->orderBy('id');
            }])
            ->get();

        $pages = Page::where('is_published', true)->orderBy('title')->get();
        $categories = Category::orderBy('name')->get();

        return view('livewire.admin.navigation.index', [
            'menu' => $menu,
            'rootItems' => $rootItems,
            'pages' => $pages,
            'categories' => $categories,
        ]);
    }
}
