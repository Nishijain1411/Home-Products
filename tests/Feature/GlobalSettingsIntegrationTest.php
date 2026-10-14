<?php

use App\Livewire\Storefront\Cart;
use App\Livewire\Storefront\Checkout;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\CartService;
use App\Services\OrderService;
use App\Services\SettingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => 'Customer']);
    Role::firstOrCreate(['name' => 'Admin']);
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

test('currency formatting reflects in helper and format_price across different configurations', function () {
    $service = app(SettingService::class);

    // Default INR
    expect(format_price(1500.50))->toBe('₹1,500.50');
    expect(currency_symbol())->toBe('₹');

    // Change to USD with space after symbol
    $service->set('default_currency', 'USD', 'currency');
    $service->set('currency_symbol', '$', 'currency');
    $service->set('price_display_format', 'symbol_space', 'currency');
    $service->clearCache();

    expect(format_price(1500.50))->toBe('$ 1,500.50');
    expect(currency_symbol())->toBe('$');

    // Change to EUR with symbol after and German format (1.500,50 €)
    $service->set('currency_symbol', '€', 'currency');
    $service->set('currency_symbol_position', 'after', 'currency');
    $service->set('thousands_separator', '.', 'currency');
    $service->set('decimal_separator', ',', 'currency');
    $service->clearCache();

    expect(format_price(1500.50))->toBe('1.500,50 €');
});

test('tax calculations handle exclusive, inclusive, and tax on shipping correctly', function () {
    $service = app(SettingService::class);

    // 1. Tax Exclusive (18% on 100 subtotal)
    $service->set('enable_tax', true, 'tax');
    $service->set('prices_include_tax', false, 'tax');
    $service->set('default_tax_rate', 18.0, 'tax');
    $service->set('tax_shipping', false, 'tax');
    $service->set('enable_free_shipping', true, 'shipping');
    $service->set('free_shipping_threshold', 500.0, 'shipping');
    $service->set('default_shipping_fee', 50.0, 'shipping');
    $service->clearCache();

    // Subtotal 100 (< 500, so shipping is 50). Tax is 18% of 100 = 18. Total = 100 + 50 + 18 = 168
    $totals = $service->calculateTotals(100.0);
    expect($totals['subtotal'])->toBe(100.0);
    expect($totals['shipping'])->toBe(50.0);
    expect($totals['tax'])->toBe(18.0);
    expect($totals['total'])->toBe(168.0);
    expect($totals['tax_inclusive'])->toBeFalse();

    // 2. Tax Exclusive with Tax on Shipping (18% on (100 + 50) = 27)
    $service->set('tax_shipping', true, 'tax');
    $service->clearCache();

    $totals = $service->calculateTotals(100.0);
    expect($totals['tax'])->toBe(27.0);
    expect($totals['total'])->toBe(177.0);

    // 3. Tax Inclusive (Subtotal 118 with 18% tax included)
    $service->set('prices_include_tax', true, 'tax');
    $service->set('tax_shipping', false, 'tax');
    $service->set('default_shipping_fee', 0.0, 'shipping');
    $service->clearCache();

    $totals = $service->calculateTotals(118.0);
    expect($totals['tax'])->toBe(18.0);
    expect($totals['total'])->toBe(118.0);
    expect($totals['tax_inclusive'])->toBeTrue();

    // 4. Tax Disabled
    $service->set('enable_tax', false, 'tax');
    $service->clearCache();

    $totals = $service->calculateTotals(100.0);
    expect($totals['tax'])->toBe(0.0);
    expect($totals['total'])->toBe(100.0);
});

test('shipping rules calculate free shipping threshold and flat rate fees correctly', function () {
    $service = app(SettingService::class);

    $service->set('enable_free_shipping', true, 'shipping');
    $service->set('free_shipping_threshold', 500.0, 'shipping');
    $service->set('default_shipping_fee', 75.0, 'shipping');
    $service->clearCache();

    // Below threshold -> charges 75
    expect($service->calculateShipping(499.0))->toBe(75.0);

    // At or above threshold -> free (0)
    expect($service->calculateShipping(500.0))->toBe(0.0);
    expect($service->calculateShipping(1000.0))->toBe(0.0);

    // Free shipping disabled -> always charges 75
    $service->set('enable_free_shipping', false, 'shipping');
    $service->clearCache();

    expect($service->calculateShipping(1000.0))->toBe(75.0);
});

