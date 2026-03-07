<x-admin-layout>
    <div class="max-w-6xl mx-auto p-4 md:p-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6 mb-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex-1">
                    <h2 class="text-xl md:text-2xl font-bold text-green-800">Phone Pricing Settings</h2>
                    <p class="text-sm text-gray-500 mt-1">Logistics (CNY), exchange rate, profit margin (NGN) and rounding. Formula: (CNY + logistics) × rate + margin, then round. Only one active setting is used.</p>
                </div>
                <a href="{{ route('admin.phones.pricing-settings.create') }}" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2.5 rounded-lg font-medium whitespace-nowrap">+ Add Setting</a>
            </div>
        </div>

        @if(session('success'))
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show"
                 class="fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">{{ session('success') }}</div>
        @endif

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rate</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Logistics (CNY)</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Margin (NGN)</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rounding</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Active</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($settings as $s)
                        <tr>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ number_format($s->exchange_rate, 2) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ number_format($s->logistics_cny ?? 0, 2) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ number_format($s->profit_margin_ngn ?? 0, 2) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ number_format($s->rounding_unit) }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 text-xs rounded-full {{ $s->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">{{ $s->is_active ? 'Active' : 'Inactive' }}</span>
                            </td>
                            <td class="px-4 py-3 text-right text-sm space-x-2">
                                <a href="{{ route('admin.phones.pricing-settings.edit', $s) }}" class="text-green-600 hover:text-green-800">Edit</a>
                                <form action="{{ route('admin.phones.pricing-settings.destroy', $s) }}" method="POST" class="inline" onsubmit="return confirm('Delete this setting?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">No pricing settings. Add one to enable NGN pricing.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <p class="mt-4 text-sm text-gray-500">
            <a href="{{ route('admin.phones.brands.index') }}" class="text-green-600 hover:text-green-800">← Back to Brands</a>
        </p>
    </div>
</x-admin-layout>
