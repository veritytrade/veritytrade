<x-layouts.customer>
    <h1 class="text-xl sm:text-2xl font-bold text-gray-800 mb-4">Order Tracking</h1>
    <p class="text-sm text-gray-600 mb-6">Track your orders using your Verity tracking code.</p>
    <div class="space-y-4">
        @forelse($orders as $order)
            <div class="bg-white rounded-xl border border-gray-200 p-4 sm:p-5 shadow-sm">
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                    <div>
                        <h2 class="font-bold text-gray-900">{{ $order->product_name ?? 'Order #'.($order->verity_tracking_code ?? $order->id) }}</h2>
                        @if($order->spec_summary)
                            <p class="text-sm text-gray-600 mt-0.5">{{ $order->spec_summary }}</p>
                        @endif
                        <p class="text-sm mt-1 flex items-center gap-2 flex-wrap">
                            <code class="font-mono bg-gray-100 px-2 py-0.5 rounded text-gray-700">{{ $order->verity_tracking_code ?? '—' }}</code>
                            @if($order->verity_tracking_code)
                                <button type="button" onclick="navigator.clipboard.writeText('{{ $order->verity_tracking_code }}'); this.textContent='Copied!'; setTimeout(()=>this.textContent='Copy', 1500)" class="text-xs text-blue-600 hover:text-blue-700 font-medium">Copy</button>
                            @endif
                        </p>
                    </div>
                    <span class="text-sm font-medium text-gray-600">{{ $order->effectiveStage()?->name ?? 'Pending' }}</span>
                </div>
                <x-tracking-progress-bar :order="$order" />
                @if($order->canCustomerConfirmDelivery())
                    <form method="POST" action="{{ route('dashboard.orders.confirm-delivery', $order) }}" class="mt-3">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition">
                            I received my order
                        </button>
                    </form>
                @elseif($order->effectiveStage() && (int) $order->effectiveStage()->position < 5)
                    <p class="text-sm text-gray-500 mt-3">Confirm delivery will appear when your package is dispatched or delivered.</p>
                @endif
            </div>
        @empty
            <div class="bg-white rounded-xl border border-gray-200 p-8 text-center text-gray-500">
                <p>No trackable orders yet.</p>
                <p class="text-sm mt-2">When your order is assigned to a shipment, tracking will appear here.</p>
            </div>
        @endforelse
    </div>
    <div class="mt-6">{{ $orders->links() }}</div>
</x-layouts.customer>
