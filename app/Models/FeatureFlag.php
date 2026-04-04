<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

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

    /**
     * Default rows for flags managed in the admin Feature Flags panel (seed + first visit).
     *
     * @return array<string, array{value: string, group: string, description: string}>
     */
    public static function adminVisibleDefaults(): array
    {
        return [
            'require_email_verification' => [
                'value' => '1',
                'group' => 'auth',
                'description' => 'Require 6-digit email verification code before login',
            ],
            'require_admin_approval' => [
                'value' => '1',
                'group' => 'auth',
                'description' => 'Require admin approval before login',
            ],
            'enable_customer_address' => [
                'value' => '0',
                'group' => 'profile',
                'description' => 'Enable customer address fields in profile/forms',
            ],
            'enable_logistics_update_emails' => [
                'value' => '1',
                'group' => 'mail',
                'description' => 'Email customers when shipment stage changes or new carrier tracking rows arrive (only while in transit; stops after dispatched)',
            ],
            'mail_from_address' => [
                'value' => (string) env('MAIL_FROM_ADDRESS', ''),
                'group' => 'mail',
                'description' => 'Sender email for system messages (leave empty to use .env MAIL_FROM_ADDRESS)',
            ],
            'mail_from_name' => [
                'value' => (string) env('MAIL_FROM_NAME', ''),
                'group' => 'mail',
                'description' => 'Sender name for system messages (leave empty to use .env MAIL_FROM_NAME)',
            ],
            'whatsapp_number' => [
                'value' => (string) env('WHATSAPP_BUSINESS_NUMBER', ''),
                'group' => 'public',
                'description' => 'Primary WhatsApp business number (leave empty to use .env)',
            ],
        ];
    }

    /**
     * Create missing feature flag rows so the admin panel always lists every visible key.
     *
     * @param  array<int, string>  $keys
     */
    public static function ensureKeysExist(array $keys): void
    {
        if (! Schema::hasTable('feature_flags')) {
            return;
        }

        $defaults = self::adminVisibleDefaults();
        foreach ($keys as $key) {
            if (! isset($defaults[$key])) {
                continue;
            }
            self::firstOrCreate(
                ['key' => $key],
                array_merge($defaults[$key], [
                    'is_active' => true,
                ])
            );
        }
    }
}
