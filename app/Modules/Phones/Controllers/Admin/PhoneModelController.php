<?php

namespace App\Modules\Phones\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Phones\Models\PhoneBrand;
use App\Modules\Phones\Models\PhoneModel;
use App\Modules\Phones\Models\PhoneModelImage;
use App\Modules\Phones\Models\PhoneSpec;
use App\Modules\Phones\Models\PhoneVariant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PhoneModelController extends Controller
{
    private const VARIANT_ROWS = 15;

    /** @return array{storage: \Illuminate\Support\Collection, appearance: \Illuminate\Support\Collection, function: \Illuminate\Support\Collection} */
    private function getSpecValuesForDropdowns(): array
    {
        $specs = PhoneSpec::with('values')
            ->where('is_active', true)
            ->orderBy('position')
            ->get()
            ->keyBy('name');

        $storage = $specs->get('Storage');
        $appearance = $specs->get('Appearance');
        $function = $specs->get('Function');

        return [
            'storage' => $storage ? $storage->values->where('is_active', true)->sortBy('position')->values() : collect(),
            'appearance' => $appearance ? $appearance->values->where('is_active', true)->sortBy('position')->values() : collect(),
            'function' => $function ? $function->values->where('is_active', true)->sortBy('position')->values() : collect(),
        ];
    }

    public function index(PhoneBrand $brand): View
    {
        $models = $brand->models()->orderBy('name')->get();

        return view('admin.phones.models.index', compact('brand', 'models'));
    }

    public function create(PhoneBrand $brand): View
    {
        $specs = $this->getSpecValuesForDropdowns();

        return view('admin.phones.models.create', [
            'brand' => $brand,
            'storageValues' => $specs['storage'],
            'appearanceValues' => $specs['appearance'],
            'functionValues' => $specs['function'],
            'variantRows' => self::VARIANT_ROWS,
        ]);
    }

    public function store(Request $request, PhoneBrand $brand): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:2048',
            'is_active' => 'nullable|boolean',
            'variants' => 'nullable|array',
            'variants.*.min' => 'nullable|numeric|min:0',
            'variants.*.max' => 'nullable|numeric|min:0',
        ]);
        $data['phone_brand_id'] = $brand->id;
        $data['is_active'] = $request->boolean('is_active', true);

        $firstImagePath = null;
        if ($request->hasFile('image')) {
            $firstImagePath = $request->file('image')->store('phones/models', 'public');
        }
        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                if ($file->isValid()) {
                    $imagePaths[] = $file->store('phones/models', 'public');
                }
            }
        }
        if ($firstImagePath !== null) {
            array_unshift($imagePaths, $firstImagePath);
        }
        $primaryImage = $imagePaths[0] ?? null;

        $model = PhoneModel::create([
            'phone_brand_id' => $data['phone_brand_id'],
            'name' => $data['name'],
            'image' => $primaryImage,
            'is_active' => $data['is_active'],
        ]);

        foreach ($imagePaths as $sortOrder => $path) {
            $model->images()->create([
                'path' => $path,
                'sort_order' => $sortOrder,
            ]);
        }

        $this->syncVariantsFromRequest($model, $request->input('variants', []));

        return redirect()->route('admin.phones.models.index', $brand)
            ->with('success', 'Model and variants saved.');
    }

    public function edit(PhoneModel $model): View
    {
        $brand = $model->brand;
        $specs = $this->getSpecValuesForDropdowns();

        $existingRows = [];
        foreach ($model->variants()->with('specValues.spec')->get() as $v) {
            $ids = ['storage_id' => null, 'appearance_id' => null, 'function_id' => null];
            foreach ($v->specValues as $sv) {
                if ($sv->spec->name === 'Storage') {
                    $ids['storage_id'] = $sv->id;
                } elseif ($sv->spec->name === 'Appearance') {
                    $ids['appearance_id'] = $sv->id;
                } elseif ($sv->spec->name === 'Function') {
                    $ids['function_id'] = $sv->id;
                }
            }
            if ($ids['storage_id'] && $ids['appearance_id'] && $ids['function_id']) {
                $existingRows[] = [
                    'storage_id' => $ids['storage_id'],
                    'appearance_id' => $ids['appearance_id'],
                    'function_id' => $ids['function_id'],
                    'min' => (float) $v->min_price_cny,
                    'max' => (float) $v->max_price_cny,
                ];
            }
        }

        return view('admin.phones.models.edit', [
            'model' => $model,
            'brand' => $brand,
            'storageValues' => $specs['storage'],
            'appearanceValues' => $specs['appearance'],
            'functionValues' => $specs['function'],
            'existingRows' => $existingRows,
            'variantRows' => self::VARIANT_ROWS,
        ]);
    }

    public function update(Request $request, PhoneModel $model): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:2048',
            'delete_images' => 'nullable|array',
            'delete_images.*' => 'exists:phone_model_images,id',
            'is_active' => 'nullable|boolean',
            'variants' => 'nullable|array',
            'variants.*.storage_id' => 'nullable|exists:phone_spec_values,id',
            'variants.*.appearance_id' => 'nullable|exists:phone_spec_values,id',
            'variants.*.function_id' => 'nullable|exists:phone_spec_values,id',
            'variants.*.min' => 'nullable|numeric|min:0',
            'variants.*.max' => 'nullable|numeric|min:0',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);

        $deleteIds = $request->input('delete_images', []);
        if (! empty($deleteIds)) {
            foreach ($model->images()->whereIn('id', $deleteIds)->get() as $img) {
                if (Storage::disk('public')->exists($img->path)) {
                    Storage::disk('public')->delete($img->path);
                }
                $img->delete();
            }
        }

        $newPaths = [];
        if ($request->hasFile('image')) {
            if ($model->image && Storage::disk('public')->exists($model->image)) {
                Storage::disk('public')->delete($model->image);
            }
            $newPaths[] = $request->file('image')->store('phones/models', 'public');
        }
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                if ($file->isValid()) {
                    $newPaths[] = $file->store('phones/models', 'public');
                }
            }
        }
        $maxOrder = (int) $model->images()->max('sort_order') ?: -1;
        foreach ($newPaths as $i => $path) {
            $model->images()->create([
                'path' => $path,
                'sort_order' => $maxOrder + 1 + $i,
            ]);
        }
        $firstImage = $model->images()->orderBy('sort_order')->first();
        $model->update([
            'name' => $data['name'],
            'is_active' => $data['is_active'],
            'image' => $firstImage?->path,
        ]);

        $this->syncVariantsFromRequest($model, $request->input('variants', []));

        return redirect()->route('admin.phones.models.index', $model->brand)
            ->with('success', 'Model and variants updated.');
    }

    private function syncVariantsFromRequest(PhoneModel $model, array $variants): void
    {
        DB::transaction(function () use ($model, $variants) {
            $model->variants()->each(fn(PhoneVariant $v) => $v->forceDelete());

            $seen = [];
            foreach ($variants as $row) {
                $s = isset($row['storage_id']) ? (int) $row['storage_id'] : null;
                $a = isset($row['appearance_id']) ? (int) $row['appearance_id'] : null;
                $f = isset($row['function_id']) ? (int) $row['function_id'] : null;
                $min = isset($row['min']) ? (float) $row['min'] : null;
                $max = isset($row['max']) ? (float) $row['max'] : null;
                if ($s === null || $a === null || $f === null || $min === null || $max === null || $max < $min) {
                    continue;
                }
                $key = "{$s}_{$a}_{$f}";
                if (isset($seen[$key])) {
                    continue;
                }
                $seen[$key] = true;
                $variant = PhoneVariant::create([
                    'phone_model_id' => $model->id,
                    'min_price_cny' => $min,
                    'max_price_cny' => $max,
                    'is_active' => true,
                ]);
                $variant->specValues()->sync([$s, $a, $f]);
            }
        });
    }

    public function destroy(PhoneModel $model): RedirectResponse
    {
        $brand = $model->brand;
        foreach ($model->images as $img) {
            if (Storage::disk('public')->exists($img->path)) {
                Storage::disk('public')->delete($img->path);
            }
        }
        if ($model->image && Storage::disk('public')->exists($model->image)) {
            Storage::disk('public')->delete($model->image);
        }
        $model->delete();

        return redirect()->route('admin.phones.models.index', $brand)
            ->with('success', 'Model deleted.');
    }
}
