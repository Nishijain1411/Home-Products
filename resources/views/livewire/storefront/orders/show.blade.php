
<div class="mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:px-8">

    <a href="{{ route('storefront.orders.index') }}"
       class="mb-6 inline-flex items-center text-sm font-medium text-gray-600
              hover:text-blue-600">
        &larr; Back to My Orders
    </a>

    <div class="mb-6 rounded-xl border border-gray-200 bg-white p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center
                    sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">
                    Order Details
                </h1>
                <p class="mt-2 text-sm text-gray-500">
                    Order #{{ $order->order_number }}
                </p>
                <p class="mt-1 text-sm text-gray-500">
                    Placed {{ $order->created_at->format('d M Y, h:i A') }}
                </p>
            </div>

            <span class="inline-flex w-fit rounded-full px-3 py-1.5
                text-sm font-medium
                {{ match ($order->status) {
                    'delivered' => 'bg-green-100 text-green-700',
                    'cancelled' => 'bg-red-100 text-red-700',
                    'shipped' => 'bg-blue-100 text-blue-700',
                    'processing' => 'bg-amber-100 text-amber-700',
                    default => 'bg-gray-100 text-gray-700',
                } }}">
                {{ ucfirst($order->status) }}
            </span>
        </div>

        @if ($order->status === 'cancelled')
            <div class="mt-5 rounded-lg bg-red-50 p-4 text-sm text-red-700">
                This order has been cancelled.
            </div>
        @else
            @php
                $stages = ['pending', 'processing', 'shipped', 'delivered'];
                $currentStage = array_search($order->status, $stages, true);
            @endphp

            <div class="mt-8 grid grid-cols-2 gap-4 sm:grid-cols-4">
                @foreach ($stages as $index => $stage)
                    <div class="flex items-center gap-2">
                        <span class="flex h-7 w-7 shrink-0 items-center
                            justify-center rounded-full text-xs font-semibold
                            {{ $currentStage !== false && $index <= $currentStage
                                ? 'bg-green-100 text-green-700'
                                : 'bg-gray-100 text-gray-500' }}">
                            @if ($currentStage !== false && $index < $currentStage)
                                ✓
                            @else
                                {{ $index + 1 }}
                            @endif
                        </span>
                        <span class="text-xs font-medium text-gray-700 sm:text-sm">
                            {{ ucfirst($stage) }}
                        </span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-6">
        <h2 class="mb-5 text-lg font-semibold text-gray-900">Items Ordered</h2>

        <div class="divide-y divide-gray-200">
            @foreach ($order->items as $item)
                <div class="flex items-start justify-between gap-4 py-4">
                    <div>
                        <p class="font-medium text-gray-900">
                            {{ $item->product?->name ?? 'Product unavailable' }}
                        </p>

                        @if ($item->variant)
                            <p class="mt-1 text-sm text-gray-500">
                                {{ $item->variant->sku }}
                            </p>
                        @endif

                        <p class="mt-1 text-sm text-gray-500">
                            Quantity: {{ $item->quantity }}
                        </p>
                    </div>

                    <p class="shrink-0 text-sm font-medium text-gray-900">
                        ₹{{ number_format(
                            (float) $item->price * (int) $item->quantity,
                            2
                        ) }}
                    </p>
                </div>
            @endforeach
        </div>

        <div class="mt-4 space-y-3 border-t border-gray-200 pt-5 text-sm">
            <div class="flex justify-between gap-4">
                <span class="text-gray-500">Subtotal</span>
                <span class="text-gray-900">
                    ₹{{ number_format((float) $order->subtotal, 2) }}
                </span>
            </div>

            <div class="flex justify-between gap-4">
                <span class="text-gray-500">Shipping</span>
                <span class="text-gray-900">
                    ₹{{ number_format((float) $order->shipping_amount, 2) }}
                </span>
            </div>

            <div class="flex justify-between gap-4">
                <span class="text-gray-500">Tax</span>
                <span class="text-gray-900">
                    ₹{{ number_format((float) $order->tax_amount, 2) }}
                </span>
            </div>

            <div class="flex justify-between gap-4 border-t border-gray-200
                        pt-4 text-base font-semibold">
                <span class="text-gray-900">Total</span>
                <span class="text-gray-900">
                    ₹{{ number_format((float) $order->total, 2) }}
                </span>
            </div>

            <div class="flex justify-between gap-4 pt-2">
                <span class="text-gray-500">Payment status</span>
                <span class="font-medium text-gray-900">
                    {{ ucfirst($order->payment_status) }}
                </span>
            </div>
        </div>
    </div>

    <div class="mt-6 rounded-xl border border-gray-200 bg-white p-6">
        <h2 class="mb-4 text-lg font-semibold text-gray-900">
            Shipping Address
        </h2>

        @php
            $address = $order->shipping_address ?? [];
        @endphp

        <div class="space-y-1 text-sm text-gray-600">
            <p>
                {{ trim(($address['first_name'] ?? '') . ' ' .
                    ($address['last_name'] ?? '')) }}
            </p>
            <p>{{ $address['address'] ?? '' }}</p>
            <p>
                {{ collect([
                    $address['city'] ?? '',
                    $address['state'] ?? '',
                    $address['postal_code'] ?? '',
                ])->filter()->implode(', ') }}
            </p>
            <p>{{ $address['country'] ?? '' }}</p>

            @if (!empty($address['phone']))
                <p class="pt-1">Phone: {{ $address['phone'] }}</p>
            @endif
        </div>
    </div>

</div>
