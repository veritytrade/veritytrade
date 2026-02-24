<x-admin-layout>
    <div class="max-w-6xl mx-auto p-4 md:p-6">
        
        {{-- Header (Horizontal Layout - Mobile Adapted) --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6 mb-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                {{-- Left: Title --}}
                <div class="flex-1">
                    <h2 class="text-xl md:text-2xl font-bold text-blue-700">
                        Pricing Settings
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">Configure pricing rules and profiles</p>
                </div>
                
                {{-- Right: Create Form (Horizontal) --}}
                <form method="POST" action="{{ route('admin.pricing.settings.store') }}" class="flex-1">
                    @csrf
                    <div class="flex flex-wrap gap-3">
                        <select name="brand_id" 
                                class="flex-1 min-w-[150px] border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                                required>
                            <option value="">Select Brand</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                            @endforeach
                        </select>
                        <input type="number" step="0.01" name="exchange_rate" 
                               placeholder="Exchange Rate"
                               class="flex-1 min-w-[120px] border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                               required>
                        <input type="number" step="0.01" name="logistics_cost_cny" 
                               placeholder="Logistics (CNY)"
                               class="flex-1 min-w-[120px] border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                               required>
                        <input type="number" step="0.01" name="fixed_margin_ngn" 
                               placeholder="Margin (NGN)"
                               class="flex-1 min-w-[120px] border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                               required>
                        <button type="submit"
                                class="bg-green-600 hover:bg-green-700 text-white px-6 py-2.5 rounded-lg font-medium transition shadow-sm whitespace-nowrap">
                            Save
                        </button>
                    </div>
                </form>
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

        @if(session('error'))
            <div x-data="{ show: true }" 
                x-init="setTimeout(() => show = false, 5000)"
                x-show="show"
                class="fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 transition-opacity">
                {{ session('error') }}
            </div>
        @endif

        {{-- Pricing Settings Table (Mobile Adapted) --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-blue-600 text-white">
                        <tr>
                            <th class="p-3 md:p-4 text-left font-semibold">Brand</th>
                            <th class="p-3 md:p-4 text-left font-semibold">Exchange Rate</th>
                            <th class="p-3 md:p-4 text-left font-semibold">Logistics (CNY)</th>
                            <th class="p-3 md:p-4 text-left font-semibold">Fixed Margin (NGN)</th>
                            <th class="p-3 md:p-4 text-center font-semibold">Active</th>
                            <th class="p-3 md:p-4 text-center font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($settings as $setting)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="p-3 md:p-4 font-medium text-gray-800">{{ $setting->brand->name ?? 'Unknown Brand' }}</td>
                            <td class="p-3 md:p-4 text-gray-600">{{ $setting->exchange_rate }}</td>
                            <td class="p-3 md:p-4 text-gray-600">{{ $setting->logistics_cost_cny }}</td>
                            <td class="p-3 md:p-4 text-gray-600">{{ number_format($setting->fixed_margin_ngn) }}</td>
                            <td class="p-3 md:p-4 text-center">
                                @if($setting->is_active)
                                    <span class="bg-green-600 text-white px-4 py-1.5 rounded-full text-xs font-medium">
                                        ACTIVE
                                    </span>
                                @else
                                    <form method="POST" action="{{ route('admin.pricing.settings.toggle', $setting) }}" class="inline">
                                        @csrf
                                        <button type="submit"
                                            class="bg-gray-400 hover:bg-gray-500 text-white px-4 py-1.5 rounded-full text-xs font-medium transition">
                                            Activate
                                        </button>
                                    </form>
                                @endif
                            </td>
                            <td class="p-3 md:p-4 text-center">
                                <form method="POST"
                                      action="{{ route('admin.pricing.settings.destroy', $setting) }}"
                                      class="inline"
                                      onsubmit="return confirm('Delete this pricing profile?')">
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
                            <td colspan="6" class="p-6 md:p-8 text-center text-gray-500">
                                No pricing profiles created yet. Create your first profile above.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-admin-layout>