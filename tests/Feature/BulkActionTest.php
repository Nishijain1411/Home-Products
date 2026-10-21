<?php

use App\Livewire\Admin\Categories\Index as AdminCategoryIndex;
use App\Livewire\Admin\Products\Index as AdminProductIndex;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => 'Customer', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Store Administrator', 'guard_name' => 'web']);
    Storage::fake('public');
});

test('products bulk activate updates status of products and variants and clears selection', function () {
    $admin = User::factory()->create();
    $this->actingAs($admin);

    $p1 = Product::create(['name' => 'Prod 1', 'slug' => 'prod-1', 'sku' => 'SKU-1', 'price' => 10, 'is_active' => false]);
    $v1 = ProductVariant::create(['product_id' => $p1->id, 'title' => 'Var 1', 'sku' => 'SKU-1-V', 'price' => 10, 'is_active' => false]);

    $p2 = Product::create(['name' => 'Prod 2', 'slug' => 'prod-2', 'sku' => 'SKU-2', 'price' => 20, 'is_active' => false]);
    $v2 = ProductVariant::create(['product_id' => $p2->id, 'title' => 'Var 2', 'sku' => 'SKU-2-V', 'price' => 20, 'is_active' => false]);

    $p3 = Product::create(['name' => 'Prod 3', 'slug' => 'prod-3', 'sku' => 'SKU-3', 'price' => 30, 'is_active' => false]);
    $v3 = ProductVariant::create(['product_id' => $p3->id, 'title' => 'Var 3', 'sku' => 'SKU-3-V', 'price' => 30, 'is_active' => false]);

    $component = Livewire::test(AdminProductIndex::class)
        ->set('selected', [(string) $p1->id, (string) $p2->id])
        ->call('bulkActivate')
        ->assertSet('selected', [])
        ->assertSet('selectAll', false);

    expect($component->get('successMessage'))->toContain('2 products activated successfully');
    expect($p1->fresh()->is_active)->toBeTrue();
    expect($v1->fresh()->is_active)->toBeTrue();
    expect($p2->fresh()->is_active)->toBeTrue();
    expect($v2->fresh()->is_active)->toBeTrue();
    expect($p3->fresh()->is_active)->toBeFalse();
    expect($v3->fresh()->is_active)->toBeFalse();
});

test('products bulk deactivate updates status of products and variants and clears selection', function () {
    $admin = User::factory()->create();
    $this->actingAs($admin);

    $p1 = Product::create(['name' => 'Active 1', 'slug' => 'act-1', 'sku' => 'SKU-A1', 'price' => 10, 'is_active' => true]);
    $v1 = ProductVariant::create(['product_id' => $p1->id, 'title' => 'Var A1', 'sku' => 'SKU-A1-V', 'price' => 10, 'is_active' => true]);

    $p2 = Product::create(['name' => 'Active 2', 'slug' => 'act-2', 'sku' => 'SKU-A2', 'price' => 20, 'is_active' => true]);
    $v2 = ProductVariant::create(['product_id' => $p2->id, 'title' => 'Var A2', 'sku' => 'SKU-A2-V', 'price' => 20, 'is_active' => true]);

    $component = Livewire::test(AdminProductIndex::class)
        ->set('selected', [(string) $p1->id, (string) $p2->id])
        ->call('bulkDeactivate')
        ->assertSet('selected', [])
        ->assertSet('selectAll', false);

    expect($component->get('successMessage'))->toContain('2 products deactivated successfully');
    expect($p1->fresh()->is_active)->toBeFalse();
    expect($v1->fresh()->is_active)->toBeFalse();
    expect($p2->fresh()->is_active)->toBeFalse();
    expect($v2->fresh()->is_active)->toBeFalse();
});

