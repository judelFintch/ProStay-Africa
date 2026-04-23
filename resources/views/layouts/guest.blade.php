<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="text-slate-900 antialiased">
        <div class="min-h-screen flex flex-col justify-center items-center px-4 py-8">
            <div>
                <a href="/" wire:navigate>
                    <x-application-logo class="h-16 w-16 fill-current text-emerald-700" />
                </a>
            </div>

            <p class="mt-3 text-center text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">ProStay Africa</p>
            <h1 class="mt-1 text-center text-3xl font-black text-slate-900" style="font-family: 'Cormorant Garamond', ui-serif, Georgia, serif;">Hotel Operations Suite</h1>

            <div class="w-full sm:max-w-md mt-6 px-6 py-5 bg-white/95 shadow-xl ring-1 ring-slate-200 overflow-hidden rounded-2xl">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
