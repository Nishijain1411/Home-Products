<?php

use App\Http\Controllers\Api\V1\PaymentWebhookController;
use App\Livewire\Storefront\Checkout;
use App\Livewire\Storefront\Orders\Index as CustomerOrderIndex;
use App\Livewire\Storefront\Orders\Show as CustomerOrderShow;
use App\Livewire\Storefront\OrderSuccess;
use App\Mail\OrderConfirmationMail;
use App\Mail\PaymentConfirmedMail;
use App\Models\Discount;
use App\Models\DiscountRedemption;
use App\Models\Inventory;
use App\Models\InventoryAdjustment;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\PaymentTransaction;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\CartService;
use App\Services\DiscountService;
use App\Services\InventoryService;
use App\Services\OrderService;
use App\Services\SettingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => 'Customer', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Super Administrator', 'guard_name' => 'web']);
});

function createQATestProduct(string $name = 'QA Smart Watch', float $price = 1500.00, int $stock = 10): array
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
        'title' => 'Standard Edition',
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

/*
|--------------------------------------------------------------------------
| 1. Authentication & Role-Based Access Control QA
|--------------------------------------------------------------------------
*/

test('authentication and role permissions enforce admin access control and customer isolation', function () {
    $customer = User::factory()->create([
        'name' => 'John Customer',
        'email' => 'john.customer@example.com',
    ]);
    $customer->assignRole('Customer');

    $admin = User::factory()->create([
        'name' => 'Admin Boss',
        'email' => 'admin.boss@example.com',
    ]);
    $admin->assignRole('Super Administrator');

    // 1. Unauthenticated guest attempting to access admin dashboard -> redirected to login
    $this->get(route('admin.dashboard'))
        ->assertRedirect(route('login'));

    // 2. Customer logged in attempting to access admin routes -> 403 Forbidden
    $this->actingAs($customer);
    $this->get(route('admin.dashboard'))->assertForbidden();
    $this->get(route('admin.orders.index'))->assertForbidden();
    $this->get(route('admin.products.index'))->assertForbidden();

    // Customer visiting /dashboard -> redirects to customer orders / storefront
    $this->get(route('dashboard'))->assertRedirect(route('storefront.orders.index'));

    // 3. Super Administrator logged in -> 200 OK
    $this->actingAs($admin);
    $this->get(route('admin.dashboard'))->assertOk();
    $this->get(route('admin.orders.index'))->assertOk();
    $this->get(route('admin.products.index'))->assertOk();
});

/*
|--------------------------------------------------------------------------
| 2. Product Management QA
|--------------------------------------------------------------------------
*/

test('product management supports creating, updating, pricing, and toggling active status', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Super Administrator');
    $this->actingAs($admin);

    // Create product & variant
    $product = Product::create([
        'name' => 'Ergonomic Gaming Mouse',
        'slug' => 'ergonomic-gaming-mouse',
        'sku' => 'MOUSE-ERGO-01',
        'price' => 2499.00,
        'is_active' => true,
    ]);

    $variant = ProductVariant::create([
        'product_id' => $product->id,
        'title' => 'Matte Black',
        'sku' => 'MOUSE-ERGO-01-BLK',
        'price' => 2499.00,
        'is_active' => true,
    ]);

    $inventory = Inventory::create([
        'product_variant_id' => $variant->id,
        'quantity' => 25,
        'reserved_quantity' => 0,
    ]);

    expect($product->exists)->toBeTrue();
    expect($variant->price)->toEqual(2499.00);
    expect($inventory->quantity)->toBe(25);

    // Update variant price
    $variant->update(['price' => 2199.00]);
    $variant->refresh();
    expect((float) $variant->price)->toBe(2199.00);

    // Toggle product is_active to false
    $product->update(['is_active' => false]);
    $product->refresh();
    expect($product->is_active)->toBeFalse();

    // Verify inactive product is excluded from active scope
    expect(Product::where('is_active', true)->where('id', $product->id)->exists())->toBeFalse();
});

/*
|--------------------------------------------------------------------------
| 3. Stock Adjustments & Audit Trail QA
|--------------------------------------------------------------------------
*/