test('products bulk delete protects products with active inventory reservations and deletes safe ones', function () {
    $admin = User::factory()->create();
    $this->actingAs($admin);

    // Protected product: active reservation
    $protected = Product::create(['name' => 'Reserved Product', 'slug' => 'reserved-prod', 'sku' => 'RES-1', 'price' => 50, 'is_active' => true]);
    $pVariant = ProductVariant::create(['product_id' => $protected->id, 'title' => 'Default', 'sku' => 'RES-1-V', 'price' => 50, 'is_active' => true]);
    Inventory::create(['product_variant_id' => $pVariant->id, 'quantity' => 10, 'reserved_quantity' => 2]);

    // Deletable product: with category pivot and image
    $deletable = Product::create(['name' => 'Deletable Product', 'slug' => 'del-prod', 'sku' => 'DEL-1', 'price' => 25, 'is_active' => true]);
    $dVariant = ProductVariant::create(['product_id' => $deletable->id, 'title' => 'Default', 'sku' => 'DEL-1-V', 'price' => 25, 'is_active' => true]);
    Inventory::create(['product_variant_id' => $dVariant->id, 'quantity' => 5, 'reserved_quantity' => 0]);

    $category = Category::create(['name' => 'Pivot Cat', 'slug' => 'pivot-cat', 'collection_type' => 'manual', 'is_active' => true]);
    $deletable->categories()->attach($category->id);

    $file = UploadedFile::fake()->image('prod.jpg');
    $path = $file->store('products', 'public');
    ProductImage::create(['product_id' => $deletable->id, 'path' => $path, 'is_primary' => true, 'sort_order' => 0]);

    Storage::disk('public')->assertExists($path);

    $component = Livewire::test(AdminProductIndex::class)
        ->set('selected', [(string) $protected->id, (string) $deletable->id])
        ->call('bulkDelete')
        ->assertSet('selected', [])
        ->assertSet('showBulkDeleteModal', false);

    expect($component->get('successMessage'))->toContain('1 product deleted successfully');
    expect($component->get('successMessage'))->toContain('Skipped (Reserved Product)');

    // Deletable should be deleted and image removed
    expect(Product::find($deletable->id))->toBeNull();
    Storage::disk('public')->assertMissing($path);

    // Protected product must NOT be deleted
    expect(Product::find($protected->id))->not->toBeNull();
});

test('products bulk delete blocks deletion entirely when all selected products have active reservations', function () {
    $admin = User::factory()->create();
    $this->actingAs($admin);

    $p = Product::create(['name' => 'Held Product', 'slug' => 'held-prod', 'sku' => 'HELD-1', 'price' => 100, 'is_active' => true]);
    $v = ProductVariant::create(['product_id' => $p->id, 'title' => 'Def', 'sku' => 'HELD-1-V', 'price' => 100, 'is_active' => true]);
    Inventory::create(['product_variant_id' => $v->id, 'quantity' => 5, 'reserved_quantity' => 1]);

    $component = Livewire::test(AdminProductIndex::class)
        ->set('selected', [(string) $p->id])
        ->call('bulkDelete');

    expect($component->get('errorMessage'))->toContain('Cannot delete selected products');
    expect(Product::find($p->id))->not->toBeNull();
});

test('products bulk select all selects all items on the current page only', function () {
    $admin = User::factory()->create();
    $this->actingAs($admin);

    for ($i = 1; $i <= 20; $i++) {
        Product::create([
            'name' => "Product {$i}",
            'slug' => "product-{$i}",
            'sku' => "SKU-PAGE-{$i}",
            'price' => 10 + $i,
            'is_active' => true,
        ]);
    }

    $component = Livewire::test(AdminProductIndex::class)
        ->set('selectAll', true);

    $selected = $component->get('selected');
    // Paginated at 15 per page: only 15 selected
    expect(count($selected))->toBe(15);

    // Toggling selectAll off clears the selection for current page
    $component->set('selectAll', false);
    expect($component->get('selected'))->toBe([]);
});

test('categories bulk activate updates status and clears selection', function () {
    $admin = User::factory()->create();
    $this->actingAs($admin);

    $c1 = Category::create(['name' => 'Cat 1', 'slug' => 'cat-1', 'collection_type' => 'manual', 'is_active' => false]);
    $c2 = Category::create(['name' => 'Cat 2', 'slug' => 'cat-2', 'collection_type' => 'manual', 'is_active' => false]);
    $c3 = Category::create(['name' => 'Cat 3', 'slug' => 'cat-3', 'collection_type' => 'manual', 'is_active' => false]);

    $component = Livewire::test(AdminCategoryIndex::class)
        ->set('selected', [(string) $c1->id, (string) $c2->id])
        ->call('bulkActivate')
        ->assertSet('selected', [])
        ->assertSet('selectAll', false);

    expect($component->get('successMessage'))->toContain('2 categories activated successfully');
    expect($c1->fresh()->is_active)->toBeTrue();
    expect($c2->fresh()->is_active)->toBeTrue();
    expect($c3->fresh()->is_active)->toBeFalse();
});

