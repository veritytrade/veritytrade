<x-admin-layout>
<div class="max-w-2xl mx-auto p-4 sm:p-6">
    <nav class="mb-3 text-xs text-gray-500">
        <a href="{{ route('admin.dashboard') }}" class="hover:text-green-700">Dashboard</a>
        <span class="mx-1">/</span>
        <a href="{{ route('admin.orders.index') }}" class="hover:text-green-700">Orders</a>
        <span class="mx-1">/</span>
        <span class="text-gray-700 font-medium">Order #{{ $order->id }}</span>
    </nav>
    <div class="mb-6">
        <h2 class="text-xl sm:text-2xl font-bold text-gray-900">Order #{{ $order->id }}</h2>
        @if($order->invoice)
            <p class="text-sm text-gray-600 mt-1">Invoice: {{ $order->invoice->invoice_number }}</p>
        @endif
        <p class="text-xs text-gray-400 mt-1">Last updated: {{ optional($order->updated_at)->format('d M Y H:i') ?? '—' }}</p>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-4 sm:p-6 mb-6">
        <h3 class="font-bold text-gray-800 mb-4">Order Info</h3>
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
            <dt class="text-gray-500">Customer</dt>
            <dd class="font-medium text-gray-800">{{ $order->user?->username ?? $order->user?->name ?? '—' }}</dd>
            <dt class="text-gray-500">Product</dt>
            <dd class="font-medium text-gray-800">{{ $order->product_name ?? '—' }}</dd>
            <dt class="text-gray-500">Spec</dt>
            <dd class="font-medium text-gray-800">{{ $order->spec_summary ?? '—' }}</dd>
            <dt class="text-gray-500">Amount</dt>
            <dd class="font-medium text-gray-800">₦{{ number_format($order->total_amount_ngn ?? 0) }}</dd>
            <dt class="text-gray-500">Payment</dt>
            <dd class="font-medium text-gray-800">{{ ucfirst($order->payment_status ?? 'pending') }}</dd>
            <dt class="text-gray-500">Status</dt>
            <dd class="font-medium text-gray-800">
                <span class="px-2 py-0.5 rounded {{ $order->status === 'pending_approval' ? 'bg-amber-100 text-amber-800' : '' }}">{{ ucfirst($order->status ?? 'pending') }}</span>
                @if($order->status === 'pending_approval' && auth()->user()->hasPermission('approve_orders'))
                    <form method="POST" action="{{ route('admin.orders.approve', $order) }}" class="inline ml-2">
                        @csrf
                        <button type="submit" class="inline-flex items-center justify-center min-h-[44px] px-6 py-2.5 bg-green-600 hover:bg-green-700 text-white font-bold rounded-xl shadow-md hover:shadow-lg transition-all">✓ Approve Order</button>
                    </form>
                @endif
            </dd>
            <dt class="text-gray-500">Shipment</dt>
            <dd class="font-medium text-gray-800">{{ $order->shipment ? $order->shipment->logistics_company . ' (' . \Illuminate\Support\Str::limit($order->shipment->chinese_tracking_code, 8) . ')' : 'Not assigned' }}</dd>
            <dt class="text-gray-500">Supplier Platform</dt>
            <dd class="font-medium text-gray-800">
                @php($supplierPlatforms = \App\Models\Order::supplierPlatforms())
                {{ $order->supplier_platform ? ($supplierPlatforms[$order->supplier_platform] ?? ucfirst($order->supplier_platform)) : '—' }}
            </dd>
            <dt class="text-gray-500">Supplier Order Number</dt>
            <dd class="font-medium text-gray-800">{{ $order->supplier_order_number ?: '—' }}</dd>
            <dt class="text-gray-500">Supplier Logistics Code</dt>
            <dd class="font-medium text-gray-800">{{ $order->supplier_logistics_code ?: '—' }}</dd>
            <dt class="text-gray-500">Mapping Status</dt>
            <dd class="font-medium text-gray-800">{{ $order->mapping_status ? ucfirst(str_replace('_', ' ', $order->mapping_status)) : '—' }}</dd>
            @if($order->invoiceRequest?->isPending())
                <dt class="text-gray-500">Invoice</dt>
                <dd class="font-medium text-amber-700">Requested by customer</dd>
            @endif
            <dt class="text-gray-500">Current Stage</dt>
            <dd class="font-medium text-gray-800">{{ $order->effectiveStage()?->name ?? '—' }}</dd>
            @if($order->full_description)
                <dt class="text-gray-500">Full description</dt>
                <dd class="font-medium text-gray-800 whitespace-pre-wrap text-sm">{{ $order->full_description }}</dd>
            @endif
            @if(isset($order->outstanding_balance_ngn) && $order->outstanding_balance_ngn > 0)
                <dt class="text-gray-500">Outstanding</dt>
                <dd class="font-medium text-gray-800">₦{{ number_format($order->outstanding_balance_ngn) }}</dd>
            @endif
        </dl>
        @if($order->paymentSlips->isNotEmpty())
            <div class="mt-4">
                <h4 class="text-sm font-semibold text-gray-700 mb-2">Payment slips</h4>
                <div class="flex flex-wrap gap-2">
                    @foreach($order->paymentSlips as $slip)
                        <a href="{{ storage_asset($slip->file_path) }}" target="_blank" rel="noopener"
                           class="text-sm text-green-600 hover:text-green-700">{{ $slip->original_name ?: 'Slip' }}</a>
                    @endforeach
                </div>
            </div>
        @endif

        @if(auth()->user()->hasPermission('assign_shipment') && $order->status !== 'pending_approval')
            <div class="mt-6 pt-4 border-t border-gray-100">
                <form method="POST" action="{{ route('admin.orders.assign-shipment', $order) }}" class="flex flex-col sm:flex-row gap-3">
                    @csrf
                    <select name="shipment_id" class="flex-1 rounded-lg border border-gray-300 px-4 py-3 min-h-[48px]">
                        <option value="">Unassign</option>
                        @foreach($shipments as $s)
                            <option value="{{ $s->id }}" {{ $order->shipment_id == $s->id ? 'selected' : '' }}>{{ $s->chinese_tracking_code }} - {{ $s->logistics_company }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="min-h-[48px] px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg">Assign Shipment</button>
                </form>
            </div>
        @endif
        @if(auth()->user()->hasPermission('override_order_stage') && $order->status !== 'pending_approval')
            <div class="mt-4">
                <form method="POST" action="{{ route('admin.orders.override-stage', $order) }}" class="flex flex-col sm:flex-row gap-3">
                    @csrf
                    <select name="current_stage_id" class="flex-1 rounded-lg border border-gray-300 px-4 py-3 min-h-[48px]">
                        <option value="">Inherit from shipment</option>
                        @foreach($stages as $stage)
                            <option value="{{ $stage->id }}" {{ $order->current_stage_id == $stage->id ? 'selected' : '' }}>{{ $stage->name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="min-h-[48px] px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg">Override Stage</button>
                </form>
            </div>
        @endif
    </div>

    <div class="flex flex-wrap gap-3 mt-4">
        <a href="{{ route('admin.orders.edit', $order) }}" class="inline-flex items-center min-h-[44px] px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg">Edit Order</a>
        @if(auth()->user()->hasPermission('generate_invoices') && $uninvoicedOrders->isNotEmpty())
            <form method="POST" action="{{ route('admin.orders.generate-invoice', $order) }}" class="inline">
                @csrf
                <button type="submit" class="inline-flex items-center min-h-[44px] px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg">Generate Invoice ({{ $uninvoicedOrders->count() }} item{{ $uninvoicedOrders->count() > 1 ? 's' : '' }})</button>
            </form>
        @elseif($order->invoice)
            <a href="{{ route('admin.orders.invoice-download', $order) }}" target="_blank" rel="noopener" class="inline-flex items-center min-h-[44px] px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg">Download Invoice</a>
        @endif
        <form method="POST" action="{{ route('admin.orders.destroy', $order) }}" class="inline" onsubmit="return confirm('Delete this order? This cannot be undone.');">
            @csrf
            @method('DELETE')
            <button type="submit" class="inline-flex items-center min-h-[44px] px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg">Delete Order</button>
        </form>
    </div>
</div>
</x-admin-layout>
