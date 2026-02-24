<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\ActiveScope;

/*
|--------------------------------------------------------------------------
| Brand Model
|--------------------------------------------------------------------------
| Represents brand under a category (e.g., "Apple" under "Phones").
| Can optionally enable structured pricing engine.
|--------------------------------------------------------------------------
*/
class Brand extends Model
{
    use SoftDeletes;
    use ActiveScope;
    
    protected $fillable = [
        'uuid',
        'category_id',
        'name',
        'representative_image',
        'image_path',
        'is_active',
        'position',
        'uses_pricing_engine',  // Toggle to enable pricing engine for this brand
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    // Connect to parent Category (e.g., "Phones")
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Connect to child Series (e.g., "iPhone 17 Series")
    public function series()
    {
        return $this->hasMany(Series::class);
    }

    public function devices()
    {
        return $this->hasMany(Device::class, 'brand_id');
    }

    // Connect to Pricing Settings (brand-specific pricing rules)
    public function pricingSettings()
    {
        return $this->hasMany(PricingSetting::class, 'brand_id');
    }
}
