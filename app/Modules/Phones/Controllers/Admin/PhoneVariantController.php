<?php

namespace App\Modules\Phones\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Phones\Models\PhoneModel;
use App\Modules\Phones\Models\PhoneSpec;
use App\Modules\Phones\Models\PhoneVariant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PhoneVariantController extends Controller
{
    public function index(PhoneModel $model): View
    {
        $variants = $model->variants()->with('specValues.spec')->orderBy('id')->get();

        return view('admin.phones.variants.index', compact('model', 'variants'));
    }

    public function create(PhoneModel $model): View
    {
        $specs = PhoneSpec::with('values')->where('is_active', true)->orderBy('position')->get();

        return view('admin.phones.variants.create', compact('model', 'specs'));
    }

    public function store(Request $request, PhoneModel $model): RedirectResponse
    {
        $specs = PhoneSpec::where('is_active', true)->orderBy('position')->get();
        $rules = [
            'min_price_cny' => 'required|numeric|min:0',
            'max_price_cny' => 'required|numeric|gte:min_price_cny',
        ];
        foreach ($specs as $spec) {
            $rules['spec_value_' . $spec->id] = 'required|exists:phone_spec_values,id';
        }
        $data = $request->validate($rules);

        $specValueIds = [];
        foreach ($specs as $spec) {
            $specValueIds[] = (int) $data['spec_value_' . $spec->id];
        }
        sort($specValueIds);

        // Uniqueness: no other variant of this model has the same combination
        $existing = $model->variants()->get()->filter(function (PhoneVariant $v) use ($specValueIds) {
            $ids = $v->specValues->pluck('id')->sort()->values()->all();
            return $ids === $specValueIds;
        });
        if ($existing->isNotEmpty()) {
            throw ValidationException::withMessages([
                'spec_value_' . $specs->first()->id => 'This combination already exists for this model.',
            ]);
        }

        DB::transaction(function () use ($model, $request, $specValueIds) {
            $variant = PhoneVariant::create([
                'phone_model_id' => $model->id,
                'min_price_cny' => $request->input('min_price_cny'),
                'max_price_cny' => $request->input('max_price_cny'),
                'is_active' => true,
            ]);
            $variant->specValues()->sync($specValueIds);
        });

        return redirect()->route('admin.phones.variants.index', $model)
            ->with('success', 'Variant created.');
    }

    public function destroy(PhoneVariant $variant): RedirectResponse
    {
        $model = $variant->model;
        $variant->delete();

        return redirect()->route('admin.phones.variants.index', $model)
            ->with('success', 'Variant deleted.');
    }
}
