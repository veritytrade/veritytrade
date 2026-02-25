<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    public static function value(string $key, $default = null)
    {
        $flag = self::query()->where('key', $key)->first();

        if (!$flag) {
            return $default;
        }

        return $flag->value ?? $default;
    }

    public static function enabled(string $key, bool $default = false): bool
    {
        $value = self::value($key, $default ? '1' : '0');

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
}
