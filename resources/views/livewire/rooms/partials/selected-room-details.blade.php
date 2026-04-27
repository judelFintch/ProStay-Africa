@if($selectedRoom)
    @php
        $selectedStatusClass = match ($selectedRoom->status->value) {
            'available' => 'bg-emerald-100 text-emerald-800',
            'occupied' => 'bg-amber-100 text-amber-800',
            'cleaning' => 'bg-sky-100 text-sky-800',
            default => 'bg-rose-100 text-rose-800',
        };
        $invoiceStatusLabels = [
            'draft' => 'Brouillon',
            'unpaid' => 'Impayee',
            'partially_paid' => 'Partiellement payee',
            'paid' => 'Payee',
            'cancelled' => 'Annulee',
        ];
        $orderStatusLabels = [
            'draft' => 'Brouillon',
            'pending' => 'En attente',
            'preparing' => 'En preparation',
            'ready' => 'Prete',
            'served' => 'Servie',
            'cancelled' => 'Annulee',
        ];
    @endphp

    <section class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
        <div class="border-b border-slate-200 bg-slate-50 px-5 py-4 sm:px-6">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Details chambre</p>
                    <h2 class="mt-1 text-xl font-black text-slate-900">Chambre {{ $selectedRoom->number }}</h2>
                    <p class="mt-1 text-sm text-slate-600">
                        Type: {{ $selectedRoom->roomType?->name ?? '-' }} · Etage: {{ $selectedRoom->floor ?? '-' }} · Capacite: {{ $selectedRoom->capacity }}
                    </p>
                </div>
                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $selectedStatusClass }}">
                    {{ $statusLabels[$selectedRoom->status->value] ?? $selectedRoom->status->value }}
                </span>
            </div>
        </div>

        <div class="space-y-5 p-5 sm:p-6">
            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-xl border border-slate-200 bg-white px-3 py-2.5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Tarif</p>
                    <p class="mt-1 text-lg font-black text-slate-900">{{ $currencySymbol }} {{ number_format((float) $selectedRoom->price, 2, '.', ' ') }} {{ $currency }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white px-3 py-2.5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Sejours (total)</p>
                    <p class="mt-1 text-lg font-black text-slate-900">{{ $selectedRoomStats['stays_count'] }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white px-3 py-2.5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Reservations (total)</p>
                    <p class="mt-1 text-lg font-black text-slate-900">{{ $selectedRoomStats['reservations_count'] }}</p>
                </div>
                <div class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2.5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-rose-700">Solde ouvert</p>
                    <p class="mt-1 text-lg font-black text-rose-800">{{ $currencySymbol }} {{ number_format((float) $selectedRoomStats['open_invoice_balance'], 2, '.', ' ') }} {{ $currency }}</p>
                </div>
            </div>

            <div class="grid gap-4 xl:grid-cols-2">
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3.5">
                    <div class="mb-2 flex items-center justify-between gap-2">
                        <p class="text-sm font-bold text-slate-900">Occupation en cours</p>
                        <button wire:click="startEditRoom({{ $selectedRoom->id }})" class="rounded-lg bg-cyan-700 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-cyan-600">Modifier la chambre</button>
                    </div>

                    @if($selectedRoom->activeStay)
                        <div class="space-y-1 text-xs text-slate-700">
                            <p><span class="font-semibold">Client:</span> {{ $selectedRoom->activeStay->customer?->full_name ?? '-' }}</p>
                            <p><span class="font-semibold">Check-in:</span> {{ $selectedRoom->activeStay->check_in_at?->format('Y-m-d H:i') ?? '-' }}</p>
                            <p><span class="font-semibold">Check-out prevu:</span> {{ $selectedRoom->activeStay->expected_check_out_at?->format('Y-m-d H:i') ?? '-' }}</p>
                            <p><span class="font-semibold">Statut sejour:</span> {{ $stayStatusLabels[$selectedRoom->activeStay->status->value] ?? $selectedRoom->activeStay->status->value }}</p>
                            <p><span class="font-semibold">Statut reservation:</span> {{ $reservationStatusLabels[$selectedRoom->activeStay->reservation?->status?->value] ?? ($selectedRoom->activeStay->reservation?->status?->value ?? '-') }}</p>
                        </div>
                    @else
                        <p class="text-xs font-semibold text-slate-600">Aucun sejour actif pour cette chambre.</p>
                    @endif
                </div>

                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3.5">
                    <div class="mb-2 flex items-center justify-between gap-2">
                        <p class="text-sm font-bold text-slate-900">Consommation liee a la chambre</p>
                        <button wire:click="toggleHistory({{ $selectedRoom->id }})" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-100">{{ $historyRoomId === $selectedRoom->id ? 'Masquer historique' : 'Voir historique' }}</button>
                    </div>
                    <div class="space-y-1 text-xs text-slate-700">
                        <p><span class="font-semibold">Montant des commandes:</span> {{ $currencySymbol }} {{ number_format((float) $selectedRoomStats['orders_total'], 2, '.', ' ') }} {{ $currency }}</p>
                        <p><span class="font-semibold">Factures recentes:</span> {{ $recentInvoices->count() }}</p>
                        <p><span class="font-semibold">Commandes recentes:</span> {{ $recentOrders->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="grid gap-4 xl:grid-cols-2">
                <div class="rounded-xl border border-slate-200 bg-white p-3.5">
                    <p class="mb-2 text-sm font-bold text-slate-900">Historique d'occupation</p>
                    <div class="max-h-56 space-y-2 overflow-auto pr-1">
                        @forelse($occupancyHistory as $stay)
                            <div class="rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-2 text-xs">
                                <p class="font-semibold text-slate-900">{{ $stay->customer?->full_name ?? 'Client non renseigne' }}</p>
                                <p class="text-slate-600">{{ $stay->check_in_at?->format('d/m/Y H:i') ?? '-' }} -> {{ $stay->check_out_at?->format('d/m/Y H:i') ?? 'en cours' }}</p>
                                <p class="text-slate-500">Statut: {{ $stayStatusLabels[$stay->status->value] ?? $stay->status->value }}</p>
                            </div>
                        @empty
                            <p class="text-xs text-slate-500">Aucun historique d'occupation pour cette chambre.</p>
                        @endforelse
                    </div>
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-3.5">
                    <p class="mb-2 text-sm font-bold text-slate-900">Historique des reservations</p>
                    <div class="max-h-56 space-y-2 overflow-auto pr-1">
                        @forelse($reservationHistory as $reservation)
                            <div class="rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-2 text-xs">
                                <p class="font-semibold text-slate-900">{{ $reservation->customer?->full_name ?? 'Client non renseigne' }}</p>
                                <p class="text-slate-600">{{ $reservation->check_in_date?->format('d/m/Y') ?? '-' }} -> {{ $reservation->check_out_date?->format('d/m/Y') ?? '-' }}</p>
                                <p class="text-slate-500">Statut: {{ $reservationStatusLabels[$reservation->status->value] ?? $reservation->status->value }}</p>
                            </div>
                        @empty
                            <p class="text-xs text-slate-500">Aucun historique de reservation pour cette chambre.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="grid gap-4 xl:grid-cols-2">
                <div class="rounded-xl border border-slate-200 bg-white p-3.5">
                    <p class="mb-2 text-sm font-bold text-slate-900">Factures recentes</p>
                    <div class="max-h-56 space-y-2 overflow-auto pr-1">
                        @forelse($recentInvoices as $invoice)
                            <div class="rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-2 text-xs">
                                <p class="font-semibold text-slate-900">{{ $invoice->reference }}</p>
                                <p class="text-slate-600">Client: {{ $invoice->customer?->full_name ?? 'Non renseigne' }}</p>
                                <p class="text-slate-600">Total: {{ number_format((float) $invoice->total, 2, '.', ' ') }} {{ strtoupper((string) $invoice->currency) }}</p>
                                <p class="text-slate-500">Statut: {{ $invoiceStatusLabels[$invoice->status->value] ?? $invoice->status->value }}</p>
                            </div>
                        @empty
                            <p class="text-xs text-slate-500">Aucune facture liee a cette chambre.</p>
                        @endforelse
                    </div>
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-3.5">
                    <p class="mb-2 text-sm font-bold text-slate-900">Commandes recentes</p>
                    <div class="max-h-56 space-y-2 overflow-auto pr-1">
                        @forelse($recentOrders as $order)
                            <div class="rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-2 text-xs">
                                <p class="font-semibold text-slate-900">{{ $order->reference }}</p>
                                <p class="text-slate-600">Client: {{ $order->customer?->full_name ?? 'Non renseigne' }}</p>
                                <p class="text-slate-600">Montant: {{ number_format((float) $order->total, 2, '.', ' ') }} {{ strtoupper((string) $order->currency) }}</p>
                                <p class="text-slate-500">Statut: {{ $orderStatusLabels[$order->status->value] ?? $order->status->value }}</p>
                            </div>
                        @empty
                            <p class="text-xs text-slate-500">Aucune commande liee a cette chambre.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </section>
@endif
