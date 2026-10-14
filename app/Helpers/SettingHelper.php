<?php

namespace App\Helpers;

use App\Services\SettingService;

class SettingHelper
{
    /**
     * Format a price according to currency and formatting settings.
     */
    public static function formatPrice(float|int|string|null $amount): string
    {
        return app(SettingService::class)->formatPrice((float) ($amount ?? 0));
    }

    /**
     * Get the configured currency symbol.
     */
    public static function currencySymbol(): string
    {
        return (string) app(SettingService::class)->get('currency_symbol', '₹');
    }

    /**
     * Get a store setting value with optional fallback.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return app(SettingService::class)->get($key, $default);
    }

    /**
     * Get the store name.
     */
    public static function storeName(): string
    {
        return (string) app(SettingService::class)->get('store_name', config('app.name', 'Custom Commerce'));
    }

    /**
     * Get the store logo storage path if set.
     */
    public static function storeLogo(): ?string
    {
        return app(SettingService::class)->get('store_logo');
    }

    /**
     * Format a date according to store timezone and date format.
     */
    public static function formatDate(mixed $date, bool $includeTime = false): string
    {
        return app(SettingService::class)->formatDate($date, $includeTime);
    }

    /**
     * Get the store legal business address.
     */
    public static function businessAddress(): string
    {
        return app(SettingService::class)->getFormattedBusinessAddress();
    }
}
