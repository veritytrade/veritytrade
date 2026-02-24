<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/*
|--------------------------------------------------------------------------
| Memory Model
|--------------------------------------------------------------------------
| Represents global memory sizes (64GB, 128GB, etc.)
|--------------------------------------------------------------------------
*/

class Memory extends Model
{
    protected $fillable = [
        'size_gb',
        'is_active',
    ];

    public function priceRules(): HasMany
    {
        return $this->hasMany(PriceRule::class);
    }
}
