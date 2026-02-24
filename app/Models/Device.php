<?php

namespace App\Models;

use App\Models\Traits\ActiveScope;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Device extends EloquentModel
{
    use SoftDeletes;
    use ActiveScope;

    protected $table = 'models';

    protected $fillable = [
        'uuid',
        'brand_id',
        'series_id',
        'name',
        'representative_image',
        'image_path',
        'is_active',
        'position',
    ];

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function series(): BelongsTo
    {
        return $this->belongsTo(Series::class);
    }

    public function priceRules(): HasMany
    {
        return $this->hasMany(PriceRule::class, 'model_id');
    }

    public function specValues(): BelongsToMany
    {
        return $this->belongsToMany(SpecValue::class, 'model_spec_values', 'model_id', 'spec_value_id')
            ->withTimestamps();
    }
}
