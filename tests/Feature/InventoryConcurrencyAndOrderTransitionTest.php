<?php

use App\Livewire\Admin\Orders\Show as AdminOrderShow;
use App\Livewire\Storefront\Checkout;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\CartService;
use App\Services\InventoryService;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => 'Customer', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
});

function createStockProduct(string $name = 'Limited Edition Watch', float $price = 2500.00, int $stock = 1): array
{
    $product = Product::create([
        'name' => $name,
        'slug' => Str::slug($name).'-'.Str::random(5),
        'sku' => 'SKU-'.strtoupper(Str::random(6)),
        'price' => $price,
        'is_active' => true,
    ]);

    $variant = ProductVariant::create([
        'product_id' => $product->id,
        'title' => 'Default Variant',
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

test('checkout rechecks stock and halts order placement if inventory was depleted after adding to cart', function () {
    [, $variant, $inventory] = createStockProduct('Fast Selling Jacket', 1200.00, 2);

    $cartService = app(CartService::class);
    $cartService->add($variant, 2);

    $component = Livewire::test(Checkout::class)
        ->set('paymentMethod', 'cod')
        ->set('firstName', 'Bruce')
        ->set('lastName', 'Wayne')
        ->set('email', 'bruce@wayne.test')
        ->set('phone', '9876543210')
        ->set('address', '1007 Mountain Drive')
        ->set('city', 'Gotham')
        ->set('state', 'New Jersey')
        ->set('postalCode', '10001')
        ->set('country', 'India');

    // Simulate inventory being bought out right before clicking placeOrder
    $inventory->update(['quantity' => 1]);

    $component->call('placeOrder')
        ->assertRedirect(route('storefront.cart'));

    // Verify no order was created
    expect(Order::where('customer_email', 'bruce@wayne.test')->exists())->toBeFalse();
});

test('pessimistic row locking prevents overselling when two customers place orders for the last item simultaneously', function () {
    [, $variant, $inventory] = createStockProduct('Single Stock Smartphone', 15000.00, 1);

    $orderService = app(OrderService::class);

    $cartA = [
        $variant->id => [
            'variant_id' => $variant->id,
            'quantity' => 1,
        ],
    ];

    $customerA = [
        'first_name' => 'Customer',
        'last_name' => 'One',
        'email' => 'customer1@example.com',
        'phone' => '9999900001',
    ];

    $cartB = [
        $variant->id => [
            'variant_id' => $variant->id,
            'quantity' => 1,
        ],
    ];

    $customerB = [
        'first_name' => 'Customer',
        'last_name' => 'Two',
        'email' => 'customer2@example.com',
        'phone' => '9999900002',
    ];

    $address = [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'address' => '123 Test St',
        'city' => 'Metropolis',
        'state' => 'NY',
        'postal_code' => '10001',
        'country' => 'India',
        'phone' => '9999900001',
    ];

    // Customer A completes checkout
    $orderA = $orderService->createOrder($customerA, $address, $address, $cartA, ['method' => 'cod', 'status' => 'pending']);
    expect($orderA)->not->toBeNull();

    $inventory->refresh();
    expect($inventory->quantity)->toBe(0);
    expect($inventory->reserved_quantity)->toBe(1);

    // Customer B tries to checkout the same item simultaneously -> Must throw Insufficient stock exception
    expect(function () use ($orderService, $customerB, $address, $cartB) {
        $orderService->createOrder($customerB, $address, $address, $cartB, ['method' => 'cod', 'status' => 'pending']);
    })->toThrow(RuntimeException::class, 'Insufficient stock');

    // Verify stock never drops below 0 and Customer B's order was not created
    $inventory->refresh();
    expect($inventory->quantity)->toBe(0);
    expect(Order::where('customer_email', 'customer2@example.com')->exists())->toBeFalse();
});

test('inventory lifecycle correctly handles reservation, deduction, release, and idempotent restoration', function () {
    [, $variant, $inventory] = createStockProduct('Lifecycle Camera', 35000.00, 5);

    $orderService = app(OrderService::class);
    $cart = [
        $variant->id => [
            'variant_id' => $variant->id,
            'quantity' => 2,
        ],
    ];

    $customer = [
        'first_name' => 'Clark',
        'last_name' => 'Kent',
        'email' => 'clark@dailyplanet.test',
        'phone' => '9888877777',
    ];

    $address = [
        'first_name' => 'Clark',
        'last_name' => 'Kent',
        'address' => '344 Clinton St',
        'city' => 'Metropolis',
        'state' => 'NY',
        'postal_code' => '10001',
        'country' => 'India',
        'phone' => '9888877777',
    ];

    // 1. Order Placed -> Stock reserved and deducted from available
    $order = $orderService->createOrder($customer, $address, $address, $cart, ['method' => 'card', 'status' => 'paid']);

    $inventory->refresh();
    expect($inventory->quantity)->toBe(3); // 5 - 2
    expect($inventory->reserved_quantity)->toBe(2);

    // 2. Order Processing then Shipped -> Reserved stock is released from warehouse
    $order->transitionTo('processing', 'Payment verified and packing order');
    $order->transitionTo('shipped', 'Package picked up by courier');
    $inventory->refresh();
    expect($inventory->quantity)->toBe(3);
    expect($inventory->reserved_quantity)->toBe(0);

    // 3. Order Refunded / Cancelled -> Restores inventory to available stock
    $inventoryService = app(InventoryService::class);
    $restored = $inventoryService->restoreStock($order, 'return');
    expect($restored)->toBeTrue();

    $inventory->refresh();
    expect($inventory->quantity)->toBe(5); // 3 + 2 restored
    expect($order->fresh()->inventory_restored_at)->not->toBeNull();

    // 4. Duplicate restoration attempt -> Must be idempotent (returns false, does not increment again)
    $secondRestore = $inventoryService->restoreStock($order, 'return');
    expect($secondRestore)->toBeFalse();

    $inventory->refresh();
    expect($inventory->quantity)->toBe(5); // Still exactly 5!
});

test('order transitions are strictly validated and record audit trail of who changed it', function () {
    $admin = User::factory()->create(['name' => 'Admin Boss']);
    [, $variant] = createStockProduct('Audit Test Item', 500.00, 10);

    $order = Order::create([
        'order_number' => 'ORD-AUDIT-001',
        'status' => 'pending',
        'payment_status' => 'paid',
        'subtotal' => 500.00,
        'total' => 500.00,
        'shipping_address' => ['first_name' => 'Peter'],
        'billing_address' => ['first_name' => 'Peter'],
    ]);

    // 1. Invalid jump: pending -> delivered should fail
    expect($order->canTransitionTo('delivered'))->toBeFalse();
    expect(function () use ($order, $admin) {
        $order->transitionTo('delivered', 'Invalid jump attempt', $admin);
    })->toThrow(DomainException::class);

    // 2. Valid transition: pending -> processing
    expect($order->canTransitionTo('processing'))->toBeTrue();
    $order->transitionTo('processing', 'Approved by admin', $admin);

    expect($order->fresh()->status)->toBe('processing');

    // Verify audit record was created with admin name
    $latestAudit = OrderStatusHistory::where('order_id', $order->id)->latest('id')->first();
    expect($latestAudit)->not->toBeNull();
    expect($latestAudit->from_status)->toBe('pending');
    expect($latestAudit->to_status)->toBe('processing');
    expect($latestAudit->user_id)->toBe($admin->id);
    expect($latestAudit->user_name)->toBe('Admin Boss');
    expect($latestAudit->reason)->toBe('Approved by admin');

    // 3. Admin Livewire UI rejects invalid transition
    Livewire::actingAs($admin)
        ->test(AdminOrderShow::class, ['orderNumber' => $order->order_number])
        ->set('status', 'delivered')
        ->call('updateStatus')
        ->assertHasErrors(['status']);
});

test('order cancellations and refunds preserve exact financial totals without corruption', function () {
    [, $variant] = createStockProduct('Financial Total Item', 1000.00, 10);

    $order = Order::create([
        'order_number' => 'ORD-FINANCE-001',
        'status' => 'processing',
        'payment_status' => 'paid',
        'subtotal' => 2000.00,
        'discount_amount' => 200.00,
        'shipping_amount' => 50.00,
        'tax_amount' => 180.00,
        'total' => 2030.00,
        'shipping_address' => ['first_name' => 'Diana'],
        'billing_address' => ['first_name' => 'Diana'],
    ]);

    $order->items()->create([
        'product_id' => $variant->product_id,
        'product_variant_id' => $variant->id,
        'product_name' => $variant->product->name,
        'variant_name' => $variant->title,
        'sku' => $variant->sku,
        'quantity' => 2,
        'unit_price' => 1000.00,
        'total' => 2000.00,
    ]);

    // Refund order
    $order->markAsRefunded('RFND-TEST-REF');
    $order->refresh();

    // Verify financial totals are completely preserved
    expect((float) $order->subtotal)->toBe(2000.00);
    expect((float) $order->discount_amount)->toBe(200.00);
    expect((float) $order->shipping_amount)->toBe(50.00);
    expect((float) $order->tax_amount)->toBe(180.00);
    expect((float) $order->total)->toBe(2030.00);
    expect($order->status)->toBe('cancelled');
    expect($order->payment_status)->toBe('refunded');
});
