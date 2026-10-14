<?php

use App\Livewire\Admin\Categories\Create;
use App\Livewire\Admin\Categories\Edit;
use App\Livewire\Admin\Categories\Index as AdminCategoryIndex;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\StreamedResponse;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => 'Customer', 'guard_name' => 'web']);
    Storage::fake('public');
});

test('can create a category with an uploaded image', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $file = UploadedFile::fake()->image('category.jpg', 600, 600);

    Livewire::test(AdminCategoryIndex::class)
        ->set('name', 'Footwear')
        ->set('slug', 'footwear')
        ->set('image', $file)
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet('showModal', false);

    $category = Category::where('slug', 'footwear')->first();
    expect($category)->not->toBeNull();
    expect($category->image)->not->toBeNull();

    Storage::disk('public')->assertExists($category->image);
});

test('editing a category preserves existing image if no new image is uploaded', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $file = UploadedFile::fake()->image('original.png', 400, 400);
    $path = $file->store('categories', 'public');

    $category = Category::create([
        'name' => 'Original Category',
        'slug' => 'original-category',
        'image' => $path,
        'collection_type' => 'manual',
        'is_active' => true,
    ]);

    Livewire::test(AdminCategoryIndex::class)
        ->call('openEdit', $category->id)
        ->assertSet('existingImage', $path)
        ->assertSet('image', null)
        ->set('name', 'Renamed Category')
        ->call('save')
        ->assertHasNoErrors();

    $category->refresh();
    expect($category->name)->toBe('Renamed Category');
    expect($category->image)->toBe($path);
    Storage::disk('public')->assertExists($path);
});

test('uploading a new image during edit replaces and deletes old image', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $oldFile = UploadedFile::fake()->image('old.jpg', 400, 400);
    $oldPath = $oldFile->store('categories', 'public');

    $category = Category::create([
        'name' => 'Tech',
        'slug' => 'tech',
        'image' => $oldPath,
        'collection_type' => 'manual',
        'is_active' => true,
    ]);

    Storage::disk('public')->assertExists($oldPath);

    $newFile = UploadedFile::fake()->image('new.jpg', 500, 500);

    Livewire::test(AdminCategoryIndex::class)
        ->call('openEdit', $category->id)
        ->set('image', $newFile)
        ->call('save')
        ->assertHasNoErrors();

    $category->refresh();
    expect($category->image)->not->toBe($oldPath);
    Storage::disk('public')->assertMissing($oldPath);
    Storage::disk('public')->assertExists($category->image);
});

test('can remove existing image from category', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $file = UploadedFile::fake()->image('delete-me.jpg', 400, 400);
    $path = $file->store('categories', 'public');

    $category = Category::create([
        'name' => 'Accessories',
        'slug' => 'accessories',
        'image' => $path,
        'collection_type' => 'manual',
        'is_active' => true,
    ]);

    Livewire::test(AdminCategoryIndex::class)
        ->call('openEdit', $category->id)
        ->call('removeExistingImage')
        ->assertSet('existingImage', null);

    $category->refresh();
    expect($category->image)->toBeNull();
    Storage::disk('public')->assertMissing($path);
});

test('validates category image format and size', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $invalidFile = UploadedFile::fake()->create('document.pdf', 500, 'application/pdf');

    Livewire::test(AdminCategoryIndex::class)
        ->set('name', 'Invalid Image Category')
        ->set('slug', 'invalid-image-category')
        ->set('image', $invalidFile)
        ->call('save')
        ->assertHasErrors(['image']);
});

test('dedicated category create page can be rendered and creates a category', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->get(route('admin.categories.create'))
        ->assertOk()
        ->assertSee('Add Category');

    Livewire::test(Create::class)
        ->set('name', 'Outdoor Gear')
        ->set('slug', 'outdoor-gear')
        ->set('description', 'Gear for camping and outdoor activities')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('admin.categories.index'));

    expect(Category::where('slug', 'outdoor-gear')->exists())->toBeTrue();
});

test('dedicated category edit page can be rendered and updates category', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $category = Category::create([
        'name' => 'Home Appliances',
        'slug' => 'home-appliances',
        'collection_type' => 'manual',
        'is_active' => true,
    ]);

    $this->get(route('admin.categories.edit', $category->id))
        ->assertOk()
        ->assertSee('Edit Category')
        ->assertSee('Home Appliances');

    Livewire::test(Edit::class, ['category' => $category])
        ->assertSet('name', 'Home Appliances')
        ->assertSet('slug', 'home-appliances')
        ->set('name', 'Kitchen & Home Appliances')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('admin.categories.index'));

    $category->refresh();
    expect($category->name)->toBe('Kitchen & Home Appliances');
});

test('can export categories to csv stream', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $parent = Category::create([
        'name' => 'Electronics',
        'slug' => 'electronics',
        'collection_type' => 'manual',
        'is_active' => true,
        'sort_order' => 1,
    ]);

    $child = Category::create([
        'name' => 'Laptops',
        'slug' => 'laptops',
        'parent_id' => $parent->id,
        'collection_type' => 'manual',
        'is_active' => true,
        'sort_order' => 2,
    ]);

    $component = Livewire::test(AdminCategoryIndex::class);
    $response = $component->instance()->export();

    expect($response)->toBeInstanceOf(StreamedResponse::class);

    ob_start();
    $response->sendContent();
    $csvContent = ob_get_clean();

    expect($csvContent)->toContain('Name');
    expect($csvContent)->toContain('Parent Category');
    expect($csvContent)->toContain('Electronics');
    expect($csvContent)->toContain('Laptops');
});

