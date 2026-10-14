<div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">

    <div class="mb-10">
        <h1 class="text-3xl font-bold tracking-tight text-gray-900">
            Shopping Cart
        </h1>

        <p class="mt-2 text-sm text-gray-500">
            Review your items before proceeding to checkout.
        </p>
    </div>

    @if (session()->has('cart_error'))
        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 flex items-center justify-between">
            <span>{{ session('cart_error') }}</span>
        </div>
    @endif

    @if (session()->has('cart_success'))
        <div class="mb-6 rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-700 flex items-center justify-between">
            <span>{{ session('cart_success') }}</span>
        </div>
    @endif

    @if ($hasStockIssues)
        <div class="mb-8 rounded-2xl border border-amber-200 bg-amber-50 p-5 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-start gap-3">
                    <svg class="h-6 w-6 shrink-0 text-amber-600 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div>
                        <h3 class="text-sm font-semibold text-amber-900">Inventory Notice</h3>
                        <p class="mt-1 text-xs text-amber-800">
                            Some items in your cart exceed available inventory or are currently out of stock.
                        </p>
                    </div>
                </div>
                <button
                    type="button"
                    wire:click="autoAdjustStock"
                    class="inline-flex shrink-0 items-center justify-center rounded-xl bg-amber-600 px-4 py-2.5 text-xs font-semibold text-white shadow-sm hover:bg-amber-700 transition"
                >
                    Auto-adjust to Available Stock
                </button>
            </div>
        </div>
    @endif

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
                                        {{ format_price($item['price']) }}
                                    </p>

                                    @if (!empty($item['is_out_of_stock']))
                                        <div class="mt-2">
                                            <span class="inline-flex items-center rounded-md bg-red-100 px-2 py-0.5 text-xs font-semibold text-red-700">
                                                Out of Stock
                                            </span>
                                        </div>
                                    @elseif (!empty($item['exceeds_stock']))
                                        <div class="mt-2">
                                            <span class="inline-flex items-center rounded-md bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-800">
                                                Only {{ $item['available_stock'] }} available in stock
                                            </span>
                                        </div>
                                    @endif
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
                                    {{ format_price($item['price'] * $item['quantity']) }}
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

                    @php
                        $freeShippingEnabled = (bool) store_setting('enable_free_shipping', true);
                        $freeShippingThreshold = (float) store_setting('free_shipping_threshold', 500);
                    @endphp

                    @if ($freeShippingEnabled && $freeShippingThreshold > 0)
                        <div class="mt-4 rounded-xl border border-blue-100 bg-blue-50/70 p-3.5 text-xs">
                            @if ($subtotal >= $freeShippingThreshold)
                                <div class="flex items-center gap-2 font-medium text-green-700">
                                    <svg class="h-4 w-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                                    </svg>
                                    <span>You unlocked <strong>Free Shipping</strong>!</span>
                                </div>
                            @else
                                <div class="flex items-center gap-2 text-blue-800">
                                    <svg class="h-4 w-4 shrink-0 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>Add <strong>{{ format_price($freeShippingThreshold - $subtotal) }}</strong> more for Free Shipping!</span>
                                </div>
                            @endif
                        </div>
                    @endif

                    <div class="mt-6 space-y-3 border-b border-gray-200 pb-4 text-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Subtotal</span>
                            <span class="font-semibold text-gray-900">{{ format_price($subtotal) }}</span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Estimated Shipping</span>
                            @if ($shippingFee <= 0)
                                <span class="font-semibold text-green-600">Free</span>
                            @else
                                <span class="font-semibold text-gray-900">{{ format_price($shippingFee) }}</span>
                            @endif
                        </div>

                        @if ($taxAmount > 0)
                            @if ($taxInclusive)
                                <div class="flex items-center justify-between text-xs text-gray-500">
                                    <span>Includes {{ $taxName }} ({{ $taxRate }}%)</span>
                                    <span class="font-medium text-gray-700">{{ format_price($taxAmount) }}</span>
                                </div>
                            @else
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-600">{{ $taxName }} ({{ $taxRate }}%)</span>
                                    <span class="font-semibold text-gray-900">{{ format_price($taxAmount) }}</span>
                                </div>
                            @endif
                        @endif
                    </div>

                    <div class="mt-4 flex items-center justify-between">
                        <span class="text-base font-semibold text-gray-900">
                            Estimated Total
                        </span>

                        <span class="text-xl font-bold text-gray-900">
                            {{ format_price($total) }}
                        </span>
                    </div>

                    @if ($hasStockIssues)
                        <button
                            type="button"
                            wire:click="autoAdjustStock"
                            class="block w-full rounded-lg bg-amber-600 px-4 py-3 text-center font-semibold text-white hover:bg-amber-700 transition"
                        >
                            Adjust Stock to Checkout
                        </button>
                        <p class="mt-2 text-center text-xs text-amber-700 font-medium">
                            Please resolve stock issues before proceeding to checkout.
                        </p>
                    @else
                        <a
                            href="{{ route('storefront.checkout') }}"
                            class="block w-full rounded-lg bg-blue-600 px-4 py-3 text-center font-semibold text-white hover:bg-blue-700 transition"
                        >
                            Proceed to Checkout
                        </a>
                    @endif

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