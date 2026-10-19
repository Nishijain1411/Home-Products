<div class="space-y-6">

    <style>
        #analyticsDateFrom::-webkit-calendar-picker-indicator,
        #analyticsDateTo::-webkit-calendar-picker-indicator {
            display: block !important;
            opacity: 0 !important;
            position: absolute !important;
            right: 0 !important;
            top: 0 !important;
            width: 100% !important;
            height: 100% !important;
            cursor: pointer !important;
        }
    </style>

    {{-- Header & Toolbar --}}
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between border-b border-gray-200 dark:border-gray-800 pb-5">
        <div>
            <div class="flex items-center gap-2 text-xs font-medium text-gray-500 dark:text-gray-400">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-blue-600 transition">Main</a>
                <span>/</span>
                <span class="text-gray-800 dark:text-gray-200 font-semibold">Analytics</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white mt-1">
                Analytics — Business performance
            </h1>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                Executive business reports, revenue trends, period comparisons, average order value, and customer loyalty.
            </p>
        </div>

        {{-- Filters & Actions --}}
        <div class="flex flex-wrap items-center gap-2.5">
            {{-- Quick Presets --}}
            <div class="inline-flex rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 p-1 shadow-xs">
                @foreach ([
                    'today' => 'Today',
                    'last_7_days' => '7D',
                    'last_30_days' => '30D',
                    'this_month' => 'This Month',
                    'last_month' => 'Last Month',
                    'this_year' => 'This Year',
                ] as $presetKey => $presetLabel)
                    <button
                        type="button"
                        wire:click="setPreset('{{ $presetKey }}')"
                        class="rounded-md px-2.5 py-1 text-xs font-semibold transition {{ $datePreset === $presetKey ? 'bg-blue-600 text-white shadow-xs' : 'text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-700' }}"
                    >
                        {{ $presetLabel }}
                    </button>
                @endforeach
            </div>

            {{-- Custom Date Inputs --}}
            <div class="flex items-center gap-1.5 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-2 py-1 shadow-xs text-xs">
                {{-- Date From --}}
                <div class="relative flex items-center">
                    <input
                        type="date"
                        id="analyticsDateFrom"
                        wire:model.live="dateFrom"
                        max="{{ $dateTo ?: '' }}"
                        onclick="try { this.showPicker(); } catch(e) {}"
                        class="h-7 w-28 sm:w-32 rounded border-0 bg-transparent pr-6 text-xs text-gray-800 dark:text-gray-200 focus:ring-1 focus:ring-blue-500 cursor-pointer"
                        title="Start date"
                    />
                    <button
                        type="button"
                        onclick="try { document.getElementById('analyticsDateFrom').showPicker(); } catch(e) { document.getElementById('analyticsDateFrom').focus(); }"
                        class="absolute right-1 text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition"
                        title="Open calendar"
                        tabindex="-1"
                    >
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </button>
                </div>

                <span class="text-xs text-gray-400 font-medium select-none">to</span>

                {{-- Date To --}}
                <div class="relative flex items-center">
                    <input
                        type="date"
                        id="analyticsDateTo"
                        wire:model.live="dateTo"
                        min="{{ $dateFrom ?: '' }}"
                        onclick="try { this.showPicker(); } catch(e) {}"
                        class="h-7 w-28 sm:w-32 rounded border-0 bg-transparent pr-6 text-xs text-gray-800 dark:text-gray-200 focus:ring-1 focus:ring-blue-500 cursor-pointer"
                        title="End date"
                    />
                    <button
                        type="button"
                        onclick="try { document.getElementById('analyticsDateTo').showPicker(); } catch(e) { document.getElementById('analyticsDateTo').focus(); }"
                        class="absolute right-1 text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition"
                        title="Open calendar"
                        tabindex="-1"
                    >
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </button>
                </div>

                @if ($datePreset === 'custom')
                    <button
                        type="button"
                        wire:click="clearCustomDates"
                        class="rounded p-0.5 text-gray-400 hover:bg-gray-100 hover:text-red-500 dark:hover:bg-gray-700 transition"
                        title="Reset custom date filter to last 30 days"
                    >
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                @endif
            </div>

            {{-- CSV Export Button --}}
            <button
                type="button"
                wire:click="exportCsv"
                wire:loading.attr="disabled"
                wire:target="exportCsv"
                class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white px-3.5 py-2 text-xs font-semibold shadow-xs transition disabled:opacity-50"
                title="Download CSV performance report"
            >
                <span wire:loading.remove wire:target="exportCsv" class="flex items-center gap-1.5">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Export CSV
                </span>
                <span wire:loading wire:target="exportCsv" class="flex items-center gap-1.5">
                    <svg class="h-3.5 w-3.5 animate-spin" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                    </svg>
                    Generating...
                </span>
            </button>
        </div>
    </div>

    {{-- Period Comparison Indicator --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-800/40 px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-800">
        <div class="flex items-center gap-2">
            <span class="inline-block h-2 w-2 rounded-full bg-blue-500"></span>
            <span>Active Reporting Period: <strong class="text-gray-800 dark:text-gray-200">{{ $rangeLabel }}</strong> ({{ $start->format('M d, Y') }} – {{ $end->format('M d, Y') }})</span>
        </div>
        <div class="flex items-center gap-2 text-gray-400">
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
            </svg>
            <span>Compared vs prior period ({{ $prevStart->format('M d, Y') }} – {{ $prevEnd->format('M d, Y') }})</span>
        </div>
    </div>

    {{-- 1. Executive Performance Reports (Comparison across periods) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-5">

        {{-- Card 1: Revenue Comparison Across Periods --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-800/50 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Net Revenue</span>
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-500/15 dark:text-blue-400">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>

                <div class="mt-3">
                    <div class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                        {{ format_price($summary['revenue']['total']) }}
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700/60 flex items-center justify-between text-xs">
                <span class="inline-flex items-center gap-1 font-semibold {{ $summary['revenue']['is_positive'] ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                    @if ($summary['revenue']['is_positive'])
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                        </svg>
                        +{{ $summary['revenue']['growth_percent'] }}%
                    @else
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                        </svg>
                        {{ $summary['revenue']['growth_percent'] }}%
                    @endif
                </span>
                <span class="text-gray-400 truncate">vs prior ({{ format_price($summary['revenue']['previous_total']) }})</span>
            </div>
        </div>

        {{-- Card 2: Order Volume Comparison --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-800/50 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Orders</span>
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/15 dark:text-indigo-400">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                    </div>
                </div>

                <div class="mt-3">
                    <div class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                        {{ number_format($summary['orders']['total']) }}
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700/60 flex items-center justify-between text-xs">
                <span class="inline-flex items-center gap-1 font-semibold {{ $summary['orders']['is_positive'] ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                    @if ($summary['orders']['is_positive'])
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                        </svg>
                        +{{ $summary['orders']['growth_percent'] }}%
                    @else
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                        </svg>
                        {{ $summary['orders']['growth_percent'] }}%
                    @endif
                </span>
                <span class="text-gray-400 truncate">{{ $summary['orders']['completed'] }} delivered</span>
            </div>
        </div>

        {{-- Card 3: Average Order Value (AOV) --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-800/50 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Avg. Order Value (AOV)</span>
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-50 text-amber-600 dark:bg-amber-500/15 dark:text-amber-400">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>

                <div class="mt-3">
                    <div class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                        {{ format_price($summary['aov']['value']) }}
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700/60 flex items-center justify-between text-xs">
                <span class="inline-flex items-center gap-1 font-semibold {{ $summary['aov']['is_positive'] ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                    @if ($summary['aov']['is_positive'])
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                        </svg>
                        +{{ $summary['aov']['growth_percent'] }}%
                    @else
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                        </svg>
                        {{ $summary['aov']['growth_percent'] }}%
                    @endif
                </span>
                <span class="text-gray-400 truncate">vs prior ({{ format_price($summary['aov']['previous_value']) }})</span>
            </div>
        </div>

        {{-- Card 4: New versus Repeat Customers --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-800/50 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">New Customers</span>
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-purple-50 text-purple-600 dark:bg-purple-500/15 dark:text-purple-400">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                </div>

                <div class="mt-3">
                    <div class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                        +{{ number_format($summary['customers']['new_in_period']) }}
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700/60 flex items-center justify-between text-xs">
                <span class="inline-flex items-center gap-1 font-semibold text-purple-600 dark:text-purple-400">
                    {{ $summary['customers']['repeat_rate'] }}% repeat rate
                </span>
                <span class="text-gray-400 truncate">{{ number_format($summary['customers']['total']) }} total registered</span>
            </div>
        </div>

    </div>

    {{-- 2. Revenue and Sales Trends Over Time --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-800/50" x-data="{ hoveredPoint: null }">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-gray-100 dark:border-gray-700/60 pb-4">
            <div>
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                    Revenue & Sales Trends Over Time
                </h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    Trajectory of revenue generation and order volume over {{ strtolower($rangeLabel) }}.
                </p>
            </div>

            <div class="flex items-center gap-2">
                {{-- Metric Switcher --}}
                <div class="inline-flex rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-0.5">
                    <button
                        type="button"
                        wire:click="setChartMetric('revenue')"
                        class="rounded-md px-3 py-1.5 text-xs font-semibold transition {{ $activeChartMetric === 'revenue' ? 'bg-white dark:bg-gray-700 text-blue-600 dark:text-blue-400 shadow-xs' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900' }}"
                    >
                        Revenue ($ / ₹)
                    </button>
                    <button
                        type="button"
                        wire:click="setChartMetric('orders')"
                        class="rounded-md px-3 py-1.5 text-xs font-semibold transition {{ $activeChartMetric === 'orders' ? 'bg-white dark:bg-gray-700 text-blue-600 dark:text-blue-400 shadow-xs' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900' }}"
                    >
                        Order Volume
                    </button>
                </div>
            </div>
        </div>

        {{-- Chart Canvas / SVG Bars --}}
        <div class="mt-6">
            @if (empty($trends['points']))
                <div class="py-16 text-center text-xs text-gray-400">
                    No data points available for this period.
                </div>
            @else
                <div class="space-y-4">
                    {{-- Total in View --}}
                    <div class="flex items-baseline gap-2">
                        <span class="text-xs text-gray-500 dark:text-gray-400">Period Total:</span>
                        <span class="text-xl font-extrabold text-gray-900 dark:text-white">
                            @if ($activeChartMetric === 'revenue')
                                {{ format_price($trends['total_revenue']) }}
                            @else
                                {{ number_format($trends['total_orders']) }} orders
                            @endif
                        </span>
                    </div>

                    {{-- Responsive Bar Chart with Tooltips --}}
                    <div class="relative h-56 w-full flex items-end gap-1 sm:gap-2 pt-6 pb-2 border-b border-gray-200 dark:border-gray-700">
                        @php
                            $maxVal = $activeChartMetric === 'revenue' ? $trends['max_revenue'] : $trends['max_orders'];
                            $pointCount = count($trends['points']);
                        @endphp

                        @foreach ($trends['points'] as $index => $point)
                            @php
                                $val = $activeChartMetric === 'revenue' ? $point['revenue'] : $point['orders'];
                                $heightPercent = $maxVal > 0 ? max(4, round(($val / $maxVal) * 100)) : 4;
                            @endphp

                            <div
                                class="flex-1 h-full flex flex-col justify-end items-center relative group"
                                @mouseenter="hoveredPoint = {{ json_encode($point) }}"
                                @mouseleave="hoveredPoint = null"
                            >
                                {{-- Bar --}}
                                <div
                                    class="w-full max-w-[28px] rounded-t-md transition-all duration-300 {{ $activeChartMetric === 'revenue' ? 'bg-blue-500 hover:bg-blue-600 group-hover:shadow-md' : 'bg-indigo-500 hover:bg-indigo-600 group-hover:shadow-md' }}"
                                    style="height: {{ $heightPercent }}%;"
                                ></div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Tooltip Details Pill (Hovered) --}}
                    <div class="h-6 flex items-center justify-between text-xs">
                        <template x-if="hoveredPoint">
                            <div class="flex items-center gap-3 font-medium text-gray-700 dark:text-gray-300">
                                <span class="font-bold text-gray-900 dark:text-white" x-text="hoveredPoint.label + ' (' + hoveredPoint.date + ')'"></span>
                                <span>Revenue: <strong class="text-blue-600 dark:text-blue-400" x-text="'₹' + Number(hoveredPoint.revenue).toLocaleString('en-IN', {minimumFractionDigits: 2})"></strong></span>
                                <span>Orders: <strong class="text-indigo-600 dark:text-indigo-400" x-text="hoveredPoint.orders"></strong></span>
                                <span>AOV: <strong class="text-gray-800 dark:text-gray-200" x-text="'₹' + Number(hoveredPoint.aov).toLocaleString('en-IN', {minimumFractionDigits: 2})"></strong></span>
                            </div>
                        </template>
                        <template x-if="!hoveredPoint">
                            <span class="text-gray-400 text-[11px]">Hover over any bar to inspect daily sales, orders, and AOV details.</span>
                        </template>

                        <div class="flex items-center gap-4 text-[11px] text-gray-400">
                            <span>Start: {{ $trends['points'][0]['label'] ?? '' }}</span>
                            <span>End: {{ end($trends['points'])['label'] ?? '' }}</span>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- 3. Order Status Breakdown & Revenue Composition --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">

        {{-- Order Status Breakdown (7 cols) --}}
        <div class="lg:col-span-7 rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-800/50">
            <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700/60 pb-3">
                <div>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                        Order Status Breakdown
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        Fulfillment distribution across live store statuses.
                    </p>
                </div>
                <a href="{{ route('admin.orders.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400">
                    View Orders &rarr;
                </a>
            </div>

            {{-- Progress Bars and Counts --}}
            <div class="mt-5 space-y-4">
                @php
                    $statusConfig = [
                        'delivered' => ['label' => 'Delivered (Completed)', 'color' => 'bg-emerald-500', 'textColor' => 'text-emerald-700 dark:text-emerald-400'],
                        'processing' => ['label' => 'Processing', 'color' => 'bg-blue-500', 'textColor' => 'text-blue-700 dark:text-blue-400'],
                        'shipped' => ['label' => 'Shipped (In Transit)', 'color' => 'bg-sky-500', 'textColor' => 'text-sky-700 dark:text-sky-400'],
                        'pending' => ['label' => 'Pending Confirmation', 'color' => 'bg-amber-500', 'textColor' => 'text-amber-700 dark:text-amber-400'],
                        'cancelled' => ['label' => 'Cancelled', 'color' => 'bg-red-500', 'textColor' => 'text-red-700 dark:text-red-400'],
                        'refunded' => ['label' => 'Refunded', 'color' => 'bg-purple-500', 'textColor' => 'text-purple-700 dark:text-purple-400'],
                    ];
                @endphp

                @foreach ($statusConfig as $statusKey => $cfg)
                    @php
                        $count = $summary['orders']['status_counts'][$statusKey] ?? 0;
                        $pct = $summary['orders']['status_percentages'][$statusKey] ?? 0;
                    @endphp
                    <div>
                        <div class="flex items-center justify-between text-xs mb-1.5">
                            <span class="font-medium text-gray-700 dark:text-gray-300">
                                {{ $cfg['label'] }}
                            </span>
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-gray-900 dark:text-white">{{ number_format($count) }}</span>
                                <span class="font-mono text-gray-400">({{ $pct }}%)</span>
                            </div>
                        </div>
                        <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-2 overflow-hidden">
                            <div class="{{ $cfg['color'] }} h-2 rounded-full transition-all duration-500" style="width: {{ $pct }}%;"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Revenue Composition & Period Comparison (5 cols) --}}
        <div class="lg:col-span-5 rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-800/50">
            <div class="border-b border-gray-100 dark:border-gray-700/60 pb-3">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                    Revenue Composition
                </h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    Gross sales, discount deductions, shipping fees, and net revenue.
                </p>
            </div>

            <div class="mt-4 space-y-3 divide-y divide-gray-100 dark:divide-gray-700/50">
                <div class="pt-2 flex items-center justify-between text-xs">
                    <span class="text-gray-500 dark:text-gray-400">Gross Merchandise Sales</span>
                    <span class="font-semibold text-gray-900 dark:text-white">{{ format_price($summary['revenue']['gross_sales']) }}</span>
                </div>

                <div class="pt-3 flex items-center justify-between text-xs">
                    <span class="text-gray-500 dark:text-gray-400 flex items-center gap-1.5">
                        <svg class="h-3.5 w-3.5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                        </svg>
                        Coupon & Code Discounts
                    </span>
                    <span class="font-semibold text-red-600 dark:text-red-400">-{{ format_price($summary['revenue']['discounts']) }}</span>
                </div>

                <div class="pt-3 flex items-center justify-between text-xs">
                    <span class="text-gray-500 dark:text-gray-400">Shipping Fees Collected</span>
                    <span class="font-semibold text-gray-900 dark:text-white">+{{ format_price($summary['revenue']['shipping']) }}</span>
                </div>

                <div class="pt-3 flex items-center justify-between text-xs">
                    <span class="text-gray-500 dark:text-gray-400">Taxes Collected</span>
                    <span class="font-semibold text-gray-900 dark:text-white">+{{ format_price($summary['revenue']['tax']) }}</span>
                </div>

                <div class="pt-3 flex items-center justify-between text-sm font-bold bg-blue-50/50 dark:bg-blue-900/20 p-2.5 rounded-xl border border-blue-100 dark:border-blue-800">
                    <span class="text-blue-900 dark:text-blue-200">Net Settled Revenue</span>
                    <span class="text-blue-600 dark:text-blue-400 font-mono text-base">{{ format_price($summary['revenue']['total']) }}</span>
                </div>
            </div>
        </div>

    </div>

    {{-- 4. Best-Selling Products & Customer Insights (New vs Repeat) --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">

        {{-- Best-Selling Products (7 cols) --}}
        <div class="lg:col-span-7 rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-800/50">
            <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700/60 pb-3">
                <div>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                        Best-Selling Products
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        Top performing products ranked by revenue and units sold in this period.
                    </p>
                </div>
                <a href="{{ route('admin.products.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400">
                    View Catalog &rarr;
                </a>
            </div>

            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-left text-xs">
                    <thead class="text-gray-400 uppercase tracking-wider font-semibold">
                        <tr>
                            <th class="py-2.5">Product</th>
                            <th class="py-2.5 text-center">Units Sold</th>
                            <th class="py-2.5 text-right">Total Revenue</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($topProducts as $idx => $prod)
                            <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-700/20 transition">
                                <td class="py-3 pr-3">
                                    <div class="flex items-center gap-3">
                                        <span class="flex h-5 w-5 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-700 font-bold text-[10px] text-gray-600 dark:text-gray-300">
                                            {{ $idx + 1 }}
                                        </span>
                                        @if (!empty($prod['image']))
                                            <img src="{{ Storage::disk('public')->url($prod['image']) }}" alt="" class="h-9 w-9 rounded-lg object-cover border border-gray-200 shrink-0" />
                                        @else
                                            <div class="h-9 w-9 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-400 shrink-0 font-bold text-xs">
                                                {{ substr($prod['product_name'], 0, 1) }}
                                            </div>
                                        @endif
                                        <div class="min-w-0">
                                            <div class="font-semibold text-gray-900 dark:text-white truncate max-w-[220px]">
                                                {{ $prod['product_name'] }}
                                            </div>
                                            <div class="font-mono text-[10px] text-gray-400">
                                                SKU: {{ $prod['sku'] }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 text-center whitespace-nowrap">
                                    <span class="inline-flex items-center rounded-full bg-blue-50 dark:bg-blue-500/10 px-2 py-0.5 font-bold text-blue-700 dark:text-blue-300">
                                        {{ number_format($prod['units_sold']) }} sold
                                    </span>
                                </td>
                                <td class="py-3 text-right font-bold text-gray-900 dark:text-white whitespace-nowrap">
                                    {{ format_price($prod['total_sales']) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-8 text-center text-xs text-gray-400">
                                    No completed product sales recorded for this date range.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- New versus Repeat Customers Report (5 cols) --}}
        <div class="lg:col-span-5 rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-800/50">
            <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700/60 pb-3">
                <div>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                        New vs Repeat Customers
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        Customer acquisition velocity and retention rate.
                    </p>
                </div>
                <a href="{{ route('admin.customers.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400">
                    Customers &rarr;
                </a>
            </div>

            <div class="mt-4 space-y-4">
                {{-- Comparison Stat Blocks --}}
                <div class="grid grid-cols-2 gap-3">
                    <div class="p-3 rounded-xl bg-purple-50/60 dark:bg-purple-950/20 border border-purple-100 dark:border-purple-900/40">
                        <span class="text-[11px] font-semibold text-purple-700 dark:text-purple-300">New Customers</span>
                        <div class="text-xl font-bold text-gray-900 dark:text-white mt-1">
                            +{{ number_format($summary['customers']['new_in_period']) }}
                        </div>
                        <span class="text-[10px] text-gray-400">
                            vs {{ number_format($summary['customers']['previous_new']) }} prior
                        </span>
                    </div>

                    <div class="p-3 rounded-xl bg-blue-50/60 dark:bg-blue-950/20 border border-blue-100 dark:border-blue-900/40">
                        <span class="text-[11px] font-semibold text-blue-700 dark:text-blue-300">Repeat Buyers</span>
                        <div class="text-xl font-bold text-gray-900 dark:text-white mt-1">
                            {{ number_format($summary['customers']['repeat_count']) }}
                        </div>
                        <span class="text-[10px] text-gray-400">
                            {{ $summary['customers']['repeat_rate'] }}% repeat rate
                        </span>
                    </div>
                </div>

                {{-- Retention Summary Bar --}}
                <div class="pt-2">
                    <div class="flex items-center justify-between text-xs mb-1.5">
                        <span class="text-gray-600 dark:text-gray-300 font-medium">Customer Retention Share</span>
                        <span class="font-bold text-purple-600 dark:text-purple-400">{{ $summary['customers']['repeat_rate'] }}%</span>
                    </div>
                    <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-2.5 overflow-hidden flex">
                        <div class="bg-purple-500 h-2.5 transition-all duration-500" style="width: {{ min(100, $summary['customers']['repeat_rate']) }}%;" title="Repeat Customers"></div>
                        <div class="bg-blue-300 dark:bg-blue-600 h-2.5 transition-all duration-500" style="width: {{ max(0, 100 - min(100, $summary['customers']['repeat_rate'])) }}%;" title="New Customers"></div>
                    </div>
                    <div class="flex items-center justify-between text-[11px] text-gray-400 mt-1.5">
                        <span class="flex items-center gap-1.5">
                            <span class="h-2 w-2 rounded-full bg-purple-500 inline-block"></span> Repeat buyers (≥2 orders)
                        </span>
                        <span class="flex items-center gap-1.5">
                            <span class="h-2 w-2 rounded-full bg-blue-300 dark:bg-blue-600 inline-block"></span> First-time buyers
                        </span>
                    </div>
                </div>

                {{-- Total Base Overview --}}
                <div class="pt-3 border-t border-gray-100 dark:border-gray-700/60 flex items-center justify-between text-xs">
                    <span class="text-gray-500 dark:text-gray-400">Total Registered Customer Base</span>
                    <span class="font-bold text-gray-900 dark:text-white">{{ number_format($summary['customers']['total']) }} users</span>
                </div>
            </div>
        </div>

    </div>

</div>
