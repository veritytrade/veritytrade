<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'price_ngn',
        'description_en',
        'specs_json',
        'condition_notes',
        'status',
        'stock',
        'source_site',
        'source_item_id',
        'source_url_private',
    ];

    protected $casts = [
        'price_ngn' => 'integer',
        'stock' => 'integer',
        'specs_json' => 'array',
    ];

    // Safety net: never serialize private source URL by default.
    protected $hidden = [
        'source_url_private',
    ];

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('position');
    }
}
