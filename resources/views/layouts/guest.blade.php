<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 pt-2">
            <!-- Logo -->
            <div class="flex text-3xl font-bold items-center">
                <a href="{{ route('dashboard') }}">
                    Atte
                </a>
            </div>
        </div>
    </div>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-neutral-100">
        <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-neutral-100 overflow-hidden sm:rounded-lg">
            {{ $slot }}
        </div>
    </div>
    <footer class="bg-white h-12 text-center pt-3">
        <p><small>Atte,inc.</small></p>
    </footer>
</body>

</html>
