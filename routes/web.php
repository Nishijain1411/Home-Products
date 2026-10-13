<?php

use App\Http\Controllers\StorefrontController;
use App\Livewire\Admin\Categories\Index as CategoryIndex;
use App\Livewire\Admin\Customers\Index as CustomerIndex;
use App\Livewire\Admin\Customers\Show as CustomerShow;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\Inventory\History as InventoryHistory;
use App\Livewire\Admin\Inventory\Index as InventoryIndex;
use App\Livewire\Admin\Orders\Index as OrderIndex;
use App\Livewire\Admin\Orders\Show as OrderShow;
use App\Livewire\Admin\Products\Index as ProductIndex;
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

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('admin.dashboard');
    }

    return redirect()->route('login');

});
/*
    |--------------------------------------------------------------------------
    | CUSTOMER / STOREFRONT ROUTES
    |--------------------------------------------------------------------------
    |
    */

Route::name('storefront.')->group(function () {
    Route::get('/storefront/home', [StorefrontController::class, 'home'])->name('home');
    Route::get('/storefront/shop', ProductListing::class)->name('shop');
    Route::get('/storefront/products/{product:slug}', ProductDetail::class)->name('products.show');
    Route::get('/storefront/cart', Cart::class)->name('cart');
    Route::get('/storefront/checkout', Checkout::class)->name('checkout');
    Route::get('/storefront/order-success/{orderNumber}', OrderSuccess::class)->name('order-success');
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
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/categories', CategoryIndex::class)->name('categories.index');
    Route::get('/products', ProductIndex::class)->name('products.index');
    // Route::get('/', function () {return view('storefront.home');})->name('storefront.home');
    Route::get('/orders', OrderIndex::class)->name('orders.index');
    Route::get('/orders/{orderNumber}', OrderShow::class)->name('orders.show');
    Route::get('/customers', CustomerIndex::class)->name('customers.index');
    Route::get('/customers/{customerId}', CustomerShow::class)->name('customers.show');
    Route::get('/inventory', InventoryIndex::class)->name('inventory.index');
    Route::get('/inventory/history', InventoryHistory::class)->name('inventory.history');
    Route::get('/theme-settings', ThemeSettingsIndex::class)->name('theme-settings.index');
});
