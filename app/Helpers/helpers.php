<?php

use App\Helpers\SettingHelper;

if (! function_exists('format_price')) {
    /**
     * Format an amount into the configured currency format.
     */
    function format_price(float|int|string|null $amount): string
    {
        return SettingHelper::formatPrice($amount);
    }
}

if (! function_exists('currency_symbol')) {
    /**
     * Get the active store currency symbol.
     */
    function currency_symbol(): string
    {
        return SettingHelper::currencySymbol();
    }
}

if (! function_exists('store_setting')) {
    /**
     * Retrieve a global store setting value.
     */
    function store_setting(string $key, mixed $default = null): mixed
    {
        return SettingHelper::get($key, $default);
    }
}

if (! function_exists('store_name')) {
    /**
     * Get the configured store name.
     */
    function store_name(): string
    {
        return SettingHelper::storeName();
    }
}

if (! function_exists('store_logo')) {
    /**
     * Get the configured store logo path.
     */
    function store_logo(): ?string
    {
        return SettingHelper::storeLogo();
    }
}

if (! function_exists('format_date')) {
    /**
     * Format a date according to store timezone and date format.
     */
    function format_date(mixed $date, bool $includeTime = false): string
    {
        return SettingHelper::formatDate($date, $includeTime);
    }
}

if (! function_exists('business_address')) {
    /**
     * Get the store legal business address.
     */
    function business_address(): string
    {
        return SettingHelper::businessAddress();
    }
}
