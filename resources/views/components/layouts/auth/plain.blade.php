<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="min-h-screen flex flex-col">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? config('app.name') }}</title>


    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('css')
</head>

<body class="flex flex-col flex-1">
    <div x-data="{ mobileMenuOpen: false }" class="antialiased flex flex-col flex-1">

        <main class="flex-grow">
            {{ $slot }}
        </main>

    </div>

    @livewireScripts
    @stack('scripts')

</body>
</html>
