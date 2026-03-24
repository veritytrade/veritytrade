<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Customer - {{ config('app.name') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/invoice/logo.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak]{display:none!important}</style>
</head>
<body class="bg-gray-50 text-gray-900">
    @php
        $user = auth()->user();
        $preferredName = $user?->username ?: $user?->name ?: 'Customer';
        $customerName = trim(explode(' ', $preferredName)[0] ?? $preferredName);
    @endphp
    <header class="bg-white border-b border-gray-200 sticky top-0 z-20 shadow-sm" x-data="{ mobileOpen: false }">
        <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2 shrink-0">
                <x-application-logo class="h-8 w-8 sm:h-9 sm:w-9 object-contain" />
                <span class="font-bold text-blue-700 truncate max-w-[120px] sm:max-w-none">{{ $customerName }}</span>
            </a>
            <nav class="hidden sm:flex items-center gap-2 lg:gap-3 text-sm">
                <a href="{{ route('dashboard') }}" class="px-2 py-1.5 rounded-lg {{ request()->routeIs('dashboard') && !request()->routeIs('dashboard.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-100 hover:text-blue-700' }}">Dashboard</a>
                <a href="{{ route('dashboard.orders') }}" class="px-2 py-1.5 rounded-lg {{ request()->routeIs('dashboard.orders') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-100 hover:text-blue-700' }}">Orders</a>
                <a href="{{ route('dashboard.tracking') }}" class="px-2 py-1.5 rounded-lg {{ request()->routeIs('dashboard.tracking') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-100 hover:text-blue-700' }}">Tracking</a>
                <a href="{{ route('dashboard.invoices') }}" class="px-2 py-1.5 rounded-lg {{ request()->routeIs('dashboard.invoices') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-100 hover:text-blue-700' }}">Invoices</a>
                <a href="{{ route('profile.edit') }}" class="px-2 py-1.5 rounded-lg text-gray-700 hover:bg-gray-100 hover:text-blue-700">Profile</a>
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="rounded-lg bg-gray-900 px-3 py-1.5 text-white hover:bg-black text-sm">Logout</button>
                </form>
            </nav>
            <button type="button" @click="mobileOpen = !mobileOpen" class="sm:hidden p-2 rounded-lg hover:bg-gray-100" aria-label="Menu">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
        </div>
        <div x-show="mobileOpen" x-cloak x-transition class="sm:hidden border-t border-gray-200 bg-white py-2 px-4 space-y-1">
            <a href="{{ route('dashboard') }}" class="block py-2 rounded-lg px-3 {{ request()->routeIs('dashboard') && !request()->routeIs('dashboard.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-100' }}">Dashboard</a>
            <a href="{{ route('dashboard.orders') }}" class="block py-2 rounded-lg px-3 {{ request()->routeIs('dashboard.orders') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-100' }}">Orders</a>
            <a href="{{ route('dashboard.tracking') }}" class="block py-2 rounded-lg px-3 {{ request()->routeIs('dashboard.tracking') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-100' }}">Tracking</a>
            <a href="{{ route('dashboard.invoices') }}" class="block py-2 rounded-lg px-3 {{ request()->routeIs('dashboard.invoices') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-100' }}">Invoices</a>
            <a href="{{ route('profile.edit') }}" class="block py-2 rounded-lg px-3 text-gray-700 hover:bg-gray-100">Profile</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="block w-full text-left py-2 px-3 rounded-lg text-gray-700 hover:bg-gray-100">Logout</button>
            </form>
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-4 py-6">
        @if (session('status'))
            <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-2 text-sm text-green-700">{{ session('status') }}</div>
        @endif
        @if (session('error'))
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-sm text-red-700">{{ session('error') }}</div>
        @endif

        {{ $slot }}
    </main>
</body>
</html>