test('can download sample category csv template', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $component = Livewire::test(AdminCategoryIndex::class);
    $response = $component->instance()->exportSample();

    expect($response)->toBeInstanceOf(StreamedResponse::class);

    ob_start();
    $response->sendContent();
    $csvContent = ob_get_clean();

    expect($csvContent)->toContain('Name');
    expect($csvContent)->toContain('Parent Category');
    expect($csvContent)->toContain('Electronics');
    expect($csvContent)->toContain('Smartphones');
    expect($csvContent)->toContain('Laptops & Computers');
});

test('can bulk import categories from csv creating parent and child categories', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $csv = "Name,Slug,Parent Category,Collection Type,Is Active,Sort Order,Description\n"
        ."Audio,audio,,manual,1,1,Audio equipment and accessories\n"
        ."Headphones,headphones,Audio,manual,1,2,Over-ear and in-ear headphones\n"
        ."Wearables,,Audio,manual,1,3,Smart watches and fitness bands\n";

    $file = UploadedFile::fake()->createWithContent('categories.csv', $csv);

    Livewire::test(AdminCategoryIndex::class)
        ->set('importFile', $file)
        ->call('import')
        ->assertHasNoErrors()
        ->assertSet('showImportModal', false)
        ->assertSet('importErrors', [])
        ->assertSet('successMessage', 'Successfully imported 3 categories (3 created, 0 updated).')
        ->assertSee('Successfully imported 3 categories (3 created, 0 updated).')
        ->assertSee('Operation Successful');

    // Verify parent category
    $parent = Category::where('slug', 'audio')->first();
    expect($parent)->not->toBeNull();
    expect($parent->name)->toBe('Audio');
    expect($parent->parent_id)->toBeNull();

    // Verify child category
    $child = Category::where('slug', 'headphones')->first();
    expect($child)->not->toBeNull();
    expect($child->name)->toBe('Headphones');
    expect($child->parent_id)->toBe($parent->id);

    // Verify auto-slug child category
    $child2 = Category::where('slug', 'wearables')->first();
    expect($child2)->not->toBeNull();
    expect($child2->name)->toBe('Wearables');
    expect($child2->parent_id)->toBe($parent->id);
});

test('bulk import correctly links parent categories even when child category appears first in csv', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    // Child category listed first, parent listed second
    $csv = "Name,Slug,Parent Category,Collection Type,Is Active,Sort Order,Description\n"
        ."Keyboards,keyboards,Computers,manual,1,2,Mechanical and membrane keyboards\n"
        ."Computers,computers,,manual,1,1,Computers and desktop devices\n";

    $file = UploadedFile::fake()->createWithContent('categories.csv', $csv);

    Livewire::test(AdminCategoryIndex::class)
        ->set('importFile', $file)
        ->call('import')
        ->assertHasNoErrors()
        ->assertSet('showImportModal', false);

    $parent = Category::where('slug', 'computers')->first();
    $child = Category::where('slug', 'keyboards')->first();

    expect($parent)->not->toBeNull();
    expect($child)->not->toBeNull();
    expect($child->parent_id)->toBe($parent->id);
});

test('bulk import updates existing category matching slug or name', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $category = Category::create([
        'name' => 'Old Name',
        'slug' => 'lighting',
        'collection_type' => 'manual',
        'is_active' => true,
    ]);

    $csv = "Name,Slug,Parent Category,Collection Type,Is Active,Sort Order,Description\n"
        ."Modern Lighting,lighting,,manual,1,5,Modern home and office lighting solutions\n";

    $file = UploadedFile::fake()->createWithContent('categories.csv', $csv);

    Livewire::test(AdminCategoryIndex::class)
        ->set('importFile', $file)
        ->call('import')
        ->assertHasNoErrors()
        ->assertSet('successMessage', 'Successfully imported 1 categories (0 created, 1 updated).');

    $category->refresh();
    expect($category->name)->toBe('Modern Lighting');
    expect($category->description)->toBe('Modern home and office lighting solutions');
    expect($category->sort_order)->toBe(5);
    expect(Category::count())->toBe(1);
});

test('bulk import validates missing headers and invalid collection type', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    // Missing Name header
    $invalidCsv = "Slug,Description\nfoo,bar\n";
    $file = UploadedFile::fake()->createWithContent('invalid.csv', $invalidCsv);

    Livewire::test(AdminCategoryIndex::class)
        ->set('importFile', $file)
        ->call('import')
        ->assertHasErrors(['importFile']);

    // Invalid Collection Type in row
    $invalidTypeCsv = "Name,Collection Type\nValid Name,invalid_type\n";
    $file2 = UploadedFile::fake()->createWithContent('invalid_type.csv', $invalidTypeCsv);

    Livewire::test(AdminCategoryIndex::class)
        ->set('importFile', $file2)
        ->call('import')
        ->assertHasNoErrors()
        ->assertSet('importErrors', [
            "Row 2: Collection Type must be either 'manual' or 'automated'.",
        ]);
});
