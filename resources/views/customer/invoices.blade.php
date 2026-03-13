<x-layouts.customer>
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <h1 class="text-xl font-bold text-gray-800 mb-4">My Invoices</h1>
        @forelse($invoices as $invoice)
            <div class="py-3 border-b border-gray-100 last:border-b-0 flex items-center justify-between text-sm">
                <div>
                    <div class="font-semibold text-gray-800">{{ $invoice->invoice_number }}</div>
                    <div class="text-gray-500">{{ optional($invoice->created_at)->format('M d, Y h:i A') }}</div>
                </div>
                <div class="text-right">
                    <div class="font-semibold text-green-700">NGN {{ number_format((float) $invoice->amount) }}</div>
                    @if($invoice->pdf_path)
                        <a href="{{ route('dashboard.invoices.download', $invoice) }}" class="text-blue-600 hover:underline">Download</a>
                    @else
                        <span class="text-gray-400">No PDF</span>
                    @endif
                </div>
            </div>
        @empty
            <p class="text-sm text-gray-500">No invoices found.</p>
        @endforelse
    </div>

    <div class="mt-4">{{ $invoices->links() }}</div>
</x-layouts.customer>
