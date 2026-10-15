<?php

use App\Livewire\Admin\Discounts\Create as DiscountCreate;
use App\Livewire\Admin\Discounts\Edit as DiscountEdit;
use App\Livewire\Admin\Discounts\Index as DiscountIndex;
use App\Livewire\Storefront\Cart;
use App\Livewire\Storefront\Checkout;
use App\Models\Discount;
use App\Models\DiscountRedemption;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\CartService;
use App\Services\DiscountService;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => 'Customer', 'guard_name' => 'web']);
});

if (! function_exists('createDiscountTestProduct')) {
    function createDiscountTestProduct(string $name, float $price, int $stock): array
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

/*
|--------------------------------------------------------------------------
| Admin CRUD & Management Tests
|--------------------------------------------------------------------------
*/

test('admin discounts screen requires authentication and renders for authenticated users', function () {
    $this->get(route('admin.discounts.index'))
        ->assertRedirect(route('login'));

    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.discounts.index'))
        ->assertOk()
        ->assertSee('Discount Codes');
});

test('admin can create a percentage discount with rules', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(DiscountCreate::class)
        ->set('code', 'summer20')
        ->set('name', 'Summer Sale 20%')
        ->set('description', '20% discount on summer collection')
        ->set('type', 'percentage')
        ->set('value', '20')
        ->set('min_order_amount', '50')
        ->set('max_discount_amount', '30')
        ->set('usage_limit', '100')
        ->set('usage_limit_per_customer', '2')
        ->set('is_active', true)
        ->call('save')
        ->assertRedirect(route('admin.discounts.index'));

    $discount = Discount::where('code', 'SUMMER20')->first();
    expect($discount)->not->toBeNull()
        ->and($discount->code)->toBe('SUMMER20')
        ->and($discount->type)->toBe('percentage')
        ->and((float) $discount->value)->toBe(20.0)
        ->and((float) $discount->min_order_amount)->toBe(50.0)
        ->and((float) $discount->max_discount_amount)->toBe(30.0)
        ->and($discount->usage_limit)->toBe(100)
        ->and($discount->usage_limit_per_customer)->toBe(2);
});

test('admin can create a fixed amount discount', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(DiscountCreate::class)
        ->set('code', 'FLAT15')
        ->set('name', 'Flat $15 Off')
        ->set('type', 'fixed')
        ->set('value', '15.00')
        ->set('min_order_amount', '60.00')
        ->set('is_active', true)
        ->call('save')
        ->assertRedirect(route('admin.discounts.index'));

    $discount = Discount::where('code', 'FLAT15')->first();
    expect($discount)->not->toBeNull()
        ->and($discount->type)->toBe('fixed')
        ->and((float) $discount->value)->toBe(15.0)
        ->and((float) $discount->min_order_amount)->toBe(60.0)
        ->and($discount->max_discount_amount)->toBeNull();
});

test('discount validation enforces uniqueness and valid percentage range', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Discount::create([
        'code' => 'EXISTING10',
        'name' => 'Existing Discount',
        'type' => 'percentage',
        'value' => 10,
        'is_active' => true,
    ]);

    // Test duplicate code
    Livewire::test(DiscountCreate::class)
        ->set('code', 'EXISTING10')
        ->set('name', 'New Discount')
        ->set('type', 'percentage')
        ->set('value', '10')
        ->call('save')
        ->assertHasErrors(['code']);

    // Test percentage > 100
    Livewire::test(DiscountCreate::class)
        ->set('code', 'INVALID150')
        ->set('name', 'Invalid Percent')
        ->set('type', 'percentage')
        ->set('value', '150')
        ->call('save')
        ->assertHasErrors(['value']);

    // Test negative or zero value
    Livewire::test(DiscountCreate::class)
        ->set('code', 'ZERO')
        ->set('name', 'Zero')
        ->set('type', 'percentage')
        ->set('value', '0')
        ->call('save')
        ->assertHasErrors(['value']);
});

test('admin can edit an existing discount', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $discount = Discount::create([
        'code' => 'OLDCODE',
        'name' => 'Old Name',
        'type' => 'percentage',
        'value' => 10,
        'is_active' => true,
    ]);

    Livewire::test(DiscountEdit::class, ['discount' => $discount])
        ->set('name', 'Updated Name')
        ->set('value', '15')
        ->call('save')
        ->assertRedirect(route('admin.discounts.index'));

    $discount->refresh();
    expect($discount->name)->toBe('Updated Name')
        ->and((float) $discount->value)->toBe(15.0);
});

