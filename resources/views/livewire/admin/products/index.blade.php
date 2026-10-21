<div class="p-6">

    {{-- Header --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

        <div>
            <h1 class="text-2xl font-semibold text-gray-900">
                Products
            </h1>

            <p class="mt-1 text-sm text-gray-600">
                Manage your products.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <button type="button" wire:click="openImportModal"
                class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                </svg>
                Import
            </button>

            <button type="button" wire:click="export" wire:loading.attr="disabled"
                class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500/20 disabled:opacity-50">
                <span wire:loading.remove wire:target="export" class="inline-flex items-center gap-1.5">
                    <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Export
                </span>
                <span wire:loading wire:target="export" class="inline-flex items-center gap-1.5">
                    <svg class="h-4 w-4 animate-spin text-gray-500" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                    </svg>
                    Exporting...
                </span>
            </button>

            <a href="{{ route('admin.products.create') }}"
                class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Product
            </a>
        </div>

    </div>


    {{-- Alerts & Notifications --}}
    @php
        $displaySuccess = $successMessage ?? session('success');
        $displayError = $errorMessage ?? session('error');
    @endphp

    {{-- Floating Toast Notification --}}
    @if ($displaySuccess)
    <div x-data="{ show: true }"
         x-show="show"
         x-init="setTimeout(() => show = false, 6000)"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-[-10px] scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="fixed top-6 right-6 z-50 flex max-w-md items-start gap-3 rounded-xl border border-green-200 bg-white p-4 shadow-xl ring-1 ring-black/5">
        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-green-100 text-green-600">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
        </div>
        <div class="flex-1 pt-0.5">
            <p class="text-sm font-semibold text-gray-900">Success</p>
            <p class="mt-0.5 text-xs text-gray-600 leading-relaxed">{{ $displaySuccess }}</p>
        </div>
        <button type="button" @click="show = false" wire:click="dismissAlert" class="rounded-md p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
    @endif

    {{-- Inline Banner Alert --}}
    @if ($displaySuccess)
    <div x-data="{ show: true }" x-show="show" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="mb-6 flex items-start justify-between rounded-xl border border-green-200 bg-gradient-to-r from-green-50 via-green-50/70 to-emerald-50/40 p-4 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-green-100 text-green-600">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <h4 class="text-sm font-semibold text-green-900">
                    Operation Successful
                </h4>
                <p class="text-sm text-green-700 mt-0.5">
                    {{ $displaySuccess }}
                </p>
            </div>
        </div>
        <button type="button" @click="show = false" wire:click="dismissAlert"
            class="rounded-lg p-1.5 text-green-600 hover:bg-green-100 hover:text-green-800 transition">
            <span class="sr-only">Dismiss</span>
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
    @endif

    @if ($displayError)
    <div x-data="{ show: true }" x-show="show"
        class="mb-6 flex items-start justify-between rounded-xl border border-red-200 bg-red-50 p-4 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-red-100 text-red-600">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <h4 class="text-sm font-semibold text-red-900">
                    Error
                </h4>
                <p class="text-sm text-red-700 mt-0.5">
                    {{ $displayError }}
                </p>
            </div>
        </div>
        <button type="button" @click="show = false"
            class="rounded-lg p-1.5 text-red-600 hover:bg-red-100 hover:text-red-800 transition">
            <span class="sr-only">Dismiss</span>
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
    @endif

    {{-- Search and Filters Bar --}}
    <div class="mb-5 rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 items-center">

            {{-- Search Input (spans 2 columns on large screens) --}}
            <div class="relative sm:col-span-2 lg:col-span-2">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search by name, SKU, or slug..."
                    class="w-full rounded-lg border border-gray-300 bg-white pl-9 pr-4 py-2 text-sm text-gray-900 placeholder:text-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder:text-gray-400">
            </div>

            {{-- Status Filter --}}
            <div>
                <select wire:model.live="statusFilter"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>

            {{-- Category Filter --}}
            <div>
                <select wire:model.live="categoryFilter"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    <option value="">All Categories</option>
                    @foreach ($categories as $parentCat)
                        <option value="{{ $parentCat->id }}">{{ $parentCat->name }}</option>
                        @if ($parentCat->children && $parentCat->children->count())
                            @foreach ($parentCat->children as $childCat)
                                <option value="{{ $childCat->id }}">&nbsp;&nbsp;↳ {{ $childCat->name }}</option>
                            @endforeach
                        @endif
                    @endforeach
                </select>
            </div>

            {{-- Stock Filter --}}
            <div>
                <select wire:model.live="stockFilter"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    <option value="">All Stock</option>
                    <option value="in_stock">In Stock</option>
                    <option value="out_of_stock">Out of Stock</option>
                </select>
            </div>

            {{-- Featured Filter --}}
            <div>
                <select wire:model.live="featuredFilter"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    <option value="">All Types</option>
                    <option value="featured">Featured Only</option>
                    <option value="standard">Standard Only</option>
                </select>
            </div>

        </div>

        {{-- Active Filters indicator and Reset button --}}
        @if ($this->hasActiveFilters)
            <div class="mt-3 pt-3 border-t border-gray-100 flex flex-wrap items-center justify-between gap-2 dark:border-gray-700">
                <div class="flex flex-wrap items-center gap-1.5 text-xs text-gray-500 dark:text-gray-400">
                    <span class="font-medium text-gray-700 dark:text-gray-300">Active filters:</span>
                    @if ($search)
                        <span class="inline-flex items-center gap-1 rounded bg-blue-50 px-2 py-0.5 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">
                            Search: "{{ $search }}"
                        </span>
                    @endif
                    @if ($statusFilter !== '')
                        <span class="inline-flex items-center gap-1 rounded bg-blue-50 px-2 py-0.5 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">
                            Status: {{ ucfirst($statusFilter) }}
                        </span>
                    @endif
                    @if ($categoryFilter !== '')
                        @php
                            $catModel = \App\Models\Category::find($categoryFilter);
                        @endphp
                        @if ($catModel)
                            <span class="inline-flex items-center gap-1 rounded bg-purple-50 px-2 py-0.5 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300">
                                Category: {{ $catModel->name }}
                            </span>
                        @endif
                    @endif
                    @if ($stockFilter !== '')
                        <span class="inline-flex items-center gap-1 rounded bg-emerald-50 px-2 py-0.5 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">
                            Stock: {{ $stockFilter === 'in_stock' ? 'In Stock' : 'Out of Stock' }}
                        </span>
                    @endif
                    @if ($featuredFilter !== '')
                        <span class="inline-flex items-center gap-1 rounded bg-amber-50 px-2 py-0.5 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">
                            {{ $featuredFilter === 'featured' ? 'Featured Only' : 'Standard Only' }}
                        </span>
                    @endif
                </div>

                <button type="button" wire:click="resetFilters"
                    class="inline-flex items-center gap-1 text-xs font-semibold text-red-600 hover:text-red-800 transition dark:text-red-400 dark:hover:text-red-300">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Reset Filters
                </button>
            </div>
        @endif
    </div>

    {{-- Bulk Actions Toolbar --}}
    @if (count($selected) > 0)
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-blue-200 bg-blue-50/80 p-3 shadow-xs dark:border-blue-900/50 dark:bg-blue-950/20">
        <div class="flex items-center gap-2">
            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-blue-600 text-xs font-bold text-white shadow-xs">
                {{ count($selected) }}
            </span>
            <span class="text-xs font-semibold text-gray-800 dark:text-gray-200">
                {{ count($selected) }} {{ \Illuminate\Support\Str::plural('product', count($selected)) }} selected
            </span>
            @if ($selectAll)
                <span class="rounded bg-blue-100 px-2 py-0.5 text-[11px] font-medium text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">
                    All on page
                </span>
            @endif
        </div>

        <div class="flex flex-wrap items-center gap-2">
            {{-- Bulk Activate --}}
            <button
                type="button"
                wire:click="bulkActivate"
                wire:loading.attr="disabled"
                class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-300 bg-white px-3 py-1.5 text-xs font-semibold text-emerald-700 shadow-2xs hover:bg-emerald-50 dark:border-emerald-800 dark:bg-gray-800 dark:text-emerald-400 dark:hover:bg-emerald-950/30 transition disabled:opacity-50"
            >
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                Activate
            </button>

            {{-- Bulk Deactivate --}}
            <button
                type="button"
                wire:click="bulkDeactivate"
                wire:loading.attr="disabled"
                class="inline-flex items-center gap-1.5 rounded-lg border border-amber-300 bg-white px-3 py-1.5 text-xs font-semibold text-amber-700 shadow-2xs hover:bg-amber-50 dark:border-amber-800 dark:bg-gray-800 dark:text-amber-400 dark:hover:bg-amber-950/30 transition disabled:opacity-50"
            >
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                </svg>
                Deactivate
            </button>

            {{-- Bulk Assign Category --}}
            <button
                type="button"
                wire:click="openBulkCategoryModal"
                wire:loading.attr="disabled"
                class="inline-flex items-center gap-1.5 rounded-lg border border-purple-300 bg-white px-3 py-1.5 text-xs font-semibold text-purple-700 shadow-2xs hover:bg-purple-50 dark:border-purple-800 dark:bg-gray-800 dark:text-purple-400 dark:hover:bg-purple-950/30 transition disabled:opacity-50"
            >
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                </svg>
                Assign Category
            </button>

            {{-- Bulk Delete --}}
            <button
                type="button"
                wire:click="confirmBulkDelete"
                wire:loading.attr="disabled"
                class="inline-flex items-center gap-1.5 rounded-lg border border-red-300 bg-white px-3 py-1.5 text-xs font-semibold text-red-700 shadow-2xs hover:bg-red-50 dark:border-red-800 dark:bg-gray-800 dark:text-red-400 dark:hover:bg-red-950/30 transition disabled:opacity-50"
            >
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                Delete Selected
            </button>

            <span class="h-4 w-px bg-gray-300 dark:bg-gray-700"></span>

            {{-- Deselect All --}}
            <button
                type="button"
                wire:click="clearSelection"
                class="text-xs font-medium text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200 transition"
            >
                Deselect all
            </button>
        </div>
    </div>
    @endif

    {{-- Products Table --}}
    <div class="overflow-hidden rounded-lg bg-white shadow-sm">

        <div class="overflow-x-auto">

            <table class="min-w-full divide-y divide-gray-200">

                <thead class="bg-gray-50">

                    <tr>

                        <th class="w-10 px-4 py-3 text-center">
                            <input
                                type="checkbox"
                                wire:model.live="selectAll"
                                class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer"
                                title="Select all on this page"
                            />
                        </th>

                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Product
                        </th>

                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                            SKU
                        </th>

                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Price
                        </th>

                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Categories
                        </th>

                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Status
                        </th>

                        <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Actions
                        </th>

                    </tr>

                </thead>


                <tbody class="divide-y divide-gray-200 bg-white">

                    @forelse ($products as $product)

                    <tr wire:key="product-{{ $product->id }}" class="transition {{ in_array((string)$product->id, array_map('strval', $selected), true) ? 'bg-blue-50/50 dark:bg-blue-900/10' : 'hover:bg-gray-50/50' }}">

                        {{-- Checkbox --}}
                        <td class="w-10 px-4 py-4 text-center">
                            <input
                                type="checkbox"
                                wire:model.live="selected"
                                value="{{ $product->id }}"
                                class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer"
                            />
                        </td>

                        {{-- Product --}}
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 shrink-0 overflow-hidden rounded-lg border border-gray-200 bg-gray-50 flex items-center justify-center">
                                    @php
                                        $primaryImg = $product->images->firstWhere('is_primary', true) ?? $product->images->first();
                                    @endphp
                                    @if ($primaryImg)
                                        <img src="{{ Storage::disk('public')->url($primaryImg->path) }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                                    @else
                                        <svg class="h-5 w-5 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="font-medium text-gray-900 truncate">
                                        {{ $product->name }}
                                    </div>
                                    @php
                                        $lowStockLimit = (int) store_setting('low_stock_threshold', 5);
                                    @endphp
                                    <div class="text-xs flex items-center gap-1.5 text-gray-500">
                                        <span>{{ $product->variants->count() }} {{ \Illuminate\Support\Str::plural('variant', $product->variants->count()) }}</span>
                                        <span>·</span>
                                        @if ($product->total_stock <= 0)
                                            <span class="font-medium text-red-600">Out of Stock</span>
                                        @elseif ($product->total_stock <= $lowStockLimit)
                                            <span class="font-medium text-amber-600">Low Stock: {{ $product->total_stock }}</span>
                                        @else
                                            <span>Stock: {{ $product->total_stock }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>


                        {{-- SKU --}}
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">

                            {{ $product->sku }}

                        </td>


                        {{-- Price --}}
                        <td class="whitespace-nowrap px-6 py-4">

                            <div class="text-sm font-semibold text-gray-900">
                                {{ format_price($product->price) }}
                            </div>

                            @if ($product->compare_at_price)

                            <div class="text-xs text-gray-400 line-through">
                                {{ format_price($product->compare_at_price) }}
                            </div>

                            @endif

                        </td>


                        {{-- Categories --}}
                        <td class="px-6 py-4">

                            @if ($product->categories->count())

                            <div class="flex flex-wrap gap-1">

                                @foreach ($product->categories as $category)

                                <span class="rounded-full bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700">
                                    {{ $category->name }}
                                </span>

                                @endforeach

                            </div>

                            @else

                            <span class="text-sm text-gray-400">
                                —
                            </span>

                            @endif

                        </td>


                        {{-- Status --}}
                        <td class="whitespace-nowrap px-6 py-4">

                            @if ($product->is_active)

                            <button wire:click="toggleStatus({{ $product->id }})"
                                class="rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-700 hover:bg-green-200">
                                Active
                            </button>

                            @else

                            <button wire:click="toggleStatus({{ $product->id }})"
                                class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-600 hover:bg-gray-200">
                                Inactive
                            </button>

                            @endif

                        </td>


                        {{-- Actions --}}
                        <td class="whitespace-nowrap px-6 py-4 text-right">

                            <a href="{{ route('admin.products.edit', $product->id) }}"
                                class="mr-3 text-sm font-medium text-blue-600 hover:text-blue-800">
                                Edit
                            </a>

                            <button wire:click="confirmDelete({{ $product->id }})"
                                class="text-sm font-medium text-red-600 hover:text-red-800">
                                Delete
                            </button>

                        </td>

                    </tr>

                    @empty

                    <tr>

                        <td colspan="7" class="px-6 py-12 text-center">

                            <div class="flex flex-col items-center justify-center">

                                <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-gray-100">

                                    <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                    </svg>

                                </div>

                                <p class="text-sm font-medium text-gray-700">
                                    No products found
                                </p>

                                <p class="mt-1 text-xs text-gray-400">
                                    Start by adding your first product.
                                </p>

                            </div>

                        </td>

                    </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        {{-- Pagination --}}
        @if ($products->hasPages())

        <div class="border-t border-gray-200 px-6 py-4">
            {{ $products->links() }}
        </div>

        @endif

    </div>



    {{-- Create / Edit Modal --}}
    @if ($showModal)
    <div class="fixed inset-0 z-[9999] flex items-start justify-center bg-black/50 px-4 pt-[80px] pb-4">
        <div class="flex w-full max-w-3xl max-h-[calc(100vh-96px)] flex-col overflow-hidden rounded-2xl bg-white shadow-2xl"
            wire:click.outside="closeModal">

            <div class="border-b border-gray-200 bg-white px-6 py-5">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900">
                            {{ $editingId ? 'Edit Product' : 'Create Product' }}
                        </h2>

                        <p class="mt-1 text-sm text-gray-500">
                            {{ $editingId ? 'Update the details of this product.'  : 'Add a new product to your store.'}}
                    </div>

                    <button type="button" wire:click="closeModal"
                        class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="min-h-0 flex-1 overflow-y-auto bg-gray-50/50 px-6 py-6">

                <div class="space-y-5">

                    {{-- Basic Information --}}
                    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">

                        <div class="mb-5">
                            <h3 class="text-sm font-semibold text-gray-900">
                                Basic Information
                            </h3>

                            <p class="mt-1 text-xs text-gray-500">
                                Add the basic details for this product.
                            </p>
                        </div>

                        <div class="grid grid-cols-1 gap-x-5 gap-y-4 md:grid-cols-2">

                            {{-- Product Name --}}
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700">
                                    Product Name <span class="text-red-500">*</span>
                                </label>

                                <input type="text" wire:model.live="name" placeholder="Enter product name"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10">

                                @error('name')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- SKU --}}
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700">
                                    SKU <span class="text-red-500">*</span>
                                </label>

                                <input type="text" wire:model="sku" placeholder="e.g. PROD-001"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10">

                                @error('sku')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Slug --}}
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700">
                                    Slug <span class="text-red-500">*</span>
                                </label>

                                <input type="text" wire:model="slug" placeholder="product-name"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10">

                                @error('slug')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Product Type --}}
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700">
                                    Product Type
                                </label>

                                <input type="text" wire:model="product_type" placeholder="e.g. Clothing"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10">

                                @error('product_type')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Vendor --}}
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700">
                                    Vendor
                                </label>

                                <input type="text" wire:model="vendor" placeholder="Enter vendor or brand"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10">

                                @error('vendor')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Sort Order --}}
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700">
                                    Sort Order
                                </label>

                                <input type="number" min="0" wire:model="sort_order" placeholder="0"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10">

                                @error('sort_order')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                        </div>

                        {{-- Description --}}
                        <div class="mt-4">
                            <label class="mb-1.5 block text-sm font-medium text-gray-700">
                                Description
                            </label>

                            <textarea wire:model="description" rows="4" placeholder="Enter product description..."
                                class="w-full resize-none rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10"></textarea>

                            @error('description')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                    </div>
                    {{-- Pricing --}}
                    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">

                        <div class="mb-5">
                            <h3 class="text-sm font-semibold text-gray-900">
                                Pricing
                            </h3>

                            <p class="mt-1 text-xs text-gray-500">
                                Set the product selling price and comparison price.
                            </p>
                        </div>

                        <div class="grid grid-cols-1 gap-x-5 gap-y-4 md:grid-cols-2">

                            {{-- Price --}}
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700">
                                    Price <span class="text-red-500">*</span>
                                </label>

                                <div class="relative">
                                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-gray-400">
                                        {{ currency_symbol() }}
                                    </span>

                                    <input type="number" step="0.01" min="0" wire:model="price" placeholder="0.00"
                                        class="w-full rounded-lg border border-gray-300 bg-white py-2.5 pl-8 pr-3.5 text-sm text-gray-900 placeholder:text-gray-400 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10">
                                </div>

                                @error('price')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Compare At Price --}}
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700">
                                    Compare-at Price
                                </label>

                                <div class="relative">
                                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-gray-400">
                                        {{ currency_symbol() }}
                                    </span>

                                    <input type="number" step="0.01" min="0" wire:model="compare_at_price"
                                        placeholder="0.00"
                                        class="w-full rounded-lg border border-gray-300 bg-white py-2.5 pl-8 pr-3.5 text-sm text-gray-900 placeholder:text-gray-400 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10">
                                </div>

                                @error('compare_at_price')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                        </div>

                    </div>
                    {{-- Categories --}}
                    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">

                        <div class="mb-5">
                            <h3 class="text-sm font-semibold text-gray-900">
                                Categories
                            </h3>

                            <p class="mt-1 text-xs text-gray-500">
                                Select the categories this product belongs to.
                            </p>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700">
                                Product Categories
                            </label>

                            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white">

                                @forelse ($categories as $category)

                                <div wire:key="category-group-{{ $category->id }}" x-data="{ open: false }"
                                    class="border-b border-gray-100 last:border-b-0">

                                    {{-- Parent Category --}}
                                    <div class="flex items-center justify-between gap-3 px-4 py-3 hover:bg-gray-50">

                                        <label class="flex min-w-0 flex-1 cursor-pointer items-center gap-3">

                                            <input type="checkbox" value="{{ $category->id }}"
                                                wire:model="selectedCategories"
                                                class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-2 focus:ring-blue-500/20">

                                            <span class="text-sm font-medium text-gray-800">
                                                {{ $category->name }}
                                            </span>

                                        </label>

                                        @if ($category->children->count())

                                        <button type="button" @click="open = !open"
                                            class="flex h-8 w-8 items-center justify-center rounded-md text-gray-400 transition hover:bg-gray-100 hover:text-gray-600"
                                            :aria-expanded="open">

                                            <svg class="h-4 w-4 transition-transform duration-200"
                                                :class="{ 'rotate-90': open }" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 5l7 7-7 7" />
                                            </svg>

                                        </button>

                                        @endif

                                    </div>


                                    {{-- Subcategories --}}
                                    @if ($category->children->count())

                                    <div x-show="open" x-collapse class="border-t border-gray-100 bg-gray-50">

                                        @foreach ($category->children as $child)

                                        <label wire:key="subcategory-{{ $child->id }}"
                                            class="flex cursor-pointer items-center gap-3 px-4 py-3 pl-11 transition hover:bg-white">

                                            <input type="checkbox" value="{{ $child->id }}"
                                                wire:model="selectedCategories"
                                                class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-2 focus:ring-blue-500/20">

                                            <div class="flex min-w-0 items-center gap-2">

                                                <span class="text-gray-300">

                                                </span>

                                                <span class="text-sm text-gray-700">
                                                    {{ $child->name }}
                                                </span>

                                            </div>

                                        </label>

                                        @endforeach

                                    </div>

                                    @endif

                                </div>

                                @empty

                                <div class="px-4 py-10 text-center">

                                    <div
                                        class="mx-auto mb-3 flex h-10 w-10 items-center justify-center rounded-full bg-gray-100">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M12 4v16m8-8H4" />
                                        </svg>
                                    </div>

                                    <p class="text-sm font-medium text-gray-600">
                                        No categories available
                                    </p>

                                    <p class="mt-1 text-xs text-gray-400">
                                        Create a category before assigning it to a product.
                                    </p>

                                </div>

                                @endforelse

                            </div>


                            {{-- Selection Summary --}}
                            @if (count($selectedCategories))

                            <div class="mt-3 flex items-center justify-between">

                                <p class="text-xs text-gray-500">
                                    {{ count($selectedCategories) }}
                                    {{ count($selectedCategories) === 1 ? 'category' : 'categories' }}
                                    selected
                                </p>

                                <button type="button" wire:click="$set('selectedCategories', [])"
                                    class="text-xs font-medium text-blue-600 transition hover:text-blue-800">
                                    Clear selection
                                </button>

                            </div>

                            @else

                            <p class="mt-2 text-xs text-gray-400">
                                Select one or more categories for this product.
                            </p>

                            @endif


                            @error('selectedCategories')
                            <p class="mt-1 text-xs text-red-600">
                                {{ $message }}
                            </p>
                            @enderror

                        </div>

                    </div>
                    {{-- Options & Variants --}}
                    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">

                        <div class="mb-5 flex items-start justify-between gap-4">
                            <div>
                                <h3 class="text-sm font-semibold text-gray-900">
                                    Options & Variants
                                </h3>

                                <p class="mt-1 text-xs text-gray-500">
                                    Add product options such as size, color, material, or other variations.
                                </p>
                            </div>

                            <button type="button" wire:click="addOption"
                                class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-3.5 py-2 text-sm font-medium text-white transition hover:bg-blue-700">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4" />
                                </svg>

                                Add Option
                            </button>
                        </div>

                        @if (count($productOptions))

                        <div class="space-y-4">

                            @foreach ($productOptions as $optionIndex => $option)

                            <div wire:key="product-option-{{ $optionIndex }}"
                                class="rounded-xl border border-gray-200 bg-gray-50 p-4">

                                {{-- Option Header --}}
                                <div class="mb-4 flex items-center justify-between gap-4">

                                    <div class="flex-1">
                                        <label class="mb-1.5 block text-sm font-medium text-gray-700">
                                            Option Name
                                        </label>

                                        <input type="text" wire:model="productOptions.{{ $optionIndex }}.name"
                                            placeholder="e.g. Size, Color"
                                            class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10">
                                    </div>

                                    <button type="button" wire:click="removeOption({{ $optionIndex }})"
                                        class="mt-6 inline-flex h-10 w-10 items-center justify-center rounded-lg text-red-500 transition hover:bg-red-50 hover:text-red-700"
                                        title="Remove option">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3m-7 0h10" />
                                        </svg>
                                    </button>

                                </div>

                                {{-- Option Values --}}
                                <div>

                                    <label class="mb-2 block text-sm font-medium text-gray-700">
                                        Values
                                    </label>

                                    <div class="space-y-2">

                                        @foreach ($option['values'] as $valueIndex => $value)

                                        <div wire:key="option-{{ $optionIndex }}-value-{{ $valueIndex }}"
                                            class="flex items-center gap-2">

                                            <input type="text"
                                                wire:model="productOptions.{{ $optionIndex }}.values.{{ $valueIndex }}"
                                                placeholder="e.g. Small"
                                                class="flex-1 rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10">

                                            @if (count($option['values']) > 1)
                                            <button type="button"
                                                wire:click="removeOptionValue({{ $optionIndex }}, {{ $valueIndex }})"
                                                class="inline-flex h-10 w-10 items-center justify-center rounded-lg text-gray-400 transition hover:bg-red-50 hover:text-red-600">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                            @endif

                                        </div>

                                        @endforeach

                                    </div>

                                    <button type="button" wire:click="addOptionValue({{ $optionIndex }})"
                                        class="mt-3 inline-flex items-center gap-1.5 text-sm font-medium text-blue-600 hover:text-blue-800">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 4v16m8-8H4" />
                                        </svg>

                                        Add Value
                                    </button>

                                </div>

                            </div>

                            @endforeach

                        </div>

                        @else

                        <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50 px-6 py-10 text-center">

                            <div
                                class="mx-auto mb-3 flex h-11 w-11 items-center justify-center rounded-full bg-white shadow-sm">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M12 4v16m8-8H4" />
                                </svg>
                            </div>

                            <p class="text-sm font-medium text-gray-700">
                                No product options added
                            </p>

                            <p class="mt-1 text-xs text-gray-400">
                                Add options such as Size or Color to create product variants.
                            </p>

                        </div>

                        @endif

                    </div>
                    {{-- Generate Variants --}}
                    @if (count($productOptions))

                    <div class="mt-4 flex justify-end">
                        <button type="button" wire:click="generateVariants"
                            class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-blue-700">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4" />
                            </svg>

                            Generate Variants
                        </button>
                    </div>

                    @endif
                    {{-- Product Variants --}}
                    @if (count($generatedVariants))

                    <div class="mt-5 rounded-xl border border-gray-200 bg-white p-5 shadow-sm">

                        <div class="mb-5">
                            <h3 class="text-sm font-semibold text-gray-900">
                                Product Variants
                            </h3>

                            <p class="mt-1 text-xs text-gray-500">
                                Review and configure the generated variants.
                            </p>
                        </div>

                        <div class="overflow-x-auto rounded-lg border border-gray-200">

                            <table class="min-w-full divide-y divide-gray-200">

                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                            Variant
                                        </th>

                                        <th
                                            class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                            SKU
                                        </th>

                                        <th
                                            class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                            Price
                                        </th>

                                        <th
                                            class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                            Compare-at
                                        </th>

                                        <th
                                            class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                            Barcode
                                        </th>

                                        <th
                                            class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                            Quantity
                                        </th>

                                        <th
                                            class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">
                                            Reserved
                                        </th>

                                        <th
                                            class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">
                                            Available
                                        </th>

                                        <th
                                            class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">
                                            Active
                                        </th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-gray-200 bg-white">

                                    @foreach ($generatedVariants as $index => $variant)

                                    <tr wire:key="generated-variant-{{ $index }}">

                                        <td class="whitespace-nowrap px-4 py-3">
                                            <span class="text-sm font-medium text-gray-900">
                                                {{ $variant['title'] }}
                                            </span>
                                        </td>

                                        <td class="px-4 py-3">
                                            <input type="text" wire:model="generatedVariants.{{ $index }}.sku"
                                                placeholder="SKU"
                                                class="w-32 rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10">
                                        </td>

                                        <td class="px-4 py-3">
                                            <input type="number" step="0.01" min="0"
                                                wire:model="generatedVariants.{{ $index }}.price"
                                                class="w-28 rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10">
                                        </td>

                                        <td class="px-4 py-3">
                                            <input type="number" step="0.01" min="0"
                                                wire:model="generatedVariants.{{ $index }}.compare_at_price"
                                                class="w-28 rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10">
                                        </td>

                                        <td class="px-4 py-3">
                                            <input type="text" wire:model="generatedVariants.{{ $index }}.barcode"
                                                placeholder="Barcode"
                                                class="w-32 rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10">
                                        </td>

                                        <td class="px-4 py-3">
                                            <input type="number" min="0"
                                                wire:model="generatedVariants.{{ $index }}.quantity"
                                                class="w-24 rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10">
                                        </td>

                                        <td class="px-4 py-3 text-center">
                                            <span class="text-sm text-gray-600">
                                                {{ $variant['reserved_quantity'] ?? 0 }}
                                            </span>
                                        </td>

                                        <td class="px-4 py-3 text-center">
                                            @php
                                            $available =
                                            ($variant['quantity'] ?? 0)
                                            - ($variant['reserved_quantity'] ?? 0);
                                            @endphp

                                            <span class="text-sm font-medium
                                                        {{ $available > 0 ? 'text-green-600' : 'text-red-600' }}">
                                                {{ $available }}
                                            </span>
                                        </td>

                                        <td class="px-4 py-3 text-center">
                                            <input type="checkbox" wire:model="generatedVariants.{{ $index }}.is_active"
                                                class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                        </td>

                                    </tr>

                                    @endforeach

                                </tbody>

                            </table>

                        </div>

                    </div>

                    @endif


                    @if (count($existingProductImages))
                    <div class="mb-5">
                        <div class="mb-3">
                            <h4 class="text-sm font-medium text-gray-700">
                                Existing Images
                            </h4>
                        </div>

                        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4">
                            @foreach ($existingProductImages as $image)
                            <div wire:key="existing-product-image-{{ $image['id'] }}"
                                class="group relative overflow-hidden rounded-lg border border-gray-200 bg-gray-100">
                                <img src="{{ Storage::disk('public')->url($image['path']) }}"
                                    class="aspect-square w-full object-cover"
                                    alt="{{ $image['alt_text'] ?? 'Product image' }}">

                                {{-- Primary Badge --}}
                                @if ($image['is_primary'])
                                <span
                                    class="absolute left-2 top-2 rounded-md bg-blue-600 px-2 py-1 text-[10px] font-semibold text-white">
                                    Primary
                                </span>
                                @endif

                                {{-- Image Actions --}}
                                <div class="absolute bottom-2 left-2 right-2 flex items-center justify-between">

                                    {{-- Reorder --}}
                                    <div class="flex items-center gap-1">

                                        {{-- Move Up --}}
                                        <button type="button" wire:click="moveImageUp({{ $image['id'] }})"
                                            title="Move up"
                                            class="flex h-8 w-8 items-center justify-center rounded-full bg-white/95 text-gray-600 shadow-md transition hover:bg-gray-100 hover:text-gray-900">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 15l7-7 7 7" />
                                            </svg>
                                        </button>

                                        {{-- Move Down --}}
                                        <button type="button" wire:click="moveImageDown({{ $image['id'] }})"
                                            title="Move down"
                                            class="flex h-8 w-8 items-center justify-center rounded-full bg-white/95 text-gray-600 shadow-md transition hover:bg-gray-100 hover:text-gray-900">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>

                                    </div>

                                    {{-- Set Primary --}}
                                    <button type="button" wire:click="setPrimaryImage({{ $image['id'] }})"
                                        title="{{ $image['is_primary'] ? 'Primary Image' : 'Set as Primary' }}"
                                        class="flex h-8 w-8 items-center justify-center rounded-full bg-white/95 shadow-md transition hover:bg-blue-50">
                                        <svg class="h-4 w-4 {{ $image['is_primary'] ? 'text-blue-600' : 'text-gray-500' }}"
                                            fill="{{ $image['is_primary'] ? 'currentColor' : 'none' }}"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                d="M12 3l2.78 5.63 6.22.9-4.5 4.39 1.06 6.2L12 17.2l-5.56 2.92 1.06-6.2L3 9.53l6.22-.9L12 3z" />
                                        </svg>
                                    </button>

                                    {{-- Remove --}}
                                    <button type="button" wire:click="removeExistingProductImage({{ $image['id'] }})"
                                        title="Remove image"
                                        class="flex h-8 w-8 items-center justify-center rounded-full bg-white/95 text-red-500 shadow-md transition hover:bg-red-50 hover:text-red-600">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                d="M6 7h12M9 7V5h6v2m-7 0l.7 12h6.6L16 7M10 11v5m4-5v5" />
                                        </svg>
                                    </button>

                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                    {{-- Product Images --}}
                    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">

                        <div class="mb-5">
                            <h3 class="text-sm font-semibold text-gray-900">
                                Product Images
                            </h3>

                            <p class="mt-1 text-xs text-gray-500">
                                Upload product images. The first image will be used as the primary image.
                            </p>
                        </div>

                        {{-- Upload --}}
                        <div
                            class="rounded-xl border-2 border-dashed border-gray-300 bg-gray-50 p-6 text-center transition hover:border-blue-400 hover:bg-blue-50/30">

                            <input type="file" wire:model="newProductImages" multiple
                                accept="image/jpeg,image/png,image/webp" class="hidden" id="product-images">

                            <label for="product-images" class="cursor-pointer">

                                <div
                                    class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-white shadow-sm">
                                    <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M3 16.5V19a2 2 0 002 2h14a2 2 0 002-2v-2.5M16 8l-4-4m0 0L8 8m4-4v12" />
                                    </svg>
                                </div>

                                <p class="text-sm font-medium text-gray-700">
                                    Click to upload product images
                                </p>

                                <p class="mt-1 text-xs text-gray-400">
                                    JPG, PNG or WebP · Maximum 5MB per image
                                </p>

                            </label>

                        </div>

                        @error('productImages.*')
                        <p class="mt-2 text-xs text-red-600">
                            {{ $message }}
                        </p>
                        @enderror


                        {{-- Preview --}}
                        @if (count($productImages))

                        <div class="mt-5">

                            <div class="mb-3 flex items-center justify-between">
                                <h4 class="text-sm font-medium text-gray-700">
                                    Selected Images
                                </h4>

                                <span class="text-xs text-gray-400">
                                    {{ count($productImages) }} selected
                                </span>
                            </div>

                            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4">

                                @foreach ($productImages as $index => $image)

                                <div wire:key="uploaded-product-image-{{ $index }}"
                                    class="group relative overflow-hidden rounded-lg border border-gray-200 bg-gray-100">

                                    <img src="{{ $image->temporaryUrl() }}" class="aspect-square w-full object-cover"
                                        alt="Product preview">


                                    <button type="button" wire:click="removeProductImage({{ $index }})"
                                        class="absolute right-2 top-2 flex h-7 w-7 items-center justify-center rounded-full bg-white/90 text-gray-500 shadow-sm transition hover:bg-red-50 hover:text-red-600">
                                        ×
                                    </button>

                                </div>

                                @endforeach

                            </div>

                        </div>

                        @endif

                    </div>

                    {{-- Status --}}
                    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">

                        <div class="mb-5">
                            <h3 class="text-sm font-semibold text-gray-900">
                                Product Status
                            </h3>

                            <p class="mt-1 text-xs text-gray-500">
                                Control the visibility and featured status of this product.
                            </p>
                        </div>

                        <div class="space-y-4">

                            {{-- Active --}}
                            <label
                                class="flex cursor-pointer items-center justify-between gap-4 rounded-lg border border-gray-200 px-4 py-4">

                                <div>
                                    <p class="text-sm font-medium text-gray-900">
                                        Active
                                    </p>

                                    <p class="mt-0.5 text-xs text-gray-500">
                                        Active products are available on the storefront.
                                    </p>
                                </div>

                                <input type="checkbox" wire:model="is_active"
                                    class="h-5 w-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500">

                            </label>

                            {{-- Featured --}}
                            <label
                                class="flex cursor-pointer items-center justify-between gap-4 rounded-lg border border-gray-200 px-4 py-4">

                                <div>
                                    <p class="text-sm font-medium text-gray-900">
                                        Featured Product
                                    </p>

                                    <p class="mt-0.5 text-xs text-gray-500">
                                        Mark this product as featured.
                                    </p>
                                </div>

                                <input type="checkbox" wire:model="is_featured"
                                    class="h-5 w-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500">

                            </label>

                        </div>

                    </div>

                    {{-- SEO --}}
                    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">

                        <div class="mb-5">
                            <h3 class="text-sm font-semibold text-gray-900">
                                SEO Settings
                            </h3>

                            <p class="mt-1 text-xs text-gray-500">
                                Configure how this product appears in search engines.
                            </p>
                        </div>

                        <div class="space-y-4">

                            {{-- SEO Title --}}
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700">
                                    SEO Title
                                </label>

                                <input type="text" wire:model="seo_title" maxlength="255" placeholder="Enter SEO title"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10">

                                <p class="mt-1 text-xs text-gray-400">
                                    Recommended: 50–60 characters.
                                </p>

                                @error('seo_title')
                                <p class="mt-1 text-xs text-red-600">
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>

                            {{-- SEO Description --}}
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700">
                                    SEO Description
                                </label>

                                <textarea wire:model="seo_description" rows="3" maxlength="500"
                                    placeholder="Enter SEO description"
                                    class="w-full resize-none rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10"></textarea>

                                <p class="mt-1 text-xs text-gray-400">
                                    Recommended: 150–160 characters.
                                </p>

                                @error('seo_description')
                                <p class="mt-1 text-xs text-red-600">
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>

                        </div>

                    </div>

                    {{-- Form Actions --}}
                    <div class="flex items-center justify-end gap-3 border-t border-gray-200 bg-white px-6 py-4">
                        <button type="button" wire:click="closeModal"
                            class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 focus:outline-none focus:ring-4 focus:ring-gray-200">
                            Cancel
                        </button>

                        <button type="button" wire:click="save" wire:loading.attr="disabled" wire:target="save"
                            class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-500/30 disabled:cursor-not-allowed disabled:opacity-60">
                            <span wire:loading.remove wire:target="save">
                                {{ $editingId ? 'Update Product' : 'Save Product' }}
                            </span>

                            <span wire:loading wire:target="save" class="inline-flex items-center gap-2">
                                <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4" />
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                                </svg>
                                Saving...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif



    {{-- Delete Confirmation Modal --}}
    @if ($showDeleteModal)

    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">

        <div class="w-full max-w-md rounded-xl bg-white shadow-xl">

            <div class="p-6">

                <h2 class="text-lg font-semibold text-gray-900">
                    Delete Product
                </h2>

                <p class="mt-2 text-sm text-gray-600">
                    Are you sure you want to delete this Product ?
                    This action cannot be undone.
                </p>

            </div>

            <div class="flex justify-end gap-3 border-t bg-gray-50 px-6 py-4">

                <button type="button" wire:click="closeDeleteModal"
                    class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Cancel
                </button>

                <button type="button" wire:click="delete"
                    class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">
                    Delete
                </button>

            </div>

        </div>

    </div>

    @endif
 
 
    {{-- Import Modal --}}
    @if ($showImportModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
        <div class="w-full max-w-lg rounded-xl bg-white shadow-xl">
            <div class="flex items-center justify-between border-b px-6 py-4">
                <div class="flex items-center gap-2.5">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">
                            Import Products
                        </h2>
                        <p class="text-xs text-gray-500">
                            Bulk create or update products via CSV
                        </p>
                    </div>
                </div>

                <button type="button" wire:click="closeImportModal" class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="space-y-4 p-6">
                <p class="text-sm text-gray-600">
                    Upload a CSV file with your product catalog. Products with matching SKUs will be updated automatically, and new SKUs will be created.
                </p>

                <div class="flex items-center justify-between rounded-lg border border-blue-100 bg-blue-50/70 p-3 text-xs text-blue-800">
                    <div class="flex items-center gap-2">
                        <svg class="h-4 w-4 shrink-0 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Need a pre-formatted template?</span>
                    </div>

                    <button type="button" wire:click="exportSample" class="font-semibold text-blue-700 underline hover:text-blue-900">
                        Download Sample CSV
                    </button>
                </div>

                {{-- File Input --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Select CSV File
                    </label>

                    <input type="file" wire:model="importFile" accept=".csv,text/csv,text/plain"
                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-gray-300 rounded-lg cursor-pointer focus:outline-none" />

                    <div wire:loading wire:target="importFile" class="mt-2 text-xs text-blue-600 flex items-center gap-1.5">
                        <svg class="h-3.5 w-3.5 animate-spin" viewBox="0 0 24 24" fill="none">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                        </svg>
                        Uploading file...
                    </div>

                    @error('importFile')
                    <p class="mt-1.5 text-xs text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Parsing Errors --}}
                @if (!empty($importErrors))
                <div class="rounded-lg border border-red-200 bg-red-50 p-3 max-h-48 overflow-y-auto">
                    <h3 class="text-xs font-semibold text-red-800 mb-1.5 flex items-center gap-1.5">
                        <svg class="h-4 w-4 shrink-0 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        Errors found in CSV (no products imported):
                    </h3>
                    <ul class="list-disc pl-5 text-xs text-red-700 space-y-0.5">
                        @foreach ($importErrors as $err)
                        <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
            </div>

            <div class="flex items-center justify-end gap-3 border-t bg-gray-50 px-6 py-4 rounded-b-xl">
                <button type="button" wire:click="closeImportModal"
                    class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 shadow-sm">
                    Cancel
                </button>

                <button type="button" wire:click="import" wire:loading.attr="disabled"
                    class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 shadow-sm disabled:opacity-50">
                    <span wire:loading.remove wire:target="import">
                        Import Products
                    </span>
                    <span wire:loading wire:target="import" class="inline-flex items-center gap-1.5">
                        <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                        </svg>
                        Importing...
                    </span>
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- Bulk Category & Status Assignment Modal --}}
    @if ($showBulkCategoryModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
        <div class="w-full max-w-lg rounded-xl bg-white shadow-xl dark:bg-gray-800">
            <div class="flex items-center justify-between border-b px-6 py-4 dark:border-gray-700">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-purple-100 text-purple-600 dark:bg-purple-900/40 dark:text-purple-300">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Assign Category & Update Status
                        </h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Applying to {{ count($selected) }} {{ \Illuminate\Support\Str::plural('product', count($selected)) }} selected
                        </p>
                    </div>
                </div>
                <button type="button" wire:click="closeBulkCategoryModal" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-700 dark:hover:text-gray-300">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="max-h-[70vh] overflow-y-auto p-6 space-y-5">
                {{-- Categories Selection --}}
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-sm font-semibold text-gray-900 dark:text-white">
                            Select Categories <span class="text-red-500">*</span>
                        </label>
                        <span class="text-xs text-purple-600 font-medium dark:text-purple-400">
                            {{ count($bulkCategoryIds) }} selected
                        </span>
                    </div>

                    <div class="max-h-52 overflow-y-auto rounded-lg border border-gray-200 bg-gray-50/50 p-3 space-y-2 dark:border-gray-700 dark:bg-gray-900/40">
                        @forelse ($categories as $parentCat)
                            <div class="space-y-1">
                                <label class="flex items-center gap-2.5 rounded-md px-2 py-1.5 hover:bg-white dark:hover:bg-gray-800 cursor-pointer">
                                    <input type="checkbox" wire:model="bulkCategoryIds" value="{{ $parentCat->id }}"
                                        class="h-4 w-4 rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $parentCat->name }}</span>
                                </label>

                                @if ($parentCat->children && $parentCat->children->count())
                                    <div class="ml-6 space-y-1 border-l-2 border-gray-200 pl-2 dark:border-gray-700">
                                        @foreach ($parentCat->children as $childCat)
                                            <label class="flex items-center gap-2 rounded-md px-2 py-1 hover:bg-white dark:hover:bg-gray-800 cursor-pointer">
                                                <input type="checkbox" wire:model="bulkCategoryIds" value="{{ $childCat->id }}"
                                                    class="h-3.5 w-3.5 rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                                                <span class="text-xs text-gray-700 dark:text-gray-300">{{ $childCat->name }}</span>
                                                <span class="text-[10px] text-gray-400">sub</span>
                                            </label>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @empty
                            <p class="text-xs text-gray-500 text-center py-4">No active categories found.</p>
                        @endforelse
                    </div>

                    @error('bulkCategoryIds')
                        <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Category Assignment Mode --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2 dark:text-white">
                        Assignment Mode
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        <label class="relative flex cursor-pointer rounded-lg border p-3 shadow-xs focus:outline-none {{ $bulkCategoryMode === 'append' ? 'border-purple-600 bg-purple-50/50 dark:border-purple-500 dark:bg-purple-950/20' : 'border-gray-200 dark:border-gray-700' }}">
                            <input type="radio" wire:model.live="bulkCategoryMode" value="append" class="sr-only">
                            <div class="flex flex-col">
                                <span class="text-xs font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-1.5">
                                    <span class="h-2 w-2 rounded-full {{ $bulkCategoryMode === 'append' ? 'bg-purple-600' : 'bg-gray-300' }}"></span>
                                    Add to Existing
                                </span>
                                <span class="text-[11px] text-gray-500 mt-0.5">Keeps current categories & appends selected</span>
                            </div>
                        </label>

                        <label class="relative flex cursor-pointer rounded-lg border p-3 shadow-xs focus:outline-none {{ $bulkCategoryMode === 'replace' ? 'border-purple-600 bg-purple-50/50 dark:border-purple-500 dark:bg-purple-950/20' : 'border-gray-200 dark:border-gray-700' }}">
                            <input type="radio" wire:model.live="bulkCategoryMode" value="replace" class="sr-only">
                            <div class="flex flex-col">
                                <span class="text-xs font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-1.5">
                                    <span class="h-2 w-2 rounded-full {{ $bulkCategoryMode === 'replace' ? 'bg-purple-600' : 'bg-gray-300' }}"></span>
                                    Replace Existing
                                </span>
                                <span class="text-[11px] text-gray-500 mt-0.5">Replaces all categories with selected</span>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- Status Update Option --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2 dark:text-white">
                        Update Status (Optional)
                    </label>
                    <div class="grid grid-cols-3 gap-2">
                        <label class="flex items-center justify-center gap-1.5 rounded-lg border px-3 py-2 text-xs font-medium cursor-pointer transition {{ $bulkStatusMode === 'keep' ? 'border-gray-900 bg-gray-900 text-white dark:border-white dark:bg-white dark:text-gray-900' : 'border-gray-200 text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300' }}">
                            <input type="radio" wire:model.live="bulkStatusMode" value="keep" class="sr-only">
                            <span>Keep Current</span>
                        </label>

                        <label class="flex items-center justify-center gap-1.5 rounded-lg border px-3 py-2 text-xs font-medium cursor-pointer transition {{ $bulkStatusMode === 'active' ? 'border-emerald-600 bg-emerald-600 text-white' : 'border-emerald-200 text-emerald-700 hover:bg-emerald-50 dark:border-emerald-900/50 dark:text-emerald-400' }}">
                            <input type="radio" wire:model.live="bulkStatusMode" value="active" class="sr-only">
                            <span>Set Active</span>
                        </label>

                        <label class="flex items-center justify-center gap-1.5 rounded-lg border px-3 py-2 text-xs font-medium cursor-pointer transition {{ $bulkStatusMode === 'inactive' ? 'border-amber-600 bg-amber-600 text-white' : 'border-amber-200 text-amber-700 hover:bg-amber-50 dark:border-amber-900/50 dark:text-amber-400' }}">
                            <input type="radio" wire:model.live="bulkStatusMode" value="inactive" class="sr-only">
                            <span>Set Inactive</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3 border-t bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-800/80">
                <button type="button" wire:click="closeBulkCategoryModal"
                    class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                    Cancel
                </button>

                <button type="button" wire:click="bulkAssignCategory" wire:loading.attr="disabled"
                    class="rounded-lg bg-purple-600 px-4 py-2 text-sm font-medium text-white hover:bg-purple-700 shadow-sm disabled:opacity-50">
                    <span wire:loading.remove wire:target="bulkAssignCategory">Apply Changes</span>
                    <span wire:loading wire:target="bulkAssignCategory" class="inline-flex items-center gap-1.5">
                        <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                        </svg>
                        Applying...
                    </span>
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- Bulk Delete Confirmation Modal --}}
    @if ($showBulkDeleteModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
        <div class="w-full max-w-md rounded-xl bg-white shadow-xl dark:bg-gray-800">
            <div class="p-6">
                <div class="flex items-center gap-3 text-red-600 mb-2">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Delete {{ count($selected) }} {{ \Illuminate\Support\Str::plural('Product', count($selected)) }}
                    </h2>
                </div>

                <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                    Are you sure you want to permanently delete the <strong>{{ count($selected) }}</strong> selected products?
                    Associated images, variants, and options will also be deleted. Products with active unfulfilled orders will be protected. This action cannot be undone.
                </p>
            </div>

            <div class="flex justify-end gap-3 border-t bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-800/80">
                <button type="button" wire:click="closeBulkDeleteModal"
                    class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                    Cancel
                </button>

                <button type="button" wire:click="bulkDelete" wire:loading.attr="disabled"
                    class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 disabled:opacity-50">
                    <span wire:loading.remove wire:target="bulkDelete">Delete Selected</span>
                    <span wire:loading wire:target="bulkDelete">Deleting...</span>
                </button>
            </div>
        </div>
    </div>
    @endif

</div>