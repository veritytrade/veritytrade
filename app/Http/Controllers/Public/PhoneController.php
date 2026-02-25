<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\AppearanceGrade;
use App\Models\Brand;
use App\Models\Category;
use App\Models\CustomerRequest;
use App\Models\Device;
use App\Models\FunctionalityGrade;
use App\Models\Memory;
use App\Models\PriceRule;
use App\Models\PricingSetting;
use App\Models\SpecGroup;
use App\Models\Series;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PhoneController extends Controller
{
    private function isPhoneBrand(Brand $brand): bool
    {
        $brand->loadMissing('category');
        $categoryName = Str::lower((string) optional($brand->category)->name);

        return Str::contains($categoryName, 'phone');
    }

    public function index()
    {
        $categories = Category::where('is_active', true)
            ->orderBy('position')
            ->get();

        $phoneCategory = Category::where('is_active', true)
            ->get()
            ->first(fn ($category) => Str::contains(Str::lower((string) $category->name), 'phone'));

        $brands = Brand::query()
            ->when($phoneCategory, function ($query) use ($phoneCategory) {
                $query->where('category_id', $phoneCategory->id);
            }, function ($query) {
                $query->whereHas('category', function ($categoryQuery) {
                    $categoryQuery->where('is_active', true)
                        ->where('name', 'like', '%Phone%');
                });
            })
            ->where('is_active', true)
            ->where('uses_pricing_engine', true)
            ->orderBy('position')
            ->orderBy('name')
            ->get();

        return view('public.phones.brands', compact('brands', 'categories'));
    }

    public function brand(string $brandUuid): View|RedirectResponse
    {
        $brand = Brand::where('uuid', $brandUuid)->firstOrFail();

        if (!$brand->is_active || !$this->isPhoneBrand($brand)) {
            throw new NotFoundHttpException();
        }

        $pricingSetting = PricingSetting::where('brand_id', $brand->id)
            ->where('is_active', true)
            ->first();

        $series = Series::with(['devices' => function ($query) {
                $query->where('is_active', true)
                    ->orderBy('position')
                    ->orderBy('name');
            }])
            ->where('brand_id', $brand->id)
            ->where('is_active', true)
            ->orderBy('position')
            ->orderBy('name')
            ->get();

        if ($series->isNotEmpty()) {
            $deviceIds = $series->flatMap(fn ($item) => $item->devices->pluck('id'))
                ->unique()
                ->values();

            $ruleOptions = $this->buildRuleOptionsByDevice($deviceIds, $pricingSetting);
            return view('public.phones.series', compact('brand', 'series', 'ruleOptions'));
        }

        $devices = Device::where('brand_id', $brand->id)
            ->whereNull('series_id')
            ->where('is_active', true)
            ->orderBy('position')
            ->orderBy('name')
            ->get();

        $ruleOptions = $this->buildRuleOptionsByDevice($devices->pluck('id'), $pricingSetting);

        if ($devices->isNotEmpty()) {
            return view('public.phones.devices', compact('brand', 'devices', 'ruleOptions'));
        }

        return $this->renderPhoneRequestView($brand);
    }

    public function device(string $brandUuid, string $deviceUuid)
    {
        $brand = Brand::where('uuid', $brandUuid)->firstOrFail();
        $device = Device::where('uuid', $deviceUuid)->firstOrFail();

        if (!$brand->is_active || !$this->isPhoneBrand($brand)) {
            throw new NotFoundHttpException();
        }

        if (
            !$device->is_active ||
            (int) $device->brand_id !== (int) $brand->id
        ) {
            throw new NotFoundHttpException();
        }

        $memories = Memory::whereHas('priceRules', function ($query) use ($device) {
                $query->where('model_id', $device->id)->where('is_active', true);
            })
            ->where('is_active', true)
            ->orderBy('size_gb')
            ->get();

        $functionalities = FunctionalityGrade::where('is_active', true)->get();
        $appearances = AppearanceGrade::where('is_active', true)->get();
        $pricingSetting = PricingSetting::where('brand_id', $brand->id)
            ->where('is_active', true)
            ->first();
        $ruleOptions = $this->buildRuleOptionsByDevice(collect([$device->id]), $pricingSetting);
        $deviceRuleOptions = $ruleOptions[$device->id] ?? [];

        if (empty($deviceRuleOptions)) {
            return $this->renderPhoneRequestView($brand);
        }

        return view('public.phones.device', compact(
            'brand',
            'device',
            'memories',
            'functionalities',
            'appearances',
            'deviceRuleOptions'
        ));
    }

    public function request(Request $request, string $brandUuid): RedirectResponse
    {
        $brand = Brand::where('uuid', $brandUuid)->firstOrFail();
        $category = $brand->category;

        if (!$brand->is_active || !$this->isPhoneBrand($brand)) {
            throw new NotFoundHttpException();
        }

        $validated = $request->validate([
            'manual_model_name' => 'required|string|max:255',
            'memory_id' => 'required|exists:memories,id',
            'functionality_grade_id' => 'required|exists:functionality_grades,id',
            'appearance_grade_id' => 'required|exists:appearance_grades,id',
            'phone_number' => 'required|string|max:30',
            'request_specs' => 'nullable|array',
        ]);

        $memory = Memory::find($validated['memory_id']);
        $func = FunctionalityGrade::find($validated['functionality_grade_id']);
        $appearance = AppearanceGrade::find($validated['appearance_grade_id']);
        $extraSpecs = $this->extractRequestSpecsFromPayload($request, $category?->id);

        CustomerRequest::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => auth()->id(),
            'category_id' => $brand->category_id,
            'brand_id' => $brand->id,
            'manual_model_name' => $validated['manual_model_name'],
            'memory_id' => $validated['memory_id'],
            'functionality_grade_id' => $validated['functionality_grade_id'],
            'appearance_grade_id' => $validated['appearance_grade_id'],
            'request_spec_json' => $extraSpecs,
            'phone_number' => $validated['phone_number'],
            'status' => 'pending',
        ]);

        $message = "Hello, I want to request a phone:\n\n";
        $message .= "Brand: {$brand->name}\n";
        $message .= "Model: {$validated['manual_model_name']}\n";
        $message .= "Memory: " . ($memory?->size_gb ? $memory->size_gb . "GB" : 'N/A') . "\n";
        $message .= "Function: " . ($func?->grade ? $func->grade . " Grade" : 'N/A') . "\n";
        $message .= "Appearance: " . ($appearance?->percentage ? $appearance->percentage . "%" : 'N/A') . "\n";
        foreach ($extraSpecs as $label => $value) {
            $message .= "{$label}: {$value}\n";
        }
        $message .= "Contact: {$validated['phone_number']}\n";

        $whatsappNumber = site_setting('whatsapp_number', site_setting('whatsapp_business_number', '2347084117779'));

        return redirect()->away("https://wa.me/{$whatsappNumber}?text=" . urlencode($message));
    }

    public function requestForm(string $brandUuid): View
    {
        $brand = Brand::where('uuid', $brandUuid)->firstOrFail();
        $category = $brand->category;

        if (!$brand->is_active || !$this->isPhoneBrand($brand)) {
            throw new NotFoundHttpException();
        }

        return $this->renderPhoneRequestView($brand);
    }

    public function whatsapp(Request $request, string $deviceUuid)
    {
        $device = Device::where('uuid', $deviceUuid)->firstOrFail();

        if (!$device->is_active) {
            throw new NotFoundHttpException();
        }

        $validated = $request->validate([
            'memory_id' => 'required|exists:memories,id',
            'functionality_grade_id' => 'required|exists:functionality_grades,id',
            'appearance_grade_id' => 'required|exists:appearance_grades,id',
        ]);

        $memory = Memory::findOrFail($validated['memory_id']);
        $funcGrade = FunctionalityGrade::findOrFail($validated['functionality_grade_id']);
        $appGrade = AppearanceGrade::findOrFail($validated['appearance_grade_id']);

        $priceRule = PriceRule::where('model_id', $device->id)
            ->where('memory_id', $validated['memory_id'])
            ->where('functionality_grade_id', $validated['functionality_grade_id'])
            ->where('appearance_grade_id', $validated['appearance_grade_id'])
            ->where('is_active', true)
            ->first();

        if (!$priceRule) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Combination unavailable',
                ], 422);
            }

            return back()->with('error', 'Combination unavailable');
        }

        $message = "Hello, I want to buy:\n\n";
        $message .= "Device: {$device->name}\n";
        $message .= "Memory: {$memory->size_gb}GB\n";
        $message .= "Function: {$funcGrade->grade} Grade\n";
        $message .= "Appearance: {$appGrade->percentage}%\n";

        if ($priceRule->min_price_ngn && $priceRule->max_price_ngn) {
            $message .= "\nPrice: N" . number_format($priceRule->min_price_ngn)
                . " - N" . number_format($priceRule->max_price_ngn) . "\n";
        }

        $message .= "\nPlease confirm availability and delivery options. Thank you!";

        $whatsappNumber = site_setting('whatsapp_number', site_setting('whatsapp_business_number', '2347084117779'));

        return redirect()->away("https://wa.me/{$whatsappNumber}?text=" . urlencode($message));
    }

    private function buildRuleOptionsByDevice(Collection $deviceIds, ?PricingSetting $pricingSetting): array
    {
        if ($deviceIds->isEmpty() || !$pricingSetting) {
            return [];
        }

        $roundingUnit = max(1, (int) ($pricingSetting?->price_rounding_unit ?? 10000));
        $exchangeRate = (float) ($pricingSetting?->exchange_rate ?? 0);
        $logistics = (float) ($pricingSetting?->logistics_cost_cny ?? 0);
        $margin = (float) ($pricingSetting?->fixed_margin_ngn ?? 0);

        $rules = PriceRule::whereIn('model_id', $deviceIds->all())
            ->where('is_active', true)
            ->with(['memory', 'functionalityGrade', 'appearanceGrade'])
            ->get()
            ->groupBy('model_id');

        $result = [];

        foreach ($rules as $modelId => $modelRules) {
            $result[$modelId] = $modelRules
                ->map(function (PriceRule $rule) use ($exchangeRate, $logistics, $margin, $roundingUnit) {
                    $minBase = (($rule->min_price_cny + $logistics) * $exchangeRate) + $margin;
                    $maxBase = (($rule->max_price_cny + $logistics) * $exchangeRate) + $margin;

                    $minNgn = (int) ceil($minBase / $roundingUnit) * $roundingUnit;
                    $maxNgn = (int) ceil($maxBase / $roundingUnit) * $roundingUnit;

                    return [
                        'memory_id' => $rule->memory_id,
                        'memory_label' => $rule->memory?->size_gb ? $rule->memory->size_gb . 'GB' : 'Unknown',
                        'functionality_grade_id' => $rule->functionality_grade_id,
                        'functionality_label' => $rule->functionalityGrade?->grade ? $rule->functionalityGrade->grade . ' Grade' : 'Unknown',
                        'appearance_grade_id' => $rule->appearance_grade_id,
                        'appearance_label' => $rule->appearanceGrade?->percentage ? $rule->appearanceGrade->percentage . '%' : 'Unknown',
                        'min_price_ngn' => $minNgn,
                        'max_price_ngn' => $maxNgn,
                    ];
                })
                ->values()
                ->all();
        }

        return $result;
    }

    private function getCategorySpecGroups(?int $categoryId)
    {
        if (!$categoryId) {
            return collect();
        }

        return SpecGroup::with(['specs.values'])
            ->where('category_id', $categoryId)
            ->where('is_active', true)
            ->orderBy('id')
            ->get();
    }

    private function extractRequestSpecsFromPayload(Request $request, ?int $categoryId): array
    {
        if (!$categoryId) {
            return [];
        }

        $payload = $request->input('request_specs', []);
        if (!is_array($payload)) {
            return [];
        }

        $groups = $this->getCategorySpecGroups($categoryId);
        $resolved = [];

        foreach ($groups as $group) {
            foreach ($group->specs as $spec) {
                $raw = $payload[$spec->id] ?? null;
                if ($raw === null || $raw === '') {
                    continue;
                }

                if ($spec->input_type === 'dropdown') {
                    $value = $spec->values->firstWhere('id', (int) $raw);
                    if ($value) {
                        $resolved[$spec->name] = $value->value;
                    }
                } else {
                    $resolved[$spec->name] = (string) $raw;
                }
            }
        }

        return $resolved;
    }

    private function renderPhoneRequestView(Brand $brand): View
    {
        $category = $brand->category;
        $memories = Memory::where('is_active', true)->orderBy('size_gb')->get();
        $functionalities = FunctionalityGrade::where('is_active', true)->orderBy('grade')->get();
        $appearances = AppearanceGrade::where('is_active', true)->orderByDesc('percentage')->get();
        $specGroups = $this->getCategorySpecGroups($category?->id);

        return view('public.phones.request', compact('brand', 'memories', 'functionalities', 'appearances', 'specGroups'));
    }
}
