<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Order extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'user_id',
        'shipment_batch_id',
        'total_amount_ngn',
        'status',
        'tracking_code',
        'verity_tracking_code',
        'current_stage',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function trackingEvents(): HasMany
    {
        return $this->hasMany(Tracking::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    public function shipmentBatch(): BelongsTo
    {
        return $this->belongsTo(ShipmentBatch::class, 'shipment_batch_id');
    }

    public function stages(): HasMany
    {
        return $this->hasMany(TrackingStage::class)->orderBy('stage_number');
    }

    public function assignToShipmentBatch(?ShipmentBatch $batch): void
    {
        $this->shipment_batch_id = $batch?->id;
        $this->current_stage = $batch?->current_stage;

        if (!$this->verity_tracking_code) {
            $this->verity_tracking_code = strtoupper(Str::random(10));
        }

        $this->save();
    }
}
