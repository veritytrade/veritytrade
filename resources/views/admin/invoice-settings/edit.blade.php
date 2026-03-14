<x-admin-layout>
    <div class="p-4 sm:p-6 max-w-6xl mx-auto">
        <div class="mb-4">
            <a href="{{ route('admin.dashboard') }}" class="text-green-600 hover:text-green-700 text-sm font-medium">&larr; Dashboard</a>
            <h2 class="text-xl sm:text-2xl font-bold text-gray-900 mt-2">Invoice</h2>
            <p class="text-sm text-gray-500 mt-1">Generate invoices and preview. Enter a customer email to preview their orders before generating.</p>
        </div>

        @if(session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
                 class="mb-4 p-4 bg-green-100 border border-green-200 text-green-800 rounded-lg">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
                 class="mb-4 p-4 bg-red-100 border border-red-200 text-red-800 rounded-lg">{{ session('error') }}</div>
        @endif

        {{-- Customer email lookup --}}
        <form method="GET" action="{{ route('admin.invoice-settings.edit') }}" class="mb-6">
            <div class="flex gap-3">
                <input type="email" name="email" value="{{ $email }}" placeholder="Customer email to preview & generate"
                    class="flex-1 rounded-lg border border-gray-300 px-4 py-2 min-h-[44px] focus:ring-2 focus:ring-green-500">
                <button type="submit" class="min-h-[44px] px-6 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg">Lookup</button>
            </div>
        </form>

        {{-- Customer shipments & generate --}}
        @if($user)
            <div class="bg-white rounded-xl border border-gray-200 p-4 sm:p-6 mb-6">
                <h3 class="font-bold text-gray-800 mb-2">{{ $user->name }} ({{ $user->email }})</h3>
                @if($shipments->isEmpty())
                    <p class="text-sm text-gray-500">No shipments with orders found for this customer.</p>
                @else
                    <div class="space-y-4">
                        @foreach($shipments as $shipment)
                            @php
                                $orderCount = $shipment->orders->count();
                                $hasInvoice = $orderCount > 0 && $shipment->orders->first()->invoice_id;
                                $invoiceNumber = $hasInvoice ? $shipment->orders->first()->invoice->invoice_number ?? null : null;
                                $uninvoicedCount = $shipment->orders->filter(fn($o) => !$o->invoice_id)->count();
                            @endphp
                            @if($orderCount > 0)
                                @php $isSelected = $selectedShipmentId && (int) $selectedShipmentId === (int) $shipment->id; @endphp
                                <div class="flex flex-wrap items-center justify-between gap-3 p-4 rounded-lg {{ $isSelected ? 'bg-green-50 border-2 border-green-300' : 'bg-gray-50' }}">
                                    <div>
                                        <span class="font-medium">{{ $shipment->chinese_tracking_code ?? 'Shipment #'.$shipment->id }}</span>
                                        <span class="text-sm text-gray-500 ml-2">({{ $shipment->logistics_company ?? '—' }})</span>
                                        <p class="text-sm text-gray-600 mt-1">
                                            @if($hasInvoice)
                                                Invoice {{ $invoiceNumber }} · Regenerate to update PDF
                                            @else
                                                {{ $uninvoicedCount }} uninvoiced order{{ $uninvoicedCount !== 1 ? 's' : '' }}
                                            @endif
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('admin.invoice-settings.edit', ['email' => $user->email, 'shipment_id' => $shipment->id]) }}"
                                           class="min-h-[40px] px-4 py-2 {{ $isSelected ? 'bg-green-600 hover:bg-green-700' : 'bg-gray-600 hover:bg-gray-700' }} text-white font-medium rounded-lg text-sm">
                                            {{ $isSelected ? 'Previewing' : 'Preview' }}
                                        </a>
                                        <form method="POST" action="{{ route('admin.invoice-settings.generate-for-shipment') }}" class="inline">
                                            @csrf
                                            <input type="hidden" name="shipment_id" value="{{ $shipment->id }}">
                                            <input type="hidden" name="user_id" value="{{ $user->id }}">
                                            <button type="submit" class="min-h-[40px] px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg text-sm">
                                                {{ $hasInvoice ? 'Regenerate Invoice' : 'Generate Invoice' }}
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>
        @endif

        {{-- Pending invoice requests --}}
        @if($pendingRequests->isNotEmpty())
            <div class="bg-white rounded-xl border border-gray-200 p-4 sm:p-6 mb-6">
                <h3 class="font-bold text-gray-800 mb-4">Pending Invoice Requests</h3>
                <div class="space-y-3">
                    @foreach($pendingRequests as $req)
                        <div class="flex items-center justify-between p-3 bg-amber-50 rounded-lg">
                            <div>
                                <span class="font-medium">{{ $req->user->name ?? '—' }}</span>
                                <span class="text-sm text-gray-600">({{ $req->user->email ?? '—' }})</span>
                                <p class="text-sm text-gray-500">{{ $req->shipment->chinese_tracking_code ?? 'Shipment' }}</p>
                            </div>
                            <a href="{{ route('admin.invoice-settings.edit', ['email' => $req->user->email ?? '', 'shipment_id' => $req->shipment_id ?? '']) }}" class="min-h-[36px] px-3 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg text-sm">Generate</a>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Preview section --}}
        @php
            $previewUrl = route('admin.invoice-settings.preview-html');
            if ($user && $selectedShipmentId) {
                $previewUrl .= '?user_id=' . $user->id . '&shipment_id=' . $selectedShipmentId;
            }
            $previewUrl .= (str_contains($previewUrl, '?') ? '&' : '?') . 'v=' . time();
            $pdfUrl = route('admin.invoice-settings.preview');
            if ($user && $selectedShipmentId) {
                $pdfUrl .= '?user_id=' . $user->id . '&shipment_id=' . $selectedShipmentId;
            }
        @endphp
        <div class="flex gap-3 mb-4">
            <a href="{{ $pdfUrl }}" target="_blank" rel="noopener"
               class="inline-flex items-center min-h-[44px] px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg text-sm">
                Download PDF {{ $user && $selectedShipmentId ? '(customer data)' : '(sample)' }}
            </a>
            @if(!extension_loaded('gd'))
                <span class="inline-flex items-center px-4 py-2 bg-amber-100 text-amber-800 rounded-lg text-sm">Enable PHP GD in php.ini for logo & icons in PDF</span>
            @endif
        </div>

        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
            <div class="p-2 bg-gray-50 border-b text-sm text-gray-600">
                Preview {{ $user && $selectedShipmentId ? '(real customer data – matches what will be generated)' : '(sample data – enter a customer email and select a shipment for real preview)' }}
            </div>
            <iframe src="{{ $previewUrl }}" class="w-full border-0" style="min-height: 850px; width: 100%;" title="Invoice preview"></iframe>
        </div>
    </div>
</x-admin-layout>
