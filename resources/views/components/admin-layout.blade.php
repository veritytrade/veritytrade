<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50" x-data="{ sidebarOpen: false }">
    @php($user = auth()->user())

    <div x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 bg-black/40 z-30 md:hidden" @click="sidebarOpen = false"></div>

    <aside class="fixed inset-y-0 left-0 w-64 bg-gradient-to-b from-green-700 to-green-800 shadow-lg z-40 overflow-y-auto transform transition-transform duration-200"
           :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'">
        <div class="p-6 border-b border-green-600">
            <h1 class="text-xl font-bold text-white">VerityTrade Admin</h1>
            <p class="text-xs text-green-200 mt-1">Management Panel</p>
        </div>

        <nav class="mt-4 px-3 pb-24">
            <ul class="space-y-1">
                @if($user->hasPermission('view_dashboard'))
                    <li><a href="{{ route('admin.dashboard') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg {{ request()->routeIs('admin.dashboard') ? 'bg-white text-green-700' : 'text-green-100 hover:bg-green-600 hover:text-white' }}">Dashboard</a></li>
                @endif
                @if($user->hasPermission('manage_deals'))
                    <li><a href="{{ route('admin.deals.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg {{ request()->routeIs('admin.deals.*') ? 'bg-white text-green-700' : 'text-green-100 hover:bg-green-600 hover:text-white' }}">Hot Deals</a></li>
                @endif
                @if($user->hasPermission('manage_categories'))
                    <li><a href="{{ route('admin.categories.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg {{ request()->routeIs('admin.categories.*') ? 'bg-white text-green-700' : 'text-green-100 hover:bg-green-600 hover:text-white' }}">Categories</a></li>
                @endif
                @if($user->hasPermission('manage_brands'))
                    <li><a href="{{ route('admin.brands.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg {{ request()->routeIs('admin.brands.*') ? 'bg-white text-green-700' : 'text-green-100 hover:bg-green-600 hover:text-white' }}">Brands</a></li>
                @endif
                @if($user->hasPermission('manage_series'))
                    <li><a href="{{ route('admin.series.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg {{ request()->routeIs('admin.series.*') ? 'bg-white text-green-700' : 'text-green-100 hover:bg-green-600 hover:text-white' }}">Series</a></li>
                @endif
                @if($user->hasPermission('manage_models'))
                    <li><a href="{{ route('admin.brands.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg text-green-100 hover:bg-green-600 hover:text-white">Models</a></li>
                @endif
                @if($user->hasPermission('access_pricing_engine'))
                    <li><a href="{{ route('admin.pricing.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg {{ request()->routeIs('admin.pricing.index') || request()->routeIs('admin.pricing.store') || request()->routeIs('admin.pricing.toggle') || request()->routeIs('admin.pricing.destroy') ? 'bg-white text-green-700' : 'text-green-100 hover:bg-green-600 hover:text-white' }}">Pricing Engine</a></li>
                @endif
                @if($user->hasPermission('access_pricing_settings'))
                    <li><a href="{{ route('admin.pricing.settings') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg {{ request()->routeIs('admin.pricing.settings*') ? 'bg-white text-green-700' : 'text-green-100 hover:bg-green-600 hover:text-white' }}">Pricing Settings</a></li>
                @endif
                @if($user->hasPermission('approve_users'))
                    <li><a href="{{ route('admin.users.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg {{ request()->routeIs('admin.users.*') ? 'bg-white text-green-700' : 'text-green-100 hover:bg-green-600 hover:text-white' }}">Users</a></li>
                @endif
                @if($user->hasPermission('manage_feature_flags'))
                    <li><a href="{{ route('admin.feature-flags.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg {{ request()->routeIs('admin.feature-flags.*') ? 'bg-white text-green-700' : 'text-green-100 hover:bg-green-600 hover:text-white' }}">Feature Flags</a></li>
                @endif
                @if($user->hasPermission('assign_roles'))
                    <li><a href="{{ route('admin.roles.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg {{ request()->routeIs('admin.roles.*') ? 'bg-white text-green-700' : 'text-green-100 hover:bg-green-600 hover:text-white' }}">Role Management</a></li>
                @endif
            </ul>
        </nav>

        <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-green-600 bg-green-800">
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit" class="w-full rounded-lg bg-red-600 hover:bg-red-700 text-white py-2 text-sm font-medium">Logout</button>
            </form>
        </div>
    </aside>

    <div class="md:ml-64 min-h-screen">
        <header class="bg-white shadow-sm p-4 flex justify-between items-center md:hidden sticky top-0 z-30">
            <button type="button" @click="sidebarOpen = true" class="inline-flex items-center justify-center rounded-lg border border-gray-200 p-2 text-gray-600">Menu</button>
            <span class="font-bold text-green-700">VerityTrade Admin</span>
            <div class="w-9"></div>
        </header>

        <main class="p-4 md:p-6">
            {{ $slot }}
        </main>
    </div>
</body>
</html>
