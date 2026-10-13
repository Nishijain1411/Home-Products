<div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">

    <div class="mb-10">
        <h1 class="text-3xl font-bold tracking-tight text-gray-900">
            Shopping Cart
        </h1>

        <p class="mt-2 text-sm text-gray-500">
            Review your items before proceeding to checkout.
        </p>
    </div>

    @if (empty($cart))

        <div class="rounded-2xl border border-gray-200 bg-white px-6 py-16 text-center">

            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-gray-100">
                <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2 4h13M9 21h.01M18 21h.01"/>
                </svg>
            </div>

            <h2 class="mt-5 text-xl font-semibold text-gray-900">
                Your cart is empty
            </h2>

            <p class="mt-2 text-sm text-gray-500">
                Add some products to your cart to get started.
            </p>

            <a
                href="{{ route('storefront.shop') }}"
                class="mt-6 inline-flex rounded-lg bg-gray-900 px-6 py-3 text-sm font-semibold text-white hover:bg-blue-600"
            >
                Continue Shopping
            </a>

        </div>

    @else

        <div class="grid gap-8 lg:grid-cols-3">

            {{-- Cart Items --}}
            <div class="space-y-4 lg:col-span-2">

                @foreach ($cart as $item)

                    <div
                        wire:key="cart-item-{{ $item['variant_id'] }}"
                        class="rounded-2xl border border-gray-200 bg-white p-5"
                        >
                        <div class="flex gap-5">

                            {{-- Product Image --}}
                            <div class="h-24 w-24 shrink-0 overflow-hidden rounded-xl bg-gray-100">

                                @if (!empty($item['image']))
                                    <img
                                        src="{{ Storage::url($item['image']) }}"
                                        alt="{{ $item['product_name'] }}"
                                        class="h-full w-full object-cover"
                                    >
                                @else
                                    <div class="flex h-full w-full items-center justify-center text-xs text-gray-400">
                                        No Image
                                    </div>
                                @endif

                            </div>

                            {{-- Product Information --}}
                            <div class="flex min-w-0 flex-1 flex-col justify-between">

                                <div>
                                    <h2 class="font-semibold text-gray-900">
                                        {{ $item['product_name'] }}
                                    </h2>

                                    @if ($item['title'] !== 'Default Variant')
                                        <p class="mt-1 text-sm text-gray-500">
                                            {{ $item['title'] }}
                                        </p>
                                    @endif

                                    <p class="mt-2 text-sm text-gray-600">
                                        ₹{{ number_format($item['price'], 2) }}
                                    </p>
                                </div>

                                <button
                                    type="button"
                                    wire:click="removeItem({{ $item['variant_id'] }})"
                                    class="mt-3 w-fit text-xs font-medium text-red-600 hover:text-red-700"
                                >
                                    Remove
                                </button>

                            </div>

                            {{-- Quantity --}}
                            <div class="flex items-center self-center rounded-lg border border-gray-300">

                                <button
                                    type="button"
                                    wire:click="updateQuantity(
                                        {{ $item['variant_id'] }},
                                        {{ max(1, $item['quantity'] - 1) }}
                                    )"
                                    class="px-3 py-2 text-gray-600 hover:bg-gray-100"
                                >
                                    −
                                </button>

                                <span class="min-w-10 text-center text-sm font-medium">
                                    {{ $item['quantity'] }}
                                </span>

                                <button
                                    type="button"
                                    wire:click="updateQuantity(
                                        {{ $item['variant_id'] }},
                                        {{ $item['quantity'] + 1 }}
                                    )"
                                    class="px-3 py-2 text-gray-600 hover:bg-gray-100"
                                >
                                    +
                                </button>

                            </div>

                            {{-- Item Total --}}
                            <div class="w-24 self-center text-right">
                                <p class="font-semibold text-gray-900">
                                    ₹{{ number_format($item['price'] * $item['quantity'], 2) }}
                                </p>
                            </div>

                        </div>
                    </div>

                @endforeach

                <button
                    type="button"
                    wire:click="clearCart"
                    class="text-sm font-medium text-red-600 hover:text-red-700"
                >
                    Clear Cart
                </button>

            </div>

            {{-- Summary --}}
            <div>

                <div class="rounded-2xl border border-gray-200 bg-white p-6">

                    <h2 class="text-lg font-semibold text-gray-900">
                        Order Summary
                    </h2>

                    <div class="mt-6 flex items-center justify-between border-b border-gray-200 pb-4">
                        <span class="text-sm text-gray-600">
                            Subtotal
                        </span>

                        <span class="font-semibold text-gray-900">
                            ₹{{ number_format($subtotal, 2) }}
                        </span>
                    </div>

                    <div class="mt-4 flex items-center justify-between">
                        <span class="text-base font-semibold text-gray-900">
                            Total
                        </span>

                        <span class="text-xl font-bold text-gray-900">
                            ₹{{ number_format($subtotal, 2) }}
                        </span>
                    </div>

                    <a
                        href="{{ route('storefront.checkout') }}"
                        class="block w-full rounded-lg bg-blue-600 px-4 py-3 text-center font-semibold text-white hover:bg-blue-700"
                    >
                        Proceed to Checkout
                    </a>

                    <a
                        href="{{ route('storefront.shop') }}"
                        class="mt-3 block text-center text-sm font-medium text-gray-600 hover:text-gray-900"
                    >
                        Continue Shopping
                    </a>

                </div>

            </div>

        </div>

    @endif

</div>