test('categories bulk deactivate updates status and clears selection', function () {
    $admin = User::factory()->create();
    $this->actingAs($admin);

    $c1 = Category::create(['name' => 'Active Cat 1', 'slug' => 'act-cat-1', 'collection_type' => 'manual', 'is_active' => true]);
    $c2 = Category::create(['name' => 'Active Cat 2', 'slug' => 'act-cat-2', 'collection_type' => 'manual', 'is_active' => true]);

    $component = Livewire::test(AdminCategoryIndex::class)
        ->set('selected', [(string) $c1->id, (string) $c2->id])
        ->call('bulkDeactivate')
        ->assertSet('selected', [])
        ->assertSet('selectAll', false);

    expect($component->get('successMessage'))->toContain('2 categories deactivated successfully');
    expect($c1->fresh()->is_active)->toBeFalse();
    expect($c2->fresh()->is_active)->toBeFalse();
});

test('categories bulk delete blocks deletion when categories have assigned products or subcategories', function () {
    $admin = User::factory()->create();
    $this->actingAs($admin);

    // Category with products
    $cWithProduct = Category::create(['name' => 'Apparel', 'slug' => 'apparel', 'collection_type' => 'manual', 'is_active' => true]);
    $prod = Product::create(['name' => 'Shirt', 'slug' => 'shirt', 'sku' => 'SHIRT-1', 'price' => 20, 'is_active' => true]);
    $cWithProduct->products()->attach($prod->id);

    // Category with subcategory
    $parentCat = Category::create(['name' => 'Electronics', 'slug' => 'electronics', 'collection_type' => 'manual', 'is_active' => true]);
    Category::create(['name' => 'Audio', 'slug' => 'audio', 'parent_id' => $parentCat->id, 'collection_type' => 'manual', 'is_active' => true]);

    // Test deleting category with products
    $comp1 = Livewire::test(AdminCategoryIndex::class)
        ->set('selected', [(string) $cWithProduct->id])
        ->call('bulkDelete');

    expect($comp1->get('errorMessage'))->toContain('assigned product');
    expect(Category::find($cWithProduct->id))->not->toBeNull();

    // Test deleting category with subcategories
    $comp2 = Livewire::test(AdminCategoryIndex::class)
        ->set('selected', [(string) $parentCat->id])
        ->call('bulkDelete');

    expect($comp2->get('errorMessage'))->toContain('subcategory');
    expect(Category::find($parentCat->id))->not->toBeNull();
});

test('categories bulk delete deletes safe categories and skips protected ones with clear feedback', function () {
    $admin = User::factory()->create();
    $this->actingAs($admin);

    $protectedCat = Category::create(['name' => 'Protected Parent', 'slug' => 'prot-parent', 'collection_type' => 'manual', 'is_active' => true]);
    Category::create(['name' => 'Child Category', 'slug' => 'child-cat', 'parent_id' => $protectedCat->id, 'collection_type' => 'manual', 'is_active' => true]);

    $file = UploadedFile::fake()->image('cat.png');
    $imagePath = $file->store('categories', 'public');
    $safeCat = Category::create(['name' => 'Safe Category', 'slug' => 'safe-cat', 'image' => $imagePath, 'collection_type' => 'manual', 'is_active' => true]);

    Storage::disk('public')->assertExists($imagePath);

    $component = Livewire::test(AdminCategoryIndex::class)
        ->set('selected', [(string) $protectedCat->id, (string) $safeCat->id])
        ->call('bulkDelete')
        ->assertSet('selected', [])
        ->assertSet('showBulkDeleteModal', false);

    expect($component->get('successMessage'))->toContain('1 category deleted successfully');
    expect($component->get('successMessage'))->toContain('Skipped: Protected Parent');

    // Safe category deleted & image removed
    expect(Category::find($safeCat->id))->toBeNull();
    Storage::disk('public')->assertMissing($imagePath);

    // Protected category preserved
    expect(Category::find($protectedCat->id))->not->toBeNull();
});

