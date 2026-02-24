<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\ActiveScope;

/*
|--------------------------------------------------------------------------
| Series Model
|--------------------------------------------------------------------------
| Represents product series under brands (e.g., "iPhone 17 Series").
| One brand can have multiple series.
|--------------------------------------------------------------------------
*/
class Series extends Model
{
    use SoftDeletes;
    use ActiveScope;
    
    protected $fillable = [
        'brand_id',
        'name',
        'representative_image',
        'image_path',
        'is_active',
        'position',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    // Connect to parent Brand (e.g., "Apple")
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    // Connect to child Devices (e.g., "iPhone 17 Pro Max", "iPhone 17 Pro")
    // ⚠️ IMPORTANT: We use "Device" class (not "Model") because we renamed it
    public function devices()
    {
        return $this->hasMany(Device::class, 'series_id');
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPE: Only show active series
    |--------------------------------------------------------------------------
    */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
