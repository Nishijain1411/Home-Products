<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Role;

class AnalyticsService
{
    /**
     * Resolve the start and end dates from a preset or custom range.
     *
     * @return array{0: CarbonInterface, 1: CarbonInterface, 2: string} [start, end, label]
     */
    public function resolveDateRange(?string $preset = 'last_30_days', ?string $from = null, ?string $to = null): array
    {
        $now = now();

        if ($preset === 'custom' || empty($preset)) {
            if (! empty($from) || ! empty($to)) {
                $start = ! empty($from)
                    ? Carbon::parse($from)->startOfDay()
                    : (! empty($to) ? Carbon::parse($to)->subDays(29)->startOfDay() : $now->copy()->subDays(29)->startOfDay());

                $end = ! empty($to)
                    ? Carbon::parse($to)->endOfDay()
                    : $now->copy()->endOfDay();

                if ($start->gt($end)) {
                    [$start, $end] = [$end->copy()->startOfDay(), $start->copy()->endOfDay()];
                }

                return [$start, $end, $start->format('d M Y').' – '.$end->format('d M Y')];
            }
        }

        return match ($preset) {
            'today' => [
                $now->copy()->startOfDay(),
                $now->copy()->endOfDay(),
                'Today',
            ],
            'yesterday' => [
                $now->copy()->subDay()->startOfDay(),
                $now->copy()->subDay()->endOfDay(),
                'Yesterday',
            ],
            'last_7_days' => [
                $now->copy()->subDays(6)->startOfDay(),
                $now->copy()->endOfDay(),
                'Last 7 Days',
            ],
            'this_month' => [
                $now->copy()->startOfMonth(),
                $now->copy()->endOfMonth(),
                'This Month ('.$now->format('M Y').')',
            ],
            'last_month' => [
                $now->copy()->subMonth()->startOfMonth(),
                $now->copy()->subMonth()->endOfMonth(),
                'Last Month ('.$now->copy()->subMonth()->format('M Y').')',
            ],
            'this_year' => [
                $now->copy()->startOfYear(),
                $now->copy()->endOfYear(),
                'This Year ('.$now->format('Y').')',
            ],
            'all_time' => [
                $this->getEarliestDate(),
                $now->copy()->endOfDay(),
                'All Time',
            ],
            default => [
                $now->copy()->subDays(29)->startOfDay(),
                $now->copy()->endOfDay(),
                'Last 30 Days',
            ],
        };
    }

    /**
     * Compute previous period with identical duration immediately prior to start date.
     *
     * @return array{0: CarbonInterface, 1: CarbonInterface} [prevStart, prevEnd]
     */
    public function getComparisonPeriod(CarbonInterface $start, CarbonInterface $end): array
    {
        $durationInSeconds = abs((int) $end->diffInSeconds($start));
        $prevEnd = $start->copy()->subSecond();
        $prevStart = $prevEnd->copy()->subSeconds($durationInSeconds);

        return [$prevStart, $prevEnd];
    }