test('categories bulk select all selects all items on the current page only', function () {
    $admin = User::factory()->create();
    $this->actingAs($admin);

    for ($i = 1; $i <= 20; $i++) {
        Category::create([
            'name' => "Category {$i}",
            'slug' => "category-{$i}",
            'collection_type' => 'manual',
            'is_active' => true,
        ]);
    }

    $component = Livewire::test(AdminCategoryIndex::class)
        ->set('selectAll', true);

    $selected = $component->get('selected');
    // Paginated at 15 per page
    expect(count($selected))->toBe(15);

    $component->set('selectAll', false);
    expect($component->get('selected'))->toBe([]);
});

test('bulk actions handle empty selection and invalid ids gracefully', function () {
    $admin = User::factory()->create();
    $this->actingAs($admin);

    $comp1 = Livewire::test(AdminProductIndex::class)
        ->set('selected', [])
        ->call('bulkActivate');
    expect($comp1->get('errorMessage'))->toContain('No products were selected');

    $comp2 = Livewire::test(AdminProductIndex::class)
        ->set('selected', ['invalid', '-5'])
        ->call('bulkActivate')
        ->assertSet('selected', []);
    expect($comp2->get('errorMessage'))->toContain('selected product IDs are invalid');

    $comp3 = Livewire::test(AdminCategoryIndex::class)
        ->set('selected', [])
        ->call('bulkActivate');
    expect($comp3->get('errorMessage'))->toContain('No categories were selected');

    $comp4 = Livewire::test(AdminCategoryIndex::class)
        ->set('selected', ['invalid', '0'])
        ->call('bulkActivate')
        ->assertSet('selected', []);
    expect($comp4->get('errorMessage'))->toContain('selected category IDs are invalid');
});

test('unauthorized users with customer role cannot perform bulk actions on products or categories', function () {
    $customer = User::factory()->create();
    $customer->assignRole('Customer');
    $this->actingAs($customer);

    $p = Product::create(['name' => 'P1', 'slug' => 'p1', 'sku' => 'SKU-C1', 'price' => 10, 'is_active' => true]);
    $c = Category::create(['name' => 'C1', 'slug' => 'c1', 'collection_type' => 'manual', 'is_active' => true]);

    Livewire::test(AdminProductIndex::class)
        ->set('selected', [(string) $p->id])
        ->call('bulkActivate')
        ->assertForbidden();

    Livewire::test(AdminCategoryIndex::class)
        ->set('selected', [(string) $c->id])
        ->call('bulkActivate')
        ->assertForbidden();
});

test('products bulk assign category appends categories to existing categories', function () {
    $admin = User::factory()->create();
    $this->actingAs($admin);

    $catA = Category::create(['name' => 'Cat A', 'slug' => 'cat-a', 'collection_type' => 'manual', 'is_active' => true]);
    $catB = Category::create(['name' => 'Cat B', 'slug' => 'cat-b', 'collection_type' => 'manual', 'is_active' => true]);

    $p1 = Product::create(['name' => 'P1', 'slug' => 'p1', 'sku' => 'SKU-BC-1', 'price' => 15, 'is_active' => true]);
    $p1->categories()->attach($catA->id);

    $p2 = Product::create(['name' => 'P2', 'slug' => 'p2', 'sku' => 'SKU-BC-2', 'price' => 25, 'is_active' => true]);

    $component = Livewire::test(AdminProductIndex::class)
        ->set('selected', [(string) $p1->id, (string) $p2->id])
        ->call('openBulkCategoryModal')
        ->assertSet('showBulkCategoryModal', true)
        ->set('bulkCategoryIds', [(string) $catB->id])
        ->set('bulkCategoryMode', 'append')
        ->set('bulkStatusMode', 'keep')
        ->call('bulkAssignCategory')
        ->assertSet('showBulkCategoryModal', false)
        ->assertSet('selected', []);

    expect($component->get('successMessage'))->toContain('Assigned 1 category to 2 products');

    // p1 has both catA and catB
    expect($p1->fresh()->categories->pluck('id')->all())->toEqualCanonicalizing([$catA->id, $catB->id]);
    // p2 has catB
    expect($p2->fresh()->categories->pluck('id')->all())->toEqualCanonicalizing([$catB->id]);
});

