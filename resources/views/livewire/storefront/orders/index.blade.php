
<div class="mx-auto max-w-6xl px-4 py-10 sm:px-6 lg:px-8">

    <div class="mb-8">
        <h1 class="text-3xl font-semibold text-gray-900">My Orders</h1>
        <p class="mt-2 text-sm text-gray-500">
            View your orders and track their progress.
        </p>
    </div>

    @if ($orders->isEmpty())
        <div class="rounded-xl border border-gray-200 bg-white p-10 text-center">
            <h2 class="text-lg font-semibold text-gray-900">
                No orders yet
            </h2>
            <p class="mt-2 text-sm text-gray-500">
                Your orders will appear here after you place an order
                while logged in.
            </p>
            <a href="{{ route('storefront.shop') }}"
               class="mt-5 inline-flex rounded-lg bg-blue-600 px-5 py-3
                      text-sm font-medium text-white hover:bg-blue-700">
                Start Shopping
            </a>
        </div>
    @else
        <div class="space-y-4">
            @foreach ($orders as $order)
                <div wire:key="customer-order-{{ $order->id }}"
                     class="rounded-xl border border-gray-200 bg-white p-5 sm:p-6">

                    <div class="flex flex-col gap-4 sm:flex-row
                                sm:items-center sm:justify-between">

                        <div>
                            <p class="text-xs text-gray-500">Order number</p>
                            <h2 class="mt-1 font-semibold text-gray-900">
                                {{ $order->order_number }}
                            </h2>
                            <p class="mt-1 text-sm text-gray-500">
                                Placed {{ format_date($order->created_at) }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-gray-500">Order status</p>
                            <span class="mt-1 inline-flex rounded-full px-3 py-1
                                text-xs font-medium
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

                        <div>
                            <p class="text-xs text-gray-500">Total</p>
                            <p class="mt-1 font-semibold text-gray-900">
                                {{ format_price($order->total) }}
                            </p>
                            <p class="mt-1 text-xs text-gray-500">
                                {{ $order->items_count }}
                                {{ $order->items_count === 1 ? 'item' : 'items' }}
                            </p>
                        </div>

                        <a href="{{ route('storefront.orders.show', [
                                'orderNumber' => $order->order_number,
                            ]) }}"
                           class="inline-flex justify-center rounded-lg border
                                  border-gray-300 px-4 py-2.5 text-sm font-medium
                                  text-gray-700 hover:bg-gray-50">
                            View Details
                        </a>

                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $orders->links() }}
        </div>
    @endif

</div>
