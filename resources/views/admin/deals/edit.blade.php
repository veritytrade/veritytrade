<x-admin-layout>
    <div class="p-6 max-w-3xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-2xl font-bold text-blue-700">Edit Hot Deal</h2>
                @if(filled($deal->ops_reference))
                    <p class="text-xs text-gray-500 mt-1">
                        Trace ref: <code class="bg-gray-100 px-1 rounded font-mono text-gray-800">{{ $deal->ops_reference }}</code>
                        — search this in the header bar; customers only see it inside WhatsApp as “Ref: …” (no source URL on the site).
                    </p>
                @endif
            </div>
            <div class="flex items-center gap-4">
                @if(request()->filled('from_product'))
                    <a href="{{ route('admin.products.show', request('from_product')) }}"
                       class="text-green-600 hover:text-green-800">
                        ← Back to Product
                    </a>
                @endif
                <a href="{{ route('admin.deals.index') }}"
                   class="text-blue-600 hover:text-blue-800">
                    ← Back to Deals
                </a>
            </div>
        </div>

        @if($errors->any())
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded">
                <p class="font-bold">Validation Errors</p>
                <ul class="list-disc pl-5 mt-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(session('success') || request('notice') === 'updated')
            <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-900 shadow-sm" role="status">
                {{ session('success') ?? 'Hot deal saved successfully.' }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-900 shadow-sm" role="alert">
                {{ session('error') }}
            </div>
        @endif

        <form method="POST"
              action="{{ route('admin.deals.update', $deal) }}"
              enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @if(request()->filled('from_product') || filled(old('from_product')))
                <input type="hidden" name="from_product" value="{{ old('from_product', request('from_product')) }}">
            @endif

            <!-- Title -->
            <div class="mb-4">
                <label class="block mb-1 font-medium text-gray-700">Title <span class="text-red-500">*</span></label>
                <input type="text" name="title" value="{{ old('title', $deal->title) }}"
                       class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                       placeholder="e.g. iPhone 15 Pro - Flash Sale"
                       required>
            </div>

            <!-- Description -->
            <div class="mb-4">
                <label class="block mb-1 font-medium text-gray-700">Description <span class="text-red-500">*</span></label>
                <textarea name="description" rows="6"
                          class="w-full border border-gray-300 rounded-lg p-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                          required>{{ old('description', $deal->description) }}</textarea>
                <p class="text-xs text-gray-500 mt-1">
                    Describe the deal in 2-3 sentences. Customers will see this on mobile.
                </p>
            </div>

            <!-- Price Display -->
            <div class="mb-4">
                <label class="block mb-1 font-medium text-gray-700">Price Display</label>
                <input type="text" name="price_display"
                       value="{{ old('price_display', $deal->price_display) }}"
                       class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                       placeholder="₦450,000 or 450k">
                <p class="text-xs text-gray-500 mt-1">
                    Marketing text only (not used for calculations)
                </p>
            </div>

            <!-- WhatsApp Message -->
            <div class="mb-4">
                <label class="block mb-1 font-medium text-gray-700">Custom WhatsApp Message (Optional)</label>
                <textarea name="whatsapp_message" rows="3"
                          class="w-full border border-gray-300 rounded-lg p-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                          placeholder="Leave blank to auto-generate">{{ old('whatsapp_message', $deal->whatsapp_message) }}</textarea>
                <p class="text-xs text-gray-500 mt-1">
                    Pre-filled message when customer clicks "Buy Now"
                </p>
            </div>

            <!-- Expiry Date -->
            <div class="mb-4">
                <label class="block mb-1 font-medium text-gray-700">Expiry Date/Time <span class="text-red-500">*</span></label>
                <input type="datetime-local" name="expires_at"
                       value="{{ old('expires_at', $deal->expires_at->format('Y-m-d\TH:i')) }}"
                       class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                       required>
                <p class="text-xs text-gray-500 mt-1">
                    ⏰ Deal disappears from homepage after this time. Set any future date.
                </p>
            </div>

            {{-- ✅ FIXED CHECKBOX (Added value="1") --}}
            <div class="mb-4 flex items-center">
                <input type="checkbox"
                       name="is_active"
                       id="is_active"
                       value="1"
                       class="rounded text-green-600 border-green-300 focus:ring-green-500 cursor-pointer"
                       {{ old('is_active', $deal->is_active) ? 'checked' : '' }}>
                <label for="is_active" class="ml-2 text-gray-700 font-medium cursor-pointer select-none">
                    Active (visible on homepage immediately)
                </label>
            </div>

            <!-- Existing Images -->
            @if($deal->images->count())
                <div class="mb-6">
                    <h3 class="font-medium mb-3 text-gray-700 flex items-center">
                        <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        Current Images ({{ $deal->images->count() }})
                    </h3>

                    <div class="grid grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3 max-h-64 overflow-y-auto p-2 border border-gray-200 rounded">
                        @foreach($deal->images as $image)
                            <div class="relative border rounded overflow-hidden group">
                                <img src="{{ storage_asset($image->image_path) }}"
                                    alt="Deal image"
                                    class="w-full aspect-square object-cover">
                                <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition flex items-center justify-center">
                                    <form method="POST"
                                        action="{{ route('admin.deals.image.destroy', ['deal' => $deal, 'imageId' => $image->id]) }}"
                                        class="opacity-0 group-hover:opacity-100 transition"
                                        onsubmit="return confirm('Delete this image?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="bg-red-600 text-white rounded-full w-8 h-8 flex items-center justify-center hover:bg-red-700 shadow-lg">
                                            ✕
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <p class="text-xs text-gray-500 mt-2">
                        • Tap image to delete<br>
                        • Scroll vertically to see all images
                    </p>
                </div>
            @endif

            <!-- New Images -->
            <div class="mb-6">
                <label class="block mb-2 font-medium text-gray-700">Add New Images (Optional)</label>
                <input type="file" name="images[]" multiple accept="image/jpeg,image/png,image/webp"
                       class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition cursor-pointer"
                       onchange="previewImages(event)">
                <p class="text-xs text-gray-500 mt-1">
                    • Square images work best (1:1 ratio)<br>
                    • First new image will be added to existing<br>
                    • Max 3 new images, 2MB each<br>
                    • Recommended size: 600x600px
                </p>

                <div id="image-preview" class="mt-4 grid grid-cols-3 gap-3 hidden"></div>
            </div>

            <!-- Actions -->
            <div class="flex justify-end space-x-3 pt-4 border-t">
                <a href="{{ route('admin.deals.index') }}"
                   class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-5 py-2.5 rounded-lg font-medium transition">
                    Cancel
                </a>
                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg font-medium transition shadow-sm">
                    Update Deal
                </button>
            </div>
        </form>
    </div>
</x-admin-layout>

<script>
function previewImages(event) {
    const preview = document.getElementById('image-preview');
    const files = event.target.files;

    preview.innerHTML = '';

    if (files.length > 0) {
        preview.classList.remove('hidden');

        const limitedFiles = [...files].slice(0, 3);

        limitedFiles.forEach((file, index) => {
            if (file.size > 2 * 1024 * 1024) {
                alert(`⚠️ ${file.name} exceeds 2MB limit and will be skipped.`);
                return;
            }

            const reader = new FileReader();
            reader.onload = (e) => {
                const previewItem = document.createElement('div');
                previewItem.className = 'aspect-square border-2 border-gray-200 rounded-lg overflow-hidden relative group';
                previewItem.innerHTML = `
                    <img src="${e.target.result}" class="w-full h-full object-cover">
                    <button type="button" onclick="removePreview(this)" class="absolute top-2 right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm opacity-0 group-hover:opacity-100 transition">×</button>
                `;
                preview.appendChild(previewItem);
            };
            reader.readAsDataURL(file);
        });
    } else {
        preview.classList.add('hidden');
    }
}

function removePreview(button) {
    button.closest('.aspect-square').remove();
    const preview = document.getElementById('image-preview');
    if (preview.children.length === 0) {
        preview.classList.add('hidden');
    }
}
</script>