    /**
     * Aggregate executive performance summary metrics for the given period.
     */
    public function getSummary(CarbonInterface $start, CarbonInterface $end): array
    {
        [$prevStart, $prevEnd] = $this->getComparisonPeriod($start, $end);

        // Current period orders (exclude cancelled for revenue)
        $ordersInPeriod = Order::query()
            ->whereBetween('created_at', [$start, $end])
            ->get();

        $validOrders = $ordersInPeriod->where('status', '!=', 'cancelled');

        $currentRevenue = (float) $validOrders->sum('total');
        $currentGross = (float) $validOrders->sum(fn ($o) => (float) $o->subtotal + (float) $o->shipping_amount + (float) $o->tax_amount);
        $currentDiscounts = (float) $validOrders->sum('discount_amount');
        $currentShipping = (float) $validOrders->sum('shipping_amount');
        $currentTax = (float) $validOrders->sum('tax_amount');
        $totalOrdersCount = $ordersInPeriod->count();
        $validOrdersCount = $validOrders->count();

        // Previous period orders
        $prevOrdersInPeriod = Order::query()
            ->whereBetween('created_at', [$prevStart, $prevEnd])
            ->get();

        $prevValidOrders = $prevOrdersInPeriod->where('status', '!=', 'cancelled');
        $prevRevenue = (float) $prevValidOrders->sum('total');
        $prevTotalOrdersCount = $prevOrdersInPeriod->count();
        $prevValidOrdersCount = $prevValidOrders->count();

        // AOV calculations
        $aov = $validOrdersCount > 0 ? round($currentRevenue / $validOrdersCount, 2) : 0.0;
        $prevAov = $prevValidOrdersCount > 0 ? round($prevRevenue / $prevValidOrdersCount, 2) : 0.0;

        // Order status breakdown
        $statusCounts = [
            'delivered' => $ordersInPeriod->where('status', 'delivered')->count(),
            'processing' => $ordersInPeriod->where('status', 'processing')->count(),
            'shipped' => $ordersInPeriod->where('status', 'shipped')->count(),
            'pending' => $ordersInPeriod->where('status', 'pending')->count(),
            'cancelled' => $ordersInPeriod->where('status', 'cancelled')->count(),
            'refunded' => $ordersInPeriod->where('payment_status', 'refunded')->count(),
        ];

        $statusPercentages = [];
        foreach ($statusCounts as $statusKey => $count) {
            $statusPercentages[$statusKey] = $totalOrdersCount > 0
                ? round(($count / $totalOrdersCount) * 100, 1)
                : 0.0;
        }

        // Customer analytics
        $totalCustomers = $this->getCustomerQuery()->count();
        $newCustomers = $this->getCustomerQuery()
            ->whereBetween('created_at', [$start, $end])
            ->count();

        $prevNewCustomers = $this->getCustomerQuery()
            ->whereBetween('created_at', [$prevStart, $prevEnd])
            ->count();

        // Repeat customers calculation (>= 2 orders placed all-time)
        $repeatCustomerUserIds = Order::query()
            ->whereNotNull('customer_id')
            ->where('status', '!=', 'cancelled')
            ->select('customer_id')
            ->groupBy('customer_id')
            ->havingRaw('count(*) > 1')
            ->pluck('customer_id');

        $repeatCustomerEmails = Order::query()
            ->whereNull('customer_id')
            ->whereNotNull('customer_email')
            ->where('status', '!=', 'cancelled')
            ->select('customer_email')
            ->groupBy('customer_email')
            ->havingRaw('count(*) > 1')
            ->pluck('customer_email');

        $totalRepeatCustomers = $repeatCustomerUserIds->count() + $repeatCustomerEmails->count();

        // Unique ordering customers in period
        $orderingUserIds = $validOrders->whereNotNull('customer_id')->pluck('customer_id')->unique();
        $orderingGuestEmails = $validOrders->whereNull('customer_id')->whereNotNull('customer_email')->pluck('customer_email')->unique();
        $uniqueOrderingCustomersInPeriod = $orderingUserIds->count() + $orderingGuestEmails->count();

        $repeatRate = $uniqueOrderingCustomersInPeriod > 0
            ? round(($totalRepeatCustomers / max(1, $uniqueOrderingCustomersInPeriod)) * 100, 1)
            : 0.0;

        return [
            'revenue' => [
                'total' => $currentRevenue,
                'gross_sales' => $currentGross,
                'discounts' => $currentDiscounts,
                'shipping' => $currentShipping,
                'tax' => $currentTax,
                'previous_total' => $prevRevenue,
                'growth_percent' => $this->calculateGrowth($currentRevenue, $prevRevenue),
                'is_positive' => $currentRevenue >= $prevRevenue,
            ],
            'orders' => [
                'total' => $totalOrdersCount,
                'completed' => $statusCounts['delivered'],
                'processing_total' => $statusCounts['processing'] + $statusCounts['shipped'],
                'pending' => $statusCounts['pending'],
                'cancelled' => $statusCounts['cancelled'],
                'refunded' => $statusCounts['refunded'],
                'status_counts' => $statusCounts,
                'status_percentages' => $statusPercentages,
                'previous_total' => $prevTotalOrdersCount,
                'growth_percent' => $this->calculateGrowth($totalOrdersCount, $prevTotalOrdersCount),
                'is_positive' => $totalOrdersCount >= $prevTotalOrdersCount,
            ],
            'aov' => [
                'value' => $aov,
                'previous_value' => $prevAov,
                'growth_percent' => $this->calculateGrowth($aov, $prevAov),
                'is_positive' => $aov >= $prevAov,
            ],
            'customers' => [
                'total' => $totalCustomers,
                'new_in_period' => $newCustomers,
                'previous_new' => $prevNewCustomers,
                'growth_percent' => $this->calculateGrowth($newCustomers, $prevNewCustomers),
                'is_positive' => $newCustomers >= $prevNewCustomers,
                'repeat_count' => $totalRepeatCustomers,
                'repeat_rate' => min(100.0, $repeatRate),
                'active_ordering' => $uniqueOrderingCustomersInPeriod,
            ],
        ];
    }

