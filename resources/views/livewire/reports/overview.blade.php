<div class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
    <div class="rounded-3xl bg-gradient-to-br from-slate-900 via-emerald-900 to-teal-900 p-6 text-white shadow-xl sm:p-8">
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-300">Analytics</p>
        <h1 class="mt-2 text-2xl font-black sm:text-3xl">Rapports complets</h1>
        <p class="mt-2 text-sm text-slate-200/90">Ventes et stock avec vision globale, par utilisateur et par service.</p>
    </div>

    <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 sm:p-6">
        <div class="grid gap-3 lg:grid-cols-5">
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Date début</label>
                <input type="date" wire:model.live="startDate" class="prostay-input" />
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Date fin</label>
                <input type="date" wire:model.live="endDate" class="prostay-input" />
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Devise</label>
                <select wire:model.live="currencyFilter" class="prostay-input">
                    <option value="all">Toutes</option>
                    @foreach($activeCurrencies as $currency)
                        <option value="{{ $currency }}">{{ $currency }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Utilisateur</label>
                <select wire:model.live="userFilter" class="prostay-input">
                    <option value="all">Tous</option>
                    @foreach($reportUsers as $reportUser)
                        <option value="{{ $reportUser->id }}">{{ $reportUser->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Service</label>
                <select wire:model.live="serviceFilter" class="prostay-input">
                    <option value="all">Tous</option>
                    @foreach($reportServices as $service)
                        <option value="{{ $service->id }}">{{ $service->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="mt-3 flex flex-wrap gap-2">
            <button wire:click="setPreset('today')" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">Aujourd hui</button>
            <button wire:click="setPreset('7d')" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">7 jours</button>
            <button wire:click="setPreset('month')" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">Mois</button>
            <button wire:click="setPreset('year')" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">Année</button>
            <button wire:click="resetFilters" class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-700 hover:bg-rose-100">Réinitialiser</button>
            <span class="ml-auto rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-600">Période: {{ $startDate }} → {{ $endDate }}</span>
        </div>
    </section>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Occupation</p>
            <p class="mt-2 text-3xl font-black text-slate-900">{{ $occupancy }}%</p>
            <p class="mt-1 text-xs text-slate-500">{{ $activeStays }}/{{ $totalRooms }} chambres occupées</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">CA commandes</p>
            <p class="mt-2 text-3xl font-black text-slate-900">{{ number_format($salesOrderAmount, 2, '.', ' ') }}</p>
            <p class="mt-1 text-xs text-slate-500">{{ $salesOrderCount }} commande(s)</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Paiements encaissés</p>
            <p class="mt-2 text-3xl font-black text-slate-900">{{ number_format($paymentsTotal, 2, '.', ' ') }}</p>
            <p class="mt-1 text-xs text-slate-500">Ticket moyen: {{ number_format($avgTicket, 2, '.', ' ') }}</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Indicateurs rapides</p>
            <p class="mt-2 text-sm font-semibold text-slate-700">CA du jour: {{ number_format($todayRevenue, 2, '.', ' ') }} {{ $reportCurrency }}</p>
            <p class="mt-1 text-sm font-semibold text-slate-700">Factures ouvertes: {{ $openInvoices }}</p>
            <p class="mt-1 text-sm font-semibold text-slate-700">Commandes du jour: {{ $ordersToday }}</p>
        </div>
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Restaurant encaissé (externe)</p>
            <p class="mt-2 text-3xl font-black text-emerald-900">{{ number_format($restaurantExternalRevenue, 2, '.', ' ') }} {{ $reportCurrency }}</p>
            <p class="mt-1 text-xs text-emerald-700">Paiements des clients externes sur la période filtrée.</p>
        </div>
        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-amber-700">Restaurant transféré vers hôtel</p>
            <p class="mt-2 text-3xl font-black text-amber-900">{{ number_format($restaurantHotelTransferBalance, 2, '.', ' ') }} {{ $reportCurrency }}</p>
            <p class="mt-1 text-xs text-amber-700">Balance restante des consommations restaurant des clients logés.</p>
        </div>
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        <section class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
            <div class="border-b border-slate-200 px-5 py-4 sm:px-6">
                <h2 class="text-lg font-bold text-slate-900">Ventes par service</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr class="text-left text-xs uppercase tracking-wide text-slate-600">
                            <th class="px-4 py-3">Service</th>
                            <th class="px-4 py-3 text-right">Commandes</th>
                            <th class="px-4 py-3 text-right">Montant</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($salesByService as $row)
                            <tr>
                                <td class="px-4 py-3 font-medium text-slate-900">{{ $row->service_name }}</td>
                                <td class="px-4 py-3 text-right text-slate-700">{{ $row->orders_count }}</td>
                                <td class="px-4 py-3 text-right font-semibold text-slate-900">{{ number_format((float) $row->orders_amount, 2, '.', ' ') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-8 text-center text-slate-500">Aucune donnée</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
            <div class="border-b border-slate-200 px-5 py-4 sm:px-6">
                <h2 class="text-lg font-bold text-slate-900">Paiements par utilisateur</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr class="text-left text-xs uppercase tracking-wide text-slate-600">
                            <th class="px-4 py-3">Utilisateur</th>
                            <th class="px-4 py-3 text-right">Paiements</th>
                            <th class="px-4 py-3 text-right">Montant</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($paymentsByUser as $row)
                            <tr>
                                <td class="px-4 py-3 font-medium text-slate-900">{{ $row->user_name }}</td>
                                <td class="px-4 py-3 text-right text-slate-700">{{ $row->payments_count }}</td>
                                <td class="px-4 py-3 text-right font-semibold text-slate-900">{{ number_format((float) $row->payments_amount, 2, '.', ' ') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-8 text-center text-slate-500">Aucune donnée</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        <section class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
            <div class="border-b border-slate-200 px-5 py-4 sm:px-6">
                <h2 class="text-lg font-bold text-slate-900">Commandes par utilisateur</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr class="text-left text-xs uppercase tracking-wide text-slate-600">
                            <th class="px-4 py-3">Utilisateur</th>
                            <th class="px-4 py-3 text-right">Commandes</th>
                            <th class="px-4 py-3 text-right">Montant</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($ordersByUser as $row)
                            <tr>
                                <td class="px-4 py-3 font-medium text-slate-900">{{ $row->user_name }}</td>
                                <td class="px-4 py-3 text-right text-slate-700">{{ $row->orders_count }}</td>
                                <td class="px-4 py-3 text-right font-semibold text-slate-900">{{ number_format((float) $row->orders_amount, 2, '.', ' ') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-8 text-center text-slate-500">Aucune donnée</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
            <div class="border-b border-slate-200 px-5 py-4 sm:px-6">
                <h2 class="text-lg font-bold text-slate-900">Charge de service (commandes)</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr class="text-left text-xs uppercase tracking-wide text-slate-600">
                            <th class="px-4 py-3">Service</th>
                            <th class="px-4 py-3 text-right">Commandes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($serviceAreaLoad as $area)
                            <tr>
                                <td class="px-4 py-3 font-medium text-slate-900">{{ $area->name }}</td>
                                <td class="px-4 py-3 text-right text-slate-700">{{ $area->period_orders_count }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="px-4 py-8 text-center text-slate-500">Aucune donnée</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl border border-sky-200 bg-sky-50 p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-sky-700">Stock entrées (qté)</p>
            <p class="mt-2 text-3xl font-black text-sky-900">{{ number_format($stockInQty, 2, '.', ' ') }}</p>
            <p class="mt-1 text-xs text-sky-700">Montant: {{ number_format($stockInAmount, 2, '.', ' ') }}</p>
        </div>
        <div class="rounded-2xl border border-rose-200 bg-rose-50 p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-rose-700">Stock sorties (qté)</p>
            <p class="mt-2 text-3xl font-black text-rose-900">{{ number_format($stockOutQty, 2, '.', ' ') }}</p>
            <p class="mt-1 text-xs text-rose-700">Montant: {{ number_format($stockOutAmount, 2, '.', ' ') }}</p>
        </div>
        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-amber-700">Delta qté</p>
            <p class="mt-2 text-3xl font-black text-amber-900">{{ number_format($stockInQty - $stockOutQty, 2, '.', ' ') }}</p>
        </div>
        <div class="rounded-2xl border border-violet-200 bg-violet-50 p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-violet-700">Delta valorisé</p>
            <p class="mt-2 text-3xl font-black text-violet-900">{{ number_format($stockInAmount - $stockOutAmount, 2, '.', ' ') }}</p>
        </div>
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        <section class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
            <div class="border-b border-slate-200 px-5 py-4 sm:px-6">
                <h2 class="text-lg font-bold text-slate-900">Stock par service</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr class="text-left text-xs uppercase tracking-wide text-slate-600">
                            <th class="px-4 py-3">Service</th>
                            <th class="px-4 py-3 text-right">Entrées</th>
                            <th class="px-4 py-3 text-right">Sorties</th>
                            <th class="px-4 py-3 text-right">Sorties valorisées</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($stockByService as $row)
                            <tr>
                                <td class="px-4 py-3 font-medium text-slate-900">{{ $row->service_name }}</td>
                                <td class="px-4 py-3 text-right text-slate-700">{{ number_format((float) $row->in_qty, 2, '.', ' ') }}</td>
                                <td class="px-4 py-3 text-right text-slate-700">{{ number_format((float) $row->out_qty, 2, '.', ' ') }}</td>
                                <td class="px-4 py-3 text-right font-semibold text-slate-900">{{ number_format((float) $row->out_amount, 2, '.', ' ') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-slate-500">Aucune donnée</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
            <div class="border-b border-slate-200 px-5 py-4 sm:px-6">
                <h2 class="text-lg font-bold text-slate-900">Stock par utilisateur</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr class="text-left text-xs uppercase tracking-wide text-slate-600">
                            <th class="px-4 py-3">Utilisateur</th>
                            <th class="px-4 py-3 text-right">Mouvements</th>
                            <th class="px-4 py-3 text-right">Entrées</th>
                            <th class="px-4 py-3 text-right">Sorties</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($stockByUser as $row)
                            <tr>
                                <td class="px-4 py-3 font-medium text-slate-900">{{ $row->user_name }}</td>
                                <td class="px-4 py-3 text-right text-slate-700">{{ $row->movement_count }}</td>
                                <td class="px-4 py-3 text-right text-slate-700">{{ number_format((float) $row->in_qty, 2, '.', ' ') }}</td>
                                <td class="px-4 py-3 text-right text-slate-700">{{ number_format((float) $row->out_qty, 2, '.', ' ') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-slate-500">Aucune donnée</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <section class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
        <div class="border-b border-slate-200 px-5 py-4 sm:px-6">
            <h2 class="text-lg font-bold text-slate-900">Top articles consommés (sorties stock)</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr class="text-left text-xs uppercase tracking-wide text-slate-600">
                        <th class="px-4 py-3">Article</th>
                        <th class="px-4 py-3 text-right">Quantité sortie</th>
                        <th class="px-4 py-3 text-right">Montant sortie</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($topProductsOut as $row)
                        <tr>
                            <td class="px-4 py-3 font-medium text-slate-900">{{ $row->product_name }}</td>
                            <td class="px-4 py-3 text-right text-slate-700">{{ number_format((float) $row->out_qty, 2, '.', ' ') }}</td>
                            <td class="px-4 py-3 text-right font-semibold text-slate-900">{{ number_format((float) $row->out_amount, 2, '.', ' ') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-8 text-center text-slate-500">Aucune donnée</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
