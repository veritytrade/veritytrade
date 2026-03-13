<?php

use App\Models\FeatureFlag;

if (!function_exists('setting_value')) {
    function setting_value(string $key, $default = null)
    {
        try {
            return FeatureFlag::value($key, $default);
        } catch (\Throwable $e) {
            return $default;
        }
    }
}

if (!function_exists('feature_enabled')) {
    function feature_enabled(string $key, bool $default = false): bool
    {
        return FeatureFlag::enabled($key, $default);
    }
}

if (!function_exists('site_setting')) {
    function site_setting(string $key, $default = false)
    {
        return setting_value($key, $default);
    }
}
