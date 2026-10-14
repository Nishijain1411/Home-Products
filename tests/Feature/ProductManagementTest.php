<?php

use App\Livewire\Admin\Products\Create;
use App\Livewire\Admin\Products\Edit;
use App\Livewire\Admin\Products\Index as AdminProductIndex;
use App\Livewire\Storefront\Checkout;
use App\Livewire\Storefront\ProductDetail;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\StreamedResponse;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => 'Customer', 'guard_name' => 'web']);
});

test('admin products screen requires authentication and renders for authenticated users', function () {
    $this->get(route('admin.products.index'))
        ->assertRedirect(route('login'));

    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.products.index'))
        ->assertOk()
        ->assertSee('Products');
});

test('product validation fails when mandatory fields are missing or duplicated', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    // Initial product to test unique slug and SKU
    Product::create([
        'name' => 'Existing Phone',
        'slug' => 'existing-phone',
        'sku' => 'PHONE-EXISTING',
        'price' => 999.00,
        'is_active' => true,
    ]);

    Livewire::test(AdminProductIndex::class)
        ->set('name', '')
        ->set('slug', '')
        ->set('sku', '')
        ->set('price', '')
        ->call('save')
        ->assertHasErrors([
            'name' => 'required',
            'slug' => 'required',
            'sku' => 'required',
            'price' => 'required',
        ]);

    Livewire::test(AdminProductIndex::class)
        ->set('name', 'Duplicate Item')
        ->set('slug', 'existing-phone')
        ->set('sku', 'PHONE-EXISTING')
        ->set('price', 100)
        ->call('save')
        ->assertHasErrors([
            'slug' => 'unique',
            'sku' => 'unique',
        ]);
});

test('can create a simple product without options and it creates a default variant with inventory', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $category = Category::create([
        'name' => 'Electronics',
        'slug' => 'electronics',
        'is_active' => true,
    ]);

    Livewire::test(AdminProductIndex::class)
        ->set('name', 'Wireless Headphones')
        ->set('slug', 'wireless-headphones')
        ->set('sku', 'WH-1000')
        ->set('description', 'High quality noise cancelling headphones')
        ->set('price', '2499.00')
        ->set('compare_at_price', '2999.00')
        ->set('product_type', 'Audio')
        ->set('vendor', 'Sony')
        ->set('selectedCategories', [$category->id])
        ->call('save')
        ->assertHasNoErrors();

    $product = Product::where('slug', 'wireless-headphones')->first();
    expect($product)->not->toBeNull();
    expect((float) $product->price)->toBe(2499.00);
    expect($product->categories)->toHaveCount(1);
    expect($product->categories->first()->id)->toBe($category->id);

    // Verify default variant and inventory
    expect($product->variants)->toHaveCount(1);
    $variant = $product->variants->first();
    expect($variant->title)->toBe('Default Variant');
    expect($variant->inventory)->not->toBeNull();
});

test('can create product with options and generate variants with custom pricing and stock', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(AdminProductIndex::class)
        ->set('name', 'Cotton T-Shirt')
        ->set('slug', 'cotton-t-shirt')
        ->set('sku', 'TSHIRT-01')
        ->set('price', '499.00')
        ->set('productOptions', [
            [
                'name' => 'Size',
                'values' => ['S', 'M'],
            ],
            [
                'name' => 'Color',
                'values' => ['Black', 'White'],
            ],
        ])
        ->call('generateVariants')
        ->assertCount('generatedVariants', 4)
        // Configure variant-level SKU, price, and stock
        ->set('generatedVariants.0.sku', 'TSHIRT-S-BLK')
        ->set('generatedVariants.0.price', '499.00')
        ->set('generatedVariants.0.quantity', 25)
        ->set('generatedVariants.1.sku', 'TSHIRT-S-WHT')
        ->set('generatedVariants.1.price', '499.00')
        ->set('generatedVariants.1.quantity', 30)
        ->set('generatedVariants.2.sku', 'TSHIRT-M-BLK')
        ->set('generatedVariants.2.price', '549.00')
        ->set('generatedVariants.2.quantity', 15)
        ->set('generatedVariants.3.sku', 'TSHIRT-M-WHT')
        ->set('generatedVariants.3.price', '549.00')
        ->set('generatedVariants.3.quantity', 20)
        ->call('save')
        ->assertHasNoErrors();

    $product = Product::where('slug', 'cotton-t-shirt')->first();
    expect($product)->not->toBeNull();
    expect($product->options)->toHaveCount(2);
    expect($product->variants)->toHaveCount(4);

    $sBlack = $product->variants()->where('sku', 'TSHIRT-S-BLK')->first();
    expect($sBlack)->not->toBeNull();
    expect($sBlack->title)->toBe('S / Black');
    expect((float) $sBlack->price)->toBe(499.00);
    expect($sBlack->inventory->quantity)->toBe(25);
    expect($sBlack->optionValues)->toHaveCount(2);

    $mBlack = $product->variants()->where('sku', 'TSHIRT-M-BLK')->first();
    expect($mBlack)->not->toBeNull();
    expect((float) $mBlack->price)->toBe(549.00);
    expect($mBlack->inventory->quantity)->toBe(15);
});

