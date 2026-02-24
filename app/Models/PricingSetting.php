<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/*
|--------------------------------------------------------------------------
| Pricing Setting Model
|--------------------------------------------------------------------------
| Stores exchange rate, logistics, fixed margin.
|--------------------------------------------------------------------------
*/

class PricingSetting extends Model
{
    protected $fillable = [
        'brand_id',
        'exchange_rate',
        'logistics_cost_cny',
        'fixed_margin_ngn',
        'price_rounding_unit',
        'is_active',
    ];

    public function brand()
    {
        return $this->belongsTo(\App\Models\Brand::class);
    }
}
