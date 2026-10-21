<?php

namespace App\Livewire\Admin\Settings;

use App\Services\SettingService;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithFileUploads;

    public string $activeTab = 'general';

    // 1. Application & Store Settings
    public string $store_name = '';

    public string $store_tagline = '';

    public ?string $store_logo = null;

    public $new_store_logo = null;

    public string $contact_email = '';

    public string $contact_phone = '';

    public string $timezone = 'Asia/Kolkata';

    public string $date_format = 'd M Y';

    public string $store_url = '';

    public bool $maintenance_mode = false;

    public string $maintenance_message = '';

    public string $address_line1 = '';

    public string $address_line2 = '';

    public string $city = '';

    public string $state = '';

    public string $postal_code = '';

    public string $country = 'India';

    // 2. Email Settings
    public string $mail_driver = 'smtp';

    public string $mail_host = '';

    public int $mail_port = 587;

    public string $mail_encryption = 'tls';

    public string $mail_username = '';

    public string $mail_password = '';

    public string $mail_from_address = '';

    public string $mail_from_name = '';

    public string $test_email_recipient = '';

    public ?string $testEmailMessage = null;

    public ?string $testEmailStatus = null;

    // 3. Currency & Pricing
    public string $default_currency = 'INR';

    public string $currency_symbol = '₹';

    public string $currency_symbol_position = 'before';

    public int $decimal_precision = 2;

    public string $thousands_separator = ',';

    public string $decimal_separator = '.';

    public string $price_display_format = 'symbol_no_space';

    // 4. Tax Settings
    public bool $enable_tax = true;

    public bool $prices_include_tax = false;

    public float $default_tax_rate = 18.0;

    public string $tax_name = 'GST';

    public bool $tax_shipping = false;

    // 5. Payment Settings
    public bool $enable_cod = true;

    public string $cod_instructions = '';

    public bool $enable_card = true;

    public string $card_mode = 'test';

    public string $card_gateway_key = '';

    public string $card_gateway_secret = '';

    public bool $enable_upi = true;

    public string $upi_mode = 'test';

    public string $upi_merchant_vpa = '';

    public string $upi_instructions = '';

    public bool $enable_razorpay = true;

    public string $razorpay_mode = 'test';

    public string $razorpay_key_id = '';

    public string $razorpay_key_secret = '';

    public string $razorpay_instructions = '';

    // 6. Shipping Settings
    public string $origin_address = '';

    public string $origin_city = '';

    public string $origin_state = '';

    public string $origin_postal_code = '';

    public string $origin_country = 'India';

    public int $handling_time_days = 2;

    public float $default_shipping_fee = 0.00;

    public bool $enable_free_shipping = true;

    public float $free_shipping_threshold = 500.00;

    public string $shipping_calculation_type = 'flat';

    // 7. Other Settings
    public bool $allow_customer_registration = true;

    public bool $guest_checkout = true;

    public string $order_number_prefix = 'ORD-';

    public int $order_number_digits = 6;

    public int $low_stock_threshold = 5;

    public bool $notify_low_stock = true;

    public string $admin_notification_email = '';

    public bool $notify_new_order = true;

    public bool $enable_api_access = false;

    public string $api_webhook_url = '';

    public function mount(SettingService $settingService): void
    {
        $this->loadSettings($settingService);
    }

    public function loadSettings(SettingService $settingService): void
    {
        // 1. General
        $general = $settingService->getGroup('general');
        $this->store_name = (string) ($general['store_name'] ?? 'Custom Commerce');
        $this->store_tagline = (string) ($general['store_tagline'] ?? '');
        $this->store_logo = $general['store_logo'] ?? null;
        $this->contact_email = (string) ($general['contact_email'] ?? '');
        $this->contact_phone = (string) ($general['contact_phone'] ?? '');
        $this->timezone = (string) ($general['timezone'] ?? 'Asia/Kolkata');
        $this->date_format = (string) ($general['date_format'] ?? 'd M Y');
        $this->store_url = (string) ($general['store_url'] ?? '');
        $this->maintenance_mode = (bool) ($general['maintenance_mode'] ?? false);
        $this->maintenance_message = (string) ($general['maintenance_message'] ?? '');
        $this->address_line1 = (string) ($general['address_line1'] ?? '');
        $this->address_line2 = (string) ($general['address_line2'] ?? '');
        $this->city = (string) ($general['city'] ?? '');
        $this->state = (string) ($general['state'] ?? '');
        $this->postal_code = (string) ($general['postal_code'] ?? '');
        $this->country = (string) ($general['country'] ?? 'India');

        // 2. Email
        $email = $settingService->getGroup('email');
        $this->mail_driver = (string) ($email['mail_driver'] ?? 'smtp');
        $this->mail_host = (string) ($email['mail_host'] ?? '');
        $this->mail_port = (int) ($email['mail_port'] ?? 587);
        $this->mail_encryption = (string) ($email['mail_encryption'] ?? 'tls');
        $this->mail_username = (string) ($email['mail_username'] ?? '');
        $this->mail_password = (string) ($email['mail_password'] ?? '');
        $this->mail_from_address = (string) ($email['mail_from_address'] ?? '');
        $this->mail_from_name = (string) ($email['mail_from_name'] ?? '');

        // 3. Currency
        $currency = $settingService->getGroup('currency');
        $this->default_currency = (string) ($currency['default_currency'] ?? 'INR');
        $this->currency_symbol = (string) ($currency['currency_symbol'] ?? '₹');
        $this->currency_symbol_position = (string) ($currency['currency_symbol_position'] ?? 'before');
        $this->decimal_precision = (int) ($currency['decimal_precision'] ?? 2);
        $this->thousands_separator = (string) ($currency['thousands_separator'] ?? ',');
        $this->decimal_separator = (string) ($currency['decimal_separator'] ?? '.');
        $this->price_display_format = (string) ($currency['price_display_format'] ?? 'symbol_no_space');

        // 4. Tax
        $tax = $settingService->getGroup('tax');
        $this->enable_tax = (bool) ($tax['enable_tax'] ?? true);
        $this->prices_include_tax = (bool) ($tax['prices_include_tax'] ?? false);
        $this->default_tax_rate = (float) ($tax['default_tax_rate'] ?? 18.0);
        $this->tax_name = (string) ($tax['tax_name'] ?? 'GST');
        $this->tax_shipping = (bool) ($tax['tax_shipping'] ?? false);

        // 5. Payment
        $payment = $settingService->getGroup('payment');
        $this->enable_cod = (bool) ($payment['enable_cod'] ?? true);
        $this->cod_instructions = (string) ($payment['cod_instructions'] ?? '');
        $this->enable_card = (bool) ($payment['enable_card'] ?? true);
        $this->card_mode = (string) ($payment['card_mode'] ?? 'test');
        $this->card_gateway_key = (string) ($payment['card_gateway_key'] ?? '');
        $this->card_gateway_secret = (string) ($payment['card_gateway_secret'] ?? '');
        $this->enable_upi = (bool) ($payment['enable_upi'] ?? true);
        $this->upi_mode = (string) ($payment['upi_mode'] ?? 'test');
        $this->upi_merchant_vpa = (string) ($payment['upi_merchant_vpa'] ?? '');
        $this->upi_instructions = (string) ($payment['upi_instructions'] ?? '');
        $this->enable_razorpay = (bool) ($payment['enable_razorpay'] ?? true);
        $this->razorpay_mode = (string) ($payment['razorpay_mode'] ?? 'test');
        $this->razorpay_key_id = (string) ($payment['razorpay_key_id'] ?? '');
        $this->razorpay_key_secret = (string) ($payment['razorpay_key_secret'] ?? '');
        $this->razorpay_instructions = (string) ($payment['razorpay_instructions'] ?? '');

        // 6. Shipping
        $shipping = $settingService->getGroup('shipping');
        $this->origin_address = (string) ($shipping['origin_address'] ?? '');
        $this->origin_city = (string) ($shipping['origin_city'] ?? '');
        $this->origin_state = (string) ($shipping['origin_state'] ?? '');
        $this->origin_postal_code = (string) ($shipping['origin_postal_code'] ?? '');
        $this->origin_country = (string) ($shipping['origin_country'] ?? 'India');
        $this->handling_time_days = (int) ($shipping['handling_time_days'] ?? 2);
        $this->default_shipping_fee = (float) ($shipping['default_shipping_fee'] ?? 0.00);
        $this->enable_free_shipping = (bool) ($shipping['enable_free_shipping'] ?? true);
        $this->free_shipping_threshold = (float) ($shipping['free_shipping_threshold'] ?? 500.00);
        $this->shipping_calculation_type = (string) ($shipping['shipping_calculation_type'] ?? 'flat');

        // 7. Other
        $other = $settingService->getGroup('other');
        $this->allow_customer_registration = (bool) ($other['allow_customer_registration'] ?? true);
        $this->guest_checkout = (bool) ($other['guest_checkout'] ?? true);
        $this->order_number_prefix = (string) ($other['order_number_prefix'] ?? 'ORD-');
        $this->order_number_digits = (int) ($other['order_number_digits'] ?? 6);
        $this->low_stock_threshold = (int) ($other['low_stock_threshold'] ?? 5);
        $this->notify_low_stock = (bool) ($other['notify_low_stock'] ?? true);
        $this->admin_notification_email = (string) ($other['admin_notification_email'] ?? '');
        $this->notify_new_order = (bool) ($other['notify_new_order'] ?? true);
        $this->enable_api_access = (bool) ($other['enable_api_access'] ?? false);
        $this->api_webhook_url = (string) ($other['api_webhook_url'] ?? '');
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetValidation();
        $this->testEmailMessage = null;
        $this->testEmailStatus = null;
    }

    public function save(SettingService $settingService, ?string $group = null): void
    {
        $targetGroup = $group ?: $this->activeTab;

        match ($targetGroup) {
            'general' => $this->saveGeneral($settingService),
            'email' => $this->saveEmail($settingService),
            'currency' => $this->saveCurrency($settingService),
            'tax' => $this->saveTax($settingService),
            'payment' => $this->savePayment($settingService),
            'shipping' => $this->saveShipping($settingService),
            'other' => $this->saveOther($settingService),
            default => null,
        };

        session()->flash('settings_success', ucfirst($targetGroup).' settings have been successfully saved.');
    }

    protected function saveGeneral(SettingService $settingService): void
    {
        $this->validate([
            'store_name' => ['required', 'string', 'max:255'],
            'store_tagline' => ['nullable', 'string', 'max:255'],
            'contact_email' => ['required', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:30'],
            'timezone' => ['required', 'string', 'max:100'],
            'date_format' => ['required', 'string', 'max:50'],
            'store_url' => ['nullable', 'url', 'max:255'],
            'maintenance_mode' => ['boolean'],
            'maintenance_message' => ['nullable', 'string', 'max:500'],
            'address_line1' => ['nullable', 'string', 'max:255'],
            'address_line2' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'country' => ['nullable', 'string', 'max:100'],
            'new_store_logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
        ]);

        if ($this->new_store_logo) {
            if ($this->store_logo && Storage::disk('public')->exists($this->store_logo)) {
                Storage::disk('public')->delete($this->store_logo);
            }
            $this->store_logo = $this->new_store_logo->store('settings', 'public');
            $this->new_store_logo = null;
        }

        $settingService->setGroup('general', [
            'store_name' => $this->store_name,
            'store_tagline' => $this->store_tagline,
            'store_logo' => $this->store_logo,
            'contact_email' => $this->contact_email,
            'contact_phone' => $this->contact_phone,
            'timezone' => $this->timezone,
            'date_format' => $this->date_format,
            'store_url' => $this->store_url,
            'maintenance_mode' => $this->maintenance_mode,
            'maintenance_message' => $this->maintenance_message,
            'address_line1' => $this->address_line1,
            'address_line2' => $this->address_line2,
            'city' => $this->city,
            'state' => $this->state,
            'postal_code' => $this->postal_code,
            'country' => $this->country,
        ]);
    }

    protected function saveEmail(SettingService $settingService): void
    {
        $this->validate([
            'mail_driver' => ['required', 'string', 'in:smtp,sendmail,log'],
            'mail_host' => ['required_if:mail_driver,smtp', 'nullable', 'string', 'max:255'],
            'mail_port' => ['required_if:mail_driver,smtp', 'nullable', 'integer', 'between:1,65535'],
            'mail_encryption' => ['nullable', 'string', 'in:tls,ssl,none'],
            'mail_username' => ['nullable', 'string', 'max:255'],
            'mail_password' => ['nullable', 'string', 'max:255'],
            'mail_from_address' => ['required', 'email', 'max:255'],
            'mail_from_name' => ['required', 'string', 'max:255'],
        ]);

        $settingService->setGroup('email', [
            'mail_driver' => $this->mail_driver,
            'mail_host' => $this->mail_host,
            'mail_port' => (int) $this->mail_port,
            'mail_encryption' => $this->mail_encryption,
            'mail_username' => $this->mail_username,
            'mail_password' => $this->mail_password,
            'mail_from_address' => $this->mail_from_address,
            'mail_from_name' => $this->mail_from_name,
        ]);
    }

    protected function saveCurrency(SettingService $settingService): void
    {
        $this->validate([
            'default_currency' => ['required', 'string', 'max:10'],
            'currency_symbol' => ['required', 'string', 'max:10'],
            'currency_symbol_position' => ['required', 'in:before,after'],
            'decimal_precision' => ['required', 'integer', 'between:0,4'],
            'thousands_separator' => ['required', 'string', 'max:5'],
            'decimal_separator' => ['required', 'string', 'max:5'],
            'price_display_format' => ['required', 'in:symbol_no_space,symbol_space'],
        ]);

        $settingService->setGroup('currency', [
            'default_currency' => $this->default_currency,
            'currency_symbol' => $this->currency_symbol,
            'currency_symbol_position' => $this->currency_symbol_position,
            'decimal_precision' => (int) $this->decimal_precision,
            'thousands_separator' => $this->thousands_separator,
            'decimal_separator' => $this->decimal_separator,
            'price_display_format' => $this->price_display_format,
        ]);
    }

    protected function saveTax(SettingService $settingService): void
    {
        $this->validate([
            'enable_tax' => ['boolean'],
            'prices_include_tax' => ['boolean'],
            'default_tax_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'tax_name' => ['required', 'string', 'max:50'],
            'tax_shipping' => ['boolean'],
        ]);

        $settingService->setGroup('tax', [
            'enable_tax' => $this->enable_tax,
            'prices_include_tax' => $this->prices_include_tax,
            'default_tax_rate' => (float) $this->default_tax_rate,
            'tax_name' => $this->tax_name,
            'tax_shipping' => $this->tax_shipping,
        ]);
    }

    protected function savePayment(SettingService $settingService): void
    {
        $this->validate([
            'enable_cod' => ['boolean'],
            'cod_instructions' => ['nullable', 'string', 'max:500'],
            'enable_card' => ['boolean'],
            'card_mode' => ['required', 'in:test,live'],
            'card_gateway_key' => ['nullable', 'string', 'max:255'],
            'card_gateway_secret' => ['nullable', 'string', 'max:255'],
            'enable_upi' => ['boolean'],
            'upi_mode' => ['required', 'in:test,live'],
            'upi_merchant_vpa' => ['nullable', 'string', 'max:255'],
            'upi_instructions' => ['nullable', 'string', 'max:500'],
            'enable_razorpay' => ['boolean'],
            'razorpay_mode' => ['required', 'in:test,live'],
            'razorpay_key_id' => ['nullable', 'string', 'max:255'],
            'razorpay_key_secret' => ['nullable', 'string', 'max:255'],
            'razorpay_instructions' => ['nullable', 'string', 'max:500'],
        ]);

        $settingService->setGroup('payment', [
            'enable_cod' => $this->enable_cod,
            'cod_instructions' => $this->cod_instructions,
            'enable_card' => $this->enable_card,
            'card_mode' => $this->card_mode,
            'card_gateway_key' => $this->card_gateway_key,
            'card_gateway_secret' => $this->card_gateway_secret,
            'enable_upi' => $this->enable_upi,
            'upi_mode' => $this->upi_mode,
            'upi_merchant_vpa' => $this->upi_merchant_vpa,
            'upi_instructions' => $this->upi_instructions,
            'enable_razorpay' => $this->enable_razorpay,
            'razorpay_mode' => $this->razorpay_mode,
            'razorpay_key_id' => $this->razorpay_key_id,
            'razorpay_key_secret' => $this->razorpay_key_secret,
            'razorpay_instructions' => $this->razorpay_instructions,
        ]);
    }

    protected function saveShipping(SettingService $settingService): void
    {
        $this->validate([
            'origin_address' => ['nullable', 'string', 'max:255'],
            'origin_city' => ['nullable', 'string', 'max:100'],
            'origin_state' => ['nullable', 'string', 'max:100'],
            'origin_postal_code' => ['nullable', 'string', 'max:20'],
            'origin_country' => ['nullable', 'string', 'max:100'],
            'handling_time_days' => ['required', 'integer', 'min:0', 'max:30'],
            'default_shipping_fee' => ['required', 'numeric', 'min:0'],
            'enable_free_shipping' => ['boolean'],
            'free_shipping_threshold' => ['required_if:enable_free_shipping,true', 'numeric', 'min:0'],
            'shipping_calculation_type' => ['required', 'in:flat,tiered'],
        ]);

        $settingService->setGroup('shipping', [
            'origin_address' => $this->origin_address,
            'origin_city' => $this->origin_city,
            'origin_state' => $this->origin_state,
            'origin_postal_code' => $this->origin_postal_code,
            'origin_country' => $this->origin_country,
            'handling_time_days' => (int) $this->handling_time_days,
            'default_shipping_fee' => (float) $this->default_shipping_fee,
            'enable_free_shipping' => $this->enable_free_shipping,
            'free_shipping_threshold' => (float) $this->free_shipping_threshold,
            'shipping_calculation_type' => $this->shipping_calculation_type,
        ]);
    }

    protected function saveOther(SettingService $settingService): void
    {
        $this->validate([
            'allow_customer_registration' => ['boolean'],
            'guest_checkout' => ['boolean'],
            'order_number_prefix' => ['required', 'string', 'max:20'],
            'order_number_digits' => ['required', 'integer', 'between:4,12'],
            'low_stock_threshold' => ['required', 'integer', 'min:0'],
            'notify_low_stock' => ['boolean'],
            'admin_notification_email' => ['nullable', 'email', 'max:255'],
            'notify_new_order' => ['boolean'],
            'enable_api_access' => ['boolean'],
            'api_webhook_url' => ['nullable', 'url', 'max:255'],
        ]);

        $settingService->setGroup('other', [
            'allow_customer_registration' => $this->allow_customer_registration,
            'guest_checkout' => $this->guest_checkout,
            'order_number_prefix' => $this->order_number_prefix,
            'order_number_digits' => (int) $this->order_number_digits,
            'low_stock_threshold' => (int) $this->low_stock_threshold,
            'notify_low_stock' => $this->notify_low_stock,
            'admin_notification_email' => $this->admin_notification_email,
            'notify_new_order' => $this->notify_new_order,
            'enable_api_access' => $this->enable_api_access,
            'api_webhook_url' => $this->api_webhook_url,
        ]);
    }

    public function removeLogo(SettingService $settingService): void
    {
        if ($this->store_logo && Storage::disk('public')->exists($this->store_logo)) {
            Storage::disk('public')->delete($this->store_logo);
        }

        $this->store_logo = null;
        $this->new_store_logo = null;
        $settingService->set('store_logo', null, 'general');

        session()->flash('settings_success', 'Store logo removed successfully.');
    }

    public function sendTestEmail(SettingService $settingService): void
    {
        $this->validate([
            'test_email_recipient' => ['required', 'email'],
        ]);

        $sent = $settingService->sendTestEmail($this->test_email_recipient);

        if ($sent) {
            $this->testEmailStatus = 'success';
            $this->testEmailMessage = 'Test email dispatched successfully to '.$this->test_email_recipient.'.';
        } else {
            $this->testEmailStatus = 'error';
            $this->testEmailMessage = 'Failed to dispatch test email. Please verify SMTP host, port, credentials, and encryption.';
        }
    }

    public function render()
    {
        return view('livewire.admin.settings.index');
    }
}
