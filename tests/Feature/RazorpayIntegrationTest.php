<?php

use App\Livewire\Admin\Settings\Index as SettingsIndex;
use App\Livewire\Storefront\Checkout;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\CartService;
use App\Services\PaymentService;
use App\Services\SettingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Auth::logout();
    Role::firstOrCreate(['name' => 'Customer', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Super Administrator', 'guard_name' => 'web']);

    $settingService = app(SettingService::class);
    $settingService->setGroup('payment', [
        'enable_razorpay' => true,
        'razorpay_mode' => 'test',
        'razorpay_key_id' => 'rzp_test_key_12345',
        'razorpay_key_secret' => 'rzp_test_secret_67890',
        'razorpay_instructions' => 'Pay securely via Razorpay.',
    ]);
});

function createRazorpayTestProduct(string $name = 'Wireless Mechanical Keyboard', float $price = 2500.00, int $stock = 10): array
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
        'title' => 'RGB White',
        'sku' => $product->sku.'-WHT',
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

test('payment service creates razorpay order via http client with basic auth and paise conversion', function () {
    $paymentService = app(PaymentService::class);

    Http::fake([
        'https://api.razorpay.com/v1/orders' => Http::response([
            'id' => 'order_rzp_mock_111',
            'entity' => 'order',
            'amount' => 250000,
            'amount_paid' => 0,
            'amount_due' => 250000,
            'currency' => 'INR',
            'receipt' => 'rcpt_test_01',
            'status' => 'created',
            'attempts' => 0,
            'notes' => ['customer_email' => 'buyer@example.com'],
            'created_at' => 1700000000,
        ], 200),
    ]);

    $orderData = $paymentService->createRazorpayOrder(2500.00, 'rcpt_test_01', 'INR', [
        'customer_email' => 'buyer@example.com',
    ]);

    expect($orderData)->toBeArray();
    expect($orderData['id'])->toBe('order_rzp_mock_111');
    expect($orderData['amount'])->toBe(250000);
    expect($orderData['currency'])->toBe('INR');

    Http::assertSent(function ($request) {
        return $request->url() === 'https://api.razorpay.com/v1/orders'
            && $request['amount'] === 250000
            && $request['currency'] === 'INR'
            && $request['receipt'] === 'rcpt_test_01'
            && $request->hasHeader('Authorization');
    });
});

test('payment service handles razorpay api errors gracefully', function () {
    $paymentService = app(PaymentService::class);

    Http::fake([
        'https://api.razorpay.com/v1/orders' => Http::response([
            'error' => [
                'code' => 'BAD_REQUEST_ERROR',
                'description' => 'The amount must be an integer.',
            ],
        ], 400),
    ]);

    expect(fn () => $paymentService->createRazorpayOrder(100.00, 'rcpt_err'))
        ->toThrow(RuntimeException::class, 'The amount must be an integer.');
});

test('payment service verifies razorpay hmac sha256 signature', function () {
    $paymentService = app(PaymentService::class);
    $secret = 'rzp_test_secret_67890';
    $orderId = 'order_ABC123';
    $paymentId = 'pay_XYZ789';

    $validSignature = hash_hmac('sha256', "{$orderId}|{$paymentId}", $secret);

    expect($paymentService->verifyRazorpaySignature($orderId, $paymentId, $validSignature, $secret))->toBeTrue();
    expect($paymentService->verifyRazorpaySignature($orderId, $paymentId, 'invalid_sig', $secret))->toBeFalse();
    expect($paymentService->verifyRazorpaySignature($orderId, 'tampered_pay_id', $validSignature, $secret))->toBeFalse();
});

test('checkout component initiates razorpay order and dispatches open-razorpay-checkout event', function () {
    [, $variant] = createRazorpayTestProduct('Gaming Headset', 1500.00, 10);
    app(CartService::class)->add($variant, 1);

    Http::fake([
        'https://api.razorpay.com/v1/orders' => Http::response([
            'id' => 'order_livewire_rzp_123',
            'amount' => 177000,
            'currency' => 'INR',
            'receipt' => 'rcpt_livewire',
            'status' => 'created',
        ], 200),
    ]);

    Livewire::test(Checkout::class)
        ->set('firstName', 'Bruce')
        ->set('lastName', 'Wayne')
        ->set('email', 'bruce@wayne.test')
        ->set('phone', '9876543210')
        ->set('address', '1007 Mountain Drive')
        ->set('city', 'Gotham')
        ->set('state', 'MH')
        ->set('postalCode', '400001')
        ->set('country', 'India')
        ->set('paymentMethod', 'razorpay')
        ->call('placeOrder')
        ->assertDispatched('open-razorpay-checkout', function ($name, $payload) {
            $data = $payload[0] ?? $payload;

            return $data['order_id'] === 'order_livewire_rzp_123'
                && $data['currency'] === 'INR'
                && $data['prefill']['email'] === 'bruce@wayne.test'
                && $data['key'] === 'rzp_test_key_12345';
        });
});

