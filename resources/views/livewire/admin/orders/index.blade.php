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

    {{-- Filters (Compact Single Row) --}}
    <div class="rounded-xl border border-gray-200 bg-white p-3 shadow-sm">
        <div class="flex flex-wrap items-center gap-2">

            {{-- Search --}}
            <div class="relative flex-1 min-w-[200px]">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-2.5 text-gray-400">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </span>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search order #, customer, email, address..."
                    class="h-9 w-full rounded-lg border border-gray-300 pl-8 pr-3 text-xs text-gray-800 placeholder-gray-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                >
            </div>

            {{-- Order Status --}}
            <div class="w-32 sm:w-36">
                <select
                    wire:model.live="status"
                    class="h-9 w-full rounded-lg border border-gray-300 px-2.5 text-xs text-gray-700 focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                >
                    <option value="">All Statuses</option>
                    <option value="pending">Pending</option>
                    <option value="processing">Processing</option>
                    <option value="shipped">Shipped</option>
                    <option value="delivered">Delivered</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>

            {{-- Payment Status --}}
            <div class="w-32 sm:w-36">
                <select
                    wire:model.live="paymentStatus"
                    class="h-9 w-full rounded-lg border border-gray-300 px-2.5 text-xs text-gray-700 focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                >
                    <option value="">All Payment</option>
                    <option value="pending">Pending</option>
                    <option value="paid">Paid</option>
                    <option value="failed">Failed</option>
                    <option value="refunded">Refunded</option>
                </select>
            </div>

            {{-- Date Range Preset --}}
            <div class="w-32 sm:w-36">
                <select
                    wire:model.live="datePreset"
                    class="h-9 w-full rounded-lg border border-gray-300 px-2.5 text-xs text-gray-700 focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                >
                    <option value="">All Dates</option>
                    <option value="today">Today</option>
                    <option value="yesterday">Yesterday</option>
                    <option value="last_7_days">Last 7 Days</option>
                    <option value="last_30_days">Last 30 Days</option>
                    <option value="this_month">This Month</option>
                    <option value="last_month">Last Month</option>
                </select>
            </div>

            {{-- Date From --}}
            <div class="w-32 sm:w-36">
                <input
                    type="date"
                    wire:model.live="dateFrom"
                    max="{{ $dateTo ?: '' }}"
                    title="From Date"
                    class="h-9 w-full rounded-lg border border-gray-300 px-2 text-xs text-gray-700 focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                >
            </div>

            {{-- Date To --}}
            <div class="w-32 sm:w-36">
                <input
                    type="date"
                    wire:model.live="dateTo"
                    min="{{ $dateFrom ?: '' }}"
                    title="To Date"
                    class="h-9 w-full rounded-lg border border-gray-300 px-2 text-xs text-gray-700 focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                >
            </div>

            {{-- Reset Button --}}
            @if ($search || $status || $paymentStatus || $dateFrom || $dateTo || $datePreset)
                <button
                    type="button"
                    wire:click="clearFilters"
                    title="Reset all filters"
                    class="h-9 inline-flex items-center gap-1 rounded-lg border border-red-200 bg-red-50 px-3 text-xs font-medium text-red-600 hover:bg-red-100 transition focus:outline-none focus:ring-2 focus:ring-red-500/20"
                >
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    <span>Reset</span>
                </button>
            @endif

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
                                {{ format_date($order->created_at, true) }}
                            </td>

                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">
                                {{ $order->items_count }}
                            </td>

                            <td class="whitespace-nowrap px-6 py-4 text-sm font-semibold text-gray-900">
                                {{ format_price($order->total) }}
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