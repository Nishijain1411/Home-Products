<div class="p-4 md:p-6 max-w-7xl mx-auto space-y-6">

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

    {{-- Breadcrumbs & Top Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b border-gray-200 pb-5 dark:border-gray-800">
        <div>
            <div class="flex items-center gap-2 text-xs font-medium text-gray-500 dark:text-gray-400">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-blue-600 transition">Dashboard</a>
                <span>/</span>
                <span class="text-gray-800 dark:text-gray-300">Sales</span>
                <span>/</span>
                <span class="text-gray-800 dark:text-gray-300">Customer Segments</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white mt-1">
                Customer Segments
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                Group customers by buying behavior, VIP status, or custom criteria and bulk assign them like in Shopify.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.customers.index') }}"
                class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3.5 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 transition dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-750">
                <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                All Customers
            </a>

            <button type="button" wire:click="openCreateSegment"
                class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-blue-700 transition">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Create Segment
            </button>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Segments</div>
            <div class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($totalSegments) }}</div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Segmented Customers</div>
            <div class="mt-2 text-2xl font-bold text-blue-600 dark:text-blue-400">{{ number_format($totalSegmentedCustomers) }}</div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Active Segments</div>
            <div class="mt-2 text-2xl font-bold text-emerald-600 dark:text-emerald-400">
                {{ \App\Models\CustomerSegment::where('is_active', true)->count() }}
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Top Segment</div>
            <div class="mt-2 text-lg font-bold text-purple-600 dark:text-purple-400 truncate">
                {{ $largestSegment ? $largestSegment->name : 'None yet' }}
            </div>
            @if ($largestSegment)
                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $largestSegment->customers_count }} customers</div>
            @endif
        </div>
    </div>

    {{-- VIEW 1: Active Segment Inspection & Member Management --}}
    @if ($activeSegment)
        <div class="space-y-4">
            {{-- Segment Detail Banner --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <button type="button" wire:click="closeSegmentMembers"
                            class="inline-flex items-center gap-1.5 text-xs font-semibold text-blue-600 hover:text-blue-800 mb-2 transition dark:text-blue-400 dark:hover:text-blue-300">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            Back to All Segments
                        </button>

                        <div class="flex items-center gap-3">
                            <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                                {{ $activeSegment->name }}
                            </h2>
                            @php
                                $colorMap = [
                                    'blue' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300 border-blue-200',
                                    'indigo' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-300 border-indigo-200',
                                    'purple' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300 border-purple-200',
                                    'emerald' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300 border-emerald-200',
                                    'amber' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300 border-amber-200',
                                    'rose' => 'bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300 border-rose-200',
                                    'cyan' => 'bg-cyan-100 text-cyan-800 dark:bg-cyan-900/40 dark:text-cyan-300 border-cyan-200',
                                    'teal' => 'bg-teal-100 text-teal-800 dark:bg-teal-900/40 dark:text-teal-300 border-teal-200',
                                ];
                                $badgeClass = $colorMap[$activeSegment->color] ?? $colorMap['blue'];
                            @endphp
                            <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold {{ $badgeClass }}">
                                {{ $activeSegment->customers_count }} {{ Str::plural('Customer', $activeSegment->customers_count) }}
                            </span>
                        </div>
                        @if ($activeSegment->description)
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                {{ $activeSegment->description }}
                            </p>
                        @endif
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" wire:click="openEditSegment({{ $activeSegment->id }})"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 transition dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-650">
                            <svg class="h-3.5 w-3.5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                            </svg>
                            Edit Segment
                        </button>

                        <button type="button" wire:click="openAddCustomersModal"
                            class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-blue-700 transition">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                            </svg>
                            + Add / Move Customers
                        </button>
                    </div>
                </div>
            </div>

            {{-- Members Search & Bulk Actions Bar --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="relative w-full max-w-sm">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input type="text" wire:model.live.debounce.300ms="searchMembers"
                        placeholder="Search customers in segment..."
                        class="w-full rounded-lg border border-gray-300 bg-white pl-9 pr-4 py-2 text-xs text-gray-900 placeholder:text-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                </div>

                @if (!empty($selectedMembers))
                    <div class="flex items-center gap-3 rounded-lg border border-red-200 bg-red-50 px-3 py-1.5 dark:border-red-900/40 dark:bg-red-950/30">
                        <span class="text-xs font-medium text-red-800 dark:text-red-300">
                            {{ count($selectedMembers) }} selected
                        </span>
                        <button type="button" wire:click="removeSelectedMembers"
                            class="inline-flex items-center gap-1 rounded bg-red-600 px-2.5 py-1 text-xs font-semibold text-white shadow-sm hover:bg-red-700 transition">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Remove from Segment
                        </button>
                    </div>
                @endif
            </div>

            {{-- Members Table --}}
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-750">
                            <tr>
                                <th class="w-12 px-4 py-3 text-left">
                                    <input type="checkbox" wire:model.live="selectAllMembers"
                                        class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700">
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Customer
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Email
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Orders
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Total Spent
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse ($segmentMembers as $member)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-750 transition" wire:key="member-{{ $member->id }}">
                                    <td class="w-12 px-4 py-3.5">
                                        <input type="checkbox" wire:model.live="selectedMembers" value="{{ $member->id }}"
                                            class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700">
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-3.5 text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $member->name }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-3.5 text-sm text-gray-600 dark:text-gray-300">
                                        {{ $member->email }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-3.5 text-sm text-gray-700 dark:text-gray-300 font-medium">
                                        {{ $member->orders_count }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-3.5 text-sm font-semibold text-gray-900 dark:text-white">
                                        {{ format_price($member->orders_sum_total ?? 0) }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-3.5 text-right text-xs space-x-2">
                                        <a href="{{ route('admin.customers.show', $member->id) }}"
                                            class="font-semibold text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                            View
                                        </a>
                                        <button type="button" wire:click="removeMember({{ $member->id }})"
                                            class="font-semibold text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                                            Remove
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                                        <div class="flex flex-col items-center justify-center">
                                            <svg class="h-10 w-10 text-gray-300 dark:text-gray-600 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                            </svg>
                                            <p class="font-medium text-gray-700 dark:text-gray-300">No customers in this segment yet</p>
                                            <p class="text-xs text-gray-400 mt-0.5">Click "+ Add / Move Customers" above to bulk assign customers to this segment.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($segmentMembers->hasPages())
                    <div class="border-t border-gray-200 px-6 py-4 dark:border-gray-700">
                        {{ $segmentMembers->links() }}
                    </div>
                @endif
            </div>
        </div>

    {{-- VIEW 2: All Segments Grid / Directory --}}
    @else
        {{-- Search Toolbar --}}
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="relative max-w-md">
                <input type="text" wire:model.live.debounce.300ms="searchSegment"
                    placeholder="Search segments by name or description..."
                    class="block w-full rounded-lg border-gray-300 pl-9 text-xs shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>
        </div>

        {{-- Segments Cards Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @forelse ($segments as $segment)
                @php
                    $colorMap = [
                        'blue' => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-900/30 dark:text-blue-300 dark:border-blue-800',
                        'indigo' => 'bg-indigo-50 text-indigo-700 border-indigo-200 dark:bg-indigo-900/30 dark:text-indigo-300 dark:border-indigo-800',
                        'purple' => 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-900/30 dark:text-purple-300 dark:border-purple-800',
                        'emerald' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-300 dark:border-emerald-800',
                        'amber' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-900/30 dark:text-amber-300 dark:border-amber-800',
                        'rose' => 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-900/30 dark:text-rose-300 dark:border-rose-800',
                        'cyan' => 'bg-cyan-50 text-cyan-700 border-cyan-200 dark:bg-cyan-900/30 dark:text-cyan-300 dark:border-cyan-800',
                        'teal' => 'bg-teal-50 text-teal-700 border-teal-200 dark:bg-teal-900/30 dark:text-teal-300 dark:border-teal-800',
                    ];
                    $badgeStyle = $colorMap[$segment->color] ?? $colorMap['blue'];
                @endphp
                <div class="flex flex-col justify-between rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition hover:shadow-md dark:border-gray-700 dark:bg-gray-800"
                    wire:key="segment-card-{{ $segment->id }}">
                    <div>
                        <div class="flex items-start justify-between gap-2">
                            <span class="inline-flex items-center rounded-lg border px-2.5 py-1 text-xs font-semibold {{ $badgeStyle }}">
                                {{ $segment->name }}
                            </span>
                            <span class="text-xs text-gray-400">
                                {{ $segment->customers_count }} {{ Str::plural('Customer', $segment->customers_count) }}
                            </span>
                        </div>

                        <p class="mt-3 text-xs text-gray-500 dark:text-gray-400 line-clamp-2">
                            {{ $segment->description ?: 'No description provided.' }}
                        </p>

                        <div class="mt-4 flex items-center gap-2 text-[11px] text-gray-400">
                            <span>Slug: <code class="text-gray-600 dark:text-gray-300 font-mono">{{ $segment->slug }}</code></span>
                            <span>&bull;</span>
                            <span>{{ $segment->is_active ? 'Active' : 'Inactive' }}</span>
                        </div>
                    </div>

                    <div class="mt-5 pt-4 border-t border-gray-100 flex items-center justify-between dark:border-gray-700">
                        <button type="button" wire:click="viewSegmentMembers({{ $segment->id }})"
                            class="inline-flex items-center gap-1.5 text-xs font-semibold text-blue-600 hover:text-blue-800 transition dark:text-blue-400 dark:hover:text-blue-300">
                            <span>Manage Customers</span>
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>

                        <div class="flex items-center gap-2">
                            <button type="button" wire:click="openEditSegment({{ $segment->id }})"
                                class="rounded p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition dark:hover:bg-gray-700 dark:hover:text-gray-200" title="Edit Segment">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                </svg>
                            </button>

                            <button type="button" wire:click="confirmDeleteSegment({{ $segment->id }})"
                                class="rounded p-1 text-red-400 hover:bg-red-50 hover:text-red-600 transition dark:hover:bg-red-950/30 dark:hover:text-red-400" title="Delete Segment">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full rounded-2xl border border-dashed border-gray-300 p-12 text-center dark:border-gray-700 bg-white dark:bg-gray-800">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400 mb-3">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.999-3.199a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">No Customer Segments Created Yet</h3>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 max-w-sm mx-auto">
                        Create your first customer segment to group VIP shoppers, repeat buyers, or promotional targets.
                    </p>
                    <button type="button" wire:click="openCreateSegment"
                        class="mt-4 inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-blue-700 transition">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Create First Segment
                    </button>
                </div>
            @endforelse
        </div>

        @if ($segments->hasPages())
            <div class="pt-4">
                {{ $segments->links() }}
            </div>
        @endif
    @endif

    {{-- MODAL 1: Create / Edit Segment Modal --}}
    @if ($showSegmentModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm"
            wire:keydown.escape="closeSegmentModal">
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl dark:bg-gray-800 border border-gray-100 dark:border-gray-700 space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-700">
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">
                        {{ $editingSegmentId ? 'Edit Customer Segment' : 'Create Customer Segment' }}
                    </h3>
                    <button type="button" wire:click="closeSegmentModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form wire:submit.prevent="saveSegment" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                            Segment Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" wire:model.live.debounce.300ms="name" placeholder="e.g. VIP Customers, High Spenders"
                            class="w-full rounded-lg border-gray-300 text-xs shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        @error('name') <span class="text-[11px] text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                            Slug <span class="text-red-500">*</span>
                        </label>
                        <input type="text" wire:model="slug" placeholder="e.g. vip-customers"
                            class="w-full rounded-lg border-gray-300 text-xs shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2 dark:border-gray-600 dark:bg-gray-700 dark:text-white font-mono">
                        @error('slug') <span class="text-[11px] text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                            Description
                        </label>
                        <textarea wire:model="description" rows="2" placeholder="Brief note on who belongs in this segment..."
                            class="w-full rounded-lg border-gray-300 text-xs shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2 dark:border-gray-600 dark:bg-gray-700 dark:text-white"></textarea>
                        @error('description') <span class="text-[11px] text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                            Badge Color
                        </label>
                        <div class="grid grid-cols-4 gap-2">
                            @foreach (['blue', 'indigo', 'purple', 'emerald', 'amber', 'rose', 'cyan', 'teal'] as $c)
                                <label class="flex items-center gap-1.5 cursor-pointer rounded-lg border p-2 text-xs transition {{ $color === $c ? 'border-blue-500 ring-2 ring-blue-500/20 bg-blue-50/50 dark:bg-blue-900/20' : 'border-gray-200 dark:border-gray-700' }}">
                                    <input type="radio" wire:model="color" value="{{ $c }}" class="sr-only">
                                    <span class="h-3 w-3 rounded-full bg-{{ $c }}-500 shrink-0"></span>
                                    <span class="capitalize text-gray-700 dark:text-gray-300 text-[11px]">{{ $c }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('color') <span class="text-[11px] text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex items-center gap-2 pt-1">
                        <input type="checkbox" id="segmentIsActive" wire:model="is_active"
                            class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700">
                        <label for="segmentIsActive" class="text-xs font-medium text-gray-700 dark:text-gray-300">
                            Active Segment
                        </label>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100 dark:border-gray-700">
                        <button type="button" wire:click="closeSegmentModal"
                            class="rounded-lg border border-gray-300 px-3.5 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                            Cancel
                        </button>
                        <button type="submit"
                            class="rounded-lg bg-blue-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-blue-700">
                            {{ $editingSegmentId ? 'Save Changes' : 'Create Segment' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- MODAL 2: Delete Segment Confirmation --}}
    @if ($showDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm">
            <div class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-2xl dark:bg-gray-800 border border-gray-100 dark:border-gray-700 space-y-4">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">Delete Customer Segment?</h3>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        This action will delete the segment. Customers attached to this segment will remain in your store and will NOT be deleted.
                    </p>
                </div>
                <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100 dark:border-gray-700">
                    <button type="button" wire:click="$set('showDeleteModal', false)"
                        class="rounded-lg border border-gray-300 px-3.5 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                        Cancel
                    </button>
                    <button type="button" wire:click="deleteSegment"
                        class="rounded-lg bg-red-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-red-700">
                        Confirm Delete
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL 3: Add / Move Customers to Segment --}}
    @if ($showAddCustomersModal && $activeSegment)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm"
            wire:keydown.escape="closeAddCustomersModal">
            <div class="w-full max-w-3xl rounded-2xl bg-white p-6 shadow-2xl dark:bg-gray-800 border border-gray-100 dark:border-gray-700 space-y-4 max-h-[90vh] flex flex-col">
                
                {{-- Modal Header --}}
                <div class="flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-700 shrink-0">
                    <div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-white">
                            Add Customers to <span class="text-blue-600 dark:text-blue-400 font-semibold">'{{ $activeSegment->name }}'</span>
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                            Search and select customers from your store to assign to this segment.
                        </p>
                    </div>
                    <button type="button" wire:click="closeAddCustomersModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Action Controls & Mode --}}
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 shrink-0 bg-gray-50 p-3 rounded-xl dark:bg-gray-750">
                    <div class="relative w-full sm:max-w-xs">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input type="text" wire:model.live.debounce.300ms="customerSearch"
                            placeholder="Filter by name or email..."
                            class="w-full rounded-lg border border-gray-300 bg-white pl-9 pr-3 py-1.5 text-xs text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    </div>

                    {{-- Assignment Mode: Add vs Move --}}
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">Mode:</span>
                        <div class="inline-flex rounded-lg border border-gray-300 bg-white p-0.5 dark:border-gray-600 dark:bg-gray-700">
                            <button type="button" wire:click="$set('assignmentMode', 'add')"
                                class="rounded-md px-2.5 py-1 text-xs font-semibold transition {{ $assignmentMode === 'add' ? 'bg-blue-600 text-white shadow-sm' : 'text-gray-600 hover:text-gray-900 dark:text-gray-300' }}">
                                Add to Segment
                            </button>
                            <button type="button" wire:click="$set('assignmentMode', 'move')"
                                class="rounded-md px-2.5 py-1 text-xs font-semibold transition {{ $assignmentMode === 'move' ? 'bg-blue-600 text-white shadow-sm' : 'text-gray-600 hover:text-gray-900 dark:text-gray-300' }}"
                                title="Move: removes selected customers from previous segments and places exclusively in this segment">
                                Move Exclusively
                            </button>
                        </div>
                    </div>
                </div>

                @if ($assignmentMode === 'move')
                    <div class="rounded-lg bg-amber-50 border border-amber-200 p-2.5 text-xs text-amber-800 dark:bg-amber-950/30 dark:border-amber-900/50 dark:text-amber-300 shrink-0 flex items-center gap-2">
                        <svg class="h-4 w-4 shrink-0 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span><strong>Move Mode:</strong> Selected customers will be detached from all other segments and assigned exclusively to '{{ $activeSegment->name }}'.</span>
                    </div>
                @endif

                {{-- Scrollable Customers Table --}}
                <div class="overflow-y-auto flex-1 border border-gray-200 rounded-xl dark:border-gray-700">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 sticky top-0 dark:bg-gray-750 z-10">
                            <tr>
                                <th class="w-12 px-4 py-3 text-left">
                                    <input type="checkbox" wire:model.live="selectAllCustomers"
                                        class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700">
                                </th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Customer
                                </th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Current Segments
                                </th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Orders
                                </th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Spent
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse ($availableCustomers as $cust)
                                @php
                                    $alreadyInActive = $cust->segments->contains('id', $activeSegment->id);
                                @endphp
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-750 transition {{ $alreadyInActive ? 'bg-blue-50/30 dark:bg-blue-950/20' : '' }}"
                                    wire:key="avail-cust-{{ $cust->id }}">
                                    <td class="w-12 px-4 py-3">
                                        <input type="checkbox" wire:model.live="selectedCustomers" value="{{ $cust->id }}"
                                            class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700">
                                    </td>
                                    <td class="px-5 py-3">
                                        <div class="text-xs font-semibold text-gray-900 dark:text-white flex items-center gap-1.5">
                                            {{ $cust->name }}
                                            @if ($alreadyInActive)
                                                <span class="inline-flex items-center rounded bg-blue-100 px-1.5 py-0.2 text-[10px] font-semibold text-blue-800 dark:bg-blue-900/50 dark:text-blue-300">
                                                    Already in segment
                                                </span>
                                            @endif
                                        </div>
                                        <div class="text-[11px] text-gray-500 dark:text-gray-400">{{ $cust->email }}</div>
                                    </td>
                                    <td class="px-5 py-3">
                                        <div class="flex flex-wrap gap-1">
                                            @forelse ($cust->segments as $seg)
                                                <span class="inline-flex items-center rounded px-1.5 py-0.5 text-[10px] font-medium bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                                    {{ $seg->name }}
                                                </span>
                                            @empty
                                                <span class="text-[11px] text-gray-400">None</span>
                                            @endforelse
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-3 text-xs text-gray-700 dark:text-gray-300 font-medium">
                                        {{ $cust->orders_count }}
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-3 text-xs font-semibold text-gray-900 dark:text-white">
                                        {{ format_price($cust->orders_sum_total ?? 0) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-xs text-gray-500 dark:text-gray-400">
                                        No customers match your search criteria.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($availableCustomers->hasPages())
                    <div class="shrink-0 pt-2">
                        {{ $availableCustomers->links() }}
                    </div>
                @endif

                {{-- Modal Footer with Action Buttons --}}
                <div class="flex items-center justify-between border-t border-gray-100 pt-3 dark:border-gray-700 shrink-0">
                    <div class="text-xs font-medium text-gray-600 dark:text-gray-300">
                        @if (!empty($selectedCustomers))
                            <span class="text-blue-600 dark:text-blue-400 font-bold">{{ count($selectedCustomers) }}</span> customer(s) selected
                        @else
                            No customers selected
                        @endif
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" wire:click="closeAddCustomersModal"
                            class="rounded-lg border border-gray-300 px-3.5 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                            Cancel
                        </button>
                        <button type="button" wire:click="assignCustomersToSegment"
                            @disabled(empty($selectedCustomers))
                            class="rounded-lg bg-blue-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition">
                            {{ $assignmentMode === 'move' ? 'Move Selected to Segment' : 'Add Selected to Segment' }}
                        </button>
                    </div>
                </div>

            </div>
        </div>
    @endif

</div>
