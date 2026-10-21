<?php

use App\Livewire\Admin\Categories\Index as AdminCategoryIndex;
use App\Livewire\Admin\Products\Index as AdminProductIndex;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => 'Store Administrator', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Customer', 'guard_name' => 'web']);
});

test('products can be filtered by active and inactive status', function () {
    $admin = User::factory()->create();
    $this->actingAs($admin);

    $activeProd = Product::create([
        'name' => 'Active Shoes',
        'slug' => 'active-shoes',
        'sku' => 'ACT-SHOE-1',
        'price' => 50,
        'is_active' => true,
    ]);

    $inactiveProd = Product::create([
        'name' => 'Draft Shoes',
        'slug' => 'draft-shoes',
        'sku' => 'DFT-SHOE-2',
        'price' => 45,
        'is_active' => false,
    ]);

    // Default view shows both
    Livewire::test(AdminProductIndex::class)
        ->assertSee('Active Shoes')
        ->assertSee('Draft Shoes');

    // Filter by active
    Livewire::test(AdminProductIndex::class)
        ->set('statusFilter', 'active')
        ->assertSee('Active Shoes')
        ->assertDontSee('Draft Shoes');

    // Filter by inactive
    Livewire::test(AdminProductIndex::class)
        ->set('statusFilter', 'inactive')
        ->assertDontSee('Active Shoes')
        ->assertSee('Draft Shoes');
});

test('products can be filtered by category', function () {
    $admin = User::factory()->create();
    $this->actingAs($admin);

    $catElectronics = Category::create([
        'name' => 'Electronics',
        'slug' => 'electronics',
        'collection_type' => 'manual',
        'is_active' => true,
    ]);

    $catClothing = Category::create([
        'name' => 'Clothing',
        'slug' => 'clothing',
        'collection_type' => 'manual',
        'is_active' => true,
    ]);

    $phone = Product::create([
        'name' => 'Smartphone Pro',
        'slug' => 'smartphone-pro',
        'sku' => 'PHONE-1',
        'price' => 800,
        'is_active' => true,
    ]);
    $phone->categories()->attach($catElectronics->id);

    $shirt = Product::create([
        'name' => 'Cotton Shirt',
        'slug' => 'cotton-shirt',
        'sku' => 'SHIRT-1',
        'price' => 30,
        'is_active' => true,
    ]);
    $shirt->categories()->attach($catClothing->id);

    Livewire::test(AdminProductIndex::class)
        ->set('categoryFilter', (string) $catElectronics->id)
        ->assertSee('Smartphone Pro')
        ->assertDontSee('Cotton Shirt');

    Livewire::test(AdminProductIndex::class)
        ->set('categoryFilter', (string) $catClothing->id)
        ->assertDontSee('Smartphone Pro')
        ->assertSee('Cotton Shirt');
});

test('products can be filtered by stock status', function () {
    $admin = User::factory()->create();
    $this->actingAs($admin);

    // In-stock product
    $inStockProd = Product::create([
        'name' => 'In Stock Item',
        'slug' => 'in-stock-item',
        'sku' => 'STK-001',
        'price' => 20,
        'is_active' => true,
    ]);
    $v1 = ProductVariant::create([
        'product_id' => $inStockProd->id,
        'title' => 'Default',
        'sku' => 'STK-001-V',
        'price' => 20,
        'is_active' => true,
    ]);
    Inventory::create([
        'product_variant_id' => $v1->id,
        'quantity' => 15,
        'reserved_quantity' => 0,
    ]);

    // Out-of-stock product
    $outOfStockProd = Product::create([
        'name' => 'Sold Out Item',
        'slug' => 'sold-out-item',
        'sku' => 'OOS-001',
        'price' => 40,
        'is_active' => true,
    ]);
    $v2 = ProductVariant::create([
        'product_id' => $outOfStockProd->id,
        'title' => 'Default',
        'sku' => 'OOS-001-V',
        'price' => 40,
        'is_active' => true,
    ]);
    Inventory::create([
        'product_variant_id' => $v2->id,
        'quantity' => 0,
        'reserved_quantity' => 0,
    ]);

    // Filter in-stock
    Livewire::test(AdminProductIndex::class)
        ->set('stockFilter', 'in_stock')
        ->assertSee('In Stock Item')
        ->assertDontSee('Sold Out Item');

    // Filter out-of-stock
    Livewire::test(AdminProductIndex::class)
        ->set('stockFilter', 'out_of_stock')
        ->assertDontSee('In Stock Item')
        ->assertSee('Sold Out Item');
});

