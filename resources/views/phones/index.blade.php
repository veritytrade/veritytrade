<x-app-layout>
    <div class="min-h-screen">
        <div class="max-w-7xl mx-auto px-4 py-6 sm:py-8">
            <nav class="flex mb-6" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 text-sm text-gray-500 md:space-x-2">
                    <li><a href="{{ route('home') }}" class="inline-flex items-center text-green-600 hover:text-green-700 font-medium">Home</a></li>
                    <li class="flex items-center"><span class="mx-1">/</span><span class="text-gray-700 font-medium">Phones</span></li>
                </ol>
            </nav>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-1">Phone brands</h1>
            <p class="text-gray-500 mb-6">Select a brand to browse models</p>
            @if($brands->isEmpty())
                <div class="block p-6 bg-white border border-gray-200 rounded-xl shadow-sm text-center text-gray-500">
                    <p>No phone brands available yet.</p>
                </div>
            @else
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                    @foreach($brands as $b)
                        <a href="{{ route('phones.brand', $b->slug) }}" class="block p-6 bg-white border border-gray-200 rounded-xl shadow-sm hover:bg-green-50 hover:border-green-200 hover:shadow-md transition duration-200">
                            @if($b->image)
                                <img src="{{ storage_asset($b->image) }}" alt="" class="w-16 h-16 sm:w-20 sm:h-20 object-contain mx-auto mb-3">
                            @else
                                <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-lg bg-gray-100 mx-auto mb-3 flex items-center justify-center text-gray-400 text-2xl font-bold">{{ Str::limit($b->name, 1) }}</div>
                            @endif
                            <h3 class="text-sm font-semibold text-gray-900 text-center">{{ $b->name }}</h3>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
