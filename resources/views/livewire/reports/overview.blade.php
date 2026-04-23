<div class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
    <div class="rounded-3xl bg-gradient-to-br from-slate-900 via-emerald-900 to-teal-900 p-6 text-white shadow-xl sm:p-8">
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-300">{{ __('Analytics') }}</p>
        <h1 class="mt-2 text-2xl font-black sm:text-3xl">{{ __('Reports') }}</h1>
        <p class="mt-2 text-sm text-slate-200/90">{{ __('A clear snapshot of occupancy, payments, invoices, and service activity.') }}</p>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Occupancy') }}</p>
            <p class="mt-2 text-3xl font-black text-slate-900">{{ $occupancy }}%</p>
            <p class="mt-1 text-xs text-slate-500">{{ $activeStays }}/{{ $totalRooms }} {{ __('rooms occupied') }}</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Revenue today') }}</p>
            <p class="mt-2 text-3xl font-black text-slate-900">{{ number_format($todayRevenue, 2, '.', ' ') }}</p>
            <p class="mt-1 text-xs text-slate-500">XOF</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Open invoices') }}</p>
            <p class="mt-2 text-3xl font-black text-slate-900">{{ $openInvoices }}</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Orders today') }}</p>
            <p class="mt-2 text-3xl font-black text-slate-900">{{ $ordersToday }}</p>
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
        <div class="border-b border-slate-200 px-5 py-4 sm:px-6">
            <h2 class="text-lg font-bold text-slate-900">{{ __('Service area load') }}</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr class="text-left text-xs uppercase tracking-wide text-slate-600">
                        <th class="px-4 py-3">{{ __('Area') }}</th>
                        <th class="px-4 py-3">{{ __('Orders') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($serviceAreaLoad as $area)
                        <tr>
                            <td class="px-4 py-3 font-medium text-slate-900">{{ $area->name }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $area->orders_count }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="px-4 py-8 text-center text-slate-500">{{ __('No data available.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
