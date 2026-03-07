<?php

namespace App\Modules\Phones\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/** @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Modules\Phones\Models\PhoneModelImage> $images */

class PhoneModel extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'phone_brand_id',
        'name',
        'slug',
        'image',
        'is_active',
    ];

    protected static function booted(): void
    {
        static::creating(function (PhoneModel $model): void {
            if (empty($model->slug)) {
                $baseSlug = Str::slug((string) $model->name);
                $slug = $baseSlug;
                $counter = 2;

                // Ensure slug is unique per brand, including soft-deleted records
                while (static::withTrashed()
                    ->where('phone_brand_id', $model->phone_brand_id)
                    ->where('slug', $slug)
                    ->exists()) {
                    $slug = $baseSlug.'-'.$counter;
                    $counter++;
                }

                $model->slug = $slug;
            }
        });

        static::updating(function (PhoneModel $model): void {
            if ($model->isDirty('slug')) {
                $model->slug = $model->getOriginal('slug');
            }
        });
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(PhoneBrand::class, 'phone_brand_id');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(PhoneVariant::class, 'phone_model_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(PhoneModelImage::class, 'phone_model_id')->orderBy('sort_order');
    }
}
