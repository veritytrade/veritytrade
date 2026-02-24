<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/*
|--------------------------------------------------------------------------
| Role Model
|--------------------------------------------------------------------------
| Represents roles such as super_admin, admin, staff, customer.
|--------------------------------------------------------------------------
*/

class Role extends Model
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

    // Role belongs to many users
    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function primaryUsers()
    {
        return $this->hasMany(User::class, 'role_id');
    }

    // Role has many permissions
    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }
}