test('can open existing product for edit, retaining existing options, variants, prices, and stock', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $product = Product::create([
        'name' => 'Smart Watch',
        'slug' => 'smart-watch',
        'sku' => 'SW-01',
        'price' => 1999.00,
        'is_active' => true,
    ]);

    $option = $product->options()->create(['name' => 'Strap', 'position' => 0]);
    $valLeather = $option->values()->create(['value' => 'Leather', 'position' => 0]);
    $valSilicone = $option->values()->create(['value' => 'Silicone', 'position' => 1]);

    $varLeather = $product->variants()->create([
        'title' => 'Leather',
        'sku' => 'SW-01-LTH',
        'price' => 2499.00,
        'is_active' => true,
    ]);
    $varLeather->optionValues()->attach($valLeather->id);
    $varLeather->inventory()->create(['quantity' => 12]);

    $varSilicone = $product->variants()->create([
        'title' => 'Silicone',
        'sku' => 'SW-01-SIL',
        'price' => 1999.00,
        'is_active' => true,
    ]);
    $varSilicone->optionValues()->attach($valSilicone->id);
    $varSilicone->inventory()->create(['quantity' => 35]);

    Livewire::test(AdminProductIndex::class)
        ->call('openEdit', $product->id)
        ->assertSet('editingId', $product->id)
        ->assertSet('name', 'Smart Watch')
        ->assertSet('sku', 'SW-01')
        ->assertCount('productOptions', 1)
        ->assertCount('generatedVariants', 2)
        // Edit variant stock and price without deleting variants
        ->set('generatedVariants.0.price', '2599.00')
        ->set('generatedVariants.0.quantity', 18)
        ->call('save')
        ->assertHasNoErrors();

    // Verify existing variant was updated and preserved
    $varLeather->refresh();
    expect((float) $varLeather->price)->toBe(2599.00);
    expect($varLeather->inventory->quantity)->toBe(18);
    expect($varLeather->id)->toBe($varLeather->id);
});

test('can toggle product active status', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $product = Product::create([
        'name' => 'Desk Lamp',
        'slug' => 'desk-lamp',
        'sku' => 'LAMP-01',
        'price' => 799.00,
        'is_active' => true,
    ]);

    Livewire::test(AdminProductIndex::class)
        ->call('toggleStatus', $product->id);

    expect($product->fresh()->is_active)->toBeFalse();

    Livewire::test(AdminProductIndex::class)
        ->call('toggleStatus', $product->id);

    expect($product->fresh()->is_active)->toBeTrue();
});

test('can upload product images, set primary image, reorder, and remove images', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    $this->actingAs($user);

    $image1 = UploadedFile::fake()->image('front.jpg');
    $image2 = UploadedFile::fake()->image('side.jpg');

    Livewire::test(AdminProductIndex::class)
        ->set('name', 'Running Shoes')
        ->set('slug', 'running-shoes')
        ->set('sku', 'SHOES-01')
        ->set('price', '1599.00')
        ->set('productImages', [$image1, $image2])
        ->call('save')
        ->assertHasNoErrors();

    $product = Product::where('slug', 'running-shoes')->first();
    expect($product->images)->toHaveCount(2);

    $firstImage = $product->images()->where('position', 0)->first();
    $secondImage = $product->images()->where('position', 1)->first();

    expect($firstImage->is_primary)->toBeTrue();
    expect($secondImage->is_primary)->toBeFalse();

    // Set second image as primary
    Livewire::test(AdminProductIndex::class)
        ->call('openEdit', $product->id)
        ->call('setPrimaryImage', $secondImage->id);

    expect($firstImage->fresh()->is_primary)->toBeFalse();
    expect($secondImage->fresh()->is_primary)->toBeTrue();

    // Delete first image
    Livewire::test(AdminProductIndex::class)
        ->call('openEdit', $product->id)
        ->call('removeExistingProductImage', $firstImage->id);

    expect(ProductImage::find($firstImage->id))->toBeNull();
    expect($product->fresh()->images)->toHaveCount(1);
});

