<?php

namespace App\Modules\Phones\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PhoneSpec extends Model
{
    protected $fillable = [
        'name',
        'position',
        'is_active',
    ];

    public function values(): HasMany
    {
        return $this->hasMany(PhoneSpecValue::class, 'phone_spec_id');
    }
}

