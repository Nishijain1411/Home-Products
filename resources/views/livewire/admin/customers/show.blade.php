<div class="space-y-6">

    {{-- Page Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">
                Customer Details
            </h1>

            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                View customer information and order history.
            </p>
        </div>

        <a
            href="{{ route('admin.customers.index') }}"
            class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]"
        >
            ← Back to Customers
        </a>
    </div>


    {{-- Customer Information --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">

        <div class="flex flex-col gap-6 sm:flex-row sm:items-center">

            {{-- Avatar --}}
            <div class="flex h-20 w-20 shrink-0 items-center justify-center rounded-full bg-blue-100 text-2xl font-semibold text-blue-600 dark:bg-blue-500/10 dark:text-blue-400">
                {{ strtoupper(substr($customer->name ?? 'C', 0, 1)) }}
            </div>

            {{-- Basic Information --}}
            <div class="flex-1">
                <div class="flex flex-wrap items-center gap-3">
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">
                        {{ $customer->name }}
                    </h2>

                    @if ($customer->is_active)
                        <span class="rounded-full bg-green-50 px-3 py-1 text-xs font-medium text-green-600 dark:bg-green-500/10 dark:text-green-400">
                            Active
                        </span>
                    @else
                        <span class="rounded-full bg-red-50 px-3 py-1 text-xs font-medium text-red-600 dark:bg-red-500/10 dark:text-red-400">
                            Inactive
                        </span>
                    @endif
                </div>

                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ $customer->email }}
                </p>

                <p class="mt-2 text-xs text-gray-400">
                    Customer since {{ $customer->created_at?->format('d M Y') }}
                </p>
            </div>

        </div>
    </div>


    {{-- Statistics --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">

        {{-- Total Orders --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Total Orders
            </p>

            <p class="mt-2 text-2xl font-semibold text-gray-800 dark:text-white/90">
                {{ $customer->orders_count }}
            </p>
        </div>


        {{-- Total Spent --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Total Spent
            </p>

            <p class="mt-2 text-2xl font-semibold text-gray-800 dark:text-white/90">
                ₹{{ number_format((float) $customer->orders_sum_total, 2) }}
            </p>
        </div>


        {{-- Average Order Value --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Average Order Value
            </p>

            <p class="mt-2 text-2xl font-semibold text-gray-800 dark:text-white/90">
                ₹{{ number_format(
                    $customer->orders_count > 0
                        ? ((float) $customer->orders_sum_total / $customer->orders_count)
                        : 0,
                    2
                ) }}
            </p>
        </div>

    </div>


    {{-- Order History --}}
    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b border-gray-200 px-6 py-5 dark:border-gray-800">
            <div>
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                    Order History
                </h3>

                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Orders placed by this customer.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <input
                    type="date"
                    wire:model.live="dateFrom"
                    max="{{ $dateTo ?: '' }}"
                    title="From Date"
                    class="h-8 rounded-lg border border-gray-300 bg-transparent px-2 text-xs text-gray-800 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:text-white"
                >
                <span class="text-xs text-gray-400">to</span>
                <input
                    type="date"
                    wire:model.live="dateTo"
                    min="{{ $dateFrom ?: '' }}"
                    title="To Date"
                    class="h-8 rounded-lg border border-gray-300 bg-transparent px-2 text-xs text-gray-800 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:text-white"
                >

                @if ($dateFrom || $dateTo)
                    <button
                        type="button"
                        wire:click="clearFilters"
                        class="text-xs font-medium text-red-600 hover:text-red-800 transition underline"
                    >
                        Reset
                    </button>
                @endif
            </div>
        </div>


        @if ($orders->count())

            <div class="overflow-x-auto">
                <table class="w-full min-w-[800px]">

                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-800">

                            <th class="px-6 py-4 text-left text-xs font-medium uppercase text-gray-500">
                                Order
                            </th>

                            <th class="px-6 py-4 text-left text-xs font-medium uppercase text-gray-500">
                                Date
                            </th>

                            <th class="px-6 py-4 text-left text-xs font-medium uppercase text-gray-500">
                                Items
                            </th>

                            <th class="px-6 py-4 text-left text-xs font-medium uppercase text-gray-500">
                                Total
                            </th>

                            <th class="px-6 py-4 text-left text-xs font-medium uppercase text-gray-500">
                                Payment
                            </th>

                            <th class="px-6 py-4 text-left text-xs font-medium uppercase text-gray-500">
                                Status
                            </th>

                            <th class="px-6 py-4 text-right text-xs font-medium uppercase text-gray-500">
                                Action
                            </th>

                        </tr>
                    </thead>


                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">

                        @foreach ($orders as $order)

                            <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02]">

                                <td class="px-6 py-4">
                                    <span class="font-medium text-gray-800 dark:text-white/90">
                                        {{ $order->order_number }}
                                    </span>
                                </td>


                                <td class="px-6 py-4 text-sm text-gray-500">
                                    {{ $order->created_at?->format('d M Y') }}
                                </td>


                                <td class="px-6 py-4 text-sm text-gray-500">
                                    {{ $order->items()->count() }}
                                </td>


                                <td class="px-6 py-4 text-sm font-medium text-gray-800 dark:text-white/90">
                                    ₹{{ number_format((float) $order->total, 2) }}
                                </td>


                                <td class="px-6 py-4">

                                    @if ($order->payment_status === 'paid')

                                        <span class="rounded-full bg-green-50 px-3 py-1 text-xs font-medium text-green-600 dark:bg-green-500/10 dark:text-green-400">
                                            Paid
                                        </span>

                                    @else

                                        <span class="rounded-full bg-yellow-50 px-3 py-1 text-xs font-medium text-yellow-600 dark:bg-yellow-500/10 dark:text-yellow-400">
                                            {{ ucfirst($order->payment_status) }}
                                        </span>

                                    @endif

                                </td>


                                <td class="px-6 py-4">

                                    <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-medium text-blue-600 dark:bg-blue-500/10 dark:text-blue-400">
                                        {{ ucfirst($order->status) }}
                                    </span>

                                </td>


                                <td class="px-6 py-4 text-right">

                                    <a
                                        href="{{ route('admin.orders.show', $order->order_number) }}"
                                        class="text-sm font-medium text-blue-600 hover:text-blue-700"
                                    >
                                        View
                                    </a>

                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>
            </div>


            {{-- Pagination --}}
            <div class="border-t border-gray-200 px-6 py-4 dark:border-gray-800">
                {{ $orders->links() }}
            </div>

        @else

            <div class="px-6 py-12 text-center">

                <h4 class="text-base font-medium text-gray-800 dark:text-white/90">
                    No orders found
                </h4>

                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    This customer has not placed any orders yet.
                </p>

            </div>

        @endif

    </div>

</div>