<?php

use App\Livewire\Storefront\Cart;
use App\Livewire\Storefront\Checkout;
use App\Livewire\Storefront\OrderSuccess;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => 'Customer', 'guard_name' => 'web']);
});

if (! function_exists('createTestProductWithStock')) {
    function createTestProductWithStock(string $name, float $price, int $stock): array
    {
        $product = Product::create([
            'name' => $name,
            'slug' => Str::slug($name),
            'sku' => 'SKU-'.strtoupper(Str::random(5)),
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
}

test('cart service prevents adding more than available stock', function () {
    [, $variant] = createTestProductWithStock('Limited Item', 100.00, 2);

    $cartService = app(CartService::class);
    $cartService->add($variant, 2);

    expect($cartService->count())->toBe(2);

    expect(function () use ($cartService, $variant) {
        $cartService->add($variant, 1);
    })->toThrow(RuntimeException::class);
});

test('cart detects inventory shortage and can auto adjust stock', function () {
    [, $variant, $inventory] = createTestProductWithStock('Dynamic Stock Item', 200.00, 5);

    $cartService = app(CartService::class);
    $cartService->add($variant, 4);

    // Simulate stock reduction elsewhere (e.g. inventory sold out down to 2)
    $inventory->update(['quantity' => 2]);

    $validation = $cartService->validateStock();
    expect($validation['has_issues'])->toBeTrue();
    expect($validation['issues'][0]['type'])->toBe('exceeds_stock');

    Livewire::test(Cart::class)
        ->assertSee('Inventory Notice')
        ->assertSee('Auto-adjust to Available Stock')
        ->call('autoAdjustStock')
        ->assertSet('hasStockIssues', false);

    expect($cartService->count())->toBe(2);
});

test('checkout redirects to cart if cart items exceed available stock', function () {
    [, $variant, $inventory] = createTestProductWithStock('Sold Out Item', 300.00, 2);

    app(CartService::class)->add($variant, 2);

    // Drop stock to 0
    $inventory->update(['quantity' => 0]);

    Livewire::test(Checkout::class)
        ->assertRedirect(route('storefront.cart'));
});

test('checkout validates card payment fields when card method is selected', function () {
    [, $variant] = createTestProductWithStock('Card Test Item', 500.00, 10);
    app(CartService::class)->add($variant, 1);

    Livewire::test(Checkout::class)
        ->set('paymentMethod', 'card')
        ->set('firstName', 'John')
        ->set('lastName', 'Doe')
        ->set('email', 'john@example.com')
        ->set('phone', '9876543210')
        ->set('address', '123 Baker Street')
        ->set('city', 'Bangalore')
        ->set('state', 'Karnataka')
        ->set('postalCode', '560001')
        ->set('country', 'India')
        ->set('cardName', '')
        ->set('cardNumber', '123')
        ->set('cardExpiry', 'invalid')
        ->set('cardCvv', '1')
        ->call('placeOrder')
        ->assertHasErrors([
            'cardName' => 'required',
            'cardNumber' => 'min',
            'cardExpiry' => 'regex',
            'cardCvv' => 'min',
        ]);
});

test('checkout validates upi id when upi method is selected', function () {
    [, $variant] = createTestProductWithStock('UPI Test Item', 500.00, 10);
    app(CartService::class)->add($variant, 1);

    Livewire::test(Checkout::class)
        ->set('paymentMethod', 'upi')
        ->set('firstName', 'John')
        ->set('lastName', 'Doe')
        ->set('email', 'john@example.com')
        ->set('phone', '9876543210')
        ->set('address', '123 Baker Street')
        ->set('city', 'Bangalore')
        ->set('state', 'Karnataka')
        ->set('postalCode', '560001')
        ->set('country', 'India')
        ->set('upiId', 'invalid-format-without-at')
        ->call('placeOrder')
        ->assertHasErrors(['upiId' => 'regex']);
});

test('placing order with cash on delivery marks payment as pending and decrements inventory', function () {
    [, $variant, $inventory] = createTestProductWithStock('COD Item', 750.00, 10);
    app(CartService::class)->add($variant, 2);

    Livewire::test(Checkout::class)
        ->set('paymentMethod', 'cod')
        ->set('firstName', 'Alice')
        ->set('lastName', 'Smith')
        ->set('email', 'alice@example.com')
        ->set('phone', '9988776655')
        ->set('address', '45 Park Avenue')
        ->set('city', 'Delhi')
        ->set('state', 'Delhi')
        ->set('postalCode', '110001')
        ->set('country', 'India')
        ->call('placeOrder')
        ->assertHasNoErrors()
        ->assertSessionHas('order_success');

    $order = Order::where('customer_email', 'alice@example.com')->first();
    expect($order)->not->toBeNull();
    expect($order->payment_method)->toBe('cod');
    expect($order->payment_status)->toBe('pending');
    expect($order->payment_reference)->toStartWith('COD-');
    expect($order->subtotal)->toBe('1500.00');
    expect((float) $order->total)->toBe(1770.00);

    // Verify inventory reserved & quantity decremented
    $inventory->refresh();
    expect($inventory->quantity)->toBe(8);
    expect($inventory->reserved_quantity)->toBe(2);

    // Verify cart cleared
    expect(app(CartService::class)->count())->toBe(0);
});

test('placing order with card marks payment as paid and records reference', function () {
    [, $variant] = createTestProductWithStock('Card Order Item', 1200.00, 5);
    app(CartService::class)->add($variant, 1);

    Livewire::test(Checkout::class)
        ->set('paymentMethod', 'card')
        ->set('firstName', 'Bob')
        ->set('lastName', 'Brown')
        ->set('email', 'bob@example.com')
        ->set('phone', '9123456780')
        ->set('address', '77 Sunset Blvd')
        ->set('city', 'Pune')
        ->set('state', 'Maharashtra')
        ->set('postalCode', '411001')
        ->set('country', 'India')
        ->set('cardName', 'Bob Brown')
        ->set('cardNumber', '4111111111111111')
        ->set('cardExpiry', '12/28')
        ->set('cardCvv', '999')
        ->call('placeOrder')
        ->assertHasNoErrors()
        ->assertSessionHas('order_success');

    $order = Order::where('customer_email', 'bob@example.com')->first();
    expect($order)->not->toBeNull();
    expect($order->payment_method)->toBe('card');
    expect($order->payment_status)->toBe('paid');
    expect($order->payment_reference)->toStartWith('TXN-CARD-');
});

test('placing order with upi marks payment as paid and records upi reference', function () {
    [, $variant] = createTestProductWithStock('UPI Order Item', 800.00, 5);
    app(CartService::class)->add($variant, 1);

    Livewire::test(Checkout::class)
        ->set('paymentMethod', 'upi')
        ->set('firstName', 'Charlie')
        ->set('lastName', 'Green')
        ->set('email', 'charlie@example.com')
        ->set('phone', '9876543219')
        ->set('address', '88 River View')
        ->set('city', 'Chennai')
        ->set('state', 'Tamil Nadu')
        ->set('postalCode', '600001')
        ->set('country', 'India')
        ->set('upiId', 'charlie@okaxis')
        ->call('placeOrder')
        ->assertHasNoErrors()
        ->assertSessionHas('order_success');

    $order = Order::where('customer_email', 'charlie@example.com')->first();
    expect($order)->not->toBeNull();
    expect($order->payment_method)->toBe('upi');
    expect($order->payment_status)->toBe('paid');
    expect($order->payment_reference)->toStartWith('UPI-');
});

test('order success page renders order details and payment metadata', function () {
    [$product, $variant] = createTestProductWithStock('Confirmed Item', 999.00, 10);

    $cart = [
        $variant->id => [
            'variant_id' => $variant->id,
            'quantity' => 1,
        ],
    ];

    $order = app(OrderService::class)->createOrder(
        [
            'first_name' => 'Emma',
            'last_name' => 'Watson',
            'email' => 'emma@example.com',
            'phone' => '9876500000',
        ],
        [
            'first_name' => 'Emma',
            'last_name' => 'Watson',
            'address' => '10 Downing St',
            'city' => 'London',
            'state' => 'Greater London',
            'postal_code' => 'SW1A 2AA',
            'country' => 'UK',
            'phone' => '9876500000',
        ],
        [
            'first_name' => 'Emma',
            'last_name' => 'Watson',
            'address' => '10 Downing St',
            'city' => 'London',
            'state' => 'Greater London',
            'postal_code' => 'SW1A 2AA',
            'country' => 'UK',
            'phone' => '9876500000',
        ],
        $cart,
        [
            'method' => 'card',
            'status' => 'paid',
            'reference' => 'TXN-CARD-TEST1234',
            'paid_at' => now(),
        ]
    );

    Livewire::test(OrderSuccess::class, ['orderNumber' => $order->order_number])
        ->assertSee($order->order_number)
        ->assertSee('Order Confirmed')
        ->assertSee('Credit / Debit Card')
        ->assertSee('Paid')
        ->assertSee('TXN-CARD-TEST1234')
        ->assertSee('10 Downing St')
        ->assertSee('London');
});
