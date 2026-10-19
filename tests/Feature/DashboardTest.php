<?php

use App\Livewire\Admin\Dashboard;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => 'Customer', 'guard_name' => 'web']);
    $this->admin = User::factory()->create();
    $this->actingAs($this->admin);
});

function createDashboardOrder(array $attributes = []): Order
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
        'status' => 'pending',
        'payment_status' => 'paid',
        'payment_method' => 'card',
        'customer_email' => 'buyer@example.com',
        'shipping_address' => [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'address' => '123 Main St',
            'city' => 'Anytown',
            'state' => 'NY',
            'postal_code' => '10001',
            'country' => 'India',
            'email' => 'buyer@example.com',
        ],
        'billing_address' => [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'address' => '123 Main St',
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

function createDashboardProductWithStock(string $name, float $price, int $quantity): array
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
        'title' => 'Standard',
        'sku' => $product->sku.'-STD',
        'price' => $price,
        'is_active' => true,
    ]);

    $inventory = Inventory::create([
        'product_variant_id' => $variant->id,
        'quantity' => $quantity,
        'reserved_quantity' => 0,
    ]);

    return [$product, $variant, $inventory];
}

test('guests are redirected to the login page', function () {
    auth()->logout();

    $response = $this->get(route('admin.dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard and see daily operations header and quick actions', function () {
    $response = $this->get(route('admin.dashboard'));

    $response->assertOk()
        ->assertSee('Dashboard — Daily operations')
        ->assertSee('Add product')
        ->assertSee(route('admin.products.create'))
        ->assertSee('View orders')
        ->assertSee(route('admin.orders.index'))
        ->assertSee('Manage inventory')
        ->assertSee(route('admin.inventory.index'));
});

test('dashboard displays total orders and today orders count accurately', function () {
    // 1 order placed yesterday
    createDashboardOrder(['created_at' => now()->subDay()]);

    // 2 orders placed today
    createDashboardOrder(['created_at' => now()]);
    createDashboardOrder(['created_at' => now()]);

    Livewire::test(Dashboard::class)
        ->assertViewHas('totalOrdersCount', 3)
        ->assertViewHas('todayOrdersCount', 2)
        ->assertSee('Total Orders')
        ->assertSee('2 today');
});

test('dashboard displays today sales and pending payments accurately', function () {
    // Today's paid order ($150)
    createDashboardOrder([
        'total' => 150,
        'status' => 'processing',
        'payment_status' => 'paid',
        'created_at' => now(),
    ]);

    // Today's pending payment order ($75)
    createDashboardOrder([
        'total' => 75,
        'status' => 'pending',
        'payment_status' => 'pending',
        'created_at' => now(),
    ]);

    // Today's cancelled order ($500) - excluded from sales
    createDashboardOrder([
        'total' => 500,
        'status' => 'cancelled',
        'payment_status' => 'failed',
        'created_at' => now(),
    ]);

    Livewire::test(Dashboard::class)
        ->assertViewHas('todaySales', 225.0) // 150 + 75
        ->assertViewHas('pendingPaymentsTotal', 75.0)
        ->assertViewHas('pendingPaymentsCount', 1)
        ->assertSee('Pending Payments')
        ->assertSee("Today's Sales", false);
});

test('dashboard highlights pending orders requiring action', function () {
    createDashboardOrder(['status' => 'pending']);
    createDashboardOrder(['status' => 'pending']);
    createDashboardOrder(['status' => 'delivered']);

    Livewire::test(Dashboard::class)
        ->assertViewHas('pendingOrdersCount', 2)
        ->assertSee('Action Required')
        ->assertSee('Pending orders');
});

test('dashboard displays low-stock product alerts and out of stock flags', function () {
    // Low stock (4 units)
    createDashboardProductWithStock('Mechanical Keyboard', 99.0, 4);

    // Out of stock (0 units)
    createDashboardProductWithStock('Wireless Mouse', 49.0, 0);

    // Healthy stock (50 units)
    createDashboardProductWithStock('USB-C Hub', 29.0, 50);

    Livewire::test(Dashboard::class)
        ->assertViewHas('totalLowStockAlerts', 2)
        ->assertViewHas('outOfStockCount', 1)
        ->assertViewHas('lowStockCount', 1)
        ->assertSee('Low-Stock Alerts')
        ->assertSee('Mechanical Keyboard')
        ->assertSee('Wireless Mouse')
        ->assertDontSee('USB-C Hub')
        ->assertSee('0 in stock')
        ->assertSee('4 left');
});

test('dashboard displays recent orders table with customer details and links', function () {
    $order = createDashboardOrder([
        'order_number' => 'ORD-SPECIAL1',
        'total' => 350.0,
        'status' => 'processing',
        'payment_status' => 'paid',
        'shipping_address' => [
            'first_name' => 'Alice',
            'last_name' => 'Smith',
            'address' => '789 Pine Rd',
            'city' => 'Metropolis',
            'state' => 'IL',
            'postal_code' => '60601',
            'country' => 'India',
            'email' => 'alice@example.com',
        ],
    ]);

    Livewire::test(Dashboard::class)
        ->assertSee('Recent Orders')
        ->assertSee('ORD-SPECIAL1')
        ->assertSee('Alice Smith')
        ->assertSee(route('admin.orders.show', 'ORD-SPECIAL1'));
});

test('dashboard allows filtering recent orders by fulfillment status', function () {
    $pendingOrder = createDashboardOrder(['order_number' => 'ORD-PENDING1', 'status' => 'pending']);
    $deliveredOrder = createDashboardOrder(['order_number' => 'ORD-DELIV1', 'status' => 'delivered']);

    Livewire::test(Dashboard::class)
        ->assertSee('ORD-PENDING1')
        ->assertSee('ORD-DELIV1')
        ->call('setStatusFilter', 'pending')
        ->assertSet('statusFilter', 'pending')
        ->assertSee('ORD-PENDING1')
        ->assertDontSee('ORD-DELIV1')
        ->call('setStatusFilter', 'delivered')
        ->assertSet('statusFilter', 'delivered')
        ->assertSee('ORD-DELIV1')
        ->assertDontSee('ORD-PENDING1');
});
