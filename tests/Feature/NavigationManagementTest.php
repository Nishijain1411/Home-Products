<?php

use App\Livewire\Admin\Navigation\Index as NavigationIndex;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Customer', 'guard_name' => 'web']);
});

test('admin navigation screen requires authentication and renders menus', function () {
    $this->get(route('admin.navigation.index'))
        ->assertRedirect(route('login'));

    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.navigation.index'))
        ->assertOk()
        ->assertSee('Navigation Menus')
        ->assertSee('Main Header')
        ->assertSee('Footer Store Links');
});

test('admin can add a menu item linked to a CMS page', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $page = Page::create([
        'title' => 'Story Page',
        'slug' => 'story',
        'content' => 'Story content',
        'is_published' => true,
    ]);

    $headerMenu = Menu::firstOrCreate(
        ['location' => 'header'],
        ['name' => 'Header Navigation', 'is_active' => true]
    );

    Livewire::test(NavigationIndex::class)
        ->set('activeLocation', 'header')
        ->call('openCreateItem')
        ->set('quickLinkType', 'page')
        ->set('selectedPageId', $page->id)
        ->set('itemTitle', 'Our Story')
        ->call('saveItem');

    $item = MenuItem::where('menu_id', $headerMenu->id)->where('title', 'Our Story')->first();

    expect($item)->not->toBeNull()
        ->and($item->url)->toBe('/page/story')
        ->and($item->sort_order)->toBe(1);
});

test('admin can add nested child menu items', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $headerMenu = Menu::firstOrCreate(
        ['location' => 'header'],
        ['name' => 'Header Navigation', 'is_active' => true]
    );

    $parentItem = MenuItem::create([
        'menu_id' => $headerMenu->id,
        'title' => 'Shop Categories',
        'url' => '#',
        'sort_order' => 1,
        'is_active' => true,
    ]);

    Livewire::test(NavigationIndex::class)
        ->set('activeLocation', 'header')
        ->call('openCreateItem', $parentItem->id)
        ->set('itemTitle', 'Electronics')
        ->set('itemUrl', '/category/electronics')
        ->call('saveItem');

    $child = MenuItem::where('parent_id', $parentItem->id)->first();

    expect($child)->not->toBeNull()
        ->and($child->title)->toBe('Electronics')
        ->and($child->url)->toBe('/category/electronics');
});

test('admin can reorder menu items', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $menu = Menu::firstOrCreate(['location' => 'header'], ['name' => 'Header', 'is_active' => true]);

    $item1 = MenuItem::create(['menu_id' => $menu->id, 'title' => 'First', 'url' => '/first', 'sort_order' => 1, 'is_active' => true]);
    $item2 = MenuItem::create(['menu_id' => $menu->id, 'title' => 'Second', 'url' => '/second', 'sort_order' => 2, 'is_active' => true]);

    Livewire::test(NavigationIndex::class)
        ->set('activeLocation', 'header')
        ->call('moveDown', $item1->id);

    expect($item1->fresh()->sort_order)->toBe(2)
        ->and($item2->fresh()->sort_order)->toBe(1);
});

test('admin can toggle item active status and delete item', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $menu = Menu::firstOrCreate(['location' => 'header'], ['name' => 'Header', 'is_active' => true]);
    $item = MenuItem::create(['menu_id' => $menu->id, 'title' => 'Sample', 'url' => '/sample', 'sort_order' => 1, 'is_active' => true]);

    Livewire::test(NavigationIndex::class)
        ->set('activeLocation', 'header')
        ->call('toggleItemStatus', $item->id);

    expect($item->fresh()->is_active)->toBeFalse();

    Livewire::test(NavigationIndex::class)
        ->set('activeLocation', 'header')
        ->call('confirmDeleteItem', $item->id)
        ->call('deleteItem');

    expect(MenuItem::find($item->id))->toBeNull();
});

test('storefront header and footer render dynamic menus', function () {
    $headerMenu = Menu::updateOrCreate(
        ['location' => 'header'],
        ['name' => 'Header', 'is_active' => true]
    );

    MenuItem::create([
        'menu_id' => $headerMenu->id,
        'title' => 'Special Spotlight',
        'url' => '/storefront/spotlight',
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $footerStoreMenu = Menu::updateOrCreate(
        ['location' => 'footer_store'],
        ['name' => 'Shop Collection', 'is_active' => true]
    );

    MenuItem::create([
        'menu_id' => $footerStoreMenu->id,
        'title' => 'Trending Deals',
        'url' => '/storefront/deals',
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $this->get(route('storefront.home'))
        ->assertOk()
        ->assertSee('Special Spotlight')
        ->assertSee('/storefront/spotlight')
        ->assertSee('Shop Collection')
        ->assertSee('Trending Deals')
        ->assertSee('/storefront/deals');
});
