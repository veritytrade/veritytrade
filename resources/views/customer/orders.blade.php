<x-layouts.customer>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-800">My Orders</h1>
            <p class="text-sm text-gray-600 mt-1">Your order history. Track shipments from the <a href="{{ route('dashboard.tracking') }}" class="text-blue-600 hover:text-blue-700 font-medium">Tracking</a> page.</p>
        </div>
        <a href="{{ route('dashboard.orders.create') }}" class="shrink-0 min-h-[44px] px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg text-center">+ Create order</a>
    </div>

    <div class="space-y-4">
        @forelse($orders as $order)
            <div class="bg-white rounded-xl border border-gray-200 p-4 sm:p-5 shadow-sm hover:shadow-md transition">
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                    <div class="flex-1 min-w-0">
                        <h2 class="font-bold text-gray-900">{{ $order->product_name ?? 'Order #'.$order->id }}</h2>
                        @if($order->spec_summary)
                            <p class="text-sm text-gray-600 mt-0.5">{{ $order->spec_summary }}</p>
                        @endif
                        <p class="text-lg font-bold text-green-700 mt-2">₦{{ number_format((float) ($order->total_amount_ngn ?? 0)) }}</p>
                        <span class="inline-block mt-2 px-2 py-0.5 rounded text-xs font-medium {{ $order->status === 'delivered' ? 'bg-green-100 text-green-800' : ($order->status === 'cancelled' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800') }}">{{ $order->customer_status_label }}</span>
                    </div>
                    <div class="shrink-0 flex flex-col sm:flex-row gap-2">
                        @if($order->canCustomerEdit())
                            <a href="{{ route('dashboard.orders.edit', $order) }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg text-center">Edit</a>
                        @endif
                        @if($order->invoice)
                            <a href="{{ route('dashboard.invoices.download', $order->invoice) }}" target="_blank" rel="noopener" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg text-center">See Invoice</a>
                        @elseif(in_array($order->status, ['processing', 'shipped', 'delivered']) && $order->shipment_id)
                            @if($order->invoiceRequest?->isPending())
                                <span class="px-4 py-2 bg-amber-50 text-amber-700 text-sm font-medium rounded-lg">Invoice requested</span>
                            @else
                                <form method="POST" action="{{ route('dashboard.orders.request-invoice', $order) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="px-4 py-2 bg-amber-100 hover:bg-amber-200 text-amber-800 text-sm font-medium rounded-lg text-center">Request Invoice</button>
                                </form>
                            @endif
                        @endif
                        @if($order->shipment_id || $order->current_stage_id)
                            <a href="{{ route('dashboard.tracking') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg text-center">Track order →</a>
                        @else
                            <span class="text-sm text-gray-400 py-2">Tracking pending</span>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-xl border border-gray-200 p-8 text-center text-gray-500">
                <p>No orders found.</p>
            </div>
        @endforelse
    </div>
    <div class="mt-6">{{ $orders->links() }}</div>
</x-layouts.customer>
