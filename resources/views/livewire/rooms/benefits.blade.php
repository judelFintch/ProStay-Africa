<div class="mx-auto max-w-5xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">

    {{-- ── EN-TÊTE ─────────────────────────────────────────────── --}}
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-900 via-teal-800 to-cyan-700 p-6 text-white shadow-xl sm:p-8">
        <div class="pointer-events-none absolute -right-16 -top-16 h-48 w-48 rounded-full bg-white/10 blur-2xl"></div>
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-300">Gestion hôtelière</p>
        <h1 class="mt-2 text-2xl font-black sm:text-3xl">Prestations incluses</h1>
        <p class="mt-2 max-w-xl text-sm text-white/80">Configurez les avantages inclus dans le séjour (petit-déjeuner, navette, welcome drink…) et associez-les aux plats du menu qui en sont le support.</p>
        <div class="mt-4">
            <a href="{{ route('rooms.index') }}" wire:navigate class="inline-flex items-center gap-1.5 rounded-xl border border-white/30 bg-white/10 px-4 py-2 text-xs font-semibold text-white transition hover:bg-white/20">
                ← Tableau des chambres
            </a>
        </div>
    </div>

    {{-- ── BARRE D'ACTIONS ─────────────────────────────────────── --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex flex-wrap items-center gap-2">
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Rechercher…"
                class="rounded-xl border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
            />
            @foreach(['all' => 'Toutes', 'active' => 'Actives', 'inactive' => 'Inactives'] as $val => $label)
                <button
                    wire:click="$set('filterActive', '{{ $val }}')"
                    class="rounded-full px-3 py-1.5 text-xs font-semibold transition {{ $filterActive === $val ? 'bg-emerald-700 text-white shadow' : 'bg-white border border-slate-300 text-slate-700 hover:bg-slate-50' }}"
                >
                    {{ $label }}
                </button>
            @endforeach
        </div>
        <button
            wire:click="openCreate"
            class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-700 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-600"
        >
            + Nouvelle prestation
        </button>
    </div>

    {{-- ── FORMULAIRE CRÉATION / ÉDITION ───────────────────────── --}}
    @if($showForm)
        <section class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-emerald-300">
            <div class="border-b border-emerald-200 bg-emerald-50 px-5 py-4 sm:px-6">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">{{ $editingId ? 'Modification' : 'Création' }}</p>
                        <h2 class="text-lg font-black text-slate-900">{{ $editingId ? 'Modifier la prestation' : 'Nouvelle prestation' }}</h2>
                    </div>
                    <button wire:click="closeForm" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-100">
                        Annuler
                    </button>
                </div>
            </div>

            <form wire:submit="save" class="space-y-5 p-5 sm:p-6">
                {{-- Identité --}}
                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Nom <span class="text-rose-500">*</span></label>
                        <input type="text" wire:model.live="form_name" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="Ex: Petit-déjeuner" />
                        @error('form_name') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">
                            Code technique <span class="text-rose-500">*</span>
                            <span class="ml-1 font-normal normal-case text-slate-400">(lettres, chiffres, _)</span>
                        </label>
                        <input type="text" wire:model="form_code" class="w-full rounded-xl border-slate-300 font-mono text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="Ex: breakfast" />
                        @error('form_code') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">
                            Icône / Emoji
                            <span class="ml-1 font-normal normal-case text-slate-400">(optionnel)</span>
                        </label>
                        <div class="flex items-center gap-2">
                            <input type="text" wire:model.live="form_icon" maxlength="4" class="w-20 rounded-xl border-slate-300 text-center text-xl shadow-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="🍳" />
                            @if($form_icon)
                                <span class="text-2xl">{{ $form_icon }}</span>
                            @endif
                        </div>
                        @error('form_icon') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Description <span class="ml-1 font-normal normal-case text-slate-400">(optionnel)</span></label>
                    <textarea wire:model="form_description" rows="2" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="Ex: Buffet complet servi de 7h à 10h…"></textarea>
                    @error('form_description') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                </div>

                <label class="flex cursor-pointer items-center gap-2">
                    <input type="checkbox" wire:model="form_is_active" class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" />
                    <span class="text-sm font-semibold text-slate-700">Prestation active (visible lors de la configuration des chambres)</span>
                </label>

                {{-- Plats associés --}}
                @if($allMenus->isNotEmpty())
                    <div class="border-t border-slate-100 pt-4">
                        <p class="mb-2 text-xs font-bold uppercase tracking-widest text-slate-500">Plats du menu associés à cette prestation</p>
                        <p class="mb-3 text-xs text-slate-500">Cochez les plats qui sont le support de livraison de cette prestation (ex: pour "Petit-déjeuner" → cocher "Buffet du matin", "Jus de fruits", etc.).</p>
                        <div class="grid gap-2 sm:grid-cols-2 xl:grid-cols-3">
                            @foreach($allMenus as $menu)
                                <label class="flex cursor-pointer items-center gap-2 rounded-xl border px-3 py-2 transition
                                    {{ in_array($menu->id, $form_menu_ids) ? 'border-emerald-300 bg-emerald-50' : 'border-slate-200 bg-slate-50 hover:bg-slate-100' }}">
                                    <input
                                        type="checkbox"
                                        wire:model="form_menu_ids"
                                        value="{{ $menu->id }}"
                                        class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                                    />
                                    <span class="text-xs font-semibold text-slate-800">{{ $menu->name }}</span>
                                    <span class="ml-auto text-xs text-slate-400">{{ number_format((float) $menu->price, 0, '.', ' ') }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('form_menu_ids') <p class="mt-2 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                    </div>
                @else
                    <p class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs text-amber-700">
                        Aucun plat actif dans le menu. Créez des plats d'abord pour les associer à cette prestation.
                    </p>
                @endif

                <div class="border-t border-slate-100 pt-4">
                    <button type="submit" class="inline-flex items-center rounded-xl bg-emerald-700 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-600">
                        {{ $editingId ? 'Enregistrer les modifications' : 'Créer la prestation' }}
                    </button>
                </div>
            </form>
        </section>
    @endif

    {{-- ── CONFIRMATION SUPPRESSION ─────────────────────────────── --}}
    @if($deletingId)
        @php $toDelete = $benefits->firstWhere('id', $deletingId); @endphp
        <div class="rounded-2xl border border-rose-300 bg-rose-50 p-5">
            <p class="text-sm font-bold text-rose-900">Supprimer la prestation « {{ $toDelete?->name }} » ?</p>
            <p class="mt-1 text-xs text-rose-700">Elle sera retirée de toutes les chambres et plats auxquels elle est associée. Cette action est irréversible.</p>
            <div class="mt-3 flex gap-2">
                <button wire:click="delete" class="rounded-xl bg-rose-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-rose-700">Supprimer définitivement</button>
                <button wire:click="cancelDelete" class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-100">Annuler</button>
            </div>
        </div>
    @endif

    {{-- ── LISTE DES PRESTATIONS ────────────────────────────────── --}}
    @if($benefits->isEmpty())
        <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-12 text-center">
            <p class="text-3xl">🎁</p>
            <p class="mt-3 text-sm font-semibold text-slate-700">Aucune prestation configurée</p>
            <p class="mt-1 text-xs text-slate-500">Créez votre première prestation pour l'associer aux chambres de votre hôtel.</p>
            <button wire:click="openCreate" class="mt-4 rounded-xl bg-emerald-700 px-5 py-2 text-sm font-semibold text-white transition hover:bg-emerald-600">
                + Créer une prestation
            </button>
        </div>
    @else
        <div class="space-y-2">
            @foreach($benefits as $benefit)
                <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 transition hover:ring-emerald-200">
                    <div class="flex flex-wrap items-center gap-3 px-4 py-3 sm:px-5">
                        {{-- Icône --}}
                        <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl {{ $benefit->is_active ? 'bg-emerald-100' : 'bg-slate-100' }} text-xl">
                            {{ $benefit->icon ?: '🎁' }}
                        </div>

                        {{-- Infos --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="font-bold text-slate-900">{{ $benefit->name }}</p>
                                <span class="rounded-full bg-slate-100 px-2 py-0.5 font-mono text-[10px] text-slate-500">{{ $benefit->code }}</span>
                                @if(! $benefit->is_active)
                                    <span class="rounded-full bg-slate-200 px-2 py-0.5 text-[10px] font-semibold text-slate-500">Inactif</span>
                                @endif
                            </div>
                            @if($benefit->description)
                                <p class="mt-0.5 truncate text-xs text-slate-500">{{ $benefit->description }}</p>
                            @endif
                            <div class="mt-1 flex flex-wrap gap-3 text-xs text-slate-500">
                                <span>🏨 {{ $benefit->rooms_count }} chambre{{ $benefit->rooms_count !== 1 ? 's' : '' }}</span>
                                <span>🍽 {{ $benefit->menus_count }} plat{{ $benefit->menus_count !== 1 ? 's' : '' }} associé{{ $benefit->menus_count !== 1 ? 's' : '' }}</span>
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="flex flex-shrink-0 items-center gap-2">
                            <button
                                wire:click="toggleActive({{ $benefit->id }})"
                                title="{{ $benefit->is_active ? 'Désactiver' : 'Activer' }}"
                                class="rounded-lg border px-3 py-1.5 text-xs font-semibold transition
                                    {{ $benefit->is_active
                                        ? 'border-emerald-300 bg-emerald-50 text-emerald-700 hover:bg-emerald-100'
                                        : 'border-slate-300 bg-slate-50 text-slate-600 hover:bg-slate-100' }}"
                            >
                                {{ $benefit->is_active ? 'Actif' : 'Inactif' }}
                            </button>
                            <button
                                wire:click="openEdit({{ $benefit->id }})"
                                class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-100"
                            >
                                Modifier
                            </button>
                            <button
                                wire:click="confirmDelete({{ $benefit->id }})"
                                class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-600 transition hover:bg-rose-100"
                            >
                                Supprimer
                            </button>
                        </div>
                    </div>

                    {{-- Plats associés (résumé) --}}
                    @if($benefit->menus_count > 0)
                        <div class="border-t border-slate-100 bg-slate-50 px-4 py-2 sm:px-5">
                            <p class="text-xs text-slate-500">
                                <span class="font-semibold text-slate-700">Plats liés :</span>
                                {{ $benefit->menus->pluck('name')->join(', ') }}
                            </p>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

</div>
