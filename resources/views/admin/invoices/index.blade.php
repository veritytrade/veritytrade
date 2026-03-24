<x-admin-layout>
    <div class="p-4 sm:p-6 max-w-6xl mx-auto">
        <nav class="mb-3 text-xs text-gray-500">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-green-700">Dashboard</a>
            <span class="mx-1">/</span>
            <span class="text-gray-700 font-medium">Invoices</span>
        </nav>
        <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <a href="{{ route('admin.dashboard') }}" class="text-green-600 hover:text-green-700 text-sm font-medium">&larr; Dashboard</a>
                <h2 class="text-xl sm:text-2xl font-bold text-gray-900 mt-2">Invoices</h2>
                <p class="text-sm text-gray-500 mt-1">Overview of all generated invoices per shipment, similar to big platforms.</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.invoice-settings.edit') }}"
                   class="min-h-[40px] px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg">
                    Go to Invoice Settings
                </a>
            </div>
        </div>

        {{-- Filters --}}
        <form method="GET" action="{{ route('admin.invoices.index') }}" class="mb-4 bg-white rounded-xl border border-gray-200 p-4 sm:p-5">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Customer email</label>
                    <input type="email" name="email" value="{{ $customerEmail }}" placeholder="e.g. customer@example.com"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Invoice number</label>
                    <input type="text" name="invoice_number" value="{{ $invoiceNumber }}" placeholder="e.g. VG-202603-0003"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500">
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit"
                            class="min-h-[40px] px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg">
                        Filter
                    </button>
                    <a href="{{ route('admin.invoices.index') }}"
                       class="min-h-[40px] px-3 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50">
                        Reset
                    </a>
                </div>
            </div>
        </form>

        {{-- Invoices table --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
            <div class="px-4 py-2 bg-gray-50 border-b text-sm text-gray-600 flex justify-between items-center">
                <span>Invoices ({{ $invoices->total() }})</span>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left font-semibold text-gray-700">Invoice</th>
                            <th class="px-4 py-2 text-left font-semibold text-gray-700">Customer</th>
                            <th class="px-4 py-2 text-left font-semibold text-gray-700">Shipments</th>
                            <th class="px-4 py-2 text-right font-semibold text-gray-700">Amount (NGN)</th>
                            <th class="px-4 py-2 text-left font-semibold text-gray-700">Created</th>
                            <th class="px-4 py-2 text-center font-semibold text-gray-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($invoices as $invoice)
                            @php
                                $shipmentCodes = $invoice->orders
                                    ->filter(fn($o) => $o->shipment && $o->shipment->chinese_tracking_code)
                                    ->pluck('shipment.chinese_tracking_code')
                                    ->unique()
                                    ->values()
                                    ->all();
                            @endphp
                            <tr>
                                <td class="px-4 py-2 align-top">
                                    <div class="font-semibold text-gray-900">{{ $invoice->invoice_number }}</div>
                                    <div class="text-xs text-gray-500">ID: {{ $invoice->id }}</div>
                                </td>
                                <td class="px-4 py-2 align-top">
                                    <div class="text-gray-900">{{ $invoice->user?->username ?? $invoice->user?->name ?? '—' }}</div>
                                    <div class="text-xs text-gray-500">{{ $invoice->user?->email ?? '—' }}</div>
                                </td>
                                <td class="px-4 py-2 align-top">
                                    @if(!empty($shipmentCodes))
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($shipmentCodes as $code)
                                                <span class="inline-flex px-2 py-0.5 rounded-full bg-gray-100 text-gray-700 text-xs">
                                                    {{ $code }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-400">No shipments linked</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 align-top text-right">
                                    <span class="font-semibold text-gray-900">
                                        &#8358;{{ number_format((float) ($invoice->amount ?? 0), 2) }}
                                    </span>
                                </td>
                                <td class="px-4 py-2 align-top">
                                    <div class="text-gray-900">
                                        {{ optional($invoice->created_at)->format('d M Y') ?? '—' }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ optional($invoice->created_at)->format('H:i') ?? '' }}
                                    </div>
                                </td>
                                <td class="px-4 py-2 align-top text-center">
                                    <div class="flex flex-col sm:flex-row justify-center gap-2">
                                        <a href="{{ route('admin.orders.index', ['invoice_id' => $invoice->id]) }}"
                                           class="px-2 py-1 rounded-lg border border-gray-300 text-gray-700 text-xs font-medium hover:bg-gray-50">
                                            View Orders
                                        </a>
                                        @if($invoice->user)
                                            <a href="{{ route('admin.invoice-settings.edit', ['email' => $invoice->user->email]) }}"
                                               class="px-2 py-1 rounded-lg border border-blue-500 text-blue-600 text-xs font-medium hover:bg-blue-50">
                                                Open in Invoice Settings
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-6 text-center text-sm text-gray-500">
                                    No invoices found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t bg-gray-50">
                {{ $invoices->links() }}
            </div>
        </div>
    </div>
</x-admin-layout>

