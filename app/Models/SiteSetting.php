<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Throwable;

class SiteSetting extends Model
{
    protected $fillable = ['key', 'value', 'label', 'description'];

    /**
     * Get setting value by key (with cache)
     */
    public static function get(string $key, $default = false)
    {
        if (!Schema::hasTable('site_settings')) {
            return $default;
        }

        return cache()->rememberForever("site_setting_{$key}", function () use ($key, $default) {
            try {
                $setting = self::where('key', $key)->first();
                return $setting ? $setting->value : $default;
            } catch (Throwable $e) {
                return $default;
            }
        });
    }

    /**
     * Clear cache when settings change
     */
    protected static function booted()
    {
        static::saved(function () {
            cache()->forget('site_settings_all');
            // Clear individual setting caches on next request
        });
    }
}
