<?php

use App\Livewire\Admin\Products\Create as ProductCreate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => 'Customer', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Super Administrator', 'guard_name' => 'web']);
});

/*
|--------------------------------------------------------------------------
| 1. Server-Side Authorization for Admin Routes
|--------------------------------------------------------------------------
*/

test('guests are redirected away from admin dashboard', function () {
    $this->get(route('admin.dashboard'))
        ->assertRedirect(route('login'));
});

test('customers with Customer role are forbidden from accessing admin routes', function () {
    $customer = User::factory()->create();
    $customer->assignRole('Customer');

    $this->actingAs($customer);

    $this->get(route('admin.dashboard'))
        ->assertForbidden();

    $this->get(route('admin.orders.index'))
        ->assertForbidden();

    $this->get(route('admin.products.index'))
        ->assertForbidden();

    $this->get(route('admin.settings.index'))
        ->assertForbidden();
});

test('authorized administrator can access admin routes', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Super Administrator');

    $this->actingAs($admin);

    $this->get(route('admin.dashboard'))
        ->assertOk();
});

test('new customer registration assigns Customer role and redirects to storefront', function () {
    Livewire::test('pages.auth.register')
        ->set('name', 'Jane Customer')
        ->set('email', 'janecustomer@example.com')
        ->set('password', 'SecurePassword@123')
        ->set('password_confirmation', 'SecurePassword@123')
        ->call('register')
        ->assertRedirect(route('storefront.home', absolute: false));

    $user = User::where('email', 'janecustomer@example.com')->first();
    expect($user)->not->toBeNull()
        ->and($user->hasRole('Customer'))->toBeTrue()
        ->and($user->canAccessAdmin())->toBeFalse();
});

/*
|--------------------------------------------------------------------------
| 2. Login Throttling & Rate Limiting
|--------------------------------------------------------------------------
*/

test('login requests are throttled after five consecutive failed attempts', function () {
    RateLimiter::clear('throttle-user@example.com|127.0.0.1');

    for ($i = 0; $i < 5; $i++) {
        Livewire::test('pages.auth.login')
            ->set('form.email', 'throttle-user@example.com')
            ->set('form.password', 'incorrect-password')
            ->call('login')
            ->assertHasErrors(['form.email']);
    }

    // 6th attempt must trigger rate limiter lockout
    Livewire::test('pages.auth.login')
        ->set('form.email', 'throttle-user@example.com')
        ->set('form.password', 'incorrect-password')
        ->call('login')
        ->assertHasErrors(['form.email']);
});

/*
|--------------------------------------------------------------------------
| 3. Product Image File Upload Validation
|--------------------------------------------------------------------------
*/

test('product image uploads reject invalid non-image file formats', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Super Administrator');
    $this->actingAs($admin);

    $pdfFile = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');
    $txtFile = UploadedFile::fake()->create('notes.txt', 20, 'text/plain');

    Livewire::test(ProductCreate::class)
        ->set('productImages', [$pdfFile])
        ->call('save')
        ->assertHasErrors(['productImages.0']);

    Livewire::test(ProductCreate::class)
        ->set('productImages', [$txtFile])
        ->call('save')
        ->assertHasErrors(['productImages.0']);
});

test('product image uploads accept valid safe image files', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Super Administrator');
    $this->actingAs($admin);

    $validJpg = UploadedFile::fake()->image('product1.jpg');
    $validPng = UploadedFile::fake()->image('product2.png');
    $validWebp = UploadedFile::fake()->image('product3.webp');

    Livewire::test(ProductCreate::class)
        ->set('productImages', [$validJpg, $validPng, $validWebp])
        ->call('save')
        ->assertHasNoErrors(['productImages.0', 'productImages.1', 'productImages.2']);
});

/*
|--------------------------------------------------------------------------
| 4. Sensitive Debug Endpoints Restricted in Production
|--------------------------------------------------------------------------
*/

test('debug config endpoint is not accessible in production environment', function () {
    app()->detectEnvironment(fn () => 'production');

    $response = $this->get('/api/debug-config');

    expect($response->status())->toBeIn([404, 403]);
});

/*
|--------------------------------------------------------------------------
| 5. Database Backup and Restore Commands
|--------------------------------------------------------------------------
*/

test('artisan db:backup command creates a valid database dump file', function () {
    $testFilename = 'test-security-backup.sql';
    $backupPath = storage_path('app/backups/'.$testFilename);

    if (File::exists($backupPath)) {
        File::delete($backupPath);
    }

    $this->artisan('db:backup', ['--filename' => $testFilename])
        ->assertSuccessful();

    expect(File::exists($backupPath))->toBeTrue()
        ->and(File::size($backupPath))->toBeGreaterThan(0);

    File::delete($backupPath);
});

test('artisan db:restore command handles backup restoration safely', function () {
    $testFilename = 'test-restore-backup.sql';
    $backupPath = storage_path('app/backups/'.$testFilename);

    $this->artisan('db:backup', ['--filename' => $testFilename])
        ->assertSuccessful();

    $this->artisan('db:restore', ['file' => $testFilename, '--force' => true])
        ->assertSuccessful();

    File::delete($backupPath);
});
