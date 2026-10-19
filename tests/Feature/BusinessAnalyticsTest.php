<?php

use App\Livewire\Admin\Analytics\Index as AnalyticsIndex;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\AnalyticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => 'Customer', 'guard_name' => 'web']);
    $this->admin = User::factory()->create();
    $this->actingAs($this->admin);
});

function createAnalyticsOrder(array $attributes = []): Order
{
    $createdAt = $attributes['created_at'] ?? null;
    unset($attributes['created_at']);

    $order = Order::create(array_merge([
        'order_number' => 'ORD-'.strtoupper(Str::random(8)),
        'subtotal' => 100,
        'tax_amount' => 10,
        'shipping_amount' => 15,
        'discount_amount' => 5,
        'total' => 120,
        'status' => 'delivered',
        'payment_status' => 'paid',
        'payment_method' => 'card',
        'customer_email' => 'customer@example.com',
        'shipping_address' => [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'address' => '456 Elm St',
            'city' => 'Anytown',
            'state' => 'NY',
            'postal_code' => '10001',
            'country' => 'India',
            'email' => 'customer@example.com',
        ],
        'billing_address' => [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'address' => '456 Elm St',
            'city' => 'Anytown',
            'state' => 'NY',
            'postal_code' => '10001',
            'country' => 'India',
        ],
    ], $attributes));

    if ($createdAt) {
        Order::where('id', $order->id)->update([
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);
        $order->refresh();
    }

    return $order;
}

function createProductWithInventory(string $name, float $price, int $stock): array
{
    $product = Product::create([
        'name' => $name,
        'slug' => Str::slug($name).'-'.Str::random(4),
        'sku' => 'SKU-'.strtoupper(Str::random(5)),
        'price' => $price,
        'is_active' => true,
    ]);

    $variant = ProductVariant::create([
        'product_id' => $product->id,
        'title' => 'Default',
        'sku' => $product->sku.'-DEF',
        'price' => $price,
        'is_active' => true,
    ]);

    $inventory = Inventory::create([
        'product_variant_id' => $variant->id,
        'quantity' => $stock,
        'reserved_quantity' => 0,
    ]);

    return [$product, $variant, $inventory];
}

/*
|--------------------------------------------------------------------------
| 1. Route Access & Livewire Component Rendering
|--------------------------------------------------------------------------
*/

test('guests are redirected away from analytics route', function () {
    auth()->logout();

    $this->get(route('admin.analytics.index'))
        ->assertRedirect(route('login'));
});

test('admin can access analytics route with business performance view', function () {
    $this->get(route('admin.analytics.index'))
        ->assertOk()
        ->assertSee('Analytics — Business performance', false);
});

test('analytics component initializes with last 30 days preset and renders metrics', function () {
    createAnalyticsOrder(['total' => 250, 'created_at' => now()->subDays(5)]);

    Livewire::test(AnalyticsIndex::class)
        ->assertSet('datePreset', 'last_30_days')
        ->assertSet('activeChartMetric', 'revenue')
        ->assertSee('Total Net Revenue')
        ->assertSee('Total Orders')
        ->assertSee('Avg. Order Value (AOV)')
        ->assertSee('New Customers');
});

/*
|--------------------------------------------------------------------------
| 2. Revenue Analytics & Period Comparisons
|--------------------------------------------------------------------------
*/

test('analytics service calculates revenue metrics accurately excluding cancelled orders', function () {
    $service = app(AnalyticsService::class);
    $start = now()->subDays(10)->startOfDay();
    $end = now()->endOfDay();

    // Valid delivered order
    createAnalyticsOrder([
        'subtotal' => 200,
        'tax_amount' => 20,
        'shipping_amount' => 15,
        'discount_amount' => 10,
        'total' => 225,
        'status' => 'delivered',
        'created_at' => now()->subDays(3),
    ]);

    // Valid processing order
    createAnalyticsOrder([
        'subtotal' => 100,
        'tax_amount' => 10,
        'shipping_amount' => 10,
        'discount_amount' => 0,
        'total' => 120,
        'status' => 'processing',
        'created_at' => now()->subDays(2),
    ]);

    // Cancelled order (should be excluded from revenue)
    createAnalyticsOrder([
        'subtotal' => 500,
        'total' => 500,
        'status' => 'cancelled',
        'created_at' => now()->subDays(1),
    ]);

    $summary = $service->getSummary($start, $end);

    // Total net revenue should be 225 + 120 = 345
    expect($summary['revenue']['total'])->toEqual(345.0)
        ->and($summary['revenue']['discounts'])->toEqual(10.0)
        ->and($summary['revenue']['shipping'])->toEqual(25.0)
        ->and($summary['orders']['total'])->toEqual(3)
        ->and($summary['orders']['completed'])->toEqual(1)
        ->and($summary['orders']['cancelled'])->toEqual(1)
        ->and($summary['aov']['value'])->toEqual(172.5); // 345 / 2 valid orders
});

test('analytics calculates positive and negative growth against prior period', function () {
    $service = app(AnalyticsService::class);
    $start = now()->subDays(6)->startOfDay();
    $end = now()->endOfDay(); // 7-day period

    [$prevStart, $prevEnd] = $service->getComparisonPeriod($start, $end);

    // Current period order: $300
    createAnalyticsOrder([
        'total' => 300,
        'created_at' => $start->copy()->addDay(),
    ]);

    // Prior period order: $150
    createAnalyticsOrder([
        'total' => 150,
        'created_at' => $prevStart->copy()->addDay(),
    ]);

    $summary = $service->getSummary($start, $end);

    // Revenue doubled from 150 to 300 => +100% growth
    expect($summary['revenue']['total'])->toEqual(300.0)
        ->and($summary['revenue']['previous_total'])->toEqual(150.0)
        ->and($summary['revenue']['growth_percent'])->toEqual(100.0)
        ->and($summary['revenue']['is_positive'])->toBeTrue();
});

/*
|--------------------------------------------------------------------------
| 3. Order Status Breakdown
|--------------------------------------------------------------------------
*/

test('analytics accurately breaks down orders by status and calculates percentages', function () {
    $service = app(AnalyticsService::class);
    $start = now()->subDays(5)->startOfDay();
    $end = now()->endOfDay();

    createAnalyticsOrder(['status' => 'delivered', 'payment_status' => 'paid', 'created_at' => now()->subDays(2)]);
    createAnalyticsOrder(['status' => 'delivered', 'payment_status' => 'paid', 'created_at' => now()->subDays(2)]);
    createAnalyticsOrder(['status' => 'processing', 'payment_status' => 'paid', 'created_at' => now()->subDays(1)]);
    createAnalyticsOrder(['status' => 'shipped', 'payment_status' => 'paid', 'created_at' => now()->subDays(1)]);
    createAnalyticsOrder(['status' => 'pending', 'payment_status' => 'pending', 'created_at' => now()->subDays(1)]);
    createAnalyticsOrder(['status' => 'cancelled', 'payment_status' => 'refunded', 'created_at' => now()->subDays(1)]);

    $summary = $service->getSummary($start, $end);

    expect($summary['orders']['total'])->toEqual(6)
        ->and($summary['orders']['status_counts']['delivered'])->toEqual(2)
        ->and($summary['orders']['status_counts']['processing'])->toEqual(1)
        ->and($summary['orders']['status_counts']['shipped'])->toEqual(1)
        ->and($summary['orders']['status_counts']['pending'])->toEqual(1)
        ->and($summary['orders']['status_counts']['cancelled'])->toEqual(1)
        ->and($summary['orders']['status_counts']['refunded'])->toEqual(1);

    // Delivered % = (2 / 6) * 100 = 33.3%
    expect($summary['orders']['status_percentages']['delivered'])->toEqual(33.3);
});

/*
|--------------------------------------------------------------------------
| 4. Customer Analytics (New & Repeat Customers)
|--------------------------------------------------------------------------
*/

test('analytics identifies new registered customers and calculates repeat customer retention', function () {
    $service = app(AnalyticsService::class);
    $start = now()->subDays(7)->startOfDay();
    $end = now()->endOfDay();

    // 1 customer created 15 days ago (not new in period)
    $oldCustomer = User::factory()->create(['created_at' => now()->subDays(15)]);
    $oldCustomer->assignRole('Customer');

    // 2 customers created 3 days ago (new in period)
    $newCustomer1 = User::factory()->create(['created_at' => now()->subDays(3)]);
    $newCustomer1->assignRole('Customer');
    $newCustomer2 = User::factory()->create(['created_at' => now()->subDays(2)]);
    $newCustomer2->assignRole('Customer');

    // Customer 1 places 2 non-cancelled orders => Repeat Customer
    createAnalyticsOrder(['customer_id' => $newCustomer1->id, 'created_at' => now()->subDays(3)]);
    createAnalyticsOrder(['customer_id' => $newCustomer1->id, 'created_at' => now()->subDays(1)]);

    // Customer 2 places only 1 order => Single Order
    createAnalyticsOrder(['customer_id' => $newCustomer2->id, 'created_at' => now()->subDays(2)]);

    $summary = $service->getSummary($start, $end);

    expect($summary['customers']['total'])->toBeGreaterThanOrEqual(3)
        ->and($summary['customers']['new_in_period'])->toEqual(2)
        ->and($summary['customers']['repeat_count'])->toEqual(1)
        ->and($summary['customers']['repeat_rate'])->toBeGreaterThan(0.0);
});

/*
|--------------------------------------------------------------------------
| 5. Top Selling Products & Low Stock Alerts
|--------------------------------------------------------------------------
*/

test('analytics aggregates top selling products by units and revenue', function () {
    $service = app(AnalyticsService::class);
    $start = now()->subDays(10)->startOfDay();
    $end = now()->endOfDay();

    [$prodA, $varA] = createProductWithInventory('Product Alpha', 50.0, 100);
    [$prodB, $varB] = createProductWithInventory('Product Beta', 100.0, 50);

    $order1 = createAnalyticsOrder(['created_at' => now()->subDays(3)]);
    OrderItem::create([
        'order_id' => $order1->id,
        'product_id' => $prodA->id,
        'product_variant_id' => $varA->id,
        'product_name' => 'Product Alpha',
        'sku' => $prodA->sku,
        'quantity' => 4,
        'unit_price' => 50.0,
        'total' => 200.0,
    ]);

    $order2 = createAnalyticsOrder(['created_at' => now()->subDays(2)]);
    OrderItem::create([
        'order_id' => $order2->id,
        'product_id' => $prodB->id,
        'product_variant_id' => $varB->id,
        'product_name' => 'Product Beta',
        'sku' => $prodB->sku,
        'quantity' => 10,
        'unit_price' => 100.0,
        'total' => 1000.0,
    ]);

    $topProducts = $service->getTopProducts($start, $end, 5);

    expect($topProducts)->toHaveCount(2)
        ->and($topProducts->first()['product_name'])->toEqual('Product Beta')
        ->and($topProducts->first()['units_sold'])->toEqual(10)
        ->and($topProducts->first()['total_sales'])->toEqual(1000.0)
        ->and($topProducts->last()['product_name'])->toEqual('Product Alpha')
        ->and($topProducts->last()['units_sold'])->toEqual(4);
});

test('analytics retrieves inventory items below low stock threshold and flags out of stock items', function () {
    $service = app(AnalyticsService::class);

    // Ample stock (15) - should NOT show
    createProductWithInventory('Adequate Product', 30.0, 15);

    // Low stock (5) - should show
    createProductWithInventory('Low Stock Product', 40.0, 5);

    // Out of stock (0) - should show and have is_out_of_stock = true
    createProductWithInventory('Empty Stock Product', 60.0, 0);

    $lowStock = $service->getLowStockItems(10, 5);

    expect($lowStock)->toHaveCount(2)
        ->and($lowStock->first()['quantity'])->toEqual(0)
        ->and($lowStock->first()['is_out_of_stock'])->toBeTrue()
        ->and($lowStock->last()['quantity'])->toEqual(5)
        ->and($lowStock->last()['is_out_of_stock'])->toBeFalse();
});

/*
|--------------------------------------------------------------------------
| 6. Preset & Custom Date Filtering in Livewire Component
|--------------------------------------------------------------------------
*/

test('analytics mounts with dateFrom and dateTo prefilled and syncs with presets', function () {
    Livewire::test(AnalyticsIndex::class)
        ->assertSet('datePreset', 'last_30_days')
        ->assertNotSet('dateFrom', '')
        ->assertNotSet('dateTo', '')
        ->call('setPreset', 'today')
        ->assertSet('dateFrom', now()->format('Y-m-d'))
        ->assertSet('dateTo', now()->format('Y-m-d'));
});

test('setting custom date range automatically switches datePreset to custom and filters data', function () {
    // Order inside custom window (2026-05-10)
    createAnalyticsOrder([
        'total' => 200,
        'created_at' => Carbon::parse('2026-05-10 12:00:00'),
    ]);

    // Order outside custom window (2026-04-10)
    createAnalyticsOrder([
        'total' => 500,
        'created_at' => Carbon::parse('2026-04-10 12:00:00'),
    ]);

    Livewire::test(AnalyticsIndex::class)
        ->set('dateFrom', '2026-05-01')
        ->assertSet('datePreset', 'custom')
        ->set('dateTo', '2026-05-31')
        ->assertSet('datePreset', 'custom')
        ->assertViewHas('start', fn ($start) => $start->format('Y-m-d') === '2026-05-01')
        ->assertViewHas('end', fn ($end) => $end->format('Y-m-d') === '2026-05-31')
        ->assertViewHas('summary', fn ($summary) => $summary['revenue']['total'] === 200.0);
});

test('date pickers auto-adjust if dateFrom is set after dateTo', function () {
    Livewire::test(AnalyticsIndex::class)
        ->set('dateTo', '2026-05-10')
        ->set('dateFrom', '2026-05-20')
        ->assertSet('dateTo', '2026-05-20');
});

test('user can clear custom date range and reset to default 30 days', function () {
    Livewire::test(AnalyticsIndex::class)
        ->set('dateFrom', '2026-01-01')
        ->set('dateTo', '2026-01-31')
        ->assertSet('datePreset', 'custom')
        ->call('clearCustomDates')
        ->assertSet('datePreset', 'last_30_days')
        ->assertSet('dateTo', now()->format('Y-m-d'));
});

test('user can toggle active chart metric between revenue and orders', function () {
    Livewire::test(AnalyticsIndex::class)
        ->assertSet('activeChartMetric', 'revenue')
        ->call('setChartMetric', 'orders')
        ->assertSet('activeChartMetric', 'orders')
        ->call('setChartMetric', 'revenue')
        ->assertSet('activeChartMetric', 'revenue')
        ->call('setChartMetric', 'invalid_metric')
        ->assertSet('activeChartMetric', 'revenue'); // unallowed metric ignored
});

/*
|--------------------------------------------------------------------------
| 7. CSV Performance Report Export
|--------------------------------------------------------------------------
*/

test('admin can export streamed CSV performance report', function () {
    createAnalyticsOrder([
        'subtotal' => 150,
        'tax_amount' => 15,
        'shipping_amount' => 10,
        'total' => 175,
        'status' => 'delivered',
        'created_at' => now()->subDays(2),
    ]);

    $component = Livewire::test(AnalyticsIndex::class);
    $response = $component->call('exportCsv');

    expect($response->effects)->toBeArray();

    // Verify service CSV generation directly
    $service = app(AnalyticsService::class);
    [$start, $end] = $service->resolveDateRange('last_30_days');
    $csv = $service->generateCsvReport($start, $end);

    expect($csv)->toContain('Business Performance & Revenue Report')
        ->and($csv)->toContain('--- EXECUTIVE SUMMARY ---')
        ->and($csv)->toContain('--- ORDER STATUS BREAKDOWN ---')
        ->and($csv)->toContain('--- TOP SELLING PRODUCTS ---')
        ->and($csv)->toContain('--- DAILY PERFORMANCE TRENDS ---')
        ->and($csv)->toContain('Total Revenue')
        ->and($csv)->toContain('Average Order Value (AOV)');
});
