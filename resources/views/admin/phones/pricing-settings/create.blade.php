<x-admin-layout>
    <div class="max-w-2xl mx-auto p-4 md:p-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6 mb-6">
            <h2 class="text-xl font-bold text-green-800 mb-4">Add Pricing Setting</h2>
            <form action="{{ route('admin.phones.pricing-settings.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label for="exchange_rate" class="block text-sm font-medium text-gray-700">Exchange rate (e.g. 220) *</label>
                    <input type="number" name="exchange_rate" id="exchange_rate" value="{{ old('exchange_rate', '220') }}" step="0.01" min="0.01" required class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm py-2 px-3">
                    @error('exchange_rate')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="logistics_cny" class="block text-sm font-medium text-gray-700">Logistics (CNY)</label>
                    <input type="number" name="logistics_cny" id="logistics_cny" value="{{ old('logistics_cny', '250') }}" step="0.01" min="0" class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm py-2 px-3">
                    @error('logistics_cny')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="profit_margin_ngn" class="block text-sm font-medium text-gray-700">Profit margin (NGN)</label>
                    <input type="number" name="profit_margin_ngn" id="profit_margin_ngn" value="{{ old('profit_margin_ngn', '10000') }}" step="0.01" min="0" class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm py-2 px-3">
                    @error('profit_margin_ngn')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="rounding_unit" class="block text-sm font-medium text-gray-700">Rounding unit (NGN, e.g. 10000) *</label>
                    <input type="number" name="rounding_unit" id="rounding_unit" value="{{ old('rounding_unit', '10000') }}" min="1" required class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm py-2 px-3">
                    @error('rounding_unit')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div class="flex items-center">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                    <label for="is_active" class="ml-2 text-sm text-gray-700">Set as active (only one can be active)</label>
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-medium">Create</button>
                    <a href="{{ route('admin.phones.pricing-settings.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-2 rounded-lg font-medium">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
