<div class="space-y-6">

    {{-- Top Header & Quick Actions --}}
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between border-b border-gray-200 dark:border-gray-800 pb-5">
        <div>
            <div class="flex items-center gap-2 text-xs font-medium text-gray-500 dark:text-gray-400">
                <span>Main</span>
                <span>/</span>
                <span class="text-gray-800 dark:text-gray-200 font-semibold">Dashboard</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white mt-1">
                Dashboard — Daily operations
            </h1>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                Daily operations overview: today's sales, pending payments, active orders, and inventory alerts.
            </p>
        </div>

        {{-- Quick Actions --}}
        <div class="flex flex-wrap items-center gap-2.5">
            {{-- Quick Action: Add product --}}
            <a
                href="{{ route('admin.products.create') }}"
                class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white px-3.5 py-2 text-xs font-semibold shadow-xs transition"
                title="Add a new product to your catalog"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add product
            </a>

            {{-- Quick Action: View orders --}}
            <a
                href="{{ route('admin.orders.index') }}"
                class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 px-3.5 py-2 text-xs font-semibold shadow-xs transition"
                title="Browse and manage all customer orders"
            >
                <svg class="h-4 w-4 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
                View orders
            </a>

            {{-- Quick Action: Manage inventory --}}
            <a
                href="{{ route('admin.inventory.index') }}"
                class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 px-3.5 py-2 text-xs font-semibold shadow-xs transition"
                title="Manage product stock and inventory levels"
            >
                <svg class="h-4 w-4 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
                Manage inventory
            </a>

            {{-- Link to Analytics --}}
            <a
                href="{{ route('admin.analytics.index') }}"
                class="inline-flex items-center gap-1.5 rounded-lg border border-indigo-200 dark:border-indigo-800 bg-indigo-50 dark:bg-indigo-950/40 hover:bg-indigo-100 dark:hover:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 px-3.5 py-2 text-xs font-semibold shadow-xs transition"
                title="View long-term business analytics & reports"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                Analytics
            </a>
        </div>
    </div>

    {{-- Overview Metrics Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 md:gap-5">

        {{-- 1. Total orders and today's orders --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-800/50 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Orders</span>
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-500/15 dark:text-blue-400">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                    </div>
                </div>

                <div class="mt-3">
                    <div class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                        {{ number_format($totalOrdersCount) }}
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700/60 flex items-center justify-between text-xs">
                <span class="inline-flex items-center gap-1 font-semibold text-emerald-600 dark:text-emerald-400">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                    </svg>
                    {{ number_format($todayOrdersCount) }} today
                </span>
                <a href="{{ route('admin.orders.index') }}" class="text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition">
                    View all &rarr;
                </a>
            </div>
        </div>

        {{-- 2. Today's sales --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-800/50 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Today's Sales</span>
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-400">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>

                <div class="mt-3">
                    <div class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                        {{ format_price($todaySales) }}
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700/60 flex items-center justify-between text-xs">
                <span class="text-gray-500 dark:text-gray-400">
                    Net settled revenue today
                </span>
                <span class="text-emerald-600 font-semibold">{{ $todayOrdersCount }} order(s)</span>
            </div>
        </div>

        {{-- 3. Pending payments --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-800/50 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Pending Payments</span>
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-50 text-amber-600 dark:bg-amber-500/15 dark:text-amber-400">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>

                <div class="mt-3">
                    <div class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                        {{ format_price($pendingPaymentsTotal) }}
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700/60 flex items-center justify-between text-xs">
                <span class="inline-flex items-center gap-1 font-semibold text-amber-600 dark:text-amber-400">
                    {{ $pendingPaymentsCount }} order(s) unpaid
                </span>
                <a href="{{ route('admin.orders.index', ['paymentStatus' => 'pending']) }}" class="text-gray-400 hover:text-amber-600 transition">
                    Review &rarr;
                </a>
            </div>
        </div>

        {{-- 4. Pending orders requiring action --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-800/50 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Action Required</span>
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-rose-50 text-rose-600 dark:bg-rose-500/15 dark:text-rose-400">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                </div>

                <div class="mt-3">
                    <div class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                        {{ number_format($pendingOrdersCount) }}
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700/60 flex items-center justify-between text-xs">
                <span class="inline-flex items-center gap-1 font-semibold text-rose-600 dark:text-rose-400">
                    Pending orders
                </span>
                <a href="{{ route('admin.orders.index', ['status' => 'pending']) }}" class="text-gray-400 hover:text-rose-600 transition">
                    Process &rarr;
                </a>
            </div>
        </div>

        {{-- 5. Low-stock product alerts --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-800/50 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Low-Stock Alerts</span>
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-orange-50 text-orange-600 dark:bg-orange-500/15 dark:text-orange-400">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                </div>

                <div class="mt-3">
                    <div class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                        {{ number_format($totalLowStockAlerts) }}
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700/60 flex items-center justify-between text-xs">
                <span class="inline-flex items-center gap-1 font-semibold {{ $outOfStockCount > 0 ? 'text-red-600' : 'text-orange-600' }}">
                    {{ $outOfStockCount }} out of stock
                </span>
                <a href="{{ route('admin.inventory.index') }}" class="text-gray-400 hover:text-orange-600 transition">
                    Restock &rarr;
                </a>
            </div>
        </div>

    </div>

    {{-- Main Operations Area: Recent Orders Table & Low-Stock Alerts --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">

        {{-- Recent Orders Table (8 cols on lg) --}}
        <div class="lg:col-span-8 rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-800/50">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-gray-100 dark:border-gray-700/60 pb-4">
                <div>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                        Recent Orders
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        Latest incoming customer orders and fulfillment progress.
                    </p>
                </div>

                {{-- Status Filter Tabs --}}
                <div class="inline-flex rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-0.5 text-xs">
                    @foreach (['all' => 'All', 'pending' => 'Pending', 'processing' => 'Processing', 'delivered' => 'Delivered'] as $tabKey => $tabLabel)
                        <button
                            type="button"
                            wire:click="setStatusFilter('{{ $tabKey }}')"
                            class="rounded-md px-2.5 py-1 font-medium transition {{ $statusFilter === $tabKey ? 'bg-white dark:bg-gray-700 text-blue-600 dark:text-blue-400 shadow-xs font-semibold' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}"
                        >
                            {{ $tabLabel }}
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Table --}}
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-left text-xs">
                    <thead class="text-gray-400 uppercase tracking-wider font-semibold">
                        <tr>
                            <th class="py-3 px-2">Order #</th>
                            <th class="py-3 px-2">Customer</th>
                            <th class="py-3 px-2">Date</th>
                            <th class="py-3 px-2 text-right">Total</th>
                            <th class="py-3 px-2 text-center">Payment</th>
                            <th class="py-3 px-2 text-center">Fulfillment</th>
                            <th class="py-3 px-2 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($recentOrders as $order)
                            <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-700/20 transition">
                                {{-- Order Number --}}
                                <td class="py-3 px-2 whitespace-nowrap">
                                    <a
                                        href="{{ route('admin.orders.show', $order->order_number) }}"
                                        class="font-semibold text-blue-600 hover:text-blue-800 dark:text-blue-400 hover:underline"
                                    >
                                        {{ $order->order_number }}
                                    </a>
                                </td>

                                {{-- Customer --}}
                                <td class="py-3 px-2">
                                    @php
                                        $customerName = $order->customer?->name ?? (
                                            trim(($order->shipping_address['first_name'] ?? '') . ' ' . ($order->shipping_address['last_name'] ?? ''))
                                            ?: trim(($order->billing_address['first_name'] ?? '') . ' ' . ($order->billing_address['last_name'] ?? ''))
                                            ?: 'Guest Customer'
                                        );
                                        $customerEmail = $order->customer?->email ?? ($order->customer_email ?? ($order->shipping_address['email'] ?? null));
                                    @endphp
                                    <div class="min-w-0">
                                        <div class="font-medium text-gray-900 dark:text-white truncate max-w-[150px]">
                                            {{ $customerName }}
                                        </div>
                                        @if ($customerEmail)
                                            <div class="text-[11px] text-gray-400 truncate max-w-[150px]">
                                                {{ $customerEmail }}
                                            </div>
                                        @endif
                                    </div>
                                </td>

                                {{-- Date --}}
                                <td class="py-3 px-2 whitespace-nowrap text-gray-500 dark:text-gray-400">
                                    {{ $order->created_at->format('M d, H:i') }}
                                </td>

                                {{-- Total --}}
                                <td class="py-3 px-2 text-right font-bold text-gray-900 dark:text-white whitespace-nowrap">
                                    {{ format_price($order->total) }}
                                </td>

                                {{-- Payment Status --}}
                                <td class="py-3 px-2 text-center whitespace-nowrap">
                                    @php
                                        $pStatus = strtolower((string) $order->payment_status);
                                        $pClass = match ($pStatus) {
                                            'paid' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-400',
                                            'pending' => 'bg-amber-50 text-amber-700 dark:bg-amber-500/15 dark:text-amber-400',
                                            'failed' => 'bg-red-50 text-red-700 dark:bg-red-500/15 dark:text-red-400',
                                            'refunded' => 'bg-purple-50 text-purple-700 dark:bg-purple-500/15 dark:text-purple-400',
                                            default => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300',
                                        };
                                    @endphp
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $pClass }}">
                                        {{ ucfirst($order->payment_status ?: 'pending') }}
                                    </span>
                                </td>

                                {{-- Order Status --}}
                                <td class="py-3 px-2 text-center whitespace-nowrap">
                                    @php
                                        $oStatus = strtolower((string) $order->status);
                                        $oClass = match ($oStatus) {
                                            'delivered' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-400',
                                            'processing' => 'bg-blue-50 text-blue-700 dark:bg-blue-500/15 dark:text-blue-400',
                                            'shipped' => 'bg-sky-50 text-sky-700 dark:bg-sky-500/15 dark:text-sky-400',
                                            'pending' => 'bg-amber-50 text-amber-700 dark:bg-amber-500/15 dark:text-amber-400',
                                            'cancelled' => 'bg-red-50 text-red-700 dark:bg-red-500/15 dark:text-red-400',
                                            default => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300',
                                        };
                                    @endphp
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $oClass }}">
                                        {{ ucfirst($order->status ?: 'pending') }}
                                    </span>
                                </td>

                                {{-- Action --}}
                                <td class="py-3 px-2 text-right whitespace-nowrap">
                                    <a
                                        href="{{ route('admin.orders.show', $order->order_number) }}"
                                        class="text-xs font-semibold text-blue-600 hover:text-blue-800 dark:text-blue-400"
                                    >
                                        View &rarr;
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-10 text-center text-xs text-gray-400">
                                    No orders found matching the selected filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Footer Link --}}
            <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700/60 flex items-center justify-between text-xs">
                <span class="text-gray-400">
                    Showing latest {{ $recentOrders->count() }} of {{ number_format($totalOrdersCount) }} total orders
                </span>
                <a href="{{ route('admin.orders.index') }}" class="font-semibold text-blue-600 hover:text-blue-800 dark:text-blue-400">
                    View all orders &rarr;
                </a>
            </div>
        </div>

        {{-- Low-Stock Product Alerts Panel (4 cols on lg) --}}
        <div class="lg:col-span-4 rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-800/50 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700/60 pb-3">
                    <div>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                            Low-Stock Alerts
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                            Items requiring replenishment (≤ 10 units).
                        </p>
                    </div>
                    <a href="{{ route('admin.inventory.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400">
                        Manage &rarr;
                    </a>
                </div>

                <div class="mt-4 space-y-2.5">
                    @forelse ($lowStockItems as $inv)
                        <div class="flex items-center justify-between p-2.5 rounded-xl border {{ $inv['is_out_of_stock'] ? 'border-red-200 bg-red-50/50 dark:border-red-900 dark:bg-red-900/10' : 'border-amber-200 bg-amber-50/50 dark:border-amber-900 dark:bg-amber-900/10' }}">
                            <div class="min-w-0 pr-2">
                                <div class="font-semibold text-xs text-gray-900 dark:text-white truncate">
                                    {{ $inv['product_name'] }}
                                </div>
                                <div class="text-[10px] text-gray-500 dark:text-gray-400 font-mono">
                                    {{ $inv['sku'] }} &bull; {{ $inv['variant_title'] }}
                                </div>
                            </div>

                            <div class="shrink-0">
                                @if ($inv['is_out_of_stock'])
                                    <span class="inline-flex items-center rounded-full bg-red-100 text-red-800 dark:bg-red-800/30 dark:text-red-300 px-2 py-0.5 text-[10px] font-bold">
                                        0 in stock
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-amber-100 text-amber-800 dark:bg-amber-800/30 dark:text-amber-300 px-2 py-0.5 text-[10px] font-bold">
                                        {{ $inv['quantity'] }} left
                                    </span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="py-8 text-center text-xs text-gray-500 dark:text-gray-400">
                            <div class="mx-auto flex h-9 w-9 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-800 text-emerald-600 dark:text-emerald-200 mb-2">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            All inventory stock levels are healthy (> 10 units).
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700/60">
                <a
                    href="{{ route('admin.inventory.index') }}"
                    class="block w-full text-center rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 py-2 text-xs font-semibold text-gray-700 dark:text-gray-300 shadow-xs transition"
                >
                    Open Inventory Manager
                </a>
            </div>
        </div>

    </div>

</div>