test('admin can toggle discount active status', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $discount = Discount::create([
        'code' => 'TOGGLEME',
        'name' => 'Toggle Me',
        'type' => 'fixed',
        'value' => 10,
        'is_active' => true,
    ]);

    Livewire::test(DiscountIndex::class)
        ->call('toggleStatus', $discount->id);

    $discount->refresh();
    expect($discount->is_active)->toBeFalse();

    Livewire::test(DiscountIndex::class)
        ->call('toggleStatus', $discount->id);

    $discount->refresh();
    expect($discount->is_active)->toBeTrue();
});

test('admin can delete a discount', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $discount = Discount::create([
        'code' => 'DELETEME',
        'name' => 'Delete Me',
        'type' => 'fixed',
        'value' => 10,
        'is_active' => true,
    ]);

    Livewire::test(DiscountIndex::class)
        ->call('confirmDelete', $discount->id)
        ->assertSet('showDeleteModal', true)
        ->assertSet('deleteId', $discount->id)
        ->call('delete');

    expect(Discount::find($discount->id))->toBeNull();
});

/*
|--------------------------------------------------------------------------
| Discount Rules & Calculations
|--------------------------------------------------------------------------
*/

test('percentage discount calculates correctly and respects maximum cap', function () {
    $discountWithoutCap = Discount::create([
        'code' => 'PCT20',
        'name' => '20% Off',
        'type' => 'percentage',
        'value' => 20,
        'is_active' => true,
    ]);

    expect($discountWithoutCap->calculateDiscountAmount(100.0))->toBe(20.0)
        ->and($discountWithoutCap->calculateDiscountAmount(250.0))->toBe(50.0);

    $discountWithCap = Discount::create([
        'code' => 'PCT20CAP',
        'name' => '20% Off Cap 30',
        'type' => 'percentage',
        'value' => 20,
        'max_discount_amount' => 30.0,
        'is_active' => true,
    ]);

    expect($discountWithCap->calculateDiscountAmount(100.0))->toBe(20.0)
        ->and($discountWithCap->calculateDiscountAmount(250.0))->toBe(30.0); // Capped to 30
});

test('fixed discount calculation caps at order subtotal', function () {
    $fixedDiscount = Discount::create([
        'code' => 'FIXED50',
        'name' => '$50 Off',
        'type' => 'fixed',
        'value' => 50,
        'is_active' => true,
    ]);

    expect($fixedDiscount->calculateDiscountAmount(100.0))->toBe(50.0)
        ->and($fixedDiscount->calculateDiscountAmount(30.0))->toBe(30.0); // Cannot exceed subtotal
});

test('minimum order value requirement is enforced', function () {
    $discount = Discount::create([
        'code' => 'MIN100',
        'name' => 'Min 100 Order',
        'type' => 'percentage',
        'value' => 10,
        'min_order_amount' => 100.0,
        'is_active' => true,
    ]);

    $service = app(DiscountService::class);

    $failValidation = $service->validateCode('MIN100', 80.0);
    expect($failValidation['valid'])->toBeFalse()
        ->and($failValidation['message'])->toContain('minimum subtotal');

    $passValidation = $service->validateCode('MIN100', 120.0);
    expect($passValidation['valid'])->toBeTrue()
        ->and($passValidation['discount_amount'])->toBe(12.0);
});

test('discount validity dates prevent usage before start and after expiry', function () {
    $expiredDiscount = Discount::create([
        'code' => 'EXPIRED',
        'name' => 'Expired Coupon',
        'type' => 'percentage',
        'value' => 10,
        'expires_at' => now()->subDay(),
        'is_active' => true,
    ]);

    $service = app(DiscountService::class);
    $expiredCheck = $service->validateCode('EXPIRED', 100.0);
    expect($expiredCheck['valid'])->toBeFalse()
        ->and($expiredCheck['message'])->toContain('expired');

    $futureDiscount = Discount::create([
        'code' => 'FUTURE',
        'name' => 'Future Coupon',
        'type' => 'percentage',
        'value' => 10,
        'starts_at' => now()->addDay(),
        'is_active' => true,
    ]);

    $futureCheck = $service->validateCode('FUTURE', 100.0);
    expect($futureCheck['valid'])->toBeFalse()
        ->and($futureCheck['message'])->toContain('not yet active');
});

