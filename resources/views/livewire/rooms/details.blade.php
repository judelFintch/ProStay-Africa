<div class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
    @php
        $statusLabels = [
            'available'   => 'Disponible',
            'occupied'    => 'Occupée',
            'cleaning'    => 'Nettoyage',
            'maintenance' => 'Maintenance',
        ];
        $stayStatusLabels = [
            'active'      => 'Actif',
            'checked_out' => 'Clôturé',
            'cancelled'   => 'Annulé',
        ];
        $reservationStatusLabels = [
            'pending'     => 'En attente',
            'confirmed'   => 'Confirmée',
            'cancelled'   => 'Annulée',
            'checked_in'  => 'Enregistrée',
            'checked_out' => 'Clôturée',
            'no_show'     => 'No-show',
        ];
        $invoiceStatusLabels = [
            'draft'           => 'Brouillon',
            'unpaid'          => 'Impayée',
            'partially_paid'  => 'Partiellement payée',
            'paid'            => 'Payée',
            'cancelled'       => 'Annulée',
        ];
        $orderStatusLabels = [
            'draft'     => 'Brouillon',
            'pending'   => 'En attente',
            'preparing' => 'En préparation',
            'ready'     => 'Prête',
            'served'    => 'Servie',
            'cancelled' => 'Annulée',
        ];
        $auditActionLabels = [
            'room_updated'        => 'Modification',
            'room_status_updated' => 'Changement de statut',
        ];
        $historyFieldLabels = [
            'room_type_id' => 'Type de chambre',
            'number'       => 'Numéro',
            'floor'        => 'Étage',
            'capacity'     => 'Capacité',
            'price'        => 'Tarif',
            'status'       => 'Statut',
        ];
        $badgeClass = match ($room->status->value) {
            'available'   => 'border-emerald-200 bg-emerald-50 text-emerald-700',
            'occupied'    => 'border-amber-200 bg-amber-50 text-amber-700',
            'cleaning'    => 'border-sky-200 bg-sky-50 text-sky-700',
            default       => 'border-rose-200 bg-rose-50 text-rose-700',
        };
    @endphp

    {{-- ── HEADER ──────────────────────────────────────────────── --}}
    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Vue détaillée</p>
        <h1 class="mt-1 text-2xl font-black text-slate-900 sm:text-3xl">Chambre {{ $room->number }}</h1>
        <p class="mt-2 max-w-3xl text-sm text-slate-600">Consultez la fiche complète, mettez à jour les caractéristiques de la chambre et gérez ses prestations incluses.</p>
        <div class="mt-4 flex flex-wrap items-center gap-2">
            @if(! $editing)
                <button
                    wire:click="startEdit"
                    class="inline-flex items-center rounded-lg border border-slate-900 bg-slate-900 px-4 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-slate-800"
                >
                    Modifier la chambre
                </button>
            @endif
            <a
                href="{{ route('rooms.index') }}"
                wire:navigate
                class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50"
            >
                ← Retour au tableau
            </a>
            <a
                href="{{ route('rooms.benefits') }}"
                wire:navigate
                class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50"
            >
                Gerer les prestations
            </a>
        </div>
    </section>

    {{-- ── FORMULAIRE DE MODIFICATION ─────────────────────────── --}}
    @if($editing)
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 bg-slate-50 px-5 py-4 sm:px-6">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Modification</p>
                        <h2 class="text-lg font-black text-slate-900">Modifier · Chambre {{ $room->number }}</h2>
                    </div>
                    <button
                        wire:click="cancelEdit"
                        class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-100"
                    >
                        Annuler
                    </button>
                </div>
            </div>

            <form wire:submit="save" class="space-y-6 p-5 sm:p-6">

                {{-- Section 1 : Identification --}}
                <div>
                    <p class="mb-3 text-xs font-bold uppercase tracking-widest text-slate-500">Identification</p>
                    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Type de chambre</label>
                            <select wire:model="edit_room_type_id" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                <option value="">Sélectionner un type</option>
                                @foreach($roomTypes as $roomType)
                                    <option value="{{ $roomType->id }}">{{ $roomType->name }}</option>
                                @endforeach
                            </select>
                            @error('edit_room_type_id') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Numéro</label>
                            <input type="text" wire:model="edit_number" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" />
                            @error('edit_number') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Étage</label>
                            <input type="text" wire:model="edit_floor" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" />
                            @error('edit_floor') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Capacité</label>
                            <input type="number" min="1" wire:model="edit_capacity" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" />
                            @error('edit_capacity') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Tarif ({{ $currency }})</label>
                            <input type="number" min="0" step="0.01" wire:model="edit_price" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" />
                            @error('edit_price') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Statut</label>
                            <select wire:model="edit_status" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                @foreach($statuses as $s)
                                    <option value="{{ $s }}">{{ $statusLabels[$s] ?? $s }}</option>
                                @endforeach
                            </select>
                            @error('edit_status') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                {{-- Section 2 : Description & caractéristiques --}}
                <div class="border-t border-slate-100 pt-5">
                    <p class="mb-3 text-xs font-bold uppercase tracking-widest text-slate-500">Description & caractéristiques</p>
                    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                        <div class="sm:col-span-2 xl:col-span-3">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Description</label>
                            <textarea wire:model="edit_description" rows="3" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="Décrivez la chambre (vue, ambiance, particularités…)"></textarea>
                            @error('edit_description') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Surface (m²)</label>
                            <input type="number" min="1" step="0.1" wire:model="edit_surface_m2" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="Ex: 24" />
                            @error('edit_surface_m2') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Type de lit</label>
                            <select wire:model="edit_bed_type" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                <option value="">— Non précisé —</option>
                                <option value="single">Simple (Single)</option>
                                <option value="double">Double</option>
                                <option value="twin">Lits jumeaux (Twin)</option>
                                <option value="triple">Triple</option>
                                <option value="queen">Queen</option>
                                <option value="king">King</option>
                                <option value="suite">Suite</option>
                            </select>
                            @error('edit_bed_type') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Vue</label>
                            <select wire:model="edit_view_type" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                <option value="">— Non précisé —</option>
                                <option value="sea">Mer</option>
                                <option value="pool">Piscine</option>
                                <option value="garden">Jardin</option>
                                <option value="city">Ville</option>
                                <option value="mountain">Montagne</option>
                                <option value="courtyard">Cour intérieure</option>
                                <option value="street">Rue</option>
                                <option value="none">Aucune vue particulière</option>
                            </select>
                            @error('edit_view_type') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                {{-- Section 3 : Équipements --}}
                <div class="border-t border-slate-100 pt-5">
                    <p class="mb-3 text-xs font-bold uppercase tracking-widest text-slate-500">Équipements</p>
                    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                        @foreach([
                            ['edit_has_private_bathroom', 'Salle de bain privée'],
                            ['edit_has_air_conditioning', 'Climatisation'],
                            ['edit_has_wifi', 'Wi-Fi'],
                            ['edit_has_tv', 'Télévision'],
                            ['edit_has_balcony', 'Balcon / Terrasse'],
                            ['edit_has_kitchenette', 'Kitchenette'],
                            ['edit_has_safe', 'Coffre-fort'],
                            ['edit_has_minibar', 'Minibar'],
                            ['edit_extra_bed_available', 'Lit supplémentaire possible'],
                            ['edit_smoking', 'Chambre fumeur'],
                        ] as [$field, $label])
                            <label class="flex cursor-pointer items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 transition hover:bg-slate-100">
                                <input type="checkbox" wire:model="{{ $field }}" class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" />
                                <span class="text-xs font-semibold text-slate-700">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Section 4 : Prestations incluses --}}
                <div class="border-t border-slate-100 pt-5">
                    <p class="mb-3 text-xs font-bold uppercase tracking-widest text-slate-500">Prestations incluses dans le séjour</p>

                    {{-- Ajouter une prestation --}}
                    @if($allBenefits->isNotEmpty())
                        <div class="mb-3 flex flex-wrap items-end gap-2">
                            <div class="flex-1 min-w-40">
                                <label class="mb-1 block text-xs font-semibold text-slate-600">Ajouter une prestation</label>
                                <select wire:model="addBenefitId" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                    <option value="">— Choisir —</option>
                                    @foreach($allBenefits as $benefit)
                                        <option value="{{ $benefit->id }}">{{ $benefit->icon ? $benefit->icon.' ' : '' }}{{ $benefit->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="w-24">
                                <label class="mb-1 block text-xs font-semibold text-slate-600">Qté/séjour</label>
                                <input type="number" min="1" wire:model="addBenefitQty" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" />
                            </div>
                            <button type="button" wire:click="addBenefit" class="rounded-xl bg-emerald-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-emerald-500">
                                + Ajouter
                            </button>
                        </div>
                    @else
                        <p class="mb-3 text-xs text-amber-600">Aucune prestation active configurée. Créez des prestations d'abord.</p>
                    @endif

                    {{-- Liste des prestations sélectionnées --}}
                    <div class="space-y-2">
                        @forelse($selectedBenefits as $index => $sb)
                            <div class="flex items-center justify-between rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2">
                                <span class="text-sm font-semibold text-emerald-900">{{ $sb['icon'] ? $sb['icon'].' ' : '' }}{{ $sb['name'] }}</span>
                                <div class="flex items-center gap-3">
                                    <span class="text-xs text-emerald-700">× {{ $sb['quantity_per_stay'] }} / séjour</span>
                                    <button type="button" wire:click="removeBenefit({{ $index }})" class="text-xs font-semibold text-rose-600 transition hover:text-rose-800">Retirer</button>
                                </div>
                            </div>
                        @empty
                            <p class="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-3 text-xs text-slate-500">Aucune prestation incluse dans cette chambre.</p>
                        @endforelse
                    </div>
                </div>

                {{-- Section 5 : Notes internes --}}
                <div class="border-t border-slate-100 pt-5">
                    <p class="mb-3 text-xs font-bold uppercase tracking-widest text-slate-500">Notes internes</p>
                    <textarea wire:model="edit_internal_notes" rows="3" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500" placeholder="Notes visibles uniquement en interne (état, travaux prévus…)"></textarea>
                    @error('edit_internal_notes') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div class="border-t border-slate-100 pt-4">
                    <button
                        type="submit"
                        class="inline-flex items-center rounded-xl border border-slate-900 bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800"
                    >
                        Enregistrer les modifications
                    </button>
                </div>
            </form>
        </section>
    @endif

    {{-- ── FICHE CHAMBRE ───────────────────────────────────────── --}}
    <section class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
        <div class="border-b border-slate-200 bg-slate-50 px-5 py-4 sm:px-6">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Informations chambre</p>
                    <p class="mt-1 text-sm text-slate-600">
                        Type: <span class="font-semibold text-slate-800">{{ $room->roomType?->name ?? '-' }}</span>
                        · Étage: <span class="font-semibold text-slate-800">{{ $room->floor ?? '-' }}</span>
                        · Capacité: <span class="font-semibold text-slate-800">{{ $room->capacity }}</span>
                        @if($room->surface_m2) · <span class="font-semibold text-slate-800">{{ $room->surface_m2 }} m²</span> @endif
                    </p>
                    @if($room->description)
                        <p class="mt-1 text-xs text-slate-500 italic">{{ $room->description }}</p>
                    @endif
                </div>
                <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold {{ $badgeClass }}">
                    <span class="mr-1.5 h-1.5 w-1.5 rounded-full bg-current"></span>
                    {{ $statusLabels[$room->status->value] ?? $room->status->value }}
                </span>
            </div>
        </div>

        <div class="space-y-4 p-5 sm:p-6">
            {{-- Stats --}}
            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-xl border border-slate-200 bg-white px-3 py-2.5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Tarif</p>
                    <p class="mt-1 text-lg font-black text-slate-900">{{ $currencySymbol }} {{ number_format((float) $room->price, 2, '.', ' ') }} {{ $currency }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white px-3 py-2.5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Séjours (total)</p>
                    <p class="mt-1 text-lg font-black text-slate-900">{{ $stats['stays_count'] }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white px-3 py-2.5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Réservations (total)</p>
                    <p class="mt-1 text-lg font-black text-slate-900">{{ $stats['reservations_count'] }}</p>
                </div>
                <div class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2.5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-rose-700">Solde ouvert</p>
                    <p class="mt-1 text-lg font-black text-rose-800">{{ $currencySymbol }} {{ number_format((float) $stats['open_invoice_balance'], 2, '.', ' ') }} {{ $currency }}</p>
                </div>
            </div>

            {{-- Caractéristiques --}}
            @php
                $bedLabels = ['single'=>'Simple','double'=>'Double','twin'=>'Lits jumeaux','triple'=>'Triple','queen'=>'Queen','king'=>'King','suite'=>'Suite'];
                $viewLabels = ['sea'=>'Mer','pool'=>'Piscine','garden'=>'Jardin','city'=>'Ville','mountain'=>'Montagne','courtyard'=>'Cour intérieure','street'=>'Rue','none'=>'Aucune vue'];
                $amenities = [];
                if($room->has_private_bathroom) $amenities[] = 'Salle de bain privée';
                if($room->has_air_conditioning) $amenities[] = 'Climatisation';
                if($room->has_wifi) $amenities[] = 'Wi-Fi';
                if($room->has_tv) $amenities[] = 'TV';
                if($room->has_balcony) $amenities[] = 'Balcon';
                if($room->has_kitchenette) $amenities[] = 'Kitchenette';
                if($room->has_safe) $amenities[] = 'Coffre-fort';
                if($room->has_minibar) $amenities[] = 'Minibar';
                if($room->extra_bed_available) $amenities[] = 'Lit suppl. possible';
                if($room->smoking) $amenities[] = 'Fumeur';
            @endphp

            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                @if($room->bed_type)
                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Type de lit</p>
                        <p class="mt-1 text-sm font-semibold text-slate-900">{{ $bedLabels[$room->bed_type] ?? $room->bed_type }}</p>
                    </div>
                @endif
                @if($room->view_type)
                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Vue</p>
                        <p class="mt-1 text-sm font-semibold text-slate-900">{{ $viewLabels[$room->view_type] ?? $room->view_type }}</p>
                    </div>
                @endif
            </div>

            @if(count($amenities) > 0)
                <div>
                    <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">Équipements</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach($amenities as $amenity)
                            <span class="inline-flex rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-semibold text-slate-700">{{ $amenity }}</span>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Prestations incluses --}}
            @if($room->benefits->isNotEmpty())
                <div>
                    <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">Prestations incluses</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach($room->benefits as $benefit)
                            <span class="inline-flex items-center gap-1 rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-semibold text-slate-700">
                                {{ $benefit->icon ? $benefit->icon.' ' : '' }}{{ $benefit->name }}
                                <span class="ml-1 rounded-full bg-slate-200 px-1.5 text-slate-700">× {{ $benefit->pivot->quantity_per_stay }}</span>
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($room->internal_notes)
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                    <p class="mb-1 text-xs font-bold uppercase tracking-wide text-slate-600">Notes internes</p>
                    <p class="text-xs text-slate-700">{{ $room->internal_notes }}</p>
                </div>
            @endif
        </div>
    </section>

    {{-- ── OCCUPATION EN COURS ─────────────────────────────────── --}}
    <div class="grid gap-4 xl:grid-cols-2">
        <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 sm:p-6">
            <p class="mb-3 text-sm font-bold text-slate-900">Occupation en cours</p>
            @if($room->activeStay)
                <dl class="grid grid-cols-2 gap-x-4 gap-y-2 text-xs">
                    <div><dt class="font-semibold text-slate-600">Client</dt><dd class="text-slate-900">{{ $room->activeStay->customer?->full_name ?? '-' }}</dd></div>
                    <div><dt class="font-semibold text-slate-600">Check-in</dt><dd class="text-slate-900">{{ $room->activeStay->check_in_at?->format('d/m/Y H:i') ?? '-' }}</dd></div>
                    <div><dt class="font-semibold text-slate-600">Check-out prévu</dt><dd class="text-slate-900">{{ $room->activeStay->expected_check_out_at?->format('d/m/Y H:i') ?? '-' }}</dd></div>
                    <div><dt class="font-semibold text-slate-600">Nuits écoulées</dt><dd class="text-slate-900">{{ $room->activeStay->check_in_at ? $room->activeStay->check_in_at->startOfDay()->diffInDays(now()->startOfDay()) : 0 }}</dd></div>
                    <div><dt class="font-semibold text-slate-600">Statut séjour</dt><dd class="text-slate-900">{{ $stayStatusLabels[$room->activeStay->status->value] ?? $room->activeStay->status->value }}</dd></div>
                    <div><dt class="font-semibold text-slate-600">Statut réservation</dt><dd class="text-slate-900">{{ $reservationStatusLabels[$room->activeStay->reservation?->status?->value] ?? ($room->activeStay->reservation?->status?->value ?? '-') }}</dd></div>
                </dl>
            @else
                <p class="text-xs text-slate-500">Aucun séjour actif pour cette chambre.</p>
            @endif
        </section>

        <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 sm:p-6">
            <p class="mb-3 text-sm font-bold text-slate-900">Consommation liée</p>
            <dl class="grid grid-cols-2 gap-x-4 gap-y-2 text-xs">
                <div><dt class="font-semibold text-slate-600">Commandes</dt><dd class="text-slate-900">{{ $currencySymbol }} {{ number_format((float) $stats['orders_total'], 2, '.', ' ') }} {{ $currency }}</dd></div>
                <div><dt class="font-semibold text-slate-600">Factures</dt><dd class="text-slate-900">{{ $recentInvoices->count() }}</dd></div>
                <div><dt class="font-semibold text-slate-600">Ordres récents</dt><dd class="text-slate-900">{{ $recentOrders->count() }}</dd></div>
            </dl>
        </section>
    </div>

    {{-- ── HISTORIQUES ─────────────────────────────────────────── --}}
    <div class="grid gap-4 xl:grid-cols-2">
        <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 sm:p-6">
            <p class="mb-3 text-sm font-bold text-slate-900">Historique d'occupation</p>
            <div class="max-h-60 space-y-2 overflow-auto pr-1">
                @forelse($occupancyHistory as $stay)
                    <div class="rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-2 text-xs">
                        <p class="font-semibold text-slate-900">{{ $stay->customer?->full_name ?? 'Client non renseigné' }}</p>
                        <p class="text-slate-600">{{ $stay->check_in_at?->format('d/m/Y H:i') ?? '-' }} → {{ $stay->check_out_at?->format('d/m/Y H:i') ?? 'en cours' }}</p>
                        <p class="text-slate-500">{{ $stayStatusLabels[$stay->status->value] ?? $stay->status->value }}</p>
                    </div>
                @empty
                    <p class="text-xs text-slate-500">Aucun historique d'occupation.</p>
                @endforelse
            </div>
        </section>

        <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 sm:p-6">
            <p class="mb-3 text-sm font-bold text-slate-900">Historique des réservations</p>
            <div class="max-h-60 space-y-2 overflow-auto pr-1">
                @forelse($reservationHistory as $reservation)
                    <div class="rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-2 text-xs">
                        <p class="font-semibold text-slate-900">{{ $reservation->customer?->full_name ?? 'Client non renseigné' }}</p>
                        <p class="text-slate-600">{{ $reservation->check_in_date?->format('d/m/Y') ?? '-' }} → {{ $reservation->check_out_date?->format('d/m/Y') ?? '-' }}</p>
                        <p class="text-slate-500">{{ $reservationStatusLabels[$reservation->status->value] ?? $reservation->status->value }}</p>
                    </div>
                @empty
                    <p class="text-xs text-slate-500">Aucun historique de réservation.</p>
                @endforelse
            </div>
        </section>
    </div>

    {{-- ── FACTURES & COMMANDES ────────────────────────────────── --}}
    <div class="grid gap-4 xl:grid-cols-2">
        <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 sm:p-6">
            <p class="mb-3 text-sm font-bold text-slate-900">Factures récentes</p>
            <div class="max-h-60 space-y-2 overflow-auto pr-1">
                @forelse($recentInvoices as $invoice)
                    <div class="rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-2 text-xs">
                        <div class="flex items-center justify-between gap-2">
                            <p class="font-semibold text-slate-900">{{ $invoice->reference }}</p>
                            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-slate-700">{{ $invoiceStatusLabels[$invoice->status->value] ?? $invoice->status->value }}</span>
                        </div>
                        <p class="mt-1 text-slate-600">{{ $invoice->customer?->full_name ?? 'Non renseigné' }}</p>
                        <p class="text-slate-600 font-semibold">{{ number_format((float) $invoice->total, 2, '.', ' ') }} {{ strtoupper((string) $invoice->currency) }}</p>
                    </div>
                @empty
                    <p class="text-xs text-slate-500">Aucune facture liée.</p>
                @endforelse
            </div>
        </section>

        <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 sm:p-6">
            <p class="mb-3 text-sm font-bold text-slate-900">Commandes récentes</p>
            <div class="max-h-60 space-y-2 overflow-auto pr-1">
                @forelse($recentOrders as $order)
                    <div class="rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-2 text-xs">
                        <div class="flex items-center justify-between gap-2">
                            <p class="font-semibold text-slate-900">{{ $order->reference }}</p>
                            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-slate-700">{{ $orderStatusLabels[$order->status->value] ?? $order->status->value }}</span>
                        </div>
                        <p class="mt-1 text-slate-600">{{ $order->customer?->full_name ?? 'Non renseigné' }}</p>
                        <p class="text-slate-600 font-semibold">{{ number_format((float) $order->total, 2, '.', ' ') }} {{ strtoupper((string) $order->currency) }}</p>
                    </div>
                @empty
                    <p class="text-xs text-slate-500">Aucune commande liée.</p>
                @endforelse
            </div>
        </section>
    </div>

    {{-- ── HISTORIQUE DES MODIFICATIONS ───────────────────────── --}}
    <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 sm:p-6">
        <p class="mb-4 text-sm font-bold text-slate-900">Historique des modifications</p>
        <div class="space-y-3">
            @forelse($auditHistory as $entry)
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 text-xs">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <p class="font-semibold text-slate-900">{{ $auditActionLabels[$entry['action']] ?? $entry['action'] }}</p>
                        <p class="text-slate-500">{{ $entry['created_at']?->format('d/m/Y H:i') }}</p>
                    </div>
                    <p class="mt-1 text-slate-500">Par: <span class="font-semibold text-slate-700">{{ $entry['user_name'] }}</span></p>

                    @if(! empty($entry['changes']))
                        <div class="mt-2 space-y-1 text-slate-700">
                            @foreach($entry['changes'] as $change)
                                <p>
                                    <span class="font-semibold">{{ $historyFieldLabels[$change['key']] ?? $change['key'] }}:</span>
                                    <span class="text-rose-600 line-through">{{ $change['old'] ?? '-' }}</span>
                                    <span class="mx-1 text-slate-400">→</span>
                                    <span class="text-emerald-700 font-semibold">{{ $change['new'] ?? '-' }}</span>
                                </p>
                            @endforeach
                        </div>
                    @endif

                    @if(! empty($entry['side_effects']['stay_closed_id']) || ! empty($entry['side_effects']['reservation_closed_id']))
                        <div class="mt-2 rounded-md bg-emerald-50 px-2 py-1.5 text-emerald-700">
                            @if(! empty($entry['side_effects']['stay_closed_id']))
                                <p>Séjour clôturé: #{{ $entry['side_effects']['stay_closed_id'] }}</p>
                            @endif
                            @if(! empty($entry['side_effects']['reservation_closed_id']))
                                <p>Réservation clôturée: #{{ $entry['side_effects']['reservation_closed_id'] }}</p>
                            @endif
                        </div>
                    @endif
                </div>
            @empty
                <p class="text-xs text-slate-500">Aucun historique de modification pour cette chambre.</p>
            @endforelse
        </div>
    </section>
</div>
