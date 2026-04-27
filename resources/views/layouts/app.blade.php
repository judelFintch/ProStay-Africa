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
        <body class="antialiased">
            <div class="min-h-screen">

            <div class="sticky top-0 z-20 border-b border-slate-200/80 bg-white/85 backdrop-blur" x-data="{ userMenuOpen: false }">
                <div class="mx-auto flex max-w-7xl items-center justify-between gap-2 px-4 py-2 sm:px-6 lg:px-8">
                    <a
                        href="{{ route('dashboard') }}"
                        wire:navigate
                        class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 hover:text-slate-900"
                    >
                        <i class="fa-solid fa-house w-4 text-center text-slate-400"></i>
                        <span>Dashboard</span>
                    </a>

                    <div class="flex items-center gap-2">
                        <a
                            href="{{ route('orders.tracking') }}"
                            wire:navigate
                            class="inline-flex items-center gap-2 rounded-lg border border-amber-200 bg-amber-50 px-3 py-1.5 text-sm font-semibold text-amber-800 shadow-sm transition hover:bg-amber-100"
                        >
                            <i class="fa-solid fa-table-list w-4 text-center"></i>
                            <span>Suivi commandes</span>
                        </a>

                        <div class="relative">
                            <button
                                type="button"
                                @click="userMenuOpen = !userMenuOpen"
                                class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50"
                            >
                                <i class="fa-solid fa-user w-4 text-center text-slate-400"></i>
                                <span class="max-w-[180px] truncate">{{ auth()->user()?->name ?? 'Mon compte' }}</span>
                                <i class="fa-solid fa-chevron-down text-xs text-slate-400"></i>
                            </button>

                            <div
                                x-cloak
                                x-show="userMenuOpen"
                                @click.outside="userMenuOpen = false"
                                class="absolute right-0 mt-2 w-56 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xl"
                            >
                                <div class="border-b border-slate-100 px-3 py-2">
                                    <p class="text-xs font-semibold text-slate-900">{{ auth()->user()?->name }}</p>
                                    <p class="mt-0.5 truncate text-xs text-slate-500">{{ auth()->user()?->email }}</p>
                                </div>

                                <div class="py-1">
                                    <a href="{{ route('profile') }}" class="flex items-center gap-2 px-3 py-2 text-sm text-slate-700 transition hover:bg-slate-50" wire:navigate>
                                        <i class="fa-solid fa-id-card w-4 text-center text-slate-400"></i>
                                        Profil
                                    </a>
                                    <a href="{{ route('users.index') }}" class="flex items-center gap-2 px-3 py-2 text-sm text-slate-700 transition hover:bg-slate-50" wire:navigate>
                                        <i class="fa-solid fa-users-gear w-4 text-center text-slate-400"></i>
                                        Utilisateurs
                                    </a>
                                    <a href="{{ route('services.index') }}" class="flex items-center gap-2 px-3 py-2 text-sm text-slate-700 transition hover:bg-slate-50" wire:navigate>
                                        <i class="fa-solid fa-diagram-project w-4 text-center text-slate-400"></i>
                                        Services
                                    </a>
                                </div>

                                <form method="POST" action="{{ route('logout') }}" class="border-t border-slate-100">
                                    @csrf
                                    <button type="submit" class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm font-semibold text-rose-700 transition hover:bg-rose-50">
                                        <i class="fa-solid fa-right-from-bracket w-4 text-center"></i>
                                        Deconnexion
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
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
