<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">

        <div>
            <div class="flex items-center gap-3">

                <a
                    href="{{ route('admin.orders.index') }}"
                    class="text-sm text-gray-500 hover:text-gray-900"
                >
                    ← Orders
                </a>

                <span class="text-gray-300">/</span>

                <h1 class="text-2xl font-semibold text-gray-900">
                    {{ $order->order_number }}
                </h1>

            </div>

            <p class="mt-2 text-sm text-gray-500">
                Placed on {{ format_date($order->created_at, true) }}
            </p>
        </div>

        {{-- Status --}}
        <div class="flex items-center gap-3">

            <select
                wire:model="status"
                class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm"
            >
                <option value="pending">Pending</option>
                <option value="processing">Processing</option>
                <option value="shipped">Shipped</option>
                <option value="delivered">Delivered</option>
                <option value="cancelled">Cancelled</option>
            </select>

            <button
                type="button"
                wire:click="updateStatus"
                wire:loading.attr="disabled"
                class="rounded-lg bg-gray-900 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-600 disabled:opacity-50"
            >
                <span wire:loading.remove wire:target="updateStatus">
                    Update Status
                </span>

                <span wire:loading wire:target="updateStatus">
                    Updating...
                </span>
            </button>

        </div>

    </div>


    {{-- Success Message --}}
    @if (session()->has('success'))

        <div class="rounded-lg bg-green-50 px-4 py-3 text-sm font-medium text-green-700">
            {{ session('success') }}
        </div>

    @endif


    <div class="grid gap-6 lg:grid-cols-3">

        {{-- Main --}}
        <div class="space-y-6 lg:col-span-2">

            {{-- Order Items --}}
            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white">

                <div class="border-b border-gray-200 px-6 py-5">

                    <h2 class="text-lg font-semibold text-gray-900">
                        Order Items
                    </h2>

                </div>

                <div class="divide-y divide-gray-200">

                    @foreach ($order->items as $item)

                        <div class="flex items-center justify-between gap-6 px-6 py-5">

                            <div class="min-w-0 flex-1">

                                <p class="font-medium text-gray-900">
                                    {{ $item->product_name }}
                                </p>

                                @if ($item->variant_name && $item->variant_name !== 'Default Variant')
                                    <p class="mt-1 text-sm text-gray-500">
                                        Variant: {{ $item->variant_name }}
                                    </p>
                                @endif

                                @if ($item->sku)
                                    <p class="mt-1 text-xs text-gray-400">
                                        SKU: {{ $item->sku }}
                                    </p>
                                @endif

                            </div>

                            <div class="text-right">

                                <p class="text-sm text-gray-500">
                                    {{ $item->quantity }} ×
                                    {{ format_price($item->unit_price) }}
                                </p>

                                <p class="mt-1 font-semibold text-gray-900">
                                    {{ format_price($item->total) }}
                                </p>

                            </div>

                        </div>

                    @endforeach

                </div>

                {{-- Totals --}}
                <div class="border-t border-gray-200 bg-gray-50 px-6 py-5">

                    <div class="ml-auto max-w-sm space-y-3">

                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">
                                Subtotal
                            </span>

                            <span class="font-medium text-gray-900">
                                {{ format_price($order->subtotal) }}
                            </span>
                        </div>

                        <div class="flex justify-between text-sm">
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

                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">
                                Tax
                            </span>

                            <span class="font-medium text-gray-900">
                                {{ format_price($order->tax_amount) }}
                            </span>
                        </div>

                        <div class="flex justify-between border-t border-gray-200 pt-3">

                            <span class="font-semibold text-gray-900">
                                Total
                            </span>

                            <span class="text-xl font-bold text-gray-900">
                                {{ format_price($order->total) }}
                            </span>

                        </div>

                    </div>

                </div>

            </div>


            {{-- Addresses --}}
            <div class="grid gap-6 md:grid-cols-2">

                {{-- Shipping --}}
                <div class="rounded-2xl border border-gray-200 bg-white p-6">

                    <h2 class="text-lg font-semibold text-gray-900">
                        Shipping Address
                    </h2>

                    <div class="mt-5 space-y-1 text-sm text-gray-600">

                        <p class="font-medium text-gray-900">
                            {{ $order->shipping_address['first_name'] ?? '' }}
                            {{ $order->shipping_address['last_name'] ?? '' }}
                        </p>

                        <p>
                            {{ $order->shipping_address['address'] ?? '' }}
                        </p>

                        <p>
                            {{ $order->shipping_address['city'] ?? '' }},
                            {{ $order->shipping_address['state'] ?? '' }}
                        </p>

                        <p>
                            {{ $order->shipping_address['postal_code'] ?? '' }}
                        </p>

                        <p>
                            {{ $order->shipping_address['country'] ?? '' }}
                        </p>

                        @if (!empty($order->shipping_address['phone']))
                            <p class="pt-2">
                                Phone:
                                {{ $order->shipping_address['phone'] }}
                            </p>
                        @endif

                    </div>

                </div>


                {{-- Billing --}}
                <div class="rounded-2xl border border-gray-200 bg-white p-6">

                    <h2 class="text-lg font-semibold text-gray-900">
                        Billing Address
                    </h2>

                    <div class="mt-5 space-y-1 text-sm text-gray-600">

                        <p class="font-medium text-gray-900">
                            {{ $order->billing_address['first_name'] ?? '' }}
                            {{ $order->billing_address['last_name'] ?? '' }}
                        </p>

                        <p>
                            {{ $order->billing_address['address'] ?? '' }}
                        </p>

                        <p>
                            {{ $order->billing_address['city'] ?? '' }},
                            {{ $order->billing_address['state'] ?? '' }}
                        </p>

                        <p>
                            {{ $order->billing_address['postal_code'] ?? '' }}
                        </p>

                        <p>
                            {{ $order->billing_address['country'] ?? '' }}
                        </p>

                    </div>

                </div>

            </div>

        </div>


        {{-- Sidebar --}}
        <div class="space-y-6">

            {{-- Customer --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-6">

                <h2 class="text-lg font-semibold text-gray-900">
                    Customer
                </h2>

                @if ($order->customer)

                    <div class="mt-5 space-y-2">

                        <p class="font-medium text-gray-900">
                            {{ $order->customer->name }}
                        </p>

                        <p class="text-sm text-gray-500">
                            {{ $order->customer->email }}
                        </p>

                    </div>

                @else

                    <p class="mt-5 text-sm text-gray-500">
                        Guest Customer
                    </p>

                @endif

            </div>


            {{-- Payment --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-6">

                <h2 class="text-lg font-semibold text-gray-900">
                    Payment
                </h2>

                <div class="mt-5">

                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-medium
                        @if ($order->payment_status === 'paid')
                            bg-green-100 text-green-700
                        @elseif ($order->payment_status === 'failed')
                            bg-red-100 text-red-700
                        @elseif ($order->payment_status === 'refunded')
                            bg-purple-100 text-purple-700
                        @else
                            bg-yellow-100 text-yellow-700
                        @endif
                    ">
                        {{ ucfirst($order->payment_status) }}
                    </span>

                </div>

            </div>


            {{-- Order Status --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-6">

                <h2 class="text-lg font-semibold text-gray-900">
                    Order Status
                </h2>

                <p class="mt-4 text-sm text-gray-600">
                    Current status:
                </p>

                <p class="mt-1 font-semibold text-gray-900">
                    {{ ucfirst($order->status) }}
                </p>

            </div>

        </div>

    </div>

    {{-- Customer Order History with Date Filter --}}
    @if ($order->customer_id || !empty($order->shipping_address['email']))
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm space-y-4">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b border-gray-100 pb-4">
            <div>
                <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Customer's Other Orders
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">
                    Other orders from {{ $order->customer->name ?? ($order->shipping_address['first_name'] ?? 'this customer') }} with date filters.
                </p>
            </div>

            {{-- Date filters --}}
            <div class="flex items-center gap-2">
                <input
                    type="date"
                    wire:model.live="historyDateFrom"
                    max="{{ $historyDateTo ?: '' }}"
                    title="From Date"
                    class="h-8 rounded-lg border border-gray-300 px-2 text-xs text-gray-700 focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                >
                <span class="text-xs text-gray-400">to</span>
                <input
                    type="date"
                    wire:model.live="historyDateTo"
                    min="{{ $historyDateFrom ?: '' }}"
                    title="To Date"
                    class="h-8 rounded-lg border border-gray-300 px-2 text-xs text-gray-700 focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                >

                @if ($historyDateFrom || $historyDateTo)
                    <button
                        type="button"
                        wire:click="clearHistoryFilters"
                        class="text-xs font-medium text-red-600 hover:text-red-800 transition underline"
                    >
                        Reset
                    </button>
                @endif
            </div>
        </div>

        @if ($relatedOrders->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead>
                        <tr class="text-left text-xs uppercase tracking-wider text-gray-500">
                            <th class="py-2.5 pr-4">Order</th>
                            <th class="py-2.5 px-4">Date</th>
                            <th class="py-2.5 px-4">Items</th>
                            <th class="py-2.5 px-4">Total</th>
                            <th class="py-2.5 px-4">Payment</th>
                            <th class="py-2.5 px-4">Status</th>
                            <th class="py-2.5 pl-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($relatedOrders as $otherOrder)
                            <tr class="hover:bg-gray-50">
                                <td class="py-3 pr-4 font-semibold text-gray-900">
                                    {{ $otherOrder->order_number }}
                                </td>
                                <td class="py-3 px-4 text-gray-600">
                                    {{ format_date($otherOrder->created_at) }}
                                </td>
                                <td class="py-3 px-4 text-gray-600">
                                    {{ $otherOrder->items_count ?? $otherOrder->items->count() }} items
                                </td>
                                <td class="py-3 px-4 font-medium text-gray-900">
                                    {{ format_price($otherOrder->total) }}
                                </td>
                                <td class="py-3 px-4">
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium
                                        @if ($otherOrder->payment_status === 'paid') bg-green-100 text-green-700
                                        @elseif ($otherOrder->payment_status === 'failed') bg-red-100 text-red-700
                                        @elseif ($otherOrder->payment_status === 'refunded') bg-purple-100 text-purple-700
                                        @else bg-yellow-100 text-yellow-700 @endif">
                                        {{ ucfirst($otherOrder->payment_status) }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium
                                        @if ($otherOrder->status === 'delivered') bg-green-100 text-green-700
                                        @elseif ($otherOrder->status === 'cancelled') bg-red-100 text-red-700
                                        @elseif ($otherOrder->status === 'shipped') bg-blue-100 text-blue-700
                                        @elseif ($otherOrder->status === 'processing') bg-yellow-100 text-yellow-700
                                        @else bg-gray-100 text-gray-700 @endif">
                                        {{ ucfirst($otherOrder->status) }}
                                    </span>
                                </td>
                                <td class="py-3 pl-4 text-right">
                                    <a
                                        href="{{ route('admin.orders.show', $otherOrder->order_number) }}"
                                        class="font-medium text-blue-600 hover:text-blue-800"
                                    >
                                        View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="py-4 text-center text-xs text-gray-500">
                @if ($historyDateFrom || $historyDateTo)
                    No other orders found for this customer within the selected date range.
                @else
                    No other orders found for this customer.
                @endif
            </p>
        @endif
    </div>
    @endif

</div>