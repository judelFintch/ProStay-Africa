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
            <script>
                document.addEventListener('alpine:init', () => {
                    const storageKey = 'prostay.sidebar.open';
                    let initialOpen = false;

                    try {
                        const saved = localStorage.getItem(storageKey);
                        if (saved !== null) {
                            initialOpen = saved === '1';
                        }
                    } catch (error) {
                        // Keep default value when localStorage is unavailable.
                    }

                    Alpine.store('sidebar', {
                        open: initialOpen,
                        setOpen(value) {
                            this.open = value;

                            try {
                                localStorage.setItem(storageKey, value ? '1' : '0');
                            } catch (error) {
                                // Ignore storage failures and keep UI state in memory.
                            }
                        },
                        toggle() {
                            this.setOpen(!this.open);
                        }
                    });
                });
            </script>
        </head>
        <body class="antialiased">
            <div class="min-h-screen transition-[padding] duration-300 ease-in-out" :class="$store.sidebar.open ? 'lg:pl-72' : 'lg:pl-0'">
            <livewire:layout.navigation />

            <div class="sticky top-0 z-20 border-b border-slate-200/80 bg-white/85 backdrop-blur">
                <div class="mx-auto flex max-w-7xl justify-end px-4 py-2 sm:px-6 lg:px-8">
                    <a
                        href="{{ route('dashboard') }}"
                        wire:navigate
                        class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 hover:text-slate-900"
                    >
                        <i class="fa-solid fa-house w-4 text-center text-slate-400"></i>
                        <span>Dashboard</span>
                    </a>
                </div>
            </div>

            <!-- Page Heading -->
            @if (isset($header))
                <header class="border-b border-slate-200/80 bg-white/80 backdrop-blur">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
