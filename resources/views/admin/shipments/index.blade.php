<x-admin-layout>
    <div class="max-w-6xl mx-auto p-4 sm:p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <h2 class="text-xl sm:text-2xl font-bold text-gray-900">Shipments</h2>
            @if(auth()->user()->hasPermission('create_shipment'))
                <a href="{{ route('admin.shipments.create') }}"
                   class="inline-flex justify-center items-center bg-green-600 hover:bg-green-700 text-white px-4 py-2.5 sm:px-6 sm:py-3 rounded-lg font-medium transition shadow-sm w-full sm:w-auto min-h-[44px]">
                    + Add Shipment
                </a>
            @endif
        </div>

        @if(session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
                 class="mb-4 p-4 bg-green-100 border border-green-200 text-green-800 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="p-3 sm:p-4 text-left font-semibold text-gray-700">Chinese Code</th>
                            <th class="p-3 sm:p-4 text-left font-semibold text-gray-700 hidden sm:table-cell">Logistics</th>
                            <th class="p-3 sm:p-4 text-left font-semibold text-gray-700">Stage</th>
                            <th class="p-3 sm:p-4 text-center font-semibold text-gray-700">Orders</th>
                            <th class="p-3 sm:p-4 text-center font-semibold text-gray-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($shipments as $s)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="p-3 sm:p-4 font-medium text-gray-800">
                                    <div class="flex flex-col gap-0.5">
                                        <span class="inline-flex items-center gap-1.5">
                                            <a href="{{ route('admin.shipments.show', $s) }}" class="text-green-600 hover:text-green-700">
                                                {{ Str::limit($s->chinese_tracking_code, 12) }}
                                            </a>
                                            <button type="button"
                                                    data-copy="{{ e($s->chinese_tracking_code) }}"
                                                    onclick="navigator.clipboard.writeText(this.dataset.copy); this.textContent='Copied!'; setTimeout(()=>this.textContent='Copy', 1500)"
                                                    class="text-xs text-gray-500 hover:text-green-600 font-medium"
                                                    title="Copy full code">
                                                Copy
                                            </button>
                                        </span>
                                        @php($logisticsShort = (string) \Illuminate\Support\Str::of($s->logistics_company)->before(' '))
                                        @if($logisticsShort !== '')
                                            <span class="text-xs text-gray-500">
                                                {{ $logisticsShort }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="p-3 sm:p-4 text-gray-600 hidden sm:table-cell">{{ $s->logistics_company }}</td>
                                <td class="p-3 sm:p-4">{{ $s->currentStage?->name ?? '—' }}</td>
                                <td class="p-3 sm:p-4 text-center">{{ $s->orders_count }}</td>
                                <td class="p-3 sm:p-4 text-center">
                                    <div class="flex flex-wrap justify-center gap-2">
                                        <a href="{{ route('admin.shipments.show', $s) }}"
                                           class="inline-flex items-center justify-center min-h-[36px] px-3 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-medium">
                                            View
                                        </a>
                                        @if(auth()->user()->hasPermission('update_shipment_stage'))
                                            <a href="{{ route('admin.shipments.edit', $s) }}"
                                               class="inline-flex items-center justify-center min-h-[36px] px-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium">
                                                Edit
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="p-6 text-center text-gray-500">No shipments yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($shipments->hasPages())
                <div class="p-4 border-t border-gray-100">{{ $shipments->links() }}</div>
            @endif
        </div>
    </div>
</x-admin-layout>
