<?php

namespace App\Models;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Device;
use App\Models\Series;
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
        'request_spec_json',
        'phone_number',
        'status',
        'processed_by',
    ];

    protected $casts = [
        'request_spec_json' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function series(): BelongsTo
    {
        return $this->belongsTo(Series::class);
    }

    public function model(): BelongsTo
    {
        return $this->belongsTo(Device::class, 'model_id');
    }
}