test('order service uses configured order prefix and digits', function () {
    $service = app(SettingService::class);
    $service->set('order_number_prefix', 'SHOP-', 'other');
    $service->set('order_number_digits', 8, 'other');
    $service->clearCache();

    [, $variant] = createTestProductWithStock('Test Product', 100, 20);

    $orderService = app(OrderService::class);
    $order = $orderService->createOrder(
        [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane@example.com',
            'phone' => '1234567890',
        ],
        [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'address' => '123 Test Rd',
            'city' => 'Metropolis',
            'state' => 'NY',
            'postal_code' => '10001',
            'country' => 'USA',
            'phone' => '1234567890',
        ],
        [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'address' => '123 Test Rd',
            'city' => 'Metropolis',
            'state' => 'NY',
            'postal_code' => '10001',
            'country' => 'USA',
            'phone' => '1234567890',
        ],
        [
            (string) $variant->id => [
                'variant_id' => $variant->id,
                'quantity' => 2,
            ],
        ],
        ['method' => 'cod']
    );

    expect($order->order_number)->toStartWith('SHOP-');
    // Order number format is SHOP-YYYYMMDD-XXXXXXXX (8 random characters)
    $parts = explode('-', $order->order_number);
    expect(count($parts))->toBe(3);
    expect(strlen($parts[2]))->toBe(8);
});

test('checkout validates and shows enabled payment methods and instructions', function () {
    $service = app(SettingService::class);
    $service->set('enable_cod', false, 'payment');
    $service->set('enable_card', true, 'payment');
    $service->set('enable_upi', true, 'payment');
    $service->set('upi_instructions', 'Scan our official UPI QR Code', 'payment');
    $service->clearCache();

    [, $variant] = createTestProductWithStock('Payment Product', 200, 10);

    app(CartService::class)->add($variant, 1);

    $test = Livewire::test(Checkout::class)
        ->assertSet('enableCod', false)
        ->assertSet('enableCard', true)
        ->assertSet('enableUpi', true)
        ->assertSet('upiInstructions', 'Scan our official UPI QR Code')
        ->assertSet('paymentMethod', 'card') // Automatically defaulted to first enabled
        ->set('paymentMethod', 'cod')
        ->call('placeOrder')
        ->assertHasErrors(['paymentMethod']); // COD was disabled, so validation rejects it
});

test('guest checkout toggle enforces authentication when disabled', function () {
    $service = app(SettingService::class);
    $service->set('guest_checkout', false, 'other');
    $service->clearCache();

    [, $variant] = createTestProductWithStock('Guest Product', 150, 5);

    app(CartService::class)->add($variant, 1);

    // Unauthenticated guest should be redirected to login
    Livewire::test(Checkout::class)
        ->assertRedirect(route('login'));

    // Authenticated customer can proceed
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(Checkout::class)
        ->assertOk();
});

test('storefront layout displays maintenance mode for visitors but allows admin access', function () {
    $service = app(SettingService::class);
    $service->set('maintenance_mode', true, 'general');
    $service->set('maintenance_message', 'Store updating for Diwali Sale!', 'general');
    $service->clearCache();

    // Guest visitor sees maintenance message
    $this->get(route('storefront.home'))
        ->assertOk()
        ->assertSee('Under Scheduled Maintenance')
        ->assertSee('Store updating for Diwali Sale!');

    // Admin user can still view the storefront with admin banner
    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    $this->actingAs($admin)
        ->get(route('storefront.home'))
        ->assertOk()
        ->assertSee('Maintenance mode is currently ACTIVE')
        ->assertDontSee('Under Scheduled Maintenance');
});

