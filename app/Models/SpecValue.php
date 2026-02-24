<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SpecValue extends Model
{
    protected $fillable = [
        'spec_id',
        'value',
        'position',
    ];

    public function spec(): BelongsTo
    {
        return $this->belongsTo(Specification::class, 'spec_id');
    }

    public function models(): BelongsToMany
    {
        return $this->belongsToMany(Device::class, 'model_spec_values', 'spec_value_id', 'model_id')
            ->withTimestamps();
    }
}
