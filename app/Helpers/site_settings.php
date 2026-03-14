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
        try {
            return FeatureFlag::enabled($key, $default);
        } catch (\Throwable $e) {
            return $default;
        }
    }
}

if (!function_exists('site_setting')) {
    function site_setting(string $key, $default = false)
    {
        return setting_value($key, $default);
    }
}

if (!function_exists('mail_from')) {
    /**
     * Return valid From address and name for sending mail (never empty; avoids SMTP rejections).
     * Uses feature flags if set, otherwise config (.env).
     */
    function mail_from(): array
    {
        $address = (string) setting_value('mail_from_address', config('mail.from.address'));
        $name = (string) setting_value('mail_from_name', config('mail.from.name'));
        if (trim($address) === '') {
            $address = (string) config('mail.from.address');
        }
        if (trim($name) === '') {
            $name = (string) config('mail.from.name') ?: 'VerityTrade';
        }
        return ['address' => $address, 'name' => $name];
    }
}