test('inactive discount cannot be applied', function () {
    Discount::create([
        'code' => 'DISABLED',
        'name' => 'Disabled Coupon',
        'type' => 'percentage',
        'value' => 10,
        'is_active' => false,
    ]);

    $service = app(DiscountService::class);
    $check = $service->validateCode('DISABLED', 100.0);
    expect($check['valid'])->toBeFalse()
        ->and($check['message'])->toContain('inactive');
});

/*
|--------------------------------------------------------------------------
| Usage Limits & Per-Customer Limits
|--------------------------------------------------------------------------
*/

test('total usage limit prevents redemption when limit reached', function () {
    $discount = Discount::create([
        'code' => 'MAX2USES',
        'name' => 'Max 2 Uses',
        'type' => 'fixed',
        'value' => 10,
        'usage_limit' => 2,
        'times_used' => 2,
        'is_active' => true,
    ]);

    $service = app(DiscountService::class);
    $check = $service->validateCode('MAX2USES', 100.0);
    expect($check['valid'])->toBeFalse()
        ->and($check['message'])->toContain('usage limit');
});

test('per-customer usage limit prevents registered customer and guest email from exceeding limit', function () {
    $discount = Discount::create([
        'code' => 'ONCEPERCUST',
        'name' => 'Once Per Customer',
        'type' => 'fixed',
        'value' => 10,
        'usage_limit_per_customer' => 1,
        'is_active' => true,
    ]);

    $customer = User::factory()->create(['email' => 'customer@example.com']);
    $customer->assignRole('Customer');

    // Create an order redeemed by this customer
    $order = Order::create([
        'order_number' => 'ORD-TEST1234',
        'customer_id' => $customer->id,
        'customer_email' => 'customer@example.com',
        'customer_first_name' => 'Jane',
        'customer_last_name' => 'Doe',
        'subtotal' => 100,
        'discount_id' => $discount->id,
        'coupon_code' => $discount->code,
        'discount_amount' => 10,
        'shipping_amount' => 0,
        'tax_amount' => 0,
        'total' => 90,
        'status' => 'pending',
        'payment_status' => 'pending',
        'shipping_address' => ['country' => 'India'],
        'billing_address' => ['country' => 'India'],
    ]);

    DiscountRedemption::create([
        'discount_id' => $discount->id,
        'order_id' => $order->id,
        'customer_id' => $customer->id,
        'customer_email' => 'customer@example.com',
        'amount' => 10,
    ]);

    $discount->increment('times_used');

    $service = app(DiscountService::class);

    // Validation for logged in customer ID
    $checkRegistered = $service->validateCode('ONCEPERCUST', 100.0, $customer->id);
    expect($checkRegistered['valid'])->toBeFalse()
        ->and($checkRegistered['message'])->toContain('redemption limit');

    // Validation for guest using same email
    $checkGuest = $service->validateCode('ONCEPERCUST', 100.0, null, 'customer@example.com');
    expect($checkGuest['valid'])->toBeFalse()
        ->and($checkGuest['message'])->toContain('redemption limit');

    // Validation for different customer passes
    $checkOther = $service->validateCode('ONCEPERCUST', 100.0, null, 'other@example.com');
    expect($checkOther['valid'])->toBeTrue();
});

/*
|--------------------------------------------------------------------------
| Cart & Checkout Storefront Integration
|--------------------------------------------------------------------------
*/

test('customer can apply and remove coupon in cart Livewire component', function () {
    [, $variant] = createDiscountTestProduct('Promo Shirt', 100.00, 10);

    $cartService = app(CartService::class);
    $cartService->add($variant, 1);

    Discount::create([
        'code' => 'CART10',
        'name' => '10% Cart Discount',
        'type' => 'percentage',
        'value' => 10,
        'is_active' => true,
    ]);

    Livewire::test(Cart::class)
        ->assertSee('Subtotal')
        ->set('couponCode', 'CART10')
        ->call('applyCoupon')
        ->assertSet('appliedCoupon.code', 'CART10')
        ->assertSet('discountAmount', 10.0)
        ->call('removeCoupon')
        ->assertSet('appliedCoupon', null)
        ->assertSet('discountAmount', 0.0);
});

