<?php

use App\Livewire\Admin\Settings\Index as SettingsIndex;
use App\Models\Setting;
use App\Models\User;
use App\Services\SettingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('guests are redirected to login when visiting settings page', function () {
    $this->get(route('admin.settings.index'))
        ->assertRedirect(route('login'));
});

test('authenticated user can view the global settings page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.settings.index'))
        ->assertOk()
        ->assertSee('Global Settings')
        ->assertSee('Store Profile')
        ->assertSee('Legal Business Address')
        ->assertSee('Email (SMTP)')
        ->assertSee('Taxes')
        ->assertSee('Payments')
        ->assertSee('Shipping')
        ->assertSee('Other Settings');
});

test('settings component mounts with expected default store values', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(SettingsIndex::class)
        ->assertSet('activeTab', 'general')
        ->assertSet('store_name', 'Custom Commerce')
        ->assertSet('timezone', 'Asia/Kolkata')
        ->assertSet('default_currency', 'INR')
        ->assertSet('currency_symbol', '₹')
        ->assertSet('enable_tax', true)
        ->assertSet('default_tax_rate', 18.0)
        ->assertSet('enable_cod', true)
        ->assertSet('enable_card', true)
        ->assertSet('enable_upi', true)
        ->assertSet('handling_time_days', 2)
        ->assertSet('allow_customer_registration', true)
        ->assertSet('guest_checkout', true)
        ->assertSet('order_number_prefix', 'ORD-');
});

test('switching tabs updates activeTab state', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(SettingsIndex::class)
        ->call('setTab', 'currency')
        ->assertSet('activeTab', 'currency')
        ->call('setTab', 'tax')
        ->assertSet('activeTab', 'tax')
        ->call('setTab', 'payment')
        ->assertSet('activeTab', 'payment')
        ->call('setTab', 'shipping')
        ->assertSet('activeTab', 'shipping')
        ->call('setTab', 'other')
        ->assertSet('activeTab', 'other');
});

test('can save application and general store settings', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(SettingsIndex::class)
        ->set('store_name', 'SuperStore Global')
        ->set('store_tagline', 'The finest products online')
        ->set('contact_email', 'support@superstore.test')
        ->set('contact_phone', '+91 9876543210')
        ->set('timezone', 'UTC')
        ->set('date_format', 'Y-m-d')
        ->set('store_url', 'https://superstore.test')
        ->set('maintenance_mode', true)
        ->set('maintenance_message', 'Under scheduled maintenance')
        ->set('address_line1', '123 Market Street')
        ->set('city', 'Bangalore')
        ->set('state', 'Karnataka')
        ->set('postal_code', '560001')
        ->set('country', 'India')
        ->call('save')
        ->assertHasNoErrors()
        ->assertSee('General settings have been successfully saved');

    $service = app(SettingService::class);
    expect($service->get('store_name'))->toBe('SuperStore Global');
    expect($service->get('store_tagline'))->toBe('The finest products online');
    expect($service->get('contact_email'))->toBe('support@superstore.test');
    expect($service->get('maintenance_mode'))->toBeTrue();
    expect($service->get('city'))->toBe('Bangalore');
});

test('general settings validation requires valid store name and email', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(SettingsIndex::class)
        ->set('store_name', '')
        ->set('contact_email', 'invalid-email-string')
        ->call('save')
        ->assertHasErrors(['store_name' => 'required', 'contact_email' => 'email']);
});

test('can upload and remove store logo', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    $this->actingAs($user);

    $logo = UploadedFile::fake()->image('brand-logo.png', 200, 200);

    $test = Livewire::test(SettingsIndex::class)
        ->set('new_store_logo', $logo)
        ->call('save')
        ->assertHasNoErrors();

    $savedLogoPath = app(SettingService::class)->get('store_logo');
    expect($savedLogoPath)->not->toBeNull();
    Storage::disk('public')->assertExists($savedLogoPath);

    // Now remove the logo
    $test->call('removeLogo')
        ->assertSet('store_logo', null);

    expect(app(SettingService::class)->get('store_logo'))->toBeNull();
    Storage::disk('public')->assertMissing($savedLogoPath);
});

