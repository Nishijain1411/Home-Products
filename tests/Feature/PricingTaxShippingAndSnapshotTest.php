<?php

use App\Livewire\Storefront\Checkout;
use App\Models\Discount;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\CartService;
use App\Services\DiscountService;
use App\Services\OrderService;
use App\Services\SettingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => 'Customer', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
});

function createPricingTestProduct(string $name = 'Leather Backpack', float $price = 1000.00, int $stock = 20, string $sku = 'BAG-001'): array
{
    $product = Product::create([
        'name' => $name,
        'slug' => Str::slug($name).'-'.Str::random(5),
        'sku' => $sku,
        'price' => $price,
        'is_active' => true,
    ]);

    $variant = ProductVariant::create([
        'product_id' => $product->id,
        'title' => 'Standard',
        'sku' => $sku.'-STD',
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

test('server authoritatively calculates shipping and tax irrespective of client manipulation', function () {
    $settings = app(SettingService::class);
    $settings->set('enable_tax', true);
    $settings->set('default_tax_rate', 10.0); // 10% tax
    $settings->set('prices_include_tax', false);
    $settings->set('enable_free_shipping', true);
    $settings->set('free_shipping_threshold', 1500.0);
    $settings->set('default_shipping_fee', 100.0);

    [, $variant] = createPricingTestProduct('Taxed Item', 1000.00, 10);
    $cartService = app(CartService::class);
    $cartService->add($variant, 1); // Subtotal: 1000 (< 1500 threshold, so shipping is 100)

    // Subtotal: 1000.00
    // Shipping: 100.00
    // Tax (10% of 1000): 100.00
    // Total: 1200.00
    $orderService = app(OrderService::class);
    $customerData = [
        'first_name' => 'Bruce',
        'last_name' => 'Banner',
        'email' => 'bruce@avengers.test',
        'phone' => '9876543210',
    ];
    $address = [
        'first_name' => 'Bruce',
        'last_name' => 'Banner',
        'address' => 'Avengers Tower',
        'city' => 'New York',
        'state' => 'NY',
        'postal_code' => '10001',
        'country' => 'India',
        'phone' => '9876543210',
    ];

    // Attempting to send manipulated client totals in payment data
    $order = $orderService->createOrder(
        $customerData,
        $address,
        $address,
        $cartService->get(),
        ['method' => 'cod', 'status' => 'pending', 'total' => 1.00] // Ignored
    );

    expect((float) $order->subtotal)->toBe(1000.00);
    expect((float) $order->shipping_amount)->toBe(100.00);
    expect((float) $order->tax_amount)->toBe(100.00);
    expect((float) $order->total)->toBe(1200.00);

    // Now test free shipping threshold: add another item to make subtotal >= 1500
    $cartService->clear();
    $cartService->add($variant, 2); // Subtotal: 2000 (>= 1500 threshold, so free shipping)

    $order2 = $orderService->createOrder(
        $customerData,
        $address,
        $address,
        $cartService->get(),
        ['method' => 'cod', 'status' => 'pending']
    );

    expect((float) $order2->subtotal)->toBe(2000.00);
    expect((float) $order2->shipping_amount)->toBe(0.00); // Free shipping
    expect((float) $order2->tax_amount)->toBe(200.00); // 10% of 2000
    expect((float) $order2->total)->toBe(2200.00);
});

test('coupon validation enforces minimum subtotal, expiry, and usage limits during checkout', function () {
    $discountService = app(DiscountService::class);

    // 1. Minimum order amount check
    $minCoupon = Discount::create([
        'code' => 'MIN500',
        'name' => 'Min 500 Discount',
        'type' => 'fixed',
        'value' => 50.00,
        'min_order_amount' => 500.00,
        'is_active' => true,
    ]);

    $valFail = $discountService->validateCode('MIN500', 300.00);
    expect($valFail['valid'])->toBeFalse();
    expect($valFail['message'])->toContain('minimum subtotal of ₹500.00');

    $valPass = $discountService->validateCode('MIN500', 600.00);
    expect($valPass['valid'])->toBeTrue();
    expect($valPass['discount_amount'])->toBe(50.00);

    // 2. Expiry check
    $expiredCoupon = Discount::create([
        'code' => 'EXPIRED',
        'name' => 'Expired Coupon',
        'type' => 'percentage',
        'value' => 10.00,
        'expires_at' => now()->subDay(),
        'is_active' => true,
    ]);

    $valExp = $discountService->validateCode('EXPIRED', 1000.00);
    expect($valExp['valid'])->toBeFalse();
    expect($valExp['message'])->toContain('expired');

    // 3. Overall Usage Limit check
    $limitedCoupon = Discount::create([
        'code' => 'LIMIT1',
        'name' => 'One Time Coupon',
        'type' => 'fixed',
        'value' => 100.00,
        'usage_limit' => 1,
        'times_used' => 1,
        'is_active' => true,
    ]);

    $valLimit = $discountService->validateCode('LIMIT1', 1000.00);
    expect($valLimit['valid'])->toBeFalse();
    expect($valLimit['message'])->toContain('maximum usage limit');

    // 4. Per-customer usage limit check
    $customerCoupon = Discount::create([
        'code' => 'ONCEPERUSER',
        'name' => 'Once Per Customer',
        'type' => 'fixed',
        'value' => 50.00,
        'usage_limit_per_customer' => 1,
        'is_active' => true,
    ]);

    $user = User::factory()->create(['email' => 'loyal@customer.test']);

    // First redemption
    [, $variant] = createPricingTestProduct('Coupon Item', 600.00, 10);
    $order = Order::create([
        'order_number' => 'ORD-COUPON-01',
        'customer_id' => $user->id,
        'customer_email' => $user->email,
        'status' => 'pending',
        'subtotal' => 600.00,
        'total' => 600.00,
        'shipping_address' => ['first_name' => 'Loyal'],
        'billing_address' => ['first_name' => 'Loyal'],
    ]);

    $discountService->recordRedemption($customerCoupon, $order, 50.00, $user->id, $user->email);

    // Subsequent redemption attempt by same user must be blocked
    $valCustomerLimit = $discountService->validateCode('ONCEPERUSER', 600.00, $user->id, $user->email);
    expect($valCustomerLimit['valid'])->toBeFalse();
    expect($valCustomerLimit['message'])->toContain('already reached the redemption limit');
});

test('order item snapshot captures product name, SKU, variant, quantity, and unit price permanently', function () {
    [$product, $variant] = createPricingTestProduct('Original Handcrafted Desk', 4500.00, 5, 'DESK-ORIG');

    $orderService = app(OrderService::class);
    $cart = [
        $variant->id => [
            'variant_id' => $variant->id,
            'quantity' => 1,
        ],
    ];

    $order = $orderService->createOrder(
        [
            'first_name' => 'Tony',
            'last_name' => 'Stark',
            'email' => 'tony@stark.test',
            'phone' => '9998887776',
        ],
        [
            'first_name' => 'Tony',
            'last_name' => 'Stark',
            'address' => '10880 Malibu Point',
            'city' => 'Malibu',
            'state' => 'CA',
            'postal_code' => '90265',
            'country' => 'India',
            'phone' => '9998887776',
        ],
        [
            'first_name' => 'Tony',
            'last_name' => 'Stark',
            'address' => '10880 Malibu Point',
            'city' => 'Malibu',
            'state' => 'CA',
            'postal_code' => '90265',
            'country' => 'India',
            'phone' => '9998887776',
        ],
        $cart,
        ['method' => 'card', 'status' => 'paid']
    );

    $orderItem = $order->items()->first();
    expect($orderItem->product_name)->toBe('Original Handcrafted Desk');
    expect($orderItem->variant_name)->toBe('Standard');
    expect($orderItem->sku)->toBe('DESK-ORIG-STD');
    expect((float) $orderItem->unit_price)->toBe(4500.00);
    expect((float) $orderItem->total)->toBe(4500.00);

    // 1. Later, the merchant renames the product, changes SKU, and triples the price in the catalogue
    $product->update([
        'name' => 'Brand New Ultra Modern Desk 2027',
        'sku' => 'DESK-MODERN-2027',
        'price' => 15000.00,
    ]);
    $variant->update([
        'title' => 'Deluxe Titanium Edition',
        'sku' => 'DESK-MODERN-DELUXE',
        'price' => 15000.00,
    ]);

    // The historical snapshot MUST remain completely unchanged!
    $orderItem->refresh();
    expect($orderItem->product_name)->toBe('Original Handcrafted Desk');
    expect($orderItem->variant_name)->toBe('Standard');
    expect($orderItem->sku)->toBe('DESK-ORIG-STD');
    expect((float) $orderItem->unit_price)->toBe(4500.00);
    expect((float) $orderItem->total)->toBe(4500.00);

    // 2. Later, the merchant completely deletes the product and variant from the catalogue
    $variant->delete();
    $product->delete();

    // The historical order item still exists, retaining all snapshot data without errors
    $orderItem->refresh();
    expect($orderItem->product_name)->toBe('Original Handcrafted Desk');
    expect($orderItem->product_id)->toBeNull();
    expect($orderItem->product_variant_id)->toBeNull();
    expect((float) $orderItem->unit_price)->toBe(4500.00);
    expect((float) $orderItem->total)->toBe(4500.00);
    expect((float) $order->fresh()->subtotal)->toBe(4500.00);
});

test('cart dynamically detects catalogue price changes and updates checkout pricing accordingly', function () {
    [$product, $variant] = createPricingTestProduct('Evolving Price Gadget', 200.00, 10);
    $cartService = app(CartService::class);

    // Customer puts item in cart at 200.00
    $cartService->add($variant, 2);
    expect($cartService->subtotal())->toBe(400.00);

    // Admin raises catalogue price to 300.00
    $product->update(['price' => 300.00]);
    $variant->update(['price' => 300.00]);

    // When cart is retrieved again (e.g. customer opens checkout)
    $priceChanges = $cartService->detectPriceChanges();
    expect($priceChanges)->toHaveCount(1);
    expect($priceChanges[0]['previous_price'])->toBe(200.00);
    expect($priceChanges[0]['current_price'])->toBe(300.00);

    // The cart subtotal updates to match authoritative catalogue pricing (2 * 300 = 600)
    expect($cartService->subtotal())->toBe(600.00);

    // Order created using this cart records unit_price of 300.00
    $order = app(OrderService::class)->createOrder(
        [
            'first_name' => 'Wanda',
            'last_name' => 'Maximoff',
            'email' => 'wanda@avengers.test',
            'phone' => '9876543210',
        ],
        [
            'first_name' => 'Wanda',
            'last_name' => 'Maximoff',
            'address' => 'Westview St',
            'city' => 'Westview',
            'state' => 'NJ',
            'postal_code' => '07001',
            'country' => 'India',
            'phone' => '9876543210',
        ],
        [
            'first_name' => 'Wanda',
            'last_name' => 'Maximoff',
            'address' => 'Westview St',
            'city' => 'Westview',
            'state' => 'NJ',
            'postal_code' => '07001',
            'country' => 'India',
            'phone' => '9876543210',
        ],
        $cartService->get(),
        ['method' => 'cod', 'status' => 'pending']
    );

    expect((float) $order->subtotal)->toBe(600.00);
    $item = $order->items()->first();
    expect((float) $item->unit_price)->toBe(300.00);
    expect((float) $item->total)->toBe(600.00);
});