    /**
     * Compute daily / period trends for charts and visual progression.
     *
     * @return array{
     *     points: array<int, array{date: string, label: string, revenue: float, orders: int, aov: float}>,
     *     max_revenue: float,
     *     max_orders: int,
     *     total_revenue: float,
     *     total_orders: int
     * }
     */
    public function getTrends(CarbonInterface $start, CarbonInterface $end): array
    {
        $orders = Order::query()
            ->whereBetween('created_at', [$start, $end])
            ->get();

        $daysCount = $start->diffInDays($end) + 1;
        $isLargeSpan = $daysCount > 62;

        $points = [];
        $maxRevenue = 0.0;
        $maxOrders = 0;
        $totalRevenue = 0.0;
        $totalOrders = 0;

        if (! $isLargeSpan) {
            // Daily points
            $cursor = $start->copy();
            while ($cursor->lte($end)) {
                $dateKey = $cursor->format('Y-m-d');
                $label = $cursor->format('d M');

                $dayOrders = $orders->filter(fn ($o) => $o->created_at->format('Y-m-d') === $dateKey);
                $dayValid = $dayOrders->where('status', '!=', 'cancelled');

                $rev = (float) $dayValid->sum('total');
                $count = $dayOrders->count();
                $aov = $dayValid->count() > 0 ? round($rev / $dayValid->count(), 2) : 0.0;

                $maxRevenue = max($maxRevenue, $rev);
                $maxOrders = max($maxOrders, $count);
                $totalRevenue += $rev;
                $totalOrders += $count;

                $points[] = [
                    'date' => $dateKey,
                    'label' => $label,
                    'revenue' => $rev,
                    'orders' => $count,
                    'aov' => $aov,
                ];

                $cursor = $cursor->addDay();
            }
        } else {
            // Group by month
            $cursor = $start->copy()->startOfMonth();
            while ($cursor->lte($end)) {
                $monthKey = $cursor->format('Y-m');
                $label = $cursor->format('M Y');

                $monthOrders = $orders->filter(fn ($o) => $o->created_at->format('Y-m') === $monthKey);
                $monthValid = $monthOrders->where('status', '!=', 'cancelled');

                $rev = (float) $monthValid->sum('total');
                $count = $monthOrders->count();
                $aov = $monthValid->count() > 0 ? round($rev / $monthValid->count(), 2) : 0.0;

                $maxRevenue = max($maxRevenue, $rev);
                $maxOrders = max($maxOrders, $count);
                $totalRevenue += $rev;
                $totalOrders += $count;

                $points[] = [
                    'date' => $monthKey,
                    'label' => $label,
                    'revenue' => $rev,
                    'orders' => $count,
                    'aov' => $aov,
                ];

                $cursor = $cursor->addMonth();
            }
        }

        return [
            'points' => $points,
            'max_revenue' => $maxRevenue > 0 ? $maxRevenue : 1.0,
            'max_orders' => $maxOrders > 0 ? $maxOrders : 1,
            'total_revenue' => $totalRevenue,
            'total_orders' => $totalOrders,
        ];
    }

    /**
     * Retrieve top selling products ranked by revenue and units sold.
     */
    public function getTopProducts(CarbonInterface $start, CarbonInterface $end, int $limit = 5): Collection
    {
        $items = OrderItem::query()
            ->whereHas('order', function ($q) use ($start, $end) {
                $q->whereBetween('created_at', [$start, $end])
                    ->where('status', '!=', 'cancelled');
            })
            ->with(['product.images'])
            ->get();

        return $items
            ->groupBy('product_id')
            ->map(function (Collection $group) {
                $first = $group->first();
                $product = $first->product;

                return [
                    'product_id' => $first->product_id,
                    'product_name' => $product?->name ?? $first->product_name,
                    'sku' => $first->sku ?? $product?->sku ?? 'N/A',
                    'price' => (float) ($product?->price ?? $first->unit_price),
                    'units_sold' => (int) $group->sum('quantity'),
                    'total_sales' => (float) $group->sum('total'),
                    'image' => $product?->images?->first()?->path,
                ];
            })
            ->sortByDesc('total_sales')
            ->take($limit)
            ->values();
    }

    /**
     * Retrieve products that have low stock (<= threshold).
     */
    public function getLowStockItems(int $threshold = 10, int $limit = 6): Collection
    {
        return Inventory::query()
            ->with(['variant.product.images', 'variant.optionValues'])
            ->where('quantity', '<=', $threshold)
            ->orderBy('quantity', 'asc')
            ->take($limit)
            ->get()
            ->map(function ($inv) use ($threshold) {
                $variant = $inv->variant;
                $product = $variant?->product;

                return [
                    'inventory_id' => $inv->id,
                    'product_name' => $product?->name ?? 'Unknown Product',
                    'variant_title' => $variant?->title ?? 'Default',
                    'sku' => $variant?->sku ?? $product?->sku ?? 'N/A',
                    'quantity' => (int) $inv->quantity,
                    'threshold' => $threshold,
                    'is_out_of_stock' => $inv->quantity <= 0,
                    'image' => $product?->images?->first()?->path,
                ];
            });
    }

