<div class="p-4 md:p-6 max-w-7xl mx-auto space-y-6">

    {{-- Flash Notifications --}}
    @if (session()->has('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800 flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-2">
                <svg class="h-5 w-5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
        </div>
    @endif

    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b border-gray-200 pb-5">
        <div>
            <div class="flex items-center gap-2 text-xs font-medium text-gray-500">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-blue-600 transition">Dashboard</a>
                <span>/</span>
                <span class="text-gray-800">Sales</span>
                <span>/</span>
                <span class="text-gray-800">Discounts</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 mt-1">
                Discount Codes & Promotions
            </h1>
            <p class="text-sm text-gray-500 mt-0.5">
                Manage promo coupons, percentage or fixed discounts, usage caps, and validity rules.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.discounts.create') }}"
                class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-blue-700 transition">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Create Discount
            </a>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <div class="text-xs font-medium uppercase tracking-wider text-gray-500">Total Discounts</div>
            <div class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($totalDiscounts) }}</div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <div class="text-xs font-medium uppercase tracking-wider text-gray-500">Active Coupons</div>
            <div class="mt-2 text-2xl font-bold text-emerald-600">{{ number_format($activeDiscounts) }}</div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <div class="text-xs font-medium uppercase tracking-wider text-gray-500">Total Redemptions</div>
            <div class="mt-2 text-2xl font-bold text-blue-600">{{ number_format($totalRedemptions) }}</div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <div class="text-xs font-medium uppercase tracking-wider text-gray-500">Total Saved by Customers</div>
            <div class="mt-2 text-2xl font-bold text-purple-600">{{ format_price($totalDiscountGiven) }}</div>
        </div>
    </div>

    {{-- Filter & Search Toolbar --}}
    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div class="relative sm:col-span-1">
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search by code or name..."
                    class="block w-full rounded-lg border-gray-300 pl-9 text-xs shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2"
                >
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>

            <div>
                <select
                    wire:model.live="typeFilter"
                    class="block w-full rounded-lg border-gray-300 text-xs shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2"
                >
                    <option value="all">All Discount Types</option>
                    <option value="percentage">Percentage (%)</option>
                    <option value="fixed">Fixed Amount</option>
                </select>
            </div>

            <div>
                <select
                    wire:model.live="statusFilter"
                    class="block w-full rounded-lg border-gray-300 text-xs shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2"
                >
                    <option value="all">All Statuses</option>
                    <option value="active">Active & Valid</option>
                    <option value="inactive">Inactive</option>
                    <option value="expired">Expired</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Discounts Table --}}
    <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-left text-sm">
                <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wider text-gray-500">
                    <tr>
                        <th class="px-6 py-3.5">Code & Name</th>
                        <th class="px-6 py-3.5">Discount Value</th>
                        <th class="px-6 py-3.5">Rules & Thresholds</th>
                        <th class="px-6 py-3.5">Usage / Limit</th>
                        <th class="px-6 py-3.5">Validity</th>
                        <th class="px-6 py-3.5">Status</th>
                        <th class="px-6 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($discounts as $discount)
                        <tr wire:key="discount-{{ $discount->id }}" class="hover:bg-gray-50/70 transition">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center rounded-md bg-gray-100 px-2 py-1 font-mono text-xs font-bold uppercase tracking-wider text-gray-800 border border-gray-200">
                                        {{ $discount->code }}
                                    </span>
                                </div>
                                <div class="font-medium text-gray-900 mt-1">{{ $discount->name }}</div>
                                @if ($discount->description)
                                    <div class="text-xs text-gray-500 line-clamp-1 mt-0.5">{{ $discount->description }}</div>
                                @endif
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                @if ($discount->type === 'percentage')
                                    <div class="font-semibold text-emerald-600 text-base">
                                        {{ rtrim(rtrim(number_format((float) $discount->value, 2), '0'), '.') }}% OFF
                                    </div>
                                    <span class="inline-flex items-center rounded bg-emerald-50 px-1.5 py-0.5 text-[10px] font-medium text-emerald-700">
                                        Percentage
                                    </span>
                                @else
                                    <div class="font-semibold text-blue-600 text-base">
                                        {{ format_price($discount->value) }} OFF
                                    </div>
                                    <span class="inline-flex items-center rounded bg-blue-50 px-1.5 py-0.5 text-[10px] font-medium text-blue-700">
                                        Fixed Amount
                                    </span>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-xs text-gray-600">
                                @if ($discount->min_order_amount)
                                    <div>Min Order: <strong class="text-gray-800">{{ format_price($discount->min_order_amount) }}</strong></div>
                                @else
                                    <div class="text-gray-400">No minimum order</div>
                                @endif

                                @if ($discount->max_discount_amount)
                                    <div class="mt-0.5">Cap: <strong class="text-gray-800">{{ format_price($discount->max_discount_amount) }}</strong></div>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-xs whitespace-nowrap">
                                <div>
                                    <span class="font-bold text-gray-900">{{ $discount->times_used }}</span>
                                    <span class="text-gray-500">/ {{ $discount->usage_limit ?? '∞' }} total</span>
                                </div>
                                @if ($discount->usage_limit_per_customer)
                                    <div class="text-gray-500 mt-0.5">
                                        Max {{ $discount->usage_limit_per_customer }} per customer
                                    </div>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-xs whitespace-nowrap">
                                @if ($discount->starts_at && ! $discount->hasStarted())
                                    <span class="text-amber-700">Starts: {{ format_date($discount->starts_at, true) }}</span>
                                @elseif ($discount->expires_at)
                                    @if ($discount->isExpired())
                                        <span class="text-red-600 font-medium">Expired: {{ format_date($discount->expires_at, true) }}</span>
                                    @else
                                        <span class="text-gray-700">Expires: {{ format_date($discount->expires_at, true) }}</span>
                                    @endif
                                @else
                                    <span class="text-emerald-700 font-medium">Never expires</span>
                                @endif
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                @if (! $discount->is_active)
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-700">
                                        Inactive
                                    </span>
                                @elseif ($discount->isExpired())
                                    <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800">
                                        Expired
                                    </span>
                                @elseif ($discount->usage_limit && $discount->times_used >= $discount->usage_limit)
                                    <span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-800">
                                        Limit Reached
                                    </span>
                                @elseif ($discount->starts_at && $discount->starts_at->isFuture())
                                    <span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800">
                                        Scheduled
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-800">
                                        Active
                                    </span>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-right whitespace-nowrap text-xs font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    <button
                                        type="button"
                                        wire:click="toggleStatus({{ $discount->id }})"
                                        class="rounded-lg p-1.5 text-gray-500 hover:bg-gray-100 hover:text-gray-900 transition"
                                        title="{{ $discount->is_active ? 'Deactivate' : 'Activate' }}"
                                    >
                                        @if ($discount->is_active)
                                            <svg class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        @else
                                            <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        @endif
                                    </button>

                                    <a
                                        href="{{ route('admin.discounts.edit', $discount->id) }}"
                                        class="rounded-lg p-1.5 text-blue-600 hover:bg-blue-50 transition"
                                        title="Edit discount"
                                    >
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>

                                    <button
                                        type="button"
                                        wire:click="confirmDelete({{ $discount->id }})"
                                        class="rounded-lg p-1.5 text-red-600 hover:bg-red-50 transition"
                                        title="Delete discount"
                                    >
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 text-gray-400">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                    </svg>
                                </div>
                                <h3 class="mt-3 text-sm font-semibold text-gray-900">No discount codes found</h3>
                                <p class="mt-1 text-xs text-gray-500">
                                    @if ($search || $typeFilter !== 'all' || $statusFilter !== 'all')
                                        Try adjusting your search query or filters.
                                    @else
                                        Get started by creating your first promotional coupon.
                                    @endif
                                </p>
                                <div class="mt-4">
                                    <a href="{{ route('admin.discounts.create') }}"
                                        class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-3.5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-blue-700 transition">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                        </svg>
                                        Create Discount
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($discounts->hasPages())
            <div class="border-t border-gray-200 px-6 py-4">
                {{ $discounts->links() }}
            </div>
        @endif
    </div>

    {{-- Delete Confirmation Modal --}}
    @if ($showDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 p-4">
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-100 text-red-600">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">Delete Discount</h3>
                        <p class="mt-1 text-xs text-gray-500">
                            Are you sure you want to delete this discount? Customers will no longer be able to apply it.
                        </p>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button
                        type="button"
                        wire:click="$set('showDeleteModal', false)"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 transition"
                    >
                        Cancel
                    </button>

                    <button
                        type="button"
                        wire:click="delete"
                        class="rounded-lg bg-red-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-red-700 transition"
                    >
                        Yes, Delete
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
