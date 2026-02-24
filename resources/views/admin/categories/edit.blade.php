<x-admin-layout>

<div class="p-6 max-w-2xl mx-auto">

    <div class="mb-6">
        <h2 class="text-2xl font-bold text-blue-700">
            Edit Category
        </h2>
    </div>

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

    <div class="bg-white shadow rounded-lg p-6">

        <form method="POST"
              action="{{ route('admin.categories.update', $category) }}">
            @csrf

            <div class="mb-4">
                <label class="block mb-2 text-gray-700 font-medium">
                    Category Name
                </label>

                <input type="text"
                       name="name"
                       value="{{ old('name', $category->name) }}"
                       class="border p-2 rounded w-full"
                       required>
            </div>

            <div class="flex justify-between">

                <a href="{{ route('admin.categories.index') }}"
                   class="bg-gray-400 hover:bg-gray-500 text-white px-4 py-2 rounded">
                    Back
                </a>

                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                    Update Category
                </button>

            </div>

        </form>

    </div>

</div>

</x-admin-layout>
