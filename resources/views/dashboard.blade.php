<x-app-layout>
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
