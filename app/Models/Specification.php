<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Specification extends Model
{
    protected $table = 'specs';

    protected $fillable = [
        'spec_group_id',
        'name',
        'input_type',
        'is_required',
        'position',
    ];

    protected $casts = [
        'is_required' => 'boolean',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(SpecGroup::class, 'spec_group_id');
    }

    public function values(): HasMany
    {
        return $this->hasMany(SpecValue::class, 'spec_id')->orderBy('position');
    }
}
