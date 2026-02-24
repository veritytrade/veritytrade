<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/*
|--------------------------------------------------------------------------
| Functionality Grade Model
|--------------------------------------------------------------------------
| S / A / B / C
|--------------------------------------------------------------------------
*/

class FunctionalityGrade extends Model
{
    protected $fillable = [
        'grade',
        'is_active',
    ];

    public function priceRules(): HasMany
    {
        return $this->hasMany(PriceRule::class);
    }
}
