<?php

use App\Livewire\Admin\ThemeSettings\Index as ThemeSettingsIndex;
use App\Models\ThemeSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('authenticated user can view the theme settings page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.theme-settings.index'))
        ->assertOk()
        ->assertSee('Theme Settings')
        ->assertSee('Hero Section')
        ->assertSee('Categories Section')
        ->assertSee('Featured Products Section')
        ->assertSee('Call to Action (CTA) Banner');
});

test('theme settings component mounts with initial default values', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(ThemeSettingsIndex::class)
        ->assertSet('themeName', 'Default Theme')
        ->assertSet('showHero', true)
        ->assertSet('heroHeading', 'Everything you need,')
        ->assertSet('showCategories', true)
        ->assertSet('categoryHeading', 'Shop by Category')
        ->assertSet('showProducts', true)
        ->assertSet('productsHeading', 'Featured Products')
        ->assertSet('showCta', true)
        ->assertSet('ctaHeading', "Discover something you'll love.");
});

test('it saves draft theme settings with all storefront sections', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(ThemeSettingsIndex::class)
        ->set('themeName', 'Summer Sale Theme')
        ->set('primaryColor', '#ef4444')
        ->set('secondaryColor', '#0f172a')
        ->set('backgroundColor', '#f8fafc')
        ->set('textColor', '#020617')
        ->set('pageWidth', '1440px')
        // Hero
        ->set('showHero', true)
        ->set('heroEyebrow', 'Summer Deals')
        ->set('heroHeading', 'Hot Summer Discounts')
        ->set('heroHighlight', 'Up to 50% Off')
        ->set('heroDescription', 'Check out our seasonal items available now.')
        ->set('heroPrimaryButtonText', 'Explore Sale')
        ->set('heroPrimaryButtonLink', '#products')
        ->set('heroSecondaryButtonText', 'Browse All')
        ->set('heroSecondaryButtonLink', '#categories')
        // Categories
        ->set('showCategories', true)
        ->set('categoryHeading', 'Popular Categories')
        ->set('categoryDescription', 'Find what you love by category.')
        ->set('categoryColumns', 3)
        // Products
        ->set('showProducts', true)
        ->set('productsHeading', 'Top Summer Picks')
        ->set('productsDescription', 'Hand-picked products for your summer.')
        ->set('productsViewAllText', 'See Everything')
        ->set('productsViewAllLink', '/storefront/shop')
        ->set('productsColumns', 3)
        ->set('productsCount', 4)
        // CTA
        ->set('showCta', true)
        ->set('ctaHeading', 'Join Our VIP Summer Club')
        ->set('ctaDescription', 'Exclusive deals and early access for members.')
        ->set('ctaButtonText', 'Get Access')
        ->set('ctaButtonLink', '#register')
        ->call('saveDraft')
        ->assertHasNoErrors();

    $theme = ThemeSetting::first();
    expect($theme)->not->toBeNull();
    expect($theme->name)->toBe('Summer Sale Theme');

    $sections = $theme->settings['sections'];
    expect($sections['hero']['heading'])->toBe('Hot Summer Discounts')
        ->and($sections['categories']['columns'])->toBe(3)
        ->and($sections['products']['heading'])->toBe('Top Summer Picks')
        ->and($sections['cta']['button_text'])->toBe('Get Access');
});

test('it publishes theme settings with all storefront sections', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(ThemeSettingsIndex::class)
        ->set('themeName', 'Published Modern Store')
        ->set('primaryColor', '#10b981')
        ->set('heroHeading', 'Modern Furniture for Living')
        ->set('productsHeading', 'New Arrivals')
        ->set('ctaHeading', 'Transform Your Home')
        ->call('publishTheme')
        ->assertHasNoErrors();

    $theme = ThemeSetting::first();
    expect($theme->published_at)->not->toBeNull();

    $published = $theme->published_settings;
    expect($published['colors']['primary'])->toBe('#10b981')
        ->and($published['sections']['hero']['heading'])->toBe('Modern Furniture for Living')
        ->and($published['sections']['products']['heading'])->toBe('New Arrivals')
        ->and($published['sections']['cta']['heading'])->toBe('Transform Your Home');
});

test('storefront home displays published theme settings', function () {
    ThemeSetting::create([
        'name' => 'Custom Storefront Theme',
        'settings' => [],
        'published_settings' => [
            'colors' => [
                'primary' => '#8b5cf6',
                'secondary' => '#1e1b4b',
                'background' => '#faf5ff',
                'text' => '#0f172a',
            ],
            'layout' => [
                'page_width' => '1440px',
            ],
            'sections' => [
                'hero' => [
                    'visible' => true,
                    'eyebrow' => 'VIP Collection',
                    'heading' => 'Elevate Your Style,',
                    'highlight' => 'Effortlessly.',
                    'description' => 'Discover boutique items curated just for you.',
                    'primary_button_text' => 'Shop Collection',
                    'primary_button_link' => '#products',
                    'secondary_button_text' => 'See Categories',
                    'secondary_button_link' => '#categories',
                ],
                'categories' => [
                    'visible' => true,
                    'heading' => 'Featured Departments',
                    'description' => 'Browse by department.',
                    'columns' => 3,
                ],
                'products' => [
                    'visible' => true,
                    'heading' => 'Trending Right Now',
                    'description' => 'Our best selling items this week.',
                    'view_all_text' => 'View Catalog',
                    'view_all_link' => '/storefront/shop',
                    'columns' => 3,
                    'count' => 4,
                ],
                'cta' => [
                    'visible' => true,
                    'heading' => 'Ready to Upgrade?',
                    'description' => 'Sign up now and save 20% on your first purchase.',
                    'button_text' => 'Claim Your Offer',
                    'button_link' => '#offer',
                ],
            ],
        ],
        'published_at' => now(),
    ]);

    $this->get(route('storefront.home'))
        ->assertOk()
        ->assertSee('VIP Collection')
        ->assertSee('Elevate Your Style,')
        ->assertSee('Effortlessly.')
        ->assertSee('Shop Collection')
        ->assertSee('Featured Departments')
        ->assertSee('Trending Right Now')
        ->assertSee('View Catalog')
        ->assertSee('Ready to Upgrade?')
        ->assertSee('Claim Your Offer')
        ->assertSee('--store-primary: #8b5cf6', false);
});

test('storefront home respects hidden section toggles', function () {
    ThemeSetting::create([
        'name' => 'Minimal Theme',
        'settings' => [],
        'published_settings' => [
            'colors' => [
                'primary' => '#2563eb',
                'secondary' => '#111827',
                'background' => '#ffffff',
                'text' => '#111827',
            ],
            'layout' => [
                'page_width' => '1280px',
            ],
            'sections' => [
                'hero' => [
                    'visible' => false,
                    'heading' => 'Hidden Hero',
                ],
                'categories' => [
                    'visible' => false,
                    'heading' => 'Hidden Categories',
                ],
                'products' => [
                    'visible' => true,
                    'heading' => 'Visible Products Section',
                    'description' => 'Only products should show.',
                    'view_all_text' => 'View All',
                    'view_all_link' => '/storefront/shop',
                    'columns' => 4,
                    'count' => 8,
                ],
                'cta' => [
                    'visible' => false,
                    'heading' => 'Hidden CTA Banner',
                ],
            ],
        ],
        'published_at' => now(),
    ]);

    $this->get(route('storefront.home'))
        ->assertOk()
        ->assertDontSee('Hidden Hero')
        ->assertDontSee('Hidden Categories')
        ->assertSee('Visible Products Section')
        ->assertDontSee('Hidden CTA Banner');
});