test('checkout component handleRazorpaySuccess verifies signature, transitions order to paid, and redirects', function () {
    [, $variant, $inventory] = createRazorpayTestProduct('Studio Monitor', 5000.00, 5);
    app(CartService::class)->add($variant, 1);

    $secret = 'rzp_test_secret_67890';
    $orderId = 'order_success_456';
    $paymentId = 'pay_success_789';
    $validSignature = hash_hmac('sha256', "{$orderId}|{$paymentId}", $secret);

    $checkout = Livewire::test(Checkout::class)
        ->set('firstName', 'Clark')
        ->set('lastName', 'Kent')
        ->set('email', 'clark@dailyplanet.test')
        ->set('phone', '9123456789')
        ->set('address', '344 Clinton Street')
        ->set('city', 'Metropolis')
        ->set('state', 'DL')
        ->set('postalCode', '110001')
        ->set('country', 'India')
        ->set('paymentMethod', 'razorpay')
        ->call('handleRazorpaySuccess', $paymentId, $orderId, $validSignature);

    $order = Order::where('customer_email', 'clark@dailyplanet.test')->first();
    expect($order)->not->toBeNull();
    expect($order->payment_method)->toBe('razorpay');
    expect($order->payment_status)->toBe('paid');
    expect($order->status)->toBe('processing');
    expect($order->payment_reference)->toBe($paymentId);

    // Cart is cleared
    expect(app(CartService::class)->count())->toBe(0);

    // Stock was deducted
    $inventory->refresh();
    expect($inventory->quantity)->toBe(4);

    // Payment transaction audit recorded
    $txn = PaymentTransaction::where('transaction_id', $paymentId)->first();
    expect($txn)->not->toBeNull();
    expect($txn->signature_verified)->toBeTrue();
    expect($txn->status)->toBe('paid');

    $checkout->assertRedirect(route('storefront.order-success', $order->order_number));
});

test('checkout component rejects tampered razorpay signature and does not clear cart', function () {
    [, $variant] = createRazorpayTestProduct('Noise Cancelling Earphones', 3000.00, 5);
    app(CartService::class)->add($variant, 1);

    Livewire::test(Checkout::class)
        ->set('firstName', 'Barry')
        ->set('lastName', 'Allen')
        ->set('email', 'barry@centralcity.test')
        ->set('phone', '9000000000')
        ->set('address', '505 Flash Way')
        ->set('city', 'Central City')
        ->set('state', 'KA')
        ->set('postalCode', '560001')
        ->set('country', 'India')
        ->set('paymentMethod', 'razorpay')
        ->call('handleRazorpaySuccess', 'pay_fake_999', 'order_fake_999', 'tampered_bogus_signature')
        ->assertHasErrors(['checkout']);

    // Order was NOT created
    expect(Order::where('customer_email', 'barry@centralcity.test')->exists())->toBeFalse();

    // Cart remains preserved
    expect(app(CartService::class)->count())->toBe(1);
});

