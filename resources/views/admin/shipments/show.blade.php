<x-admin-layout>
<div class="max-w-4xl mx-auto p-4 sm:p-6">
    <nav class="mb-3 text-xs text-gray-500">
        <a href="{{ route('admin.dashboard') }}" class="hover:text-green-700">Dashboard</a>
        <span class="mx-1">/</span>
        <a href="{{ route('admin.shipments.index') }}" class="hover:text-green-700">Shipments</a>
        <span class="mx-1">/</span>
        <span class="text-gray-700 font-medium">Shipment Details</span>
    </nav>
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-gray-900">Shipment Details</h2>
            <p class="text-xs text-gray-400 mt-1">Last updated: {{ optional($shipment->updated_at)->format('d M Y H:i') ?? '—' }}</p>
        </div>
        <div class="flex flex-col sm:flex-row gap-2 sm:items-center">
        @if(auth()->user()->hasPermission('update_shipment_stage'))
            <a href="{{ route('admin.shipments.edit', $shipment) }}" class="inline-flex items-center justify-center min-h-[44px] px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg">
                Edit Shipment
            </a>
            <form method="POST" action="{{ route('admin.shipments.refresh-carrier-tracking', $shipment) }}" class="inline" onsubmit="return confirm('Fetch latest logistics updates from Sky Cargo for this tracking code?');">
                @csrf
                <button type="submit" class="inline-flex items-center justify-center min-h-[44px] px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white font-medium rounded-lg">
                    Refresh carrier tracking
                </button>
            </form>
        @endif
        </div>
    </div>

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
            <dt class="text-gray-500">Carrier data</dt>
            <dd class="font-medium text-gray-700">
                @if($shipment->carrier_tracks_synced_at)
                    Last refreshed {{ $shipment->carrier_tracks_synced_at->format('d M Y H:i') }}
                    @if(is_array($shipment->carrier_tracks_json))
                        · {{ count($shipment->carrier_tracks_json['tracks'] ?? []) }} event(s) stored
                    @endif
                @else
                    Not loaded — use “Refresh carrier tracking”
                @endif
            </dd>
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

    @if($shipment->orders->isNotEmpty())
        <div class="bg-white rounded-xl border border-gray-200 p-4 sm:p-6 mb-6">
            <h3 class="font-bold text-gray-800 mb-2">Customer preview</h3>
            <p class="text-xs text-gray-500 mb-4">Same tracking card as the linked customer sees (first order in this shipment).</p>
            <x-tracking-vertical-timeline :order="$shipment->orders->first()" />
        </div>
    @else
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6 text-sm text-amber-900">
            Link at least one order to preview the customer tracking card. You can still refresh carrier data using the tracking code above.
        </div>
    @endif

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <h3 class="p-4 sm:p-6 font-bold text-gray-800 border-b border-gray-100">Linked Orders ({{ $shipment->orders->count() }})</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="p-3 sm:p-4 text-left font-semibold text-gray-700">Order</th>
                        <th class="p-3 sm:p-4 text-left font-semibold text-gray-700">Customer</th>
                        <th class="p-3 sm:p-4 text-left font-semibold text-gray-700">Invoice</th>
                        <th class="p-3 sm:p-4 text-left font-semibold text-gray-700">Stage</th>
                        <th class="p-3 sm:p-4 text-center font-semibold text-gray-700">Override</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($shipment->orders as $order)
                        @php($eff = $order->effectiveStage())
                        <tr class="hover:bg-gray-50">
                            <td class="p-3 sm:p-4"><a href="{{ route('admin.orders.show', $order) }}" class="text-green-600 hover:text-green-700 font-medium">{{ Str::limit($order->product_name ?? 'Order #'.$order->id, 20) }}</a></td>
                            <td class="p-3 sm:p-4 text-gray-700">
                                @if($order->user)
                                    <a href="{{ route('admin.customers.show', ['q' => $order->user->email]) }}" class="text-green-600 hover:text-green-700">
                                        {{ $order->user?->username ?? $order->user?->name ?? '—' }}
                                    </a>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="p-3 sm:p-4 font-mono text-gray-700">{{ $order->invoice?->invoice_number ?? '—' }}</td>
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
