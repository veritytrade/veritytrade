<?php

namespace App\Models;

use App\Casts\SafeArrayCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Shipment extends Model
{
    protected $fillable = [
        'chinese_tracking_code',
        'logistics_company',
        'carrier_tracks_json',
        'carrier_tracks_synced_at',
        'current_stage_id',
        'status',
        'waybill_outstanding_ngn',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'waybill_outstanding_ngn' => 'decimal:2',
        'carrier_tracks_json' => SafeArrayCast::class,
        'carrier_tracks_synced_at' => 'datetime',
    ];

    public function currentStage(): BelongsTo
    {
        return $this->belongsTo(TrackingStage::class, 'current_stage_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function invoiceRequest(): HasOne
    {
        return $this->hasOne(InvoiceRequest::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
