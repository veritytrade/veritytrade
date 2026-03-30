<x-admin-layout>
    <div class="max-w-2xl mx-auto p-4 sm:p-6">
        <div class="mb-6">
            <a href="{{ route('admin.shipments.show', $shipment) }}" class="text-green-600 hover:text-green-700 text-sm font-medium">← Back</a>
            <h2 class="text-xl sm:text-2xl font-bold text-gray-900 mt-2">Edit Shipment</h2>
        </div>

        <form method="POST" action="{{ route('admin.shipments.update', $shipment) }}" class="bg-white rounded-xl border border-gray-200 p-4 sm:p-6 space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Chinese Tracking Code *</label>
                <input type="text" name="chinese_tracking_code" required value="{{ old('chinese_tracking_code', $shipment->chinese_tracking_code) }}"
                       class="w-full rounded-lg border border-gray-300 px-4 py-3 text-base">
                @error('chinese_tracking_code')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Logistics Company *</label>
                <select name="logistics_company" required
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 text-base min-h-[48px]">
                    <option value="skycargo" {{ old('logistics_company', $shipment->logistics_company) === 'skycargo' ? 'selected' : '' }}>SkyCargo</option>
                    <option value="fish-logistics" {{ old('logistics_company', $shipment->logistics_company) === 'fish-logistics' ? 'selected' : '' }}>Fish Logistics</option>
                    <option value="other" {{ old('logistics_company', $shipment->logistics_company) === 'other' ? 'selected' : '' }}>Other</option>
                </select>
                @error('logistics_company')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full rounded-lg border border-gray-300 px-4 py-3 text-base min-h-[48px]">
                    <option value="active" {{ $shipment->status === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="completed" {{ $shipment->status === 'completed' ? 'selected' : '' }}>Completed</option>
                </select>
            </div>
            <div class="pt-4">
                <button type="submit" class="w-full sm:w-auto min-h-[48px] px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</x-admin-layout>
