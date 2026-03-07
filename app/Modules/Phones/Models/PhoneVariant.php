<?php

namespace App\Modules\Phones\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PhoneVariant extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'phone_model_id',
        'min_price_cny',
        'max_price_cny',
        'is_active',
    ];

    public function model(): BelongsTo
    {
        return $this->belongsTo(PhoneModel::class, 'phone_model_id');
    }

    public function specValues(): BelongsToMany
    {
        return $this->belongsToMany(
            PhoneSpecValue::class,
            'phone_variant_spec_values',
            'phone_variant_id',
            'phone_spec_value_id'
        );
    }
}

