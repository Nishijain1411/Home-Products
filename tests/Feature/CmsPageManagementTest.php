<?php

use App\Livewire\Admin\Pages\Create as PageCreate;
use App\Livewire\Admin\Pages\Edit as PageEdit;
use App\Livewire\Admin\Pages\Index as PageIndex;
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

test('admin pages list requires authentication and renders successfully', function () {
    $this->get(route('admin.pages.index'))
        ->assertRedirect(route('login'));

    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.pages.index'))
        ->assertOk()
        ->assertSee('CMS Pages');
});

test('admin can create a CMS page with content, template and SEO fields', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(PageCreate::class)
        ->set('title', 'About Our Brand')
        ->set('content', '<p>We make great products with sustainable materials.</p>')
        ->set('template', 'default')
        ->set('is_published', true)
        ->set('meta_title', 'About Our Brand — Custom Commerce')
        ->set('meta_description', 'Learn about our brand philosophy and vision.')
        ->set('canonical_url', 'https://example.com/page/about-our-brand')
        ->set('is_indexed', true)
        ->call('save')
        ->assertRedirect(route('admin.pages.index'));

    $page = Page::where('slug', 'about-our-brand')->first();

    expect($page)->not->toBeNull()
        ->and($page->title)->toBe('About Our Brand')
        ->and($page->template)->toBe('default')
        ->and($page->is_published)->toBeTrue()
        ->and($page->meta_title)->toBe('About Our Brand — Custom Commerce')
        ->and($page->meta_description)->toBe('Learn about our brand philosophy and vision.')
        ->and($page->canonical_url)->toBe('https://example.com/page/about-our-brand')
        ->and($page->is_indexed)->toBeTrue();
});

test('page slug is generated from title and sanitized properly', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $component = Livewire::test(PageCreate::class)
        ->set('title', 'Frequently Asked Questions & Answers!')
        ->call('generateSlug');

    expect($component->get('slug'))->toBe('frequently-asked-questions-answers');
});

test('admin can edit an existing page', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $page = Page::create([
        'title' => 'Initial Title',
        'slug' => 'initial-title',
        'content' => '<p>Initial content</p>',
        'template' => 'default',
        'is_published' => false,
    ]);

    Livewire::test(PageEdit::class, ['page' => $page])
        ->set('title', 'Updated Title')
        ->set('content', '<p>Updated content body</p>')
        ->set('template', 'legal')
        ->set('is_published', true)
        ->call('save')
        ->assertRedirect(route('admin.pages.index'));

    $page->refresh();

    expect($page->title)->toBe('Updated Title')
        ->and($page->template)->toBe('legal')
        ->and($page->is_published)->toBeTrue()
        ->and($page->content)->toBe('<p>Updated content body</p>');
});

test('admin can visit the edit page via HTTP GET with page id or slug', function () {
    $user = User::factory()->create();
    $page = Page::create([
        'title' => 'Test Page',
        'slug' => 'test-page',
        'content' => 'Content',
        'template' => 'default',
        'is_published' => true,
    ]);

    $this->actingAs($user)
        ->get(route('admin.pages.edit', $page->id))
        ->assertOk()
        ->assertSee('Test Page');

    $this->actingAs($user)
        ->get(route('admin.pages.edit', $page->slug))
        ->assertOk()
        ->assertSee('Test Page');
});

test('admin can toggle page publish status', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $page = Page::create([
        'title' => 'Draft Page',
        'slug' => 'draft-page',
        'content' => '<p>Draft</p>',
        'is_published' => false,
    ]);

    Livewire::test(PageIndex::class)
        ->call('togglePublish', $page->id);

    expect($page->fresh()->is_published)->toBeTrue()
        ->and($page->fresh()->published_at)->not->toBeNull();

    Livewire::test(PageIndex::class)
        ->call('togglePublish', $page->id);

    expect($page->fresh()->is_published)->toBeFalse();
});

test('admin can delete a page', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $page = Page::create([
        'title' => 'Page to Delete',
        'slug' => 'to-delete',
        'content' => '<p>Content</p>',
        'is_published' => true,
    ]);

    Livewire::test(PageIndex::class)
        ->call('confirmDelete', $page->id)
        ->call('delete');

    expect(Page::find($page->id))->toBeNull();
});

test('public visitor can view published page with SEO fields', function () {
    $page = Page::create([
        'title' => 'Public Policy',
        'slug' => 'public-policy',
        'content' => '<p>This is public information.</p>',
        'template' => 'default',
        'is_published' => true,
        'published_at' => now(),
        'meta_title' => 'Public Policy SEO Title',
        'meta_description' => 'Public Policy meta description text for search engines.',
        'canonical_url' => 'https://example.com/page/public-policy',
        'is_indexed' => true,
    ]);

    $this->get(route('storefront.pages.show', $page->slug))
        ->assertOk()
        ->assertSee('Public Policy')
        ->assertSee('This is public information.', false)
        ->assertSee('Public Policy SEO Title')
        ->assertSee('Public Policy meta description text for search engines.')
        ->assertSee('https://example.com/page/public-policy')
        ->assertSee('index, follow');
});

test('public visitor receives 404 for draft page', function () {
    $page = Page::create([
        'title' => 'Secret Draft Page',
        'slug' => 'secret-draft',
        'content' => '<p>Confidential unreleased details.</p>',
        'is_published' => false,
    ]);

    $this->get(route('storefront.pages.show', $page->slug))
        ->assertNotFound();
});

test('store admin can preview draft page with preview notice', function () {
    $admin = User::factory()->create();
    $adminRole = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
    $admin->assignRole($adminRole);

    $page = Page::create([
        'title' => 'Upcoming Promotion Page',
        'slug' => 'upcoming-promo',
        'content' => '<p>Draft promotion body.</p>',
        'is_published' => false,
    ]);

    $this->actingAs($admin)
        ->get(route('storefront.pages.show', $page->slug))
        ->assertOk()
        ->assertSee('Upcoming Promotion Page')
        ->assertSee('Preview Mode')
        ->assertSee('Draft');
});

test('contact template renders store contact details box', function () {
    $page = Page::create([
        'title' => 'Get in Touch',
        'slug' => 'get-in-touch',
        'content' => '<p>Send us a message anytime.</p>',
        'template' => 'contact',
        'is_published' => true,
        'published_at' => now(),
    ]);

    $this->get(route('storefront.pages.show', $page->slug))
        ->assertOk()
        ->assertSee('Direct Contact Information')
        ->assertSee('Send us a message anytime.', false);
});
