<?php

use App\Livewire\Storefront\Checkout;
use App\Livewire\Storefront\OrderSuccess;
use App\Mail\OrderConfirmationMail;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CartService;
use App\Services\PaymentService;
use App\Services\SettingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => 'Customer', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
});

function createTestCheckoutProduct(string $name = 'Premium Headphones', float $price = 1000.00, int $stock = 10): array
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

test('payment service cryptographically verifies razorpay hmac-sha256 signature', function () {
    $paymentService = app(PaymentService::class);
    $secret = 'test_razorpay_secret_123';
    $orderId = 'order_test_98765';
    $paymentId = 'pay_test_12345';

    $validSignature = hash_hmac('sha256', "{$orderId}|{$paymentId}", $secret);

    expect($paymentService->verifyRazorpaySignature($orderId, $paymentId, $validSignature, $secret))->toBeTrue();
    expect($paymentService->verifyRazorpaySignature($orderId, $paymentId, 'invalid_tampered_signature', $secret))->toBeFalse();
    expect($paymentService->verifyRazorpaySignature($orderId, 'tampered_pay_id', $validSignature, $secret))->toBeFalse();
});

test('payment service cryptographically verifies stripe webhook signature headers', function () {
    $paymentService = app(PaymentService::class);
    $secret = 'whsec_stripe_secret_abc';
    $payload = json_encode(['id' => 'evt_test_123', 'type' => 'payment_intent.succeeded']);
    $timestamp = time();

    $signature = hash_hmac('sha256', "{$timestamp}.{$payload}", $secret);
    $header = "t={$timestamp},v1={$signature}";

    expect($paymentService->verifyWebhookSignature('stripe', $payload, $header, $secret))->toBeTrue();
    expect($paymentService->verifyWebhookSignature('stripe', $payload, "t={$timestamp},v1=fake_signature", $secret))->toBeFalse();
});

test('order transitions correctly across pending, paid, failed, and refunded states with inventory restoration', function () {
    [, $variant, $inventory] = createTestCheckoutProduct('Lifecycle Item', 500.00, 10);

    $order = Order::create([
        'order_number' => 'ORD-TEST-001',
        'status' => 'pending',
        'payment_status' => 'pending',
        'payment_method' => 'cod',
        'subtotal' => 500.00,
        'total' => 500.00,
        'shipping_address' => ['first_name' => 'John', 'last_name' => 'Doe'],
        'billing_address' => ['first_name' => 'John', 'last_name' => 'Doe'],
    ]);

    $order->items()->create([
        'product_id' => $variant->product_id,
        'product_variant_id' => $variant->id,
        'product_name' => $variant->product->name,
        'variant_name' => $variant->title,
        'sku' => $variant->sku,
        'quantity' => 2,
        'unit_price' => 500.00,
        'total' => 1000.00,
    ]);

    // Simulate inventory reservation
    $inventory->update(['quantity' => 8, 'reserved_quantity' => 2]);

    expect($order->isPending())->toBeTrue();
    expect($order->isPaid())->toBeFalse();

    // 1. Transition to Paid
    $order->markAsPaid('TXN-PAY-12345', 'razorpay');
    $order->refresh();

    expect($order->isPaid())->toBeTrue();
    expect($order->status)->toBe('processing');
    expect($order->payment_reference)->toBe('TXN-PAY-12345');
    expect($order->paid_at)->not->toBeNull();

    // 2. Transition to Refunded -> Verifies Inventory is automatically Restored
    $order->markAsRefunded('RFND-999');
    $order->refresh();
    $inventory->refresh();

    expect($order->isRefunded())->toBeTrue();
    expect($order->status)->toBe('cancelled');
    expect($inventory->quantity)->toBe(10);
    expect($inventory->reserved_quantity)->toBe(0);

    // 3. Mark As Failed
    $order->markAsFailed('Card declined');
    $order->refresh();
    expect($order->isFailed())->toBeTrue();
});

