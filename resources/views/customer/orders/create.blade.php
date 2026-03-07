<x-layouts.customer>
    <div class="mb-6">
        <a href="{{ route('dashboard.orders') }}" class="text-blue-600 hover:text-blue-700 text-sm font-medium">← Orders</a>
        <h1 class="text-xl sm:text-2xl font-bold text-gray-800 mt-2">Create Order</h1>
        <p class="text-sm text-gray-600 mt-1">Paste your gadget details (WhatsApp style). Quote the Ref code when contacting support.</p>
    </div>

    <form method="POST" action="{{ route('dashboard.orders.store') }}" enctype="multipart/form-data"
          class="bg-white rounded-xl border border-gray-200 p-4 sm:p-6 space-y-4">
        @csrf
        <div x-data="orderPriceExtractor()" x-init="init()">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Gadget description *</label>
                <textarea name="gadget_description" required rows="10"
                          x-ref="desc"
                          x-on:input="extractPrice()"
                          class="w-full rounded-lg border border-gray-300 px-4 py-3 text-base focus:ring-2 focus:ring-green-500 focus:border-green-500 font-mono text-sm"
                          placeholder="Model: iPhone 14 Pro Max (Gold)
Memory: 512 GB
Battery: In good condition (100% health, 0 cycles, 4323mAh capacity)
Defect: Battery replaced (brand battery: Pisen)
Appearance: 95% (Excellent) Minor marks on housing.
Features: Dual SIM, 5G, High refresh rate screen
Price: 1,030,000">{{ old('gadget_description') }}</textarea>
                @error('gadget_description')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Total Amount (NGN) <span x-show="!hasPrice" class="text-red-600">*</span></label>
                <input type="number" name="total_amount_ngn" min="0" step="0.01"
                       x-ref="amount"
                       x-bind:required="!hasPrice"
                       x-bind:value="amountValue"
                       x-on:input="amountValue = $event.target.value"
                       class="w-full rounded-lg border border-gray-300 px-4 py-3 text-base focus:ring-2 focus:ring-green-500 focus:border-green-500"
                       placeholder="">
                @error('total_amount_ngn')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Outstanding balance (NGN)</label>
            <input type="number" name="outstanding_balance_ngn" min="0" step="0.01" value="{{ old('outstanding_balance_ngn', 0) }}"
                   class="w-full rounded-lg border border-gray-300 px-4 py-3 text-base focus:ring-2 focus:ring-green-500 focus:border-green-500"
                   placeholder="Amount still to pay">
            @error('outstanding_balance_ngn')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Payment slip(s)</label>
            <input type="file" name="payment_slips[]" multiple accept=".jpg,.jpeg,.png,.gif,.webp,.pdf"
                   class="w-full rounded-lg border border-gray-300 px-4 py-3 text-base focus:ring-2 focus:ring-green-500 focus:border-green-500">
            <p class="text-xs text-gray-500 mt-1">Up to 5 images or PDFs. Max 5MB each.</p>
            @error('payment_slips.*')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Delivery / logistics</label>
            <div class="flex flex-wrap gap-3">
                <label class="flex-1 min-w-[120px] cursor-pointer">
                    <input type="radio" name="logistics_type" value="within_lagos" {{ in_array(old('logistics_type'), ['within_lagos', null, '']) ? 'checked' : '' }} class="sr-only peer">
                    <span class="flex flex-col items-center justify-center py-3 px-4 rounded-lg border-2 border-gray-200 bg-white text-gray-700 font-medium hover:border-green-300 transition peer-checked:border-green-600 peer-checked:bg-green-50 peer-checked:text-green-800 peer-checked:ring-2 peer-checked:ring-green-600/20">
                        <span>Within Lagos</span>
                        <span class="text-sm font-semibold text-green-700 mt-0.5">N0</span>
                    </span>
                </label>
                <label class="flex-1 min-w-[120px] cursor-pointer">
                    <input type="radio" name="logistics_type" value="outside_lagos" {{ old('logistics_type') === 'outside_lagos' ? 'checked' : '' }} class="sr-only peer">
                    <span class="flex flex-col items-center justify-center py-3 px-4 rounded-lg border-2 border-gray-200 bg-white text-gray-700 font-medium hover:border-green-300 transition peer-checked:border-green-600 peer-checked:bg-green-50 peer-checked:text-green-800 peer-checked:ring-2 peer-checked:ring-green-600/20">
                        <span>Outside Lagos</span>
                        <span class="text-sm font-semibold text-amber-600 mt-0.5">+N10,000</span>
                    </span>
                </label>
                <label class="flex-1 min-w-[120px] cursor-pointer">
                    <input type="radio" name="logistics_type" value="combined" {{ old('logistics_type') === 'combined' ? 'checked' : '' }} class="sr-only peer">
                    <span class="flex flex-col items-center justify-center py-3 px-4 rounded-lg border-2 border-gray-200 bg-white text-gray-700 font-medium hover:border-green-300 transition peer-checked:border-green-600 peer-checked:bg-green-50 peer-checked:text-green-800 peer-checked:ring-2 peer-checked:ring-green-600/20">
                        <span>Part of combined</span>
                        <span class="text-sm font-semibold text-green-700 mt-0.5">N0</span>
                    </span>
                </label>
            </div>
            <p class="text-xs text-gray-500 mt-1">Use "Part of combined" when logistics is paid on another order in the same shipment.</p>
        </div>

        <div class="pt-4 flex flex-col sm:flex-row gap-3">
            <button type="submit" class="min-h-[48px] px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition">
                Submit order
            </button>
            <a href="{{ route('dashboard.orders') }}" class="min-h-[48px] px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg text-center">
                Cancel
            </a>
        </div>
    </form>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('orderPriceExtractor', () => ({
                hasPrice: false,
                amountValue: @json(old('total_amount_ngn', '')),
                init() {
                    this.extractPrice();
                },
                extractPrice() {
                    const textarea = this.$refs.desc;
                    if (!textarea) return;
                    const text = textarea.value || '';
                    const match = text.match(/Price:\s*(.+)$/im);
                    if (match) {
                        const raw = (match[1] || '').replace(/[^\d.]/g, '');
                        const num = raw ? parseFloat(raw) : 0;
                        this.amountValue = num;
                        this.hasPrice = true;
                    } else {
                        this.hasPrice = false;
                    }
                }
            }));
        });
    </script>
</x-layouts.customer>
