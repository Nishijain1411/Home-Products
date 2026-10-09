<div>

    {{-- Breadcrumb --}}
    <section class="border-b border-gray-200 bg-gray-50">
        <div class="mx-auto max-w-7xl px-6 py-5 lg:px-8">

            <nav class="text-sm text-gray-500">

                <a href="{{ route('storefront.home') }}" class="hover:text-blue-600">
                    Home
                </a>

                <span class="mx-2">/</span>

                <a href="{{ route('storefront.shop') }}" class="hover:text-blue-600">
                    Shop
                </a>

                <span class="mx-2">/</span>

                <span class="text-gray-900">
                    {{ $product->name }}
                </span>

            </nav>

        </div>
    </section>


    {{-- Product --}}
    <section class="mx-auto max-w-7xl px-6 py-12 lg:px-8 lg:py-16">

        <div class="grid gap-10 lg:grid-cols-2 lg:gap-16">


            {{-- ============================= --}}
            {{-- Product Images --}}
            {{-- ============================= --}}

            <div>

                {{-- Main Image --}}
                <div class="aspect-square overflow-hidden rounded-2xl bg-gray-100">

                    @php
                    $selectedProductImage = $product->images
                    ->firstWhere('id', $selectedImage)
                    ?? $product->images->first();
                    @endphp

                    @if ($selectedProductImage)

                    <img src="{{ Storage::disk('public')->url($selectedProductImage->path) }}"
                        alt="{{ $selectedProductImage->alt_text ?? $product->name }}"
                        class="h-full w-full object-cover">

                    @else

                    <div class="flex h-full items-center justify-center text-gray-400">
                        No Image Available
                    </div>

                    @endif

                </div>


                {{-- Thumbnails --}}
                @if ($product->images->count() > 1)

                <div class="mt-4 grid grid-cols-5 gap-3">

                    @foreach ($product->images as $image)

                    <button type="button" wire:click="selectImage({{ $image->id }})" class="aspect-square overflow-hidden rounded-lg border-2 transition
                                    {{ $selectedImage === $image->id
                                        ? 'border-blue-600'
                                        : 'border-transparent hover:border-gray-300' }}">

                        <img src="{{ Storage::disk('public')->url($image->path) }}"
                            alt="{{ $image->alt_text ?? $product->name }}" class="h-full w-full object-cover">

                    </button>

                    @endforeach

                </div>

                @endif

            </div>


            {{-- ============================= --}}
            {{-- Product Information --}}
            {{-- ============================= --}}

            <div>

                {{-- Categories --}}
                @if ($product->categories->isNotEmpty())

                <div class="mb-4 flex flex-wrap gap-2">

                    @foreach ($product->categories as $category)

                    <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-medium text-blue-600">
                        {{ $category->name }}
                    </span>

                    @endforeach

                </div>

                @endif


                {{-- Product Name --}}
                <h1 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">
                    {{ $product->name }}
                </h1>


                {{-- SKU --}}
                <p class="mt-3 text-sm text-gray-500">
                    SKU: {{ $product->sku }}
                </p>


                {{-- Price --}}
                <div class="mt-6 flex items-center gap-3">

                    <span class="text-2xl font-bold text-gray-900">
                        ₹{{ number_format($product->price, 2) }}
                    </span>

                    @if ($product->compare_at_price)

                    <span class="text-lg text-gray-400 line-through">
                        ₹{{ number_format($product->compare_at_price, 2) }}
                    </span>

                    @if ($product->compare_at_price > $product->price)

                    <span class="rounded-full bg-red-50 px-3 py-1 text-xs font-semibold text-red-600">
                        Sale
                    </span>

                    @endif

                    @endif

                </div>
                @if ($selectedVariant)

                                @php
                                    $stock = $selectedVariant->inventory?->quantity ?? 0;
                                @endphp

                                @if ($stock > 0)

                                    <p class="mt-3 text-sm font-medium text-green-600">
                                        In stock

                                        @if ($stock <= 10)
                                            · Only {{ $stock }} left
                                        @endif
                                    </p>

                                @else

                                    <p class="mt-3 text-sm font-medium text-red-600">
                                        Out of stock
                                    </p>

                                @endif

                            @elseif ($product->options->isNotEmpty())

                                <p class="mt-3 text-sm text-gray-500">
                                    Please select product options.
                                </p>

                @endif


                {{-- Description --}}
                @if ($product->description)

                    <div class="mt-8 border-t border-gray-200 pt-8">

                        <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-900">
                            Product Description
                        </h2>

                        <div class="mt-4 text-sm leading-7 text-gray-600">
                            {!! nl2br(e($product->description)) !!}
                        </div>

                    </div>

                @endif


                {{-- Options --}}
                @if ($product->options->isNotEmpty())

                    <div class="mt-8 space-y-6 border-t border-gray-200 pt-8">

                        @foreach ($product->options as $option)

                        <div>

                            <label class="mb-3 block text-sm font-semibold text-gray-900">
                                {{ $option->name }}
                            </label>

                            <div class="flex flex-wrap gap-2">

                                @foreach ($option->values as $value)

                                @php
                                $isSelected =
                                ($selectedOptions[$option->id] ?? null) == $value->id;
                                @endphp

                                <button type="button" wire:click="selectOption({{ $option->id }}, {{ $value->id }})"
                                    class="rounded-lg border px-4 py-2 text-sm font-medium transition
                                                    {{ $isSelected
                                                        ? 'border-blue-600 bg-blue-600 text-white'
                                                        : 'border-gray-300 bg-white text-gray-700 hover:border-blue-600 hover:text-blue-600' }}">
                                    {{ $value->value }}
                                </button>

                                @endforeach

                            </div>

                        </div>

                        @endforeach

                    </div>

                @endif


                {{-- Quantity --}}
                <div class="mt-8">

                    <label class="mb-3 block text-sm font-semibold text-gray-900">
                        Quantity
                    </label>

                    <div class="flex w-fit items-center overflow-hidden rounded-lg border border-gray-300">

                        <button type="button" wire:click="decrementQuantity"
                            class="flex h-11 w-11 items-center justify-center text-gray-600 transition hover:bg-gray-100">
                            −
                        </button>

                        <span
                            class="flex h-11 w-12 items-center justify-center border-x border-gray-300 text-sm font-medium">
                            {{ $quantity }}
                        </span>

                        <button type="button" wire:click="incrementQuantity"
                            class="flex h-11 w-11 items-center justify-center text-gray-600 transition hover:bg-gray-100">
                            +
                        </button>

                    </div>

                </div>


                {{-- Add To Cart --}}
            @php
                $stock = $selectedVariant?->inventory?->quantity ?? 0;
                $canAddToCart = $selectedVariant && $stock >= $quantity;
            @endphp

            <div class="mt-10 border-t border-gray-200 pt-8">

                <button
                    type="button"
                    wire:click="addToCart"
                    wire:loading.attr="disabled"
                    @disabled(!$canAddToCart)
                    class="w-full rounded-xl px-6 py-4 text-sm font-semibold transition
                        {{ $canAddToCart
                            ? 'bg-gray-900 text-white hover:bg-blue-600'
                            : 'cursor-not-allowed bg-gray-200 text-gray-400' }}"
                >
                    <span wire:loading.remove wire:target="addToCart">
                        @if (!$selectedVariant)
                            Select Available Variant
                        @elseif ($stock <= 0)
                            Out of Stock
                        @else
                            Add to Cart
                        @endif
                    </span>

                    <span wire:loading wire:target="addToCart">
                        Adding...
                    </span>
                </button>

                @if (session()->has('cart_success'))
                    <div class="mt-4 rounded-lg bg-green-50 px-4 py-3 text-sm font-medium text-green-700">
                        {{ session('cart_success') }}
                    </div>
                @endif

            </div>


                {{-- Product Information --}}
                <div class="mt-8 border-t border-gray-200 pt-8">

                    <div class="grid grid-cols-2 gap-4 text-sm">

                        @if ($product->product_type)

                        <div>
                            <span class="block text-gray-500">
                                Product Type
                            </span>

                            <span class="mt-1 block font-medium text-gray-900">
                                {{ $product->product_type }}
                            </span>
                        </div>

                        @endif

                        @if ($product->vendor)

                        <div>
                            <span class="block text-gray-500">
                                Vendor
                            </span>

                            <span class="mt-1 block font-medium text-gray-900">
                                {{ $product->vendor }}
                            </span>
                        </div>

                        @endif

                    </div>

                </div>

            </div>

        </div>

    </section>

</div>