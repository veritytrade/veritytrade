<x-admin-layout>
    <div class="max-w-7xl mx-auto p-4 md:p-6">
        
        {{-- Header (Horizontal Layout) --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6 mb-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                {{-- Left: Title --}}
                <div class="flex-1">
                    <h2 class="text-xl md:text-2xl font-bold text-blue-700">
                        Pricing Engine
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">Manage pricing combinations</p>
                </div>
            </div>
        </div>

        {{-- Flash Messages --}}
        @if(session('success'))
            <div x-data="{ show: true }" 
                x-init="setTimeout(() => show = false, 3000)"
                x-show="show"
                class="fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 transition-opacity">
                {{ session('success') }}
            </div>
        @endif

        {{-- Warning if No Brand Enabled --}}
        @if($brands->isEmpty())
            <div class="bg-yellow-50 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-6 rounded">
                <p class="font-medium">⚠️ No active brand has Structural Pricing enabled.</p>
                <p class="text-sm mt-1">Enable it inside Brand Management first.</p>
            </div>
        @endif

        {{-- Filter Section (Horizontal) --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6 mb-6">
            <h3 class="font-semibold text-gray-700 mb-4">Filter Combinations</h3>
            <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <select name="brand" onchange="this.form.submit()"
                        class="border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                    <option value="">All Brands</option>
                    @foreach($brands as $brand)
                        <option value="{{ $brand->id }}"
                            {{ request('brand') == $brand->id ? 'selected' : '' }}>
                            {{ $brand->name }}
                        </option>
                    @endforeach
                </select>

                <select name="series" onchange="this.form.submit()"
                        class="border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                    <option value="">All Series</option>
                    @foreach($series as $s)
                        <option value="{{ $s->id }}"
                            {{ request('series') == $s->id ? 'selected' : '' }}>
                            {{ $s->name }}
                        </option>
                    @endforeach
                </select>

                <select name="model" onchange="this.form.submit()"
                        class="border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                    <option value="">All Models</option>
                    @foreach($models as $m)
                        <option value="{{ $m->id }}"
                            {{ request('model') == $m->id ? 'selected' : '' }}>
                            {{ $m->name }}
                        </option>
                    @endforeach
                </select>
            </form>
            {{-- Show active filters --}}
            @if(request('brand') || request('series') || request('model'))
                <div class="mt-4 flex items-center gap-2">
                    <span class="text-sm text-gray-500">Active filters:</span>
                    <a href="{{ route('admin.pricing.index') }}"
                       class="text-xs bg-red-100 text-red-600 px-3 py-1 rounded-full hover:bg-red-200 transition">
                        Clear All ✕
                    </a>
                </div>
            @endif
        </div>

        {{-- Create Price Rule (Horizontal) --}}
        @if(request('model'))
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6 mb-6">
            <h3 class="font-semibold text-gray-700 mb-4">
                Add Price Combination
                <span class="text-sm text-gray-500 font-normal">
                    (for {{ $models->firstWhere('id', request('model'))->name }})
                </span>
            </h3>

            <form method="POST" action="{{ route('admin.pricing.store') }}">
                @csrf
                <input type="hidden" name="model_id" value="{{ request('model') }}">

                <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-4">
                    <select name="memory_id"
                            class="border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                            required>
                        <option value="">Memory</option>
                        @foreach($memories as $memory)
                            <option value="{{ $memory->id }}">{{ $memory->size_gb }}GB</option>
                        @endforeach
                    </select>

                    <select name="functionality_grade_id"
                            class="border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                            required>
                        <option value="">Function</option>
                        @foreach($functionalities as $f)
                            <option value="{{ $f->id }}">{{ $f->grade }}</option>
                        @endforeach
                    </select>

                    <select name="appearance_grade_id"
                            class="border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                            required>
                        <option value="">Appearance</option>
                        @foreach($appearances as $a)
                            <option value="{{ $a->id }}">{{ $a->percentage }}%</option>
                        @endforeach
                    </select>

                    <input type="number" step="0.01"
                           name="min_price_cny"
                           placeholder="Min ¥"
                           class="border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                           required>

                    <input type="number" step="0.01"
                           name="max_price_cny"
                           placeholder="Max ¥"
                           class="border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                           required>
                </div>

                <button type="submit"
                        class="bg-green-600 hover:bg-green-700 text-white px-6 py-2.5 rounded-lg font-medium transition shadow-sm">
                    Add Combination
                </button>
            </form>
        </div>
        @endif

        {{-- All Price Rules Table (Shows by Default) --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-blue-600 text-white">
                        <tr>
                            <th class="p-3 md:p-4 text-left font-semibold">Model</th>
                            <th class="p-3 md:p-4 text-left font-semibold">Memory</th>
                            <th class="p-3 md:p-4 text-left font-semibold">Function</th>
                            <th class="p-3 md:p-4 text-left font-semibold">Appearance</th>
                            <th class="p-3 md:p-4 text-left font-semibold">CNY Range</th>
                            <th class="p-3 md:p-4 text-left font-semibold">NGN Range</th>
                            <th class="p-3 md:p-4 text-center font-semibold">Active</th>
                            <th class="p-3 md:p-4 text-center font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($priceRules as $rule)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="p-3 md:p-4 font-medium text-gray-800">
                                {{ $rule->model->name ?? 'N/A' }}
                            </td>
                            <td class="p-3 md:p-4 text-gray-600">{{ $rule->memory->size_gb }}GB</td>
                            <td class="p-3 md:p-4 text-gray-600">{{ $rule->functionalityGrade->grade }}</td>
                            <td class="p-3 md:p-4 text-gray-600">{{ $rule->appearanceGrade->percentage }}%</td>
                            <td class="p-3 md:p-4 text-gray-600">
                                ¥{{ $rule->min_price_cny }} - ¥{{ $rule->max_price_cny }}
                            </td>
                            <td class="p-3 md:p-4">
                                @if($rule->min_price_ngn && $rule->max_price_ngn)
                                    <div class="text-green-600 font-semibold">
                                        ₦{{ number_format($rule->min_price_ngn) }} - ₦{{ number_format($rule->max_price_ngn) }}
                                    </div>
                                @else
                                    <div class="text-red-500 text-xs">
                                        No active pricing profile
                                    </div>
                                @endif
                            </td>
                            <td class="p-3 md:p-4 text-center">
                                <form method="POST" action="{{ route('admin.pricing.toggle', $rule) }}">
                                    @csrf
                                    <button type="submit"
                                        class="{{ $rule->is_active ? 'bg-green-600' : 'bg-gray-400' }} 
                                               text-white px-3 md:px-4 py-1.5 rounded-full text-xs font-medium transition hover:opacity-90">
                                        {{ $rule->is_active ? 'YES' : 'NO' }}
                                    </button>
                                </form>
                            </td>
                            <td class="p-3 md:p-4 text-center">
                                <form method="POST"
                                      action="{{ route('admin.pricing.destroy', $rule) }}"
                                      onsubmit="return confirm('Delete this combination?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="bg-red-600 hover:bg-red-700 text-white px-3 md:px-4 py-1.5 rounded-lg text-xs font-medium transition">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="p-6 md:p-8 text-center text-gray-500">
                                No pricing combinations created yet. Select a model above to create your first combination.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-admin-layout>