    /**
     * Generate an executive performance CSV export report.
     */
    public function generateCsvReport(CarbonInterface $start, CarbonInterface $end): string
    {
        $summary = $this->getSummary($start, $end);
        $trends = $this->getTrends($start, $end);
        $topProducts = $this->getTopProducts($start, $end, 10);

        $output = fopen('php://temp', 'r+');

        // Header section
        fputcsv($output, [store_name().' — Business Performance & Revenue Report']);
        fputcsv($output, ['Generated At', now()->format('Y-m-d H:i:s T')]);
        fputcsv($output, ['Reporting Period', $start->format('Y-m-d').' to '.$end->format('Y-m-d')]);
        fputcsv($output, []);

        // Executive Summary
        fputcsv($output, ['--- EXECUTIVE SUMMARY ---']);
        fputcsv($output, ['Metric', 'Current Period', 'Prior Period', 'Growth (%)']);
        fputcsv($output, ['Total Revenue', number_format($summary['revenue']['total'], 2), number_format($summary['revenue']['previous_total'], 2), ($summary['revenue']['is_positive'] ? '+' : '').$summary['revenue']['growth_percent'].'%']);
        fputcsv($output, ['Gross Sales', number_format($summary['revenue']['gross_sales'], 2), '-', '-']);
        fputcsv($output, ['Discounts Given', number_format($summary['revenue']['discounts'], 2), '-', '-']);
        fputcsv($output, ['Shipping Collected', number_format($summary['revenue']['shipping'], 2), '-', '-']);
        fputcsv($output, ['Total Orders', $summary['orders']['total'], $summary['orders']['previous_total'], ($summary['orders']['is_positive'] ? '+' : '').$summary['orders']['growth_percent'].'%']);
        fputcsv($output, ['Average Order Value (AOV)', number_format($summary['aov']['value'], 2), number_format($summary['aov']['previous_value'], 2), ($summary['aov']['is_positive'] ? '+' : '').$summary['aov']['growth_percent'].'%']);
        fputcsv($output, ['Total Customers', $summary['customers']['total'], '-', '-']);
        fputcsv($output, ['New Customers in Period', $summary['customers']['new_in_period'], $summary['customers']['previous_new'], ($summary['customers']['is_positive'] ? '+' : '').$summary['customers']['growth_percent'].'%']);
        fputcsv($output, ['Repeat Customer Rate', $summary['customers']['repeat_rate'].'%', '-', '-']);
        fputcsv($output, []);

        // Order Status Breakdown
        fputcsv($output, ['--- ORDER STATUS BREAKDOWN ---']);
        fputcsv($output, ['Status', 'Order Count', 'Percentage (%)']);
        foreach ($summary['orders']['status_counts'] as $status => $count) {
            fputcsv($output, [ucfirst($status), $count, $summary['orders']['status_percentages'][$status].'%']);
        }
        fputcsv($output, []);

        // Top Selling Products
        fputcsv($output, ['--- TOP SELLING PRODUCTS ---']);
        fputcsv($output, ['Rank', 'Product Name', 'SKU', 'Units Sold', 'Total Revenue']);
        foreach ($topProducts as $idx => $prod) {
            fputcsv($output, [
                $idx + 1,
                $prod['product_name'],
                $prod['sku'],
                $prod['units_sold'],
                number_format($prod['total_sales'], 2),
            ]);
        }
        fputcsv($output, []);

        // Daily Breakdown
        fputcsv($output, ['--- DAILY PERFORMANCE TRENDS ---']);
        fputcsv($output, ['Date', 'Orders Count', 'Net Revenue', 'Average Order Value (AOV)']);
        foreach ($trends['points'] as $point) {
            fputcsv($output, [
                $point['date'],
                $point['orders'],
                number_format($point['revenue'], 2),
                number_format($point['aov'], 2),
            ]);
        }

        rewind($output);
        $content = stream_get_contents($output);
        fclose($output);

        return $content ?: '';
    }

    protected function calculateGrowth(float|int $current, float|int $previous): float
    {
        if ($previous > 0) {
            return round((($current - $previous) / $previous) * 100, 1);
        }

        return $current > 0 ? 100.0 : 0.0;
    }

    protected function getEarliestDate(): CarbonInterface
    {
        $earliestOrder = Order::min('created_at');
        $earliestUser = User::min('created_at');

        $dates = array_filter([$earliestOrder, $earliestUser]);

        if (empty($dates)) {
            return now()->subYear()->startOfDay();
        }

        return Carbon::parse(min($dates))->startOfDay();
    }

    protected function getCustomerQuery()
    {
        $customerRoleExists = Role::where('name', 'Customer')->exists();

        if ($customerRoleExists) {
            return User::role('Customer');
        }

        return User::query();
    }
}
