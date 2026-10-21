<?php

use App\Livewire\Admin\Discounts\Create as AdminDiscountCreate;
use App\Models\Category;
use App\Models\CustomerSegment;
use App\Models\Discount;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\DiscountService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => 'Store Administrator', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Customer', 'guard_name' => 'web']);
});

test('administrator can create discount targeted to customer segments via form', function () {
    $admin = User::factory()->create();
    $this->actingAs($admin);

    $segment = CustomerSegment::create([
        'name' => 'VIP Members',
        'slug' => 'vip-members',
        'color' => 'purple',
    ]);

    Livewire::test(AdminDiscountCreate::class)
        ->set('code', 'VIP20')
        ->set('name', 'VIP Exclusive 20%')
        ->set('type', 'percentage')
        ->set('value', '20')
        ->set('customer_eligibility', 'segments')
        ->set('selected_segments', [$segment->id])
        ->set('applies_to', 'all')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('admin.discounts.index'));

    $discount = Discount::where('code', 'VIP20')->first();
    expect($discount)->not->toBeNull();
    expect($discount->customer_eligibility)->toBe('segments');
    expect($discount->customerSegments->pluck('id')->all())->toContain($segment->id);
});

test('administrator can create discount targeted to specific categories and products via form', function () {
    $admin = User::factory()->create();
    $this->actingAs($admin);

    $category = Category::create([
        'name' => 'Footwear',
        'slug' => 'footwear',
        'collection_type' => 'manual',
    ]);

    $product = Product::create([
        'name' => 'Leather Boots',
        'slug' => 'leather-boots',
        'sku' => 'BOOT-01',
        'price' => 120,
    ]);

    // Test category targeting
    Livewire::test(AdminDiscountCreate::class)
        ->set('code', 'SHOES15')
        ->set('name', 'Shoes 15% Off')
        ->set('type', 'percentage')
        ->set('value', '15')
        ->set('customer_eligibility', 'all')
        ->set('applies_to', 'categories')
        ->set('selected_categories', [$category->id])
        ->call('save')
        ->assertHasNoErrors();

    $catDiscount = Discount::where('code', 'SHOES15')->first();
    expect($catDiscount->applies_to)->toBe('categories');
    expect($catDiscount->categories->pluck('id')->all())->toContain($category->id);

    // Test product targeting
    Livewire::test(AdminDiscountCreate::class)
        ->set('code', 'BOOT50')
        ->set('name', 'Boots Flat 50 Off')
        ->set('type', 'fixed')
        ->set('value', '50')
        ->set('customer_eligibility', 'all')
        ->set('applies_to', 'products')
        ->set('selected_products', [$product->id])
        ->call('save')
        ->assertHasNoErrors();

    $prodDiscount = Discount::where('code', 'BOOT50')->first();
    expect($prodDiscount->applies_to)->toBe('products');
    expect($prodDiscount->products->pluck('id')->all())->toContain($product->id);
});

test('discount targeted to customer segment rejects customers not in the segment and accepts segment members', function () {
    $vipSegment = CustomerSegment::create(['name' => 'VIP Club', 'slug' => 'vip-club', 'color' => 'purple']);

    $vipDiscount = Discount::create([
        'code' => 'CLUBVIP',
        'name' => 'VIP Club Exclusive',
        'type' => 'percentage',
        'value' => 25,
        'customer_eligibility' => 'segments',
        'applies_to' => 'all',
        'is_active' => true,
    ]);
    $vipDiscount->customerSegments()->attach($vipSegment->id);

    $vipCustomer = User::factory()->create(['email' => 'vip@example.com']);
    $vipCustomer->segments()->attach($vipSegment->id);

    $regularCustomer = User::factory()->create(['email' => 'regular@example.com']);

    $discountService = app(DiscountService::class);

    // 1. Regular customer not in segment -> Rejected
    $res1 = $discountService->validateCode('CLUBVIP', 500.0, $regularCustomer->id, $regularCustomer->email);
    expect($res1['valid'])->toBeFalse();
    expect($res1['message'])->toContain('exclusively available to members of the selected customer segment');

    // 2. Guest user (no account in segment) -> Rejected
    $res2 = $discountService->validateCode('CLUBVIP', 500.0, null, 'guest@example.com');
    expect($res2['valid'])->toBeFalse();

    // 3. VIP customer in segment -> Accepted
    $res3 = $discountService->validateCode('CLUBVIP', 500.0, $vipCustomer->id, $vipCustomer->email);
    expect($res3['valid'])->toBeTrue();
    expect($res3['discount_amount'])->toEqual(125.0); // 25% of 500
});

