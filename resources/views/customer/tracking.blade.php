<x-layouts.customer>
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <h1 class="text-xl font-bold text-gray-800 mb-4">Tracking Updates</h1>
        @forelse($trackingEvents as $event)
            <div class="py-3 border-b border-gray-100 last:border-b-0 text-sm">
                <div class="font-semibold text-gray-800">{{ $event->status_label }}</div>
                <div class="text-gray-600">Order ID: {{ $event->order_id }}</div>
                <div class="text-gray-500">{{ optional($event->event_time)->format('M d, Y h:i A') }}</div>
                @if($event->description)
                    <div class="text-gray-600 mt-1">{{ $event->description }}</div>
                @endif
            </div>
        @empty
            <p class="text-sm text-gray-500">No tracking updates available.</p>
        @endforelse
    </div>

    <div class="mt-4">{{ $trackingEvents->links() }}</div>
</x-layouts.customer>
