<?php

use App\Livewire\Storefront\CartCounter;
use App\Livewire\Storefront\ProductDetail;
use App\Livewire\Storefront\ProductListing;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductOption;
use App\Models\ProductOptionValue;
use App\Models\ProductVariant;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('storefront products page renders successfully with products', function () {
    $product1 = Product::create([
        'name' => 'Cotton T-Shirt',
        'slug' => 'cotton-t-shirt',
        'sku' => 'TSHIRT-01',
        'price' => 499.00,
        'is_active' => true,
    ]);

    $product2 = Product::create([
        'name' => 'Denim Jacket',
        'slug' => 'denim-jacket',
        'sku' => 'JACKET-01',
        'price' => 1999.00,
        'is_active' => true,
    ]);

    $inactiveProduct = Product::create([
        'name' => 'Hidden Product',
        'slug' => 'hidden-product',
        'sku' => 'HIDDEN-01',
        'price' => 99.00,
        'is_active' => false,
    ]);

    $this->get(route('storefront.products.index'))
        ->assertOk()
        ->assertSee('Cotton T-Shirt')
        ->assertSee('Denim Jacket')
        ->assertDontSee('Hidden Product');
});

test('storefront products page supports sorting and pagination', function () {
    Product::create([
        'name' => 'Affordable Cap',
        'slug' => 'affordable-cap',
        'sku' => 'CAP-01',
        'price' => 199.00,
        'is_active' => true,
    ]);

    Product::create([
        'name' => 'Luxury Watch',
        'slug' => 'luxury-watch',
        'sku' => 'WATCH-01',
        'price' => 9999.00,
        'is_active' => true,
    ]);

    Livewire::test(ProductListing::class)
        ->set('sort', 'price_low')
        ->assertSeeInOrder(['Affordable Cap', 'Luxury Watch'])
        ->set('sort', 'price_high')
        ->assertSeeInOrder(['Luxury Watch', 'Affordable Cap']);
});

test('database-backed search finds matching products by name, sku or description', function () {
    Product::create([
        'name' => 'Wireless Bluetooth Headphones',
        'slug' => 'wireless-headphones',
        'sku' => 'AUDIO-WL-01',
        'description' => 'Noise-cancelling over-ear headphones',
        'price' => 2499.00,
        'is_active' => true,
    ]);

    Product::create([
        'name' => 'Running Sneakers',
        'slug' => 'running-sneakers',
        'sku' => 'SHOE-RUN-01',
        'description' => 'Comfortable breathable athletic shoes',
        'price' => 1899.00,
        'is_active' => true,
    ]);

    Livewire::test(ProductListing::class)
        ->set('search', 'Bluetooth')
        ->assertSee('Wireless Bluetooth Headphones')
        ->assertDontSee('Running Sneakers')
        ->set('search', 'SHOE-RUN')
        ->assertSee('Running Sneakers')
        ->assertDontSee('Wireless Bluetooth Headphones')
        ->set('search', 'Noise-cancelling')
        ->assertSee('Wireless Bluetooth Headphones')
        ->assertDontSee('Running Sneakers');
});

test('category pages show only relevant products', function () {
    $categoryA = Category::create([
        'name' => 'Footwear',
        'slug' => 'footwear',
        'is_active' => true,
    ]);

    $categoryB = Category::create([
        'name' => 'Electronics',
        'slug' => 'electronics',
        'is_active' => true,
    ]);

    $shoe = Product::create([
        'name' => 'Leather Boots',
        'slug' => 'leather-boots',
        'sku' => 'BOOTS-01',
        'price' => 3499.00,
        'is_active' => true,
    ]);
    $shoe->categories()->attach($categoryA);

    $phone = Product::create([
        'name' => 'Smartphone Pro',
        'slug' => 'smartphone-pro',
        'sku' => 'PHONE-01',
        'price' => 45000.00,
        'is_active' => true,
    ]);
    $phone->categories()->attach($categoryB);

    // Visiting dedicated category page
    $this->get(route('storefront.categories.show', $categoryA->slug))
        ->assertOk()
        ->assertSee('Footwear')
        ->assertSee('Leather Boots')
        ->assertDontSee('Smartphone Pro');

    // Filtering via Livewire component
    Livewire::test(ProductListing::class, ['category' => $categoryB])
        ->assertSee('Smartphone Pro')
        ->assertDontSee('Leather Boots');
});