test('can delete a product and its associated relations and images', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    $this->actingAs($user);

    $product = Product::create([
        'name' => 'To Be Deleted',
        'slug' => 'to-be-deleted',
        'sku' => 'DEL-01',
        'price' => 100.00,
        'is_active' => true,
    ]);

    $variant = $product->variants()->create([
        'title' => 'Default Variant',
        'sku' => 'DEL-01-DEF',
        'price' => 100.00,
        'is_active' => true,
    ]);
    $variant->inventory()->create(['quantity' => 5]);

    Livewire::test(AdminProductIndex::class)
        ->call('confirmDelete', $product->id)
        ->assertSet('deleteId', $product->id)
        ->call('delete')
        ->assertHasNoErrors();

    expect(Product::find($product->id))->toBeNull();
    expect(ProductVariant::find($variant->id))->toBeNull();
});

test('end to end: multi-variant product connects to storefront, option switching updates price and stock, and checkout completes', function () {
    $product = Product::create([
        'name' => 'Gaming Mouse',
        'slug' => 'gaming-mouse',
        'sku' => 'GM-01',
        'price' => 999.00,
        'compare_at_price' => 1299.00,
        'is_active' => true,
    ]);

    $optionColor = $product->options()->create(['name' => 'Color', 'position' => 0]);
    $valWhite = $optionColor->values()->create(['value' => 'White', 'position' => 0]);
    $valBlack = $optionColor->values()->create(['value' => 'Black', 'position' => 1]);

    // White variant: ₹999, 10 in stock
    $varWhite = $product->variants()->create([
        'title' => 'White',
        'sku' => 'GM-01-WHT',
        'price' => 999.00,
        'is_active' => true,
    ]);
    $varWhite->optionValues()->attach($valWhite->id);
    $varWhite->inventory()->create(['quantity' => 10]);

    // Black variant: Premium RGB, ₹1199, 3 in stock
    $varBlack = $product->variants()->create([
        'title' => 'Black',
        'sku' => 'GM-01-BLK',
        'price' => 1199.00,
        'is_active' => true,
    ]);
    $varBlack->optionValues()->attach($valBlack->id);
    $varBlack->inventory()->create(['quantity' => 3]);

    // 1. Visit storefront product detail
    $detail = Livewire::test(ProductDetail::class, ['product' => $product])
        ->assertSee('Gaming Mouse')
        ->assertSee('GM-01-WHT')
        ->assertSee('999.00')
        ->assertSee('In Stock');

    // 2. Switch to Black variant
    $detail->call('selectOption', $optionColor->id, $valBlack->id)
        ->assertSee('GM-01-BLK')
        ->assertSee('1,199.00')
        ->assertSee('Only 3 left, order soon!');

    // 3. Add Black variant to cart
    $detail->set('quantity', 2)
        ->call('addToCart')
        ->assertSee('Added 2 × Gaming Mouse to your cart!');

    $cartService = app(CartService::class);
    $cart = $cartService->get();
    expect($cart)->toHaveKey((string) $varBlack->id);
    expect($cart[(string) $varBlack->id]['quantity'])->toBe(2);
    expect($cart[(string) $varBlack->id]['price'])->toBe(1199.00);
    expect($cartService->subtotal())->toBe(2398.00);

    // 4. Complete checkout
    Livewire::test(Checkout::class)
        ->set('firstName', 'Gamer')
        ->set('lastName', 'One')
        ->set('email', 'gamer@example.com')
        ->set('phone', '9876543210')
        ->set('address', '101 Cyber Street')
        ->set('city', 'Bangalore')
        ->set('state', 'Karnataka')
        ->set('postalCode', '560001')
        ->set('country', 'India')
        ->set('paymentMethod', 'cod')
        ->call('placeOrder')
        ->assertHasNoErrors();

    // 5. Verify order placed and inventory decremented
    $order = Order::where('customer_email', 'gamer@example.com')->first();
    expect($order)->not->toBeNull();
    expect($order->subtotal)->toBe('2398.00');
    expect((float) $order->total)->toBe(2829.64);
    expect($order->items)->toHaveCount(1);
    expect($order->items->first()->variant_name)->toBe('Black');
    expect($order->items->first()->sku)->toBe('GM-01-BLK');

    $varBlack->inventory->refresh();
    expect($varBlack->inventory->quantity)->toBe(1);
    expect($varBlack->inventory->reserved_quantity)->toBe(2);

    // Cart is cleared
    expect($cartService->count())->toBe(0);
});

