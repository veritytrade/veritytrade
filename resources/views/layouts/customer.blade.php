<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Customer - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-900">
    @php($customer = auth()->user())
    @php($customerName = $customer?->username ?: $customer?->name ?: 'Customer')
    <header class="bg-white border-b border-gray-200 sticky top-0 z-20">
        <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2 shrink-0">
                <x-application-logo class="h-8 w-8 object-contain" />
                <span class="font-bold text-blue-700">{{ $customerName }}</span>
            </a>
            <nav class="flex items-center gap-3 text-sm">
                <a href="{{ route('dashboard') }}" class="text-gray-700 hover:text-blue-700">Dashboard</a>
                <a href="{{ route('dashboard.orders') }}" class="text-gray-700 hover:text-blue-700">Orders</a>
                <a href="{{ route('dashboard.tracking') }}" class="text-gray-700 hover:text-blue-700">Tracking</a>
                <a href="{{ route('dashboard.invoices') }}" class="text-gray-700 hover:text-blue-700">Invoices</a>
                <a href="{{ route('profile.edit') }}" class="text-gray-700 hover:text-blue-700">Profile</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="rounded-lg bg-gray-900 px-3 py-1.5 text-white hover:bg-black">Logout</button>
                </form>
            </nav>
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
