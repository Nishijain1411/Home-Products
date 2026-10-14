<?php

use App\Livewire\Admin\Orders\Index as AdminOrderIndex;
use App\Livewire\Admin\Orders\Show as AdminOrderShow;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => 'Customer', 'guard_name' => 'web']);
});

function createTestOrder(array $attributes = []): Order
{
    $createdAt = $attributes['created_at'] ?? null;
    unset($attributes['created_at']);

    $order = Order::create(array_merge([
        'order_number' => 'ORD-'.strtoupper(Str::random(8)),
        'subtotal' => 100,
        'tax_amount' => 10,
        'shipping_amount' => 15,
        'total' => 125,
        'status' => 'pending',
        'payment_status' => 'paid',
        'payment_method' => 'card',
        'shipping_address' => [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'address' => '123 Main St',
            'city' => 'Anytown',
            'state' => 'NY',
            'postal_code' => '10001',
            'country' => 'India',
            'email' => 'john@example.com',
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
        $order->timestamps = false;
        $order->created_at = $createdAt;
        $order->saveQuietly();
    }

    return $order;
}

test('admin can view orders index and see orders regardless of customer id', function () {
    $admin = User::factory()->create();
    $customer = User::factory()->create(['name' => 'Jane Doe', 'email' => 'jane@example.com']);
    $this->actingAs($admin);

    createTestOrder([
        'order_number' => 'ORD-10001',
        'customer_id' => $customer->id,
    ]);

    createTestOrder([
        'order_number' => 'ORD-10002',
        'customer_id' => null,
        'shipping_address' => [
            'first_name' => 'Guest',
            'last_name' => 'User',
            'email' => 'guest@example.com',
            'address' => '456 Guest Rd',
            'city' => 'City',
            'state' => 'State',
            'postal_code' => '20002',
            'country' => 'India',
        ],
    ]);

    Livewire::test(AdminOrderIndex::class)
        ->assertSee('ORD-10001')
        ->assertSee('ORD-10002')
        ->assertSee('Jane Doe');
});

test('admin can filter orders by search term', function () {
    $admin = User::factory()->create();
    $customer = User::factory()->create(['name' => 'Alice Wonder', 'email' => 'alice@example.com']);
    $this->actingAs($admin);

    createTestOrder([
        'order_number' => 'ORD-SEARCH-01',
        'customer_id' => $customer->id,
    ]);

    createTestOrder([
        'order_number' => 'ORD-OTHER-02',
        'customer_id' => null,
    ]);

    Livewire::test(AdminOrderIndex::class)
        ->set('search', 'ORD-SEARCH')
        ->assertSee('ORD-SEARCH-01')
        ->assertDontSee('ORD-OTHER-02')
        ->set('search', 'alice@example.com')
        ->assertSee('ORD-SEARCH-01')
        ->assertDontSee('ORD-OTHER-02');
});

test('admin can filter orders by status and payment status', function () {
    $admin = User::factory()->create();
    $this->actingAs($admin);

    createTestOrder([
        'order_number' => 'ORD-STATUS-DELIV',
        'status' => 'delivered',
        'payment_status' => 'paid',
    ]);

    createTestOrder([
        'order_number' => 'ORD-STATUS-PEND',
        'status' => 'pending',
        'payment_status' => 'failed',
    ]);

    Livewire::test(AdminOrderIndex::class)
        ->set('status', 'delivered')
        ->assertSee('ORD-STATUS-DELIV')
        ->assertDontSee('ORD-STATUS-PEND')
        ->set('status', '')
        ->set('paymentStatus', 'failed')
        ->assertSee('ORD-STATUS-PEND')
        ->assertDontSee('ORD-STATUS-DELIV');
});

test('admin can filter orders by date range', function () {
    $admin = User::factory()->create();
    $this->actingAs($admin);

    createTestOrder([
        'order_number' => 'ORD-OLD-DATE',
        'status' => 'delivered',
        'created_at' => now()->subDays(15),
    ]);

    createTestOrder([
        'order_number' => 'ORD-NEW-DATE',
        'status' => 'processing',
        'created_at' => now(),
    ]);

    Livewire::test(AdminOrderIndex::class)
        ->assertSee('ORD-OLD-DATE')
        ->assertSee('ORD-NEW-DATE')
        // Filter by from date
        ->set('dateFrom', now()->subDays(2)->format('Y-m-d'))
        ->assertSee('ORD-NEW-DATE')
        ->assertDontSee('ORD-OLD-DATE')
        // Filter by to date
        ->set('dateFrom', '')
        ->set('dateTo', now()->subDays(5)->format('Y-m-d'))
        ->assertSee('ORD-OLD-DATE')
        ->assertDontSee('ORD-NEW-DATE');
});

test('admin can filter orders using date preset and reset filters', function () {
    $admin = User::factory()->create();
    $this->actingAs($admin);

    createTestOrder([
        'order_number' => 'ORD-YESTERDAY',
        'status' => 'delivered',
        'created_at' => now()->subDay(),
    ]);

    createTestOrder([
        'order_number' => 'ORD-TODAY',
        'status' => 'pending',
        'created_at' => now(),
    ]);

    Livewire::test(AdminOrderIndex::class)
        ->set('datePreset', 'today')
        ->assertSet('dateFrom', now()->format('Y-m-d'))
        ->assertSet('dateTo', now()->format('Y-m-d'))
        ->assertSee('ORD-TODAY')
        ->assertDontSee('ORD-YESTERDAY')
        ->call('clearFilters')
        ->assertSet('datePreset', '')
        ->assertSet('dateFrom', '')
        ->assertSet('dateTo', '')
        ->assertSee('ORD-TODAY')
        ->assertSee('ORD-YESTERDAY');
});

test('order show page displays customer other orders with date filtering', function () {
    $admin = User::factory()->create();
    $customer = User::factory()->create(['name' => 'Charlie', 'email' => 'charlie@example.com']);
    $this->actingAs($admin);

    $currentOrder = createTestOrder([
        'order_number' => 'ORD-MAIN-001',
        'customer_id' => $customer->id,
        'status' => 'processing',
        'created_at' => now(),
    ]);

    createTestOrder([
        'order_number' => 'ORD-PAST-002',
        'customer_id' => $customer->id,
        'status' => 'delivered',
        'created_at' => now()->subDays(20),
    ]);

    createTestOrder([
        'order_number' => 'ORD-RECENT-003',
        'customer_id' => $customer->id,
        'status' => 'shipped',
        'created_at' => now()->subDays(2),
    ]);

    Livewire::test(AdminOrderShow::class, ['orderNumber' => $currentOrder->order_number])
        ->assertSee('ORD-MAIN-001')
        ->assertSee('Other Orders', false)
        ->assertSee('ORD-PAST-002')
        ->assertSee('ORD-RECENT-003')
        // Filter customer history by date
        ->set('historyDateFrom', now()->subDays(5)->format('Y-m-d'))
        ->assertSee('ORD-RECENT-003')
        ->assertDontSee('ORD-PAST-002')
        ->call('clearHistoryFilters')
        ->assertSet('historyDateFrom', '')
        ->assertSet('historyDateTo', '')
        ->assertSee('ORD-PAST-002')
        ->assertSee('ORD-RECENT-003');
});
