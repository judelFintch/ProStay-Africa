<x-app-layout>
    @php
        $modules = [
            [
                'label' => __('Customers'),
                'description' => 'Gestion des fiches clients et des profils identifies.',
                'route' => route('customers.index'),
                'icon' => 'fa-users',
                'color' => 'from-cyan-500 to-cyan-600',
            ],
            [
                'label' => __('Reservations'),
                'description' => 'Suivi des reservations, arrivees et annulations.',
                'route' => route('reservations.index'),
                'icon' => 'fa-calendar-check',
                'color' => 'from-emerald-500 to-emerald-600',
            ],
            [
                'label' => __('Rooms'),
                'description' => 'Pilotage des statuts de chambres en temps reel.',
                'route' => route('rooms.index'),
                'icon' => 'fa-bed',
                'color' => 'from-indigo-500 to-indigo-600',
            ],
            [
                'label' => __('Orders'),
                'description' => 'Saisie des commandes restaurant, bar et service.',
                'route' => route('orders.create'),
                'icon' => 'fa-utensils',
                'color' => 'from-amber-500 to-amber-600',
            ],
            [
                'label' => __('Invoices'),
                'description' => 'Creation des factures a partir des commandes ouvertes.',
                'route' => route('billing.invoices'),
                'icon' => 'fa-file-invoice-dollar',
                'color' => 'from-violet-500 to-violet-600',
            ],
            [
                'label' => __('Payments'),
                'description' => 'Enregistrement des paiements complets et partiels.',
                'route' => route('billing.payments'),
                'icon' => 'fa-wallet',
                'color' => 'from-fuchsia-500 to-fuchsia-600',
            ],
            [
                'label' => __('Stock'),
                'description' => 'Entrees, sorties et alertes de seuil de stock.',
                'route' => route('stock.index'),
                'icon' => 'fa-boxes-stacked',
                'color' => 'from-amber-500 to-amber-600',
            ],
            [
                'label' => __('Laundry'),
                'description' => 'Suivi du linge: dirty, washing, clean, distributed.',
                'route' => route('laundry.index'),
                'icon' => 'fa-soap',
                'color' => 'from-sky-500 to-sky-600',
            ],
            [
                'label' => __('POS'),
                'description' => 'Ventes rapides au comptoir avec encaissement immediat.',
                'route' => route('pos.quick-sale'),
                'icon' => 'fa-cash-register',
                'color' => 'from-rose-500 to-rose-600',
            ],
            [
                'label' => __('Reports'),
                'description' => 'Vue analytique des revenus, occupation et activite.',
                'route' => route('reports.index'),
                'icon' => 'fa-chart-pie',
                'color' => 'from-slate-600 to-slate-700',
            ],
        ];
    @endphp

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-600">ProStay Africa</p>
                <h2 class="text-2xl font-black text-slate-900 leading-tight">
                    {{ __('messages.operational_dashboard') }}
                </h2>
                <p class="text-sm text-slate-500">{{ now()->translatedFormat('l d F Y') }}</p>
            </div>
            <div class="hidden sm:flex items-center gap-2 rounded-full bg-slate-100 px-4 py-2">
                <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                <span class="text-xs font-medium text-slate-700">{{ __('messages.system_online') }}</span>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="rounded-3xl bg-gradient-to-br from-slate-900 via-slate-800 to-emerald-900 p-6 sm:p-8 text-white shadow-2xl">
                <div class="grid gap-6 lg:grid-cols-3">
                    <div class="lg:col-span-2">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-300">{{ __('messages.today_focus') }}</p>
                        <h3 class="mt-2 text-2xl sm:text-3xl font-black leading-tight">{{ __('messages.hero_title') }}</h3>
                        <p class="mt-3 max-w-2xl text-sm text-slate-200/90">
                            {{ __('messages.hero_subtitle') }}
                        </p>
                        <div class="mt-6 flex flex-wrap gap-3">
                            <a href="{{ route('customers.index') }}" class="rounded-xl bg-white/15 px-4 py-2 text-sm font-semibold backdrop-blur hover:bg-white/25 transition">Customers</a>
                            <a href="{{ route('orders.create') }}" class="rounded-xl bg-white/15 px-4 py-2 text-sm font-semibold backdrop-blur hover:bg-white/25 transition">Orders</a>
                            <a href="{{ route('billing.invoices') }}" class="rounded-xl bg-white/15 px-4 py-2 text-sm font-semibold backdrop-blur hover:bg-white/25 transition">Billing</a>
                            <a href="{{ route('pos.quick-sale') }}" class="rounded-xl bg-white/15 px-4 py-2 text-sm font-semibold backdrop-blur hover:bg-white/25 transition">POS</a>
                        </div>
                    </div>
                    <div class="rounded-2xl bg-white/10 p-5 ring-1 ring-white/20 backdrop-blur">
                        <p class="text-xs uppercase tracking-widest text-emerald-200">{{ __('messages.shift_status') }}</p>
                        <p class="mt-2 text-3xl font-black">{{ __('messages.afternoon') }}</p>
                        <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                            <div class="rounded-xl bg-black/20 p-3">
                                <p class="text-slate-300">{{ __('messages.open_invoices') }}</p>
                                <p class="mt-1 text-xl font-bold">12</p>
                            </div>
                            <div class="rounded-xl bg-black/20 p-3">
                                <p class="text-slate-300">{{ __('messages.pending_orders') }}</p>
                                <p class="mt-1 text-xl font-bold">8</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 sm:p-6">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-600">Module Hub</p>
                        <h3 class="mt-1 text-xl font-black text-slate-900">Acces direct a tous les modules</h3>
                    </div>
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ count($modules) }} modules</span>
                </div>

                <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach($modules as $module)
                        <a href="{{ $module['route'] }}" class="group rounded-2xl border border-slate-200 bg-white p-4 transition hover:-translate-y-0.5 hover:shadow-md" wire:navigate>
                            <div class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br text-white {{ $module['color'] }}">
                                <i class="fa-solid {{ $module['icon'] }}"></i>
                            </div>
                            <p class="mt-3 text-base font-bold text-slate-900">{{ $module['label'] }}</p>
                            <p class="mt-1 text-xs text-slate-500">{{ $module['description'] }}</p>
                            <p class="mt-3 text-xs font-semibold text-emerald-700 group-hover:text-emerald-600">Entrer dans le module <i class="fa-solid fa-arrow-right ml-1"></i></p>
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('messages.occupancy') }}</p>
                    <p class="mt-2 text-3xl font-black text-slate-900">78%</p>
                    <p class="mt-2 text-xs text-emerald-600">{{ __('messages.vs_yesterday') }}</p>
                </div>
                <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('messages.revenue_today') }}</p>
                    <p class="mt-2 text-3xl font-black text-slate-900">1 420 000</p>
                    <p class="mt-2 text-xs text-slate-500">XOF</p>
                </div>
                <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('messages.pos_tickets') }}</p>
                    <p class="mt-2 text-3xl font-black text-slate-900">36</p>
                    <p class="mt-2 text-xs text-amber-600">{{ __('messages.peak_hours') }}</p>
                </div>
                <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('messages.cash_in_hand') }}</p>
                    <p class="mt-2 text-3xl font-black text-slate-900">320 000</p>
                    <p class="mt-2 text-xs text-slate-500">XOF</p>
                </div>
            </div>

            <div class="mt-6 grid gap-6 lg:grid-cols-3">
                <div class="lg:col-span-2 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-bold text-slate-900">{{ __('messages.service_board') }}</h3>
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">{{ __('messages.live') }}</span>
                    </div>
                    <div class="mt-4 grid gap-3 sm:grid-cols-3">
                        <div class="rounded-xl border border-slate-200 p-4">
                            <p class="text-xs uppercase tracking-wide text-slate-500">{{ __('messages.accommodation') }}</p>
                            <p class="mt-2 text-2xl font-black text-slate-900">24</p>
                            <p class="text-xs text-slate-500">{{ __('messages.active_stays') }}</p>
                        </div>
                        <div class="rounded-xl border border-slate-200 p-4">
                            <p class="text-xs uppercase tracking-wide text-slate-500">{{ __('messages.restaurant') }}</p>
                            <p class="mt-2 text-2xl font-black text-slate-900">11</p>
                            <p class="text-xs text-slate-500">{{ __('messages.orders_in_queue') }}</p>
                        </div>
                        <div class="rounded-xl border border-slate-200 p-4">
                            <p class="text-xs uppercase tracking-wide text-slate-500">{{ __('messages.laundry') }}</p>
                            <p class="mt-2 text-2xl font-black text-slate-900">7</p>
                            <p class="text-xs text-slate-500">{{ __('messages.items_processing') }}</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                    <h3 class="text-lg font-bold text-slate-900">{{ __('messages.quick_actions') }}</h3>
                    <div class="mt-4 grid gap-2">
                        <a href="{{ route('customers.index') }}" class="rounded-xl border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">{{ __('messages.create_customer') }}</a>
                        <a href="{{ route('orders.create') }}" class="rounded-xl border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">{{ __('messages.create_order') }}</a>
                        <a href="{{ route('billing.invoices') }}" class="rounded-xl border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">{{ __('messages.issue_invoice') }}</a>
                        <a href="{{ route('pos.quick-sale') }}" class="rounded-xl border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">{{ __('messages.open_pos_sale') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