test('product detail page renders with images, price, stock status and variants', function () {
    $product = Product::create([
        'name' => 'Silk Casual Shirt',
        'slug' => 'silk-casual-shirt',
        'sku' => 'SHIRT-SILK-01',
        'description' => 'Premium silk shirt for summer.',
        'price' => 1500.00,
        'compare_at_price' => 2000.00,
        'is_active' => true,
    ]);

    $option = ProductOption::create([
        'product_id' => $product->id,
        'name' => 'Size',
        'position' => 0,
    ]);

    $valM = ProductOptionValue::create([
        'product_option_id' => $option->id,
        'value' => 'Medium',
        'position' => 0,
    ]);

    $valL = ProductOptionValue::create([
        'product_option_id' => $option->id,
        'value' => 'Large',
        'position' => 1,
    ]);

    $varM = ProductVariant::create([
        'product_id' => $product->id,
        'title' => 'Medium',
        'sku' => 'SHIRT-M',
        'price' => 1500.00,
        'is_active' => true,
    ]);
    $varM->optionValues()->attach($valM);
    Inventory::create([
        'product_variant_id' => $varM->id,
        'quantity' => 15,
    ]);

    $varL = ProductVariant::create([
        'product_id' => $product->id,
        'title' => 'Large',
        'sku' => 'SHIRT-L',
        'price' => 1700.00,
        'is_active' => true,
    ]);
    $varL->optionValues()->attach($valL);
    Inventory::create([
        'product_variant_id' => $varL->id,
        'quantity' => 0, // Out of stock
    ]);

    // Initial mount selects Medium
    $test = Livewire::test(ProductDetail::class, ['product' => $product])
        ->assertSee('Silk Casual Shirt')
        ->assertSee('SHIRT-M')
        ->assertSee('1,500.00')
        ->assertSee('In Stock')
        ->assertSet('selectedVariant.id', $varM->id);

    // Select Large -> updates price to 1700, sku to SHIRT-L, and shows Out of Stock
    $test->call('selectOption', $option->id, $valL->id)
        ->assertSee('SHIRT-L')
        ->assertSee('1,700.00')
        ->assertSee('Out of Stock')
        ->assertSet('selectedVariant.id', $varL->id);
});

test('add-to-cart adds item to session, dispatches event, and cart counter updates', function () {
    $product = Product::create([
        'name' => 'Classic Coffee Mug',
        'slug' => 'classic-coffee-mug',
        'sku' => 'MUG-01',
        'price' => 299.00,
        'is_active' => true,
    ]);

    $variant = ProductVariant::create([
        'product_id' => $product->id,
        'title' => 'Default Variant',
        'sku' => 'MUG-01-DEF',
        'price' => 299.00,
        'is_active' => true,
    ]);

    Inventory::create([
        'product_variant_id' => $variant->id,
        'quantity' => 10,
    ]);

    // Cart counter initially 0
    Livewire::test(CartCounter::class)
        ->assertSee('0');

    // Add to cart on ProductDetail
    Livewire::test(ProductDetail::class, ['product' => $product])
        ->set('quantity', 2)
        ->call('addToCart')
        ->assertDispatched('cart-updated')
        ->assertSee('Added 2 × Classic Coffee Mug');

    $cartService = app(CartService::class);
    expect($cartService->count())->toBe(2);
    expect($cartService->subtotal())->toBe(598.00);

    // Cart counter reflects new count
    Livewire::test(CartCounter::class)
        ->assertSee('2');
});
