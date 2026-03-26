<x-app-layout>
    <div class="min-h-screen">
        <div class="max-w-7xl mx-auto px-4 py-6 sm:py-8">
            <nav class="flex mb-6" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 text-sm text-gray-500 md:space-x-2">
                    <li><a href="{{ route('home') }}" class="inline-flex items-center text-green-600 hover:text-green-700 font-medium">Home</a></li>
                    <li class="flex items-center"><span class="mx-1">/</span><a href="{{ route('phones.index') }}" class="text-green-600 hover:text-green-700 font-medium">Phones</a></li>
                    <li class="flex items-center"><span class="mx-1">/</span><span class="text-gray-700 font-medium">{{ $brand->name }}</span></li>
                </ol>
            </nav>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-6">{{ $brand->name }}</h1>
            @if($models->isEmpty())
                <div class="block p-8 bg-white border border-gray-200 rounded-xl shadow-sm text-center text-gray-500">No models for this brand yet.</div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    @foreach($models as $m)
                        <a href="{{ route('phones.model', [$brand->slug, $m->slug]) }}" class="flex items-center gap-4 p-4 bg-white border border-gray-200 rounded-xl shadow-sm hover:bg-green-50 hover:border-green-200 hover:shadow-md transition duration-200">
                            @if($m->image)
                                <img src="{{ storage_asset($m->image) }}" alt="" class="w-14 h-14 object-contain rounded-lg bg-gray-50 flex-shrink-0">
                            @else
                                <div class="w-14 h-14 rounded-lg bg-gray-100 flex items-center justify-center text-gray-400 text-xs flex-shrink-0">—</div>
                            @endif
                            <span class="font-semibold text-gray-900">{{ $m->name }}</span>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