test('can save email and smtp settings', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(SettingsIndex::class)
        ->call('setTab', 'email')
        ->set('mail_driver', 'smtp')
        ->set('mail_host', 'smtp.mailtrap.io')
        ->set('mail_port', 2525)
        ->set('mail_encryption', 'tls')
        ->set('mail_username', 'mailtrap_user')
        ->set('mail_password', 'secret_password')
        ->set('mail_from_address', 'no-reply@mystore.test')
        ->set('mail_from_name', 'MyStore Postmaster')
        ->call('save')
        ->assertHasNoErrors()
        ->assertSee('Email settings have been successfully saved');

    $service = app(SettingService::class);
    expect($service->get('mail_host'))->toBe('smtp.mailtrap.io');
    expect($service->get('mail_port'))->toBe(2525);
    expect($service->get('mail_from_address'))->toBe('no-reply@mystore.test');
});

test('email settings validation enforces correct mail driver and email address', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(SettingsIndex::class)
        ->call('setTab', 'email')
        ->set('mail_driver', 'invalid_driver')
        ->set('mail_from_address', 'not-an-email')
        ->set('mail_port', 999999)
        ->call('save')
        ->assertHasErrors(['mail_driver', 'mail_from_address', 'mail_port']);
});

test('can send test email from settings', function () {
    Mail::fake();
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(SettingsIndex::class)
        ->call('setTab', 'email')
        ->set('test_email_recipient', 'test@receiver.com')
        ->call('sendTestEmail')
        ->assertHasNoErrors()
        ->assertSet('testEmailStatus', 'success')
        ->assertSee('Test email dispatched successfully');
});

test('test email validation requires recipient email', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(SettingsIndex::class)
        ->call('setTab', 'email')
        ->set('test_email_recipient', '')
        ->call('sendTestEmail')
        ->assertHasErrors(['test_email_recipient' => 'required']);
});

test('can save currency and pricing settings and price formatting respects them', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(SettingsIndex::class)
        ->call('setTab', 'currency')
        ->set('default_currency', 'USD')
        ->set('currency_symbol', '$')
        ->set('currency_symbol_position', 'before')
        ->set('decimal_precision', 2)
        ->set('thousands_separator', ',')
        ->set('decimal_separator', '.')
        ->set('price_display_format', 'symbol_space')
        ->call('save')
        ->assertHasNoErrors()
        ->assertSee('Currency settings have been successfully saved');

    $service = app(SettingService::class);
    expect($service->get('default_currency'))->toBe('USD');
    expect($service->get('currency_symbol'))->toBe('$');

    // Test formatted price with symbol_space
    $formatted = $service->formatPrice(1299.50);
    expect($formatted)->toBe('$ 1,299.50');

    // Test with symbol after and no space
    Livewire::test(SettingsIndex::class)
        ->call('setTab', 'currency')
        ->set('currency_symbol', 'EUR')
        ->set('currency_symbol_position', 'after')
        ->set('price_display_format', 'symbol_no_space')
        ->set('thousands_separator', '.')
        ->set('decimal_separator', ',')
        ->call('save')
        ->assertHasNoErrors();

    $service->clearCache();
    expect($service->formatPrice(1299.50))->toBe('1.299,50EUR');
});

test('can save tax configuration', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(SettingsIndex::class)
        ->call('setTab', 'tax')
        ->set('enable_tax', true)
        ->set('prices_include_tax', true)
        ->set('default_tax_rate', 12.5)
        ->set('tax_name', 'VAT')
        ->set('tax_shipping', true)
        ->call('save')
        ->assertHasNoErrors()
        ->assertSee('Tax settings have been successfully saved');

    $service = app(SettingService::class);
    expect($service->get('enable_tax'))->toBeTrue();
    expect($service->get('prices_include_tax'))->toBeTrue();
    expect($service->get('default_tax_rate'))->toBe(12.5);
    expect($service->get('tax_name'))->toBe('VAT');
    expect($service->get('tax_shipping'))->toBeTrue();
});

test('tax rate must be a valid percentage between 0 and 100', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(SettingsIndex::class)
        ->call('setTab', 'tax')
        ->set('default_tax_rate', 150.0)
        ->set('tax_name', '')
        ->call('save')
        ->assertHasErrors(['default_tax_rate', 'tax_name']);
});

