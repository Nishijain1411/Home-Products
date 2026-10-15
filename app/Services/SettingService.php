<?php

namespace App\Services;

use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\Setting;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class SettingService
{
    /**
     * Default settings dictionary by group.
     */
    protected array $defaults = [
        'general' => [
            'store_name' => 'Custom Commerce',
            'store_tagline' => 'Modern E-Commerce Store',
            'store_logo' => null,
            'contact_email' => 'support@customcommerce.test',
            'contact_phone' => '+91 9876543210',
            'timezone' => 'Asia/Kolkata',
            'date_format' => 'd M Y',
            'store_url' => 'http://custom-commerce.test',
            'maintenance_mode' => false,
            'maintenance_message' => 'We are undergoing scheduled maintenance. Please check back shortly.',
            'address_line1' => '123 Commercial Boulevard',
            'address_line2' => 'Suite 400',
            'city' => 'Mumbai',
            'state' => 'Maharashtra',
            'postal_code' => '400001',
            'country' => 'India',
        ],
        'email' => [
            'mail_driver' => 'smtp',
            'mail_host' => 'sandbox.smtp.mailtrap.io',
            'mail_port' => 2525,
            'mail_encryption' => 'tls',
            'mail_username' => '',
            'mail_password' => '',
            'mail_from_address' => 'noreply@customcommerce.test',
            'mail_from_name' => 'Custom Commerce',
        ],
        'currency' => [
            'default_currency' => 'INR',
            'currency_symbol' => '₹',
            'currency_symbol_position' => 'before',
            'decimal_precision' => 2,
            'thousands_separator' => ',',
            'decimal_separator' => '.',
            'price_display_format' => 'symbol_no_space',
        ],
        'tax' => [
            'enable_tax' => true,
            'prices_include_tax' => false,
            'default_tax_rate' => 18.0,
            'tax_name' => 'GST',
            'tax_shipping' => false,
            'tax_zones' => [],
        ],
        'payment' => [
            'enable_cod' => true,
            'cod_instructions' => 'Pay in cash when order is delivered.',
            'enable_card' => true,
            'card_mode' => 'test',
            'card_gateway_key' => '',
            'card_gateway_secret' => '',
            'enable_upi' => true,
            'upi_mode' => 'test',
            'upi_merchant_vpa' => 'merchant@upi',
            'upi_instructions' => 'Pay instantly via Google Pay, PhonePe or Paytm.',
        ],
        'shipping' => [
            'origin_address' => '123 Warehouse Logistics Center',
            'origin_city' => 'Mumbai',
            'origin_state' => 'Maharashtra',
            'origin_postal_code' => '400001',
            'origin_country' => 'India',
            'handling_time_days' => 2,
            'default_shipping_fee' => 0.00,
            'enable_free_shipping' => true,
            'free_shipping_threshold' => 500.00,
            'shipping_calculation_type' => 'flat',
        ],
        'other' => [
            'allow_customer_registration' => true,
            'guest_checkout' => true,
            'order_number_prefix' => 'ORD-',
            'order_number_digits' => 6,
            'low_stock_threshold' => 5,
            'notify_low_stock' => true,
            'admin_notification_email' => 'admin@customcommerce.test',
            'notify_new_order' => true,
            'enable_api_access' => false,
            'api_webhook_url' => '',
        ],
    ];

    /**
     * Get a setting by key, with default fallback.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $value = Setting::get($key);

        if ($value !== null) {
            return $value;
        }

        if ($default !== null) {
            return $default;
        }

        // Check internal defaults
        foreach ($this->defaults as $groupDefaults) {
            if (array_key_exists($key, $groupDefaults)) {
                return $groupDefaults[$key];
            }
        }

        return null;
    }

    /**
     * Set a setting key and value.
     */
    public function set(string $key, mixed $value, string $group = 'general', ?string $type = null): void
    {
        Setting::set($key, $value, $group, $type);
    }

    /**
     * Get all settings for a group merged with default fallbacks.
     */
    public function getGroup(string $group): array
    {
        $groupDefaults = $this->defaults[$group] ?? [];
        $saved = Setting::getGroup($group);

        return array_merge($groupDefaults, $saved);
    }

    /**
     * Save multiple settings for a group.
     */
    public function setGroup(string $group, array $settings): void
    {
        Setting::setMany($settings, $group);
    }

    /**
     * Format a price according to currency settings.
     */
    public function formatPrice(float $amount): string
    {
        $symbol = $this->get('currency_symbol', '₹');
        $position = $this->get('currency_symbol_position', 'before');
        $decimals = (int) $this->get('decimal_precision', 2);
        $decPoint = $this->get('decimal_separator', '.');
        $thousandsSep = $this->get('thousands_separator', ',');
        $format = $this->get('price_display_format', 'symbol_no_space');

        $formattedNumber = number_format($amount, $decimals, $decPoint, $thousandsSep);
        $spacing = $format === 'symbol_space' ? ' ' : '';

        return $position === 'after'
            ? $formattedNumber.$spacing.$symbol
            : $symbol.$spacing.$formattedNumber;
    }

    /**
     * Calculate shipping fee based on cart subtotal and shipping settings.
     */
    public function calculateShipping(float $subtotal): float
    {
        $enableFreeShipping = (bool) $this->get('enable_free_shipping', true);
        $threshold = (float) $this->get('free_shipping_threshold', 500.0);
        $defaultFee = (float) $this->get('default_shipping_fee', 0.0);

        if ($enableFreeShipping && $subtotal >= $threshold) {
            return 0.0;
        }

        return $defaultFee;
    }

    /**
     * Calculate tax based on subtotal, shipping, and tax settings.
     *
     * @return array{amount: float, rate: float, name: string, inclusive: bool, taxable_amount: float}
     */
    public function calculateTax(float $subtotal, float $shippingFee = 0.0): array
    {
        $enableTax = (bool) $this->get('enable_tax', true);
        $taxName = (string) $this->get('tax_name', 'GST');
        $taxRate = (float) $this->get('default_tax_rate', 18.0);
        $pricesIncludeTax = (bool) $this->get('prices_include_tax', false);
        $taxShipping = (bool) $this->get('tax_shipping', false);

        if (! $enableTax || $taxRate <= 0) {
            return [
                'amount' => 0.0,
                'rate' => 0.0,
                'name' => $taxName,
                'inclusive' => false,
                'taxable_amount' => 0.0,
            ];
        }

        $taxableAmount = $subtotal + ($taxShipping ? $shippingFee : 0.0);

        if ($pricesIncludeTax) {
            $taxAmount = round($taxableAmount - ($taxableAmount / (1 + ($taxRate / 100))), 2);
        } else {
            $taxAmount = round($taxableAmount * ($taxRate / 100), 2);
        }

        return [
            'amount' => max(0.0, $taxAmount),
            'rate' => $taxRate,
            'name' => $taxName,
            'inclusive' => $pricesIncludeTax,
            'taxable_amount' => $taxableAmount,
        ];
    }

    /**
     * Calculate all order totals including subtotal, discount, shipping, tax, and grand total.
     *
     * @return array{subtotal: float, discount: float, discounted_subtotal: float, shipping: float, tax: float, tax_name: string, tax_rate: float, tax_inclusive: bool, total: float}
     */
    public function calculateTotals(float $subtotal, float $discount = 0.0): array
    {
        $discount = max(0.0, min($discount, $subtotal));
        $discountedSubtotal = max(0.0, $subtotal - $discount);

        $shipping = $this->calculateShipping($discountedSubtotal);
        $taxData = $this->calculateTax($discountedSubtotal, $shipping);

        $taxAmount = $taxData['amount'];
        $inclusive = $taxData['inclusive'];

        $total = $inclusive
            ? ($discountedSubtotal + $shipping)
            : ($discountedSubtotal + $shipping + $taxAmount);

        return [
            'subtotal' => round($subtotal, 2),
            'discount' => round($discount, 2),
            'discounted_subtotal' => round($discountedSubtotal, 2),
            'shipping' => round($shipping, 2),
            'tax' => round($taxAmount, 2),
            'tax_name' => $taxData['name'],
            'tax_rate' => $taxData['rate'],
            'tax_inclusive' => $inclusive,
            'total' => round(max(0.0, $total), 2),
        ];
    }

    /**
     * Apply active mail settings to Laravel config at runtime.
     */
    public function applyMailConfig(): void
    {
        $driver = (string) $this->get('mail_driver', 'smtp');
        $host = (string) $this->get('mail_host', config('mail.mailers.smtp.host'));
        $port = (int) $this->get('mail_port', config('mail.mailers.smtp.port', 587));
        $encryption = (string) $this->get('mail_encryption', config('mail.mailers.smtp.encryption', 'tls'));
        $username = (string) $this->get('mail_username', config('mail.mailers.smtp.username'));
        $password = (string) $this->get('mail_password', config('mail.mailers.smtp.password'));
        $fromAddress = (string) $this->get('mail_from_address', config('mail.from.address'));
        $fromName = (string) $this->get('mail_from_name', config('mail.from.name', 'Custom Commerce'));

        config([
            'mail.default' => $driver,
            'mail.mailers.smtp.host' => $host,
            'mail.mailers.smtp.port' => $port,
            'mail.mailers.smtp.encryption' => $encryption === 'none' ? null : $encryption,
            'mail.mailers.smtp.username' => $username,
            'mail.mailers.smtp.password' => $password,
            'mail.from.address' => $fromAddress ?: config('mail.from.address'),
            'mail.from.name' => $fromName ?: config('mail.from.name'),
        ]);
    }

    /**
     * Format a date according to store timezone and date format settings.
     */
    public function formatDate(mixed $date, bool $includeTime = false): string
    {
        if (! $date) {
            return '';
        }

        $dateFormat = (string) $this->get('date_format', 'd M Y');
        $timezone = (string) $this->get('timezone', 'Asia/Kolkata');

        try {
            $carbon = $date instanceof CarbonInterface
                ? $date->setTimezone($timezone)
                : Carbon::parse($date)->setTimezone($timezone);

            return $includeTime
                ? $carbon->format($dateFormat.', h:i A')
                : $carbon->format($dateFormat);
        } catch (\Throwable) {
            return (string) $date;
        }
    }

    /**
     * Get the store legal business address formatted as a string.
     */
    public function getFormattedBusinessAddress(): string
    {
        $parts = array_filter([
            $this->get('address_line1'),
            $this->get('address_line2'),
            $this->get('city'),
            $this->get('state'),
            $this->get('postal_code'),
            $this->get('country'),
        ]);

        return implode(', ', $parts);
    }

    /**
     * Send order notifications and triggers (email, webhook) if configured.
     */
    public function notifyOrderPlaced(Order $order): void
    {
        $this->applyMailConfig();

        $storeName = (string) $this->get('store_name', 'Custom Commerce');
        $fromAddress = (string) $this->get('mail_from_address', config('mail.from.address', 'noreply@customcommerce.test'));
        $fromName = (string) $this->get('mail_from_name', $storeName);

        // 1. Customer order confirmation email
        if (! empty($order->customer_email)) {
            try {
                $totalFormatted = $this->formatPrice((float) $order->total);
                Mail::raw(
                    "Hello {$order->customer_first_name},\n\nThank you for ordering with {$storeName}!\n\nOrder Number: {$order->order_number}\nOrder Total: {$totalFormatted}\nPayment Method: {$order->payment_method_label}\nStatus: Pending\n\nWe will notify you once your order has been dispatched.",
                    function ($message) use ($order, $storeName, $fromAddress, $fromName) {
                        $message->to($order->customer_email)
                            ->from($fromAddress, $fromName)
                            ->subject("[{$storeName}] Order Confirmation - #{$order->order_number}");
                    }
                );
            } catch (\Throwable) {
                // Silently avoid interrupting the order response
            }
        }

        // 2. Admin new order alert
        $notifyNewOrder = (bool) $this->get('notify_new_order', true);
        $adminEmail = (string) $this->get('admin_notification_email', $this->get('contact_email', ''));

        if ($notifyNewOrder && ! empty($adminEmail)) {
            try {
                $totalFormatted = $this->formatPrice((float) $order->total);
                Mail::raw(
                    "A new customer order has been placed on {$storeName}!\n\nOrder Number: {$order->order_number}\nCustomer: {$order->customer_first_name} {$order->customer_last_name} ({$order->customer_email})\nTotal: {$totalFormatted}\nPayment: {$order->payment_method_label}\n\nView details in your admin dashboard.",
                    function ($message) use ($adminEmail, $order, $storeName, $fromAddress, $fromName) {
                        $message->to($adminEmail)
                            ->from($fromAddress, $fromName)
                            ->subject("[{$storeName}] New Order Received - #{$order->order_number}");
                    }
                );
            } catch (\Throwable) {
                // Silently continue
            }
        }

        // 3. API Webhook dispatch if enabled
        $apiEnabled = (bool) $this->get('enable_api_access', false);
        $webhookUrl = (string) $this->get('api_webhook_url', '');

        if ($apiEnabled && ! empty($webhookUrl)) {
            try {
                Http::timeout(3)->post($webhookUrl, [
                    'event' => 'order.created',
                    'order_number' => $order->order_number,
                    'total' => (float) $order->total,
                    'subtotal' => (float) $order->subtotal,
                    'tax' => (float) $order->tax_amount,
                    'shipping' => (float) $order->shipping_amount,
                    'payment_method' => $order->payment_method ?? 'cod',
                    'customer' => [
                        'name' => trim($order->customer_first_name.' '.$order->customer_last_name),
                        'email' => $order->customer_email,
                    ],
                    'created_at' => $order->created_at?->toISOString(),
                ]);
            } catch (\Throwable) {
                // Silently continue
            }
        }
    }

    /**
     * Check and alert admin if inventory quantity dropped to or below low stock threshold.
     */
    public function checkAndNotifyLowStock(ProductVariant $variant): void
    {
        $notify = (bool) $this->get('notify_low_stock', true);
        if (! $notify) {
            return;
        }

        $threshold = (int) $this->get('low_stock_threshold', 5);
        $available = $variant->inventory ? $variant->inventory->quantity : 0;

        if ($available <= $threshold) {
            $adminEmail = (string) $this->get('admin_notification_email', $this->get('contact_email', ''));
            if (! empty($adminEmail)) {
                $storeName = (string) $this->get('store_name', 'Custom Commerce');
                $fromAddress = (string) $this->get('mail_from_address', config('mail.from.address', 'noreply@customcommerce.test'));
                $fromName = (string) $this->get('mail_from_name', $storeName);

                try {
                    $this->applyMailConfig();
                    Mail::raw(
                        "Low stock alert for {$variant->product->name} ({$variant->title})!\n\nSKU: {$variant->sku}\nCurrent Stock: {$available} unit(s) remaining (threshold is {$threshold}).\n\nPlease restock soon.",
                        function ($message) use ($adminEmail, $variant, $storeName, $fromAddress, $fromName) {
                            $message->to($adminEmail)
                                ->from($fromAddress, $fromName)
                                ->subject("[{$storeName}] Low Stock Alert: {$variant->product->name}");
                        }
                    );
                } catch (\Throwable) {
                    // Silently continue
                }
            }
        }
    }

    /**
     * Send a test email using configured SMTP settings.
     */
    public function sendTestEmail(string $recipientEmail): bool
    {
        $this->applyMailConfig();

        $fromAddress = $this->get('mail_from_address', config('mail.from.address', 'noreply@customcommerce.test'));
        $fromName = $this->get('mail_from_name', config('mail.from.name', 'Custom Commerce'));
        $storeName = $this->get('store_name', 'Custom Commerce');

        try {
            Mail::raw(
                "Hello,\n\nThis is a test email sent from your {$storeName} Global Settings configuration.\nYour SMTP and Email configuration is active and working properly!\n\nSent at: ".now()->toDateTimeString(),
                function ($message) use ($recipientEmail, $fromAddress, $fromName, $storeName) {
                    $message->to($recipientEmail)
                        ->from($fromAddress, $fromName)
                        ->subject("[{$storeName}] Test Email Verification");
                }
            );

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Clear cached settings.
     */
    public function clearCache(): void
    {
        Setting::clearCache();
    }
}
