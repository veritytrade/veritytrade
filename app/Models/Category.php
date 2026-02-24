<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\ActiveScope;

/*
|--------------------------------------------------------------------------
| Category Model
|--------------------------------------------------------------------------
| Represents top-level catalog groups like Phones or Laptops.
|--------------------------------------------------------------------------
*/

class Category extends Model
{
    use SoftDeletes;
    use ActiveScope;
    
    protected $fillable = [
        'name',
        'is_active',
        'position',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    // Category has many brands
    public function brands()
    {
        return $this->hasMany(Brand::class);
    }

    public function specGroups()
    {
        return $this->hasMany(SpecGroup::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scope: Only Active Categories
    |--------------------------------------------------------------------------
    */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