test('can save payment gateway settings', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(SettingsIndex::class)
        ->call('setTab', 'payment')
        ->set('enable_cod', true)
        ->set('cod_instructions', 'Pay cash upon delivery at your doorstep.')
        ->set('enable_card', true)
        ->set('card_mode', 'live')
        ->set('card_gateway_key', 'rzp_live_abc123')
        ->set('card_gateway_secret', 'live_secret_456')
        ->set('enable_upi', true)
        ->set('upi_mode', 'live')
        ->set('upi_merchant_vpa', 'store@okaxis')
        ->set('upi_instructions', 'Scan QR code and complete payment within 5 minutes.')
        ->call('save')
        ->assertHasNoErrors()
        ->assertSee('Payment settings have been successfully saved');

    $service = app(SettingService::class);
    expect($service->get('card_mode'))->toBe('live');
    expect($service->get('card_gateway_key'))->toBe('rzp_live_abc123');
    expect($service->get('upi_merchant_vpa'))->toBe('store@okaxis');
    expect($service->get('cod_instructions'))->toBe('Pay cash upon delivery at your doorstep.');
});

test('can save shipping and fulfillment settings', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(SettingsIndex::class)
        ->call('setTab', 'shipping')
        ->set('origin_address', 'Warehouse 4B, Industrial Area')
        ->set('origin_city', 'Mumbai')
        ->set('origin_state', 'Maharashtra')
        ->set('origin_postal_code', '400001')
        ->set('origin_country', 'India')
        ->set('handling_time_days', 1)
        ->set('default_shipping_fee', 60.00)
        ->set('enable_free_shipping', true)
        ->set('free_shipping_threshold', 999.00)
        ->set('shipping_calculation_type', 'flat')
        ->call('save')
        ->assertHasNoErrors()
        ->assertSee('Shipping settings have been successfully saved');

    $service = app(SettingService::class);
    expect($service->get('origin_city'))->toBe('Mumbai');
    expect($service->get('handling_time_days'))->toBe(1);
    expect($service->get('default_shipping_fee'))->toBe(60.0);
    expect($service->get('free_shipping_threshold'))->toBe(999.0);
});

test('can save other store configurations including inventory alerts and API', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(SettingsIndex::class)
        ->call('setTab', 'other')
        ->set('allow_customer_registration', false)
        ->set('guest_checkout', false)
        ->set('order_number_prefix', 'INV-')
        ->set('order_number_digits', 8)
        ->set('low_stock_threshold', 10)
        ->set('notify_low_stock', true)
        ->set('admin_notification_email', 'admin@store.test')
        ->set('notify_new_order', true)
        ->set('enable_api_access', true)
        ->set('api_webhook_url', 'https://erp.store.test/webhooks/orders')
        ->call('save')
        ->assertHasNoErrors()
        ->assertSee('Other settings have been successfully saved');

    $service = app(SettingService::class);
    expect($service->get('allow_customer_registration'))->toBeFalse();
    expect($service->get('guest_checkout'))->toBeFalse();
    expect($service->get('order_number_prefix'))->toBe('INV-');
    expect($service->get('order_number_digits'))->toBe(8);
    expect($service->get('low_stock_threshold'))->toBe(10);
    expect($service->get('enable_api_access'))->toBeTrue();
    expect($service->get('api_webhook_url'))->toBe('https://erp.store.test/webhooks/orders');
});

test('setting model supports typed values and caching', function () {
    Setting::set('test_bool', true, 'general', 'boolean');
    Setting::set('test_int', 42, 'general', 'integer');
    Setting::set('test_float', 99.95, 'general', 'float');
    Setting::set('test_json', ['a' => 1, 'b' => 'test'], 'general', 'json');
    Setting::set('test_str', 'sample text', 'general', 'string');

    expect(Setting::get('test_bool'))->toBeTrue();
    expect(Setting::get('test_int'))->toBe(42);
    expect(Setting::get('test_float'))->toBe(99.95);
    expect(Setting::get('test_json'))->toBe(['a' => 1, 'b' => 'test']);
    expect(Setting::get('test_str'))->toBe('sample text');

    // Setting group retrieval
    $general = Setting::getGroup('general');
    expect($general)->toHaveKey('test_bool');
    expect($general['test_int'])->toBe(42);

    // Setting default fallback
    expect(Setting::get('non_existent_key', 'fallback_val'))->toBe('fallback_val');
});