test('products can be filtered by featured status', function () {
    $admin = User::factory()->create();
    $this->actingAs($admin);

    $featured = Product::create([
        'name' => 'Featured Watch',
        'slug' => 'featured-watch',
        'sku' => 'FT-01',
        'price' => 150,
        'is_active' => true,
        'is_featured' => true,
    ]);

    $standard = Product::create([
        'name' => 'Standard Watch',
        'slug' => 'standard-watch',
        'sku' => 'STD-01',
        'price' => 50,
        'is_active' => true,
        'is_featured' => false,
    ]);

    Livewire::test(AdminProductIndex::class)
        ->set('featuredFilter', 'featured')
        ->assertSee('Featured Watch')
        ->assertDontSee('Standard Watch');

    Livewire::test(AdminProductIndex::class)
        ->set('featuredFilter', 'standard')
        ->assertDontSee('Featured Watch')
        ->assertSee('Standard Watch');
});

test('products resetFilters clears all filters and restores full listing', function () {
    $admin = User::factory()->create();
    $this->actingAs($admin);

    $prodA = Product::create(['name' => 'Alpha Item', 'slug' => 'alpha-item', 'sku' => 'ALP-1', 'price' => 10, 'is_active' => true]);
    $prodB = Product::create(['name' => 'Beta Item', 'slug' => 'beta-item', 'sku' => 'BET-1', 'price' => 20, 'is_active' => false]);

    $component = Livewire::test(AdminProductIndex::class)
        ->set('statusFilter', 'inactive')
        ->set('search', 'Beta')
        ->assertSee('Beta Item')
        ->assertDontSee('Alpha Item');

    expect($component->get('hasActiveFilters'))->toBeTrue();

    $component->call('resetFilters')
        ->assertSet('search', '')
        ->assertSet('statusFilter', '')
        ->assertSet('categoryFilter', '')
        ->assertSet('stockFilter', '')
        ->assertSet('featuredFilter', '')
        ->assertSee('Alpha Item')
        ->assertSee('Beta Item');

    expect($component->get('hasActiveFilters'))->toBeFalse();
});

test('products selectAll under active filter only selects filtered products', function () {
    $admin = User::factory()->create();
    $this->actingAs($admin);

    $active = Product::create(['name' => 'Active Only', 'slug' => 'active-only', 'sku' => 'A-01', 'price' => 10, 'is_active' => true]);
    $inactive = Product::create(['name' => 'Inactive Only', 'slug' => 'inactive-only', 'sku' => 'I-01', 'price' => 20, 'is_active' => false]);

    $component = Livewire::test(AdminProductIndex::class)
        ->set('statusFilter', 'active')
        ->set('selectAll', true);

    $selected = $component->get('selected');
    expect($selected)->toContain((string) $active->id);
    expect($selected)->not->toContain((string) $inactive->id);
});

test('categories can be filtered by active and inactive status', function () {
    $admin = User::factory()->create();
    $this->actingAs($admin);

    $activeCat = Category::create(['name' => 'Active Category', 'slug' => 'active-category', 'collection_type' => 'manual', 'is_active' => true]);
    $inactiveCat = Category::create(['name' => 'Archived Category', 'slug' => 'archived-category', 'collection_type' => 'manual', 'is_active' => false]);

    Livewire::test(AdminCategoryIndex::class)
        ->set('statusFilter', 'active')
        ->assertSee('Active Category')
        ->assertDontSee('Archived Category');

    Livewire::test(AdminCategoryIndex::class)
        ->set('statusFilter', 'inactive')
        ->assertDontSee('Active Category')
        ->assertSee('Archived Category');
});

