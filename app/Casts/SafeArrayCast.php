<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

/**
 * Decodes JSON columns without throwing when the DB holds invalid JSON (prevents 500s on read).
 */
final class SafeArrayCast implements CastsAttributes
{
    /**
     * @param  mixed  $value
     * @return array<string, mixed>|null
     */
    public function get($model, string $key, $value, array $attributes): ?array
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_array($value)) {
            return $value;
        }
        if (! is_string($value)) {
            return [];
        }
        $decoded = json_decode($value, true);
        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded)) {
            return [];
        }

        return $decoded;
    }

    /**
     * @param  mixed  $value
     */
    public function set($model, string $key, $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }
        if (! is_array($value)) {
            $value = [];
        }
        $encoded = json_encode($value);
        if ($encoded === false) {
            return json_encode([]);
        }

        return $encoded;
    }
}
