<div class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
    @php
        $laundryStatusLabels = [
            'dirty' => 'Sale',
            'washing' => 'En lavage',
            'clean' => 'Propre',
            'distributed' => 'Distribuée',
        ];
    @endphp

    <div class="rounded-3xl bg-gradient-to-br from-slate-900 via-indigo-900 to-sky-900 p-6 text-white shadow-xl sm:p-8">
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-sky-300">Blanchisserie</p>
        <h1 class="mt-2 text-2xl font-black sm:text-3xl">Suivi blanchisserie</h1>
        <p class="mt-2 text-sm text-slate-200/90">Suivez le cycle du linge, de la collecte à la distribution.</p>
    </div>

    <div class="prostay-surface p-5 sm:p-6">
        <h2 class="text-lg font-bold text-slate-900">Créer un lot de linge</h2>
        <form wire:submit="createItem" class="mt-4 grid gap-3 md:grid-cols-3">
            <input type="text" wire:model="name" class="prostay-input md:col-span-2" placeholder="Nom de l'article" />
            <input type="number" wire:model="quantity" min="1" class="prostay-input" placeholder="Quantité" />
            <button class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-700 md:col-span-3">Créer le lot</button>
        </form>
    </div>

    <div class="grid gap-4 xl:grid-cols-3">
        <div class="prostay-surface p-5 xl:col-span-2">
            <h2 class="text-lg font-bold text-slate-900">Articles de blanchisserie</h2>
            <div class="mt-4 space-y-3">
                @foreach($items as $item)
                    <div class="rounded-xl border border-slate-200 p-3">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <p class="font-semibold text-slate-900">{{ $item->name }}</p>
                                <p class="text-xs text-slate-500">Qté: {{ $item->quantity }} • Statut: {{ $laundryStatusLabels[$item->status->value] ?? $item->status->value }}</p>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                @foreach($statuses as $status)
                                    <button wire:click="moveStatus({{ $item->id }}, '{{ $status }}')" class="rounded-lg border px-2.5 py-1 text-xs font-semibold {{ $item->status->value === $status ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-300 bg-white text-slate-700' }}">
                                        {{ $laundryStatusLabels[$status] ?? $status }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="prostay-surface p-5">
            <h3 class="text-base font-bold text-slate-900">Opérations récentes</h3>
            <div class="mt-3 space-y-2">
                @foreach($operations as $operation)
                    <div class="rounded-xl border border-slate-200 px-3 py-2 text-xs">
                        <p class="font-semibold text-slate-900">{{ $operation->laundryItem?->name ?? '-' }}</p>
                        <p class="text-slate-600">{{ $laundryStatusLabels[$operation->from_status] ?? $operation->from_status }} → {{ $laundryStatusLabels[$operation->to_status] ?? $operation->to_status }}</p>
                        <p class="text-slate-500">{{ $operation->created_at->format('d/m H:i') }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
