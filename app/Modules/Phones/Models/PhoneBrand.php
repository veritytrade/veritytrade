<?php

namespace App\Modules\Phones\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class PhoneBrand extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'image',
        'is_active',
    ];

    protected static function booted(): void
    {
        static::creating(function (PhoneBrand $brand): void {
            if (empty($brand->slug)) {
                $brand->slug = Str::slug((string) $brand->name);
            }
        });

        static::updating(function (PhoneBrand $brand): void {
            // Prevent manual slug changes; keep original
            if ($brand->isDirty('slug')) {
                $brand->slug = $brand->getOriginal('slug');
            }
        });
    }

    public function models(): HasMany
    {
        return $this->hasMany(PhoneModel::class, 'phone_brand_id');
    }
}

