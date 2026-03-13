<x-admin-layout>
    <div class="max-w-2xl mx-auto p-4 sm:p-6">
        <div class="mb-6">
            <a href="{{ route('admin.orders.show', $order) }}" class="text-green-600 hover:text-green-700 text-sm font-medium">&larr; Back</a>
            <h2 class="text-xl sm:text-2xl font-bold text-gray-900 mt-2">Edit Order</h2>
        </div>

        <form method="POST" action="{{ route('admin.orders.update', $order) }}" enctype="multipart/form-data" class="bg-white rounded-xl border border-gray-200 p-4 sm:p-6 space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Customer *</label>
                <select name="user_id" required
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 text-base focus:ring-2 focus:ring-green-500 focus:border-green-500 min-h-[48px]">
                    <option value="">Select customer</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" {{ old('user_id', $order->user_id) == $u->id ? 'selected' : '' }}>{{ $u->name }} ({{ $u->email }})</option>
                    @endforeach
                </select>
                @error('user_id')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Product Name *</label>
                <input type="text" name="product_name" required value="{{ old('product_name', $order->product_name) }}"
                       class="w-full rounded-lg border border-gray-300 px-4 py-3 text-base focus:ring-2 focus:ring-green-500 focus:border-green-500"
                       placeholder="e.g. iPhone 14 Pro Max">
                @error('product_name')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Spec Summary</label>
                <textarea name="spec_summary" rows="2"
                          class="w-full rounded-lg border border-gray-300 px-4 py-3 text-base focus:ring-2 focus:ring-green-500 focus:border-green-500"
                          placeholder="e.g. 256GB, Space Black">{{ old('spec_summary', $order->spec_summary) }}</textarea>
                @error('spec_summary')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Full description</label>
                <textarea name="full_description" rows="3"
                          class="w-full rounded-lg border border-gray-300 px-4 py-3 text-base focus:ring-2 focus:ring-green-500 focus:border-green-500">{{ old('full_description', $order->full_description) }}</textarea>
                @error('full_description')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Total Amount (NGN) *</label>
                <input type="number" name="total_amount_ngn" required min="0" step="0.01" value="{{ old('total_amount_ngn', $order->total_amount_ngn) }}"
                       class="w-full rounded-lg border border-gray-300 px-4 py-3 text-base focus:ring-2 focus:ring-green-500 focus:border-green-500">
                @error('total_amount_ngn')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Outstanding balance (NGN)</label>
                <input type="number" name="outstanding_balance_ngn" min="0" step="0.01" value="{{ old('outstanding_balance_ngn', $order->outstanding_balance_ngn ?? 0) }}"
                       class="w-full rounded-lg border border-gray-300 px-4 py-3 text-base focus:ring-2 focus:ring-green-500 focus:border-green-500">
                @error('outstanding_balance_ngn')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Payment Status</label>
                <p class="text-sm text-gray-500 mb-1">Derived from Outstanding Balance (paid when 0, partial when some paid, pending when none).</p>
                <div class="px-4 py-3 rounded-lg bg-gray-50 border border-gray-200 text-gray-700">
                    {{ ucfirst($order->payment_status ?? 'pending') }}
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <p class="text-sm text-gray-500 mb-2">When shipment is assigned, status is auto-derived from the shipment stage.</p>
                <select name="status" required
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 text-base focus:ring-2 focus:ring-green-500 focus:border-green-500 min-h-[48px]">
                    <option value="pending" {{ old('status', $order->status) === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="processing" {{ old('status', $order->status) === 'processing' ? 'selected' : '' }}>Processing</option>
                    <option value="shipped" {{ old('status', $order->status) === 'shipped' ? 'selected' : '' }}>Shipped</option>
                    <option value="delivered" {{ old('status', $order->status) === 'delivered' ? 'selected' : '' }}>Delivered</option>
                    <option value="cancelled" {{ old('status', $order->status) === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    <option value="pending_approval" {{ old('status', $order->status) === 'pending_approval' ? 'selected' : '' }}>Pending approval</option>
                </select>
                @error('status')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            @if($order->paymentSlips && $order->paymentSlips->isNotEmpty())
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Current payment slips</label>
                    <div class="flex flex-wrap gap-2">
                        @foreach($order->paymentSlips as $slip)
                            <a href="{{ asset('storage/' . $slip->file_path) }}" target="_blank" class="text-sm text-green-600 hover:text-green-700">{{ $slip->original_name ?: 'Slip' }}</a>
                        @endforeach
                    </div>
                </div>
            @endif
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Add payment slip(s) (optional)</label>
                <input type="file" name="payment_slips[]" multiple accept=".jpg,.jpeg,.png,.gif,.webp,.pdf"
                       class="w-full rounded-lg border border-gray-300 px-4 py-3 text-base focus:ring-2 focus:ring-green-500 focus:border-green-500">
                @error('payment_slips.*')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Shipment</label>
                <select name="shipment_id"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 text-base focus:ring-2 focus:ring-green-500 focus:border-green-500 min-h-[48px]">
                    <option value="">None</option>
                    @foreach($shipments as $s)
                        <option value="{{ $s->id }}" {{ old('shipment_id', $order->shipment_id) == $s->id ? 'selected' : '' }}>{{ $s->chinese_tracking_code }} ({{ $s->logistics_company }})</option>
                    @endforeach
                </select>
                @error('shipment_id')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Stage Override</label>
                <select name="current_stage_id"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 text-base focus:ring-2 focus:ring-green-500 focus:border-green-500 min-h-[48px]">
                    <option value="">Inherit from shipment</option>
                    @foreach($stages as $stage)
                        <option value="{{ $stage->id }}" {{ old('current_stage_id', $order->current_stage_id) == $stage->id ? 'selected' : '' }}>{{ $stage->name }}</option>
                    @endforeach
                </select>
                @error('current_stage_id')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="pt-4">
                <button type="submit" class="w-full sm:w-auto min-h-[48px] px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</x-admin-layout>

