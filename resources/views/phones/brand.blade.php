<x-app-layout>
    <div class="premium-shell">
        <div class="premium-container py-6 sm:py-8">
            <nav class="flex mb-6" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 text-sm text-gray-500 md:space-x-2">
                    <li><a href="{{ route('home') }}" class="inline-flex items-center premium-link font-medium">Home</a></li>
                    <li class="flex items-center"><span class="mx-1">/</span><a href="{{ route('phones.index') }}" class="premium-link font-medium">Phones</a></li>
                    <li class="flex items-center"><span class="mx-1">/</span><span class="text-gray-700 font-medium">{{ $brand->name }}</span></li>
                </ol>
            </nav>
            <h1 class="premium-title text-2xl sm:text-3xl mb-6">{{ $brand->name }}</h1>
            @if($models->isEmpty())
                <div class="premium-card p-8 text-center text-gray-500">No models for this brand yet.</div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    @foreach($models as $m)
                        <a href="{{ route('phones.model', [$brand->slug, $m->slug]) }}" class="premium-card-soft flex items-center gap-4 p-4 hover:-translate-y-0.5 hover:shadow-[0_12px_30px_rgba(2,6,23,0.1)] transition duration-200">
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
