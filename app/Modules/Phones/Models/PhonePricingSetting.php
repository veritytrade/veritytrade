<?php

namespace App\Modules\Phones\Models;

use Illuminate\Database\Eloquent\Model;

class PhonePricingSetting extends Model
{
    protected $fillable = [
        'exchange_rate',
        'logistics_cny',
        'rounding_unit',
        'profit_margin_ngn',
        'is_active',
    ];

    public static function active(): ?self
    {
        return static::where('is_active', true)->latest('id')->first();
    }
}

