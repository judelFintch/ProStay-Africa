<div class="mx-auto max-w-7xl space-y-4 px-4 py-4 sm:px-6 lg:px-8">
    <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 sm:p-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-600">Restaurant Operations</p>
                <h1 class="mt-1 text-2xl font-black text-slate-900">Gestion des serveurs</h1>
                <p class="mt-1 text-sm text-slate-500">Activez les profils serveurs et affectez ensuite chaque commande a la personne qui sert.</p>
            </div>

            <div class="grid gap-2 sm:grid-cols-3">
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400">Total</p>
                    <p class="mt-1 text-xl font-black text-slate-900">{{ $stats['total'] }}</p>
                </div>
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2.5">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-emerald-700">Actifs</p>
                    <p class="mt-1 text-xl font-black text-emerald-700">{{ $stats['active'] }}</p>
                </div>
                <div class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-2.5">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-amber-700">Inactifs</p>
                    <p class="mt-1 text-xl font-black text-amber-700">{{ $stats['inactive'] }}</p>
                </div>
            </div>
        </div>
    </section>

    <div class="grid gap-4 xl:grid-cols-[1.2fr_1fr]">
        <section class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
            <div class="border-b border-slate-200 px-4 py-3 sm:px-5">
                <h2 class="text-base font-black text-slate-900">Nouveau serveur</h2>
            </div>

            <form wire:submit="saveServer" class="grid gap-3 p-4 sm:grid-cols-2 sm:p-5">
                <div>
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Nom complet</label>
                    <input type="text" wire:model="name" class="prostay-input" placeholder="Ex: Jean Konan" />
                    @error('name') <p class="mt-1 text-xs font-semibold text-rose-700">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Alias serveur</label>
                    <input type="text" wire:model="server_alias" class="prostay-input" placeholder="Ex: Service Salle A" />
                    @error('server_alias') <p class="mt-1 text-xs font-semibold text-rose-700">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Email</label>
                    <input type="email" wire:model="email" class="prostay-input" placeholder="serveur@hotel.com" />
                    @error('email') <p class="mt-1 text-xs font-semibold text-rose-700">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Mot de passe</label>
                    <input type="password" wire:model="password" class="prostay-input" placeholder="Minimum 8 caracteres" />
                    @error('password') <p class="mt-1 text-xs font-semibold text-rose-700">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-medium text-slate-700">
                        <input type="checkbox" wire:model="server_active" class="rounded border-slate-300 text-emerald-600 shadow-sm focus:ring-emerald-500">
                        Serveur actif des la creation
                    </label>
                </div>

                <div class="sm:col-span-2">
                    <button class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800">
                        Enregistrer le serveur
                    </button>
                </div>
            </form>

            <div class="border-t border-slate-200 p-4 sm:p-5">
                <h3 class="text-sm font-black text-slate-900">Promouvoir un utilisateur existant</h3>
                <div class="mt-3 flex flex-col gap-2 sm:flex-row">
                    <select wire:model="promote_user_id" class="prostay-input sm:flex-1">
                        <option value="">Selectionner un utilisateur</option>
                        @foreach($promotableUsers as $user)
                            <option value="{{ $user->id }}">{{ $user->name }} - {{ $user->email }}</option>
                        @endforeach
                    </select>
                    <button type="button" wire:click="promoteUser" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                        Promouvoir serveur
                    </button>
                </div>
                @error('promote_user_id') <p class="mt-1 text-xs font-semibold text-rose-700">{{ $message }}</p> @enderror
            </div>
        </section>

        <section class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
            <div class="border-b border-slate-200 px-4 py-3 sm:px-5">
                <div class="flex items-center justify-between gap-2">
                    <h2 class="text-base font-black text-slate-900">Equipe serveurs</h2>
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        class="prostay-input max-w-[180px]"
                        placeholder="Rechercher"
                    />
                </div>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse($servers as $server)
                    <div class="flex items-start justify-between gap-3 px-4 py-3 sm:px-5">
                        <div>
                            <p class="text-sm font-semibold text-slate-900">{{ $server->name }}</p>
                            <p class="text-xs text-slate-500">{{ $server->email }}</p>
                            @if($server->server_alias)
                                <p class="mt-1 text-xs font-semibold text-slate-600">Alias: {{ $server->server_alias }}</p>
                            @endif
                            <span class="mt-2 inline-flex rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $server->server_active ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                {{ $server->server_active ? 'Actif' : 'Inactif' }}
                            </span>
                        </div>

                        <div class="flex flex-col gap-2 sm:flex-row">
                            <button
                                type="button"
                                wire:click="toggleServerStatus({{ $server->id }})"
                                class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50"
                            >
                                {{ $server->server_active ? 'Desactiver' : 'Activer' }}
                            </button>
                            <button
                                type="button"
                                wire:click="removeServerRole({{ $server->id }})"
                                class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-700 transition hover:bg-rose-100"
                            >
                                Retirer role
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="px-4 py-8 text-center text-sm text-slate-500 sm:px-5">
                        Aucun serveur enregistre pour le moment.
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</div>
