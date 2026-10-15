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

        <p class="mt-1 text-xs text-gray-400">
            Placed on {{ format_date($order->created_at, true) }}
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
                            {{ format_price($item->total) }}
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
                    {{ format_price($order->subtotal) }}
                </span>
            </div>

            @if ($order->discount_amount > 0)
                <div class="mt-3 flex justify-between text-sm text-emerald-600 font-medium">
                    <span>
                        Discount ({{ $order->coupon_code ?? 'Coupon' }})
                    </span>

                    <span class="font-semibold">
                        -{{ format_price($order->discount_amount) }}
                    </span>
                </div>
            @endif

            <div class="mt-3 flex justify-between text-sm">
                <span class="text-gray-600">
                    Shipping
                </span>

                <span class="font-medium text-gray-900">
                    @if ($order->shipping_amount <= 0)
                        Free
                    @else
                        {{ format_price($order->shipping_amount) }}
                    @endif
                </span>
            </div>

            <div class="mt-3 flex justify-between text-sm">
                <span class="text-gray-600">
                    Tax
                </span>

                <span class="font-medium text-gray-900">
                    {{ format_price($order->tax_amount) }}
                </span>
            </div>

            <div class="mt-4 flex justify-between border-t border-gray-200 pt-4">

                <span class="text-base font-semibold text-gray-900">
                    Total
                </span>

                <span class="text-xl font-bold text-gray-900">
                    {{ format_price($order->total) }}
                </span>

            </div>

        </div>

    {{-- Order Summary & Payment Info --}}
    <div class="mt-8 grid gap-6 sm:grid-cols-2">

        {{-- Payment Details --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-6">
            <h3 class="text-xs font-semibold uppercase tracking-wider text-gray-500">
                Payment Information
            </h3>

            <div class="mt-4 space-y-3 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Method:</span>
                    <span class="font-medium text-gray-900">{{ $order->payment_method_label }}</span>
                </div>

                <div class="flex justify-between items-center">
                    <span class="text-gray-500">Status:</span>
                    @if ($order->payment_status === 'paid')
                        <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-semibold text-green-800">
                            Paid
                        </span>
                    @else
                        <span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-800">
                            Pending (Pay on Delivery)
                        </span>
                    @endif
                </div>

                @if ($order->payment_reference)
                    <div class="flex justify-between">
                        <span class="text-gray-500">Reference:</span>
                        <span class="font-mono text-xs font-medium text-gray-800">{{ $order->payment_reference }}</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- Delivery Address --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-6">
            <h3 class="text-xs font-semibold uppercase tracking-wider text-gray-500">
                Delivery Address
            </h3>

            @php $shipping = $order->shipping_address ?? []; @endphp
            <div class="mt-4 text-sm text-gray-700 leading-relaxed">
                <p class="font-semibold text-gray-900">{{ trim(($shipping['first_name'] ?? '') . ' ' . ($shipping['last_name'] ?? '')) }}</p>
                <p>{{ $shipping['address'] ?? '' }}</p>
                <p>{{ ($shipping['city'] ?? '') . (!empty($shipping['city']) && !empty($shipping['state']) ? ', ' : '') . ($shipping['state'] ?? '') }} {{ $shipping['postal_code'] ?? '' }}</p>
                <p>{{ $shipping['country'] ?? '' }}</p>
                @if (!empty($shipping['phone']))
                    <p class="mt-1 text-xs text-gray-500">Phone: {{ $shipping['phone'] }}</p>
                @endif
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