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
                          placeholder="📱 OnePlus Turbo 6V

Specifications:
• 💾 Storage: 256 GB
• 🧠 RAM: 12 GB
• 🔋 Battery: 95%-100% health (9000mAh capacity)
• ⚡ Processor: Qualcomm Snapdragon 7s Gen 4
• 📶 Connectivity: 5G, Dual SIM

Condition Notes:
• ✅ Grade S - Fully functional
• 99% appearance (Like New)
• No marks on body
• 📸 See photos for exact condition

💰 Price: ₦440k">{{ old('gadget_description') }}</textarea>
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
                    let num = 0;
                    const priceMatch = text.match(/(?:Price|Cost|Amount)\s*[:\-]\s*(.+?)(?=\n|$)/im);
                    const priceLine = priceMatch ? priceMatch[1].trim() : '';
                    if (priceLine) {
                        const pm = priceLine.match(/([\d,.]+\s*[kKmM]?)/);
                        if (pm) {
                            num = parseFloat(pm[1].replace(/,/g, ''));
                            const s = (pm[1].match(/[kKmM]/) || [])[0];
                            if (s === 'k' || s === 'K') num *= 1000;
                            else if (s === 'm' || s === 'M') num *= 1000000;
                        }
                    }
                    if (!num) {
                        const nm = text.match(/(?:₦|NGN|N)\s*([\d,.]+\s*[kKmM]?)/);
                        if (nm) {
                            const v = nm[1].replace(/,/g, '');
                            num = parseFloat(v) || 0;
                            const s = (v.match(/[kKmM]/) || [])[0];
                            if (s === 'k' || s === 'K') num *= 1000;
                            else if (s === 'm' || s === 'M') num *= 1000000;
                        }
                    }
                    if (num > 0) {
                        this.amountValue = Math.round(num);
                        this.hasPrice = true;
                    } else {
                        this.hasPrice = false;
                    }
                }
            }));
        });
    </script>
</x-layouts.customer>
