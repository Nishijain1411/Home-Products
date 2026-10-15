<div class="p-4 md:p-6 max-w-6xl mx-auto space-y-6">

    {{-- Flash Notifications --}}
    @if (session()->has('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800 flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-2">
                <svg class="h-5 w-5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
        </div>
    @endif

    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b border-gray-200 pb-5">
        <div>
            <div class="flex items-center gap-2 text-xs font-medium text-gray-500">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-blue-600 transition">Dashboard</a>
                <span>/</span>
                <span class="text-gray-800">Content</span>
                <span>/</span>
                <span class="text-gray-800">Navigation</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 mt-1">
                Navigation Menus
            </h1>
            <p class="text-sm text-gray-500 mt-0.5">
                Manage header and footer links, menu hierarchies, external links, and display order across the storefront.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <button
                type="button"
                wire:click="openCreateItem()"
                class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-blue-700 transition"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Menu Item
            </button>
        </div>
    </div>

    {{-- Location Tabs --}}
    <div class="border-b border-gray-200">
        <nav class="-mb-px flex space-x-6">
            <button
                type="button"
                wire:click="selectLocation('header')"
                class="py-3 px-1 border-b-2 text-sm font-semibold transition {{ $activeLocation === 'header' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
            >
                Header Navigation
            </button>
            <button
                type="button"
                wire:click="selectLocation('footer_store')"
                class="py-3 px-1 border-b-2 text-sm font-semibold transition {{ $activeLocation === 'footer_store' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
            >
                Footer Store Links
            </button>
            <button
                type="button"
                wire:click="selectLocation('footer_legal')"
                class="py-3 px-1 border-b-2 text-sm font-semibold transition {{ $activeLocation === 'footer_legal' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
            >
                Footer Support & Legal Links
            </button>
        </nav>
    </div>

    {{-- Menu Items List --}}
    <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
        <div class="p-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
            <div class="text-xs font-semibold uppercase tracking-wider text-gray-500">
                {{ $menu->name }} Items
            </div>
            <div class="text-xs text-gray-500">
                {{ $rootItems->count() }} top-level items
            </div>
        </div>

        <div class="divide-y divide-gray-200">
            @forelse ($rootItems as $item)
                <div wire:key="root-item-{{ $item->id }}" class="p-4 hover:bg-gray-50/50 transition">
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3 min-w-0">
                            {{-- Reorder Controls --}}
                            <div class="flex flex-col gap-0.5 text-gray-400">
                                <button type="button" wire:click="moveUp({{ $item->id }})" class="hover:text-blue-600" title="Move Up">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                    </svg>
                                </button>
                                <button type="button" wire:click="moveDown({{ $item->id }})" class="hover:text-blue-600" title="Move Down">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                            </div>

                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-semibold text-gray-900 text-sm">{{ $item->title }}</span>
                                    @if ($item->target === '_blank')
                                        <span class="rounded bg-gray-100 px-1.5 py-0.5 text-[10px] font-medium text-gray-600" title="Opens in new tab">
                                            ↗ New Tab
                                        </span>
                                    @endif
                                    @if (! $item->is_active)
                                        <span class="rounded bg-amber-100 px-1.5 py-0.5 text-[10px] font-semibold text-amber-800">
                                            Hidden
                                        </span>
                                    @endif
                                </div>
                                <div class="text-xs font-mono text-gray-500 truncate mt-0.5">
                                    {{ $item->url }}
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            {{-- Add child item button (only for header menu) --}}
                            @if ($activeLocation === 'header')
                                <button
                                    type="button"
                                    wire:click="openCreateItem({{ $item->id }})"
                                    class="rounded-lg border border-gray-200 bg-white px-2.5 py-1 text-xs font-semibold text-gray-700 hover:bg-gray-50 transition"
                                >
                                    + Sub-item
                                </button>
                            @endif

                            <button
                                type="button"
                                wire:click="toggleItemStatus({{ $item->id }})"
                                class="rounded p-1 text-gray-400 hover:text-gray-600"
                                title="{{ $item->is_active ? 'Hide link' : 'Show link' }}"
                            >
                                @if ($item->is_active)
                                    <svg class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                @else
                                    <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                    </svg>
                                @endif
                            </button>

                            <button
                                type="button"
                                wire:click="openEditItem({{ $item->id }})"
                                class="rounded p-1 text-blue-600 hover:bg-blue-50"
                                title="Edit Item"
                            >
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </button>

                            <button
                                type="button"
                                wire:click="confirmDeleteItem({{ $item->id }})"
                                class="rounded p-1 text-red-600 hover:bg-red-50"
                                title="Delete Item"
                            >
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Nested Child Items (Submenu) --}}
                    @if ($item->children->isNotEmpty())
                        <div class="mt-3 ml-8 space-y-2 border-l-2 border-gray-100 pl-4">
                            @foreach ($item->children as $child)
                                <div wire:key="child-item-{{ $child->id }}" class="flex items-center justify-between gap-4 py-1.5 px-2 rounded-lg hover:bg-gray-100/50 transition">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <div class="flex flex-col gap-0.5 text-gray-400">
                                            <button type="button" wire:click="moveUp({{ $child->id }})" class="hover:text-blue-600">
                                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                                </svg>
                                            </button>
                                            <button type="button" wire:click="moveDown({{ $child->id }})" class="hover:text-blue-600">
                                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                </svg>
                                            </button>
                                        </div>

                                        <div>
                                            <div class="flex items-center gap-2">
                                                <span class="text-xs font-semibold text-gray-800">{{ $child->title }}</span>
                                                <span class="rounded bg-blue-50 px-1 py-0.2 text-[9px] font-bold text-blue-600">Dropdown</span>
                                                @if (! $child->is_active)
                                                    <span class="rounded bg-amber-100 px-1 py-0.2 text-[9px] font-semibold text-amber-800">Hidden</span>
                                                @endif
                                            </div>
                                            <div class="text-[11px] font-mono text-gray-400 truncate">{{ $child->url }}</div>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-1 shrink-0">
                                        <button
                                            type="button"
                                            wire:click="openEditItem({{ $child->id }})"
                                            class="rounded p-1 text-blue-600 hover:bg-blue-50"
                                        >
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>

                                        <button
                                            type="button"
                                            wire:click="confirmDeleteItem({{ $child->id }})"
                                            class="rounded p-1 text-red-600 hover:bg-red-50"
                                        >
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @empty
                <div class="p-12 text-center text-sm text-gray-500">
                    <p class="font-semibold text-gray-700">No menu items configured for this location.</p>
                    <p class="text-xs text-gray-400 mt-1">Click "Add Menu Item" above to add your first navigation link.</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Add / Edit Item Modal --}}
    @if ($showItemModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 p-4">
            <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <h3 class="text-base font-bold text-gray-900">
                        {{ $editingItemId ? 'Edit Menu Item' : 'Add Menu Item' }}
                    </h3>
                    <button type="button" wire:click="$set('showItemModal', false)" class="text-gray-400 hover:text-gray-600">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Quick Link Helper --}}
                <div class="rounded-xl border border-blue-100 bg-blue-50/50 p-3.5 space-y-2.5">
                    <label class="block text-xs font-semibold text-blue-900 uppercase tracking-wider">
                        Quick Link Selector
                    </label>

                    <div class="grid grid-cols-4 gap-2 text-xs">
                        <button
                            type="button"
                            wire:click="$set('quickLinkType', 'custom')"
                            class="rounded-lg px-2 py-1.5 font-medium transition {{ $quickLinkType === 'custom' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 border border-gray-200' }}"
                        >
                            Custom URL
                        </button>
                        <button
                            type="button"
                            wire:click="$set('quickLinkType', 'page')"
                            class="rounded-lg px-2 py-1.5 font-medium transition {{ $quickLinkType === 'page' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 border border-gray-200' }}"
                        >
                            CMS Page
                        </button>
                        <button
                            type="button"
                            wire:click="$set('quickLinkType', 'category')"
                            class="rounded-lg px-2 py-1.5 font-medium transition {{ $quickLinkType === 'category' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 border border-gray-200' }}"
                        >
                            Category
                        </button>
                        <button
                            type="button"
                            wire:click="$set('quickLinkType', 'store')"
                            class="rounded-lg px-2 py-1.5 font-medium transition {{ $quickLinkType === 'store' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 border border-gray-200' }}"
                        >
                            Store Link
                        </button>
                    </div>

                    @if ($quickLinkType === 'page')
                        <select
                            wire:model.live="selectedPageId"
                            class="block w-full rounded-lg border-gray-300 text-xs shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                            <option value="">-- Choose a published page --</option>
                            @foreach ($pages as $p)
                                <option value="{{ $p->id }}">{{ $p->title }} (/page/{{ $p->slug }})</option>
                            @endforeach
                        </select>
                    @elseif ($quickLinkType === 'category')
                        <select
                            wire:model.live="selectedCategoryId"
                            class="block w-full rounded-lg border-gray-300 text-xs shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                            <option value="">-- Choose a category --</option>
                            @foreach ($categories as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    @elseif ($quickLinkType === 'store')
                        <select
                            wire:model.live="selectedStoreLink"
                            class="block w-full rounded-lg border-gray-300 text-xs shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                            <option value="">-- Choose store section --</option>
                            <option value="/storefront/home">Storefront Home (/storefront/home)</option>
                            <option value="/storefront/shop">All Products (/storefront/shop)</option>
                            <option value="/storefront/home#categories">Categories Anchor (#categories)</option>
                            <option value="/storefront/cart">Cart (/storefront/cart)</option>
                            <option value="/my-orders">Customer Orders (/my-orders)</option>
                        </select>
                    @endif
                </div>

                {{-- Form Fields --}}
                <div class="space-y-3">
                    <div>
                        <label for="itemTitle" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider">
                            Link Title <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            id="itemTitle"
                            wire:model="itemTitle"
                            placeholder="e.g. About Us"
                            class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                        @error('itemTitle')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="itemUrl" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider">
                            URL / Destination Path <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            id="itemUrl"
                            wire:model="itemUrl"
                            placeholder="e.g. /page/about-us or https://..."
                            class="mt-1 block w-full font-mono text-xs rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                        @error('itemUrl')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    @if ($activeLocation === 'header')
                        <div>
                            <label for="itemParentId" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                Parent Item (for nested dropdown)
                            </label>
                            <select
                                id="itemParentId"
                                wire:model="itemParentId"
                                class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                                <option value="">None (Top-Level Item)</option>
                                @foreach ($rootItems as $parentCandidate)
                                    @if (! $editingItemId || $parentCandidate->id !== $editingItemId)
                                        <option value="{{ $parentCandidate->id }}">{{ $parentCandidate->title }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="itemTarget" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                Target Window
                            </label>
                            <select
                                id="itemTarget"
                                wire:model="itemTarget"
                                class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                                <option value="_self">Same Tab (_self)</option>
                                <option value="_blank">New Tab (_blank)</option>
                            </select>
                        </div>

                        <div>
                            <label for="itemSortOrder" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                Sort Order
                            </label>
                            <input
                                type="number"
                                id="itemSortOrder"
                                wire:model="itemSortOrder"
                                class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                        </div>
                    </div>

                    <div class="pt-2">
                        <label class="flex items-center gap-2.5 cursor-pointer">
                            <input
                                type="checkbox"
                                wire:model="itemIsActive"
                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 h-4 w-4"
                            >
                            <span class="text-xs font-medium text-gray-800">Item is Active & Visible</span>
                        </label>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3 border-t border-gray-100 pt-4">
                    <button
                        type="button"
                        wire:click="$set('showItemModal', false)"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 transition"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        wire:click="saveItem"
                        class="rounded-lg bg-blue-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-blue-700 transition"
                    >
                        {{ $editingItemId ? 'Update Item' : 'Save Item' }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Delete Modal --}}
    @if ($showDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 p-4">
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-100 text-red-600">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">Delete Menu Link</h3>
                        <p class="mt-1 text-xs text-gray-500">
                            Are you sure you want to delete this link from navigation?
                        </p>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button
                        type="button"
                        wire:click="$set('showDeleteModal', false)"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 transition"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        wire:click="deleteItem"
                        class="rounded-lg bg-red-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-red-700 transition"
                    >
                        Yes, Delete
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
