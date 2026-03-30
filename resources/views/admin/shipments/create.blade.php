<x-admin-layout>
    <div class="max-w-2xl mx-auto p-4 sm:p-6">
        <div class="mb-6">
            <a href="{{ route('admin.shipments.index') }}" class="text-green-600 hover:text-green-700 text-sm font-medium">← Shipments</a>
            <h2 class="text-xl sm:text-2xl font-bold text-gray-900 mt-2">Add Shipment</h2>
        </div>

        <form method="POST" action="{{ route('admin.shipments.store') }}" class="bg-white rounded-xl border border-gray-200 p-4 sm:p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Chinese Tracking Code *</label>
                <input type="text" name="chinese_tracking_code" required
                       class="w-full rounded-lg border border-gray-300 px-4 py-3 text-base focus:ring-2 focus:ring-green-500 focus:border-green-500"
                       placeholder="Enter code from logistics provider">
                @error('chinese_tracking_code')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Logistics Company *</label>
                <select name="logistics_company" required
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 text-base focus:ring-2 focus:ring-green-500 focus:border-green-500 min-h-[48px]">
                    <option value="">Select logistics company</option>
                    <option value="skycargo" {{ old('logistics_company') === 'skycargo' ? 'selected' : '' }}>SkyCargo</option>
                    <option value="fish-logistics" {{ old('logistics_company') === 'fish-logistics' ? 'selected' : '' }}>Fish Logistics</option>
                    <option value="other" {{ old('logistics_company') === 'other' ? 'selected' : '' }}>Other</option>
                </select>
                @error('logistics_company')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Current Stage</label>
                <select name="current_stage_id" class="w-full rounded-lg border border-gray-300 px-4 py-3 text-base focus:ring-2 focus:ring-green-500 focus:border-green-500 min-h-[48px]">
                    <option value="">Select stage</option>
                    @foreach($stages as $stage)
                        <option value="{{ $stage->id }}">{{ $stage->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="pt-4">
                <button type="submit" class="w-full sm:w-auto min-h-[48px] px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition">
                    Create Shipment
                </button>
            </div>
        </form>
    </div>
</x-admin-layout>
