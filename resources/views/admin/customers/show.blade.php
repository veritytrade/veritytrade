<x-admin-layout>
    <div class="max-w-7xl mx-auto p-4 md:p-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6 mb-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <div>
                    <h2 class="text-xl md:text-2xl font-bold text-blue-700">Customer 360</h2>
                    <p class="text-sm text-gray-500 mt-1">Search by email or WhatsApp name to see a full view of this customer.</p>
                </div>
            </div>

            <form method="GET" action="{{ route('admin.customers.show') }}" class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-3 text-sm">
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Customer email or WhatsApp name</label>
                    <input type="text"
                           name="q"
                           value="{{ $query ?? '' }}"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500"
                           placeholder="e.g. customer@example.com or WhatsApp name">
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit"
                            class="px-4 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white font-medium min-h-[40px] w-full md:w-auto">
                        Search
                    </button>
                    <a href="{{ route('admin.customers.show') }}"
                       class="px-3 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 min-h-[40px] w-full md:w-auto text-center">
                        Reset
                    </a>
                </div>
            </form>

            @if(!$user && !empty($query))
                <p class="mt-4 text-sm text-red-600">No customer found matching "{{ $query }}".</p>
            @endif
        </div>

        @if($user)
            {{-- Profile summary --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3">Profile</h3>
                    <dl class="text-sm text-gray-800 space-y-1.5">
                        <div>
                            <dt class="font-medium text-gray-600">WhatsApp Name (Username)</dt>
                            <dd>{{ $user->username ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-600">Full Name (Invoice)</dt>
                            <dd>{{ $user->name }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-600">Email</dt>
                            <dd>{{ $user->email }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-600">Phone</dt>
                            <dd>{{ $user->getDisplayPhone() ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-600">Location</dt>
                            <dd>{{ $user->getDisplayCity() ?: '-' }}, {{ $user->getDisplayState() ?: '-' }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-600">Approved</dt>
                            <dd>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $user->is_approved ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                    {{ $user->is_approved ? 'YES' : 'NO' }}
                                </span>
                            </dd>
                        </div>
                    </dl>
                </div>

                {{-- Quick stats --}}
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 lg:col-span-2">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3">Overview</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                        <div class="p-3 rounded-lg bg-gray-50 border border-gray-200">
                            <div class="text-xs text-gray-500">Total Orders</div>
                            <div class="mt-1 text-lg font-semibold text-gray-900">{{ $orders->total() }}</div>
                        </div>
                        <div class="p-3 rounded-lg bg-gray-50 border border-gray-200">
                            <div class="text-xs text-gray-500">Shipments</div>
                            <div class="mt-1 text-lg font-semibold text-gray-900">{{ $shipments->count() }}</div>
                        </div>
                        <div class="p-3 rounded-lg bg-gray-50 border border-gray-200">
                            <div class="text-xs text-gray-500">Invoices</div>
                            <div class="mt-1 text-lg font-semibold text-gray-900">{{ $invoices->count() }}</div>
                        </div>
                        <div class="p-3 rounded-lg bg-gray-50 border border-gray-200">
                            <div class="text-xs text-gray-500">Outstanding Balance (approx)</div>
                            <div class="mt-1 text-sm font-semibold text-gray-900">
                                ₦{{ number_format((float) $approxOutstanding, 2) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Orders list --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
                <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-700">Orders</h3>
                    <span class="text-xs text-gray-500">Newest first</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="p-3 text-left font-semibold text-gray-700">Order</th>
                                <th class="p-3 text-left font-semibold text-gray-700">Status</th>
                                <th class="p-3 text-left font-semibold text-gray-700">Logistics</th>
                                <th class="p-3 text-right font-semibold text-gray-700">Amount (NGN)</th>
                                <th class="p-3 text-center font-semibold text-gray-700">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($orders as $order)
                                <tr class="hover:bg-gray-50">
                                    <td class="p-3">
                                        <div class="font-medium text-gray-800">
                                            {{ Str::limit($order->product_name ?? 'Order #'.$order->id, 30) }}
                                        </div>
                                        <div class="text-xs text-gray-500">ID: {{ $order->id }}</div>
                                    </td>
                                    <td class="p-3 text-sm text-gray-700">
                                        {{ $order->customer_status_label }}
                                    </td>
                                    <td class="p-3 text-sm text-gray-700">
                                        @if($order->shipment)
                                            <div>{{ $order->shipment->chinese_tracking_code }}</div>
                                            <div class="text-xs text-gray-500">{{ $order->shipment->logistics_company }}</div>
                                        @else
                                            <span class="text-xs text-gray-400">Unassigned</span>
                                        @endif
                                    </td>
                                    <td class="p-3 text-right text-sm text-gray-700">
                                        ₦{{ number_format((float) ($order->total_amount_ngn ?? 0), 2) }}
                                    </td>
                                    <td class="p-3 text-center">
                                        <a href="{{ route('admin.orders.show', $order) }}"
                                           class="inline-flex items-center justify-center px-3 py-1.5 rounded-lg bg-green-600 hover:bg-green-700 text-white text-xs font-medium min-h-[32px]">
                                            View Order
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="p-4 text-center text-sm text-gray-500">No orders for this customer.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Shipments & Invoices side by side --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-gray-700">Shipments (from orders)</h3>
                        <span class="text-xs text-gray-500">{{ $shipments->count() }} unique</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="p-3 text-left font-semibold text-gray-700">Chinese Code</th>
                                    <th class="p-3 text-left font-semibold text-gray-700">Logistics</th>
                                    <th class="p-3 text-left font-semibold text-gray-700">Stage</th>
                                    <th class="p-3 text-center font-semibold text-gray-700">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($shipments as $shipment)
                                    <tr class="hover:bg-gray-50">
                                        <td class="p-3 text-sm text-gray-800">
                                            <a href="{{ route('admin.shipments.show', $shipment) }}"
                                               class="text-green-600 hover:text-green-700">
                                                {{ $shipment->chinese_tracking_code }}
                                            </a>
                                        </td>
                                        <td class="p-3 text-sm text-gray-700">
                                            {{ $shipment->logistics_company }}
                                        </td>
                                        <td class="p-3 text-sm text-gray-700">
                                            {{ $shipment->currentStage?->name ?? '—' }}
                                        </td>
                                        <td class="p-3 text-center">
                                            <a href="{{ route('admin.shipments.show', $shipment) }}"
                                               class="inline-flex items-center justify-center px-3 py-1.5 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-800 text-xs font-medium min-h-[32px]">
                                                Open Shipment
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="p-4 text-center text-sm text-gray-500">No shipments linked.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-gray-700">Invoices</h3>
                        <span class="text-xs text-gray-500">{{ $invoices->count() }} total</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="p-3 text-left font-semibold text-gray-700">Invoice</th>
                                    <th class="p-3 text-left font-semibold text-gray-700">Amount</th>
                                    <th class="p-3 text-left font-semibold text-gray-700">Created</th>
                                    <th class="p-3 text-center font-semibold text-gray-700">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($invoices as $invoice)
                                    <tr class="hover:bg-gray-50">
                                        <td class="p-3 text-sm text-gray-800">
                                            {{ $invoice->invoice_number }}
                                        </td>
                                        <td class="p-3 text-sm text-gray-700">
                                            ₦{{ number_format((float) ($invoice->amount ?? 0), 2) }}
                                        </td>
                                        <td class="p-3 text-sm text-gray-700">
                                            {{ optional($invoice->created_at)->format('d M Y') ?? '—' }}
                                        </td>
                                        <td class="p-3 text-center">
                                            <a href="{{ route('admin.orders.index', ['invoice' => $invoice->invoice_number]) }}"
                                               class="inline-flex items-center justify-center px-3 py-1.5 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-800 text-xs font-medium min-h-[32px]">
                                                View Related Orders
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="p-4 text-center text-sm text-gray-500">No invoices for this customer.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-admin-layout>