test('razorpay webhook successfully captures payment and transitions order', function () {
    $secret = 'rzp_test_secret_67890';
    app(SettingService::class)->set('razorpay_key_secret', $secret);

    $order = Order::create([
        'order_number' => 'ORD-RZP-WH-001',
        'customer_first_name' => 'Hal',
        'customer_last_name' => 'Jordan',
        'customer_email' => 'hal@coastcity.test',
        'status' => 'pending',
        'payment_status' => 'pending',
        'payment_method' => 'razorpay',
        'subtotal' => 3000.00,
        'total' => 3000.00,
        'shipping_address' => ['first_name' => 'Hal'],
        'billing_address' => ['first_name' => 'Hal'],
    ]);

    $webhookData = [
        'event_id' => 'evt_rzp_hook_01',
        'event' => 'payment.captured',
        'payload' => [
            'payment' => [
                'entity' => [
                    'id' => 'pay_rzp_hook_captured',
                    'amount' => 300000,
                    'currency' => 'INR',
                    'notes' => [
                        'order_number' => 'ORD-RZP-WH-001',
                    ],
                ],
            ],
        ],
    ];

    $payloadJson = json_encode($webhookData);
    $signature = hash_hmac('sha256', $payloadJson, $secret);

    $response = $this->call(
        'POST',
        '/api/v1/payments/webhook/razorpay',
        [],
        [],
        [],
        [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_RAZORPAY_SIGNATURE' => $signature,
        ],
        $payloadJson
    );

    $response->assertStatus(200);
    $response->assertJson(['status' => 'success', 'payment_status' => 'paid']);

    $order->refresh();
    expect($order->payment_status)->toBe('paid');
    expect($order->status)->toBe('processing');
    expect($order->payment_reference)->toBe('pay_rzp_hook_captured');

    // Duplicate webhook call should be idempotent
    $duplicate = $this->call(
        'POST',
        '/api/v1/payments/webhook/razorpay',
        [],
        [],
        [],
        [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_RAZORPAY_SIGNATURE' => $signature,
        ],
        $payloadJson
    );

    $duplicate->assertStatus(200);
    $duplicate->assertJson(['status' => 'already_processed']);
});

test('admin can view and save razorpay settings in settings panel', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Super Administrator');
    $this->actingAs($admin);

    Livewire::test(SettingsIndex::class)
        ->set('activeTab', 'payment')
        ->assertSet('enable_razorpay', true)
        ->assertSet('razorpay_key_id', 'rzp_test_key_12345')
        ->set('razorpay_mode', 'live')
        ->set('razorpay_key_id', 'rzp_live_new_key_999')
        ->set('razorpay_key_secret', 'rzp_live_new_secret_888')
        ->set('razorpay_instructions', 'Pay with UPI or Cards.')
        ->call('save')
        ->assertHasNoErrors();

    $settingService = app(SettingService::class);
    expect($settingService->get('razorpay_mode'))->toBe('live');
    expect($settingService->get('razorpay_key_id'))->toBe('rzp_live_new_key_999');
    expect($settingService->get('razorpay_key_secret'))->toBe('rzp_live_new_secret_888');
    expect($settingService->get('razorpay_instructions'))->toBe('Pay with UPI or Cards.');
});

test('checkout component uses razorpay simulation sandbox when default placeholder credentials are used', function () {
    app(SettingService::class)->setGroup('payment', [
        'enable_razorpay' => true,
        'razorpay_mode' => 'test',
        'razorpay_key_id' => 'rzp_test_key',
        'razorpay_key_secret' => 'rzp_test_secret_key',
    ]);

    [, $variant] = createRazorpayTestProduct('Desk LED Lamp', 899.00, 10);
    app(CartService::class)->add($variant, 1);

    $checkout = Livewire::test(Checkout::class)
        ->set('firstName', 'Azad')
        ->set('lastName', 'Singh')
        ->set('email', 'azad@example.com')
        ->set('phone', '9876543210')
        ->set('address', '123 Main Road')
        ->set('city', 'New Delhi')
        ->set('state', 'DL')
        ->set('postalCode', '110001')
        ->set('country', 'India')
        ->set('paymentMethod', 'razorpay')
        ->call('placeOrder')
        ->assertSet('showRazorpaySimulatedModal', true)
        ->assertSee('Razorpay Checkout')
        ->assertSee('Test Sandbox');

    // Simulate successful payment from modal
    $checkout->call('simulateRazorpaySuccess');

    $order = Order::where('customer_email', 'azad@example.com')->first();
    expect($order)->not->toBeNull();
    expect($order->payment_method)->toBe('razorpay');
    expect($order->payment_status)->toBe('paid');
    expect($order->status)->toBe('processing');

    // Cart is emptied
    expect(app(CartService::class)->count())->toBe(0);
});

test('payment service provides clear explanation when razorpay api keys are invalid (401)', function () {
    $paymentService = app(PaymentService::class);

    Http::fake([
        'https://api.razorpay.com/v1/orders' => Http::response([
            'error' => [
                'code' => 'BAD_REQUEST_ERROR',
                'description' => 'Authentication failed',
            ],
        ], 401),
    ]);

    expect(fn () => $paymentService->createRazorpayOrder(500.00, 'rcpt_auth_fail'))
        ->toThrow(RuntimeException::class, 'Razorpay Authentication Failed: The Key ID or Key Secret configured in Admin Settings > Payment is invalid or expired.');
});
