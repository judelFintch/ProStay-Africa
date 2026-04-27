@php
    $isAvailable = $room->status->value === 'available';
    $isOccupied = $room->status->value === 'occupied';
    $activeStay = $room->activeStay;
    $statusClass = match ($room->status->value) {
        'available' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
        'occupied' => 'border-amber-200 bg-amber-50 text-amber-700',
        'cleaning' => 'border-sky-200 bg-sky-50 text-sky-700',
        default => 'border-rose-200 bg-rose-50 text-rose-700',
    };
@endphp

<article class="flex h-full flex-col rounded-xl border border-slate-200 bg-white p-4 shadow-sm transition hover:shadow-md">
    <div class="flex items-start justify-between gap-3">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Chambre</p>
            <p class="mt-1 text-lg font-black text-slate-900">{{ $room->number }}</p>
        </div>
        <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold {{ $statusClass }}">
            <span class="mr-1.5 h-1.5 w-1.5 rounded-full bg-current"></span>
            {{ $statusLabels[$room->status->value] ?? str_replace('_', ' ', $room->status->value) }}
        </span>
    </div>

    <div class="mt-3 grid grid-cols-2 gap-2 text-xs text-slate-600">
        <p class="rounded-md border border-slate-200 bg-slate-50 px-2 py-1.5">Type: <span class="font-semibold text-slate-800">{{ $room->roomType?->name ?? '-' }}</span></p>
        <p class="rounded-md border border-slate-200 bg-slate-50 px-2 py-1.5">Etage: <span class="font-semibold text-slate-800">{{ $room->floor ?? '-' }}</span></p>
        <p class="rounded-md border border-slate-200 bg-slate-50 px-2 py-1.5">Capacite: <span class="font-semibold text-slate-800">{{ $room->capacity ?? '-' }}</span></p>
        <p class="rounded-md border border-slate-200 bg-slate-50 px-2 py-1.5">Tarif: <span class="font-semibold text-slate-800">{{ $currencySymbol }} {{ number_format((float) $room->price, 2, '.', ' ') }}</span></p>
    </div>

    <div class="mt-3 flex flex-wrap items-center gap-2 text-xs">
        <span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 font-semibold text-slate-700">
            Prestations: {{ (int) ($room->benefits_count ?? 0) }}
        </span>
        @if($activeStay)
            <span class="inline-flex max-w-full items-center rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 font-semibold text-slate-700">
                <span class="mr-1">Client:</span>
                <span class="max-w-[150px] truncate" title="{{ $activeStay->customer?->full_name ?? '-' }}">{{ $activeStay->customer?->full_name ?? '-' }}</span>
            </span>
        @endif
    </div>

    <div class="mt-3 grid grid-cols-2 gap-2">
        @foreach($statuses as $status)
            <button
                wire:click="setStatus({{ $room->id }}, '{{ $status }}')"
                class="rounded-md border px-2 py-1.5 text-xs font-semibold capitalize transition {{ $room->status->value === $status ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-300 bg-white text-slate-700 hover:bg-slate-50' }}"
            >
                {{ $statusLabels[$status] ?? str_replace('_', ' ', $status) }}
            </button>
        @endforeach
    </div>

    <div class="mt-3 grid gap-2 sm:grid-cols-3">
        <a
            href="{{ route('rooms.show', ['room' => $room->id]) }}"
            wire:navigate
            class="inline-flex items-center justify-center rounded-md border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50"
        >
            Details complets
        </a>
        <button
            wire:click="startEditRoom({{ $room->id }})"
            class="inline-flex items-center justify-center rounded-md border border-slate-900 bg-slate-900 px-3 py-2 text-xs font-semibold text-white transition hover:bg-slate-800"
        >
            Modifier
        </button>
        <button
            wire:click="toggleHistory({{ $room->id }})"
            class="inline-flex items-center justify-center rounded-md border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50"
        >
            {{ $historyRoomId === $room->id ? 'Masquer historique' : 'Voir historique' }}
        </button>
    </div>

    @if($isOccupied)
        <div class="mt-3">
            <button
                wire:click="toggleOccupationDetails({{ $room->id }})"
                class="inline-flex items-center rounded-md border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50"
            >
                {{ $expandedRoomId === $room->id ? 'Masquer l\'occupation' : 'Voir l\'occupation' }}
            </button>
        </div>

        @if($expandedRoomId === $room->id)
            <div class="mt-3 rounded-lg border border-slate-200 bg-slate-50 p-3 text-xs text-slate-700">
                @if($activeStay)
                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                        <p><span class="font-semibold">Client:</span> {{ $activeStay->customer?->full_name ?? '-' }}</p>
                        <p><span class="font-semibold">Check-in:</span> {{ $activeStay->check_in_at?->format('Y-m-d H:i') ?? '-' }}</p>
                        <p><span class="font-semibold">Check-out prevu:</span> {{ $activeStay->expected_check_out_at?->format('Y-m-d H:i') ?? '-' }}</p>
                        <p><span class="font-semibold">Nuits ecoulees:</span> {{ $activeStay->check_in_at ? $activeStay->check_in_at->startOfDay()->diffInDays(now()->startOfDay()) : 0 }}</p>
                        <p><span class="font-semibold">Statut sejour:</span> {{ $stayStatusLabels[$activeStay->status->value] ?? $activeStay->status->value }}</p>
                        <p><span class="font-semibold">Statut reservation:</span> {{ $reservationStatusLabels[$activeStay->reservation?->status?->value] ?? ($activeStay->reservation?->status?->value ?? '-') }}</p>
                    </div>
                @else
                    <p class="font-semibold text-rose-700">Aucun sejour actif lie a cette chambre occupee.</p>
                @endif
            </div>
        @endif
    @endif

    @if($isAvailable)
        <p class="mt-3 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-600">
            Chambre liberee: le sejour actif et la reservation en cours sont clotures automatiquement.
        </p>
    @endif

    @if($historyRoomId === $room->id)
        <div class="mt-3 rounded-xl border border-slate-200 bg-slate-50 p-3">
            <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-600">Historique des modifications</p>

            <div class="space-y-2">
                @forelse($roomHistoryEntries as $entry)
                    <div class="rounded-lg border border-slate-200 bg-white p-2.5 text-xs">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <p class="font-semibold text-slate-900">{{ $auditActionLabels[$entry['action']] ?? $entry['action'] }}</p>
                            <p class="text-slate-500">{{ $entry['created_at']?->format('d/m/Y H:i') }}</p>
                        </div>

                        <p class="mt-1 text-slate-500">Par: {{ $entry['user_name'] }}</p>

                        @if(! empty($entry['changes']))
                            <div class="mt-2 space-y-1 text-slate-700">
                                @foreach($entry['changes'] as $change)
                                    <p>
                                        <span class="font-semibold">{{ $historyFieldLabels[$change['key']] ?? $change['key'] }}:</span>
                                        {{ $change['old'] ?? '-' }}
                                        <span class="px-1 text-slate-400">-></span>
                                        {{ $change['new'] ?? '-' }}
                                    </p>
                                @endforeach
                            </div>
                        @endif

                        @if(! empty($entry['side_effects']['stay_closed_id']) || ! empty($entry['side_effects']['reservation_closed_id']))
                            <div class="mt-2 rounded-md bg-emerald-50 px-2 py-1.5 text-emerald-700">
                                @if(! empty($entry['side_effects']['stay_closed_id']))
                                    <p>Sejour cloture: #{{ $entry['side_effects']['stay_closed_id'] }}</p>
                                @endif
                                @if(! empty($entry['side_effects']['reservation_closed_id']))
                                    <p>Reservation cloturee: #{{ $entry['side_effects']['reservation_closed_id'] }}</p>
                                @endif
                            </div>
                        @endif
                    </div>
                @empty
                    <p class="text-xs text-slate-500">Aucune trace d'historique pour cette chambre.</p>
                @endforelse
            </div>
        </div>
    @endif
</article>
