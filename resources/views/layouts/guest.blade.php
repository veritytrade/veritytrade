<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="description" content="{{ config('seo.description') }}">

        <title>{{ $title ?? config('seo.title') }}</title>

        <link rel="icon" type="image/png" href="{{ asset('images/invoice/logo.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased" style="background-color: #e8f5e9;">
        <div class="min-h-screen flex flex-col justify-center items-center px-4 py-6 sm:py-8" style="background: linear-gradient(135deg, #e8f5e9 0%, #f1f8e9 50%, #e3f2fd 100%);">
            <div class="flex justify-center">
                <a href="/">
                    <x-application-logo class="w-20 h-20 fill-current text-gray-500" />
                </a>
            </div>

            <div class="w-full max-w-md mt-6 px-6 py-4 bg-white/90 backdrop-blur-sm shadow-lg rounded-xl border border-gray-100 overflow-hidden">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
