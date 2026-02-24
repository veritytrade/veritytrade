<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/*
|--------------------------------------------------------------------------
| Permission Model
|--------------------------------------------------------------------------
| Represents system capabilities such as manage_categories.
|--------------------------------------------------------------------------
*/

class Permission extends Model
{
    protected $fillable = [
        'name',
        'description',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    // Permission belongs to many roles
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }
}