test('can export products to csv with headers and correct product data', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $category = Category::create([
        'name' => 'Smartphones',
        'slug' => 'smartphones',
        'is_active' => true,
    ]);

    $product = Product::create([
        'name' => 'Flagship Phone',
        'slug' => 'flagship-phone',
        'sku' => 'PHONE-FLAGSHIP-01',
        'price' => 69999.00,
        'compare_at_price' => 79999.00,
        'product_type' => 'Smartphone',
        'vendor' => 'SuperTech',
        'is_active' => true,
        'is_featured' => true,
        'description' => 'Top tier smartphone with AMOLED display',
    ]);
    $product->categories()->attach($category->id);

    $variant = $product->variants()->create([
        'title' => 'Default Variant',
        'sku' => 'PHONE-FLAGSHIP-01',
        'price' => 69999.00,
        'is_active' => true,
    ]);
    $variant->inventory()->create([
        'quantity' => 45,
        'reserved_quantity' => 0,
    ]);

    $component = Livewire::test(AdminProductIndex::class);
    $response = $component->instance()->export();

    expect($response)->toBeInstanceOf(StreamedResponse::class);

    ob_start();
    $response->sendContent();
    $csvContent = ob_get_clean();

    expect($csvContent)->toContain('SKU');
    expect($csvContent)->toContain('Compare at Price');
    expect($csvContent)->toContain('Flagship Phone');
    expect($csvContent)->toContain('PHONE-FLAGSHIP-01');
    expect($csvContent)->toContain('Smartphones');
    expect($csvContent)->toContain('SuperTech');
});

test('can download sample csv template', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $component = Livewire::test(AdminProductIndex::class);
    $response = $component->instance()->exportSample();

    expect($response)->toBeInstanceOf(StreamedResponse::class);

    ob_start();
    $response->sendContent();
    $csvContent = ob_get_clean();

    expect($csvContent)->toContain('SKU');
    expect($csvContent)->toContain('Compare at Price');
    expect($csvContent)->toContain('Wireless Earbuds Pro');
    expect($csvContent)->toContain('Classic Cotton T-Shirt');
});

test('can bulk import products from csv creating products, variants, inventory and categories', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $csv = "Name,Slug,SKU,Price,Compare at Price,Stock,Categories,Product Type,Vendor,Is Active,Is Featured,Description\n"
        ."Mechanical Keyboard,mech-keyboard,KEY-MECH-01,2999.00,3499.00,30,Peripherals,Computer,KeyCo,1,1,RGB mechanical gaming keyboard\n"
        ."Gaming Headset,,AUDIO-HS-01,1499.00,,15,\"Peripherals, Audio\",Audio,SoundMax,1,0,Over-ear stereo headset\n";

    $file = UploadedFile::fake()->createWithContent('products.csv', $csv);

    Livewire::test(AdminProductIndex::class)
        ->set('importFile', $file)
        ->call('import')
        ->assertHasNoErrors()
        ->assertSet('showImportModal', false)
        ->assertSet('importErrors', [])
        ->assertSet('successMessage', 'Successfully imported 2 products (2 created, 0 updated).')
        ->assertSee('Successfully imported 2 products (2 created, 0 updated).')
        ->assertSee('Operation Successful');

    // Verify first product
    $prod1 = Product::where('sku', 'KEY-MECH-01')->first();
    expect($prod1)->not->toBeNull();
    expect($prod1->name)->toBe('Mechanical Keyboard');
    expect($prod1->slug)->toBe('mech-keyboard');
    expect((float) $prod1->price)->toBe(2999.00);
    expect((float) $prod1->compare_at_price)->toBe(3499.00);
    expect($prod1->is_featured)->toBeTrue();
    expect($prod1->categories->pluck('name')->toArray())->toContain('Peripherals');
    expect($prod1->total_stock)->toBe(30);

    // Verify second product with auto slug and multiple categories
    $prod2 = Product::where('sku', 'AUDIO-HS-01')->first();
    expect($prod2)->not->toBeNull();
    expect($prod2->name)->toBe('Gaming Headset');
    expect($prod2->slug)->toBe('gaming-headset');
    expect((float) $prod2->price)->toBe(1499.00);
    expect($prod2->compare_at_price)->toBeNull();
    expect($prod2->categories->pluck('name')->toArray())->toContain('Peripherals');
    expect($prod2->categories->pluck('name')->toArray())->toContain('Audio');
    expect($prod2->total_stock)->toBe(15);
});

