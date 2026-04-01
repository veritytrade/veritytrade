<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin - {{ config('app.name') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/invoice/logo.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak]{display:none !important;}</style>
</head>
<body class="bg-gray-50" x-data="{ sidebarOpen: false }">
    @php($user = auth()->user())

    <div x-cloak x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 bg-black/40 z-30 md:hidden" @click="sidebarOpen = false"></div>

    <aside x-cloak class="fixed inset-y-0 left-0 w-64 bg-gradient-to-b from-green-700 to-green-800 shadow-lg z-40 transform transition-transform duration-200 flex flex-col"
           :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'">
        <div class="p-6 border-b border-green-600">
            <h1 class="text-xl font-bold text-white">VerityTrade Admin</h1>
            <p class="text-xs text-green-200 mt-1">Management Panel</p>
        </div>

        <nav class="mt-4 px-3 pb-6 flex-1 overflow-y-auto" @click="sidebarOpen = false">
            <ul class="space-y-1">
                @if($user->hasPermission('view_dashboard'))
                    <li><a href="{{ route('admin.dashboard') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg {{ request()->routeIs('admin.dashboard') ? 'bg-white text-green-700' : 'text-green-100 hover:bg-green-600 hover:text-white' }}">Dashboard</a></li>
                    <li><a href="{{ route('admin.homepage-hero.edit') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg {{ request()->routeIs('admin.homepage-hero.*') ? 'bg-white text-green-700' : 'text-green-100 hover:bg-green-600 hover:text-white' }}">Homepage Hero</a></li>
                @endif
                @if($user->hasPermission('manage_deals'))
                    <li><a href="{{ route('admin.deals.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg {{ request()->routeIs('admin.deals.*') ? 'bg-white text-green-700' : 'text-green-100 hover:bg-green-600 hover:text-white' }}">Hot Deals</a></li>
                    <li><a href="{{ route('admin.products.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg {{ request()->routeIs('admin.products.*') ? 'bg-white text-green-700' : 'text-green-100 hover:bg-green-600 hover:text-white' }}">Products</a></li>
                @endif
                <li><a href="{{ route('admin.phones.brands.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg {{ request()->routeIs('admin.phones.*') ? 'bg-white text-green-700' : 'text-green-100 hover:bg-green-600 hover:text-white' }}">Phones</a></li>
                @if($user->hasPermission('view_tracking'))
                    <li><a href="{{ route('admin.shipments.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg {{ request()->routeIs('admin.shipments.*') ? 'bg-white text-green-700' : 'text-green-100 hover:bg-green-600 hover:text-white' }}">Shipments</a></li>
                @endif
                @if($user->hasPermission('view_tracking'))
                    <li><a href="{{ route('admin.orders.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg {{ request()->routeIs('admin.orders.*') ? 'bg-white text-green-700' : 'text-green-100 hover:bg-green-600 hover:text-white' }}">Orders</a></li>
                @endif
                @if($user->hasPermission('generate_invoices'))
                    <li><a href="{{ route('admin.invoices.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg {{ request()->routeIs('admin.invoices.*') ? 'bg-white text-green-700' : 'text-green-100 hover:bg-green-600 hover:text-white' }}">Invoices</a></li>
                @endif
                @if($user->hasPermission('assign_roles'))
                    <li><a href="{{ route('admin.staff.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg {{ request()->routeIs('admin.staff.*') ? 'bg-white text-green-700' : 'text-green-100 hover:bg-green-600 hover:text-white' }}">Staff Management</a></li>
                @endif
                @if($user->hasPermission('approve_users'))
                    <li><a href="{{ route('admin.registered-users.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg {{ request()->routeIs('admin.registered-users.*') ? 'bg-white text-green-700' : 'text-green-100 hover:bg-green-600 hover:text-white' }}">Registered Users</a></li>
                    <li><a href="{{ route('admin.customers.show') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg {{ request()->routeIs('admin.customers.*') ? 'bg-white text-green-700' : 'text-green-100 hover:bg-green-600 hover:text-white' }}">Customer 360</a></li>
                @endif
                @if($user->hasPermission('manage_feature_flags'))
                    <li><a href="{{ route('admin.feature-flags.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg {{ request()->routeIs('admin.feature-flags.*') ? 'bg-white text-green-700' : 'text-green-100 hover:bg-green-600 hover:text-white' }}">Feature Flags</a></li>
                @endif
                @if($user->hasPermission('assign_roles'))
                    <li><a href="{{ route('admin.roles.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg {{ request()->routeIs('admin.roles.*') ? 'bg-white text-green-700' : 'text-green-100 hover:bg-green-600 hover:text-white' }}">Role Management</a></li>
                @endif
            </ul>
        </nav>

        <div class="p-4 border-t border-green-600 bg-green-800">
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit" class="w-full rounded-lg bg-red-600 hover:bg-red-700 text-white py-2 text-sm font-medium">Logout</button>
            </form>
        </div>
    </aside>

    <div class="md:ml-64 min-h-screen">
        <header class="bg-white shadow-sm p-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between sticky top-0 z-30 border-b border-gray-100">
            <div class="flex items-center justify-between md:justify-start gap-3">
                <button type="button" @click="sidebarOpen = true" class="inline-flex md:hidden items-center justify-center rounded-lg border border-gray-200 p-2 text-gray-600">Menu</button>
                <div>
                    <span class="font-bold text-green-700">VerityTrade Admin</span>
                    @if($user)
                        <p class="text-xs text-gray-500">{{ $user->name }} • {{ $user->role?->name ?? 'staff' }}</p>
                    @endif
                </div>
            </div>
            @if($user && ($user->hasPermission('view_dashboard') || $user->hasPermission('approve_users') || $user->hasPermission('view_tracking')))
                <form method="POST" action="{{ route('admin.search') }}" class="flex items-center gap-2 max-w-md w-full">
                    @csrf
                    <input type="text"
                           name="q"
                           class="flex-1 rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500"
                           placeholder="Quick search: customer, order, shipment, invoice">
                    <button type="submit"
                            class="px-3 py-1.5 rounded-lg bg-green-600 hover:bg-green-700 text-white text-xs font-medium">
                        Search
                    </button>
                </form>
            @endif
        </header>

        <main class="p-4 md:p-6">
            @if(session('success'))
                <div x-data="{ show: true, progress: 100 }"
                     x-show="show"
                     x-init="
                        const total = 3500;
                        const started = Date.now();
                        const timer = setInterval(() => {
                            const elapsed = Date.now() - started;
                            progress = Math.max(0, 100 - Math.floor((elapsed / total) * 100));
                            if (elapsed >= total) { clearInterval(timer); show = false; }
                        }, 100);
                     "
                     class="mb-4 rounded-lg border border-green-200 bg-green-50 text-green-800 px-4 py-3 shadow-sm">
                    <div class="text-sm font-medium">{{ session('success') }}</div>
                    <div class="mt-2 h-1.5 w-full rounded bg-green-100 overflow-hidden">
                        <div class="h-full bg-green-500 transition-all duration-100" :style="`width: ${progress}%`"></div>
                    </div>
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 text-red-800 px-4 py-3 shadow-sm text-sm font-medium">
                    {{ session('error') }}
                </div>
            @endif
            {{ $slot }}
        </main>
    </div>
</body>
</html>
