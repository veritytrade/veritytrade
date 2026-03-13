<x-admin-layout>
    <div class="p-6 max-w-3xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-blue-700">Create Hot Deal</h2>
            <a href="{{ route('admin.deals.index') }}"
               class="text-blue-600 hover:text-blue-800">
                ← Back to Deals
            </a>
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

        <form method="POST" action="{{ route('admin.deals.store') }}" enctype="multipart/form-data" id="deal-form">
            @csrf

            {{-- Smart Description Parser --}}
            <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                <label class="block mb-2 font-medium text-gray-700">
                    📋 Paste WhatsApp Description
                    <span class="text-xs text-blue-600 ml-2">(Auto-extracts Model/Price)</span>
                </label>
                
                <textarea name="description" id="whatsapp-desc" rows="8"
                          class="w-full border border-gray-300 rounded-lg p-3 text-sm font-mono focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                          placeholder="📱 OnePlus Turbo 6V&#10;&#10;Specifications:&#10;• 💾 Storage: 256 GB&#10;• 🧠 RAM: 12 GB&#10;• 🔋 Battery: 95%-100% health&#10;• Condition: Grade S, 99% appearance&#10;&#10;💰 Price: ₦440k"
                          required>{{ old('description') }}</textarea>
                
                <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs text-gray-600 mb-1 font-medium">📱 Extracted Model (Title)</label>
                        <input type="text" id="auto-title" 
                               value="{{ old('title') }}"
                               class="w-full border border-gray-300 rounded-lg p-2.5 text-sm font-medium focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                               placeholder="iPhone 17 Pro Max">
                        {{-- Hidden field that actually gets submitted --}}
                        <input type="hidden" name="title" id="form-title" value="{{ old('title') }}">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600 mb-1 font-medium">💰 Extracted Price Display</label>
                        <input type="text" id="auto-price"
                               value="{{ old('price_display') }}"
                               class="w-full border border-gray-300 rounded-lg p-2.5 text-sm font-bold text-green-600 focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition"
                               placeholder="₦450,000">
                        {{-- Hidden field that actually gets submitted --}}
                        <input type="hidden" name="price_display" id="form-price" value="{{ old('price_display') }}">
                    </div>
                </div>
                
                <p class="text-xs text-gray-500 mt-3 bg-white rounded p-2 border">
                    ✅ Paste your WhatsApp text above → Model & Price auto-fill below<br>
                    ✅ Edit extracted values manually if needed<br>
                    ✅ Full description is still saved for customer view
                </p>
            </div>

            <!-- WhatsApp Message (Custom Override) -->
            <div class="mb-4">
                <label class="block mb-1 font-medium text-gray-700">Custom WhatsApp Message (Optional)</label>
                <textarea name="whatsapp_message" rows="3"
                          class="w-full border border-gray-300 rounded-lg p-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                          placeholder="Leave blank to auto-generate from description">{{ old('whatsapp_message') }}</textarea>
                <p class="text-xs text-gray-500 mt-1">
                    Pre-filled message when customer clicks "Buy Now"
                </p>
            </div>

            <!-- Expiry Date (NO 3-day limit) -->
            <div class="mb-4">
                <label class="block mb-1 font-medium text-gray-700">Expiry Date/Time</label>
                <input type="datetime-local" name="expires_at" 
                    value="{{ old('expires_at', now()->addDays(7)->format('Y-m-d\TH:i')) }}"
                    min="{{ now()->format('Y-m-d\TH:i') }}"
                    class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                    required>
                <p class="text-xs text-gray-500 mt-1">
                    ⏰ Deal disappears from homepage after this time. Set any future date.
                </p>
            </div>

            <!-- ✅ ACTIVE TOGGLE (FIXED BOOLEAN) -->
            <div class="mb-4 flex items-center">
                <input type="checkbox" 
                       name="is_active" 
                       id="is_active" 
                       value="1"  {{-- ✅ CRITICAL: MUST HAVE value="1" --}}
                       class="rounded text-green-600 border-green-300 focus:ring-green-500 cursor-pointer" 
                       checked>
                <label for="is_active" class="ml-2 text-gray-700 font-medium cursor-pointer select-none">
                    Active (visible on homepage immediately)
                </label>
            </div>

            <!-- Images -->
            <div class="mb-6">
                <label class="block mb-2 font-medium text-gray-700">Images <span class="text-red-500">*</span></label>
                <input type="file" name="images[]" multiple accept="image/jpeg,image/png,image/webp"
                       class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition cursor-pointer"
                       onchange="previewImages(event)"
                       required>
                <p class="text-xs text-gray-500 mt-1">
                    • Square images work best (1:1 ratio)<br>
                    • First image shown as main preview<br>
                    • Max 3 images, 2MB each<br>
                    • Recommended size: 600x600px
                </p>
                
                <!-- Image Preview Container -->
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
                    Create Deal
                </button>
            </div>
        </form>
    </div>
