<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShipmentBatch extends Model
{
    protected $fillable = [
        'china_tracking_code',
        'current_stage',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'shipment_batch_id');
    }

    protected static function booted(): void
    {
        static::saved(function (ShipmentBatch $batch) {
            if (!$batch->wasChanged('current_stage')) {
                return;
            }

            $batch->orders()->update([
                'current_stage' => $batch->current_stage,
                'updated_at' => now(),
            ]);
        });
    }
}
