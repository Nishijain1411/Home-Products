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
        {{-- Hero Section --}}
        <section class="relative overflow-hidden" style="background-color: var(--store-secondary);">
            <div class="mx-auto px-6 py-24 lg:px-8 lg:py-32" style="max-width: var(--store-page-width);">
                <div class="max-w-2xl">
                    <span class="mb-4 inline-block text-sm font-semibold uppercase tracking-widest" style="color: var(--store-primary);">
                        {{ $heroEyebrow }}
                    </span>

                    <h1 class="text-4xl font-bold tracking-tight text-white sm:text-5xl lg:text-6xl">
                        {{ $heroHeading }}
                        <span style="color: var(--store-primary);">
                            {{ $heroHighlight }}
                        </span>
                    </h1>

                    <p class="mt-6 max-w-xl text-lg leading-8 text-gray-300">
                        {{ $heroDescription }}
                    </p>

                    <div class="mt-8 flex flex-wrap gap-4">
                        <a
                            href="{{ $heroPrimaryButtonLink }}"
                            class="rounded-lg px-6 py-3 text-sm font-semibold text-white transition hover:opacity-90 shadow-sm"
                            style="background-color: var(--store-primary);"
                        >
                            {{ $heroPrimaryButtonText }}
                        </a>

                        <a
                            href="{{ $heroSecondaryButtonLink }}"
                            class="rounded-lg border border-white/20 px-6 py-3 text-sm font-semibold text-white transition hover:bg-white/10"
                        >
                            {{ $heroSecondaryButtonText }}
                        </a>
                    </div>
                </div>
            </div>
        </section>
    @elseif ($sectionKey === 'categories' && $showCategories)
        {{-- Categories Section --}}
        <section id="categories" class="mx-auto px-6 py-16 lg:px-8" style="max-width: var(--store-page-width);">
            <div class="mb-8">
                <h2 class="text-2xl font-bold tracking-tight" style="color: var(--store-text);">
                    {{ $categoryHeading }}
                </h2>

                <p class="mt-2 text-sm text-gray-500">
                    {{ $categoryDescription }}
                </p>
            </div>

            <div class="grid {{ $categoryGridClass }} gap-4">
                @forelse ($categories ?? [] as $category)
                    <a
                        href="{{ route('storefront.categories.show', $category->slug) }}"
                        class="group rounded-xl border border-gray-200 bg-white p-6 transition hover:-translate-y-1 hover:shadow-lg"
                    >
                        <h3 class="font-semibold transition group-hover:opacity-80" style="color: var(--store-text);">
                            {{ $category->name }}
                        </h3>

                        <p class="mt-2 text-sm text-gray-500">
                            Explore products
                        </p>
                    </a>
                @empty
                    <div class="col-span-full rounded-xl border border-dashed border-gray-300 p-10 text-center">
                        <p class="text-sm text-gray-500">
                            Categories will appear here.
                        </p>
                    </div>
                @endforelse
            </div>
        </section>
    @elseif ($sectionKey === 'products' && $showProducts)
        {{-- Featured Products Section --}}
        <section id="products" class="bg-gray-50/70 border-t border-b border-gray-100">
            <div class="mx-auto px-6 py-16 lg:px-8" style="max-width: var(--store-page-width);">
                <div class="mb-8 flex items-end justify-between gap-4">
                    <div>
                        <h2 class="text-2xl font-bold tracking-tight" style="color: var(--store-text);">
                            {{ $productsHeading }}
                        </h2>

                        <p class="mt-2 text-sm text-gray-500">
                            {{ $productsDescription }}
                        </p>
                    </div>

                    @if ($productsViewAllText)
                        <a
                            href="{{ $productsViewAllLink }}"
                            class="hidden text-sm font-semibold transition hover:opacity-80 sm:block"
                            style="color: var(--store-primary);"
                        >
                            {{ $productsViewAllText }} &rarr;
                        </a>
                    @endif
                </div>

                <div class="grid {{ $productsGridClass }} gap-4">
                    @forelse ($products ?? [] as $product)
                        <article class="overflow-hidden rounded-xl border border-gray-200 bg-white transition hover:-translate-y-1 hover:shadow-lg">
                            <div class="aspect-square overflow-hidden bg-gray-100">
                                @if ($product->images && $product->images->first())
                                    <img
                                        src="{{ Storage::disk('public')->url($product->images->first()->path) }}"
                                        alt="{{ $product->images->first()->alt_text ?? $product->name }}"
                                        class="h-full w-full object-cover transition duration-300 hover:scale-105"
                                    >
                                @else
                                    <div class="flex h-full items-center justify-center text-sm text-gray-400">
                                        No Image
                                    </div>
                                @endif
                            </div>

                            <div class="p-4">
                                <h3 class="font-semibold truncate" style="color: var(--store-text);">
                                    {{ $product->name }}
                                </h3>

                                <div class="mt-2 flex items-center gap-2">
                                    <span class="font-semibold" style="color: var(--store-text);">
                                        {{ format_price($product->price) }}
                                    </span>

                                    @if ($product->compare_at_price)
                                        <span class="text-sm text-gray-400 line-through">
                                            {{ format_price($product->compare_at_price) }}
                                        </span>
                                    @endif
                                </div>

                                <a
                                    href="{{ route('storefront.products.show', $product) }}"
                                    class="mt-4 block w-full rounded-lg px-4 py-2.5 text-center text-sm font-medium text-white transition hover:opacity-90 shadow-sm"
                                    style="background-color: var(--store-secondary);"
                                >
                                    View Product
                                </a>
                            </div>
                        </article>
                    @empty
                        <div class="col-span-full rounded-xl border border-dashed border-gray-300 bg-white p-12 text-center">
                            <p class="text-sm text-gray-500">
                                Products will appear here.
                            </p>
                        </div>
                    @endforelse
                </div>
            </div>
        </section>
    @elseif ($sectionKey === 'cta' && $showCta)
        {{-- Call to Action (CTA) Section --}}
        <section class="mx-auto px-6 py-16 lg:px-8" style="max-width: var(--store-page-width);">
            <div
                class="rounded-2xl px-6 py-12 text-center sm:px-12 text-white shadow-lg"
                style="background-color: var(--store-primary);"
            >
                <h2 class="text-3xl font-bold text-white">
                    {{ $ctaHeading }}
                </h2>

                <p class="mx-auto mt-4 max-w-xl text-white/90">
                    {{ $ctaDescription }}
                </p>

                <a
                    href="{{ $ctaButtonLink }}"
                    class="mt-8 inline-flex rounded-lg bg-white px-6 py-3 text-sm font-semibold transition hover:bg-gray-100 shadow-sm"
                    style="color: var(--store-primary);"
                >
                    {{ $ctaButtonText }}
                </a>
            </div>
        </section>
    @endif
@endforeach

@endsection