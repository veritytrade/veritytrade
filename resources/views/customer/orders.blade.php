<x-layouts.customer>
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <h1 class="text-xl font-bold text-gray-800 mb-4">My Orders</h1>
        @forelse($orders as $order)
            <div class="py-3 border-b border-gray-100 last:border-b-0 flex items-center justify-between text-sm">
                <div>
                    <div class="font-semibold text-gray-800">Order #{{ $order->uuid ?? $order->id }}</div>
                    <div class="text-gray-500">{{ optional($order->created_at)->format('M d, Y h:i A') }}</div>
                </div>
                <div class="text-right">
                    <div class="font-semibold text-blue-700">{{ ucfirst($order->status) }}</div>
                    <div class="text-gray-600">NGN {{ number_format((float) $order->total_amount_ngn) }}</div>
                </div>
            </div>
        @empty
            <p class="text-sm text-gray-500">No orders found.</p>
        @endforelse
    </div>

    <div class="mt-4">{{ $orders->links() }}</div>
</x-layouts.customer>
