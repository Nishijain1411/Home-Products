<?php

use App\Livewire\Admin\ThemeSettings\Index as ThemeSettingsIndex;
use App\Models\Category;
use App\Models\ThemeSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Customer', 'guard_name' => 'web']);
});

test('admin can configure and publish announcement bar and section display order', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $theme = ThemeSetting::create([
        'name' => 'Custom Theme',
        'settings' => [],
        'published_settings' => [],
    ]);

    Livewire::test(ThemeSettingsIndex::class)
        ->set('showAnnouncement', true)
        ->set('announcementText', 'Flash Sale: 25% off everything with code FLASH25')
        ->set('announcementLink', '/storefront/shop')
        ->set('announcementBg', '#dc2626')
        ->set('announcementTextColor', '#ffffff')
        ->call('moveSectionDown', 'hero')
        ->call('publishTheme')
        ->assertHasNoErrors();

    $theme->refresh();

    expect($theme->published_settings['announcement']['visible'])->toBeTrue()
        ->and($theme->published_settings['announcement']['text'])->toBe('Flash Sale: 25% off everything with code FLASH25')
        ->and($theme->published_settings['announcement']['bg_color'])->toBe('#dc2626')
        ->and($theme->published_settings['sections_order'][0])->not->toBe('hero');
});

test('storefront displays published promotional announcement banner', function () {
    ThemeSetting::create([
        'name' => 'Live Store Theme',
        'settings' => [],
        'published_settings' => [
            'colors' => [
                'primary' => '#2563eb',
                'secondary' => '#111827',
                'background' => '#ffffff',
                'text' => '#111827',
            ],
            'layout' => ['page_width' => '1280px'],
            'announcement' => [
                'visible' => true,
                'text' => 'Free shipping on orders above ₹499!',
                'link' => '/storefront/shop',
                'bg_color' => '#16a34a',
                'text_color' => '#ffffff',
            ],
            'sections_order' => ['hero', 'categories', 'products', 'cta'],
            'sections' => [
                'hero' => ['visible' => true, 'heading' => 'Welcome to Store'],
            ],
        ],
        'published_at' => now(),
    ]);

    $this->get(route('storefront.home'))
        ->assertOk()
        ->assertSee('Free shipping on orders above ₹499!')
        ->assertSee('/storefront/shop');
});

test('storefront filters featured categories when selected in theme settings', function () {
    $cat1 = Category::create(['name' => 'Electronics', 'slug' => 'electronics', 'is_active' => true]);
    $cat2 = Category::create(['name' => 'Fashion', 'slug' => 'fashion', 'is_active' => true]);

    ThemeSetting::create([
        'name' => 'Selective Theme',
        'settings' => [],
        'published_settings' => [
            'colors' => ['primary' => '#2563eb', 'secondary' => '#111827', 'background' => '#ffffff', 'text' => '#111827'],
            'layout' => ['page_width' => '1280px'],
            'sections_order' => ['categories'],
            'sections' => [
                'categories' => [
                    'visible' => true,
                    'heading' => 'Featured Categories',
                    'description' => 'Browse our picks',
                    'columns' => 3,
                    'category_ids' => [$cat1->id],
                ],
            ],
        ],
        'published_at' => now(),
    ]);

    $this->get(route('storefront.home'))
        ->assertOk()
        ->assertSee('Electronics')
        ->assertDontSee('Fashion');
});
