<div class="space-y-6">
    <section class="rounded-3xl border border-slate-200 bg-[radial-gradient(circle_at_top_left,_rgba(14,165,233,0.14),_transparent_35%),linear-gradient(135deg,#ffffff_0%,#f8fafc_52%,#ecfeff_100%)] px-5 py-6 shadow-sm sm:px-6">
        <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
            <div class="max-w-3xl">
                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-sky-700">Access Control</p>
                <h1 class="mt-1 text-2xl font-black text-slate-900">Gestion des utilisateurs</h1>
                <p class="mt-2 text-sm text-slate-600">
                    Creez les comptes, affectez les roles, visualisez les permissions heritees et gerez aussi les profils serveurs depuis une seule interface.
                </p>
            </div>

            <div class="grid gap-3 sm:grid-cols-4">
                <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Comptes</p>
                    <p class="mt-1 text-2xl font-black text-slate-900">{{ number_format($stats['total']) }}</p>
                </div>
                <div class="rounded-2xl border border-indigo-200 bg-indigo-50 px-4 py-3 shadow-sm">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-indigo-700">Admins</p>
                    <p class="mt-1 text-2xl font-black text-indigo-900">{{ number_format($stats['admins']) }}</p>
                </div>
                <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 shadow-sm">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-amber-700">Serveurs</p>
                    <p class="mt-1 text-2xl font-black text-amber-900">{{ number_format($stats['servers']) }}</p>
                </div>
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 shadow-sm">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-emerald-700">Serveurs actifs</p>
                    <p class="mt-1 text-2xl font-black text-emerald-900">{{ number_format($stats['active_servers']) }}</p>
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
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Compte</p>
                <h2 class="mt-1 text-lg font-black text-slate-900">{{ $editing_user_id ? 'Modifier un utilisateur' : 'Nouvel utilisateur' }}</h2>
            </div>

            <form wire:submit="save" class="space-y-4 px-5 py-5">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Nom complet</label>
                        <input type="text" wire:model="name" class="prostay-input" placeholder="Ex: Marie Ilunga" />
                        @error('name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Email</label>
                        <input type="email" wire:model="email" class="prostay-input" placeholder="utilisateur@prostay.africa" />
                        @error('email') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Mot de passe</label>
                        <input type="password" wire:model="password" class="prostay-input" placeholder="{{ $editing_user_id ? 'Laisser vide pour conserver' : 'Minimum 8 caracteres' }}" />
                        @error('password') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Confirmation</label>
                        <input type="password" wire:model="password_confirmation" class="prostay-input" placeholder="Confirmer le mot de passe" />
                        @error('password_confirmation') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Roles affectes</p>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        @foreach($roles as $role)
                            <label class="rounded-2xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700">
                                <span class="flex items-start justify-between gap-3">
                                    <span>
                                        <span class="block font-semibold text-slate-900">{{ $role->label ?: $role->name }}</span>
                                        <span class="mt-1 block text-xs text-slate-500">{{ $role->permissions->pluck('label')->filter()->join(', ') ?: 'Aucune permission liee' }}</span>
                                    </span>
                                    <input type="checkbox" value="{{ $role->id }}" wire:model="role_ids" class="mt-0.5 rounded border-slate-300 text-sky-600" />
                                </span>
                            </label>
                        @endforeach
                    </div>
                    @error('role_ids') <p class="mt-2 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Profil serveur</p>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <label class="flex items-center justify-between rounded-2xl border border-slate-200 bg-white px-3 py-2.5 text-sm font-medium text-slate-700">
                            <span>Activer comme serveur</span>
                            <input type="checkbox" wire:model.live="is_server" class="rounded border-slate-300 text-sky-600" />
                        </label>
                        <label class="flex items-center justify-between rounded-2xl border border-slate-200 bg-white px-3 py-2.5 text-sm font-medium text-slate-700 {{ $is_server ? '' : 'opacity-60' }}">
                            <span>Serveur actif</span>
                            <input type="checkbox" wire:model="server_active" @disabled(! $is_server) class="rounded border-slate-300 text-sky-600" />
                        </label>
                    </div>
                    <div class="mt-3">
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Alias serveur</label>
                        <input type="text" wire:model="server_alias" @disabled(! $is_server) class="prostay-input" placeholder="Ex: Service Salle A" />
                        @error('server_alias') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="flex flex-wrap gap-3">
                    <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800">
                        {{ $editing_user_id ? 'Mettre a jour le compte' : 'Creer le compte' }}
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
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Registre</p>
                        <h2 class="mt-1 text-lg font-black text-slate-900">Comptes utilisateurs</h2>
                    </div>

                    <div class="grid gap-2 sm:grid-cols-3">
                        <input type="text" wire:model.live.debounce.300ms="search" class="prostay-input" placeholder="Nom, email, alias..." />
                        <select wire:model.live="roleFilter" class="prostay-input">
                            <option value="all">Tous les roles</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->label ?: $role->name }}</option>
                            @endforeach
                        </select>
                        <select wire:model.live="serverFilter" class="prostay-input">
                            <option value="all">Tous les profils</option>
                            <option value="server">Serveurs</option>
                            <option value="active_server">Serveurs actifs</option>
                            <option value="inactive_server">Serveurs inactifs</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-4 py-3">Utilisateur</th>
                            <th class="px-4 py-3">Roles</th>
                            <th class="px-4 py-3">Permissions effectives</th>
                            <th class="px-4 py-3">Profil serveur</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($users as $user)
                            <tr>
                                <td class="px-4 py-3 align-top">
                                    <p class="font-semibold text-slate-900">{{ $user->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $user->email }}</p>
                                    <p class="mt-1 text-xs text-slate-500">Compte #{{ $user->id }}</p>
                                </td>
                                <td class="px-4 py-3 align-top">
                                    <div class="flex flex-wrap gap-1.5">
                                        @forelse($user->roles as $role)
                                            <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">{{ $role->label ?: $role->name }}</span>
                                        @empty
                                            <span class="text-xs text-slate-400">Aucun role</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="px-4 py-3 align-top">
                                    <div class="flex flex-wrap gap-1.5">
                                        @forelse($user->effective_permissions as $permission)
                                            <span class="inline-flex rounded-full bg-sky-50 px-2.5 py-1 text-xs font-semibold text-sky-700">{{ $permission->label ?: $permission->name }}</span>
                                        @empty
                                            <span class="text-xs text-slate-400">Aucune permission</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="px-4 py-3 align-top text-xs text-slate-600">
                                    @if($user->is_server)
                                        <p class="font-semibold text-slate-800">Serveur</p>
                                        <p>{{ $user->server_alias ?: 'Sans alias' }}</p>
                                        <span class="mt-2 inline-flex rounded-full px-2.5 py-1 font-semibold {{ $user->server_active ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                            {{ $user->server_active ? 'Actif' : 'Inactif' }}
                                        </span>
                                    @else
                                        <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 font-semibold text-slate-600">Standard</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 align-top">
                                    <div class="flex justify-end gap-2">
                                        <button type="button" wire:click="edit({{ $user->id }})" class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">
                                            Modifier
                                        </button>
                                        <button type="button" wire:click="toggleServerStatus({{ $user->id }})" class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">
                                            {{ $user->is_server ? ($user->server_active ? 'Desactiver serveur' : 'Activer serveur') : 'Promouvoir serveur' }}
                                        </button>
                                        <button type="button" wire:click="delete({{ $user->id }})" class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700 transition hover:bg-rose-100">
                                            Supprimer
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-10 text-center text-sm text-slate-500">Aucun utilisateur ne correspond aux filtres en cours.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>