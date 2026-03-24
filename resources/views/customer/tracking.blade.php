<x-layouts.customer>
    <h1 class="text-xl sm:text-2xl font-bold text-gray-800 mb-4">Order Tracking</h1>
    <div class="space-y-4">
        @forelse($orders as $order)
            <div id="order-{{ $order->id }}" class="bg-white rounded-xl border p-4 sm:p-5 shadow-sm {{ (($highlightOrderId ?? 0) === $order->id) ? 'border-blue-300 bg-blue-50/40' : 'border-gray-200' }}">
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                    <div>
                        <h2 class="font-bold text-gray-900">{{ $order->product_name ?? 'Order #'.$order->id }}</h2>
                        @if($order->spec_summary)
                            <p class="text-sm text-gray-600 mt-0.5">{{ $order->spec_summary }}</p>
                        @endif
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
