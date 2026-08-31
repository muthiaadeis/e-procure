<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'e-Procure') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center py-10 px-4 bg-gradient-to-br from-indigo-50 via-white to-rose-50 relative overflow-hidden">

            <!-- Decorative blobs -->
            <div class="pointer-events-none absolute -top-24 -left-24 w-72 h-72 bg-indigo-200/40 rounded-full blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-24 -right-24 w-72 h-72 bg-rose-200/40 rounded-full blur-3xl"></div>

            <div class="relative flex flex-col items-center mb-6">
                <a href="/" class="flex items-center justify-center w-16 h-16 rounded-2xl bg-indigo-600 shadow-lg shadow-indigo-600/30 mb-3">
                    <x-application-logo class="w-9 h-9 fill-current text-white" />
                </a>
                <h1 class="text-lg font-semibold text-gray-800">{{ config('app.name', 'e-Procure') }}</h1>
                <p class="text-xs text-gray-400">Material Request Management</p>
            </div>

            <div class="relative w-full sm:max-w-md px-6 py-8 sm:px-8 bg-white/90 backdrop-blur shadow-xl shadow-gray-200/60 border border-gray-100 overflow-hidden sm:rounded-2xl">
                {{ $slot }}
            </div>

            <p class="relative mt-6 text-xs text-gray-400">&copy; {{ date('Y') }} {{ config('app.name', 'e-Procure') }}. All rights reserved.</p>
        </div>
    </body>
</html>