test('inventory stock adjustments, reservations and audit logging', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Super Administrator');

    [, $variant, $inventory] = createQATestProduct('Mechanical Keyboard', 4000.00, 10);
    $inventoryService = app(InventoryService::class);

    // 1. Reserve and deduct during checkout
    $inventoryService->reserveAndDeduct($variant, 3, 'ORD-QA-100', $admin->id);
    $inventory->refresh();

    expect($inventory->quantity)->toBe(7);
    expect($inventory->reserved_quantity)->toBe(3);

    // Check InventoryAdjustment audit trail
    $adjustment = InventoryAdjustment::where('inventory_id', $inventory->id)->first();
    expect($adjustment)->not->toBeNull();
    expect($adjustment->quantity_before)->toBe(10);
    expect($adjustment->quantity_changed)->toBe(3);
    expect($adjustment->quantity_after)->toBe(7);
    expect($adjustment->type)->toBe('decrease');

    // 2. Fulfill order (release reservation)
    $order = Order::create([
        'order_number' => 'ORD-QA-100',
        'status' => 'shipped',
        'payment_status' => 'paid',
        'subtotal' => 12000.00,
        'total' => 12000.00,
        'shipping_address' => ['first_name' => 'Tester'],
        'billing_address' => ['first_name' => 'Tester'],
    ]);

    $order->items()->create([
        'product_id' => $variant->product_id,
        'product_variant_id' => $variant->id,
        'product_name' => $variant->product->name,
        'variant_name' => $variant->title,
        'sku' => $variant->sku,
        'quantity' => 3,
        'unit_price' => 4000.00,
        'total' => 12000.00,
    ]);

    $inventoryService->releaseReservation($order, $admin->id);
    $inventory->refresh();
    expect($inventory->reserved_quantity)->toBe(0);

    // 3. Cancel order and restore stock
    $inventoryService->restoreStock($order, 'return', $admin->id);
    $inventory->refresh();
    expect($inventory->quantity)->toBe(10); // Restored back to 10
});

/*
|--------------------------------------------------------------------------
| 4. Coupons, Cart Calculations & Minimum Spend QA
|--------------------------------------------------------------------------
*/

test('coupons and cart calculations accurately compute subtotals, fixed and percentage discounts, and respect minimum spend', function () {
    [, $variant] = createQATestProduct('Soundbar', 1000.00, 10);
    $cartService = app(CartService::class);
    $discountService = app(DiscountService::class);
    $settingService = app(SettingService::class);

    // Create fixed discount coupon ($200 off, minimum spend $1500)
    $fixedCoupon = Discount::create([
        'code' => 'FIXED200',
        'name' => 'Fixed 200 Off',
        'type' => 'fixed',
        'value' => 200.00,
        'min_order_amount' => 1500.00,
        'is_active' => true,
    ]);

    // Create percentage discount coupon (10% off, max discount $150, min spend $500)
    $percentCoupon = Discount::create([
        'code' => 'PERCENT10',
        'name' => '10 Percent Off',
        'type' => 'percentage',
        'value' => 10.00,
        'min_order_amount' => 500.00,
        'max_discount_amount' => 150.00,
        'is_active' => true,
    ]);

    // 1. Cart subtotal = 1000 (1 item)
    $cartService->clear();
    $cartService->add($variant, 1);
    expect($cartService->subtotal())->toEqual(1000.00);

    // Fixed coupon rejected: minimum spend $1500 not reached
    $validation = $discountService->validateCode('FIXED200', $cartService->subtotal());
    expect($validation['valid'])->toBeFalse();
    expect($validation['discount_amount'])->toEqual(0.0);

    // Percentage coupon valid: 10% of 1000 = 100
    $validationPct = $discountService->validateCode('PERCENT10', $cartService->subtotal());
    expect($validationPct['valid'])->toBeTrue();
    expect($validationPct['discount_amount'])->toEqual(100.0);

    // 2. Add second item: subtotal = 2000
    $cartService->add($variant, 1);
    expect($cartService->subtotal())->toEqual(2000.00);

    // Fixed coupon now valid: gives $200 discount
    $validationFixed = $discountService->validateCode('FIXED200', $cartService->subtotal());
    expect($validationFixed['valid'])->toBeTrue();
    expect($validationFixed['discount_amount'])->toEqual(200.0);

    // Percentage coupon: 10% of 2000 = 200, but capped at max discount 150
    $validationCapped = $discountService->validateCode('PERCENT10', $cartService->subtotal());
    expect($validationCapped['valid'])->toBeTrue();
    expect($validationCapped['discount_amount'])->toEqual(150.0);

    // 3. Test final totals calculation via SettingService
    $totals = $settingService->calculateTotals(2000.00, 200.00);
    $discountedSubtotal = 1800.00;
    expect($totals['total'])->toEqual(round($discountedSubtotal + $totals['shipping'] + $totals['tax'], 2));
});

