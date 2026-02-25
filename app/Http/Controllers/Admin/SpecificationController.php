<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\SpecGroup;
use App\Models\Specification;
use App\Models\SpecValue;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SpecificationController extends Controller
{
    public function index(Request $request): View
    {
        $categories = Category::orderBy('position')->orderBy('name')->get();
        $selectedCategoryId = $request->integer('category_id');

        $groupsQuery = SpecGroup::with(['specs.values'])
            ->orderBy('id', 'desc');

        if ($selectedCategoryId) {
            $groupsQuery->where('category_id', $selectedCategoryId);
        }

        $groups = $groupsQuery->get();

        return view('admin.specs.index', compact('categories', 'groups', 'selectedCategoryId'));
    }

    public function storeGroup(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        SpecGroup::create([
            'category_id' => $data['category_id'],
            'name' => $data['name'],
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);

        return back()->with('success', 'Spec group created.');
    }

    public function storeSpec(Request $request, SpecGroup $group): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'input_type' => ['required', Rule::in(['dropdown', 'text', 'number'])],
            'is_required' => ['nullable', 'boolean'],
            'position' => ['nullable', 'integer', 'min:0'],
        ]);

        Specification::create([
            'spec_group_id' => $group->id,
            'name' => $data['name'],
            'input_type' => $data['input_type'],
            'is_required' => (bool) ($data['is_required'] ?? false),
            'position' => (int) ($data['position'] ?? 0),
        ]);

        return back()->with('success', 'Specification created.');
    }

    public function storeValue(Request $request, Specification $spec): RedirectResponse
    {
        $data = $request->validate([
            'value' => ['required', 'string', 'max:255'],
            'position' => ['nullable', 'integer', 'min:0'],
        ]);

        SpecValue::create([
            'spec_id' => $spec->id,
            'value' => $data['value'],
            'position' => (int) ($data['position'] ?? 0),
        ]);

        return back()->with('success', 'Spec value created.');
    }

    public function toggleGroup(SpecGroup $group): RedirectResponse
    {
        $group->update(['is_active' => !$group->is_active]);

        return back()->with('success', 'Group status updated.');
    }
}