test('payment webhook verifies signature and idempotently processes payment capture', function () {
    $secret = 'rzp_test_secret_key';
    app(SettingService::class)->set('razorpay_key_secret', $secret);

    [, $variant] = createTestCheckoutProduct('Webhook Item', 1200.00, 5);

    $order = Order::create([
        'order_number' => 'ORD-WEBHOOK-99',
        'status' => 'pending',
        'payment_status' => 'pending',
        'payment_method' => 'razorpay',
        'subtotal' => 1200.00,
        'total' => 1200.00,
        'shipping_address' => ['first_name' => 'Alice'],
        'billing_address' => ['first_name' => 'Alice'],
    ]);

    $webhookData = [
        'event_id' => 'evt_rzp_112233',
        'event' => 'payment.captured',
        'payload' => [
            'payment' => [
                'entity' => [
                    'id' => 'pay_rzp_998877',
                    'amount' => 120000,
                    'currency' => 'INR',
                    'notes' => [
                        'order_number' => 'ORD-WEBHOOK-99',
                    ],
                ],
            ],
        ],
    ];

    $payloadJson = json_encode($webhookData);
    $validSignature = hash_hmac('sha256', $payloadJson, $secret);

    // 1. First webhook call -> Must succeed and mark order as paid
    $response = $this->call(
        'POST',
        '/api/v1/payments/webhook/razorpay',
        [],
        [],
        [],
        [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_RAZORPAY_SIGNATURE' => $validSignature,
        ],
        $payloadJson
    );

    $response->assertStatus(200);
    $response->assertJson(['status' => 'success', 'payment_status' => 'paid']);

    $order->refresh();
    expect($order->payment_status)->toBe('paid');
    expect($order->payment_reference)->toBe('pay_rzp_998877');

    $txn = PaymentTransaction::where('transaction_id', 'pay_rzp_998877')->first();
    expect($txn)->not->toBeNull();
    expect($txn->status)->toBe('paid');
    expect($txn->signature_verified)->toBeTrue();

    // 2. Duplicate webhook call (Idempotency test) -> Must return already_processed without duplicate order processing
    $duplicateResponse = $this->call(
        'POST',
        '/api/v1/payments/webhook/razorpay',
        [],
        [],
        [],
        [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_RAZORPAY_SIGNATURE' => $validSignature,
        ],
        $payloadJson
    );

    $duplicateResponse->assertStatus(200);
    $duplicateResponse->assertJson([
        'status' => 'already_processed',
        'transaction_id' => 'pay_rzp_998877',
    ]);

    // Ensure database row count for transactions did not increase
    expect(PaymentTransaction::where('transaction_id', 'pay_rzp_998877')->count())->toBe(1);
});

test('payment webhook rejects requests with invalid or tampered signature', function () {
    $secret = 'rzp_test_secret_key';
    app(SettingService::class)->set('razorpay_key_secret', $secret);

    $payloadJson = json_encode([
        'event_id' => 'evt_fake_999',
        'event' => 'payment.captured',
        'transaction_id' => 'pay_fake_111',
    ]);

    $response = $this->call(
        'POST',
        '/api/v1/payments/webhook/razorpay',
        [],
        [],
        [],
        [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_RAZORPAY_SIGNATURE' => 'totally_fake_signature_hash',
        ],
        $payloadJson
    );

    $response->assertStatus(401);
    $response->assertJson(['status' => 'error']);
});

