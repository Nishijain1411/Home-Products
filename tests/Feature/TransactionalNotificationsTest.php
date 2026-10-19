<?php

use App\Mail\OrderCancelledMail;
use App\Mail\OrderConfirmationMail;
use App\Mail\OrderDeliveredMail;
use App\Mail\OrderRefundedMail;
use App\Mail\OrderShippedMail;
use App\Mail\PaymentConfirmedMail;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderNotification;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\OrderNotificationService;
use App\Services\OrderService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => 'Customer', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
});

function createNotificationTestProduct(string $name = 'Smart Watch', float $price = 5000.00, int $stock = 10): array
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
        'title' => 'Standard',
        'sku' => $product->sku.'-STD',
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

test('all transactional mailables implement ShouldQueue and define transient retry backoff', function () {
    $mailables = [
        OrderConfirmationMail::class,
        PaymentConfirmedMail::class,
        OrderShippedMail::class,
        OrderDeliveredMail::class,
        OrderCancelledMail::class,
        OrderRefundedMail::class,
    ];

    $order = new Order(['order_number' => 'ORD-QUEUE-TEST', 'customer_first_name' => 'Test', 'total' => 100]);

    foreach ($mailables as $class) {
        $reflection = new ReflectionClass($class);
        expect($reflection->implementsInterface(ShouldQueue::class))->toBeTrue("{$class} must implement ShouldQueue");

        $instance = new $class($order, 'Custom Commerce');
        expect($instance->tries)->toBeGreaterThanOrEqual(3, "{$class} must have at least 3 tries");
        expect($instance->backoff)->toBeArray("{$class} must have retry backoff intervals");
    }
});

test('order placement sends confirmation email and deduplication prevents duplicate emails', function () {
    Mail::fake();

    [, $variant] = createNotificationTestProduct('Order Item', 1000.00, 10);
    $orderService = app(OrderService::class);
    $notificationService = app(OrderNotificationService::class);

    $customer = [
        'first_name' => 'Natasha',
        'last_name' => 'Romanoff',
        'email' => 'natasha@avengers.test',
        'phone' => '9876543210',
    ];

    $address = [
        'first_name' => 'Natasha',
        'last_name' => 'Romanoff',
        'address' => 'Secret Base 42',
        'city' => 'Budapest',
        'state' => 'PE',
        'postal_code' => '10001',
        'country' => 'India',
        'phone' => '9876543210',
    ];

    $cart = [
        $variant->id => [
            'variant_id' => $variant->id,
            'quantity' => 1,
        ],
    ];

    // 1. Order placed -> Sends confirmation email
    $order = $orderService->createOrder($customer, $address, $address, $cart, ['method' => 'cod', 'status' => 'pending']);

    Mail::assertQueued(OrderConfirmationMail::class, function (OrderConfirmationMail $mail) use ($order) {
        return $mail->hasTo('natasha@avengers.test')
            && $mail->order->order_number === $order->order_number;
    });

    // Verify deduplication tracking record exists in order_notifications
    expect(OrderNotification::where('order_id', $order->id)->where('type', 'order_placed')->count())->toBe(1);

    // 2. Trigger notification service a second time for order_placed
    $reSent = $notificationService->notifyOrderPlaced($order);
    expect($reSent)->toBeFalse(); // Must be blocked!

    // Verify Mail was not queued a second time
    Mail::assertQueuedCount(1);
});

test('marking order as paid sends payment confirmation email and deduplicates retries', function () {
    Mail::fake();

    [, $variant] = createNotificationTestProduct('Payment Item', 2000.00, 10);
    $order = Order::create([
        'order_number' => 'ORD-PAY-TEST',
        'customer_first_name' => 'Clint',
        'customer_email' => 'clint@hawkeye.test',
        'status' => 'pending',
        'payment_status' => 'pending',
        'subtotal' => 2000.00,
        'total' => 2000.00,
        'shipping_address' => ['first_name' => 'Clint'],
        'billing_address' => ['first_name' => 'Clint'],
    ]);

    // 1. Mark as Paid -> Dispatches PaymentConfirmedMail
    $order->markAsPaid('TXN-HAWK-1234', 'card');

    Mail::assertQueued(PaymentConfirmedMail::class, function (PaymentConfirmedMail $mail) use ($order) {
        return $mail->hasTo('clint@hawkeye.test')
            && $mail->order->order_number === $order->order_number;
    });

    expect(OrderNotification::where('order_id', $order->id)->where('type', 'payment_confirmed')->count())->toBe(1);

    // 2. Second payment confirmation trigger (e.g. duplicate webhook callback)
    $secondResult = app(OrderNotificationService::class)->notifyPaymentConfirmed($order);
    expect($secondResult)->toBeFalse();

    // Verify Mail was not dispatched a second time
    Mail::assertQueuedCount(1);
});

