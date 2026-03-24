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
            <div class="rounded-lg border border-gray-200 p-4 space-y-3">
                <h3 class="text-sm font-semibold text-gray-800">Supplier Mapping</h3>
                <p class="text-xs text-gray-500">Keep supplier order number and logistics code in one row to track package ownership.</p>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Supplier Platform</label>
                    <select name="supplier_platform"
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 text-base focus:ring-2 focus:ring-green-500 focus:border-green-500 min-h-[48px]">
                        <option value="">Select platform</option>
                        @foreach($supplierPlatforms as $value => $label)
                            <option value="{{ $value }}" {{ old('supplier_platform', $order->supplier_platform) === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('supplier_platform')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Supplier Order Number</label>
                    <input type="text" name="supplier_order_number"
                           value="{{ old('supplier_order_number', $order->supplier_order_number) }}"
                           class="w-full rounded-lg border border-gray-300 px-4 py-3 text-base focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    @error('supplier_order_number')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Supplier Logistics Code</label>
                    <input type="text" name="supplier_logistics_code"
                           value="{{ old('supplier_logistics_code', $order->supplier_logistics_code) }}"
                           class="w-full rounded-lg border border-gray-300 px-4 py-3 text-base focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    @error('supplier_logistics_code')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
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
                <p class="text-sm text-gray-500 mb-2">When shipment or stage override exists, status is auto-derived and manual value is ignored.</p>
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
            <div class="rounded-lg border border-blue-100 bg-blue-50 p-3 text-sm text-blue-800">
                Shipment assignment and stage override have been centralized on the Order Details page to reduce conflicts.
                <a href="{{ route('admin.orders.show', $order) }}" class="font-medium underline hover:text-blue-900">Go to Order Details</a>.
            </div>
            @if($order->paymentSlips && $order->paymentSlips->isNotEmpty())
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Current payment slips</label>
                    <div class="flex flex-wrap gap-2">
                        @foreach($order->paymentSlips as $slip)
                            <a href="{{ storage_asset($slip->file_path) }}" target="_blank" class="text-sm text-green-600 hover:text-green-700">{{ $slip->original_name ?: 'Slip' }}</a>
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
            <div class="pt-4">
                <button type="submit" class="w-full sm:w-auto min-h-[48px] px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</x-admin-layout>

