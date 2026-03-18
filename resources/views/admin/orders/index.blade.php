<x-admin-layout>
    <div class="max-w-6xl mx-auto p-4 sm:p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <h2 class="text-xl sm:text-2xl font-bold text-gray-900">Orders</h2>
            @if(auth()->user()->hasPermission('create_order'))
                <a href="{{ route('admin.orders.create') }}"
                   class="inline-flex justify-center items-center bg-green-600 hover:bg-green-700 text-white px-4 py-2.5 sm:px-6 sm:py-3 rounded-lg font-medium transition shadow-sm w-full sm:w-auto min-h-[44px]">
                    + Add Order
                </a>
            @endif
        </div>

        @if(session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
                 class="mb-4 p-4 bg-green-100 border border-green-200 text-green-800 rounded-lg">{{ session('success') }}</div>
        @endif

        {{-- Filters --}}
        <div class="mb-4 bg-white rounded-xl border border-gray-200 p-4">
            <form method="GET" action="{{ route('admin.orders.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-sm">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Customer (email or WhatsApp name)</label>
                    <input type="text" name="customer" value="{{ request('customer') }}"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500"
                           placeholder="Search by customer">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Logistics</label>
                    <input type="text" name="logistics" value="{{ request('logistics') }}"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500"
                           placeholder="e.g. DHL, FedEx">
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit"
                            class="px-4 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white font-medium min-h-[40px]">
                        Filter
                    </button>
                    <a href="{{ route('admin.orders.index') }}"
                       class="px-3 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 min-h-[40px]">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="p-3 sm:p-4 text-left font-semibold text-gray-700">Order</th>
                            <th class="p-3 sm:p-4 text-left font-semibold text-gray-700">Customer</th>
                            <th class="p-3 sm:p-4 text-left font-semibold text-gray-700">Stage</th>
                            <th class="p-3 sm:p-4 text-left font-semibold text-gray-700 hidden md:table-cell">Invoice</th>
                            <th class="p-3 sm:p-4 text-center font-semibold text-gray-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($orders as $o)
                            @php($eff = $o->effectiveStage())
                            <tr class="hover:bg-gray-50">
                                <td class="p-3 sm:p-4">
                                    <span class="font-medium text-gray-800">{{ Str::limit($o->product_name ?? 'Order #'.$o->id, 18) }}</span>
                                    @if($o->status === 'pending_approval')
                                        <span class="ml-1 px-1.5 py-0.5 text-xs rounded bg-amber-100 text-amber-800">Pending</span>
                                    @endif
                                </td>
                                <td class="p-3 sm:p-4 text-gray-700">
                                    {{ $o->user?->username ?? $o->user?->name ?? '—' }}
                                </td>
                                <td class="p-3 sm:p-4">{{ $eff?->name ?? '—' }}</td>
                                <td class="p-3 sm:p-4 text-gray-600 hidden md:table-cell">{{ $o->invoice?->invoice_number ?? '—' }}</td>
                                <td class="p-3 sm:p-4">
                                    <a href="{{ route('admin.orders.show', $o) }}" class="inline-flex items-center justify-center min-h-[36px] px-3 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-medium">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="p-6 text-center text-gray-500">No orders yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($orders->hasPages())
                <div class="p-4 border-t border-gray-100">{{ $orders->links() }}</div>
            @endif
        </div>
    </div>
</x-admin-layout>
