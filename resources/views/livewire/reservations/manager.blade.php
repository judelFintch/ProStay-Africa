<div class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
    @php
        $roomStatusLabels = [
            'available' => 'Disponible',
            'occupied' => 'Occupée',
            'cleaning' => 'Nettoyage',
            'maintenance' => 'Maintenance',
        ];
        $reservationStatusLabels = [
            'pending' => 'En attente',
            'confirmed' => 'Confirmée',
            'cancelled' => 'Annulée',
            'checked_in' => 'Enregistrée',
            'checked_out' => 'Clôturée',
            'no_show' => 'No-show',
        ];
    @endphp

    <div class="rounded-3xl bg-gradient-to-br from-slate-900 via-slate-800 to-emerald-900 p-6 text-white shadow-xl sm:p-8">
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-300">Réception</p>
        <h1 class="mt-2 text-2xl font-black sm:text-3xl">Réservations</h1>
        <p class="mt-2 text-sm text-slate-200/90">Créez des réservations, enregistrez les arrivées et suivez le cycle des séjours.</p>
    </div>

    <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 sm:p-6">
        <h2 class="text-lg font-bold text-slate-900">Nouvelle réservation</h2>

        <form wire:submit="createReservation" class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Client</label>
                <select wire:model="customer_id" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">Sélectionner un client</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}">{{ $customer->full_name ?? 'Sans nom' }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Chambre</label>
                <select wire:model="room_id" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">Sélectionner une chambre</option>
                    @foreach($rooms as $room)
                        <option value="{{ $room->id }}">Chambre {{ $room->number }} ({{ $roomStatusLabels[$room->status->value] ?? $room->status->value }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Arrivée</label>
                <input type="date" wire:model="check_in_date" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" />
            </div>

            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Départ</label>
                <input type="date" wire:model="check_out_date" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" />
            </div>

            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Adultes</label>
                <input type="number" min="1" wire:model="adults" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" />
            </div>

            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Enfants</label>
                <input type="number" min="0" wire:model="children" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" />
            </div>

            <div class="md:col-span-2 xl:col-span-3">
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Notes</label>
                <textarea wire:model="notes" rows="3" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="Demandes spéciales, préférences, détails d'arrivée..."></textarea>
            </div>

            <div class="md:col-span-2 xl:col-span-3">
                <button type="submit" class="inline-flex items-center rounded-xl bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-600">
                    Créer la réservation
                </button>
            </div>
        </form>
    </div>

    <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
        <div class="border-b border-slate-200 px-5 py-4 sm:px-6">
            <h2 class="text-lg font-bold text-slate-900">Réservations récentes</h2>
        </div>

        @error('checkin')
            <div class="border-b border-rose-200 bg-rose-50 px-5 py-3 text-sm font-semibold text-rose-700 sm:px-6">
                {{ $message }}
            </div>
        @enderror

        @error('reservation_action')
            <div class="border-b border-rose-200 bg-rose-50 px-5 py-3 text-sm font-semibold text-rose-700 sm:px-6">
                {{ $message }}
            </div>
        @enderror

        @if($edit_reservation_id)
            <form wire:submit="updateReservation" class="grid gap-3 border-b border-slate-200 bg-slate-50 px-5 py-4 sm:px-6 md:grid-cols-2 xl:grid-cols-6">
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Chambre</label>
                    <select wire:model="edit_room_id" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                        <option value="">Sélectionner une chambre</option>
                        @foreach($editRooms as $room)
                            <option value="{{ $room->id }}">Chambre {{ $room->number }} ({{ $roomStatusLabels[$room->status->value] ?? $room->status->value }})</option>
                        @endforeach
                    </select>
                    @error('edit_room_id') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Arrivée</label>
                    <input type="date" wire:model="edit_check_in_date" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" />
                    @error('edit_check_in_date') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Départ</label>
                    <input type="date" wire:model="edit_check_out_date" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" />
                    @error('edit_check_out_date') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Adultes</label>
                    <input type="number" min="1" wire:model="edit_adults" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" />
                </div>

                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Enfants</label>
                    <input type="number" min="0" wire:model="edit_children" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" />
                </div>

                <div class="flex items-end gap-2">
                    <button type="submit" class="rounded-lg bg-emerald-700 px-3 py-2 text-xs font-semibold text-white transition hover:bg-emerald-600">Enregistrer</button>
                    <button type="button" wire:click="cancelEdit" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-100">Fermer</button>
                </div>

                <div class="md:col-span-2 xl:col-span-6">
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Notes</label>
                    <textarea wire:model="edit_notes" rows="2" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"></textarea>
                </div>
            </form>
        @endif

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr class="text-left text-xs uppercase tracking-wide text-slate-600">
                        <th class="px-4 py-3">Client</th>
                        <th class="px-4 py-3">Chambre</th>
                        <th class="px-4 py-3">Dates</th>
                        <th class="px-4 py-3">Statut</th>
                        <th class="px-4 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($reservations as $reservation)
                        <tr class="align-middle">
                            <td class="px-4 py-3 font-medium text-slate-900">{{ $reservation->customer?->full_name ?? 'Inconnu' }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $reservation->room?->number ?? '-' }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $reservation->check_in_date?->format('Y-m-d') }} au {{ $reservation->check_out_date?->format('Y-m-d') }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">{{ $reservationStatusLabels[$reservation->status->value] ?? $reservation->status->value }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-2">
                                    @if($reservation->status->value !== $checkedInValue)
                                        <button wire:click="checkIn({{ $reservation->id }})" class="rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-slate-700">Enregistrer l'arrivée</button>
                                    @endif
                                    @if(in_array($reservation->status->value, ['pending', 'confirmed'], true))
                                        <button wire:click="startEdit({{ $reservation->id }})" class="rounded-lg bg-sky-700 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-sky-600">Modifier</button>
                                        <button wire:click="markNoShow({{ $reservation->id }})" class="rounded-lg bg-amber-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-amber-500">No-show</button>
                                    @endif
                                    <button wire:click="cancel({{ $reservation->id }})" class="rounded-lg bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-rose-500">Annuler</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-slate-500">Aucune réservation pour le moment.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
        <div class="border-b border-slate-200 px-5 py-4 sm:px-6">
            <div class="flex flex-wrap items-end justify-between gap-3">
                <h2 class="text-lg font-bold text-slate-900">Séjours actifs</h2>
                <div class="flex flex-wrap gap-2">
                    <button wire:click="setStayFilter('all')" class="rounded-full px-3 py-1 text-xs font-semibold transition {{ $stay_filter === 'all' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">Tous</button>
                    <button wire:click="setStayFilter('checkout_due')" class="rounded-full px-3 py-1 text-xs font-semibold transition {{ $stay_filter === 'checkout_due' ? 'bg-amber-600 text-white' : 'bg-amber-50 text-amber-700 hover:bg-amber-100' }}">Checkout à terme</button>
                    <button wire:click="setStayFilter('overdue')" class="rounded-full px-3 py-1 text-xs font-semibold transition {{ $stay_filter === 'overdue' ? 'bg-rose-600 text-white' : 'bg-rose-50 text-rose-700 hover:bg-rose-100' }}">En retard</button>
                </div>
            </div>
        </div>

        @error('checkout')
            <div class="border-b border-rose-200 bg-rose-50 px-5 py-3 text-sm font-semibold text-rose-700 sm:px-6">
                {{ $message }}
            </div>
        @enderror

        @if($invoice_notice)
            <div class="border-b border-emerald-200 bg-emerald-50 px-5 py-3 text-sm font-semibold text-emerald-700 sm:px-6">
                {{ $invoice_notice }}
            </div>
        @endif

        <div class="border-b border-slate-200 px-5 py-3 sm:px-6">
            <div class="flex flex-wrap items-end gap-3">
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Prolonger (nuits)</label>
                    <input type="number" min="1" max="30" wire:model="extend_nights" class="w-32 rounded-xl border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" />
                </div>
                <p class="text-xs text-slate-500">Utilise le bouton prolonger sur une ligne de séjour pour ajouter des nuits.</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr class="text-left text-xs uppercase tracking-wide text-slate-600">
                        <th class="px-4 py-3">Client</th>
                        <th class="px-4 py-3">Chambre</th>
                        <th class="px-4 py-3">Arrivée</th>
                        <th class="px-4 py-3">Départ prévu</th>
                        <th class="px-4 py-3">Facture</th>
                        <th class="px-4 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($activeStays as $stay)
                        <tr>
                            <td class="px-4 py-3 font-medium text-slate-900">{{ $stay->customer?->full_name ?? '-' }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $stay->room?->number ?? '-' }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $stay->check_in_at?->format('Y-m-d H:i') }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $stay->expected_check_out_at?->format('Y-m-d H:i') ?? '-' }}</td>
                            <td class="px-4 py-3 text-slate-700">
                                @php($stayInvoice = $stay->invoices->sortByDesc('issued_at')->first())
                                @if($stayInvoice)
                                    <div class="space-y-1">
                                        <div class="font-semibold text-slate-900">{{ $stayInvoice->reference }}</div>
                                        <div class="text-xs text-slate-500">Reste: {{ number_format($stayInvoice->balance, 2, '.', ' ') }}</div>
                                        @if($stayInvoice->balance > 0)
                                            <a href="{{ route('billing.payments', ['invoice' => $stayInvoice->id]) }}" wire:navigate class="text-xs font-semibold text-emerald-700 hover:text-emerald-600">Paiement</a>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-xs text-slate-400">Préparée au départ</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-2">
                                    <button wire:click="extendStay({{ $stay->id }})" class="rounded-lg bg-amber-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-amber-500">Prolonger</button>
                                    <button wire:click="prepareInvoice({{ $stay->id }})" class="rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-slate-700">Préparer la facture</button>
                                    <button wire:click="checkOut({{ $stay->id }})" class="rounded-lg bg-emerald-700 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-emerald-600">Clôturer le séjour</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-slate-500">Aucun séjour actif.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