test('categories can be filtered by hierarchy parent and subcategories', function () {
    $admin = User::factory()->create();
    $this->actingAs($admin);

    $rootOnly = Category::create(['name' => 'Root Dept', 'slug' => 'root-dept', 'collection_type' => 'manual', 'is_active' => true]);
    $parent = Category::create(['name' => 'Parent Dept', 'slug' => 'parent-dept', 'collection_type' => 'manual', 'is_active' => true]);
    $sub = Category::create(['name' => 'Sub Section', 'slug' => 'sub-section', 'parent_id' => $parent->id, 'collection_type' => 'manual', 'is_active' => true]);

    // Root only
    Livewire::test(AdminCategoryIndex::class)
        ->set('parentFilter', 'root')
        ->assertSee('Root Dept')
        ->assertSee('Parent Dept')
        ->assertDontSee('Sub Section');

    // Subcategories only
    Livewire::test(AdminCategoryIndex::class)
        ->set('parentFilter', 'sub')
        ->assertDontSee('Root Dept')
        ->assertSee('Sub Section');
});

test('categories can be filtered by collection type', function () {
    $admin = User::factory()->create();
    $this->actingAs($admin);

    $manual = Category::create(['name' => 'Manual Collection', 'slug' => 'manual-col', 'collection_type' => 'manual', 'is_active' => true]);
    $automated = Category::create(['name' => 'Smart Collection', 'slug' => 'smart-col', 'collection_type' => 'automated', 'is_active' => true]);

    Livewire::test(AdminCategoryIndex::class)
        ->set('typeFilter', 'manual')
        ->assertSee('Manual Collection')
        ->assertDontSee('Smart Collection');

    Livewire::test(AdminCategoryIndex::class)
        ->set('typeFilter', 'automated')
        ->assertDontSee('Manual Collection')
        ->assertSee('Smart Collection');
});

test('categories resetFilters clears all filters and restores full listing', function () {
    $admin = User::factory()->create();
    $this->actingAs($admin);

    $cat1 = Category::create(['name' => 'Standalone Cat', 'slug' => 'standalone-cat', 'collection_type' => 'manual', 'is_active' => true]);
    $cat2 = Category::create(['name' => 'Other Cat', 'slug' => 'other-cat', 'collection_type' => 'automated', 'is_active' => false]);

    $component = Livewire::test(AdminCategoryIndex::class)
        ->set('statusFilter', 'inactive')
        ->set('typeFilter', 'automated')
        ->assertSee('Other Cat')
        ->assertDontSee('Standalone Cat');

    expect($component->get('hasActiveFilters'))->toBeTrue();

    $component->call('resetFilters')
        ->assertSet('search', '')
        ->assertSet('statusFilter', '')
        ->assertSet('parentFilter', '')
        ->assertSet('typeFilter', '')
        ->assertSee('Standalone Cat')
        ->assertSee('Other Cat');

    expect($component->get('hasActiveFilters'))->toBeFalse();
});

test('categories selectAll under active filter only selects filtered categories', function () {
    $admin = User::factory()->create();
    $this->actingAs($admin);

    $parent = Category::create(['name' => 'Root Cat', 'slug' => 'root-cat', 'collection_type' => 'manual', 'is_active' => true]);
    $child = Category::create(['name' => 'Child Cat', 'slug' => 'child-cat', 'parent_id' => $parent->id, 'collection_type' => 'manual', 'is_active' => true]);

    $component = Livewire::test(AdminCategoryIndex::class)
        ->set('parentFilter', 'root')
        ->set('selectAll', true);

    $selected = $component->get('selected');
    expect($selected)->toContain((string) $parent->id);
    expect($selected)->not->toContain((string) $child->id);
});
