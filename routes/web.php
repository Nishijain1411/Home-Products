<?php

use App\Http\Controllers\PageController;
use App\Http\Controllers\StorefrontController;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Livewire\Admin\Analytics\Index as AnalyticsIndex;
use App\Livewire\Admin\Categories\Create as CategoryCreate;
use App\Livewire\Admin\Categories\Edit as CategoryEdit;
use App\Livewire\Admin\Categories\Index as CategoryIndex;
use App\Livewire\Admin\Customers\Index as CustomerIndex;
use App\Livewire\Admin\Customers\Show as CustomerShow;
use App\Livewire\Admin\CustomerSegments\Index as CustomerSegmentIndex;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\Discounts\Create as DiscountCreate;
use App\Livewire\Admin\Discounts\Edit as DiscountEdit;
use App\Livewire\Admin\Discounts\Index as DiscountIndex;
use App\Livewire\Admin\Inventory\History as InventoryHistory;
use App\Livewire\Admin\Inventory\Index as InventoryIndex;
use App\Livewire\Admin\Navigation\Index as NavigationIndex;
use App\Livewire\Admin\Orders\Index as OrderIndex;
use App\Livewire\Admin\Orders\Show as OrderShow;
use App\Livewire\Admin\Pages\Create as PageCreate;
use App\Livewire\Admin\Pages\Edit as PageEdit;
use App\Livewire\Admin\Pages\Index as PageIndex;
use App\Livewire\Admin\Products\Create as ProductCreate;
use App\Livewire\Admin\Products\Edit as ProductEdit;
use App\Livewire\Admin\Products\Index as ProductIndex;
use App\Livewire\Admin\Settings\Index as SettingsIndex;
use App\Livewire\Admin\ThemeSettings\Index as ThemeSettingsIndex;
use App\Livewire\Storefront\Cart;
use App\Livewire\Storefront\Checkout;
use App\Livewire\Storefront\Orders\Index as CustomerOrderIndex;
use App\Livewire\Storefront\Orders\Show as CustomerOrderShow;
use App\Livewire\Storefront\OrderSuccess;
use App\Livewire\Storefront\ProductDetail;
use App\Livewire\Storefront\ProductListing;
use Illuminate\Support\Facades\Route;

// Route::view('/', 'welcome');
require __DIR__.'/auth.php';

Route::view('profile', 'profile')->middleware(['auth'])->name('profile');

Route::get('/dashboard', function () {
    if (auth()->user()?->canAccessAdmin()) {
        return redirect()->route('admin.dashboard');
    }

    return redirect()->route('storefront.orders.index');
})->middleware(['auth'])->name('dashboard');

Route::get('/', function () {
    if (auth()->check()) {
        if (auth()->user()?->canAccessAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('storefront.home');
    }

    return redirect()->route('storefront.home');
});
/*
    |--------------------------------------------------------------------------
    | CUSTOMER / STOREFRONT ROUTES
    |--------------------------------------------------------------------------
    |
    */

Route::name('storefront.')->group(function () {
    Route::get('/storefront/home', [StorefrontController::class, 'home'])->name('home');
    Route::get('/storefront/products', ProductListing::class)->name('products.index');
    Route::get('/storefront/shop', ProductListing::class)->name('shop');
    Route::get('/storefront/categories/{category:slug}', ProductListing::class)->name('categories.show');
    Route::get('/storefront/products/{product:slug}', ProductDetail::class)->name('products.show');
    Route::get('/storefront/cart', Cart::class)->name('cart');
    Route::get('/storefront/checkout', Checkout::class)->name('checkout');
    Route::get('/storefront/order-success/{orderNumber}', OrderSuccess::class)->name('order-success');
    Route::get('/page/{slug}', [PageController::class, 'show'])->name('pages.show');
    Route::middleware('auth')->group(function () {
        Route::get('/my-orders', CustomerOrderIndex::class)->name('orders.index');
        Route::get('/my-orders/{orderNumber}', CustomerOrderShow::class)->name('orders.show');
    });
});

/*
|--------------------------------------------------------------------------
| DEFAULT AUTHENTICATED USER ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', EnsureUserIsAdmin::class])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/analytics', AnalyticsIndex::class)->name('analytics.index');
    Route::get('/categories', CategoryIndex::class)->name('categories.index');
    Route::get('/categories/create', CategoryCreate::class)->name('categories.create');
    Route::get('/categories/{category}/edit', CategoryEdit::class)->name('categories.edit');
    Route::get('/products', ProductIndex::class)->name('products.index');
    Route::get('/products/create', ProductCreate::class)->name('products.create');
    Route::get('/products/{product}/edit', ProductEdit::class)->name('products.edit');
    // Route::get('/', function () {return view('storefront.home');})->name('storefront.home');
    Route::get('/orders', OrderIndex::class)->name('orders.index');
    Route::get('/orders/{orderNumber}', OrderShow::class)->name('orders.show');
    Route::get('/discounts', DiscountIndex::class)->name('discounts.index');
    Route::get('/discounts/create', DiscountCreate::class)->name('discounts.create');
    Route::get('/discounts/{discount}/edit', DiscountEdit::class)->name('discounts.edit');
    Route::get('/customers', CustomerIndex::class)->name('customers.index');
    Route::get('/customers/{customerId}', CustomerShow::class)->name('customers.show');
    Route::get('/customer-segments', CustomerSegmentIndex::class)->name('customer-segments.index');
    Route::get('/inventory', InventoryIndex::class)->name('inventory.index');
    Route::get('/inventory/history', InventoryHistory::class)->name('inventory.history');
    Route::get('/pages', PageIndex::class)->name('pages.index');
    Route::get('/pages/create', PageCreate::class)->name('pages.create');
    Route::get('/pages/{page}/edit', PageEdit::class)->name('pages.edit');
    Route::get('/navigation', NavigationIndex::class)->name('navigation.index');
    Route::get('/theme-settings', ThemeSettingsIndex::class)->name('theme-settings.index');
    Route::get('/settings', SettingsIndex::class)->name('settings.index');
});
