@props(['title' => config('app.name', 'ProStay Africa')])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.store('sidebar', {
                    open: true,
                    toggle() { this.open = !this.open; }
                });
            });
        </script>
    </head>
    <body class="antialiased">
        <div class="min-h-screen transition-[padding] duration-300 ease-in-out" :class="$store.sidebar.open ? 'lg:pl-72' : 'lg:pl-0'">
            <livewire:layout.navigation />

            @if (isset($header))
                <header class="border-b border-slate-200/80 bg-white/80 backdrop-blur">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <main>
                {{ $slot }}
            </main>
        </div>

        @livewireScripts
    </body>
</html>