/*
|--------------------------------------------------------------------------
| 5. Coupon Usage Limits QA
|--------------------------------------------------------------------------
*/

test('coupon usage limit enforcement prevents over-redemption across checkouts', function () {
    [, $variant] = createQATestProduct('Wireless Charger', 600.00, 10);
    $discountService = app(DiscountService::class);
    $orderService = app(OrderService::class);

    $coupon = Discount::create([
        'code' => 'LIMITED1',
        'name' => 'Single Use Coupon',
        'type' => 'fixed',
        'value' => 100.00,
        'min_order_amount' => 500.00,
        'usage_limit' => 1,
        'times_used' => 0,
        'is_active' => true,
    ]);

    $cart = [
        $variant->id => [
            'variant_id' => $variant->id,
            'quantity' => 1,
        ],
    ];

    $address = [
        'first_name' => 'Bruce',
        'last_name' => 'Wayne',
        'address' => 'Wayne Manor',
        'city' => 'Gotham',
        'state' => 'NJ',
        'postal_code' => '07001',
        'country' => 'India',
        'phone' => '1122334455',
    ];

    // Order 1 uses coupon -> Succeeds
    $order1 = $orderService->createOrder(
        ['first_name' => 'Bruce', 'last_name' => 'Wayne', 'email' => 'bruce@wayne.test', 'phone' => '1122334455'],
        $address,
        $address,
        $cart,
        ['method' => 'cod', 'status' => 'pending'],
        'LIMITED1'
    );

    $coupon->refresh();
    expect($coupon->times_used)->toBe(1);

    // Attempting to use LIMITED1 on second order -> Rejected!
    $validation = $discountService->validateCode('LIMITED1', 600.00, null, 'other@customer.test');
    expect($validation['valid'])->toBeFalse();
    expect($validation['message'])->toContain('reached its maximum usage limit');
});

/*
|--------------------------------------------------------------------------
| 6. Checkout Validation QA
|--------------------------------------------------------------------------
*/

test('checkout validates empty cart, mandatory fields and payment methods', function () {
    app(CartService::class)->clear();

    // 1. Empty cart redirects away from checkout on mount
    Livewire::test(Checkout::class)
        ->assertRedirect(route('storefront.cart'));

    // 2. Add product to cart and test mandatory validation fields
    [, $variant] = createQATestProduct('Webcam 4K', 3500.00, 5);
    app(CartService::class)->add($variant, 1);

    Livewire::test(Checkout::class)
        ->set('firstName', '')
        ->set('lastName', '')
        ->set('email', '')
        ->set('phone', '')
        ->set('address', '')
        ->set('city', '')
        ->set('state', '')
        ->set('postalCode', '')
        ->set('country', '')
        ->set('paymentMethod', 'invalid_gateway')
        ->call('placeOrder')
        ->assertHasErrors([
            'firstName' => 'required',
            'lastName' => 'required',
            'email' => 'required',
            'phone' => 'required',
            'address' => 'required',
            'city' => 'required',
            'state' => 'required',
            'postalCode' => 'required',
            'country' => 'required',
            'paymentMethod' => 'in',
        ]);
});

/*
|--------------------------------------------------------------------------
| 7. Payment Webhook Callbacks & Idempotency QA
|--------------------------------------------------------------------------
*/

