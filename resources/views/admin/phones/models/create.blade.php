<x-admin-layout>
    <div class="max-w-4xl mx-auto p-4 md:p-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6 mb-6">
            <h2 class="text-xl font-bold text-green-800 mb-4">Add Model under {{ $brand->name }}</h2>
            <p class="text-sm text-gray-500 mb-6">Enter model details, then add price combinations by selecting Storage, Appearance, and Function from the dropdowns and entering min/max (CNY). Leave a row empty to skip.</p>

            <form action="{{ route('admin.phones.models.store', $brand) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Name *</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required
                               class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm py-2 px-3 focus:border-green-500 focus:ring-green-500">
                        @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="image" class="block text-sm font-medium text-gray-700">Primary image (optional)</label>
                        <input type="file" name="image" id="image" accept="image/jpeg,image/png,image/jpg,image/webp"
                               class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-gray-100 file:text-gray-700">
                        @error('image')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">More images (optional)</label>
                    <p class="text-xs text-gray-500 mb-2">Upload additional images. Each will be shown in the model gallery.</p>
                    <input type="file" name="images[]" id="images" accept="image/jpeg,image/png,image/jpg,image/webp" multiple
                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-gray-100 file:text-gray-700">
                    @error('images.*')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div class="flex items-center">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                           class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                    <label for="is_active" class="ml-2 text-sm text-gray-700">Active</label>
                </div>

                <hr class="border-gray-200">

                <div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Price combinations (CNY)</h3>
                    <p class="text-sm text-gray-500 mb-3">Select one combination per row and set min/max price. Up to {{ $variantRows }} rows; leave dropdowns empty to skip.</p>
                    <div class="space-y-3">
                        @for($i = 0; $i < $variantRows; $i++)
                            <div class="flex flex-wrap items-center gap-2 sm:gap-3 p-3 rounded-lg bg-gray-50 border border-gray-100">
                                <select name="variants[{{ $i }}][storage_id]" class="rounded border border-gray-300 py-2 px-3 text-sm focus:border-green-500 focus:ring-green-500 min-w-[100px]">
                                    <option value="">Storage</option>
                                    @foreach($storageValues as $v)
                                        <option value="{{ $v->id }}" {{ (string)old('variants.'.$i.'.storage_id') === (string)$v->id ? 'selected' : '' }}>{{ $v->value }}</option>
                                    @endforeach
                                </select>
                                <select name="variants[{{ $i }}][appearance_id]" class="rounded border border-gray-300 py-2 px-3 text-sm focus:border-green-500 focus:ring-green-500 min-w-[90px]">
                                    <option value="">Appearance</option>
                                    @foreach($appearanceValues as $v)
                                        <option value="{{ $v->id }}" {{ (string)old('variants.'.$i.'.appearance_id') === (string)$v->id ? 'selected' : '' }}>{{ $v->value }}</option>
                                    @endforeach
                                </select>
                                <select name="variants[{{ $i }}][function_id]" class="rounded border border-gray-300 py-2 px-3 text-sm focus:border-green-500 focus:ring-green-500 min-w-[80px]">
                                    <option value="">Function</option>
                                    @foreach($functionValues as $v)
                                        <option value="{{ $v->id }}" {{ (string)old('variants.'.$i.'.function_id') === (string)$v->id ? 'selected' : '' }}>{{ $v->value }}</option>
                                    @endforeach
                                </select>
                                <input type="number" step="0.01" min="0" name="variants[{{ $i }}][min]" value="{{ old('variants.'.$i.'.min') }}" placeholder="Min"
                                       class="rounded border border-gray-300 py-2 px-3 text-sm w-24 focus:border-green-500 focus:ring-green-500">
                                <input type="number" step="0.01" min="0" name="variants[{{ $i }}][max]" value="{{ old('variants.'.$i.'.max') }}" placeholder="Max"
                                       class="rounded border border-gray-300 py-2 px-3 text-sm w-24 focus:border-green-500 focus:ring-green-500">
                            </div>
                        @endfor
                    </div>
                    @if($storageValues->isEmpty() || $appearanceValues->isEmpty() || $functionValues->isEmpty())
                        <p class="mt-2 text-sm text-amber-600">No specs (Storage / Appearance / Function) found. Run the PhoneSpec seeder first.</p>
                    @endif
                </div>

                <div class="flex flex-wrap gap-3">
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2.5 rounded-lg font-medium">Create model & variants</button>
                    <a href="{{ route('admin.phones.models.index', $brand) }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-2.5 rounded-lg font-medium">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
