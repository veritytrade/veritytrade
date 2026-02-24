<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModelSpecValue extends Model
{
    protected $table = 'model_spec_values';

    protected $fillable = [
        'model_id',
        'spec_value_id',
    ];

    public function model(): BelongsTo
    {
        return $this->belongsTo(Device::class, 'model_id');
    }

    public function specValue(): BelongsTo
    {
        return $this->belongsTo(SpecValue::class, 'spec_value_id');
    }
}
