<x-admin-layout>
    <div class="max-w-4xl mx-auto p-4 md:p-6">
        <div class="bg-white rounded-lg border border-gray-200 p-4 md:p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-gray-900">Edit Ingested Product</h2>
                <a href="{{ route('admin.products.show', $product) }}" class="text-sm text-blue-600 hover:text-blue-700">Back to Preview</a>
            </div>

            @if($errors->any())
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 text-red-700 px-4 py-3 text-sm">
                    <ul class="list-disc pl-5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                    <input type="text" name="title" value="{{ old('title', $product->title) }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Price (NGN)</label>
                        <input type="number" min="0" step="1" name="price_ngn" value="{{ old('price_ngn', (int) $product->price_ngn) }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Stock</label>
                        <input type="number" min="0" step="1" name="stock" value="{{ old('stock', (int) $product->stock) }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                            @foreach(['draft', 'active', 'archived'] as $status)
                                <option value="{{ $status }}" {{ old('status', $product->status) === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description (EN)</label>
                    <textarea name="description_en" rows="6" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">{{ old('description_en', $product->description_en) }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Specs JSON</label>
                    <textarea name="specs_json_text" rows="8" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono">{{ old('specs_json_text', json_encode($product->specs_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Condition Notes</label>
                    <textarea name="condition_notes" rows="4" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">{{ old('condition_notes', $product->condition_notes) }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Add More Images</label>
                    <input type="file" name="images[]" multiple class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    <p class="text-xs text-gray-500 mt-1">Optional. Adds to existing media gallery.</p>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <a href="{{ route('admin.products.show', $product) }}" class="px-4 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-800 text-sm font-medium">Cancel</a>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
