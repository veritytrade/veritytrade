<?php

namespace App\Modules\Phones\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Modules\Phones\Models\PhoneBrand;
use App\Modules\Phones\Models\PhoneModel;
use App\Modules\Phones\Models\PhoneSpec;
use App\Modules\Phones\Models\PhoneVariant;
use App\Modules\Phones\Services\PhonePricingService;
use Illuminate\Contracts\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PhoneBrowseController extends Controller
{
    public function index(): View
    {
        $brands = PhoneBrand::where('is_active', true)->orderBy('name')->get();

        return view('phones.index', compact('brands'));
    }

    public function brand(string $brandSlug): View
    {
        $brand = PhoneBrand::where('slug', $brandSlug)
            ->where('is_active', true)
            ->first();

        if (!$brand) {
            throw new NotFoundHttpException();
        }

        $models = $brand->models()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('phones.brand', compact('brand', 'models'));
    }

    public function model(
        string $brandSlug,
        string $modelSlug,
        PhonePricingService $pricingService
    ): View {
        $brand = PhoneBrand::where('slug', $brandSlug)
            ->where('is_active', true)
            ->first();

        if (!$brand) {
            throw new NotFoundHttpException();
        }

        $model = PhoneModel::with('images')
            ->where('slug', $modelSlug)
            ->where('phone_brand_id', $brand->id)
            ->where('is_active', true)
            ->first();

        if (!$model) {
            throw new NotFoundHttpException();
        }

        $variants = PhoneVariant::with('specValues.spec')
            ->where('phone_model_id', $model->id)
            ->where('is_active', true)
            ->get();

        $specs = PhoneSpec::with('values')->where('is_active', true)->orderBy('position')->get();

        $variantsData = $variants->map(function (PhoneVariant $v) use ($pricingService) {
            $range = $pricingService->calculate($v);
            $bySpec = [];
            foreach ($v->specValues as $sv) {
                $bySpec[$sv->spec->name ?? ''] = $sv->id;
            }

            return [
                'id' => $v->id,
                'min_ngn' => $range['min_ngn'],
                'max_ngn' => $range['max_ngn'],
                'storage_id' => $bySpec['Storage'] ?? null,
                'appearance_id' => $bySpec['Appearance'] ?? null,
                'function_id' => $bySpec['Function'] ?? null,
            ];
        });

        return view('phones.model', [
            'brand' => $brand,
            'model' => $model,
            'specs' => $specs,
            'variants' => $variantsData,
        ]);
    }
}
