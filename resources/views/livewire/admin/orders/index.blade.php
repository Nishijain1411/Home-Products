<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">

        <div>
            <h1 class="text-2xl font-semibold text-gray-900">
                Orders
            </h1>

            <p class="mt-1 text-sm text-gray-500">
                Manage customer orders and order statuses.
            </p>
        </div>

    </div>

    {{-- Filters --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-5">

        <div class="grid gap-4 md:grid-cols-3">

            {{-- Search --}}
            <div>
                <label class="text-sm font-medium text-gray-700">
                    Search
                </label>

                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Order number, customer or email"
                    class="mt-2 w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-blue-500"
                >
            </div>

            {{-- Order Status --}}
            <div>
                <label class="text-sm font-medium text-gray-700">
                    Order Status
                </label>

                <select
                    wire:model.live="status"
                    class="mt-2 w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm"
                >
                    <option value="">All statuses</option>
                    <option value="pending">Pending</option>
                    <option value="processing">Processing</option>
                    <option value="shipped">Shipped</option>
                    <option value="delivered">Delivered</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>

            {{-- Payment Status --}}
            <div>
                <label class="text-sm font-medium text-gray-700">
                    Payment Status
                </label>

                <select
                    wire:model.live="paymentStatus"
                    class="mt-2 w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm"
                >
                    <option value="">All payment statuses</option>
                    <option value="pending">Pending</option>
                    <option value="paid">Paid</option>
                    <option value="failed">Failed</option>
                    <option value="refunded">Refunded</option>
                </select>
            </div>

        </div>

    </div>

    {{-- Orders Table --}}
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white">

        <div class="overflow-x-auto">

            <table class="min-w-full divide-y divide-gray-200">

                <thead class="bg-gray-50">

                    <tr>

                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Order
                        </th>

                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Customer
                        </th>

                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Date
                        </th>

                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Items
                        </th>

                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Total
                        </th>

                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Payment
                        </th>

                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Status
                        </th>

                        <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Action
                        </th>

                    </tr>

                </thead>

                <tbody class="divide-y divide-gray-200">

                    @forelse ($orders as $order)

                        <tr class="hover:bg-gray-50">

                            <td class="whitespace-nowrap px-6 py-4">

                                <p class="text-sm font-semibold text-gray-900">
                                    {{ $order->order_number }}
                                </p>

                            </td>

                            <td class="px-6 py-4">

                                @if ($order->customer)

                                    <p class="text-sm font-medium text-gray-900">
                                        {{ $order->customer->name }}
                                    </p>

                                    <p class="text-xs text-gray-500">
                                        {{ $order->customer->email }}
                                    </p>

                                @else

                                    <span class="text-sm text-gray-500">
                                        Guest Customer
                                    </span>

                                @endif

                            </td>

                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">
                                {{ $order->created_at->format('d M Y, h:i A') }}
                            </td>

                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">
                                {{ $order->items_count }}
                            </td>

                            <td class="whitespace-nowrap px-6 py-4 text-sm font-semibold text-gray-900">
                                ₹{{ number_format($order->total, 2) }}
                            </td>

                            <td class="whitespace-nowrap px-6 py-4">

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

                            </td>

                            <td class="whitespace-nowrap px-6 py-4">

                                <span class="inline-flex rounded-full bg-blue-100 px-3 py-1 text-xs font-medium text-blue-700">
                                    {{ ucfirst($order->status) }}
                                </span>

                            </td>

                            <td class="whitespace-nowrap px-6 py-4 text-right">

                                <a
                                    href="{{ route('admin.orders.show', $order->order_number) }}"
                                    class="text-sm font-semibold text-blue-600 hover:text-blue-700"
                                >
                                    View
                                </a>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="8"
                                class="px-6 py-12 text-center text-sm text-gray-500"
                            >
                                No orders found.
                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        @if ($orders->hasPages())

            <div class="border-t border-gray-200 px-6 py-4">
                {{ $orders->links() }}
            </div>

        @endif

    </div>

</div>