<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/*
|--------------------------------------------------------------------------
| Appearance Grade Model
|--------------------------------------------------------------------------
| 99%, 95%, etc.
|--------------------------------------------------------------------------
*/

class AppearanceGrade extends Model
{
    protected $fillable = [
        'percentage',
        'is_active',
    ];

    public function priceRules(): HasMany
    {
        return $this->hasMany(PriceRule::class);
    }
}
