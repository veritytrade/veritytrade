<?php

namespace App\Modules\Phones\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PhoneSpecValue extends Model
{
    protected $fillable = [
        'phone_spec_id',
        'value',
        'position',
        'is_active',
    ];

    public function spec(): BelongsTo
    {
        return $this->belongsTo(PhoneSpec::class, 'phone_spec_id');
    }

    public function variants(): BelongsToMany
    {
        return $this->belongsToMany(
            PhoneVariant::class,
            'phone_variant_spec_values',
            'phone_spec_value_id',
            'phone_variant_id'
        );
    }
}

