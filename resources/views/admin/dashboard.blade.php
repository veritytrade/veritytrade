<x-layouts.admin>
    <div class="max-w-6xl mx-auto p-4 md:p-6">
        
        {{-- Header (Horizontal Layout - Matches Other Pages) --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6 mb-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                {{-- Left: Title --}}
                <div class="flex-1">
                    <h2 class="text-xl md:text-2xl font-bold text-blue-700">
                        Admin Dashboard
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">Overview of your store statistics</p>
                </div>
                
                {{-- Right: Quick Actions --}}
                <div class="flex gap-3">
                    <a href="{{ route('admin.deals.create') }}"
                       class="bg-green-600 hover:bg-green-700 text-white px-6 py-2.5 rounded-lg font-medium transition shadow-sm whitespace-nowrap text-center text-sm">
                        + Add Hot Deal
                    </a>
                    <a href="{{ route('admin.categories.index') }}"
                       class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-lg font-medium transition shadow-sm whitespace-nowrap text-center text-sm">
                        Manage Categories
                    </a>
                </div>
            </div>
        </div>

        {{-- Stats Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-6">
            
            {{-- Categories Card --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6 hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Total Categories</p>
                        <p class="text-2xl md:text-3xl font-bold text-blue-700">
                            {{ \App\Models\Category::count() }}
                        </p>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </div>
                </div>
                <a href="{{ route('admin.categories.index') }}" 
                   class="text-sm text-blue-600 hover:text-blue-800 font-medium mt-3 inline-block">
                    View All →
                </a>
            </div>

            {{-- Brands Card --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6 hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Total Brands</p>
                        <p class="text-2xl md:text-3xl font-bold text-green-700">
                            {{ \App\Models\Brand::count() }}
                        </p>
                    </div>
                    <div class="bg-green-100 p-3 rounded-lg">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                        </svg>
                    </div>
                </div>
                <a href="{{ route('admin.brands.index') }}" 
                   class="text-sm text-green-600 hover:text-green-800 font-medium mt-3 inline-block">
                    View All →
                </a>
            </div>

            {{-- Series Card --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6 hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Total Series</p>
                        <p class="text-2xl md:text-3xl font-bold text-indigo-700">
                            {{ \App\Models\Series::count() }}
                        </p>
                    </div>
                    <div class="bg-indigo-100 p-3 rounded-lg">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                    </div>
                </div>
                <a href="{{ route('admin.series.index') }}" 
                   class="text-sm text-indigo-600 hover:text-indigo-800 font-medium mt-3 inline-block">
                    View All →
                </a>
            </div>

            {{-- Hot Deals Card --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6 hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Active Hot Deals</p>
                        <p class="text-2xl md:text-3xl font-bold text-red-700">
                            {{ \App\Models\Deal::where('is_active', true)->where('expires_at', '>', now())->count() }}
                        </p>
                    </div>
                    <div class="bg-red-100 p-3 rounded-lg">
                        <svg class="w-6 h-6 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.214.33-.403.713-.57 1.116-.334.804-.614 1.768-.84 2.734a31.365 31.365 0 00-.613 3.58 2.64 2.64 0 01-.945-1.067c-.328-.68-.398-1.534-.398-2.654A1 1 0 005.05 6.098a1 1 0 00-.945 1.066c.124.642.367 1.246.67 1.802.488.897.598 1.963.598 3.118 0 2.114-.613 4.086-1.64 5.788 1.52 1.038 3.407 1.634 5.59 1.634 2.183 0 4.07-.596 5.59-1.634.945-.644 1.64-1.64 1.64-2.754 0-1.155-.11-2.122-.598-3.118.303-.556.546-1.16.67-1.802.108-.562.066-1.416-.398-2.654a9.768 9.768 0 00-.613-3.58 2.64 2.64 0 01-.945-1.067c-.214-.33-.403-.713-.57-1.116-.208-.422-.477-.75-.822-.98C12.847 2.93 12.65 2.7 12.395 2.553z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
                <a href="{{ route('admin.deals.index') }}" 
                   class="text-sm text-red-600 hover:text-red-800 font-medium mt-3 inline-block">
                    View All →
                </a>
            </div>
        </div>

        {{-- Quick Actions Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
            
            {{-- Pricing Engine Card --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6 hover:shadow-md transition">
                <div class="flex items-center gap-4 mb-4">
                    <div class="bg-purple-100 p-3 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2-3-.895-3-2 1.343-2 3-2z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-800">Pricing Engine</h3>
                        <p class="text-sm text-gray-500">Manage pricing combinations</p>
                    </div>
                </div>
                <a href="{{ route('admin.pricing.index') }}" 
                   class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition inline-block">
                    Go to Pricing Engine
                </a>
            </div>

            {{-- Pricing Settings Card --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6 hover:shadow-md transition">
                <div class="flex items-center gap-4 mb-4">
                    <div class="bg-orange-100 p-3 rounded-lg">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-800">Pricing Settings</h3>
                        <p class="text-sm text-gray-500">Configure pricing profiles</p>
                    </div>
                </div>
                <a href="{{ route('admin.pricing.settings') }}" 
                   class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition inline-block">
                    Go to Pricing Settings
                </a>
            </div>
        </div>

        {{-- Info Box --}}
        <div class="mt-6 bg-blue-50 border border-blue-200 p-4 md:p-6 rounded-lg">
            <h3 class="font-semibold text-blue-800 mb-3">📊 Dashboard Tips</h3>
            <ul class="text-sm text-blue-700 space-y-2">
                <li>• Hot Deals expire automatically after the set date</li>
                <li>• Only active + non-expired deals show on homepage</li>
                <li>• Enable Structured Pricing in Brand Management for automated pricing</li>
                <li>• Create Categories → Brands → Series → Models in that order</li>
            </ul>
        </div>
    </div>
</x-layouts.admin>
