<div class="space-y-6">
    <section class="rounded-3xl border border-slate-200 bg-[radial-gradient(circle_at_top_left,_rgba(16,185,129,0.12),_transparent_34%),linear-gradient(135deg,#ffffff_0%,#f8fafc_52%,#ecfeff_100%)] px-5 py-6 shadow-sm sm:px-6">
        <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
            <div class="max-w-3xl">
                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-emerald-600">Service Control</p>
                <h1 class="mt-1 text-2xl font-black text-slate-900">Gestion dynamique des services</h1>
                <p class="mt-2 text-sm text-slate-600">
                    Configurez chaque service comme une unite operationnelle: domaine, priorite, activite et capacites metier.
                    Les ecrans restaurant et hotel reutilisent ensuite cette configuration automatiquement.
                </p>
            </div>

            <div class="grid gap-3 sm:grid-cols-6">
                <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Services</p>
                    <p class="mt-1 text-2xl font-black text-slate-900">{{ number_format($stats['total']) }}</p>
                </div>
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 shadow-sm">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-emerald-700">Actifs</p>
                    <p class="mt-1 text-2xl font-black text-emerald-900">{{ number_format($stats['active']) }}</p>
                </div>
                <div class="rounded-2xl border border-cyan-200 bg-cyan-50 px-4 py-3 shadow-sm">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-cyan-700">Hotel</p>
                    <p class="mt-1 text-2xl font-black text-cyan-900">{{ number_format($stats['hotel']) }}</p>
                </div>
                <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 shadow-sm">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-amber-700">Restaurant</p>
                    <p class="mt-1 text-2xl font-black text-amber-900">{{ number_format($stats['restaurant']) }}</p>
                </div>
                <div class="rounded-2xl border border-violet-200 bg-violet-50 px-4 py-3 shadow-sm">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-violet-700">Configures</p>
                    <p class="mt-1 text-2xl font-black text-violet-900">{{ number_format($stats['dynamic']) }}</p>
                </div>
                <div class="rounded-2xl border border-indigo-200 bg-indigo-50 px-4 py-3 shadow-sm">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-indigo-700">Ouverts</p>
                    <p class="mt-1 text-2xl font-black text-indigo-900">{{ number_format($stats['open_now']) }}</p>
                    <p class="mt-1 text-[11px] text-indigo-700">Budget cumule {{ number_format($stats['monthly_budget'], 2, '.', ' ') }}</p>
                </div>
            </div>
        </div>
    </section>

    @if($feedbackMessage)
        <div class="rounded-2xl border px-4 py-3 text-sm font-medium {{ $feedbackTone === 'warning' ? 'border-amber-200 bg-amber-50 text-amber-900' : 'border-emerald-200 bg-emerald-50 text-emerald-900' }}">
            {{ $feedbackMessage }}
        </div>
    @endif

    <div class="grid gap-5 xl:grid-cols-[0.95fr_1.45fr]">
        <section class="rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-5 py-4">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Configuration</p>
                <h2 class="mt-1 text-lg font-black text-slate-900">{{ $editing_service_id ? 'Modifier un service' : 'Nouveau service' }}</h2>
            </div>

            <form wire:submit="save" class="space-y-4 px-5 py-5">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Nom service</label>
                        <input type="text" wire:model="name" class="prostay-input" placeholder="Ex: Room Service" />
                        @error('name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Code</label>
                        <input type="text" wire:model="code" class="prostay-input" placeholder="room-service" />
                        @error('code') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Domaine</label>
                        <select wire:model.live="domain" class="prostay-input">
                            <option value="shared">Transversal</option>
                            <option value="hotel">Hotel</option>
                            <option value="restaurant">Restaurant</option>
                        </select>
                        @error('domain') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Ordre d affichage</label>
                        <input type="number" min="0" wire:model="sort_order" class="prostay-input" />
                        @error('sort_order') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Description</label>
                    <textarea wire:model="description" rows="3" class="prostay-input" placeholder="Mission du service, type d operations, usage attendu..."></textarea>
                    @error('description') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Responsable</label>
                        <input type="text" wire:model="manager_name" class="prostay-input" placeholder="Ex: Chef de salle" />
                        @error('manager_name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Telephone responsable</label>
                        <input type="text" wire:model="manager_phone" class="prostay-input" placeholder="Ex: +243 ..." />
                        @error('manager_phone') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Ouverture</label>
                        <input type="time" wire:model="opens_at" class="prostay-input" />
                        @error('opens_at') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Fermeture</label>
                        <input type="time" wire:model="closes_at" class="prostay-input" />
                        @error('closes_at') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Objectif journalier</label>
                        <input type="number" min="0" step="0.01" wire:model="daily_target_amount" class="prostay-input" />
                        @error('daily_target_amount') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Budget mensuel</label>
                        <input type="number" min="0" step="0.01" wire:model="monthly_budget" class="prostay-input" />
                        @error('monthly_budget') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Capacites actives</p>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <label class="flex items-center justify-between rounded-2xl border border-slate-200 bg-white px-3 py-2.5 text-sm font-medium text-slate-700">
                            <span>Commandes</span>
                            <input type="checkbox" wire:model="supports_orders" class="rounded border-slate-300 text-emerald-600" />
                        </label>
                        <label class="flex items-center justify-between rounded-2xl border border-slate-200 bg-white px-3 py-2.5 text-sm font-medium text-slate-700">
                            <span>Carte et plats</span>
                            <input type="checkbox" wire:model="supports_menu" class="rounded border-slate-300 text-emerald-600" />
                        </label>
                        <label class="flex items-center justify-between rounded-2xl border border-slate-200 bg-white px-3 py-2.5 text-sm font-medium text-slate-700">
                            <span>POS / caisse</span>
                            <input type="checkbox" wire:model="supports_pos" class="rounded border-slate-300 text-emerald-600" />
                        </label>
                        <label class="flex items-center justify-between rounded-2xl border border-slate-200 bg-white px-3 py-2.5 text-sm font-medium text-slate-700">
                            <span>Stock</span>
                            <input type="checkbox" wire:model="supports_stock" class="rounded border-slate-300 text-emerald-600" />
                        </label>
                        <label class="flex items-center justify-between rounded-2xl border border-slate-200 bg-white px-3 py-2.5 text-sm font-medium text-slate-700 sm:col-span-2">
                            <span>Tables / points de service</span>
                            <input type="checkbox" wire:model="supports_tables" class="rounded border-slate-300 text-emerald-600" />
                        </label>
                    </div>
                </div>

                <label class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm font-medium text-slate-700">
                    <input type="checkbox" wire:model="is_active" class="rounded border-slate-300 text-emerald-600" />
                    <span>Service actif</span>
                </label>

                <div class="flex flex-wrap gap-3">
                    <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800">
                        {{ $editing_service_id ? 'Mettre a jour le service' : 'Creer le service' }}
                    </button>
                    <button type="button" wire:click="resetForm" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                        Reinitialiser
                    </button>
                </div>
            </form>
        </section>

        <section class="rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-5 py-4">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Registre des services</p>
                        <h2 class="mt-1 text-lg font-black text-slate-900">Pilotage des services</h2>
                    </div>

                    <div class="grid gap-2 sm:grid-cols-3">
                        <input type="text" wire:model.live.debounce.300ms="search" class="prostay-input" placeholder="Nom, code, description..." />
                        <select wire:model.live="domainFilter" class="prostay-input">
                            <option value="all">Tous les domaines</option>
                            <option value="shared">Transversal</option>
                            <option value="hotel">Hotel</option>
                            <option value="restaurant">Restaurant</option>
                        </select>
                        <select wire:model.live="statusFilter" class="prostay-input">
                            <option value="all">Tous les statuts</option>
                            <option value="active">Actifs</option>
                            <option value="inactive">Inactifs</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-4 py-3">Service</th>
                            <th class="px-4 py-3">Domaine</th>
                            <th class="px-4 py-3">Capacites</th>
                            <th class="px-4 py-3">Pilotage</th>
                            <th class="px-4 py-3">Usage</th>
                            <th class="px-4 py-3">Statut</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($services as $service)
                            <tr>
                                <td class="px-4 py-3 align-top">
                                    <p class="font-semibold text-slate-900">{{ $service->name }}</p>
                                    <p class="text-xs uppercase tracking-wide text-slate-500">{{ $service->code }}</p>
                                    @if($service->description)
                                        <p class="mt-1 max-w-md text-xs text-slate-500">{{ $service->description }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3 align-top">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $service->domain === 'hotel' ? 'bg-cyan-100 text-cyan-700' : ($service->domain === 'restaurant' ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-700') }}">
                                        {{ $domainLabels[$service->domain] ?? ucfirst($service->domain) }}
                                    </span>
                                    <p class="mt-2 text-xs text-slate-500">Priorite {{ $service->sort_order }}</p>
                                </td>
                                <td class="px-4 py-3 align-top">
                                    <div class="flex flex-wrap gap-1.5">
                                        @if($service->supports_orders)
                                            <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">Commandes</span>
                                        @endif
                                        @if($service->supports_menu)
                                            <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">Plats</span>
                                        @endif
                                        @if($service->supports_pos)
                                            <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">POS</span>
                                        @endif
                                        @if($service->supports_stock)
                                            <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">Stock</span>
                                        @endif
                                        @if($service->supports_tables)
                                            <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">Tables</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3 align-top text-xs text-slate-600">
                                    <p>{{ $service->manager_name ?: 'Aucun responsable' }}</p>
                                    <p>{{ $service->opens_at ? substr($service->opens_at, 0, 5) : '--:--' }} - {{ $service->closes_at ? substr($service->closes_at, 0, 5) : '--:--' }}</p>
                                    <p>Objectif {{ number_format((float) $service->daily_target_amount, 2, '.', ' ') }}</p>
                                    <p>Budget {{ number_format((float) $service->monthly_budget, 2, '.', ' ') }}</p>
                                </td>
                                <td class="px-4 py-3 align-top text-xs text-slate-600">
                                    <p>{{ $service->orders_count }} commande(s)</p>
                                    <p>{{ $service->menus_count }} plat(s)</p>
                                    <p>{{ $service->products_count }} article(s)</p>
                                    <p>{{ $service->stock_movements_count }} mouvement(s)</p>
                                    <p>{{ $service->dining_tables_count }} table(s)</p>
                                </td>
                                <td class="px-4 py-3 align-top">
                                    <div class="flex flex-col gap-2">
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $service->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                            {{ $service->is_active ? 'Actif' : 'Inactif' }}
                                        </span>
                                        @php($openState = $service->isOpenNow())
                                        @if($openState === true)
                                            <span class="inline-flex rounded-full bg-indigo-100 px-2.5 py-1 text-xs font-semibold text-indigo-700">Ouvert maintenant</span>
                                        @elseif($openState === false)
                                            <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">Ferme maintenant</span>
                                        @else
                                            <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-500">Horaire non defini</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3 align-top">
                                    <div class="flex justify-end gap-2">
                                        <button type="button" wire:click="edit({{ $service->id }})" class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">
                                            Modifier
                                        </button>
                                        <button type="button" wire:click="toggleStatus({{ $service->id }})" class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">
                                            {{ $service->is_active ? 'Desactiver' : 'Activer' }}
                                        </button>
                                        <button type="button" wire:click="delete({{ $service->id }})" class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700 transition hover:bg-rose-100">
                                            Supprimer
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-10 text-center text-sm text-slate-500">Aucun service ne correspond aux filtres en cours.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>