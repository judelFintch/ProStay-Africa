<div class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
    @php
        $statusLabels = [
            'available' => 'Disponible',
            'occupied' => 'Occupée',
            'cleaning' => 'Nettoyage',
            'maintenance' => 'Maintenance',
        ];
        $auditActionLabels = [
            'room_updated' => 'Modification de la chambre',
            'room_status_updated' => 'Changement de statut',
        ];
        $historyFieldLabels = [
            'room_type_id' => 'Type de chambre',
            'number' => 'Numéro',
            'floor' => 'Étage',
            'capacity' => 'Capacité',
            'price' => 'Tarif',
            'status' => 'Statut',
        ];
        $stayStatusLabels = [
            'active' => 'Actif',
            'checked_out' => 'Clôturé',
            'cancelled' => 'Annulé',
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

    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Exploitation hôtelière</p>
                <h1 class="mt-1 text-2xl font-black text-slate-900 sm:text-3xl">Tableau des chambres</h1>
                <p class="mt-2 max-w-3xl text-sm text-slate-600">Suivez l occupation en temps reel, mettez a jour les statuts et gardez la traçabilite des changements.</p>
            </div>
            <a href="{{ route('rooms.benefits') }}" wire:navigate class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">
                Prestations incluses
            </a>
        </div>
    </section>

    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total chambres</p>
            <p class="mt-2 text-2xl font-black text-slate-900">{{ $totalRooms }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Disponibles</p>
            <p class="mt-2 text-2xl font-black text-emerald-700">{{ $availableRooms }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Occupées</p>
            <p class="mt-2 text-2xl font-black text-amber-700">{{ $occupiedRooms }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Taux d occupation</p>
            <p class="mt-2 text-2xl font-black text-slate-900">{{ number_format($occupancyRate, 1, '.', ' ') }}%</p>
            <p class="mt-1 text-xs text-slate-500">Nettoyage: {{ $cleaningRooms }}</p>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
        @php($hasFilters = $search !== '' || $filter !== 'all')

        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div class="space-y-3">
                <div class="flex flex-wrap items-center gap-2 text-xs text-slate-500">
                    <span class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-2 py-1 font-semibold text-emerald-700"><span class="mr-1.5 h-1.5 w-1.5 rounded-full bg-current"></span>Disponible</span>
                    <span class="inline-flex items-center rounded-full border border-amber-200 bg-amber-50 px-2 py-1 font-semibold text-amber-700"><span class="mr-1.5 h-1.5 w-1.5 rounded-full bg-current"></span>Occupée</span>
                    <span class="inline-flex items-center rounded-full border border-sky-200 bg-sky-50 px-2 py-1 font-semibold text-sky-700"><span class="mr-1.5 h-1.5 w-1.5 rounded-full bg-current"></span>Nettoyage</span>
                    <span class="inline-flex items-center rounded-full border border-rose-200 bg-rose-50 px-2 py-1 font-semibold text-rose-700"><span class="mr-1.5 h-1.5 w-1.5 rounded-full bg-current"></span>Maintenance</span>
                </div>

                <label class="block w-full lg:w-[420px]">
                    <span class="sr-only">Rechercher une chambre</span>
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Rechercher par numéro, étage ou type..."
                        class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                    />
                </label>
            </div>

            <div>
                <button
                    wire:click="resetFilters"
                    @disabled(! $hasFilters)
                    class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-3 py-2 text-xs font-semibold transition {{ $hasFilters ? 'bg-white text-slate-700 hover:bg-slate-50' : 'cursor-not-allowed bg-slate-100 text-slate-400' }}"
                >
                    Réinitialiser
                </button>
            </div>
        </div>

        <div class="mt-4 overflow-x-auto pb-1">
            <div class="flex w-max items-center gap-2">
            <button
                wire:click="setFilter('all')"
                class="rounded-full px-3 py-1.5 text-xs font-semibold transition {{ $filter === 'all' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}"
            >
                Toutes ({{ $totalRooms }})
            </button>

            @foreach($statuses as $status)
                <button
                    wire:click="setFilter('{{ $status }}')"
                    class="rounded-full px-3 py-1.5 text-xs font-semibold capitalize transition {{ $filter === $status ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}"
                >
                    {{ $statusLabels[$status] ?? str_replace('_', ' ', $status) }} ({{ $statusCounts[$status] ?? 0 }})
                </button>
            @endforeach
            </div>
        </div>

        @error('room_action')
            <div class="mt-3 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700">
                {{ $message }}
            </div>
        @enderror
    </div>

    @include('livewire.rooms.partials.edit-room-form')

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
        @forelse($rooms as $room)
            @include('livewire.rooms.partials.room-card')
        @empty
            <div class="sm:col-span-2 xl:col-span-3 2xl:col-span-4">
                <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-6 py-10 text-center text-sm font-medium text-slate-500">
                    Aucune chambre trouvée pour ce filtre.
                </div>
            </div>
        @endforelse
    </div>
</div>
