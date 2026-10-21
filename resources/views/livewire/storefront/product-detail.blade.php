<div>

    {{-- Breadcrumb --}}
    <section class="border-b border-gray-200 bg-gray-50/70">
        <div class="mx-auto px-6 py-4 lg:px-8" style="max-width: var(--store-page-width);">
            <nav class="flex items-center gap-2 text-xs text-gray-500">
                <a href="{{ route('storefront.home') }}" class="transition hover:text-blue-600">
                    Home
                </a>
                <span>/</span>
                <a href="{{ route('storefront.products.index') }}" class="transition hover:text-blue-600">
                    Products
                </a>
                @if ($product->categories->first())
                    <span>/</span>
                    <a
                        href="{{ route('storefront.categories.show', $product->categories->first()->slug) }}"
                        class="transition hover:text-blue-600"
                    >
                        {{ $product->categories->first()->name }}
                    </a>
                @endif
                <span>/</span>
                <span class="font-medium text-gray-900 truncate max-w-xs">
                    {{ $product->name }}
                </span>
            </nav>
        </div>
    </section>

    {{-- Product Section --}}
    <section class="mx-auto px-6 py-12 lg:px-8 lg:py-16" style="max-width: var(--store-page-width);">
        <div class="grid gap-10 lg:grid-cols-2 lg:gap-16">

            {{-- Product Gallery --}}
            <div>
                {{-- Main Image --}}
                <div class="aspect-square overflow-hidden rounded-2xl border border-gray-200 bg-gray-100 shadow-sm">
                    @php
                        $selectedProductImage = $product->images->firstWhere('id', $selectedImage) ?? $product->images->first();
                    @endphp

                    @if ($selectedProductImage)
                        <img
                            src="{{ Storage::disk('public')->url($selectedProductImage->path) }}"
                            alt="{{ $selectedProductImage->alt_text ?? $product->name }}"
                            class="h-full w-full object-cover transition duration-300"
                        >
                    @else
                        <div class="flex h-full items-center justify-center text-sm text-gray-400">
                            No Image Available
                        </div>
                    @endif
                </div>

                {{-- Thumbnails --}}
                @if ($product->images->count() > 1)
                    <div class="mt-4 grid grid-cols-5 gap-3">
                        @foreach ($product->images as $image)
                            <button
                                type="button"
                                wire:click="selectImage({{ $image->id }})"
                                class="aspect-square overflow-hidden rounded-xl border-2 transition hover:opacity-90
                                    {{ $selectedImage === $image->id
                                        ? 'border-blue-600 ring-2 ring-blue-100'
                                        : 'border-gray-200 hover:border-gray-300' }}"
                                style="{{ $selectedImage === $image->id ? 'border-color: var(--store-primary);' : '' }}"
                            >
                                <img
                                    src="{{ Storage::disk('public')->url($image->path) }}"
                                    alt="{{ $image->alt_text ?? $product->name }}"
                                    class="h-full w-full object-cover"
                                >
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Product Information --}}
            <div>
                {{-- Category Badges --}}
                @if ($product->categories->isNotEmpty())
                    <div class="mb-3 flex flex-wrap gap-2">
                        @foreach ($product->categories as $category)
                            <a
                                href="{{ route('storefront.categories.show', $category->slug) }}"
                                class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700 transition hover:bg-blue-100"
                            >
                                {{ $category->name }}
                            </a>
                        @endforeach
                    </div>
                @endif

                {{-- Product Name --}}
                <h1 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">
                    {{ $product->name }}
                </h1>

                {{-- SKU & Vendor --}}
                <div class="mt-2 flex flex-wrap items-center gap-4 text-xs text-gray-500">
                    <span>SKU: <strong class="text-gray-700">{{ $selectedVariant?->sku ?? $product->sku }}</strong></span>
                    @if ($product->vendor)
                        <span>Brand: <strong class="text-gray-700">{{ $product->vendor }}</strong></span>
                    @endif
                </div>

                {{-- Pricing --}}
                @php
                    $currentPrice = $selectedVariant ? $selectedVariant->effective_price : (float) $product->price;
                    $currentComparePrice = $selectedVariant ? $selectedVariant->effective_compare_at_price : ($product->compare_at_price ? (float) $product->compare_at_price : null);
                    $isDiscounted = $currentComparePrice && $currentComparePrice > $currentPrice;
                @endphp

                <div class="mt-5 flex items-center gap-3">
                    <span class="text-3xl font-extrabold text-gray-900" style="color: var(--store-text);">
                        {{ format_price($currentPrice) }}
                    </span>

                    @if ($isDiscounted)
                        <span class="text-lg text-gray-400 line-through">
                            {{ format_price($currentComparePrice) }}
                        </span>

                        @php
                            $discountPercentage = round((($currentComparePrice - $currentPrice) / $currentComparePrice) * 100);
                        @endphp
                        <span class="rounded-full bg-red-50 px-2.5 py-0.5 text-xs font-bold text-red-600">
                            Save {{ $discountPercentage }}%
                        </span>
                    @endif
                </div>

                {{-- Stock Status --}}
                @php
                    $stock = $selectedVariant ? $selectedVariant->available_stock : 0;
                    $lowStockThreshold = (int) store_setting('low_stock_threshold', 5);
                @endphp

                <div class="mt-4">
                    @if ($selectedVariant)
                        @if ($stock > 0)
                            <div class="flex items-center gap-2 text-sm font-medium text-green-700">
                                <span class="h-2.5 w-2.5 rounded-full bg-green-600"></span>
                                <span>In Stock</span>
                                @if ($stock <= $lowStockThreshold)
                                    <span class="text-xs text-amber-600 font-normal">
                                        (Only {{ $stock }} left, order soon!)
                                    </span>
                                @endif
                            </div>
                        @else
                            <div class="flex items-center gap-2 text-sm font-medium text-red-600">
                                <span class="h-2.5 w-2.5 rounded-full bg-red-600"></span>
                                <span>Out of Stock</span>
                            </div>
                        @endif
                    @elseif ($product->options->isNotEmpty())
                        <p class="text-sm text-gray-500">
                            Please select product options to check availability.
                        </p>
                    @endif
                </div>

                {{-- Description --}}
                @if ($product->description)
                    <div class="mt-6 border-t border-gray-200 pt-6">
                        <h2 class="text-xs font-bold uppercase tracking-wider text-gray-500">
                            Description
                        </h2>
                        <div class="mt-3 text-sm leading-6 text-gray-600">
                            {!! nl2br(e($product->description)) !!}
                        </div>
                    </div>
                @endif

                {{-- Product Options / Variants --}}
                @if ($product->options->isNotEmpty())
                    <div class="mt-6 space-y-5 border-t border-gray-200 pt-6">
                        @foreach ($product->options as $option)
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-gray-700">
                                    {{ $option->name }}
                                </label>

                                <div class="mt-2 flex flex-wrap gap-2">
                                    @foreach ($option->values as $value)
                                        @php
                                            $isSelected = ($selectedOptions[$option->id] ?? null) == $value->id;
                                        @endphp

                                        <button
                                            type="button"
                                            wire:click="selectOption({{ $option->id }}, {{ $value->id }})"
                                            class="rounded-lg border px-4 py-2 text-sm font-semibold transition
                                                {{ $isSelected
                                                    ? 'border-blue-600 bg-blue-600 text-white shadow-sm'
                                                    : 'border-gray-200 bg-white text-gray-700 hover:border-gray-400' }}"
                                            style="{{ $isSelected ? 'background-color: var(--store-primary); border-color: var(--store-primary);' : '' }}"
                                        >
                                            {{ $value->value }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Quantity Selector --}}
                <div class="mt-6 border-t border-gray-200 pt-6">
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-700">
                        Quantity
                    </label>

                    <div class="mt-2 flex items-center gap-3">
                        <div class="flex items-center overflow-hidden rounded-lg border border-gray-300 bg-white">
                            <button
                                type="button"
                                wire:click="decrementQuantity"
                                @disabled($quantity <= 1)
                                class="flex h-10 w-10 items-center justify-center text-gray-600 transition hover:bg-gray-100 disabled:opacity-40"
                            >
                                −
                            </button>

                            <span class="flex h-10 w-12 items-center justify-center border-x border-gray-300 text-sm font-semibold text-gray-900">
                                {{ $quantity }}
                            </span>

                            <button
                                type="button"
                                wire:click="incrementQuantity"
                                @disabled($stock > 0 && $quantity >= $stock)
                                class="flex h-10 w-10 items-center justify-center text-gray-600 transition hover:bg-gray-100 disabled:opacity-40"
                            >
                                +
                            </button>
                        </div>

                        @if ($stock > 0 && $quantity >= $stock)
                            <span class="text-xs text-amber-600">
                                Maximum available quantity reached
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Add To Cart Action --}}
                @php
                    $canAddToCart = $selectedVariant && $stock >= $quantity;
                @endphp

                <div class="mt-8 space-y-3">
                    <button
                        type="button"
                        wire:click="addToCart"
                        wire:loading.attr="disabled"
                        @disabled(! $canAddToCart)
                        class="group relative w-full overflow-hidden rounded-2xl px-7 py-4 text-sm font-bold text-white shadow-lg transition-all duration-300
                            {{ $canAddToCart
                                ? 'hover:shadow-xl hover:-translate-y-0.5 active:translate-y-0'
                                : 'cursor-not-allowed opacity-50 bg-gray-400' }}"
                        style="{{ $canAddToCart ? 'background-color: var(--store-primary, #2563eb);' : '' }}"
                    >
                        <span wire:loading.remove wire:target="addToCart" class="relative z-10 flex items-center justify-center gap-2">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                            @if (! $selectedVariant)
                                <span>Select Available Variant</span>
                            @elseif ($stock <= 0)
                                <span>Out of Stock</span>
                            @else
                                <span>Add to Cart &bull; {{ format_price($currentPrice * $quantity) }}</span>
                            @endif
                        </span>

                        <span wire:loading wire:target="addToCart" class="relative z-10 inline-flex items-center justify-center gap-2">
                            <svg class="h-5 w-5 animate-spin" viewBox="0 0 24 24" fill="none">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>Adding to Cart...</span>
                        </span>

                        <div class="absolute inset-0 -translate-x-full bg-gradient-to-r from-transparent via-white/20 to-transparent transition-transform duration-700 group-hover:translate-x-full"></div>
                    </button>

                    @if (session()->has('cart_success'))
                        <div class="flex items-center justify-between rounded-xl bg-green-50 p-4 text-sm text-green-800 border border-green-200 animate-slide-up">
                            <div class="flex items-center gap-2">
                                <svg class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span class="font-medium">{{ session('cart_success') }}</span>
                            </div>
                            <a
                                href="{{ route('storefront.cart') }}"
                                class="font-bold text-green-700 underline hover:text-green-900"
                            >
                                View Cart &rarr;
                            </a>
                        </div>
                    @endif

                    @if (session()->has('cart_error'))
                        <div class="rounded-xl bg-red-50 p-4 text-sm text-red-700 border border-red-200">
                            {{ session('cart_error') }}
                        </div>
                    @endif

                    {{-- Product Assurance / Trust Badges --}}
                    <div class="mt-6 rounded-2xl border border-gray-200/80 bg-gray-50/70 p-4 divide-y divide-gray-200/60">
                        <div class="flex items-center gap-3 pb-3">
                            <span class="text-lg">🚚</span>
                            <div class="text-xs">
                                <strong class="text-gray-900 font-bold">Fast & Tracked Shipping</strong>
                                <p class="text-gray-500">Dispatched directly to your address with tracking.</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 py-3">
                            <span class="text-lg">🔒</span>
                            <div class="text-xs">
                                <strong class="text-gray-900 font-bold">Secure Checkout</strong>
                                <p class="text-gray-500">Encrypted payment via Razorpay, UPI, & Cards.</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 pt-3">
                            <span class="text-lg">↩️</span>
                            <div class="text-xs">
                                <strong class="text-gray-900 font-bold">Easy 7-Day Returns</strong>
                                <p class="text-gray-500">Hassle-free replacement if damaged or incorrect.</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>

        {{-- Related Products --}}
        @if ($relatedProducts->isNotEmpty())
            <div class="mt-20 border-t border-gray-100 pt-14">
                <div class="mb-8 flex items-end justify-between">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-widest text-blue-600" style="color: var(--store-primary);">
                            Recommendations
                        </span>
                        <h2 class="mt-1 text-2xl font-extrabold tracking-tight text-gray-900 sm:text-3xl">
                            You May Also Like
                        </h2>
                        <p class="mt-1 text-sm text-gray-500">
                            Similar products curated from the same collection
                        </p>
                    </div>

                    <a
                        href="{{ route('storefront.products.index') }}"
                        class="group inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-blue-600 hover:underline"
                        style="color: var(--store-primary);"
                    >
                        <span>Browse All</span>
                        <span class="transition-transform duration-200 group-hover:translate-x-1">&rarr;</span>
                    </a>
                </div>

                <div class="grid grid-cols-2 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($relatedProducts as $rel)
                        @php
                            $relImg = $rel->images->first();
                            $relDiscount = $rel->compare_at_price && $rel->compare_at_price > $rel->price;
                            $relDiscPct = $relDiscount ? round((($rel->compare_at_price - $rel->price) / $rel->compare_at_price) * 100) : 0;
                        @endphp
                        <article class="store-card group flex flex-col overflow-hidden rounded-2xl border border-gray-200/80 bg-white shadow-xs transition-all duration-300">
                            <a href="{{ route('storefront.products.show', $rel->slug) }}" class="relative block aspect-square overflow-hidden bg-gray-50">
                                @if ($relImg)
                                    <img
                                        src="{{ Storage::disk('public')->url($relImg->path) }}"
                                        alt="{{ $rel->name }}"
                                        class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                                    >
                                @else
                                    <div class="flex h-full items-center justify-center text-xs text-gray-400">
                                        No Image
                                    </div>
                                @endif

                                @if ($relDiscount)
                                    <div class="absolute left-3 top-3">
                                        <span class="rounded-lg bg-red-600 px-2 py-0.5 text-[10px] font-extrabold uppercase tracking-wider text-white shadow-xs">
                                            -{{ $relDiscPct }}%
                                        </span>
                                    </div>
                                @endif
                            </a>

                            <div class="flex flex-1 flex-col p-4 sm:p-5">
                                <div class="flex items-center justify-between text-xs text-gray-400">
                                    <span class="truncate max-w-[100px]">{{ $rel->categories->first()?->name ?? 'Collection' }}</span>
                                    <span class="text-amber-400 text-xs">★ 4.9</span>
                                </div>

                                <h3 class="mt-1.5 font-bold text-gray-900 truncate group-hover:text-blue-600 transition text-sm" title="{{ $rel->name }}">
                                    <a href="{{ route('storefront.products.show', $rel->slug) }}">
                                        {{ $rel->name }}
                                    </a>
                                </h3>

                                <div class="mt-2 flex items-center gap-2">
                                    <span class="font-extrabold text-sm text-gray-900" style="color: var(--store-text);">
                                        {{ format_price($rel->price) }}
                                    </span>
                                    @if ($rel->compare_at_price)
                                        <span class="text-xs text-gray-400 line-through">
                                            {{ format_price($rel->compare_at_price) }}
                                        </span>
                                    @endif
                                </div>

                                <div class="mt-auto pt-4">
                                    <a
                                        href="{{ route('storefront.products.show', $rel->slug) }}"
                                        class="block w-full rounded-xl px-3 py-2 text-center text-xs font-bold text-white shadow-xs transition-all duration-200 hover:opacity-90 active:scale-[0.98]"
                                        style="background-color: var(--store-secondary, #111827);"
                                    >
                                        View Details
                                    </a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        @endif

    </section>

</div>