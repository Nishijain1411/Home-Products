<div class="mx-auto max-w-4xl px-4 py-16 sm:px-6 lg:px-8">

    <div class="rounded-2xl border border-gray-200 bg-white p-8 text-center">

        {{-- Success Icon --}}
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-green-100">

            <svg
                class="h-8 w-8 text-green-600"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M5 13l4 4L19 7"
                />
            </svg>

        </div>

        <h1 class="mt-6 text-3xl font-bold text-gray-900">
            Order Confirmed
        </h1>

        <p class="mt-3 text-gray-600">
            Thank you for your order.
        </p>

        <p class="mt-2 text-sm text-gray-500">
            Your order number is
            <span class="font-semibold text-gray-900">
                {{ $order->order_number }}
            </span>
        </p>

    </div>


    {{-- Order Details --}}
    <div class="mt-8 rounded-2xl border border-gray-200 bg-white p-6">

        <h2 class="text-lg font-semibold text-gray-900">
            Order Details
        </h2>

        <div class="mt-6 divide-y divide-gray-200">

            @foreach ($order->items as $item)

                <div class="flex items-center justify-between gap-4 py-4">

                    <div>
                        <p class="font-medium text-gray-900">
                            {{ $item->product_name }}
                        </p>

                        @if ($item->variant_name !== 'Default Variant')
                            <p class="mt-1 text-sm text-gray-500">
                                {{ $item->variant_name }}
                            </p>
                        @endif

                        <p class="mt-1 text-sm text-gray-500">
                            Quantity: {{ $item->quantity }}
                        </p>
                    </div>

                    <div class="text-right">
                        <p class="font-semibold text-gray-900">
                            ₹{{ number_format($item->total, 2) }}
                        </p>
                    </div>

                </div>

            @endforeach

        </div>

        <div class="mt-6 border-t border-gray-200 pt-6">

            <div class="flex justify-between text-sm">
                <span class="text-gray-600">
                    Subtotal
                </span>

                <span class="font-medium text-gray-900">
                    ₹{{ number_format($order->subtotal, 2) }}
                </span>
            </div>

            <div class="mt-3 flex justify-between text-sm">
                <span class="text-gray-600">
                    Shipping
                </span>

                <span class="font-medium text-gray-900">
                    ₹{{ number_format($order->shipping_amount, 2) }}
                </span>
            </div>

            <div class="mt-3 flex justify-between text-sm">
                <span class="text-gray-600">
                    Tax
                </span>

                <span class="font-medium text-gray-900">
                    ₹{{ number_format($order->tax_amount, 2) }}
                </span>
            </div>

            <div class="mt-4 flex justify-between border-t border-gray-200 pt-4">

                <span class="text-base font-semibold text-gray-900">
                    Total
                </span>

                <span class="text-xl font-bold text-gray-900">
                    ₹{{ number_format($order->total, 2) }}
                </span>

            </div>

        </div>

    </div>


    {{-- Actions --}}
    <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">

        <a
            href="{{ route('storefront.shop') }}"
            class="rounded-xl bg-gray-900 px-6 py-3 text-center text-sm font-semibold text-white hover:bg-blue-600"
        >
            Continue Shopping
        </a>

        <a
            href="{{ route('storefront.home') }}"
            class="rounded-xl border border-gray-300 px-6 py-3 text-center text-sm font-semibold text-gray-700 hover:bg-gray-50"
        >
            Back to Home
        </a>

    </div>

</div>