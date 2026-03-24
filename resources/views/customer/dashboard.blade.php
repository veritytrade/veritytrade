<x-layouts.customer>
    <div class="mb-4">
        <a href="{{ route('dashboard.orders.create') }}"
           class="w-full min-h-[56px] inline-flex items-center justify-center rounded-xl bg-green-600 hover:bg-green-700 text-white text-base font-bold shadow-sm">
            + Create Order
        </a>
        <p class="text-xs text-gray-500 mt-2">Start here to place a new order quickly.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
        <section class="bg-white rounded-xl border border-gray-200 p-4">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2">
                    <h2 class="font-bold text-gray-800">Recent Orders</h2>
                    <span class="px-2 py-0.5 rounded-full bg-gray-100 text-gray-700 text-xs font-medium">
                        Pending: {{ $pendingOrdersCount ?? 0 }}
                    </span>
                </div>
                <a href="{{ route('dashboard.orders') }}" class="text-sm font-medium text-blue-600 hover:text-blue-700">View all →</a>
            </div>
            @forelse($orders as $order)
                <div class="py-3 border-b border-gray-100 last:border-b-0">
                    <div class="flex justify-between items-start text-sm">
                        <div>
                            <a href="{{ route('dashboard.orders', ['order' => $order->id]) }}" class="font-semibold text-gray-800 hover:text-blue-700">{{ $order->product_name ?? 'Order #'.$order->id }}</a>
                            <p class="text-gray-500 text-xs mt-0.5">₦{{ number_format((float) ($order->total_amount_ngn ?? 0)) }} · {{ $order->customer_status_label }}</p>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            @if($order->invoice)
                                <a href="{{ route('dashboard.invoices.download', $order->invoice) }}" target="_blank" rel="noopener" class="text-xs text-green-600 hover:text-green-700 font-medium">See Invoice</a>
                            @endif
                            @if($order->shipment_id || $order->current_stage_id)
                                <a href="{{ route('dashboard.tracking', ['order' => $order->id]) }}" class="text-xs text-blue-600 hover:text-blue-700 font-medium">Track →</a>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-sm text-gray-500">
                    <p>No orders yet.</p>
                    <a href="{{ route('dashboard.orders.create') }}" class="inline-block mt-2 text-green-600 hover:text-green-700 font-medium">Create your first order</a>
                </div>
            @endforelse
            @if($orders->isNotEmpty())
                <a href="{{ route('dashboard.orders') }}" class="block mt-3 text-sm font-medium text-blue-600 hover:text-blue-700">View all orders →</a>
            @endif
        </section>

        <section class="bg-white rounded-xl border border-gray-200 p-4">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2">
                    <h2 class="font-bold text-gray-800">In Transit</h2>
                    <span class="px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 text-xs font-medium">
                        {{ $inTransitCount ?? 0 }}
                    </span>
                </div>
                <a href="{{ route('dashboard.tracking') }}" class="text-sm font-medium text-blue-600 hover:text-blue-700">Tracking page →</a>
            </div>
            @forelse($trackableOrders as $order)
                <div class="py-3 border-b border-gray-100 last:border-b-0">
                    <div class="flex justify-between items-start text-sm mb-2">
                        <a href="{{ route('dashboard.tracking', ['order' => $order->id]) }}" class="font-semibold text-gray-800 hover:text-blue-700">{{ $order->product_name ?? 'Order #'.$order->id }}</a>
                        <span class="text-gray-500 text-xs">{{ $order->effectiveStage()?->short_name ?? $order->effectiveStage()?->name ?? 'Pending' }}</span>
                    </div>
                    <x-tracking-progress-bar :order="$order" />
                    @if($order->canCustomerConfirmDelivery())
                        <form method="POST" action="{{ route('dashboard.orders.confirm-delivery', $order) }}" class="mt-2">
                            @csrf
                            <button type="submit" class="px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white text-xs font-medium rounded-lg">I received my order</button>
                        </form>
                    @endif
                </div>
            @empty
                <p class="text-sm text-gray-500">No packages in transit. When your order is assigned to a shipment, tracking will appear here.</p>
            @endforelse
            @if($trackableOrders->isNotEmpty())
                <a href="{{ route('dashboard.tracking') }}" class="block mt-3 text-sm font-medium text-blue-600 hover:text-blue-700">View all tracking →</a>
            @endif
        </section>
    </div>

    <section class="bg-white rounded-xl border border-gray-200 p-4">
        <div class="flex items-center justify-between mb-3">
            <h2 class="font-bold text-gray-800">Invoice Requests</h2>
            <a href="{{ route('dashboard.invoices') }}" class="text-sm font-medium text-blue-600 hover:text-blue-700">View Invoices →</a>
        </div>
        @if($pendingInvoiceRequests->isNotEmpty())
            <p class="text-sm text-gray-600 mb-3">You requested invoices for these shipments. They become available after admin generation.</p>
            <div class="space-y-2">
                @foreach($pendingInvoiceRequests as $req)
                    <div class="flex items-center justify-between p-3 bg-amber-50 rounded-lg border border-amber-200">
                        <div>
                            <span class="font-medium text-gray-800">{{ $req->shipment?->chinese_tracking_code ?? 'Shipment #'.$req->shipment_id }}</span>
                            <span class="text-sm text-gray-500 ml-2">{{ $req->shipment?->logistics_company ?? '' }}</span>
                        </div>
                        <span class="px-2 py-1 bg-amber-200 text-amber-900 text-xs font-medium rounded">Pending</span>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-sm text-gray-500">No pending invoice requests.</p>
        @endif
    </section>
</x-layouts.customer>