test('bulk import updates existing products matching sku and handles duplicate skus in csv', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $existing = Product::create([
        'name' => 'Old Product Name',
        'slug' => 'old-product-name',
        'sku' => 'UPDATE-SKU-01',
        'price' => 500.00,
        'is_active' => true,
    ]);
    $variant = $existing->variants()->create([
        'title' => 'Default Variant',
        'sku' => 'UPDATE-SKU-01',
        'price' => 500.00,
        'is_active' => true,
    ]);
    $variant->inventory()->create([
        'quantity' => 5,
        'reserved_quantity' => 0,
    ]);

    // CSV updating existing product
    $csv = "Name,Slug,SKU,Price,Compare at Price,Stock,Categories,Product Type,Vendor,Is Active,Is Featured,Description\n"
        ."Updated Product Name,updated-product-name,UPDATE-SKU-01,750.00,900.00,50,New Category,Gadgets,NewVendor,1,0,Updated description\n";

    $file = UploadedFile::fake()->createWithContent('products.csv', $csv);

    Livewire::test(AdminProductIndex::class)
        ->set('importFile', $file)
        ->call('import')
        ->assertHasNoErrors()
        ->assertSet('showImportModal', false);

    $existing->refresh();
    expect($existing->name)->toBe('Updated Product Name');
    expect((float) $existing->price)->toBe(750.00);
    expect((float) $existing->compare_at_price)->toBe(900.00);
    expect($existing->total_stock)->toBe(50);

    // CSV with validation failure (missing name and invalid price)
    $invalidCsv = "Name,Slug,SKU,Price,Compare at Price,Stock,Categories,Product Type,Vendor,Is Active,Is Featured,Description\n"
        .",,BAD-SKU-01,invalid-price,,,Peripherals,,,,,\n";

    $invalidFile = UploadedFile::fake()->createWithContent('invalid_products.csv', $invalidCsv);

    Livewire::test(AdminProductIndex::class)
        ->call('openImportModal')
        ->set('importFile', $invalidFile)
        ->call('import')
        ->assertSet('showImportModal', true)
        ->assertCount('importErrors', 2);

    expect(Product::where('sku', 'BAD-SKU-01')->exists())->toBeFalse();
});

test('dedicated product create page renders and creates product with variant', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->get(route('admin.products.create'))
        ->assertOk()
        ->assertSee('Add Product');

    Livewire::test(Create::class)
        ->set('name', 'Mechanical Keyboard Pro')
        ->set('slug', 'mechanical-keyboard-pro')
        ->set('sku', 'MECH-KB-01')
        ->set('price', '4999.00')
        ->set('description', 'RGB Hot-swappable keyboard')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('admin.products.index'));

    $product = Product::where('sku', 'MECH-KB-01')->first();
    expect($product)->not->toBeNull();
    expect($product->variants)->toHaveCount(1);
    expect($product->variants->first()->title)->toBe('Default Variant');
});

test('dedicated product edit page renders and updates product details', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $product = Product::create([
        'name' => 'Original Tablet',
        'slug' => 'original-tablet',
        'sku' => 'TABLET-01',
        'price' => 19999.00,
        'is_active' => true,
    ]);

    $this->get(route('admin.products.edit', $product->id))
        ->assertOk()
        ->assertSee('Original Tablet');

    Livewire::test(Edit::class, ['product' => $product])
        ->assertSet('name', 'Original Tablet')
        ->assertSet('sku', 'TABLET-01')
        ->set('name', 'Upgraded Tablet Pro')
        ->set('price', '22999.00')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('admin.products.index'));

    $product->refresh();
    expect($product->name)->toBe('Upgraded Tablet Pro');
    expect((float) $product->price)->toBe(22999.00);
});
