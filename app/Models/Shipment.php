<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Shipment extends Model
{
    protected $fillable = [
        'chinese_tracking_code',
        'logistics_company',
        'current_stage_id',
        'status',
        'created_by',
        'updated_by',
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
