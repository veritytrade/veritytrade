<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceRule extends Model
{
    protected $fillable = [
        'model_id',
        'memory_id',
        'functionality_grade_id',
        'appearance_grade_id',
        'spec_combination_json',
        'min_price_cny',
        'max_price_cny',
        'is_active',
    ];

    protected $casts = [
        'spec_combination_json' => 'array',
        'is_active' => 'boolean',
    ];

    public function model(): BelongsTo
    {
        return $this->belongsTo(Device::class, 'model_id');
    }

    public function memory(): BelongsTo
    {
        return $this->belongsTo(Memory::class);
    }

    public function functionalityGrade(): BelongsTo
    {
        return $this->belongsTo(FunctionalityGrade::class);
    }

    public function appearanceGrade(): BelongsTo
    {
        return $this->belongsTo(AppearanceGrade::class);
    }

    public function getMinPriceNgnAttribute()
    {
        $brand = $this->model?->brand ?? $this->model?->series?->brand;
        $setting = $brand?->pricingSettings()?->where('is_active', true)->first();

        if (!$setting) {
            return null;
        }

        $base = ($this->min_price_cny + $setting->logistics_cost_cny) * $setting->exchange_rate;
        $total = $base + $setting->fixed_margin_ngn;

        $rounding = max(1, (int) ($setting->price_rounding_unit ?? 10000));

        return (int) (ceil($total / $rounding) * $rounding);
    }

    public function getMaxPriceNgnAttribute()
    {
        $brand = $this->model?->brand ?? $this->model?->series?->brand;
        $setting = $brand?->pricingSettings()?->where('is_active', true)->first();

        if (!$setting) {
            return null;
        }

        $base = ($this->max_price_cny + $setting->logistics_cost_cny) * $setting->exchange_rate;
        $total = $base + $setting->fixed_margin_ngn;

        $rounding = max(1, (int) ($setting->price_rounding_unit ?? 10000));

        return (int) (ceil($total / $rounding) * $rounding);
    }
}
