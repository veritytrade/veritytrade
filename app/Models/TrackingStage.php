<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrackingStage extends Model
{
    protected $fillable = [
        'name',
        'short_name',
        'position',
        'description',
        'color_code',
    ];

    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class, 'current_stage_id');
    }

    public function ordersWithOverride(): HasMany
    {
        return $this->hasMany(Order::class, 'current_stage_id');
    }
}