test('discount targeted to specific products applies only to eligible items and computes discount proportionally', function () {
    $targetProduct = Product::create(['name' => 'Smartphone Pro', 'slug' => 'smart-pro', 'sku' => 'SP-01', 'price' => 1000]);
    $targetVariant = ProductVariant::create(['product_id' => $targetProduct->id, 'title' => 'Default', 'sku' => 'SP-01-V', 'price' => 1000]);

    $otherProduct = Product::create(['name' => 'Phone Case', 'slug' => 'phone-case', 'sku' => 'PC-01', 'price' => 50]);
    $otherVariant = ProductVariant::create(['product_id' => $otherProduct->id, 'title' => 'Default', 'sku' => 'PC-01-V', 'price' => 50]);

    $discount = Discount::create([
        'code' => 'PHONE10',
        'name' => '10% Off Smartphone',
        'type' => 'percentage',
        'value' => 10,
        'customer_eligibility' => 'all',
        'applies_to' => 'products',
        'is_active' => true,
    ]);
    $discount->products()->attach($targetProduct->id);

    $discountService = app(DiscountService::class);

    // Cart contains only the non-target phone case (50.00)
    $cartWithoutPhone = [
        (string) $otherVariant->id => [
            'variant_id' => $otherVariant->id,
            'product_id' => $otherProduct->id,
            'price' => 50.00,
            'quantity' => 1,
        ],
    ];

    $res1 = $discountService->validateCode('PHONE10', 50.0, null, null, $cartWithoutPhone);
    expect($res1['valid'])->toBeFalse();
    expect($res1['message'])->toContain('Smartphone Pro');

    // Cart contains both phone (1000) and case (50), total 1050
    $cartWithBoth = [
        (string) $targetVariant->id => [
            'variant_id' => $targetVariant->id,
            'product_id' => $targetProduct->id,
            'price' => 1000.00,
            'quantity' => 1,
        ],
        (string) $otherVariant->id => [
            'variant_id' => $otherVariant->id,
            'product_id' => $otherProduct->id,
            'price' => 50.00,
            'quantity' => 1,
        ],
    ];

    $res2 = $discountService->validateCode('PHONE10', 1050.0, null, null, $cartWithBoth);
    expect($res2['valid'])->toBeTrue();
    // 10% should only apply to the Smartphone Pro ($1000 * 10% = $100), not the case
    expect($res2['discount_amount'])->toEqual(100.0);
});

