<x-admin-layout>
    <div class="p-4 sm:p-6 max-w-6xl mx-auto">
        <div class="mb-4">
            <a href="{{ route('admin.dashboard') }}" class="text-green-600 hover:text-green-700 text-sm font-medium">&larr; Dashboard</a>
            <h2 class="text-xl sm:text-2xl font-bold text-gray-900 mt-2">Invoice</h2>
            <p class="text-sm text-gray-500 mt-1">Preview and settings. Logo & icons: save files in <code>public/images/invoice/</code> and <code>public/images/invoice-icons/</code>.</p>
        </div>

        @if(session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
                 class="mb-4 p-4 bg-green-100 border border-green-200 text-green-800 rounded-lg">{{ session('success') }}</div>
        @endif

        <div x-data="{ settingsOpen: false }" class="mb-6">
            <button type="button" @click="settingsOpen = !settingsOpen"
                    class="w-full flex items-center justify-between px-4 py-3 bg-gray-100 hover:bg-gray-200 rounded-lg text-left font-medium">
                <span>Settings</span>
                <span x-text="settingsOpen ? '−' : '+'"></span>
            </button>
            <div x-show="settingsOpen" x-collapse class="mt-2">
                <form method="POST" action="{{ route('admin.invoice-settings.update') }}" class="bg-white rounded-xl border border-gray-200 p-4 sm:p-6 space-y-4">
                    @csrf
                    @method('PUT')
                    <p class="text-sm text-gray-500 mb-4">Verity Gadgets · A Division of Verity Trade Global Limited · QR: veritytrade.ng/connect</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                            <input type="text" name="company_address" value="{{ old('company_address', $setting->company_address) }}" class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:ring-2 focus:ring-green-500">
                            @error('company_address')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                            <input type="text" name="company_phone" value="{{ old('company_phone', $setting->company_phone) }}" class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:ring-2 focus:ring-green-500">
                        </div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" name="company_email" value="{{ old('company_email', $setting->company_email) }}" class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:ring-2 focus:ring-green-500">
                        </div>
                        <div class="sm:col-span-2"><label class="block text-sm font-medium text-gray-700 mb-1">Copyright</label>
                            <input type="text" name="copyright" value="{{ old('copyright', $setting->copyright) }}" class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:ring-2 focus:ring-green-500">
                        </div>
                    </div>
                    <button type="submit" class="min-h-[40px] px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg text-sm">Save</button>
                </form>
            </div>
        </div>

        <div class="flex gap-3 mb-4">
            <a href="{{ route('admin.invoice-settings.preview') }}" target="_blank" rel="noopener"
               class="inline-flex items-center min-h-[44px] px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg text-sm">
                Download PDF
            </a>
            @if(!extension_loaded('gd'))
                <span class="inline-flex items-center px-4 py-2 bg-amber-100 text-amber-800 rounded-lg text-sm">Enable PHP GD in php.ini for logo & icons in PDF</span>
            @endif
        </div>

        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
            <div class="p-2 bg-gray-50 border-b text-sm text-gray-600">Preview (HTML – matches PDF layout)</div>
            <iframe src="{{ route('admin.invoice-settings.preview-html') }}?v={{ time() }}" class="w-full border-0" style="min-height: 850px; width: 100%;" title="Invoice preview"></iframe>
        </div>
    </div>
</x-admin-layout>
