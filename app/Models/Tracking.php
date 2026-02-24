<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tracking extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'order_id',
        'status_label',
        'description',
        'updated_by',
        'event_time',
    ];

    protected $casts = [
        'event_time' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