test('products bulk assign category replaces existing categories when replace mode is selected', function () {
    $admin = User::factory()->create();
    $this->actingAs($admin);

    $catA = Category::create(['name' => 'Old Cat', 'slug' => 'old-cat', 'collection_type' => 'manual', 'is_active' => true]);
    $catB = Category::create(['name' => 'New Cat B', 'slug' => 'new-cat-b', 'collection_type' => 'manual', 'is_active' => true]);
    $catC = Category::create(['name' => 'New Cat C', 'slug' => 'new-cat-c', 'collection_type' => 'manual', 'is_active' => true]);

    $p = Product::create(['name' => 'Product Replace', 'slug' => 'prod-replace', 'sku' => 'SKU-REP-1', 'price' => 30, 'is_active' => true]);
    $p->categories()->attach($catA->id);

    Livewire::test(AdminProductIndex::class)
        ->set('selected', [(string) $p->id])
        ->set('bulkCategoryIds', [(string) $catB->id, (string) $catC->id])
        ->set('bulkCategoryMode', 'replace')
        ->set('bulkStatusMode', 'keep')
        ->call('bulkAssignCategory')
        ->assertSet('selected', []);

    // Old category A is replaced by B and C
    expect($p->fresh()->categories->pluck('id')->all())->toEqualCanonicalizing([$catB->id, $catC->id]);
});

test('products bulk assign category can update status in bulk at the same time', function () {
    $admin = User::factory()->create();
    $this->actingAs($admin);

    $cat = Category::create(['name' => 'Gadgets', 'slug' => 'gadgets', 'collection_type' => 'manual', 'is_active' => true]);

    $p = Product::create(['name' => 'Inactive Gadget', 'slug' => 'inact-gadget', 'sku' => 'INACT-01', 'price' => 99, 'is_active' => false]);
    $v = ProductVariant::create(['product_id' => $p->id, 'title' => 'Default', 'sku' => 'INACT-01-V', 'price' => 99, 'is_active' => false]);

    // Bulk assign category AND set to active
    $component = Livewire::test(AdminProductIndex::class)
        ->set('selected', [(string) $p->id])
        ->set('bulkCategoryIds', [(string) $cat->id])
        ->set('bulkCategoryMode', 'append')
        ->set('bulkStatusMode', 'active')
        ->call('bulkAssignCategory');

    expect($component->get('successMessage'))->toContain('and set to Active');
    expect($p->fresh()->categories->pluck('id')->all())->toContain($cat->id);
    expect($p->fresh()->is_active)->toBeTrue();
    expect($v->fresh()->is_active)->toBeTrue();

    // Now test setting to inactive along with category assignment
    $cat2 = Category::create(['name' => 'Accessories', 'slug' => 'accessories', 'collection_type' => 'manual', 'is_active' => true]);

    $component2 = Livewire::test(AdminProductIndex::class)
        ->set('selected', [(string) $p->id])
        ->set('bulkCategoryIds', [(string) $cat2->id])
        ->set('bulkCategoryMode', 'append')
        ->set('bulkStatusMode', 'inactive')
        ->call('bulkAssignCategory');

    expect($component2->get('successMessage'))->toContain('and set to Inactive');
    expect($p->fresh()->categories->pluck('id')->all())->toContain($cat2->id);
    expect($p->fresh()->is_active)->toBeFalse();
    expect($v->fresh()->is_active)->toBeFalse();
});

test('products bulk assign category validates that at least one category is selected', function () {
    $admin = User::factory()->create();
    $this->actingAs($admin);

    $p = Product::create(['name' => 'Prod Val', 'slug' => 'prod-val', 'sku' => 'VAL-01', 'price' => 10, 'is_active' => true]);

    Livewire::test(AdminProductIndex::class)
        ->set('selected', [(string) $p->id])
        ->set('bulkCategoryIds', [])
        ->call('bulkAssignCategory')
        ->assertHasErrors(['bulkCategoryIds']);
});
