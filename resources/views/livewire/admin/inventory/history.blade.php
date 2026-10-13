
<div class="space-y-6">

    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-gray-800 dark:text-white">
                Stock Adjustment History
            </h1>

            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Review stock changes, adjustment reasons, and the users
                who performed them.
            </p>
        </div>

        <a
            href="{{ route('admin.inventory.index') }}"
            class="inline-flex items-center rounded-lg border border-gray-300
                   bg-white px-4 py-2.5 text-sm font-medium text-gray-700
                   hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800
                   dark:text-gray-200"
        >
            Back to Inventory
        </a>
    </div>

    @php
        $reasonLabels = [
            'new_stock' => 'New Stock',
            'damaged_goods' => 'Damaged Goods',
            'stock_correction' => 'Stock Correction',
            'return' => 'Return',
            'other' => 'Other',
        ];
    @endphp

    <div class="rounded-xl border border-gray-200 bg-white p-4
                dark:border-gray-800 dark:bg-white/[0.03]">

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700
                              dark:text-gray-300">
                    Search
                </label>

                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Product, SKU, user, notes..."
                    class="h-11 w-full rounded-lg border border-gray-300
                           bg-transparent px-4 text-sm text-gray-800
                           dark:border-gray-700 dark:text-white"
                />
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700
                              dark:text-gray-300">
                    Adjustment Type
                </label>

                <select
                    wire:model.live="typeFilter"
                    class="h-11 w-full rounded-lg border border-gray-300
                           bg-transparent px-3 text-sm text-gray-800
                           dark:border-gray-700 dark:text-white"
                >
                    <option value="">All Types</option>
                    <option value="increase">Increase</option>
                    <option value="decrease">Decrease</option>
                </select>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700
                              dark:text-gray-300">
                    Reason
                </label>

                <select
                    wire:model.live="reasonFilter"
                    class="h-11 w-full rounded-lg border border-gray-300
                           bg-transparent px-3 text-sm text-gray-800
                           dark:border-gray-700 dark:text-white"
                >
                    <option value="">All Reasons</option>

                    @foreach ($reasonLabels as $value => $label)
                        <option value="{{ $value }}">
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700
                              dark:text-gray-300">
                    From Date
                </label>

                <input
                    type="date"
                    wire:model.live="dateFrom"
                    max="{{ $dateTo ?: '' }}"
                    class="h-11 w-full rounded-lg border border-gray-300
                           bg-transparent px-3 text-sm text-gray-800
                           dark:border-gray-700 dark:text-white"
                />
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700
                              dark:text-gray-300">
                    To Date
                </label>

                <input
                    type="date"
                    wire:model.live="dateTo"
                    min="{{ $dateFrom ?: '' }}"
                    class="h-11 w-full rounded-lg border border-gray-300
                           bg-transparent px-3 text-sm text-gray-800
                           dark:border-gray-700 dark:text-white"
                />
            </div>

        </div>

        <div class="mt-4 flex justify-end">
            <button
                type="button"
                wire:click="clearFilters"
                class="rounded-lg border border-gray-300 px-4 py-2
                       text-sm font-medium text-gray-700 hover:bg-gray-50
                       dark:border-gray-700 dark:text-gray-200
                       dark:hover:bg-gray-800"
            >
                Clear Filters
            </button>
        </div>
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-200
                bg-white dark:border-gray-800 dark:bg-white/[0.03]">

        <div class="max-w-full overflow-x-auto">
            <table class="min-w-[1150px] w-full text-left">

                <thead class="border-b border-gray-200 bg-gray-50
                              dark:border-gray-800 dark:bg-gray-900">
                    <tr>
                        <th class="px-5 py-4 text-xs font-semibold uppercase
                                   tracking-wider text-gray-500">
                            Date & Time
                        </th>

                        <th class="px-5 py-4 text-xs font-semibold uppercase
                                   tracking-wider text-gray-500">
                            Product / SKU
                        </th>

                        <th class="px-5 py-4 text-xs font-semibold uppercase
                                   tracking-wider text-gray-500">
                            Type
                        </th>

                        <th class="px-5 py-4 text-xs font-semibold uppercase
                                   tracking-wider text-gray-500">
                            Before
                        </th>

                        <th class="px-5 py-4 text-xs font-semibold uppercase
                                   tracking-wider text-gray-500">
                            Change
                        </th>

                        <th class="px-5 py-4 text-xs font-semibold uppercase
                                   tracking-wider text-gray-500">
                            After
                        </th>

                        <th class="px-5 py-4 text-xs font-semibold uppercase
                                   tracking-wider text-gray-500">
                            Reason / Notes
                        </th>

                        <th class="px-5 py-4 text-xs font-semibold uppercase
                                   tracking-wider text-gray-500">
                            Adjusted By
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">

                    @forelse ($adjustments as $adjustment)
                        @php
                            $variant = $adjustment->inventory?->variant;
                            $product = $variant?->product;
                        @endphp

                        <tr wire:key="adjustment-{{ $adjustment->id }}"
                            class="hover:bg-gray-50 dark:hover:bg-white/[0.02]">

                            <td class="px-5 py-4">
                                <div class="text-sm font-medium text-gray-800
                                            dark:text-white">
                                    {{ $adjustment->created_at?->format('d M Y') }}
                                </div>

                                <div class="mt-1 text-xs text-gray-500">
                                    {{ $adjustment->created_at?->format('h:i A') }}
                                </div>
                            </td>

                            <td class="px-5 py-4">
                                <div class="text-sm font-medium text-gray-800
                                            dark:text-white">
                                    {{ $product?->name ?? 'Product deleted' }}
                                </div>

                                <div class="mt-1 text-xs text-gray-500">
                                    SKU: {{ $variant?->sku ?? 'N/A' }}
                                </div>
                            </td>

                            <td class="px-5 py-4">
                                @if ($adjustment->type === 'increase')
                                    <span class="inline-flex rounded-full
                                                 bg-green-50 px-2.5 py-1
                                                 text-xs font-medium text-green-700
                                                 dark:bg-green-500/10
                                                 dark:text-green-400">
                                        Increase
                                    </span>
                                @else
                                    <span class="inline-flex rounded-full
                                                 bg-red-50 px-2.5 py-1
                                                 text-xs font-medium text-red-700
                                                 dark:bg-red-500/10
                                                 dark:text-red-400">
                                        Decrease
                                    </span>
                                @endif
                            </td>

                            <td class="px-5 py-4 text-sm text-gray-700
                                       dark:text-gray-300">
                                {{ number_format($adjustment->quantity_before) }}
                            </td>

                            <td class="px-5 py-4">
                                <span class="text-sm font-semibold
                                    {{ $adjustment->type === 'increase'
                                        ? 'text-green-600 dark:text-green-400'
                                        : 'text-red-600 dark:text-red-400' }}">
                                    {{ $adjustment->type === 'increase' ? '+' : '-' }}{{ number_format($adjustment->quantity_changed) }}
                                </span>
                            </td>

                            <td class="px-5 py-4 text-sm font-semibold
                                       text-gray-800 dark:text-white">
                                {{ number_format($adjustment->quantity_after) }}
                            </td>

                            <td class="max-w-xs px-5 py-4">
                                <div class="text-sm text-gray-800 dark:text-white">
                                    {{ $reasonLabels[$adjustment->reason] ?? $adjustment->reason }}
                                </div>

                                @if ($adjustment->notes)
                                    <div class="mt-1 whitespace-normal text-xs
                                                text-gray-500 dark:text-gray-400">
                                        {{ $adjustment->notes }}
                                    </div>
                                @endif
                            </td>

                            <td class="px-5 py-4 text-sm text-gray-700
                                       dark:text-gray-300">
                                {{ $adjustment->user?->name ?? 'System / Deleted User' }}
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-12 text-center">
                                <p class="text-sm font-medium text-gray-700
                                          dark:text-gray-200">
                                    No stock adjustments found.
                                </p>

                                <p class="mt-1 text-sm text-gray-500">
                                    Try changing your filters or adjust stock
                                    from the Inventory page.
                                </p>
                            </td>
                        </tr>
                    @endforelse

                </tbody>
            </table>
        </div>

        <div class="border-t border-gray-200 px-5 py-4 dark:border-gray-800">
            {{ $adjustments->links() }}
        </div>
    </div>

</div>
