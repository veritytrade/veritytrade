<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class FeatureFlag extends Model
{
    protected $fillable = [
        'key',
        'value',
        'group',
        'description',
        'is_active',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /** Cache TTL in seconds (5 min). */
    private const CACHE_TTL = 300;

    public static function value(string $key, $default = null)
    {
        $cacheKey = 'feature_flag_value:' . $key;
        try {
            $raw = Cache::remember($cacheKey, self::CACHE_TTL, fn () => self::query()->where('key', $key)->value('value'));
        } catch (\Throwable) {
            return $default;
        }

        return $raw ?? $default;
    }

    public static function enabled(string $key, bool $default = false): bool
    {
        $cacheKey = 'feature_flag_enabled:' . $key;
        try {
            $raw = Cache::remember($cacheKey, self::CACHE_TTL, fn () => self::query()->where('key', $key)->value('value'));
        } catch (\Throwable) {
            return $default;
        }

        if ($raw === null) {
            return $default;
        }

        return filter_var($raw, FILTER_VALIDATE_BOOLEAN);
    }

    /** Clear cached values for a key (call after update). */
    public static function clearCache(string $key): void
    {
        Cache::forget('feature_flag_value:' . $key);
        Cache::forget('feature_flag_enabled:' . $key);
    }
}
