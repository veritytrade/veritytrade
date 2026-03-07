<x-layouts.customer>
    <div class="mb-6">
        <a href="{{ route('dashboard.orders') }}" class="text-blue-600 hover:text-blue-700 text-sm font-medium">← Orders</a>
        <h1 class="text-xl sm:text-2xl font-bold text-gray-800 mt-2">Edit Order</h1>
        <p class="text-sm text-gray-600 mt-1">Ref: {{ $order->verity_tracking_code }} — Only editable while pending approval.</p>
    </div>

    <form method="POST" action="{{ route('dashboard.orders.update', $order) }}" enctype="multipart/form-data"
          class="bg-white rounded-xl border border-gray-200 p-4 sm:p-6 space-y-4">
        @csrf
        @method('PUT')
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Gadget description *</label>
            <textarea name="gadget_description" required rows="8"
                      class="w-full rounded-lg border border-gray-300 px-4 py-3 text-base focus:ring-2 focus:ring-green-500 focus:border-green-500 font-mono text-sm">{{ old('gadget_description', $order->full_description ?? '') }}</textarea>
            @error('gadget_description')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Total Amount (NGN)</label>
            <input type="number" name="total_amount_ngn" min="0" step="0.01" value="{{ old('total_amount_ngn', $order->total_amount_ngn) }}"
                   class="w-full rounded-lg border border-gray-300 px-4 py-3 text-base focus:ring-2 focus:ring-green-500 focus:border-green-500">
            @error('total_amount_ngn')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Outstanding balance (NGN)</label>
            <input type="number" name="outstanding_balance_ngn" min="0" step="0.01" value="{{ old('outstanding_balance_ngn', $order->outstanding_balance_ngn ?? 0) }}"
                   class="w-full rounded-lg border border-gray-300 px-4 py-3 text-base focus:ring-2 focus:ring-green-500 focus:border-green-500">
            @error('outstanding_balance_ngn')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
        </div>

        @if($order->paymentSlips->isNotEmpty())
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Current payment slips</label>
                <div class="flex flex-wrap gap-2">
                    @foreach($order->paymentSlips as $slip)
                        <a href="{{ asset('storage/' . $slip->file_path) }}" target="_blank" rel="noopener"
                           class="text-sm text-blue-600 hover:text-blue-700">{{ $slip->original_name ?: 'Slip #'.($loop->iteration) }}</a>
                    @endforeach
                </div>
                <p class="text-xs text-gray-500 mt-1">{{ $order->paymentSlips->count() }}/5 slots used. Add more below.</p>
            </div>
        @endif

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Add more payment slip(s)</label>
            <input type="file" name="payment_slips[]" multiple accept=".jpg,.jpeg,.png,.gif,.webp,.pdf"
                   class="w-full rounded-lg border border-gray-300 px-4 py-3 text-base focus:ring-2 focus:ring-green-500 focus:border-green-500">
            @error('payment_slips.*')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Delivery / logistics</label>
            <div class="flex flex-wrap gap-3">
                <label class="flex-1 min-w-[120px] cursor-pointer">
                    <input type="radio" name="logistics_type" value="within_lagos" {{ in_array(old('logistics_type', $order->logistics_type ?? 'within_lagos'), ['within_lagos', null, '']) ? 'checked' : '' }} class="sr-only peer">
                    <span class="flex flex-col items-center justify-center py-3 px-4 rounded-lg border-2 border-gray-200 bg-white text-gray-700 font-medium hover:border-green-300 transition peer-checked:border-green-600 peer-checked:bg-green-50 peer-checked:text-green-800 peer-checked:ring-2 peer-checked:ring-green-600/20">
                        <span>Within Lagos</span>
                        <span class="text-sm font-semibold text-green-700 mt-0.5">N0</span>
                    </span>
                </label>
                <label class="flex-1 min-w-[120px] cursor-pointer">
                    <input type="radio" name="logistics_type" value="outside_lagos" {{ old('logistics_type', $order->logistics_type) === 'outside_lagos' ? 'checked' : '' }} class="sr-only peer">
                    <span class="flex flex-col items-center justify-center py-3 px-4 rounded-lg border-2 border-gray-200 bg-white text-gray-700 font-medium hover:border-green-300 transition peer-checked:border-green-600 peer-checked:bg-green-50 peer-checked:text-green-800 peer-checked:ring-2 peer-checked:ring-green-600/20">
                        <span>Outside Lagos</span>
                        <span class="text-sm font-semibold text-amber-600 mt-0.5">+N10,000</span>
                    </span>
                </label>
                <label class="flex-1 min-w-[120px] cursor-pointer">
                    <input type="radio" name="logistics_type" value="combined" {{ old('logistics_type', $order->logistics_type) === 'combined' ? 'checked' : '' }} class="sr-only peer">
                    <span class="flex flex-col items-center justify-center py-3 px-4 rounded-lg border-2 border-gray-200 bg-white text-gray-700 font-medium hover:border-green-300 transition peer-checked:border-green-600 peer-checked:bg-green-50 peer-checked:text-green-800 peer-checked:ring-2 peer-checked:ring-green-600/20">
                        <span>Part of combined</span>
                        <span class="text-sm font-semibold text-green-700 mt-0.5">N0</span>
                    </span>
                </label>
            </div>
        </div>

        <div class="pt-4 flex flex-col sm:flex-row gap-3">
            <button type="submit" class="min-h-[48px] px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition">
                Save changes
            </button>
            <form method="POST" action="{{ route('dashboard.orders.destroy', $order) }}" class="inline" onsubmit="return confirm('Cancel this order?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="min-h-[48px] px-6 py-3 bg-red-100 hover:bg-red-200 text-red-700 font-medium rounded-lg">
                    Cancel order
                </button>
            </form>
            <a href="{{ route('dashboard.orders') }}" class="min-h-[48px] px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg text-center flex items-center justify-center">
                Back
            </a>
        </div>
    </form>
</x-layouts.customer>
