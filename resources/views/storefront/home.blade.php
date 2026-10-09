@extends('storefront.layouts.app')

@section('content')

    {{-- Hero --}}
    <section class="relative overflow-hidden bg-gray-900">
        <div class="mx-auto max-w-7xl px-6 py-24 lg:px-8 lg:py-32">
            <div class="max-w-2xl">
                <span class="mb-4 inline-block text-sm font-semibold uppercase tracking-widest text-blue-400">
                    New Collection
                </span>

                <h1 class="text-4xl font-bold tracking-tight text-white sm:text-5xl lg:text-6xl">
                    Everything you need,
                    <span class="text-blue-400">all in one place.</span>
                </h1>

                <p class="mt-6 max-w-xl text-lg leading-8 text-gray-300">
                    Discover our latest products, collections and exclusive offers.
                </p>

                <div class="mt-8 flex flex-wrap gap-4">
                    <a
                        href="#products"
                        class="rounded-lg bg-blue-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-blue-700"
                    >
                        Shop Now
                    </a>

                    <a
                        href="#categories"
                        class="rounded-lg border border-white/20 px-6 py-3 text-sm font-semibold text-white transition hover:bg-white/10"
                    >
                        Explore Categories
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- Categories --}}
    <section id="categories" class="mx-auto max-w-7xl px-6 py-16 lg:px-8">
        <div class="mb-8">
            <h2 class="text-2xl font-bold tracking-tight text-gray-900">
                Shop by Category
            </h2>

            <p class="mt-2 text-sm text-gray-500">
                Explore our product categories.
            </p>
        </div>

        <div class="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-4">
            @forelse ($categories ?? [] as $category)
                <a
                    href="#"
                    class="group rounded-xl border border-gray-200 bg-white p-6 transition hover:-translate-y-1 hover:border-blue-200 hover:shadow-lg"
                >
                    <h3 class="font-semibold text-gray-900 group-hover:text-blue-600">
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

    {{-- Products --}}
    <section id="products" class="bg-gray-50">
        <div class="mx-auto max-w-7xl px-6 py-16 lg:px-8">
            <div class="mb-8 flex items-end justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight text-gray-900">
                        Featured Products
                    </h2>

                    <p class="mt-2 text-sm text-gray-500">
                        Discover some of our latest products.
                    </p>
                </div>

                <a
                    href="#"
                    class="hidden text-sm font-semibold text-blue-600 hover:text-blue-700 sm:block"
                >
                    View All
                </a>
            </div>

            <div class="grid grid-cols-2 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @forelse ($products ?? [] as $product)
                    <article class="overflow-hidden rounded-xl border border-gray-200 bg-white transition hover:-translate-y-1 hover:shadow-lg">

                        <div class="aspect-square overflow-hidden bg-gray-100">
                            @if ($product->images->first())
                                <img
                                    src="{{ Storage::disk('public')->url($product->images->first()->path) }}"
                                    alt="{{ $product->images->first()->alt_text ?? $product->name }}"
                                    class="h-full w-full object-cover transition duration-300 group-hover:scale-105"
                                >
                            @else
                                <div class="flex h-full items-center justify-center text-sm text-gray-400">
                                    No Image
                                </div>
                            @endif
                        </div>

                        <div class="p-4">
                            <h3 class="font-semibold text-gray-900">
                                {{ $product->name }}
                            </h3>

                            <div class="mt-2 flex items-center gap-2">
                                <span class="font-semibold text-gray-900">
                                    ₹{{ number_format($product->price, 2) }}
                                </span>

                                @if ($product->compare_at_price)
                                    <span class="text-sm text-gray-400 line-through">
                                        ₹{{ number_format($product->compare_at_price, 2) }}
                                    </span>
                                @endif
                            </div>

                            <button
                                type="button"
                                class="mt-4 w-full rounded-lg bg-gray-900 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-blue-600"
                            >
                                View Product
                            </button>
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

    {{-- CTA --}}
    <section class="mx-auto max-w-7xl px-6 py-16 lg:px-8">
        <div class="rounded-2xl bg-blue-600 px-6 py-12 text-center sm:px-12">
            <h2 class="text-3xl font-bold text-white">
                Discover something you'll love.
            </h2>

            <p class="mx-auto mt-4 max-w-xl text-blue-100">
                Browse our collection and find the perfect products for you.
            </p>

            <a
                href="#products"
                class="mt-8 inline-flex rounded-lg bg-white px-6 py-3 text-sm font-semibold text-blue-600 transition hover:bg-gray-100"
            >
                Start Shopping
            </a>
        </div>
    </section>

@endsection