<x-admin-layout>
    <div class="p-6">
        <h2 class="text-2xl font-bold text-blue-700 mb-6">
            Site Settings
        </h2>

        @if(session('success'))
            <div x-data="{ show: true }" 
                x-init="setTimeout(() => show = false, 3000)"
                x-show="show"
                class="fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 transition-opacity">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div x-data="{ show: true }" 
                x-init="setTimeout(() => show = false, 5000)"
                x-show="show"
                class="fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 transition-opacity">
                {{ session('error') }}
            </div>
        @endif

        <div class="bg-white rounded shadow overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-blue-600 text-white">
                    <tr>
                        <th class="p-3 text-left">Setting</th>
                        <th class="p-3 text-left">Description</th>
                        <th class="p-3 text-center">Status</th>
                        <th class="p-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($settings as $setting)
                    <tr class="border-t hover:bg-gray-50">
                        <td class="p-3 font-medium">{{ $setting->label }}</td>
                        <td class="p-3 text-gray-600">{{ $setting->description }}</td>
                        <td class="p-3 text-center">
                            @if($setting->value)
                                <span class="bg-green-600 text-white px-3 py-1 rounded text-xs">ENABLED</span>
                            @else
                                <span class="bg-gray-400 text-white px-3 py-1 rounded text-xs">DISABLED</span>
                            @endif
                        </td>
                        <td class="p-3 text-center">
                            <form method="POST" action="{{ route('admin.settings.update', $setting) }}">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-xs">
                                    {{ $setting->value ? 'Disable' : 'Enable' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6 bg-blue-50 p-4 rounded">
            <h3 class="font-semibold mb-2">How Settings Work</h3>
            <p class="text-sm text-gray-700">
                • All settings take effect immediately — no deployment needed<br>
                • Changes apply site-wide within 1 minute (cache refresh)<br>
                • Admin panel always has full access regardless of settings
            </p>
        </div>
    </div>
</x-admin-layout>