test('discount targeted to category applies only to items from that category', function () {
    $categoryShoes = Category::create(['name' => 'Shoes', 'slug' => 'shoes', 'collection_type' => 'manual']);
    $categoryShirts = Category::create(['name' => 'Shirts', 'slug' => 'shirts', 'collection_type' => 'manual']);

    $shoe = Product::create(['name' => 'Sneakers', 'slug' => 'sneakers', 'sku' => 'SNK-01', 'price' => 200]);
    $shoe->categories()->attach($categoryShoes->id);
    $shoeVariant = ProductVariant::create(['product_id' => $shoe->id, 'title' => 'Size 10', 'sku' => 'SNK-01-V', 'price' => 200]);

    $shirt = Product::create(['name' => 'T-Shirt', 'slug' => 't-shirt', 'sku' => 'TSH-01', 'price' => 50]);
    $shirt->categories()->attach($categoryShirts->id);
    $shirtVariant = ProductVariant::create(['product_id' => $shirt->id, 'title' => 'Medium', 'sku' => 'TSH-01-V', 'price' => 50]);

    $discount = Discount::create([
        'code' => 'SHOESAVE',
        'name' => 'Save on Shoes',
        'type' => 'percentage',
        'value' => 20,
        'customer_eligibility' => 'all',
        'applies_to' => 'categories',
        'is_active' => true,
    ]);
    $discount->categories()->attach($categoryShoes->id);

    $discountService = app(DiscountService::class);

    // Cart with only shirt -> Rejected
    $cartOnlyShirt = [
        (string) $shirtVariant->id => [
            'variant_id' => $shirtVariant->id,
            'product_id' => $shirt->id,
            'price' => 50.00,
            'quantity' => 2, // $100
        ],
    ];
    $res1 = $discountService->validateCode('SHOESAVE', 100.0, null, null, $cartOnlyShirt);
    expect($res1['valid'])->toBeFalse();
    expect($res1['message'])->toContain('Shoes');

    // Cart with 1 shoe ($200) and 2 shirts ($100), subtotal $300
    $cartMixed = [
        (string) $shoeVariant->id => [
            'variant_id' => $shoeVariant->id,
            'product_id' => $shoe->id,
            'price' => 200.00,
            'quantity' => 1,
        ],
        (string) $shirtVariant->id => [
            'variant_id' => $shirtVariant->id,
            'product_id' => $shirt->id,
            'price' => 50.00,
            'quantity' => 2,
        ],
    ];
    $res2 = $discountService->validateCode('SHOESAVE', 300.0, null, null, $cartMixed);
    expect($res2['valid'])->toBeTrue();
    // 20% on Shoes ($200 * 20% = $40), shirts excluded
    expect($res2['discount_amount'])->toEqual(40.0);
});

test('combined targeting enforces both customer segment AND catalog scope', function () {
    $wholesaleSegment = CustomerSegment::create(['name' => 'Wholesale Buyers', 'slug' => 'wholesale', 'color' => 'indigo']);

    $categoryBulk = Category::create(['name' => 'Bulk Supplies', 'slug' => 'bulk-supplies', 'collection_type' => 'manual']);
    $productBulk = Product::create(['name' => 'Bulk Cotton Pack', 'slug' => 'bulk-cotton', 'sku' => 'BLK-01', 'price' => 500]);
    $productBulk->categories()->attach($categoryBulk->id);
    $variantBulk = ProductVariant::create(['product_id' => $productBulk->id, 'title' => 'Default', 'sku' => 'BLK-01-V', 'price' => 500]);

    $wholesaleDiscount = Discount::create([
        'code' => 'WS30',
        'name' => 'Wholesale Bulk 30% Off',
        'type' => 'percentage',
        'value' => 30,
        'customer_eligibility' => 'segments',
        'applies_to' => 'categories',
        'is_active' => true,
    ]);
    $wholesaleDiscount->customerSegments()->attach($wholesaleSegment->id);
    $wholesaleDiscount->categories()->attach($categoryBulk->id);

    $wsCustomer = User::factory()->create(['email' => 'buyer@wholesale.com']);
    $wsCustomer->segments()->attach($wholesaleSegment->id);

    $retailCustomer = User::factory()->create(['email' => 'shopper@retail.com']);

    $cart = [
        (string) $variantBulk->id => [
            'variant_id' => $variantBulk->id,
            'product_id' => $productBulk->id,
            'price' => 500.0,
            'quantity' => 1,
        ],
    ];

    $discountService = app(DiscountService::class);

    // 1. Retail customer with bulk items -> Rejected by segment
    $res1 = $discountService->validateCode('WS30', 500.0, $retailCustomer->id, $retailCustomer->email, $cart);
    expect($res1['valid'])->toBeFalse();
    expect($res1['message'])->toContain('Wholesale Buyers');

    // 2. Wholesale customer with bulk items -> Accepted
    $res2 = $discountService->validateCode('WS30', 500.0, $wsCustomer->id, $wsCustomer->email, $cart);
    expect($res2['valid'])->toBeTrue();
    expect($res2['discount_amount'])->toEqual(150.0); // 30% of 500
});