test('customer orders apply taxes adding tax amount to subtotal and persisting in order', function () {
    $service = app(SettingService::class);
    $service->set('enable_tax', true, 'tax');
    $service->set('prices_include_tax', false, 'tax');
    $service->set('default_tax_rate', 18.0, 'tax');
    $service->set('tax_name', 'GST', 'tax');
    $service->set('tax_shipping', false, 'tax');
    $service->set('enable_free_shipping', true, 'shipping');
    $service->set('free_shipping_threshold', 500.0, 'shipping');
    $service->set('default_shipping_fee', 0.0, 'shipping');
    $service->clearCache();

    [, $variant] = createTestProductWithStock('Taxable Product', 100.0, 10);
    app(CartService::class)->add($variant, 1);

    // Verify cart component reflects tax and total
    Livewire::test(Cart::class)
        ->assertSet('subtotal', 100.0)
        ->assertSet('taxAmount', 18.0)
        ->assertSet('total', 118.0)
        ->assertSee('GST (18%)')
        ->assertSee('₹18.00')
        ->assertSee('₹118.00');

    // Verify checkout component reflects tax and total
    Livewire::test(Checkout::class)
        ->assertSet('subtotal', 100.0)
        ->assertSet('taxAmount', 18.0)
        ->assertSet('total', 118.0)
        ->assertSee('GST (18%)')
        ->set('firstName', 'Test')
        ->set('lastName', 'Customer')
        ->set('email', 'customer@example.com')
        ->set('phone', '9876543210')
        ->set('address', '123 Main St')
        ->set('city', 'Mumbai')
        ->set('state', 'MH')
        ->set('postalCode', '400001')
        ->set('country', 'India')
        ->set('paymentMethod', 'cod')
        ->call('placeOrder');

    $order = Order::latest()->first();
    expect($order)->not->toBeNull();
    expect((float) $order->subtotal)->toBe(100.0);
    expect((float) $order->tax_amount)->toBe(18.0);
    expect((float) $order->total)->toBe(118.0);
});

test('date formatting helper reflects configured timezone and date format', function () {
    $service = app(SettingService::class);
    $service->set('date_format', 'd/m/Y', 'general');
    $service->set('timezone', 'Asia/Kolkata', 'general');
    $service->clearCache();

    $utcDate = Carbon::create(2026, 10, 13, 10, 0, 0, 'UTC');

    expect(format_date($utcDate))->toBe('13/10/2026');
    expect(format_date($utcDate, true))->toBe('13/10/2026, 03:30 PM');

    $service->set('date_format', 'F j, Y', 'general');
    $service->clearCache();

    expect(format_date($utcDate))->toBe('October 13, 2026');
});

test('business address helper concatenates address fields', function () {
    $service = app(SettingService::class);
    $service->set('address_line1', '456 Tech Park', 'general');
    $service->set('address_line2', 'Tower B', 'general');
    $service->set('city', 'Bengaluru', 'general');
    $service->set('state', 'Karnataka', 'general');
    $service->set('postal_code', '560001', 'general');
    $service->set('country', 'India', 'general');
    $service->clearCache();

    expect(business_address())->toBe('456 Tech Park, Tower B, Bengaluru, Karnataka, 560001, India');
});

test('runtime mail configuration is applied from settings', function () {
    $service = app(SettingService::class);
    $service->set('mail_driver', 'smtp', 'email');
    $service->set('mail_host', 'smtp.custom-domain.test', 'email');
    $service->set('mail_port', 465, 'email');
    $service->set('mail_encryption', 'ssl', 'email');
    $service->set('mail_from_address', 'orders@customstore.com', 'email');
    $service->set('mail_from_name', 'Custom Store Support', 'email');
    $service->clearCache();

    $service->applyMailConfig();

    expect(config('mail.mailers.smtp.host'))->toBe('smtp.custom-domain.test');
    expect(config('mail.mailers.smtp.port'))->toBe(465);
    expect(config('mail.mailers.smtp.encryption'))->toBe('ssl');
    expect(config('mail.from.address'))->toBe('orders@customstore.com');
    expect(config('mail.from.name'))->toBe('Custom Store Support');
});
