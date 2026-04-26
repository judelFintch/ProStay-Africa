<div class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
    <div class="rounded-3xl bg-slate-950 p-6 text-white shadow-xl sm:p-8">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-300">Reception</p>
                <h1 class="mt-2 text-2xl font-black sm:text-3xl">Tableau de bord hotel</h1>
                <p class="mt-2 text-sm text-slate-300">Arrivees, departs, chambres et soldes clients du jour.</p>
            </div>
            <div class="rounded-2xl bg-white/10 px-4 py-3 text-sm font-semibold text-slate-100 ring-1 ring-white/15">
                {{ $summary['businessDate']->format('d/m/Y') }}
            </div>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Occupation</p>
            <p class="mt-2 text-3xl font-black text-slate-900">{{ $summary['occupancyRate'] }}%</p>
            <p class="mt-1 text-sm text-slate-500">{{ $summary['activeStays'] }} / {{ $summary['totalRooms'] }} chambres occupees</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Arrivees</p>
            <p class="mt-2 text-3xl font-black text-slate-900">{{ $summary['arrivalsToday'] }}</p>
            <p class="mt-1 text-sm text-slate-500">Reservations attendues aujourd'hui</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Departs</p>
            <p class="mt-2 text-3xl font-black text-slate-900">{{ $summary['departuresToday'] }}</p>
            <p class="mt-1 text-sm text-slate-500">Check-out prevus aujourd'hui</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Solde ouvert</p>
            <p class="mt-2 text-3xl font-black text-slate-900">{{ number_format($summary['openInvoiceBalance'], 0, '.', ' ') }}</p>
            <p class="mt-1 text-sm text-slate-500">Factures sejour non soldees</p>
        </div>
    </div>

    <div class="grid gap-4 lg:grid-cols-4">
        @foreach($roomStatusCounts as $status => $count)
            <a href="{{ route('rooms.index') }}" wire:navigate class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200 transition hover:bg-slate-50">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $status }}</p>
                <p class="mt-2 text-2xl font-black text-slate-900">{{ $count }}</p>
            </a>
        @endforeach
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
        <section class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
            <div class="border-b border-slate-200 px-5 py-4">
                <h2 class="text-lg font-bold text-slate-900">Arrivees du jour</h2>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($arrivals as $reservation)
                    <div class="flex items-center justify-between gap-3 px-5 py-4">
                        <div>
                            <p class="font-semibold text-slate-900">{{ $reservation->customer?->full_name ?? 'Client sans nom' }}</p>
                            <p class="text-sm text-slate-500">Chambre {{ $reservation->room?->number ?? '-' }} · {{ $reservation->adults }} adulte(s), {{ $reservation->children }} enfant(s)</p>
                        </div>
                        <a href="{{ route('reservations.index') }}" wire:navigate class="rounded-lg bg-slate-900 px-3 py-2 text-xs font-semibold text-white">Check-in</a>
                    </div>
                @empty
                    <p class="px-5 py-8 text-center text-sm text-slate-500">Aucune arrivee attendue.</p>
                @endforelse
            </div>
        </section>

        <section class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
            <div class="border-b border-slate-200 px-5 py-4">
                <h2 class="text-lg font-bold text-slate-900">Departs du jour</h2>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($departures as $stay)
                    @php
                        $invoice = $stay->invoices->sortByDesc('issued_at')->first();
                    @endphp
                    <div class="flex items-center justify-between gap-3 px-5 py-4">
                        <div>
                            <p class="font-semibold text-slate-900">{{ $stay->customer?->full_name ?? 'Client sans nom' }}</p>
                            <p class="text-sm text-slate-500">Chambre {{ $stay->room?->number ?? '-' }} · solde {{ number_format((float) ($invoice?->balance ?? 0), 0, '.', ' ') }}</p>
                        </div>
                        <a href="{{ route('reservations.index') }}" wire:navigate class="rounded-lg bg-emerald-700 px-3 py-2 text-xs font-semibold text-white">Check-out</a>
                    </div>
                @empty
                    <p class="px-5 py-8 text-center text-sm text-slate-500">Aucun depart prevu.</p>
                @endforelse
            </div>
        </section>
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
        <section class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
            <div class="border-b border-slate-200 px-5 py-4">
                <h2 class="text-lg font-bold text-slate-900">Departs en retard</h2>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($overdueDepartures as $stay)
                    <div class="px-5 py-4">
                        <p class="font-semibold text-slate-900">{{ $stay->customer?->full_name ?? 'Client sans nom' }}</p>
                        <p class="text-sm text-rose-600">Chambre {{ $stay->room?->number ?? '-' }} · sortie prevue le {{ $stay->expected_check_out_at?->format('d/m/Y') }}</p>
                    </div>
                @empty
                    <p class="px-5 py-8 text-center text-sm text-slate-500">Aucun depart en retard.</p>
                @endforelse
            </div>
        </section>

        <section class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
            <div class="border-b border-slate-200 px-5 py-4">
                <h2 class="text-lg font-bold text-slate-900">Factures sejour a solder</h2>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($unpaidStayInvoices as $invoice)
                    <div class="flex items-center justify-between gap-3 px-5 py-4">
                        <div>
                            <p class="font-semibold text-slate-900">{{ $invoice->reference }}</p>
                            <p class="text-sm text-slate-500">{{ $invoice->customer?->full_name ?? 'Client sans nom' }} · chambre {{ $invoice->room?->number ?? '-' }}</p>
                        </div>
                        <a href="{{ route('billing.payments', ['invoice' => $invoice->id]) }}" wire:navigate class="rounded-lg bg-emerald-700 px-3 py-2 text-xs font-semibold text-white">
                            {{ number_format((float) $invoice->balance, 0, '.', ' ') }}
                        </a>
                    </div>
                @empty
                    <p class="px-5 py-8 text-center text-sm text-slate-500">Aucune facture sejour ouverte.</p>
                @endforelse
            </div>
        </section>
    </div>

    <section class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
        <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-lg font-bold text-slate-900">Planning 7 jours</h2>
                <p class="text-sm text-slate-500">Vue rapide par chambre.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <button wire:click="previousWeek" class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700">Precedent</button>
                <button wire:click="today" class="rounded-lg bg-slate-900 px-3 py-2 text-xs font-semibold text-white">Aujourd'hui</button>
                <button wire:click="nextWeek" class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700">Suivant</button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="sticky left-0 z-10 bg-slate-50 px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Chambre</th>
                        @foreach($planning['dates'] as $date)
                            <th class="min-w-32 px-3 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">
                                {{ $date->format('d/m') }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @foreach($planning['rooms'] as $line)
                        <tr>
                            <td class="sticky left-0 z-10 bg-white px-4 py-3">
                                <p class="font-bold text-slate-900">{{ $line['room']->number }}</p>
                                <p class="text-xs text-slate-500">{{ $line['room']->roomType?->name ?? '-' }}</p>
                            </td>
                            @foreach($line['days'] as $day)
                                @php
                                    $stateClasses = [
                                        'occupied' => 'bg-slate-900 text-white',
                                        'reserved' => 'bg-sky-100 text-sky-800',
                                        'maintenance' => 'bg-rose-100 text-rose-800',
                                        'cleaning' => 'bg-amber-100 text-amber-800',
                                        'available' => 'bg-emerald-50 text-emerald-800',
                                    ];
                                    $classes = $stateClasses[$day['state']] ?? 'bg-slate-100 text-slate-700';
                                @endphp
                                <td class="px-3 py-3">
                                    <span class="block truncate rounded-lg px-2.5 py-2 text-xs font-semibold {{ $classes }}" title="{{ $day['label'] }}">
                                        {{ $day['label'] }}
                                    </span>
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
</div>