</x-admin-layout>

{{-- Smart Parser + Image Preview Script --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const whatsappDesc = document.getElementById('whatsapp-desc');
    const autoTitle = document.getElementById('auto-title');
    const autoPrice = document.getElementById('auto-price');
    const formTitle = document.getElementById('form-title');
    const formPrice = document.getElementById('form-price');
    
    // Sync visible inputs to hidden form fields
    function syncToForm() {
        formTitle.value = autoTitle.value;
        formPrice.value = autoPrice.value;
    }
    
    // Auto-extract on input
    function extractFields() {
        const text = whatsappDesc.value;
        
        // Extract Model/Title (Model: line, or first non-header line for new format)
        const modelMatch = text.match(/Model\s*[:\-]\s*(.+?)(?=\n|$)/i);
        if (modelMatch && !autoTitle.dataset.userEdited) {
            autoTitle.value = modelMatch[1].trim();
            syncToForm();
        } else if (!autoTitle.dataset.userEdited && text.trim()) {
            const firstLine = text.split('\n').map(l => l.trim()).find(l => l && !/^(?:Specifications?|Condition\s*Notes?|Price)[:\s]/i.test(l) && !/^[•\s]*\s*(?:Storage|RAM|Battery|Processor|Connectivity):/i.test(l));
            if (firstLine) {
                const cleaned = firstLine.replace(/^[\s\u2022]*(?:[\u2600-\u27BF]|[\uD83C-\uDBFF][\uDC00-\uDFFF])?\s*/, '').trim();
                if (cleaned) {
                    autoTitle.value = cleaned;
                    syncToForm();
                }
            }
        }
        
        // Extract Price (Price: ₦440k, 💰 Price:, standalone ₦450k, etc.)
        let priceVal = null;
        const patterns = [
            /\b(?:Price|Cost|Amount)\s*[:\-]\s*([₦NGN\s]*[\d,]+(?:\.\d+)?\s*[kKmM]?)/i,
            /(₦|NGN|N)\s*([\d,]+(?:\.\d+)?\s*[kKmM]?)/i,
        ];
        for (const re of patterns) {
            const m = text.match(re);
            if (m) {
                priceVal = (m[2] !== undefined ? (m[1] + m[2]) : m[1]).trim();
                break;
            }
        }
        if (priceVal && !autoPrice.dataset.userEdited) {
            const cleaned = priceVal.replace(/\s+/g, '');
            autoPrice.value = /^[₦NGN]/i.test(cleaned) ? cleaned : ('₦' + cleaned);
            syncToForm();
        }
    }
    
    // Mark fields as user-edited when they receive focus
    autoTitle.addEventListener('focus', () => autoTitle.dataset.userEdited = 'true');
    autoPrice.addEventListener('focus', () => autoPrice.dataset.userEdited = 'true');
    
    autoTitle.addEventListener('input', syncToForm);
    autoPrice.addEventListener('input', syncToForm);
    
    // Extract on paste or input
    whatsappDesc.addEventListener('input', extractFields);
    whatsappDesc.addEventListener('paste', () => setTimeout(extractFields, 100));
    
    // Run extraction on load if textarea has content (e.g. from validation old())
    if (whatsappDesc.value && whatsappDesc.value.trim()) {
        extractFields();
    }
    syncToForm();

    // Ensure hidden fields are synced before form submit (safety net)
    document.getElementById('deal-form').addEventListener('submit', syncToForm);
});

// Image Preview Functionality
function previewImages(event) {
    const preview = document.getElementById('image-preview');
    const files = event.target.files;
    
    preview.innerHTML = '';
    
    if (files.length > 0) {
        preview.classList.remove('hidden');
        
        // Limit to 3 images
        const limitedFiles = [...files].slice(0, 3);
        
        limitedFiles.forEach((file, index) => {
            // Validate file size (2MB max)
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
                    ${index === 0 ? '<span class="absolute top-2 left-2 bg-blue-600 text-white text-xs px-2 py-1 rounded shadow">Main</span>' : ''}
                    <button type="button" onclick="removePreview(this)" class="absolute top-2 right-2 bg-red-500 hover:bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm opacity-0 group-hover:opacity-100 transition">×</button>
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