test('order shipped transition sends shipping notification email and deduplicates retries', function () {
    Mail::fake();

    [, $variant] = createNotificationTestProduct('Shipping Item', 1500.00, 10);
    $order = Order::create([
        'order_number' => 'ORD-SHIP-TEST',
        'customer_first_name' => 'Steve',
        'customer_email' => 'steve@cap.test',
        'status' => 'processing',
        'payment_status' => 'paid',
        'subtotal' => 1500.00,
        'total' => 1500.00,
        'shipping_address' => ['first_name' => 'Steve', 'address' => '569 Leaman Place', 'city' => 'Brooklyn', 'state' => 'NY'],
        'billing_address' => ['first_name' => 'Steve'],
    ]);

    // Transition to shipped
    $order->transitionTo('shipped', 'Shipped via Express Courier');

    Mail::assertQueued(OrderShippedMail::class, function (OrderShippedMail $mail) use ($order) {
        return $mail->hasTo('steve@cap.test')
            && $mail->order->order_number === $order->order_number;
    });

    expect(OrderNotification::where('order_id', $order->id)->where('type', 'order_shipped')->count())->toBe(1);

    // Duplicate notification attempt
    $retry = app(OrderNotificationService::class)->notifyOrderShipped($order);
    expect($retry)->toBeFalse();

    Mail::assertQueuedCount(1);
});

test('order delivered transition sends delivery notification email and deduplicates retries', function () {
    Mail::fake();

    [, $variant] = createNotificationTestProduct('Delivery Item', 1200.00, 10);
    $order = Order::create([
        'order_number' => 'ORD-DELIV-TEST',
        'customer_first_name' => 'Thor',
        'customer_email' => 'thor@asgard.test',
        'status' => 'shipped',
        'payment_status' => 'paid',
        'subtotal' => 1200.00,
        'total' => 1200.00,
        'shipping_address' => ['first_name' => 'Thor'],
        'billing_address' => ['first_name' => 'Thor'],
    ]);

    // Transition to delivered
    $order->transitionTo('delivered', 'Signed by customer');

    Mail::assertQueued(OrderDeliveredMail::class, function (OrderDeliveredMail $mail) use ($order) {
        return $mail->hasTo('thor@asgard.test')
            && $mail->order->order_number === $order->order_number;
    });

    expect(OrderNotification::where('order_id', $order->id)->where('type', 'order_delivered')->count())->toBe(1);

    // Duplicate delivery notification attempt
    $retry = app(OrderNotificationService::class)->notifyOrderDelivered($order);
    expect($retry)->toBeFalse();

    Mail::assertQueuedCount(1);
});

test('order cancellation transition sends cancellation email and deduplicates retries', function () {
    Mail::fake();

    [, $variant] = createNotificationTestProduct('Cancel Item', 800.00, 10);
    $order = Order::create([
        'order_number' => 'ORD-CANCEL-TEST',
        'customer_first_name' => 'Loki',
        'customer_email' => 'loki@mischief.test',
        'status' => 'pending',
        'payment_status' => 'pending',
        'subtotal' => 800.00,
        'total' => 800.00,
        'shipping_address' => ['first_name' => 'Loki'],
        'billing_address' => ['first_name' => 'Loki'],
    ]);

    // Transition to cancelled
    $order->transitionTo('cancelled', 'Customer requested cancellation');

    Mail::assertQueued(OrderCancelledMail::class, function (OrderCancelledMail $mail) use ($order) {
        return $mail->hasTo('loki@mischief.test')
            && $mail->order->order_number === $order->order_number;
    });

    expect(OrderNotification::where('order_id', $order->id)->where('type', 'order_cancelled')->count())->toBe(1);

    // Duplicate cancel notification attempt
    $retry = app(OrderNotificationService::class)->notifyOrderCancelled($order);
    expect($retry)->toBeFalse();

    Mail::assertQueuedCount(1);
});

test('order refund processing sends refund notification email and deduplicates retries', function () {
    Mail::fake();

    [, $variant] = createNotificationTestProduct('Refund Item', 3000.00, 10);
    $order = Order::create([
        'order_number' => 'ORD-REFUND-TEST',
        'customer_first_name' => 'Peter',
        'customer_email' => 'peter@parker.test',
        'status' => 'processing',
        'payment_status' => 'paid',
        'subtotal' => 3000.00,
        'total' => 3000.00,
        'shipping_address' => ['first_name' => 'Peter'],
        'billing_address' => ['first_name' => 'Peter'],
    ]);

    // Process refund
    $order->markAsRefunded('RFND-SPIDER-99', 3000.00);

    Mail::assertQueued(OrderRefundedMail::class, function (OrderRefundedMail $mail) use ($order) {
        return $mail->hasTo('peter@parker.test')
            && $mail->order->order_number === $order->order_number;
    });

    expect(OrderNotification::where('order_id', $order->id)->where('type', 'order_refunded')->count())->toBe(1);

    // Duplicate refund notification attempt
    $retry = app(OrderNotificationService::class)->notifyOrderRefunded($order, 3000.00);
    expect($retry)->toBeFalse();
    Mail::assertQueued(OrderRefundedMail::class, 1);
    Mail::assertQueued(OrderCancelledMail::class, 1);
    Mail::assertQueuedCount(2);
});
