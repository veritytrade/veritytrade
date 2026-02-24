<x-admin-layout>
    <div class="max-w-7xl mx-auto p-4 md:p-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6 mb-6">
            <h2 class="text-xl md:text-2xl font-bold text-blue-700">Role Permission Management</h2>
            <p class="text-sm text-gray-500 mt-1">Define what each role can access in the admin dashboard.</p>
        </div>

        @if(session('success'))
            <div class="mb-4 rounded-lg bg-green-100 text-green-800 px-4 py-3 text-sm">{{ session('success') }}</div>
        @endif

        <div class="space-y-6">
            @foreach($roles as $role)
                <form method="POST" action="{{ route('admin.roles.permissions.update', $role) }}" class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-5">
                    @csrf
                    @method('PUT')

                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">{{ strtoupper($role->name) }}</h3>
                        <span class="text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-600">{{ $role->permissions->count() }} permissions</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-2 mb-4">
                        @foreach($permissions as $permission)
                            <label class="flex items-center gap-2 text-sm text-gray-700 bg-gray-50 rounded px-2 py-2">
                                <input type="checkbox"
                                       name="permissions[]"
                                       value="{{ $permission->id }}"
                                       {{ $role->permissions->contains('id', $permission->id) ? 'checked' : '' }}>
                                <span>{{ $permission->name }}</span>
                            </label>
                        @endforeach
                    </div>

                    <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">Save Permissions</button>
                </form>
            @endforeach
        </div>
    </div>
</x-admin-layout>
