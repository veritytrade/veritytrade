<?php

namespace App\Modules\Phones\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PhoneModelImage extends Model
{
    protected $fillable = [
        'phone_model_id',
        'path',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function phoneModel(): BelongsTo
    {
        return $this->belongsTo(PhoneModel::class, 'phone_model_id');
    }
}
