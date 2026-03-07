<x-admin-layout>
    <div class="max-w-2xl mx-auto p-4 md:p-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6 mb-6">
            <h2 class="text-xl font-bold text-green-800 mb-4">Add Variant: {{ $model->name }}</h2>
            <p class="text-sm text-gray-500 mb-4">Select one value per spec and enter price range in CNY. Combination must be unique for this model.</p>
            <form action="{{ route('admin.phones.variants.store', $model) }}" method="POST" class="space-y-4">
                @csrf
                @foreach($specs as $spec)
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ $spec->name }} *</label>
                        <select name="spec_value_{{ $spec->id }}" required class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm py-2 px-3 focus:border-green-500 focus:ring-green-500">
                            <option value="">Select {{ $spec->name }}</option>
                            @foreach($spec->values as $val)
                                <option value="{{ $val->id }}" {{ old('spec_value_'.$spec->id) == $val->id ? 'selected' : '' }}>{{ $val->value }}</option>
                            @endforeach
                        </select>
                        @error('spec_value_'.$spec->id)<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                @endforeach
                <div>
                    <label for="min_price_cny" class="block text-sm font-medium text-gray-700">Min price (CNY) *</label>
                    <input type="number" name="min_price_cny" id="min_price_cny" value="{{ old('min_price_cny') }}" step="0.01" min="0" required class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm py-2 px-3">
                    @error('min_price_cny')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="max_price_cny" class="block text-sm font-medium text-gray-700">Max price (CNY) *</label>
                    <input type="number" name="max_price_cny" id="max_price_cny" value="{{ old('max_price_cny') }}" step="0.01" min="0" required class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm py-2 px-3">
                    @error('max_price_cny')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-medium">Create</button>
                    <a href="{{ route('admin.phones.variants.index', $model) }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-2 rounded-lg font-medium">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
