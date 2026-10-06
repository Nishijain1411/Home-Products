<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\Categories\Index as CategoryIndex;   
use App\Http\Controllers\Admin\CategoryController;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';

// Route::middleware(['auth'])->prefix('admin')->group(function () {
//     Route::get('/dashboard', Dashboard::class)->name('admin.dashboard');
// });

Route::middleware(['auth'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/dashboard', \App\Livewire\Admin\Dashboard::class)->name('dashboard');

            Route::get('/categories', CategoryIndex::class)
                ->name('categories.index');

            // Route::get('/categories/create', CategoryCreate::class)
            //     ->name('categories.create');

            // Route::get('/categories/{category}/edit', CategoryEdit::class)
            //     ->name('categories.edit');        // Later:
        // Route::resource('products', ProductController::class);
        // Route::resource('orders', OrderController::class);
        // Route::resource('customers', CustomerController::class);
    });