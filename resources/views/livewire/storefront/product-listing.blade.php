<div>

    {{-- Page Header --}}
    <section class="border-b border-gray-200 bg-gray-50">
        <div class="mx-auto max-w-7xl px-6 py-12 lg:px-8">

            <h1 class="text-3xl font-bold tracking-tight text-gray-900">
                Shop
            </h1>

            <p class="mt-2 text-sm text-gray-500">
                Browse our collection and find something you'll love.
            </p>

        </div>
    </section>


    {{-- Products --}}
    <section class="mx-auto max-w-7xl px-6 py-12 lg:px-8">

        {{-- Filters --}}
        <div class="mb-8 grid gap-4 md:grid-cols-3">

            {{-- Search --}}
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">
                    Search
                </label>

                <input
                    type="text"
                    wire:model.live.debounce.400ms="search"
                    placeholder="Search products..."
                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                >
            </div>


            {{-- Category --}}
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">
                    Category
                </label>

                <select
                    wire:model.live="category"
                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                >
                    <option value="">All Categories</option>

                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}">
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>


            {{-- Sort --}}
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">
                    Sort By
                </label>

                <select
                    wire:model.live="sort"
                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                >
                    <option value="latest">Latest</option>
                    <option value="price_low">Price: Low to High</option>
                    <option value="price_high">Price: High to Low</option>
                </select>
            </div>

        </div>


        {{-- Product Count --}}
        <div class="mb-6 flex items-center justify-between">

            <p class="text-sm text-gray-500">
                {{ $products->total() }} products
            </p>

        </div>


        {{-- Product Grid --}}
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">

            @forelse ($products as $product)

                <article
                    wire:key="product-{{ $product->id }}"
                    class="group overflow-hidden rounded-xl border border-gray-200 bg-white transition hover:-translate-y-1 hover:shadow-lg"
                >

                    {{-- Image --}}
                    <a
                        href="#"
                        class="block aspect-square overflow-hidden bg-gray-100"
                    >

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

                    </a>


                    {{-- Product Details --}}
                    <div class="p-4">

                        <p class="text-xs text-gray-500">
                            {{ $product->categories->first()?->name ?? 'Product' }}
                        </p>

                        <h2 class="mt-1 font-semibold text-gray-900">
                            {{ $product->name }}
                        </h2>


                        {{-- Price --}}
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

                        


                       <a href="{{ route('storefront.products.show', $product->slug) }}"class="mt-4 block w-full rounded-lg bg-gray-900 px-4 py-2.5 text-center text-sm font-medium text-white transition hover:bg-blue-600" >
                            View Product
                        </a>

                    </div>

                </article>

            @empty

                <div class="col-span-full rounded-xl border border-dashed border-gray-300 p-12 text-center">

                    <h3 class="font-semibold text-gray-900">
                        No products found
                    </h3>

                    <p class="mt-2 text-sm text-gray-500">
                        Try changing your search or category filter.
                    </p>

                </div>

            @endforelse

        </div>


        {{-- Pagination --}}
        @if ($products->hasPages())

            <div class="mt-10">
                {{ $products->links() }}
            </div>

        @endif

    </section>

</div>