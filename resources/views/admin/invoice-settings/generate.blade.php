<x-admin-layout>
<div class="p-4 sm:p-6 max-w-4xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.invoice-settings.edit') }}" class="text-green-600 hover:text-green-700 text-sm font-medium">← Invoice Settings</a>
        <h2 class="text-xl sm:text-2xl font-bold text-gray-900 mt-2">Generate Invoice</h2>
        <p class="text-sm text-gray-500 mt-1">Enter customer email to find uninvoiced shipments. Or use a pending request below.</p>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-200 text-green-800 rounded-lg">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-4 bg-red-100 border border-red-200 text-red-800 rounded-lg">{{ session('error') }}</div>
    @endif

    <form method="GET" action="{{ route('admin.invoice-settings.generate') }}" class="mb-8">
        <div class="flex gap-3">
            <input type="email" name="email" value="{{ $email }}" placeholder="Customer email" required
                class="flex-1 rounded-lg border border-gray-300 px-4 py-2 min-h-[44px] focus:ring-2 focus:ring-green-500">
            <button type="submit" class="min-h-[44px] px-6 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg">Lookup</button>
        </div>
    </form>

    @if($user)
        <div class="bg-white rounded-xl border border-gray-200 p-4 sm:p-6 mb-6">
            <h3 class="font-bold text-gray-800 mb-2">{{ $user->name }} ({{ $user->email }})</h3>
            @if($shipments->isEmpty())
                <p class="text-sm text-gray-500">No uninvoiced shipments found.</p>
            @else
                <div class="space-y-4">
                    @foreach($shipments as $shipment)
                        @php $count = $shipment->orders->count(); @endphp
                        @if($count > 0)
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                <div>
                                    <span class="font-medium">{{ $shipment->chinese_tracking_code ?? 'Shipment #'.$shipment->id }}</span>
                                    <span class="text-sm text-gray-500 ml-2">({{ $shipment->logistics_company ?? '—' }})</span>
                                    <p class="text-sm text-gray-600 mt-1">{{ $count }} uninvoiced order{{ $count > 1 ? 's' : '' }}</p>
                                </div>
                                <form method="POST" action="{{ route('admin.invoice-settings.generate-for-shipment') }}" class="inline">
                                    @csrf
                                    <input type="hidden" name="shipment_id" value="{{ $shipment->id }}">
                                    <input type="hidden" name="user_id" value="{{ $user->id }}">
                                    <button type="submit" class="min-h-[40px] px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg text-sm">Generate Invoice</button>
                                </form>
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    @if($pendingRequests->isNotEmpty())
        <div class="bg-white rounded-xl border border-gray-200 p-4 sm:p-6">
            <h3 class="font-bold text-gray-800 mb-4">Pending Invoice Requests</h3>
            <div class="space-y-3">
                @foreach($pendingRequests as $req)
                    <div class="flex items-center justify-between p-3 bg-amber-50 rounded-lg">
                        <div>
                            <span class="font-medium">{{ $req->user->name ?? '—' }}</span>
                            <span class="text-sm text-gray-600">({{ $req->user->email ?? '—' }})</span>
                            <p class="text-sm text-gray-500">{{ $req->shipment->chinese_tracking_code ?? 'Shipment' }}</p>
                        </div>
                        <a href="{{ route('admin.invoice-settings.generate', ['email' => $req->user->email ?? '']) }}" class="min-h-[36px] px-3 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg text-sm">Generate</a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
</x-admin-layout>
