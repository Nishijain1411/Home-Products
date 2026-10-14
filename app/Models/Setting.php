<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class Setting extends Model
{
    protected $fillable = [
        'group',
        'key',
        'value',
        'type',
    ];

    /**
     * Cache key for all settings.
     */
    public const CACHE_KEY = 'global_store_settings';

    /**
     * Get typed value attribute.
     */
    public function getCastValueAttribute(): mixed
    {
        if ($this->value === null) {
            return null;
        }

        return match ($this->type) {
            'boolean', 'bool' => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            'integer', 'int' => (int) $this->value,
            'float', 'numeric', 'double' => (float) $this->value,
            'json', 'array' => json_decode($this->value, true) ?? [],
            default => $this->value,
        };
    }

    /**
     * Retrieve a setting by key with fallback default.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $all = static::getAllCached();

        if (array_key_exists($key, $all)) {
            return $all[$key];
        }

        return $default;
    }

    /**
     * Set a setting key and value.
     */
    public static function set(string $key, mixed $value, string $group = 'general', ?string $type = null): ?self
    {
        if (! static::isTableAvailable()) {
            return null;
        }

        if ($value === null) {
            $type = $type ?? 'string';
            $serializedValue = null;
        } else {
            if ($type === null) {
                $type = match (true) {
                    is_bool($value) => 'boolean',
                    is_int($value) => 'integer',
                    is_float($value) => 'float',
                    is_array($value) => 'json',
                    default => 'string',
                };
            }

            $serializedValue = match ($type) {
                'boolean' => $value ? '1' : '0',
                'json', 'array' => is_string($value) ? $value : json_encode($value),
                default => (string) $value,
            };
        }

        $setting = static::updateOrCreate(
            ['key' => $key],
            [
                'group' => $group,
                'value' => $serializedValue,
                'type' => $type,
            ]
        );

        static::clearCache();

        return $setting;
    }

    /**
     * Retrieve all settings in a specific group as key => cast_value associative array.
     */
    public static function getGroup(string $group): array
    {
        if (! static::isTableAvailable()) {
            return [];
        }

        return static::where('group', $group)
            ->get()
            ->mapWithKeys(fn (self $item) => [$item->key => $item->cast_value])
            ->toArray();
    }

    /**
     * Save multiple settings for a group at once.
     */
    public static function setMany(array $settings, string $group = 'general'): void
    {
        foreach ($settings as $key => $value) {
            static::set($key, $value, $group);
        }
    }

    /**
     * Check if a setting key exists.
     */
    public static function has(string $key): bool
    {
        $all = static::getAllCached();

        return array_key_exists($key, $all);
    }

    /**
     * Get all settings cached as an associative array.
     */
    public static function getAllCached(): array
    {
        if (! static::isTableAvailable()) {
            return [];
        }

        return Cache::remember(self::CACHE_KEY, 3600, function () {
            return static::all()
                ->mapWithKeys(fn (self $item) => [$item->key => $item->cast_value])
                ->toArray();
        });
    }

    /**
     * Clear settings cache.
     */
    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Check if the settings table exists defensively.
     */
    public static function isTableAvailable(): bool
    {
        try {
            return Schema::hasTable('settings');
        } catch (\Throwable) {
            return false;
        }
    }
}
