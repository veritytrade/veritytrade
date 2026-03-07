<x-admin-layout>
<div class="max-w-4xl mx-auto p-4 sm:p-6">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <a href="{{ route('admin.shipments.index') }}" class="text-green-600 hover:text-green-700 text-sm font-medium">← Shipments</a>
            <h2 class="text-xl sm:text-2xl font-bold text-gray-900 mt-2">Shipment Details</h2>
        </div>
        @if(auth()->user()->hasPermission('update_shipment_stage'))
            <a href="{{ route('admin.shipments.edit', $shipment) }}" class="inline-flex items-center justify-center min-h-[44px] px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg">
                Edit Shipment
            </a>
        @endif
    </div>

    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
             class="mb-4 p-4 bg-green-100 border border-green-200 text-green-800 rounded-lg">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-xl border border-gray-200 p-4 sm:p-6 mb-6">
        <h3 class="font-bold text-gray-800 mb-4">Shipment Info</h3>
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
            <dt class="text-gray-500">Chinese Tracking Code</dt>
            <dd class="font-medium text-gray-800 flex items-center gap-2 flex-wrap">
                <code class="font-mono bg-gray-100 px-2 py-1 rounded">{{ $shipment->chinese_tracking_code }}</code>
                <button type="button" data-copy="{{ e($shipment->chinese_tracking_code) }}" onclick="navigator.clipboard.writeText(this.dataset.copy); this.textContent='Copied!'; setTimeout(()=>this.textContent='Copy', 1500)" class="text-xs text-green-600 hover:text-green-700 font-medium px-2 py-1 rounded hover:bg-green-50">
                    Copy
                </button>
            </dd>
            <dt class="text-gray-500">Logistics</dt>
            <dd class="font-medium text-gray-800">{{ $shipment->logistics_company }}</dd>
            <dt class="text-gray-500">Status</dt>
            <dd><span class="px-2 py-0.5 rounded {{ $shipment->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700' }}">{{ ucfirst($shipment->status) }}</span></dd>
            <dt class="text-gray-500">Current Stage</dt>
            <dd class="font-medium">{{ $shipment->currentStage?->name ?? '—' }}</dd>
        </dl>

        @if(auth()->user()->hasPermission('update_shipment_stage'))
            <form method="POST" action="{{ route('admin.shipments.update-stage', $shipment) }}" class="mt-6 flex flex-col sm:flex-row gap-3">
                @csrf
                <select name="current_stage_id" class="flex-1 rounded-lg border border-gray-300 px-4 py-3 min-h-[48px]">
                    @foreach($stages as $stage)
                        <option value="{{ $stage->id }}" {{ $shipment->current_stage_id == $stage->id ? 'selected' : '' }}>{{ $stage->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="min-h-[48px] px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg">Update Stage</button>
            </form>
            <form method="POST" action="{{ route('admin.shipments.apply-stage-all', $shipment) }}" class="mt-3 flex flex-col sm:flex-row gap-3">
                @csrf
                <select name="current_stage_id" class="rounded-lg border border-gray-300 px-4 py-2 min-h-[44px] flex-1">
                    @foreach($stages as $stage)
                        <option value="{{ $stage->id }}" {{ $shipment->current_stage_id == $stage->id ? 'selected' : '' }}>{{ $stage->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="min-h-[44px] px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg" {{ $shipment->orders_count < 1 ? 'disabled' : '' }}>Apply to all orders</button>
            </form>
        @endif
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <h3 class="p-4 sm:p-6 font-bold text-gray-800 border-b border-gray-100">Linked Orders ({{ $shipment->orders->count() }})</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="p-3 sm:p-4 text-left font-semibold text-gray-700">Order</th>
                        <th class="p-3 sm:p-4 text-left font-semibold text-gray-700">Customer</th>
                        <th class="p-3 sm:p-4 text-left font-semibold text-gray-700">Verity Code</th>
                        <th class="p-3 sm:p-4 text-left font-semibold text-gray-700">Stage</th>
                        <th class="p-3 sm:p-4 text-center font-semibold text-gray-700">Override</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($shipment->orders as $order)
                        @php($eff = $order->effectiveStage())
                        <tr class="hover:bg-gray-50">
                            <td class="p-3 sm:p-4"><a href="{{ route('admin.orders.show', $order) }}" class="text-green-600 hover:text-green-700 font-medium">{{ Str::limit($order->product_name ?? 'Order #'.$order->id, 20) }}</a></td>
                            <td class="p-3 sm:p-4 text-gray-700">{{ $order->user?->name ?? '—' }}</td>
                            <td class="p-3 sm:p-4 font-mono text-gray-700">{{ $order->verity_tracking_code ?? '—' }}</td>
                            <td class="p-3 sm:p-4">{{ $eff?->name ?? '—' }}</td>
                            <td class="p-3 sm:p-4">
                                @if(auth()->user()->hasPermission('override_order_stage'))
                                    <form method="POST" action="{{ route('admin.orders.override-stage', $order) }}" class="inline">
                                        @csrf
                                        <select name="current_stage_id" onchange="this.form.submit()" class="rounded border border-gray-300 py-1.5 px-2 text-xs min-h-[36px]">
                                            <option value="">Inherit</option>
                                            @foreach($stages as $stage)
                                                <option value="{{ $stage->id }}" {{ $order->current_stage_id == $stage->id ? 'selected' : '' }}>{{ $stage->name }}</option>
                                            @endforeach
                                        </select>
                                    </form>
                                @else — @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="p-6 text-center text-gray-500">No orders linked.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
</x-admin-layout>
