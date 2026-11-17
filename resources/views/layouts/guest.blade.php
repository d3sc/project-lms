<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'LMS') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-slate-50 min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
        <div class="w-full sm:max-w-md px-6">
            <div class="text-center mb-8">
                <a href="/" class="inline-block">
                    <svg class="w-12 h-12 mx-auto text-indigo-600" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 3L1 9l11 6 9-4.91V17h2V9L12 3z" />
                    </svg>
                </a>
                <h1 class="mt-4 text-2xl font-semibold text-slate-900">{{ config('app.name', 'LMS') }}</h1>
            </div>

            <div class="card">
                <div class="card-body">
                    {{ $slot }}
                </div>
            </div>

            <p class="mt-6 text-center text-sm text-slate-500">
                &copy; {{ date('Y') }} {{ config('app.name', 'LMS') }}. Hak cipta dilindungi.
            </p>
        </div>
    </body>
</html>