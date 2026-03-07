<?php

namespace App\Modules\Phones\Services;

use App\Modules\Phones\Models\PhonePricingSetting;
use App\Modules\Phones\Models\PhoneVariant;

class PhonePricingService
{
    /**
    * @return array{min_ngn:int|null,max_ngn:int|null}
    */
    public function calculate(PhoneVariant $variant): array
    {
        $setting = PhonePricingSetting::active();

        if (!$setting) {
            return ['min_ngn' => null, 'max_ngn' => null];
        }

        $minCny = (float) $variant->min_price_cny;
        $maxCny = (float) $variant->max_price_cny;
        $logistics = (float) ($setting->logistics_cny ?? 0);
        $rate = (float) $setting->exchange_rate;
        $margin = (float) ($setting->profit_margin_ngn ?? 0);
        $rounding = max(1, (int) ($setting->rounding_unit ?? 10000));

        $minBase = ($minCny + $logistics) * $rate + $margin;
        $maxBase = ($maxCny + $logistics) * $rate + $margin;

        $minNgn = (int) (ceil($minBase / $rounding) * $rounding);
        $maxNgn = (int) (ceil($maxBase / $rounding) * $rounding);

        return [
            'min_ngn' => $minNgn,
            'max_ngn' => $maxNgn,
        ];
    }
}

