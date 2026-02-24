<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerRequest extends Model
{
    use SoftDeletes;

    protected $table = 'requests';

    protected $fillable = [
        'uuid',
        'user_id',
        'category_id',
        'brand_id',
        'series_id',
        'model_id',
        'manual_model_name',
        'memory_id',
        'functionality_grade_id',
        'appearance_grade_id',
        'phone_number',
        'status',
        'processed_by',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