test('placing order with coupon saves discount on order, records redemption, and increments times_used', function () {
    [, $variant] = createDiscountTestProduct('Sneakers', 200.00, 5);

    $discount = Discount::create([
        'code' => 'SNEAKER25',
        'name' => '$25 Off Sneakers',
        'type' => 'fixed',
        'value' => 25.00,
        'is_active' => true,
    ]);

    $customerData = [
        'first_name' => 'Alice',
        'last_name' => 'Smith',
        'email' => 'alice@example.com',
        'phone' => '1234567890',
    ];

    $address = [
        'first_name' => 'Alice',
        'last_name' => 'Smith',
        'address' => '456 Oak Ave',
        'city' => 'Mumbai',
        'state' => 'Maharashtra',
        'postal_code' => '400001',
        'country' => 'India',
        'phone' => '1234567890',
    ];

    $cart = [
        [
            'variant_id' => $variant->id,
            'quantity' => 1,
        ],
    ];

    $orderService = app(OrderService::class);
    $order = $orderService->createOrder(
        $customerData,
        $address,
        $address,
        $cart,
        ['method' => 'cod', 'status' => 'pending'],
        'SNEAKER25'
    );

    expect($order->discount_id)->toBe($discount->id)
        ->and($order->coupon_code)->toBe('SNEAKER25')
        ->and((float) $order->discount_amount)->toBe(25.00)
        ->and((float) $order->subtotal)->toBe(200.00);

    // Verify redemption row created
    $redemption = DiscountRedemption::where('discount_id', $discount->id)
        ->where('order_id', $order->id)
        ->first();

    expect($redemption)->not->toBeNull()
        ->and((float) $redemption->amount)->toBe(25.00)
        ->and($redemption->customer_email)->toBe('alice@example.com');

    // Verify times_used incremented
    $discount->refresh();
    expect($discount->times_used)->toBe(1);
});

test('concurrency safety prevents over-redemption when limit is reached', function () {
    [, $variant] = createDiscountTestProduct('Exclusive Watch', 500.00, 10);

    // Coupon with usage limit of 1
    $discount = Discount::create([
        'code' => 'ONLYONE',
        'name' => 'Only One Redemption Allowed',
        'type' => 'fixed',
        'value' => 50.00,
        'usage_limit' => 1,
        'times_used' => 0,
        'is_active' => true,
    ]);

    $customerData = [
        'first_name' => 'Bob',
        'last_name' => 'Builder',
        'email' => 'bob@example.com',
        'phone' => '9876543210',
    ];

    $address = [
        'first_name' => 'Bob',
        'last_name' => 'Builder',
        'address' => '789 Pine Rd',
        'city' => 'Delhi',
        'state' => 'Delhi',
        'postal_code' => '110001',
        'country' => 'India',
        'phone' => '9876543210',
    ];

    $cart = [
        [
            'variant_id' => $variant->id,
            'quantity' => 1,
        ],
    ];

    $orderService = app(OrderService::class);

    // First checkout succeeds
    $order1 = $orderService->createOrder(
        $customerData,
        $address,
        $address,
        $cart,
        ['method' => 'cod', 'status' => 'pending'],
        'ONLYONE'
    );

    expect($order1)->not->toBeNull();
    $discount->refresh();
    expect($discount->times_used)->toBe(1);

    // Second checkout with same coupon fails due to usage limit
    expect(function () use ($orderService, $customerData, $address, $cart) {
        $orderService->createOrder(
            $customerData,
            $address,
            $address,
            $cart,
            ['method' => 'cod', 'status' => 'pending'],
            'ONLYONE'
        );
    })->toThrow(RuntimeException::class);
});

test('admin creating discount with store local time correctly converts to UTC and is active immediately', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $storeTimezone = (string) store_setting('timezone', 'Asia/Kolkata');
    $nowInStore = now()->setTimezone($storeTimezone)->format('Y-m-d\TH:i');

    Livewire::test(DiscountCreate::class)
        ->set('code', 'TZTEST')
        ->set('name', 'Timezone Test')
        ->set('type', 'fixed')
        ->set('value', '10')
        ->set('starts_at', $nowInStore)
        ->set('is_active', true)
        ->call('save')
        ->assertRedirect(route('admin.discounts.index'));

    $discount = Discount::where('code', 'TZTEST')->firstOrFail();
    expect($discount->hasStarted())->toBeTrue();

    $eligibility = $discount->checkEligibility(50.0);
    expect($eligibility['eligible'])->toBeTrue();
});
