@extends('storefront.layouts.app')

@section('content')

@php
    $sections = $storefrontTheme['sections'] ?? [];
    $sectionsOrder = $storefrontTheme['sections_order'] ?? ['hero', 'categories', 'products', 'cta'];

    // Hero Section
    $hero = $sections['hero'] ?? [];
    $showHero = (bool) ($hero['visible'] ?? true);
    $heroEyebrow = $hero['eyebrow'] ?? 'New Collection';
    $heroHeading = $hero['heading'] ?? 'Everything you need,';
    $heroHighlight = $hero['highlight'] ?? 'all in one place.';
    $heroDescription = $hero['description'] ?? 'Discover our latest products, collections and exclusive offers.';
    $heroPrimaryButtonText = $hero['primary_button_text'] ?? 'Shop Now';
    $heroPrimaryButtonLink = $hero['primary_button_link'] ?? '#products';
    $heroSecondaryButtonText = $hero['secondary_button_text'] ?? 'Explore Categories';
    $heroSecondaryButtonLink = $hero['secondary_button_link'] ?? '#categories';

    // Categories Section
    $categoriesSection = $sections['categories'] ?? [];
    $showCategories = (bool) ($categoriesSection['visible'] ?? true);
    $categoryHeading = $categoriesSection['heading'] ?? 'Shop by Category';
    $categoryDescription = $categoriesSection['description'] ?? 'Explore our product categories.';
    $categoryColumns = (int) ($categoriesSection['columns'] ?? 4);
    $categoryGridClass = match ($categoryColumns) {
        2 => 'grid-cols-2',
        3 => 'grid-cols-2 md:grid-cols-3',
        default => 'grid-cols-2 md:grid-cols-3 lg:grid-cols-4',
    };

    // Products Section
    $productsSection = $sections['products'] ?? [];
    $showProducts = (bool) ($productsSection['visible'] ?? true);
    $productsHeading = $productsSection['heading'] ?? 'Featured Products';
    $productsDescription = $productsSection['description'] ?? 'Discover some of our latest products.';
    $productsViewAllText = $productsSection['view_all_text'] ?? 'View All';
    $productsViewAllLink = $productsSection['view_all_link'] ?? route('storefront.shop');
    $productsColumns = (int) ($productsSection['columns'] ?? 4);
    $productsGridClass = match ($productsColumns) {
        2 => 'grid-cols-2',
        3 => 'grid-cols-2 sm:grid-cols-2 lg:grid-cols-3',
        default => 'grid-cols-2 sm:grid-cols-2 lg:grid-cols-4',
    };

    // CTA Section
    $cta = $sections['cta'] ?? [];
    $showCta = (bool) ($cta['visible'] ?? true);
    $ctaHeading = $cta['heading'] ?? "Discover something you'll love.";
    $ctaDescription = $cta['description'] ?? 'Browse our collection and find the perfect products for you.';
    $ctaButtonText = $cta['button_text'] ?? 'Start Shopping';
    $ctaButtonLink = $cta['button_link'] ?? '#products';
@endphp

