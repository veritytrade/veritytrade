<x-admin-layout>
    <div class="max-w-2xl mx-auto p-4 sm:p-6">
        <div class="mb-6">
            <a href="{{ route('admin.orders.index') }}" class="text-green-600 hover:text-green-700 text-sm font-medium">&larr; Orders</a>
            <h2 class="text-xl sm:text-2xl font-bold text-gray-900 mt-2">Add Order</h2>
            <p class="text-sm text-gray-500 mt-1">Paste gadget details (WhatsApp style). Same as customer-side creation.</p>
        </div>

        <form method="POST" action="{{ route('admin.orders.store') }}" enctype="multipart/form-data" class="bg-white rounded-xl border border-gray-200 p-4 sm:p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Customer *</label>
                <select name="user_id" required
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 text-base focus:ring-2 focus:ring-green-500 focus:border-green-500 min-h-[48px]">
                    <option value="">Select customer</option>
                    @foreach($customers as $u)
                        <option value="{{ $u->id }}" {{ old('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }} ({{ $u->email }})</option>
                    @endforeach
                </select>
                @error('user_id')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

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
                           class="w-full rounded-lg border border-gray-300 px-4 py-3 text-base focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    @error('total_amount_ngn')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Outstanding balance (NGN)</label>
                <input type="number" name="outstanding_balance_ngn" min="0" step="0.01" value="{{ old('outstanding_balance_ngn', 0) }}"
                       class="w-full rounded-lg border border-gray-300 px-4 py-3 text-base focus:ring-2 focus:ring-green-500 focus:border-green-500">
                @error('outstanding_balance_ngn')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Delivery / logistics</label>
                <div class="flex flex-wrap gap-3">
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="logistics_type" value="within_lagos" {{ in_array(old('logistics_type'), ['within_lagos', null, '']) ? 'checked' : '' }} class="rounded border-gray-300 text-green-600">
                        <span class="text-sm">Within Lagos (N0)</span>
                    </label>
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="logistics_type" value="outside_lagos" {{ old('logistics_type') === 'outside_lagos' ? 'checked' : '' }} class="rounded border-gray-300 text-green-600">
                        <span class="text-sm">Outside Lagos (+N10,000)</span>
                    </label>
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="logistics_type" value="combined" {{ old('logistics_type') === 'combined' ? 'checked' : '' }} class="rounded border-gray-300 text-green-600">
                        <span class="text-sm">Part of combined shipment (N0)</span>
                    </label>
                </div>
                <p class="text-xs text-gray-500 mt-1">Use "Part of combined shipment" when logistics is paid on another order in the same shipment.</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Payment slip(s) (optional)</label>
                <input type="file" name="payment_slips[]" multiple accept=".jpg,.jpeg,.png,.gif,.webp,.pdf"
                       class="w-full rounded-lg border border-gray-300 px-4 py-3 text-base focus:ring-2 focus:ring-green-500 focus:border-green-500">
                <p class="text-xs text-gray-500 mt-1">Up to 5. Max 5MB each.</p>
                @error('payment_slips.*')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Shipment</label>
                <select name="shipment_id"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 text-base focus:ring-2 focus:ring-green-500 focus:border-green-500 min-h-[48px]">
                    <option value="">None</option>
                    @foreach($shipments as $s)
                        <option value="{{ $s->id }}" {{ old('shipment_id') == $s->id ? 'selected' : '' }}>{{ $s->chinese_tracking_code }} ({{ $s->logistics_company }})</option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-500 mt-1">Status is set automatically from the shipment stage.</p>
                @error('shipment_id')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="pt-4">
                <button type="submit" class="w-full sm:w-auto min-h-[48px] px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition">
                    Create Order
                </button>
            </div>
        </form>

        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('orderPriceExtractor', () => ({
                    hasPrice: false,
                    amountValue: @json(old('total_amount_ngn', '')),
                    init() { this.extractPrice(); },
                    extractPrice() {
                        const textarea = this.$refs.desc;
                        if (!textarea) return;
                        const text = textarea.value || '';
                        const match = text.match(/Price:\s*(.+)$/im);
                        if (match) {
                            const raw = (match[1] || '').replace(/[^\d.]/g, '');
                            this.amountValue = raw ? parseFloat(raw) : 0;
                            this.hasPrice = true;
                        } else { this.hasPrice = false; }
                    }
                }));
            });
        </script>
    </div>
</x-admin-layout>
