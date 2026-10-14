<div>

    {{-- Page & Category Header --}}
    <section class="border-b border-gray-200 bg-gray-50/70">
        <div class="mx-auto px-6 py-10 lg:px-8" style="max-width: var(--store-page-width);">

            {{-- Breadcrumb --}}
            <nav class="mb-4 flex items-center gap-2 text-xs text-gray-500">
                <a href="{{ route('storefront.home') }}" class="transition hover:text-blue-600">
                    Home
                </a>
                <span>/</span>
                <a href="{{ route('storefront.products.index') }}" class="transition hover:text-blue-600">
                    Products
                </a>
                @if ($currentCategory)
                    <span>/</span>
                    @if ($currentCategory->parent)
                        <a
                            href="{{ route('storefront.categories.show', $currentCategory->parent->slug) }}"
                            class="transition hover:text-blue-600"
                        >
                            {{ $currentCategory->parent->name }}
                        </a>
                        <span>/</span>
                    @endif
                    <span class="font-medium text-gray-900">
                        {{ $currentCategory->name }}
                    </span>
                @endif
            </nav>

            <div class="flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-gray-900 sm:text-4xl">
                        @if ($currentCategory)
                            {{ $currentCategory->name }}
                        @elseif ($search !== '')
                            Search Results for "{{ $search }}"
                        @else
                            All Products
                        @endif
                    </h1>

                    <p class="mt-2 max-w-2xl text-sm text-gray-600">
                        @if ($currentCategory && $currentCategory->description)
                            {{ $currentCategory->description }}
                        @elseif ($currentCategory)
                            Explore products in the {{ $currentCategory->name }} category.
                        @elseif ($search !== '')
                            Showing matching items from our complete product catalog.
                        @else
                            Explore our latest collections with quick filtering and real-time search.
                        @endif
                    </p>
                </div>

                @if ($currentCategory)
                    <button
                        type="button"
                        wire:click="clearFilters"
                        class="text-xs font-semibold text-blue-600 hover:underline"
                        style="color: var(--store-primary);"
                    >
                        &larr; View all products
                    </button>
                @endif
            </div>

            {{-- Subcategories pills if current category has children --}}
            @if ($currentCategory && $currentCategory->children->isNotEmpty())
                <div class="mt-6 flex flex-wrap items-center gap-2 pt-4 border-t border-gray-200">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-400">
                        Subcategories:
                    </span>
                    @foreach ($currentCategory->children as $child)
                        <a
                            href="{{ route('storefront.categories.show', $child->slug) }}"
                            class="rounded-full border border-gray-300 bg-white px-3.5 py-1 text-xs font-medium text-gray-700 shadow-sm transition hover:border-blue-500 hover:text-blue-600"
                        >
                            {{ $child->name }}
                        </a>
                    @endforeach
                </div>
            @endif

        </div>
    </section>

    {{-- Main Catalog Container --}}
    <section class="mx-auto px-6 py-10 lg:px-8" style="max-width: var(--store-page-width);">

        {{-- Filters & Search Controls Bar --}}
        <div class="mb-6 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-5 items-end">

                {{-- Database Search --}}
                <div class="lg:col-span-2">
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600">
                        Search Products
                    </label>
                    <div class="relative">
                        <input
                            type="text"
                            wire:model.live.debounce.400ms="search"
                            placeholder="Search by title, SKU, brand..."
                            class="w-full rounded-xl border border-gray-300 bg-gray-50/50 pl-10 pr-9 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-100"
                        >
                        <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>

                        @if ($search !== '')
                            <button
                                type="button"
                                wire:click="$set('search', '')"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                                title="Clear search"
                            >
                                &times;
                            </button>
                        @endif
                    </div>
                </div>

                {{-- Category Filter --}}
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600">
                        Category
                    </label>
                    <select
                        wire:model.live="category"
                        class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                    >
                        <option value="">All Categories</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}">
                                {{ $cat->name }} ({{ $cat->products_count }})
                            </option>
                            @foreach ($cat->children as $subCat)
                                <option value="{{ $subCat->id }}">
                                    &nbsp;&nbsp;— {{ $subCat->name }}
                                </option>
                            @endforeach
                        @endforeach
                    </select>
                </div>

                {{-- Sort By --}}
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600">
                        Sort By
                    </label>
                    <select
                        wire:model.live="sort"
                        class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                    >
                        <option value="latest">Newest First</option>
                        <option value="price_low">Price: Low to High</option>
                        <option value="price_high">Price: High to Low</option>
                        <option value="name_asc">Name: A to Z</option>
                        <option value="name_desc">Name: Z to A</option>
                    </select>
                </div>

                {{-- Stock & Clear Toggle --}}
                <div class="flex items-center justify-between gap-3 pt-2 sm:pt-0">
                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <input
                            type="checkbox"
                            wire:model.live="inStock"
                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                        >
                        <span class="text-xs font-semibold text-gray-700">In Stock Only</span>
                    </label>

                    @php
                        $hasFilters = $search !== '' || $category !== null || $sort !== 'latest' || $minPrice !== '' || $maxPrice !== '' || $inStock;
                    @endphp

                    @if ($hasFilters)
                        <button
                            type="button"
                            wire:click="clearFilters"
                            class="text-xs font-medium text-red-600 hover:underline"
                        >
                            Reset
                        </button>
                    @endif
                </div>

            </div>

            {{-- Optional Price Range Row --}}
            <div class="mt-4 pt-4 border-t border-gray-100 flex flex-wrap items-center gap-4 text-xs text-gray-600">
                <span class="font-bold uppercase tracking-wider text-gray-500">Price Range:</span>
                <div class="flex items-center gap-2">
                    <span class="text-gray-400">{{ currency_symbol() }}</span>
                    <input
                        type="number"
                        wire:model.live.debounce.500ms="minPrice"
                        placeholder="Min"
                        class="w-24 rounded-lg border border-gray-300 px-2.5 py-1 text-xs outline-none focus:border-blue-500"
                    >
                    <span class="text-gray-400">—</span>
                    <span class="text-gray-400">{{ currency_symbol() }}</span>
                    <input
                        type="number"
                        wire:model.live.debounce.500ms="maxPrice"
                        placeholder="Max"
                        class="w-24 rounded-lg border border-gray-300 px-2.5 py-1 text-xs outline-none focus:border-blue-500"
                    >
                </div>
            </div>
        </div>

        {{-- Active Filters Badges & Count --}}
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-sm font-semibold text-gray-900">
                    {{ $products->total() }} {{ Str::plural('product', $products->total()) }} found
                </span>

                @if ($search !== '')
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 px-3 py-1 text-xs font-medium text-blue-700">
                        Search: "{{ $search }}"
                        <button type="button" wire:click="$set('search', '')" class="hover:text-blue-900">&times;</button>
                    </span>
                @endif

                @if ($currentCategory)
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 px-3 py-1 text-xs font-medium text-blue-700">
                        Category: {{ $currentCategory->name }}
                        <button type="button" wire:click="selectCategory(null)" class="hover:text-blue-900">&times;</button>
                    </span>
                @endif

                @if ($inStock)
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-green-50 px-3 py-1 text-xs font-medium text-green-700">
                        In Stock Only
                        <button type="button" wire:click="$set('inStock', false)" class="hover:text-green-900">&times;</button>
                    </span>
                @endif

                @if ($minPrice !== '' || $maxPrice !== '')
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700">
                        Price: {{ currency_symbol() }}{{ $minPrice ?: '0' }} — {{ currency_symbol() }}{{ $maxPrice ?: '∞' }}
                        <button type="button" wire:click="$set('minPrice', ''); $set('maxPrice', '');" class="hover:text-gray-900">&times;</button>
                    </span>
                @endif
            </div>

            {{-- Loading Indicator --}}
            <div wire:loading class="text-xs text-gray-500 animate-pulse">
                Updating catalog...
            </div>
        </div>

        {{-- Products Grid --}}
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
            @forelse ($products as $product)
                @php
                    $primaryImg = $product->images->first();
                    $firstVariant = $product->variants->first();
                    $inStockStatus = $product->variants->sum(fn ($v) => $v->inventory?->quantity ?? 0) > 0;
                @endphp

                <article
                    wire:key="catalog-product-{{ $product->id }}"
                    class="group flex flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm transition duration-200 hover:-translate-y-1 hover:shadow-lg"
                >
                    {{-- Image Container --}}
                    <a
                        href="{{ route('storefront.products.show', $product->slug) }}"
                        class="relative block aspect-square overflow-hidden bg-gray-100"
                    >
                        @if ($primaryImg)
                            <img
                                src="{{ Storage::disk('public')->url($primaryImg->path) }}"
                                alt="{{ $primaryImg->alt_text ?? $product->name }}"
                                class="h-full w-full object-cover transition duration-300 group-hover:scale-105"
                            >
                        @else
                            <div class="flex h-full w-full items-center justify-center text-xs text-gray-400">
                                No Image
                            </div>
                        @endif

                        {{-- Badges --}}
                        <div class="absolute left-3 top-3 flex flex-col gap-1">
                            @if ($product->compare_at_price && $product->compare_at_price > $product->price)
                                <span class="rounded-md bg-red-600 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-white shadow-sm">
                                    Sale
                                </span>
                            @endif

                            @if (! $inStockStatus)
                                <span class="rounded-md bg-gray-900/80 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-white backdrop-blur shadow-sm">
                                    Out of stock
                                </span>
                            @endif
                        </div>
                    </a>

                    {{-- Product Info --}}
                    <div class="flex flex-1 flex-col p-4">
                        <div class="flex items-center justify-between text-xs text-gray-500">
                            <span>{{ $product->categories->first()?->name ?? 'Store' }}</span>
                            @if ($product->vendor)
                                <span class="truncate max-w-[100px]">{{ $product->vendor }}</span>
                            @endif
                        </div>

                        <h2 class="mt-1 font-semibold text-gray-900 group-hover:text-blue-600 transition truncate" title="{{ $product->name }}">
                            <a href="{{ route('storefront.products.show', $product->slug) }}">
                                {{ $product->name }}
                            </a>
                        </h2>

                        {{-- Price Block --}}
                        <div class="mt-2 flex items-center gap-2">
                            <span class="font-extrabold text-gray-900" style="color: var(--store-text);">
                                {{ format_price($product->price) }}
                            </span>

                            @if ($product->compare_at_price && $product->compare_at_price > $product->price)
                                <span class="text-xs text-gray-400 line-through">
                                    {{ format_price($product->compare_at_price) }}
                                </span>
                            @endif
                        </div>

                        {{-- Card CTA --}}
                        <div class="mt-auto pt-4">
                            <a
                                href="{{ route('storefront.products.show', $product->slug) }}"
                                class="block w-full rounded-xl px-4 py-2.5 text-center text-xs font-semibold text-white shadow-sm transition hover:opacity-90 active:scale-[0.99]"
                                style="background-color: var(--store-secondary, #111827);"
                            >
                                View Product
                            </a>
                        </div>
                    </div>
                </article>
            @empty
                <div class="col-span-full rounded-2xl border border-dashed border-gray-300 bg-white p-16 text-center shadow-sm">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-gray-100 text-gray-400 text-2xl">
                        🔍
                    </div>

                    <h3 class="mt-4 text-lg font-bold text-gray-900">
                        No products match your criteria
                    </h3>

                    <p class="mx-auto mt-2 max-w-sm text-sm text-gray-500">
                        We couldn't find any products matching your current filters or search terms. Try clearing some filters or searching for something else.
                    </p>

                    <div class="mt-6">
                        <button
                            type="button"
                            wire:click="clearFilters"
                            class="rounded-xl px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:opacity-90"
                            style="background-color: var(--store-primary, #2563eb);"
                        >
                            Reset All Filters
                        </button>
                    </div>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if ($products->hasPages())
            <div class="mt-12 flex justify-center">
                {{ $products->links() }}
            </div>
        @endif

    </section>

</div>