test('payment webhook callback securely verifies signature, transitions order to paid, and handles duplicate webhooks idempotently', function () {
    Mail::fake();

    $secret = 'test_webhook_secret_key_88';
    app(SettingService::class)->set('razorpay_key_secret', $secret);

    [, $variant] = createQATestProduct('Noise Cancelling Earbuds', 2500.00, 5);

    $order = Order::create([
        'order_number' => 'ORD-WH-QA-77',
        'customer_first_name' => 'Wanda',
        'customer_last_name' => 'Maximoff',
        'customer_email' => 'wanda@westview.test',
        'status' => 'pending',
        'payment_status' => 'pending',
        'payment_method' => 'razorpay',
        'subtotal' => 2500.00,
        'total' => 2500.00,
        'shipping_address' => ['first_name' => 'Wanda'],
        'billing_address' => ['first_name' => 'Wanda'],
    ]);

    $webhookData = [
        'event_id' => 'evt_qa_778899',
        'event' => 'payment.captured',
        'payload' => [
            'payment' => [
                'entity' => [
                    'id' => 'pay_rzp_qa_123',
                    'amount' => 250000,
                    'currency' => 'INR',
                    'notes' => [
                        'order_number' => 'ORD-WH-QA-77',
                    ],
                ],
            ],
        ],
    ];

    $payloadJson = json_encode($webhookData);

    // 1. Webhook with invalid signature -> 400 Bad Request
    $badResponse = $this->call(
        'POST',
        '/api/v1/payments/webhook/razorpay',
        [],
        [],
        [],
        [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_RAZORPAY_SIGNATURE' => 'invalid_signature_hash',
        ],
        $payloadJson
    );
    $badResponse->assertStatus(401);

    $order->refresh();
    expect($order->payment_status)->toBe('pending');

    // 2. Webhook with valid signature -> 200 OK & Order Marked Paid
    $validSignature = hash_hmac('sha256', $payloadJson, $secret);

    $goodResponse = $this->call(
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

    $goodResponse->assertStatus(200);
    $goodResponse->assertJson(['status' => 'success', 'payment_status' => 'paid']);

    $order->refresh();
    expect($order->payment_status)->toBe('paid');
    expect($order->status)->toBe('processing');
    expect($order->payment_reference)->toBe('pay_rzp_qa_123');

    // PaymentConfirmedMail must be queued
    Mail::assertQueued(PaymentConfirmedMail::class, function (PaymentConfirmedMail $mail) use ($order) {
        return $mail->hasTo('wanda@westview.test')
            && $mail->order->order_number === $order->order_number;
    });

    // 3. Duplicate Webhook -> Must be Idempotent (200 OK, no duplicate transactions or emails)
    $repeatResponse = $this->call(
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

    $repeatResponse->assertStatus(200);
    $repeatResponse->assertJson(['status' => 'already_processed']);

    // Ensure payment transaction was not duplicated
    expect(PaymentTransaction::where('transaction_id', 'pay_rzp_qa_123')->count())->toBe(1);
    // Ensure email was not sent again
    Mail::assertQueued(PaymentConfirmedMail::class, 1);
});

/*
|--------------------------------------------------------------------------
| 8. Order Status Transitions & Audit History QA
|--------------------------------------------------------------------------
*/

test('order status transitions follow valid lifecycle states, block illegal jumps, and log audit history', function () {
    $admin = User::factory()->create(['name' => 'Fulfillment Agent']);
    $admin->assignRole('Super Administrator');

    $order = Order::create([
        'order_number' => 'ORD-TRANS-QA-01',
        'status' => 'pending',
        'payment_status' => 'pending',
        'subtotal' => 1500.00,
        'total' => 1500.00,
        'shipping_address' => ['first_name' => 'Vision'],
        'billing_address' => ['first_name' => 'Vision'],
    ]);

    // Transition 1: pending -> processing
    $order->transitionTo('processing', 'Payment verified and items sent to warehouse', $admin);
    expect($order->status)->toBe('processing');

    // Transition 2: processing -> shipped
    $order->transitionTo('shipped', 'Shipped via BlueDart tracking #BD123', $admin);
    expect($order->status)->toBe('shipped');

    // Transition 3: shipped -> delivered
    $order->transitionTo('delivered', 'Package delivered and signed', $admin);
    expect($order->status)->toBe('delivered');

    // Illegal jump: delivered -> pending MUST throw DomainException
    expect(function () use ($order, $admin) {
        $order->transitionTo('pending', 'Illegal status revert', $admin);
    })->toThrow(DomainException::class);

    // Verify all status changes are logged in OrderStatusHistory with user name
    $histories = OrderStatusHistory::where('order_id', $order->id)->orderBy('id')->get();
    expect($histories->count())->toBeGreaterThanOrEqual(3);
    expect($histories->pluck('to_status')->all())->toContain('processing', 'shipped', 'delivered');
    expect($histories->last()->user_name)->toBe('Fulfillment Agent');
});

/*
|--------------------------------------------------------------------------
| 9. Customer Data Isolation QA
|--------------------------------------------------------------------------
*/

test('strict customer data isolation prevents customer A from viewing customer B orders and history', function () {
    $customerA = User::factory()->create(['name' => 'Customer Alpha', 'email' => 'alpha@test.com']);
    $customerA->assignRole('Customer');

    $customerB = User::factory()->create(['name' => 'Customer Beta', 'email' => 'beta@test.com']);
    $customerB->assignRole('Customer');

    // Order A belonging to Customer A
    $orderA = Order::create([
        'order_number' => 'ORD-ALPHA-111',
        'customer_id' => $customerA->id,
        'customer_first_name' => 'Customer',
        'customer_last_name' => 'Alpha',
        'customer_email' => 'alpha@test.com',
        'status' => 'processing',
        'payment_status' => 'paid',
        'subtotal' => 1000.00,
        'total' => 1000.00,
        'shipping_address' => ['first_name' => 'Alpha'],
        'billing_address' => ['first_name' => 'Alpha'],
    ]);

    // Order B belonging to Customer B
    $orderB = Order::create([
        'order_number' => 'ORD-BETA-222',
        'customer_id' => $customerB->id,
        'customer_first_name' => 'Customer',
        'customer_last_name' => 'Beta',
        'customer_email' => 'beta@test.com',
        'status' => 'processing',
        'payment_status' => 'paid',
        'subtotal' => 2000.00,
        'total' => 2000.00,
        'shipping_address' => ['first_name' => 'Beta'],
        'billing_address' => ['first_name' => 'Beta'],
    ]);

    // 1. Unauthenticated guest visiting my-orders is redirected to login
    $this->get(route('storefront.orders.index'))
        ->assertRedirect(route('login'));

    // 2. Act as Customer B
    $this->actingAs($customerB);

    // Customer B's order index displays Order B, but NOT Order A
    Livewire::test(CustomerOrderIndex::class)
        ->assertSee('ORD-BETA-222')
        ->assertDontSee('ORD-ALPHA-111');

    // Customer B accessing Order B details -> 200 OK
    Livewire::test(CustomerOrderShow::class, ['orderNumber' => $orderB->order_number])
        ->assertSee('ORD-BETA-222');

    // Customer B attempting to view Customer A's order details -> 404 ModelNotFoundException
    $this->get(route('storefront.orders.show', $orderA->order_number))
        ->assertNotFound();

    // Customer B attempting to access Customer A's order-success page -> 403 Forbidden
    $this->get(route('storefront.order-success', $orderA->order_number))
        ->assertForbidden();
});

/*
|--------------------------------------------------------------------------
| 10. Complete End-to-End Guest Checkout Purchase QA
|--------------------------------------------------------------------------
*/

test('complete end-to-end guest checkout purchase journey', function () {
    Mail::fake();

    [, $variant, $inventory] = createQATestProduct('Guest Portable Speaker', 500.00, 10);

    // Create valid fixed discount coupon
    Discount::create([
        'code' => 'GUESTSAVE50',
        'name' => 'Guest Promo',
        'type' => 'fixed',
        'value' => 50.00,
        'min_order_amount' => 500.00,
        'is_active' => true,
    ]);

    $cartService = app(CartService::class);
    $cartService->clear();

    // 1. Guest adds 2 units to cart (subtotal = $1000)
    $cartService->add($variant, 2);
    expect($cartService->count())->toBe(2);
    expect($cartService->subtotal())->toEqual(1000.00);

    // 2. Guest applies coupon at checkout
    $checkout = Livewire::test(Checkout::class)
        ->set('couponCode', 'GUESTSAVE50')
        ->call('applyCoupon')
        ->assertSet('appliedCoupon.code', 'GUESTSAVE50')
        ->set('firstName', 'Tony')
        ->set('lastName', 'Stark')
        ->set('email', 'tony@starkindustries.test')
        ->set('phone', '9876543210')
        ->set('address', '10880 Malibu Point')
        ->set('city', 'Malibu')
        ->set('state', 'CA')
        ->set('postalCode', '90265')
        ->set('country', 'India')
        ->set('paymentMethod', 'cod');

    expect($checkout->get('discountAmount'))->toEqual(50.00);

    // 3. Place order
    $checkout->call('placeOrder');

    // 4. Verify order created in database
    $order = Order::where('customer_email', 'tony@starkindustries.test')->first();
    expect($order)->not->toBeNull();
    expect($order->customer_id)->toBeNull(); // Guest order
    expect((float) $order->subtotal)->toBe(1000.00);
    expect((float) $order->discount_amount)->toBe(50.00);
    expect($order->status)->toBe('pending');
    expect($order->payment_status)->toBe('pending');
    expect($order->payment_method)->toBe('cod');

    // 5. Verify snapshot integrity in order items
    $orderItem = $order->items->first();
    expect($orderItem)->not->toBeNull();
    expect($orderItem->product_name)->toBe('Guest Portable Speaker');
    expect($orderItem->sku)->toBe($variant->sku);
    expect((int) $orderItem->quantity)->toBe(2);
    expect((float) $orderItem->unit_price)->toBe(500.00);
    expect((float) $orderItem->total)->toBe(1000.00);

    // 6. Verify stock was deducted from 10 to 8
    $inventory->refresh();
    expect($inventory->quantity)->toBe(8);

    // 7. Verify confirmation email was queued for guest customer
    Mail::assertQueued(OrderConfirmationMail::class, function (OrderConfirmationMail $mail) use ($order) {
        return $mail->hasTo('tony@starkindustries.test')
            && $mail->order->order_number === $order->order_number;
    });

    // 8. Verify cart was cleared after purchase
    expect($cartService->count())->toBe(0);

    // 9. Verify guest can view order success page
    Livewire::test(OrderSuccess::class, ['orderNumber' => $order->order_number])
        ->assertSee($order->order_number)
        ->assertSee('Pending (Pay on Delivery)');
});

/*
|--------------------------------------------------------------------------
| 11. Complete End-to-End Logged-In Customer Purchase QA
|--------------------------------------------------------------------------
*/

test('complete end-to-end logged-in customer purchase journey with gateway payment and order history verification', function () {
    Mail::fake();

    $secret = 'rzp_e2e_secret_999';
    app(SettingService::class)->set('razorpay_key_secret', $secret);

    // 1. Registered customer logs in
    $customer = User::factory()->create([
        'name' => 'Diana Prince',
        'email' => 'diana@themyscira.test',
    ]);
    $customer->assignRole('Customer');
    $this->actingAs($customer);

    [, $variant, $inventory] = createQATestProduct('Golden Lasso Smart Bracelet', 3000.00, 5);

    $cartService = app(CartService::class);
    $cartService->clear();
    $cartService->add($variant, 1);

    // 2. Customer performs checkout with razorpay gateway payment
    $gatewayTxnId = 'pay_diana_987654';
    $checkout = Livewire::test(Checkout::class)
        ->set('firstName', 'Diana')
        ->set('lastName', 'Prince')
        ->set('email', 'diana@themyscira.test')
        ->set('phone', '8899001122')
        ->set('address', 'Amazonian Embassy 1')
        ->set('city', 'Gateway City')
        ->set('state', 'DL')
        ->set('postalCode', '110001')
        ->set('country', 'India')
        ->set('paymentMethod', 'razorpay')
        ->set('gatewayTransactionId', $gatewayTxnId)
        ->call('placeOrder');

    // 3. Verify order created attached to customer ID
    $order = Order::where('customer_email', 'diana@themyscira.test')->first();
    expect($order)->not->toBeNull();
    expect($order->customer_id)->toBe($customer->id);
    expect($order->payment_method)->toBe('razorpay');

    // Stock deducted
    $inventory->refresh();
    expect($inventory->quantity)->toBe(4);

    // 4. Gateway webhook callback received
    $webhookData = [
        'event_id' => 'evt_diana_001',
        'event' => 'payment.captured',
        'payload' => [
            'payment' => [
                'entity' => [
                    'id' => $gatewayTxnId,
                    'amount' => (int) ($order->total * 100),
                    'currency' => 'INR',
                    'notes' => [
                        'order_number' => $order->order_number,
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

    // 5. Order is marked paid and processing
    $order->refresh();
    expect($order->payment_status)->toBe('paid');
    expect($order->status)->toBe('processing');

    // PaymentConfirmedMail queued
    Mail::assertQueued(PaymentConfirmedMail::class, function (PaymentConfirmedMail $mail) use ($order) {
        return $mail->hasTo('diana@themyscira.test')
            && $mail->order->order_number === $order->order_number;
    });

    // 6. Customer views order in order history list (/my-orders)
    Livewire::test(CustomerOrderIndex::class)
        ->assertSee($order->order_number);

    // 7. Customer views order details page (/my-orders/{orderNumber})
    Livewire::test(CustomerOrderShow::class, ['orderNumber' => $order->order_number])
        ->assertSee($order->order_number)
        ->assertSee('Golden Lasso Smart Bracelet');
});
