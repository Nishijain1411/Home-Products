<div class="space-y-6">

    {{-- Flash Notifications --}}
    @if ($successMessage)
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800 flex items-center justify-between shadow-sm dark:border-emerald-900/50 dark:bg-emerald-900/20 dark:text-emerald-300">
            <div class="flex items-center gap-2">
                <svg class="h-5 w-5 text-emerald-600 shrink-0 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="font-medium">{{ $successMessage }}</span>
            </div>
            <button type="button" wire:click="dismissAlert" class="text-emerald-600 hover:text-emerald-800 dark:text-emerald-400 dark:hover:text-emerald-200">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    @endif

    @if ($errorMessage)
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800 flex items-center justify-between shadow-sm dark:border-red-900/50 dark:bg-red-900/20 dark:text-red-300">
            <div class="flex items-center gap-2">
                <svg class="h-5 w-5 text-red-600 shrink-0 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="font-medium">{{ $errorMessage }}</span>
            </div>
            <button type="button" wire:click="dismissAlert" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-200">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    @endif

    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                Customers
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Manage registered customers, view order histories, and assign customer segments.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.customer-segments.index') }}"
                class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 transition">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                Customer Segments &rarr;
            </a>
        </div>
    </div>

    {{-- Search & Filters Card --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800 space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-center">
            {{-- Search Input (2 cols) --}}
            <div class="sm:col-span-2 relative">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search by customer name or email..."
                    class="w-full rounded-lg border border-gray-300 bg-white pl-9 pr-4 py-2 text-sm text-gray-900 placeholder:text-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                >
            </div>

            {{-- Segment Filter --}}
            <div>
                <select wire:model.live="segmentFilter"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    <option value="">All Segments</option>
                    <option value="unsegmented">Unsegmented Customers</option>
                    @foreach ($segments as $seg)
                        <option value="{{ $seg->id }}">{{ $seg->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Active Filters indicator and Reset --}}
        @if ($this->hasActiveFilters)
            <div class="pt-2 border-t border-gray-100 flex items-center justify-between text-xs dark:border-gray-700">
                <div class="flex items-center gap-1.5 text-gray-500 dark:text-gray-400">
                    <span class="font-medium text-gray-700 dark:text-gray-300">Active filters:</span>
                    @if ($search)
                        <span class="inline-flex items-center rounded bg-blue-50 px-2 py-0.5 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">
                            Search: "{{ $search }}"
                        </span>
                    @endif
                    @if ($segmentFilter !== '')
                        <span class="inline-flex items-center rounded bg-indigo-50 px-2 py-0.5 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300">
                            Segment: {{ $segmentFilter === 'unsegmented' ? 'Unsegmented' : ($segments->firstWhere('id', $segmentFilter)?->name ?? 'Selected') }}
                        </span>
                    @endif
                </div>

                <button type="button" wire:click="resetFilters"
                    class="font-semibold text-red-600 hover:text-red-800 transition dark:text-red-400">
                    Reset Filters
                </button>
            </div>
        @endif
    </div>

    {{-- Bulk Action Floating Bar --}}
    @if (!empty($selected))
        <div class="flex items-center justify-between rounded-xl border border-indigo-200 bg-indigo-50/90 p-3 shadow-md dark:border-indigo-900/50 dark:bg-indigo-950/50">
            <div class="flex items-center gap-3">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-600 text-white font-bold text-xs shadow-sm">
                    {{ count($selected) }}
                </div>
                <div class="text-xs font-semibold text-indigo-950 dark:text-indigo-200">
                    {{ count($selected) }} {{ Str::plural('customer', count($selected)) }} selected
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button type="button" wire:click="clearSelection"
                    class="rounded-lg border border-indigo-300 bg-white px-3 py-1.5 text-xs font-medium text-indigo-700 hover:bg-indigo-50 transition dark:border-indigo-800 dark:bg-gray-800 dark:text-indigo-300">
                    Clear Selection
                </button>

                <button type="button" wire:click="openBulkSegmentModal"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-3.5 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-indigo-700 transition">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    Assign / Move to Segment...
                </button>
            </div>
        </div>
    @endif

    {{-- Customer Table --}}
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-750">
                    <tr>
                        <th class="w-12 px-4 py-3.5 text-left">
                            <input type="checkbox" wire:model.live="selectAll"
                                class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700"
                                title="Select all on this page">
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Customer
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Email
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Segments
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Joined
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Orders
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Total Spent
                        </th>
                        <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Action
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($customers as $customer)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-750 transition" wire:key="customer-row-{{ $customer->id }}">
                            <td class="w-12 px-4 py-4">
                                <input type="checkbox" wire:model.live="selected" value="{{ $customer->id }}"
                                    class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700">
                            </td>

                            <td class="px-6 py-4">
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ $customer->name }}
                                </p>
                            </td>

                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                                {{ $customer->email }}
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1">
                                    @forelse ($customer->segments as $seg)
                                        <span class="inline-flex items-center rounded px-2 py-0.5 text-[11px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100 dark:bg-indigo-900/40 dark:text-indigo-300 dark:border-indigo-800">
                                            {{ $seg->name }}
                                        </span>
                                    @empty
                                        <span class="text-xs text-gray-400 italic">None</span>
                                    @endforelse
                                </div>
                            </td>

                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                {{ $customer->created_at->format('d M Y') }}
                            </td>

                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">
                                {{ $customer->orders_count }}
                            </td>

                            <td class="whitespace-nowrap px-6 py-4 text-sm font-semibold text-gray-900 dark:text-white">
                                {{ format_price($customer->orders_sum_total ?? 0) }}
                            </td>

                            <td class="whitespace-nowrap px-6 py-4 text-right">
                                <a href="{{ route('admin.customers.show', ['customerId' => $customer->id]) }}"
                                    class="text-sm font-semibold text-blue-600 hover:text-blue-800 dark:text-blue-400">                    
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                                No customers found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($customers->hasPages())
            <div class="border-t border-gray-200 px-6 py-4 dark:border-gray-700">
                {{ $customers->links() }}
            </div>
        @endif
    </div>

    {{-- MODAL: Bulk Assign / Move to Segment --}}
    @if ($showBulkSegmentModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm"
            wire:keydown.escape="closeBulkSegmentModal">
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl dark:bg-gray-800 border border-gray-100 dark:border-gray-700 space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-700">
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">
                        Assign Customers to Segment
                    </h3>
                    <button type="button" wire:click="closeBulkSegmentModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="rounded-lg bg-blue-50 p-3 text-xs text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                    You have selected <span class="font-bold">{{ count($selected) }}</span> customer(s).
                </div>

                <form wire:submit.prevent="bulkAssignSegment" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                            Target Customer Segment <span class="text-red-500">*</span>
                        </label>
                        <select wire:model="selectedSegmentId"
                            class="w-full rounded-lg border-gray-300 text-xs shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                            <option value="">Select a segment...</option>
                            @foreach ($segments as $seg)
                                <option value="{{ $seg->id }}">{{ $seg->name }}</option>
                            @endforeach
                        </select>
                        @error('selectedSegmentId') <span class="text-[11px] text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            Assignment Action
                        </label>
                        <div class="space-y-2">
                            <label class="flex items-start gap-2.5 p-2.5 rounded-lg border cursor-pointer {{ $bulkMode === 'add' ? 'border-indigo-500 bg-indigo-50/40 dark:bg-indigo-900/20' : 'border-gray-200 dark:border-gray-700' }}">
                                <input type="radio" wire:model="bulkMode" value="add" class="mt-0.5 text-indigo-600 focus:ring-indigo-500">
                                <div>
                                    <span class="block text-xs font-semibold text-gray-900 dark:text-white">Add to Segment</span>
                                    <span class="block text-[11px] text-gray-500 dark:text-gray-400">Keeps any existing segments the customer is already assigned to.</span>
                                </div>
                            </label>

                            <label class="flex items-start gap-2.5 p-2.5 rounded-lg border cursor-pointer {{ $bulkMode === 'move' ? 'border-indigo-500 bg-indigo-50/40 dark:bg-indigo-900/20' : 'border-gray-200 dark:border-gray-700' }}">
                                <input type="radio" wire:model="bulkMode" value="move" class="mt-0.5 text-indigo-600 focus:ring-indigo-500">
                                <div>
                                    <span class="block text-xs font-semibold text-gray-900 dark:text-white">Move to Segment</span>
                                    <span class="block text-[11px] text-gray-500 dark:text-gray-400">Removes selected customers from previous segments and places them exclusively in this one.</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100 dark:border-gray-700">
                        <button type="button" wire:click="closeBulkSegmentModal"
                            class="rounded-lg border border-gray-300 px-3.5 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                            Cancel
                        </button>
                        <button type="submit"
                            class="rounded-lg bg-indigo-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-indigo-700 transition">
                            Apply Segment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

</div>