@foreach ($sectionsOrder as $sectionKey)
    @if ($sectionKey === 'hero' && $showHero)
        {{-- Modern Animated Hero Section --}}
        <section class="relative overflow-hidden" style="background-color: var(--store-secondary, #111827);">
            {{-- Floating Ambient Gradient Glows --}}
            <div class="pointer-events-none absolute -top-24 -left-24 h-96 w-96 rounded-full bg-blue-500/25 blur-3xl animate-float"></div>
            <div class="pointer-events-none absolute -bottom-24 -right-24 h-96 w-96 rounded-full bg-indigo-500/20 blur-3xl animate-float-reverse"></div>
            <div class="pointer-events-none absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 h-64 w-64 rounded-full bg-blue-400/10 blur-3xl animate-pulse-subtle"></div>

            <div class="relative mx-auto px-6 py-20 lg:px-8 lg:py-28" style="max-width: var(--store-page-width);">
                <div class="grid items-center gap-12 lg:grid-cols-12">

                    {{-- Left Column: Copy & Actions --}}
                    <div class="lg:col-span-7">
                        {{-- Eyebrow Chip with Subtle Shimmer Glow --}}
                        <div class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3.5 py-1.5 text-xs font-semibold backdrop-blur-md shadow-xs animate-slide-up">
                            <span class="flex h-2 w-2 rounded-full bg-blue-400 animate-ping"></span>
                            <span class="text-white/90 uppercase tracking-widest font-bold">
                                ✨ {{ $heroEyebrow }}
                            </span>
                        </div>

                        {{-- Main Headline --}}
                        <h1 class="mt-6 text-4xl font-extrabold tracking-tight text-white sm:text-5xl lg:text-6xl leading-[1.15]">
                            {{ $heroHeading }}
                            <span class="block bg-gradient-to-r from-blue-400 via-indigo-300 to-sky-300 bg-clip-text text-transparent">
                                {{ $heroHighlight }}
                            </span>
                        </h1>

                        {{-- Description --}}
                        <p class="mt-6 max-w-xl text-base sm:text-lg leading-relaxed text-gray-300">
                            {{ $heroDescription }}
                        </p>

                        {{-- CTAs --}}
                        <div class="mt-8 flex flex-wrap items-center gap-4">
                            <a
                                href="{{ $heroPrimaryButtonLink }}"
                                class="group relative inline-flex items-center gap-2.5 overflow-hidden rounded-xl px-7 py-3.5 text-sm font-bold text-white shadow-lg transition-all duration-300 hover:shadow-xl hover:-translate-y-0.5 active:translate-y-0"
                                style="background-color: var(--store-primary, #2563eb);"
                            >
                                <span class="relative z-10">{{ $heroPrimaryButtonText }}</span>
                                <svg class="relative z-10 h-4 w-4 transition-transform duration-300 group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                                <div class="absolute inset-0 -translate-x-full bg-gradient-to-r from-transparent via-white/20 to-transparent transition-transform duration-700 group-hover:translate-x-full"></div>
                            </a>

                            <a
                                href="{{ $heroSecondaryButtonLink }}"
                                class="inline-flex items-center gap-2 rounded-xl border border-white/20 bg-white/5 px-6 py-3.5 text-sm font-semibold text-white backdrop-blur-md transition-all duration-300 hover:bg-white/15 hover:border-white/30 hover:-translate-y-0.5"
                            >
                                <span>{{ $heroSecondaryButtonText }}</span>
                                <svg class="h-4 w-4 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </a>
                        </div>

                        {{-- Trust Metrics Row --}}
                        <div class="mt-10 flex flex-wrap items-center gap-6 border-t border-white/10 pt-6 text-xs text-gray-400">
                            <div class="flex items-center gap-2">
                                <div class="flex text-amber-400">
                                    ★★★★★
                                </div>
                                <span class="text-white font-medium">4.9/5 Customer Rating</span>
                            </div>
                            <span class="hidden sm:inline text-gray-600">•</span>
                            <div class="flex items-center gap-2">
                                <span class="text-emerald-400 font-bold">✓</span>
                                <span class="text-white font-medium">Verified Razorpay Payments</span>
                            </div>
                        </div>
                    </div>

                    {{-- Right Column: Dynamic Visual Showcase Card --}}
                    <div class="hidden lg:col-span-5 lg:block">
                        <div class="relative mx-auto max-w-sm">
                            {{-- Decorative Floating Mini Badges --}}
                            <div class="absolute -top-4 -left-6 z-20 flex items-center gap-2 rounded-2xl glass-panel p-3 shadow-xl animate-float">
                                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-500 text-white font-bold text-sm shadow-sm">
                                    ⚡
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-gray-900">Express Shipping</p>
                                    <p class="text-[10px] text-gray-500">Dispatched in 24 hrs</p>
                                </div>
                            </div>

                            <div class="absolute -bottom-6 -right-6 z-20 flex items-center gap-2 rounded-2xl glass-panel p-3 shadow-xl animate-float-reverse">
                                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-600 text-white font-bold text-sm shadow-sm">
                                    🛡️
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-gray-900">100% Guaranteed</p>
                                    <p class="text-[10px] text-gray-500">Authentic & Secure</p>
                                </div>
                            </div>

                            {{-- Main Glass Card Container --}}
                            <div class="overflow-hidden rounded-3xl border border-white/15 bg-gradient-to-b from-white/15 to-white/5 p-6 backdrop-blur-xl shadow-2xl transition-all duration-300 hover:border-white/30">
                                @php
                                    $showcaseProduct = $products->first();
                                @endphp

                                @if ($showcaseProduct)
                                    <div class="relative aspect-square overflow-hidden rounded-2xl bg-gray-900/60">
                                        @if ($showcaseProduct->images && $showcaseProduct->images->first())
                                            <img
                                                src="{{ Storage::disk('public')->url($showcaseProduct->images->first()->path) }}"
                                                alt="{{ $showcaseProduct->name }}"
                                                class="h-full w-full object-cover transition-transform duration-500 hover:scale-105"
                                            >
                                        @else
                                            <div class="flex h-full items-center justify-center text-sm text-gray-400">
                                                Featured Item
                                            </div>
                                        @endif
                                        <div class="absolute top-3 left-3 rounded-full bg-blue-600/90 px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-white backdrop-blur-md">
                                            Top Pick
                                        </div>
                                    </div>

                                    <div class="mt-4 flex items-center justify-between">
                                        <div class="truncate pr-2">
                                            <h3 class="font-bold text-white truncate">{{ $showcaseProduct->name }}</h3>
                                            <p class="text-xs text-gray-400">{{ $showcaseProduct->categories->first()?->name ?? 'Popular' }}</p>
                                        </div>
                                        <span class="text-lg font-extrabold text-blue-400 shrink-0">
                                            {{ format_price($showcaseProduct->price) }}
                                        </span>
                                    </div>

                                    <a
                                        href="{{ route('storefront.products.show', $showcaseProduct) }}"
                                        class="mt-4 block w-full rounded-xl bg-white/20 py-2.5 text-center text-xs font-bold text-white backdrop-blur-md transition hover:bg-white hover:text-gray-900"
                                    >
                                        View Details
                                    </a>
                                @else
                                    <div class="aspect-square flex flex-col items-center justify-center text-center p-8 text-white/80">
                                        <span class="text-4xl">🛍️</span>
                                        <h3 class="mt-3 font-bold text-lg text-white">Curated For You</h3>
                                        <p class="mt-1 text-xs text-gray-300">Discover premium selections updated daily.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        {{-- Trust & Value Props Strip --}}
        <section class="border-b border-gray-100 bg-white">
            <div class="mx-auto px-6 py-8 lg:px-8" style="max-width: var(--store-page-width);">
                <div class="grid grid-cols-2 gap-6 md:grid-cols-4">
                    <div class="flex items-center gap-3.5 group">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-blue-50 text-blue-600 transition-colors group-hover:bg-blue-600 group-hover:text-white">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold uppercase tracking-wider text-gray-900">Fast Delivery</h4>
                            <p class="text-xs text-gray-500">Free shipping on top orders</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3.5 group">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600 transition-colors group-hover:bg-emerald-600 group-hover:text-white">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold uppercase tracking-wider text-gray-900">Secure Payments</h4>
                            <p class="text-xs text-gray-500">Razorpay 256-bit SSL</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3.5 group">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-amber-50 text-amber-600 transition-colors group-hover:bg-amber-600 group-hover:text-white">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold uppercase tracking-wider text-gray-900">Easy Returns</h4>
                            <p class="text-xs text-gray-500">7-day hassle-free policy</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3.5 group">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-purple-50 text-purple-600 transition-colors group-hover:bg-purple-600 group-hover:text-white">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold uppercase tracking-wider text-gray-900">24/7 Support</h4>
                            <p class="text-xs text-gray-500">Dedicated assistance</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    @elseif ($sectionKey === 'categories' && $showCategories)
        {{-- Modern Categories Section --}}
        <section id="categories" class="mx-auto px-6 py-16 lg:px-8" style="max-width: var(--store-page-width);">
            <div class="mb-10 flex flex-col md:flex-row md:items-end md:justify-between gap-4">
                <div>
                    <span class="text-xs font-bold uppercase tracking-widest text-blue-600" style="color: var(--store-primary);">
                        Browse Collections
                    </span>
                    <h2 class="mt-1 text-2xl sm:text-3xl font-extrabold tracking-tight" style="color: var(--store-text);">
                        {{ $categoryHeading }}
                    </h2>
                    <p class="mt-2 text-sm text-gray-500 max-w-xl">
                        {{ $categoryDescription }}
                    </p>
                </div>

                <a
                    href="{{ route('storefront.products.index') }}"
                    class="group inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider transition hover:underline"
                    style="color: var(--store-primary);"
                >
                    <span>View All Categories</span>
                    <span class="transition-transform duration-200 group-hover:translate-x-1">&rarr;</span>
                </a>
            </div>

            <div class="grid {{ $categoryGridClass }} gap-5">
                @forelse ($categories ?? [] as $category)
                    <a
                        href="{{ route('storefront.categories.show', $category->slug) }}"
                        class="store-card group relative flex flex-col justify-between overflow-hidden rounded-2xl border border-gray-200/80 bg-white p-6 transition-all duration-300"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-gray-50 text-xl shadow-xs transition-colors group-hover:bg-blue-50">
                                📁
                            </div>
                            <span class="rounded-full bg-gray-100 px-2.5 py-1 text-[11px] font-semibold text-gray-600 transition-colors group-hover:bg-blue-100 group-hover:text-blue-700">
                                Explore
                            </span>
                        </div>

                        <div class="mt-6">
                            <h3 class="font-bold text-gray-900 text-lg transition-colors group-hover:text-blue-600" style="color: var(--store-text);">
                                {{ $category->name }}
                            </h3>
                            <p class="mt-1 text-xs text-gray-500">
                                Browse products in this collection
                            </p>
                        </div>

                        <div class="mt-4 flex items-center gap-1 text-xs font-semibold text-blue-600 opacity-0 transition-all duration-200 group-hover:opacity-100 group-hover:translate-x-1" style="color: var(--store-primary);">
                            <span>Discover now</span>
                            <span>&rarr;</span>
                        </div>
                    </a>
                @empty
                    <div class="col-span-full rounded-2xl border border-dashed border-gray-200 bg-white p-12 text-center">
                        <p class="text-sm text-gray-500">
                            Categories will appear here.
                        </p>
                    </div>
                @endforelse
            </div>
        </section>

    @elseif ($sectionKey === 'products' && $showProducts)
        {{-- Modern Featured Products Section --}}
        <section id="products" class="bg-gray-50/70 border-t border-b border-gray-100">
            <div class="mx-auto px-6 py-16 lg:px-8" style="max-width: var(--store-page-width);">
                <div class="mb-10 flex flex-col md:flex-row md:items-end md:justify-between gap-4">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-widest text-blue-600" style="color: var(--store-primary);">
                            Handpicked For You
                        </span>
                        <h2 class="mt-1 text-2xl sm:text-3xl font-extrabold tracking-tight" style="color: var(--store-text);">
                            {{ $productsHeading }}
                        </h2>
                        <p class="mt-2 text-sm text-gray-500">
                            {{ $productsDescription }}
                        </p>
                    </div>

                    @if ($productsViewAllText)
                        <a
                            href="{{ $productsViewAllLink }}"
                            class="group inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider transition hover:underline"
                            style="color: var(--store-primary);"
                        >
                            <span>{{ $productsViewAllText }}</span>
                            <span class="transition-transform duration-200 group-hover:translate-x-1">&rarr;</span>
                        </a>
                    @endif
                </div>

                <div class="grid {{ $productsGridClass }} gap-6">
                    @forelse ($products ?? [] as $product)
                        @php
                            $primaryImg = $product->images ? $product->images->first() : null;
                            $hasDiscount = $product->compare_at_price && $product->compare_at_price > $product->price;
                            $discountPct = $hasDiscount ? round((($product->compare_at_price - $product->price) / $product->compare_at_price) * 100) : 0;
                        @endphp

                        <article class="store-card group flex flex-col overflow-hidden rounded-2xl border border-gray-200/80 bg-white shadow-xs transition-all duration-300">
                            {{-- Image Container --}}
                            <a href="{{ route('storefront.products.show', $product) }}" class="relative block aspect-square overflow-hidden bg-gray-50">
                                @if ($primaryImg)
                                    <img
                                        src="{{ Storage::disk('public')->url($primaryImg->path) }}"
                                        alt="{{ $primaryImg->alt_text ?? $product->name }}"
                                        class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                                    >
                                @else
                                    <div class="flex h-full w-full items-center justify-center text-xs text-gray-400">
                                        No Image
                                    </div>
                                @endif

                                {{-- Badges Overlay --}}
                                <div class="absolute left-3 top-3 flex flex-col gap-1.5">
                                    @if ($hasDiscount)
                                        <span class="rounded-lg bg-red-600 px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-wider text-white shadow-sm">
                                            -{{ $discountPct }}% OFF
                                        </span>
                                    @endif
                                </div>
                            </a>

                            {{-- Product Information --}}
                            <div class="flex flex-1 flex-col p-5">
                                <div class="flex items-center justify-between text-xs text-gray-500">
                                    <span class="font-medium text-gray-400 truncate max-w-[120px]">
                                        {{ $product->categories->first()?->name ?? 'Collection' }}
                                    </span>
                                    <div class="flex items-center text-amber-400 text-xs">
                                        ★ 4.9
                                    </div>
                                </div>

                                <h3 class="mt-2 font-bold text-gray-900 group-hover:text-blue-600 transition truncate" style="color: var(--store-text);" title="{{ $product->name }}">
                                    <a href="{{ route('storefront.products.show', $product) }}">
                                        {{ $product->name }}
                                    </a>
                                </h3>

                                <div class="mt-3 flex items-center gap-2">
                                    <span class="text-base font-extrabold text-gray-900" style="color: var(--store-text);">
                                        {{ format_price($product->price) }}
                                    </span>

                                    @if ($hasDiscount)
                                        <span class="text-xs text-gray-400 line-through">
                                            {{ format_price($product->compare_at_price) }}
                                        </span>
                                    @endif
                                </div>

                                <div class="mt-auto pt-5">
                                    <a
                                        href="{{ route('storefront.products.show', $product) }}"
                                        class="block w-full rounded-xl px-4 py-2.5 text-center text-xs font-bold text-white shadow-xs transition-all duration-200 hover:opacity-90 active:scale-[0.98]"
                                        style="background-color: var(--store-secondary, #111827);"
                                    >
                                        View Product
                                    </a>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="col-span-full rounded-2xl border border-dashed border-gray-300 bg-white p-14 text-center">
                            <p class="text-sm text-gray-500">
                                Products will appear here.
                            </p>
                        </div>
                    @endforelse
                </div>
            </div>
        </section>

    @elseif ($sectionKey === 'cta' && $showCta)
        {{-- Modern Animated Call to Action (CTA) Section --}}
        <section class="mx-auto px-6 py-16 lg:px-8" style="max-width: var(--store-page-width);">
            <div
                class="relative overflow-hidden rounded-3xl px-6 py-16 text-center sm:px-16 text-white shadow-xl"
                style="background-color: var(--store-primary, #2563eb);"
            >
                {{-- Decorative Ambient Blobs --}}
                <div class="pointer-events-none absolute -top-16 -left-16 h-64 w-64 rounded-full bg-white/10 blur-2xl animate-float"></div>
                <div class="pointer-events-none absolute -bottom-16 -right-16 h-64 w-64 rounded-full bg-black/10 blur-2xl animate-float-reverse"></div>

                <div class="relative z-10 max-w-2xl mx-auto">
                    <span class="inline-block rounded-full bg-white/20 px-3.5 py-1 text-xs font-bold uppercase tracking-wider text-white backdrop-blur-md mb-4">
                        Exclusive Offers
                    </span>

                    <h2 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight leading-tight">
                        {{ $ctaHeading }}
                    </h2>

                    <p class="mt-4 text-base sm:text-lg text-white/90 leading-relaxed">
                        {{ $ctaDescription }}
                    </p>

                    <div class="mt-8">
                        <a
                            href="{{ $ctaButtonLink }}"
                            class="inline-flex items-center gap-2 rounded-xl bg-white px-8 py-4 text-sm font-bold shadow-lg transition-all duration-300 hover:bg-gray-100 hover:shadow-xl hover:-translate-y-0.5 active:translate-y-0"
                            style="color: var(--store-primary, #2563eb);"
                        >
                            <span>{{ $ctaButtonText }}</span>
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </section>
    @endif
@endforeach

@endsection