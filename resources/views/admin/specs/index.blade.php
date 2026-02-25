<x-layouts.admin>
    <div class="max-w-7xl mx-auto p-4 md:p-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6 mb-6">
            <h2 class="text-xl md:text-2xl font-bold text-blue-700">Category Specifications</h2>
            <p class="text-sm text-gray-500 mt-1">Create specs for phones, laptops, tabs, and games.</p>
        </div>

        @if(session('success'))
            <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-2 text-sm text-green-700">{{ session('success') }}</div>
        @endif

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6 mb-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <select name="category_id" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ (int) $selectedCategoryId === (int) $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white rounded-lg px-4 py-2 text-sm">Filter</button>
            </form>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6 mb-6">
            <h3 class="font-semibold text-gray-800 mb-3">Create Spec Group</h3>
            <form method="POST" action="{{ route('admin.specs.groups.store') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3">
                @csrf
                <select name="category_id" required class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    <option value="">Select Category</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ (int) $selectedCategoryId === (int) $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                    @endforeach
                </select>
                <input type="text" name="name" required class="border border-gray-300 rounded-lg px-3 py-2 text-sm" placeholder="e.g. Laptop Spec Group">
                <label class="inline-flex items-center text-sm text-gray-700"><input type="checkbox" name="is_active" value="1" checked class="mr-2">Active</label>
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white rounded-lg px-4 py-2 text-sm">Create Group</button>
            </form>
        </div>

        <div class="space-y-6">
            @forelse($groups as $group)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
                        <div>
                            <h3 class="text-lg font-bold text-gray-800">{{ $group->name }}</h3>
                            <p class="text-sm text-gray-500">Category: {{ optional($group->category)->name }}</p>
                        </div>
                        <form method="POST" action="{{ route('admin.specs.groups.toggle', $group) }}">
                            @csrf
                            <button type="submit" class="px-3 py-1.5 rounded text-white text-xs {{ $group->is_active ? 'bg-green-600' : 'bg-gray-500' }}">{{ $group->is_active ? 'ACTIVE' : 'INACTIVE' }}</button>
                        </form>
                    </div>

                    <div class="bg-gray-50 rounded-lg border border-gray-200 p-3 mb-4">
                        <form method="POST" action="{{ route('admin.specs.store', $group) }}" class="grid grid-cols-1 md:grid-cols-5 gap-3">
                            @csrf
                            <input type="text" name="name" required placeholder="Spec name (RAM, CPU...)" class="border border-gray-300 rounded px-3 py-2 text-sm">
                            <select name="input_type" class="border border-gray-300 rounded px-3 py-2 text-sm">
                                <option value="dropdown">dropdown</option>
                                <option value="text">text</option>
                                <option value="number">number</option>
                            </select>
                            <input type="number" name="position" min="0" value="0" class="border border-gray-300 rounded px-3 py-2 text-sm">
                            <label class="inline-flex items-center text-sm text-gray-700"><input type="checkbox" name="is_required" value="1" class="mr-2">Required</label>
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white rounded px-3 py-2 text-sm">Add Spec</button>
                        </form>
                    </div>

                    <div class="space-y-3">
                        @forelse($group->specs as $spec)
                            <div class="border border-gray-200 rounded-lg p-3">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="font-semibold text-gray-800">{{ $spec->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $spec->input_type }} | {{ $spec->is_required ? 'required' : 'optional' }}</div>
                                </div>

                                <form method="POST" action="{{ route('admin.specs.values.store', $spec) }}" class="grid grid-cols-1 md:grid-cols-4 gap-2 mb-2">
                                    @csrf
                                    <input type="text" name="value" required placeholder="Add value (e.g. 16GB, i7...)" class="border border-gray-300 rounded px-3 py-2 text-sm md:col-span-2">
                                    <input type="number" name="position" min="0" value="0" class="border border-gray-300 rounded px-3 py-2 text-sm">
                                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white rounded px-3 py-2 text-sm">Add Value</button>
                                </form>

                                <div class="flex flex-wrap gap-2">
                                    @forelse($spec->values as $value)
                                        <span class="inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs text-blue-700">{{ $value->value }}</span>
                                    @empty
                                        <span class="text-xs text-gray-400">No values yet.</span>
                                    @endforelse
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-400">No specs added yet.</p>
                        @endforelse
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-lg border border-gray-200 p-6 text-center text-sm text-gray-500">No spec groups found.</div>
            @endforelse
        </div>
    </div>
</x-layouts.admin>