test('safe checkout preserves cart when payment verification fails', function () {
    [, $variant] = createTestCheckoutProduct('Preserved Cart Item', 850.00, 5);
    app(CartService::class)->add($variant, 1);

    // Call checkout with an invalid card (fails validation or payment processing)
    Livewire::test(Checkout::class)
        ->set('paymentMethod', 'card')
        ->set('firstName', 'Dave')
        ->set('lastName', 'Miller')
        ->set('email', 'dave@example.com')
        ->set('phone', '9876543210')
        ->set('address', '101 Pine Street')
        ->set('city', 'Bangalore')
        ->set('state', 'Karnataka')
        ->set('postalCode', '560001')
        ->set('country', 'India')
        ->set('cardName', 'Dave Miller')
        ->set('cardNumber', '4111111111111111')
        ->set('cardExpiry', 'invalid-expiry')
        ->set('cardCvv', '123')
        ->call('placeOrder')
        ->assertHasErrors();

    // Cart is safely intact
    expect(app(CartService::class)->count())->toBe(1);
});

test('order success page does not mark an order as paid simply by returning to it', function () {
    [, $variant] = createTestCheckoutProduct('Unpaid Item', 300.00, 5);

    $order = Order::create([
        'order_number' => 'ORD-UNPAID-123',
        'status' => 'pending',
        'payment_status' => 'pending',
        'payment_method' => 'cod',
        'subtotal' => 300.00,
        'total' => 300.00,
        'shipping_address' => ['first_name' => 'Sam', 'address' => 'Baker St'],
        'billing_address' => ['first_name' => 'Sam', 'address' => 'Baker St'],
    ]);

    $order->items()->create([
        'product_id' => $variant->product_id,
        'product_variant_id' => $variant->id,
        'product_name' => $variant->product->name,
        'variant_name' => $variant->title,
        'sku' => $variant->sku,
        'quantity' => 1,
        'unit_price' => 300.00,
        'total' => 300.00,
    ]);

    // Mount success page as a guest or customer
    Livewire::test(OrderSuccess::class, ['orderNumber' => $order->order_number])
        ->assertSee('ORD-UNPAID-123')
        ->assertSee('Pending (Pay on Delivery)');

    $order->refresh();
    // Payment status MUST STILL be pending!
    expect($order->payment_status)->toBe('pending');
    expect($order->paid_at)->toBeNull();
});

test('order success page shows failed state and retry link when payment status is failed', function () {
    [, $variant] = createTestCheckoutProduct('Failed Item', 400.00, 5);

    $order = Order::create([
        'order_number' => 'ORD-FAILED-789',
        'status' => 'pending',
        'payment_status' => 'failed',
        'payment_method' => 'card',
        'subtotal' => 400.00,
        'total' => 400.00,
        'shipping_address' => ['first_name' => 'Sam', 'address' => 'Baker St'],
        'billing_address' => ['first_name' => 'Sam', 'address' => 'Baker St'],
    ]);

    $order->items()->create([
        'product_id' => $variant->product_id,
        'product_variant_id' => $variant->id,
        'product_name' => $variant->product->name,
        'variant_name' => $variant->title,
        'sku' => $variant->sku,
        'quantity' => 1,
        'unit_price' => 400.00,
        'total' => 400.00,
    ]);

    Livewire::test(OrderSuccess::class, ['orderNumber' => $order->order_number])
        ->assertSee('Payment Failed')
        ->assertSee('Retry Payment at Checkout');
});

test('order confirmation email reflects true payment status', function () {
    Mail::fake();

    $order = Order::create([
        'order_number' => 'ORD-EMAIL-TEST',
        'customer_first_name' => 'Elena',
        'customer_last_name' => 'Gilbert',
        'customer_email' => 'elena@example.com',
        'status' => 'processing',
        'payment_status' => 'paid',
        'payment_method' => 'card',
        'subtotal' => 1500.00,
        'total' => 1500.00,
        'shipping_address' => ['first_name' => 'Elena'],
        'billing_address' => ['first_name' => 'Elena'],
    ]);

    app(SettingService::class)->notifyOrderPlaced($order);

    Mail::assertQueued(OrderConfirmationMail::class, function (OrderConfirmationMail $mail) use ($order) {
        return $mail->hasTo('elena@example.com')
            && $mail->order->order_number === $order->order_number
            && $mail->order->payment_status === 'paid';
    });
});
