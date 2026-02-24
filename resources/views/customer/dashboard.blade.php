<x-layouts.customer>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <div class="text-sm text-gray-500">Orders</div>
            <div class="text-2xl font-bold text-blue-700">{{ $orders->count() }}</div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <div class="text-sm text-gray-500">Invoices</div>
            <div class="text-2xl font-bold text-green-700">{{ $invoices->count() }}</div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <div class="text-sm text-gray-500">Tracking Updates</div>
            <div class="text-2xl font-bold text-indigo-700">{{ $trackingEvents->count() }}</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <section class="bg-white rounded-xl border border-gray-200 p-4">
            <h2 class="font-bold text-gray-800 mb-3">Recent Orders</h2>
            @forelse($orders as $order)
                <div class="py-2 border-b border-gray-100 last:border-b-0 text-sm flex justify-between">
                    <span>#{{ $order->uuid ?? $order->id }}</span>
                    <span class="font-semibold">{{ ucfirst($order->status) }}</span>
                </div>
            @empty
                <p class="text-sm text-gray-500">No orders yet.</p>
            @endforelse
        </section>

        <section class="bg-white rounded-xl border border-gray-200 p-4">
            <h2 class="font-bold text-gray-800 mb-3">Recent Tracking</h2>
            @forelse($trackingEvents as $event)
                <div class="py-2 border-b border-gray-100 last:border-b-0 text-sm">
                    <div class="font-semibold text-gray-800">{{ $event->status_label }}</div>
                    <div class="text-gray-500">{{ optional($event->event_time)->format('M d, Y h:i A') }}</div>
                </div>
            @empty
                <p class="text-sm text-gray-500">No tracking updates yet.</p>
            @endforelse
        </section>
    </div>
</x-layouts.customer>
