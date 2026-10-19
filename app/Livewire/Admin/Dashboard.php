<?php

namespace App\Livewire\Admin;

use App\Models\Inventory;
use App\Models\Order;
use App\Services\AnalyticsService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Dashboard extends Component
{
    public string $statusFilter = 'all';

    public function setStatusFilter(string $status): void
    {
        $allowed = ['all', 'pending', 'processing', 'shipped', 'delivered', 'cancelled'];
        if (in_array($status, $allowed, true)) {
            $this->statusFilter = $status;
        }
    }

    public function render(AnalyticsService $analyticsService)
    {
        $today = today();

        // 1. Total orders and today's orders
        $totalOrdersCount = Order::count();
        $todayOrdersCount = Order::whereDate('created_at', $today)->count();

        // 2. Today's sales and pending payments
        $todaySales = (float) Order::query()
            ->whereDate('created_at', $today)
            ->where('status', '!=', 'cancelled')
            ->sum('total');

        $pendingPaymentsQuery = Order::query()
            ->where('payment_status', 'pending')
            ->where('status', '!=', 'cancelled');

        $pendingPaymentsTotal = (float) $pendingPaymentsQuery->sum('total');
        $pendingPaymentsCount = $pendingPaymentsQuery->count();

        // 3. Pending orders requiring action
        $pendingOrdersCount = Order::where('status', 'pending')->count();
        $processingOrdersCount = Order::where('status', 'processing')->count();

        // 4. Low-stock product alerts
        $lowStockCount = Inventory::where('quantity', '<=', 10)->where('quantity', '>', 0)->count();
        $outOfStockCount = Inventory::where('quantity', '<=', 0)->count();
        $totalLowStockAlerts = Inventory::where('quantity', '<=', 10)->count();
        $lowStockItems = $analyticsService->getLowStockItems(10, 6);

        // 5. Recent orders table
        $recentOrdersQuery = Order::query()
            ->with(['customer', 'items'])
            ->latest();

        if ($this->statusFilter !== 'all') {
            $recentOrdersQuery->where('status', $this->statusFilter);
        }

        $recentOrders = $recentOrdersQuery->take(10)->get();

        return view('livewire.admin.dashboard', [
            'totalOrdersCount' => $totalOrdersCount,
            'todayOrdersCount' => $todayOrdersCount,
            'todaySales' => $todaySales,
            'pendingPaymentsTotal' => $pendingPaymentsTotal,
            'pendingPaymentsCount' => $pendingPaymentsCount,
            'pendingOrdersCount' => $pendingOrdersCount,
            'processingOrdersCount' => $processingOrdersCount,
            'lowStockCount' => $lowStockCount,
            'outOfStockCount' => $outOfStockCount,
            'totalLowStockAlerts' => $totalLowStockAlerts,
            'lowStockItems' => $lowStockItems,
            'recentOrders' => $recentOrders,
        ]);
    }
}
