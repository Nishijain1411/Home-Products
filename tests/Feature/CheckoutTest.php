<?php

use App\Livewire\Storefront\Checkout;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

test('checkout validates mandatory inputs and displays error messages when fields are blank', function () {
    $product = Product::create([
        'name' => 'Sample Item',
        'slug' => 'sample-item',
        'sku' => 'SAMPLE-01',
        'price' => 500.00,
        'is_active' => true,
    ]);

    $variant = ProductVariant::create([
        'product_id' => $product->id,
        'title' => 'Default Variant',
        'sku' => 'SAMPLE-01-DEF',
        'price' => 500.00,
        'is_active' => true,
    ]);

    Inventory::create([
        'product_variant_id' => $variant->id,
        'quantity' => 10,
    ]);

    // Populate cart
    app(CartService::class)->add($variant, 1);

    // Call placeOrder with blank mandatory fields
    Livewire::test(Checkout::class)
        ->set('firstName', '')
        ->set('lastName', '')
        ->set('email', '')
        ->set('phone', '')
        ->set('address', '')
        ->set('city', '')
        ->set('state', '')
        ->set('postalCode', '')
        ->set('country', '')
        ->call('placeOrder')
        ->assertHasErrors([
            'firstName' => 'required',
            'lastName' => 'required',
            'email' => 'required',
            'phone' => 'required',
            'address' => 'required',
            'city' => 'required',
            'state' => 'required',
            'postalCode' => 'required',
            'country' => 'required',
        ])
        ->assertSee('The first name field is required.')
        ->assertSee('The street address field is required.')
        ->assertSee('The city field is required.')
        ->assertSee('The state field is required.')
        ->assertSee('The postal code field is required.')
        ->assertSee('The country field is required.');
});

test('checkout succeeds when all mandatory fields are filled', function () {
    Role::firstOrCreate(['name' => 'Customer', 'guard_name' => 'web']);

    $product = Product::create([
        'name' => 'Sample Item',
        'slug' => 'sample-item',
        'sku' => 'SAMPLE-01',
        'price' => 500.00,
        'is_active' => true,
    ]);

    $variant = ProductVariant::create([
        'product_id' => $product->id,
        'title' => 'Default Variant',
        'sku' => 'SAMPLE-01-DEF',
        'price' => 500.00,
        'is_active' => true,
    ]);

    Inventory::create([
        'product_variant_id' => $variant->id,
        'quantity' => 10,
    ]);

    app(CartService::class)->add($variant, 1);

    Livewire::test(Checkout::class)
        ->set('firstName', 'Jane')
        ->set('lastName', 'Doe')
        ->set('email', 'jane@example.com')
        ->set('phone', '9876543210')
        ->set('address', '123 Main Street')
        ->set('city', 'Mumbai')
        ->set('state', 'Maharashtra')
        ->set('postalCode', '400001')
        ->set('country', 'India')
        ->call('placeOrder')
        ->assertHasNoErrors()
        ->assertSessionHas('order_success');
});
