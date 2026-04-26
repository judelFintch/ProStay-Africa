<div class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
    <section class="rounded-[2rem] bg-white p-6 shadow-sm ring-1 ring-slate-200 sm:p-8">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.25em] text-emerald-600">Guest Registry</p>
                <h1 class="mt-2 text-3xl font-black text-slate-900 sm:text-4xl">Clients enregistres</h1>
                <p class="mt-2 max-w-2xl text-sm text-slate-500">
                    Registre central des clients de l'hotel avec identification, contact, preferences et historique d'exploitation.
                </p>
            </div>

            <a
                href="{{ route('dashboard') }}"
                wire:navigate
                class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800"
            >
                <i class="fa-solid fa-grid-2"></i>
                Dashboard des modules
            </a>
        </div>

        <div class="mt-6 flex flex-wrap gap-3">
            <a
                href="{{ route('customers.index') }}"
                wire:navigate
                class="inline-flex items-center gap-2 rounded-2xl px-4 py-2.5 text-sm font-semibold transition {{ $mode === 'form' ? 'bg-slate-900 text-white' : 'border border-slate-200 bg-white text-slate-700 hover:bg-slate-50' }}"
            >
                <i class="fa-solid fa-user-plus"></i>
                Nouveau client
            </a>
            <a
                href="{{ route('customers.registry') }}"
                wire:navigate
                class="inline-flex items-center gap-2 rounded-2xl px-4 py-2.5 text-sm font-semibold transition {{ $mode === 'registry' ? 'bg-slate-900 text-white' : 'border border-slate-200 bg-white text-slate-700 hover:bg-slate-50' }}"
            >
                <i class="fa-solid fa-address-book"></i>
                Liste des clients
            </a>
        </div>

        <div class="mt-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl bg-slate-50 p-4 ring-1 ring-slate-200">
                <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Clients</p>
                <p class="mt-2 text-3xl font-black text-slate-900">{{ number_format($stats['total']) }}</p>
                <p class="mt-1 text-xs text-slate-500">Profils enregistres</p>
            </div>
            <div class="rounded-2xl bg-slate-50 p-4 ring-1 ring-slate-200">
                <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Identifies</p>
                <p class="mt-2 text-3xl font-black text-slate-900">{{ number_format($stats['identified']) }}</p>
                <p class="mt-1 text-xs text-slate-500">Profils verifies</p>
            </div>
            <div class="rounded-2xl bg-slate-50 p-4 ring-1 ring-slate-200">
                <p class="text-xs uppercase tracking-[0.2em] text-slate-400">VIP</p>
                <p class="mt-2 text-3xl font-black text-slate-900">{{ number_format($stats['vip']) }}</p>
                <p class="mt-1 text-xs text-slate-500">Clients a attention particuliere</p>
            </div>
            <div class="rounded-2xl bg-slate-50 p-4 ring-1 ring-slate-200">
                <p class="text-xs uppercase tracking-[0.2em] text-slate-400">En sejour</p>
                <p class="mt-2 text-3xl font-black text-slate-900">{{ number_format($stats['inHouse']) }}</p>
                <p class="mt-1 text-xs text-slate-500">Clients actuellement heberges</p>
            </div>
        </div>
    </section>

    @if($mode === 'form')
    <div class="grid gap-6 xl:grid-cols-[1.45fr_0.95fr]">
        <section class="prostay-surface overflow-hidden">
            <div class="border-b border-slate-200 bg-slate-50/80 px-5 py-4 sm:px-6">
                @if($step === 'recap')
                <div class="flex items-center gap-3">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-amber-100">
                        <i class="fa-solid fa-clipboard-check text-sm text-amber-600"></i>
                    </span>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-amber-600">Verification avant enregistrement</p>
                        <h2 class="mt-0.5 text-xl font-black text-slate-900">Recapitulatif de la fiche</h2>
                    </div>
                </div>
                @elseif($step === 'ticket')
                <div class="flex items-center gap-3">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-emerald-100">
                        <i class="fa-solid fa-circle-check text-sm text-emerald-600"></i>
                    </span>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-600">Client enregistre avec succes</p>
                        <h2 class="mt-0.5 text-xl font-black text-slate-900">Ticket d'accueil</h2>
                    </div>
                </div>
                @else
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-600">Front Office</p>
                <h2 class="mt-1 text-xl font-black text-slate-900">Nouvelle fiche client</h2>
                <p class="mt-1 text-sm text-slate-500">Formulaire complet pour reception, hebergement, identification et relation client.</p>
                @endif
            </div>

            @if($step === 'form')
            <form wire:submit="goToRecap" class="divide-y divide-slate-100">

                {{-- ───── ESSENTIELS (toujours visibles) ───── --}}
                <div class="p-5 sm:p-6">
                    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                        <div class="xl:col-span-2">
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Nom complet <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" wire:model="full_name" placeholder="Nom officiel du client" class="prostay-input" />
                            @error('full_name') <p class="mt-1 text-xs font-semibold text-rose-700">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Telephone</label>
                            <input type="text" wire:model="phone" class="prostay-input" />
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Email</label>
                            <input type="email" wire:model="email" class="prostay-input" />
                        </div>
                        <div class="flex flex-wrap items-center gap-2 pt-1">
                            <label class="inline-flex cursor-pointer items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-800">
                                <input type="checkbox" wire:model="is_identified" class="rounded border-emerald-300 text-emerald-600 shadow-sm focus:ring-emerald-500">
                                Identifie
                            </label>
                            <label class="inline-flex cursor-pointer items-center gap-2 rounded-full border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-800">
                                <input type="checkbox" wire:model="vip_status" class="rounded border-amber-300 text-amber-600 shadow-sm focus:ring-amber-500">
                                VIP
                            </label>
                            <label class="inline-flex cursor-pointer items-center gap-2 rounded-full border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-800">
                                <input type="checkbox" wire:model="blacklisted" class="rounded border-rose-300 text-rose-600 shadow-sm focus:ring-rose-500">
                                Liste rouge
                            </label>
                        </div>
                    </div>
                </div>

                {{-- ───── TYPE DE VOYAGE ───── --}}
                <div class="px-5 pb-5 sm:px-6">
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <i class="fa-solid fa-users mr-1 text-slate-400"></i>
                        Le client voyage...
                    </label>
                    <div class="flex flex-wrap gap-2">
                        @foreach([
                            ['value' => 'solo',         'label' => 'Seul(e)',         'icon' => 'fa-solid fa-person',              'color' => 'slate'],
                            ['value' => 'accompanied',  'label' => 'Accompagne(e)',   'icon' => 'fa-solid fa-user-friends',        'color' => 'indigo'],
                            ['value' => 'couple',       'label' => 'En couple',       'icon' => 'fa-solid fa-heart',               'color' => 'rose'],
                            ['value' => 'family',       'label' => 'En famille',      'icon' => 'fa-solid fa-house-chimney-user',  'color' => 'emerald'],
                            ['value' => 'group',        'label' => 'En groupe',       'icon' => 'fa-solid fa-people-group',        'color' => 'violet'],
                        ] as $opt)
                        <label
                            wire:key="travel-{{ $opt['value'] }}"
                            class="inline-flex cursor-pointer items-center gap-2 rounded-2xl border px-3.5 py-2 text-xs font-semibold transition select-none
                                {{ $travel_type === $opt['value']
                                    ? match($opt['color']) {
                                        'slate'   => 'border-slate-900 bg-slate-900 text-white shadow-sm',
                                        'indigo'  => 'border-indigo-600 bg-indigo-600 text-white shadow-sm',
                                        'rose'    => 'border-rose-500 bg-rose-500 text-white shadow-sm',
                                        'emerald' => 'border-emerald-600 bg-emerald-600 text-white shadow-sm',
                                        'violet'  => 'border-violet-600 bg-violet-600 text-white shadow-sm',
                                    }
                                    : match($opt['color']) {
                                        'slate'   => 'border-slate-200 bg-white text-slate-600 hover:border-slate-400 hover:bg-slate-50',
                                        'indigo'  => 'border-indigo-100 bg-indigo-50 text-indigo-700 hover:border-indigo-300',
                                        'rose'    => 'border-rose-100 bg-rose-50 text-rose-700 hover:border-rose-300',
                                        'emerald' => 'border-emerald-100 bg-emerald-50 text-emerald-700 hover:border-emerald-300',
                                        'violet'  => 'border-violet-100 bg-violet-50 text-violet-700 hover:border-violet-300',
                                    }
                                }}">
                            <input type="radio" wire:model.live="travel_type" value="{{ $opt['value'] }}" class="sr-only">
                            <i class="{{ $opt['icon'] }}"></i>
                            {{ $opt['label'] }}
                        </label>
                        @endforeach
                        @if($travel_type)
                        <button type="button" wire:click="$set('travel_type', null)"
                            class="inline-flex items-center gap-1.5 rounded-2xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-400 transition hover:bg-slate-50 hover:text-slate-600">
                            <i class="fa-solid fa-xmark text-[10px]"></i>
                            Effacer
                        </button>
                        @endif
                    </div>
                </div>

                {{-- ───── PROFIL COMPLET (repliable) ───── --}}
                <div x-data="{ open: false }">
                    <button type="button" @click="open = !open"
                        class="flex w-full items-center justify-between px-5 py-4 text-left transition hover:bg-slate-50 sm:px-6">
                        <div class="flex items-center gap-3">
                            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-slate-100 text-slate-500">
                                <i class="fa-solid fa-id-card text-xs"></i>
                            </span>
                            <div>
                                <p class="text-sm font-bold text-slate-900">Profil détaillé</p>
                                <p class="text-xs text-slate-500">Civilite, nom prefere, genre, date de naissance, code client</p>
                            </div>
                        </div>
                        <i class="fa-solid fa-chevron-down text-xs text-slate-400 transition-transform duration-200" :class="open && 'rotate-180'"></i>
                    </button>
                    <div x-show="open" x-transition:enter="transition duration-150 ease-out"
                        x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                        class="border-t border-slate-100 bg-slate-50/50 px-5 pb-5 pt-4 sm:px-6">
                        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                            <div>
                                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Code client</label>
                                <input type="text" wire:model="guest_code" placeholder="Auto si vide" class="prostay-input" />
                                @error('guest_code') <p class="mt-1 text-xs font-semibold text-rose-700">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Civilite</label>
                                <select wire:model="title" class="prostay-input">
                                    <option value="">Selectionner</option>
                                    <option value="Mr">Mr</option>
                                    <option value="Mrs">Mrs</option>
                                    <option value="Ms">Ms</option>
                                    <option value="Dr">Dr</option>
                                </select>
                            </div>
                            <div class="md:col-span-2">
                                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Nom prefere</label>
                                <input type="text" wire:model="preferred_name" placeholder="Comment l'equipe s'adresse au client" class="prostay-input" />
                            </div>
                            <div>
                                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Genre</label>
                                <select wire:model="gender" class="prostay-input">
                                    <option value="">Selectionner</option>
                                    <option value="male">Homme</option>
                                    <option value="female">Femme</option>
                                    <option value="other">Autre</option>
                                    <option value="not_specified">Non specifie</option>
                                </select>
                            </div>
                            <div>
                                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Date de naissance</label>
                                <input type="date" wire:model="date_of_birth" class="prostay-input" />
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ───── PROVENANCE & ADRESSE (repliable) ───── --}}
                <div x-data="{ open: false }">
                    <button type="button" @click="open = !open"
                        class="flex w-full items-center justify-between px-5 py-4 text-left transition hover:bg-slate-50 sm:px-6">
                        <div class="flex items-center gap-3">
                            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-slate-100 text-slate-500">
                                <i class="fa-solid fa-earth-africa text-xs"></i>
                            </span>
                            <div>
                                <p class="text-sm font-bold text-slate-900">Provenance & Adresse</p>
                                <p class="text-xs text-slate-500">Nationalite, pays, ville, adresse et lieu de naissance</p>
                            </div>
                        </div>
                        <i class="fa-solid fa-chevron-down text-xs text-slate-400 transition-transform duration-200" :class="open && 'rotate-180'"></i>
                    </button>
                    <div x-show="open" x-transition:enter="transition duration-150 ease-out"
                        x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                        class="border-t border-slate-100 bg-slate-50/50 px-5 pb-5 pt-4 sm:px-6">
                        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                            <div>
                                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Lieu de naissance</label>
                                <input type="text" wire:model="place_of_birth" class="prostay-input" />
                            </div>
                            <div>
                                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Nationalite</label>
                                <input type="text" wire:model="nationality" class="prostay-input" />
                            </div>
                            <div>
                                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Pays de residence</label>
                                <input type="text" wire:model="country" class="prostay-input" />
                            </div>
                            <div>
                                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Ville</label>
                                <input type="text" wire:model="city" class="prostay-input" />
                            </div>
                            <div class="md:col-span-2 xl:col-span-4">
                                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Adresse complete</label>
                                <textarea wire:model="address" rows="2" class="prostay-input"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ───── COORDONNEES COMPLEMENTAIRES (repliable) ───── --}}
                <div x-data="{ open: false }">
                    <button type="button" @click="open = !open"
                        class="flex w-full items-center justify-between px-5 py-4 text-left transition hover:bg-slate-50 sm:px-6">
                        <div class="flex items-center gap-3">
                            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-slate-100 text-slate-500">
                                <i class="fa-solid fa-address-card text-xs"></i>
                            </span>
                            <div>
                                <p class="text-sm font-bold text-slate-900">Coordonnees complementaires</p>
                                <p class="text-xs text-slate-500">Tel secondaire, profession, societe, langue, source</p>
                            </div>
                        </div>
                        <i class="fa-solid fa-chevron-down text-xs text-slate-400 transition-transform duration-200" :class="open && 'rotate-180'"></i>
                    </button>
                    <div x-show="open" x-transition:enter="transition duration-150 ease-out"
                        x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                        class="border-t border-slate-100 bg-slate-50/50 px-5 pb-5 pt-4 sm:px-6">
                        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                            <div>
                                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Tel secondaire</label>
                                <input type="text" wire:model="secondary_phone" class="prostay-input" />
                            </div>
                            <div>
                                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Profession</label>
                                <input type="text" wire:model="profession" class="prostay-input" />
                            </div>
                            <div>
                                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Societe</label>
                                <input type="text" wire:model="company_name" class="prostay-input" />
                            </div>
                            <div>
                                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Langue preferee</label>
                                <select wire:model="preferred_language" class="prostay-input">
                                    <option value="fr">Francais</option>
                                    <option value="en">English</option>
                                    <option value="sw">Swahili</option>
                                </select>
                            </div>
                            <div class="md:col-span-2">
                                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Source acquisition</label>
                                <input type="text" wire:model="marketing_source" placeholder="Walk-in, agence, OTA..." class="prostay-input" />
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ───── PIECE D'IDENTITE (repliable) ───── --}}
                <div x-data="{ open: false }">
                    <button type="button" @click="open = !open"
                        class="flex w-full items-center justify-between px-5 py-4 text-left transition hover:bg-slate-50 sm:px-6">
                        <div class="flex items-center gap-3">
                            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-slate-100 text-slate-500">
                                <i class="fa-solid fa-passport text-xs"></i>
                            </span>
                            <div>
                                <p class="text-sm font-bold text-slate-900">Piece d'identite</p>
                                <p class="text-xs text-slate-500">Type, numero, lieu de delivrance, dates de validite</p>
                            </div>
                        </div>
                        <i class="fa-solid fa-chevron-down text-xs text-slate-400 transition-transform duration-200" :class="open && 'rotate-180'"></i>
                    </button>
                    <div x-show="open" x-transition:enter="transition duration-150 ease-out"
                        x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                        class="border-t border-slate-100 bg-slate-50/50 px-5 pb-5 pt-4 sm:px-6">
                        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                            <div>
                                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Type de piece</label>
                                <select wire:model="identity_document_type" class="prostay-input">
                                    <option value="">Selectionner</option>
                                    <option value="passport">Passeport</option>
                                    <option value="national_id">Carte nationale</option>
                                    <option value="driver_license">Permis</option>
                                    <option value="residence_permit">Titre de sejour</option>
                                </select>
                            </div>
                            <div>
                                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Numero</label>
                                <input type="text" wire:model="identity_document" class="prostay-input" />
                            </div>
                            <div>
                                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Lieu de delivrance</label>
                                <input type="text" wire:model="identity_document_issue_place" class="prostay-input" />
                            </div>
                            <div>
                                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Date emission</label>
                                <input type="date" wire:model="identity_document_issued_at" class="prostay-input" />
                            </div>
                            <div>
                                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Date expiration</label>
                                <input type="date" wire:model="identity_document_expires_at" class="prostay-input" />
                                @error('identity_document_expires_at') <p class="mt-1 text-xs font-semibold text-rose-700">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ───── URGENCE & PREFERENCES (repliable) ───── --}}
                <div x-data="{ open: false }">
                    <button type="button" @click="open = !open"
                        class="flex w-full items-center justify-between px-5 py-4 text-left transition hover:bg-slate-50 sm:px-6">
                        <div class="flex items-center gap-3">
                            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-slate-100 text-slate-500">
                                <i class="fa-solid fa-heart-pulse text-xs"></i>
                            </span>
                            <div>
                                <p class="text-sm font-bold text-slate-900">Urgence & Preferences</p>
                                <p class="text-xs text-slate-500">Contact d'urgence, preferences client et notes internes</p>
                            </div>
                        </div>
                        <i class="fa-solid fa-chevron-down text-xs text-slate-400 transition-transform duration-200" :class="open && 'rotate-180'"></i>
                    </button>
                    <div x-show="open" x-transition:enter="transition duration-150 ease-out"
                        x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                        class="border-t border-slate-100 bg-slate-50/50 px-5 pb-5 pt-4 sm:px-6">
                        <div class="grid gap-6 lg:grid-cols-2">
                            <div class="space-y-4">
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Contact d'urgence</p>
                                <div>
                                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Nom</label>
                                    <input type="text" wire:model="emergency_contact_name" class="prostay-input" />
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Telephone</label>
                                    <input type="text" wire:model="emergency_contact_phone" class="prostay-input" />
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Lien avec le client</label>
                                    <input type="text" wire:model="emergency_contact_relationship" placeholder="Conjoint, frere, collegue..." class="prostay-input" />
                                </div>
                            </div>
                            <div class="space-y-4">
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Preferences & Notes</p>
                                <div>
                                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Preferences client</label>
                                    <textarea wire:model="guest_preferences" rows="3" class="prostay-input" placeholder="Regime alimentaire, chambre, allergies..."></textarea>
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Notes internes</label>
                                    <textarea wire:model="internal_notes" rows="3" class="prostay-input" placeholder="Visible uniquement par l'equipe"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ───── SEJOUR IMMEDIAT (toggle + repliable) ───── --}}
                <div x-data="{ open: $wire.entangle('with_room_rental') }">
                    <button type="button" @click="open = !open; $wire.set('with_room_rental', open)"
                        class="flex w-full items-center justify-between px-5 py-4 text-left transition hover:bg-cyan-50 sm:px-6"
                        :class="open ? 'bg-cyan-50/70' : ''">
                        <div class="flex items-center gap-3">
                            <span class="flex h-7 w-7 items-center justify-center rounded-lg transition"
                                :class="open ? 'bg-cyan-600 text-white' : 'bg-slate-100 text-slate-500'">
                                <i class="fa-solid fa-bed text-xs"></i>
                            </span>
                            <div>
                                <p class="text-sm font-bold" :class="open ? 'text-cyan-900' : 'text-slate-900'">
                                    Loger le client maintenant
                                </p>
                                <p class="text-xs" :class="open ? 'text-cyan-700' : 'text-slate-500'">
                                    Creer la reservation + check-in en meme temps que la fiche
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-semibold" :class="open ? 'text-cyan-700' : 'text-slate-400'"
                                x-text="open ? 'Actif' : 'Inactif'"></span>
                            <div class="relative h-5 w-9 rounded-full transition-colors duration-200"
                                :class="open ? 'bg-cyan-600' : 'bg-slate-300'">
                                <span class="absolute top-0.5 h-4 w-4 rounded-full bg-white shadow transition-transform duration-200"
                                    :class="open ? 'translate-x-4' : 'translate-x-0.5'"></span>
                            </div>
                        </div>
                    </button>
                    <div x-show="open" x-transition:enter="transition duration-150 ease-out"
                        x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                        class="border-t border-cyan-200 bg-cyan-50/50 px-5 pb-5 pt-4 sm:px-6">
                        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                            <div class="md:col-span-2">
                                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-cyan-800">Chambre</label>
                                <select wire:model.live="room_id" class="prostay-input">
                                    <option value="">Selectionner une chambre disponible</option>
                                    @forelse($rooms as $room)
                                        <option value="{{ $room->id }}">
                                            Chambre {{ $room->number }} · cap {{ $room->capacity }} · {{ number_format($room->price, 2, '.', ' ') }} / nuit
                                        </option>
                                    @empty
                                        <option value="" disabled>Aucune chambre disponible actuellement</option>
                                    @endforelse
                                </select>
                                @if($rooms->isEmpty())
                                    <p class="mt-2 flex items-center gap-1.5 text-xs font-semibold text-amber-700">
                                        <i class="fa-solid fa-triangle-exclamation"></i>
                                        Toutes les chambres sont occupees ou en maintenance.
                                    </p>
                                @endif
                                @error('room_id') <p class="mt-1 text-xs font-semibold text-rose-700">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-cyan-800">Tarif / nuit</label>
                                <input type="number" step="0.01" min="0" wire:model.live="nightly_rate" placeholder="Laisser vide = tarif chambre" class="prostay-input" />
                            </div>
                            <div>
                                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-cyan-800">Adultes</label>
                                <input type="number" min="1" wire:model="adults" class="prostay-input" />
                            </div>
                            <div>
                                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-cyan-800">Check-in</label>
                                <input type="date" wire:model.live="check_in_date" class="prostay-input" />
                                @error('check_in_date') <p class="mt-1 text-xs font-semibold text-rose-700">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-cyan-800">Check-out</label>
                                <input type="date" wire:model.live="check_out_date" class="prostay-input" />
                                @error('check_out_date') <p class="mt-1 text-xs font-semibold text-rose-700">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-cyan-800">Enfants</label>
                                <input type="number" min="0" wire:model="children" class="prostay-input" />
                            </div>
                            <div class="md:col-span-2">
                                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-cyan-800">Notes de sejour</label>
                                <input type="text" wire:model="rental_notes" placeholder="Instructions, preferences de chambre..." class="prostay-input" />
                            </div>

                            {{-- ─── RECAPITULATIF CALCUL ─── --}}
                            @if($nights > 0 && $nightly_rate)
                            <div class="xl:col-span-4 md:col-span-2">
                                <div class="rounded-2xl border border-cyan-300 bg-cyan-600 px-5 py-4 text-white">
                                    <div class="flex flex-wrap items-center justify-between gap-4">
                                        <div class="flex items-center gap-4">
                                            <div class="text-center">
                                                <p class="text-[10px] font-semibold uppercase tracking-widest text-cyan-200">Nuits</p>
                                                <p class="text-3xl font-black">{{ $nights }}</p>
                                            </div>
                                            <span class="text-2xl font-light text-cyan-300">&times;</span>
                                            <div class="text-center">
                                                <p class="text-[10px] font-semibold uppercase tracking-widest text-cyan-200">Tarif / nuit</p>
                                                <p class="text-xl font-black">{{ number_format($nightly_rate, 2) }}</p>
                                            </div>
                                            <span class="text-2xl font-light text-cyan-300">=</span>
                                            <div class="text-center">
                                                <p class="text-[10px] font-semibold uppercase tracking-widest text-cyan-200">Total estimé</p>
                                                <p class="text-3xl font-black">{{ number_format($estimatedTotal, 2) }}</p>
                                            </div>
                                        </div>
                                        <div class="rounded-xl bg-white/10 px-4 py-2 text-center">
                                            <p class="text-[10px] font-semibold uppercase tracking-widest text-cyan-200">Sejour</p>
                                            <p class="text-sm font-bold">
                                                {{ \Carbon\Carbon::parse($check_in_date)->format('d/m') }}
                                                &rarr;
                                                {{ \Carbon\Carbon::parse($check_out_date)->format('d/m/Y') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @elseif($check_in_date && $check_out_date && $nights === 0)
                            <div class="xl:col-span-4 md:col-span-2">
                                <p class="rounded-xl bg-rose-50 px-4 py-2.5 text-xs font-semibold text-rose-700">
                                    <i class="fa-solid fa-triangle-exclamation mr-1.5"></i>
                                    La date de check-out doit être apres le check-in.
                                </p>
                            </div>
                            @elseif($nights > 0 && ! $nightly_rate)
                            <div class="xl:col-span-4 md:col-span-2">
                                <p class="rounded-xl bg-amber-50 px-4 py-2.5 text-xs font-semibold text-amber-700">
                                    <i class="fa-solid fa-info-circle mr-1.5"></i>
                                    {{ $nights }} nuit(s) detected. Renseignez ou selectionnez une chambre pour calculer le total.
                                </p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- ───── FOOTER SUBMIT ───── --}}
                <div class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                    <p class="text-xs text-slate-500">
                        <i class="fa-solid fa-circle-info mr-1 text-slate-400"></i>
                        Nom, telephone ou email requis pour identifier le client.
                    </p>
                    <button type="submit"
                        class="inline-flex items-center justify-center gap-2 rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800">
                        <i class="fa-solid fa-arrow-right"></i>
                        Verifier avant d'enregistrer
                    </button>
                </div>
            </form>

            @elseif($step === 'recap')
            {{-- ══════════════════════════════════════ RECAP ══════════════════════════════════════ --}}
            <div class="divide-y divide-slate-100">

                {{-- Identite & Contact --}}
                <div class="px-5 py-5 sm:px-6">
                    <p class="mb-4 text-xs font-semibold uppercase tracking-wider text-slate-400">
                        <i class="fa-solid fa-user mr-1.5"></i>Identite & Contact
                    </p>
                    <div class="grid gap-4 sm:grid-cols-2">
                        @if($full_name || $title)
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Nom complet</p>
                            <p class="mt-0.5 text-base font-bold text-slate-900">{{ trim(($title ? $title . ' ' : '') . $full_name) ?: '—' }}</p>
                        </div>
                        @endif
                        @if($preferred_name)
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Nom prefere</p>
                            <p class="mt-0.5 font-semibold text-slate-700">{{ $preferred_name }}</p>
                        </div>
                        @endif
                        @if($phone)
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Telephone</p>
                            <p class="mt-0.5 font-semibold text-slate-700">{{ $phone }}</p>
                        </div>
                        @endif
                        @if($secondary_phone)
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Tel secondaire</p>
                            <p class="mt-0.5 font-semibold text-slate-700">{{ $secondary_phone }}</p>
                        </div>
                        @endif
                        @if($email)
                        <div class="sm:col-span-2">
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Email</p>
                            <p class="mt-0.5 font-semibold text-slate-700">{{ $email }}</p>
                        </div>
                        @endif
                    </div>
                    <div class="mt-3 flex flex-wrap gap-2">
                        @if($is_identified)
                            <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-700"><i class="fa-solid fa-shield-check mr-1"></i>Identifie</span>
                        @endif
                        @if($vip_status)
                            <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-700"><i class="fa-solid fa-star mr-1"></i>VIP</span>
                        @endif
                        @if($blacklisted)
                            <span class="rounded-full bg-rose-100 px-3 py-1 text-xs font-bold text-rose-700"><i class="fa-solid fa-ban mr-1"></i>Liste rouge</span>
                        @endif
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ strtoupper($preferred_language) }}</span>
                        @if($travel_type)
                        @php
                            $travelLabels = [
                                'solo'        => ['label' => 'Seul(e)',       'icon' => 'fa-solid fa-person',             'class' => 'bg-slate-100 text-slate-700'],
                                'accompanied' => ['label' => 'Accompagne(e)', 'icon' => 'fa-solid fa-user-friends',       'class' => 'bg-indigo-100 text-indigo-700'],
                                'couple'      => ['label' => 'En couple',     'icon' => 'fa-solid fa-heart',              'class' => 'bg-rose-100 text-rose-700'],
                                'family'      => ['label' => 'En famille',    'icon' => 'fa-solid fa-house-chimney-user', 'class' => 'bg-emerald-100 text-emerald-700'],
                                'group'       => ['label' => 'En groupe',     'icon' => 'fa-solid fa-people-group',       'class' => 'bg-violet-100 text-violet-700'],
                            ];
                            $tl = $travelLabels[$travel_type] ?? null;
                        @endphp
                        @if($tl)
                            <span class="rounded-full px-3 py-1 text-xs font-bold {{ $tl['class'] }}">
                                <i class="{{ $tl['icon'] }} mr-1"></i>{{ $tl['label'] }}
                            </span>
                        @endif
                        @endif
                    </div>
                </div>

                {{-- Provenance --}}
                @if($nationality || $country || $city || $address)
                <div class="px-5 py-4 sm:px-6">
                    <p class="mb-4 text-xs font-semibold uppercase tracking-wider text-slate-400">
                        <i class="fa-solid fa-earth-africa mr-1.5"></i>Provenance & Adresse
                    </p>
                    <div class="grid gap-3 sm:grid-cols-2">
                        @if($nationality)
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Nationalite</p>
                            <p class="mt-0.5 font-semibold text-slate-700">{{ $nationality }}</p>
                        </div>
                        @endif
                        @if($country || $city)
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Pays / Ville</p>
                            <p class="mt-0.5 font-semibold text-slate-700">{{ collect([$country, $city])->filter()->join(', ') }}</p>
                        </div>
                        @endif
                        @if($address)
                        <div class="sm:col-span-2">
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Adresse</p>
                            <p class="mt-0.5 text-sm text-slate-600">{{ $address }}</p>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                {{-- Piece d'identite --}}
                @if($identity_document || $identity_document_type)
                <div class="px-5 py-4 sm:px-6">
                    <p class="mb-4 text-xs font-semibold uppercase tracking-wider text-slate-400">
                        <i class="fa-solid fa-passport mr-1.5"></i>Piece d'identite
                    </p>
                    <div class="grid gap-3 sm:grid-cols-2">
                        @if($identity_document_type)
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Type</p>
                            <p class="mt-0.5 font-semibold text-slate-700">{{ $identity_document_type }}</p>
                        </div>
                        @endif
                        @if($identity_document)
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Numero</p>
                            <p class="mt-0.5 font-mono font-bold text-slate-900">{{ $identity_document }}</p>
                        </div>
                        @endif
                        @if($identity_document_issue_place)
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Delivre a</p>
                            <p class="mt-0.5 font-semibold text-slate-700">{{ $identity_document_issue_place }}</p>
                        </div>
                        @endif
                        @if($identity_document_expires_at)
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Expiration</p>
                            <p class="mt-0.5 font-semibold text-slate-700">{{ \Carbon\Carbon::parse($identity_document_expires_at)->format('d/m/Y') }}</p>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                {{-- Pro --}}
                @if($profession || $company_name || $marketing_source)
                <div class="px-5 py-4 sm:px-6">
                    <p class="mb-4 text-xs font-semibold uppercase tracking-wider text-slate-400">
                        <i class="fa-solid fa-briefcase mr-1.5"></i>Professionnel
                    </p>
                    <div class="grid gap-3 sm:grid-cols-2">
                        @if($profession)
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Profession</p>
                            <p class="mt-0.5 font-semibold text-slate-700">{{ $profession }}</p>
                        </div>
                        @endif
                        @if($company_name)
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Societe</p>
                            <p class="mt-0.5 font-semibold text-slate-700">{{ $company_name }}</p>
                        </div>
                        @endif
                        @if($marketing_source)
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Source</p>
                            <p class="mt-0.5 font-semibold text-slate-700">{{ $marketing_source }}</p>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                {{-- Sejour --}}
                @if($with_room_rental)
                <div class="bg-cyan-50/60 px-5 py-4 sm:px-6">
                    <p class="mb-4 text-xs font-semibold uppercase tracking-wider text-cyan-700">
                        <i class="fa-solid fa-bed mr-1.5"></i>Sejour a creer
                    </p>
                    <div class="grid gap-3 sm:grid-cols-2">
                        @php $selectedRoom = $rooms->firstWhere('id', $room_id); @endphp
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-cyan-600">Chambre</p>
                            <p class="mt-0.5 font-bold text-slate-900">{{ $selectedRoom ? 'Chambre ' . $selectedRoom->number : '—' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-cyan-600">Check-in</p>
                            <p class="mt-0.5 font-semibold text-slate-700">{{ $check_in_date }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-cyan-600">Check-out</p>
                            <p class="mt-0.5 font-semibold text-slate-700">{{ $check_out_date }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-cyan-600">Occupants</p>
                            <p class="mt-0.5 font-semibold text-slate-700">{{ $adults }} adulte(s) + {{ $children }} enfant(s)</p>
                        </div>
                        @if($nightly_rate)
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-cyan-600">Tarif nuit</p>
                            <p class="mt-0.5 font-semibold text-slate-700">{{ number_format($nightly_rate, 2) }}</p>
                        </div>
                        @endif
                        @php
                            $recapNights = ($check_in_date && $check_out_date)
                                ? max(0, (int) \Carbon\Carbon::parse($check_in_date)->diffInDays(\Carbon\Carbon::parse($check_out_date)))
                                : 0;
                            $recapTotal = ($recapNights > 0 && $nightly_rate) ? $recapNights * $nightly_rate : null;
                        @endphp
                        @if($recapNights > 0)
                        <div class="sm:col-span-2 rounded-xl bg-cyan-600 px-4 py-3 text-white">
                            <p class="text-[10px] font-semibold uppercase tracking-widest text-cyan-200">Calcul du sejour</p>
                            <div class="mt-1.5 flex flex-wrap items-baseline gap-1.5 text-sm">
                                <span class="text-2xl font-black">{{ $recapNights }}</span>
                                <span class="text-cyan-300">nuit(s)</span>
                                @if($nightly_rate)
                                <span class="text-cyan-300">&times; {{ number_format($nightly_rate, 2) }}</span>
                                <span class="text-cyan-300">=</span>
                                <span class="text-2xl font-black">{{ number_format($recapTotal, 2) }}</span>
                                @endif
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                {{-- Recap footer --}}
                <div class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                    <button type="button" wire:click="backToForm"
                        class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                        <i class="fa-solid fa-arrow-left"></i>
                        Modifier la fiche
                    </button>
                    <button type="button" wire:click="confirmCreate"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-600 px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 disabled:opacity-60">
                        <i class="fa-solid fa-check" wire:loading.class="hidden" wire:target="confirmCreate"></i>
                        <i class="fa-solid fa-spinner fa-spin hidden" wire:loading.class.remove="hidden" wire:target="confirmCreate"></i>
                        <span wire:loading.remove wire:target="confirmCreate">
                            {{ $with_room_rental ? 'Confirmer et lancer le sejour' : 'Confirmer et enregistrer' }}
                        </span>
                        <span class="hidden" wire:loading wire:target="confirmCreate">Enregistrement en cours...</span>
                    </button>
                </div>
            </div>

            @elseif($step === 'ticket')
            {{-- ══════════════════════════════════════ TICKET ══════════════════════════════════════ --}}
            <style>
                @media print {
                    body * { visibility: hidden !important; }
                    .ticket-printable, .ticket-printable * { visibility: visible !important; }
                    .ticket-printable { position: fixed !important; top: 0 !important; left: 0 !important; width: 100% !important; z-index: 9999 !important; }
                }
            </style>
            @if($createdData)
            <div x-data="{ style: 'themed' }" class="p-5 sm:p-6">

                {{-- Toggle style --}}
                <div class="mb-6 flex flex-wrap gap-2">
                    <button type="button" @click="style = 'themed'"
                        :class="style === 'themed' ? 'bg-slate-900 text-white shadow-sm' : 'border border-slate-200 bg-white text-slate-600 hover:bg-slate-50'"
                        class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold transition">
                        <i class="fa-solid fa-palette"></i>
                        Ticket thematique
                    </button>
                    <button type="button" @click="style = 'pdf'"
                        :class="style === 'pdf' ? 'bg-slate-900 text-white shadow-sm' : 'border border-slate-200 bg-white text-slate-600 hover:bg-slate-50'"
                        class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold transition">
                        <i class="fa-solid fa-file-lines"></i>
                        Format recu / PDF
                    </button>
                </div>

                {{-- ─── TICKET THEMATIQUE ─── --}}
                <div x-show="style === 'themed'" x-cloak class="ticket-printable">
                    <div class="overflow-hidden rounded-3xl bg-slate-900 text-white shadow-2xl">
                        {{-- Header gradient --}}
                        <div class="relative overflow-hidden bg-gradient-to-br from-emerald-600 to-slate-800 px-7 py-8">
                            <div class="absolute -right-8 -top-8 h-40 w-40 rounded-full bg-white/5"></div>
                            <div class="absolute -bottom-6 right-16 h-24 w-24 rounded-full bg-white/5"></div>
                            <div class="relative z-10 flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <p class="text-[11px] font-bold uppercase tracking-[0.3em] text-emerald-300">ProStay Africa · Fiche d'accueil</p>
                                    <h3 class="mt-2 text-3xl font-black leading-tight text-white">
                                        {{ ($createdData['title'] ? $createdData['title'] . ' ' : '') . ($createdData['full_name'] ?: 'Client Anonyme') }}
                                    </h3>
                                    @if($createdData['preferred_name'])
                                    <p class="mt-1 text-base text-emerald-200">« {{ $createdData['preferred_name'] }} »</p>
                                    @endif
                                    <div class="mt-3 flex flex-wrap gap-2">
                                        @if($createdData['is_identified'])
                                        <span class="rounded-full bg-emerald-500/30 px-3 py-1 text-[11px] font-bold text-emerald-200">IDENTIFIE</span>
                                        @endif
                                        @if($createdData['vip_status'])
                                        <span class="rounded-full bg-amber-500/30 px-3 py-1 text-[11px] font-bold text-amber-200">⭐ VIP</span>
                                        @endif
                                        @if($createdData['blacklisted'])
                                        <span class="rounded-full bg-rose-500/30 px-3 py-1 text-[11px] font-bold text-rose-200">LISTE ROUGE</span>
                                        @endif
                                        @if($createdData['travel_type'])
                                        @php
                                            $travelMap = ['solo'=>'Seul(e)','accompanied'=>'Accompagne(e)','couple'=>'En couple','family'=>'En famille','group'=>'En groupe'];
                                            $travelIcons = ['solo'=>'fa-person','accompanied'=>'fa-user-friends','couple'=>'fa-heart','family'=>'fa-house-chimney-user','group'=>'fa-people-group'];
                                        @endphp
                                        <span class="rounded-full bg-white/15 px-3 py-1 text-[11px] font-bold text-white/80">
                                            <i class="fa-solid {{ $travelIcons[$createdData['travel_type']] ?? 'fa-users' }} mr-1"></i>
                                            {{ $travelMap[$createdData['travel_type']] ?? $createdData['travel_type'] }}
                                        </span>
                                        @endif
                                        <span class="rounded-full bg-white/10 px-3 py-1 text-[11px] font-bold text-white/60">{{ $createdData['preferred_language'] }}</span>
                                    </div>
                                </div>
                                <div class="shrink-0 self-start rounded-2xl bg-white/10 px-6 py-4 text-center backdrop-blur-sm">
                                    <p class="text-[10px] font-semibold uppercase tracking-widest text-white/40">Code client</p>
                                    <p class="mt-1 text-2xl font-black tracking-wider text-white">{{ $createdData['guest_code'] }}</p>
                                </div>
                            </div>
                        </div>
                        {{-- Body --}}
                        <div class="divide-y divide-white/10 px-7">
                            @if($createdData['phone'] || $createdData['email'])
                            <div class="grid gap-4 py-5 sm:grid-cols-2">
                                @if($createdData['phone'])
                                <div>
                                    <p class="text-[10px] font-semibold uppercase tracking-widest text-white/40">Telephone</p>
                                    <p class="mt-1 font-semibold text-white">{{ $createdData['phone'] }}</p>
                                </div>
                                @endif
                                @if($createdData['email'])
                                <div>
                                    <p class="text-[10px] font-semibold uppercase tracking-widest text-white/40">Email</p>
                                    <p class="mt-1 font-semibold text-white">{{ $createdData['email'] }}</p>
                                </div>
                                @endif
                            </div>
                            @endif

                            @if($createdData['nationality'] || $createdData['country'])
                            <div class="grid gap-4 py-5 sm:grid-cols-2">
                                @if($createdData['nationality'])
                                <div>
                                    <p class="text-[10px] font-semibold uppercase tracking-widest text-white/40">Nationalite</p>
                                    <p class="mt-1 font-semibold text-white">{{ $createdData['nationality'] }}</p>
                                </div>
                                @endif
                                @if($createdData['country'])
                                <div>
                                    <p class="text-[10px] font-semibold uppercase tracking-widest text-white/40">Residence</p>
                                    <p class="mt-1 font-semibold text-white">{{ collect([$createdData['country'], $createdData['city']])->filter()->join(', ') }}</p>
                                </div>
                                @endif
                            </div>
                            @endif

                            @if($createdData['with_room_rental'])
                            <div class="py-5">
                                <p class="mb-3 text-[10px] font-semibold uppercase tracking-widest text-cyan-400">Sejour en cours</p>
                                <div class="grid gap-4 rounded-2xl bg-cyan-500/10 p-5 sm:grid-cols-2">
                                    <div>
                                        <p class="text-[10px] text-cyan-400/70">Chambre</p>
                                        <p class="mt-0.5 text-2xl font-black text-white">{{ $createdData['room_number'] }}</p>
                                        @if($createdData['room_type'])
                                        <p class="text-xs text-white/50">{{ $createdData['room_type'] }}</p>
                                        @endif
                                    </div>
                                    <div>
                                        <p class="text-[10px] text-cyan-400/70">Periode</p>
                                        <p class="mt-0.5 font-bold text-white">{{ \Carbon\Carbon::parse($createdData['check_in_date'])->format('d/m/Y') }}</p>
                                        <p class="text-sm text-white/60">→ {{ \Carbon\Carbon::parse($createdData['check_out_date'])->format('d/m/Y') }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] text-cyan-400/70">Occupants</p>
                                        <p class="mt-0.5 font-semibold text-white">{{ $createdData['adults'] }} adulte(s) · {{ $createdData['children'] }} enfant(s)</p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] text-cyan-400/70">Tarif / nuit</p>
                                        <p class="mt-0.5 font-semibold text-white">{{ number_format($createdData['nightly_rate'], 2) }}</p>
                                    </div>
                                    @if($createdData['nights'] && $createdData['total_estimate'])
                                    <div class="sm:col-span-2 rounded-xl bg-white/10 px-4 py-3">
                                        <p class="text-[10px] font-semibold uppercase tracking-widest text-cyan-300">Total du sejour</p>
                                        <div class="mt-1 flex flex-wrap items-baseline gap-2 text-white">
                                            <span class="text-3xl font-black">{{ number_format($createdData['total_estimate'], 2) }}</span>
                                            <span class="text-sm text-white/50">{{ $createdData['nights'] }} nuits &times; {{ number_format($createdData['nightly_rate'], 2) }}</span>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @endif
                        </div>
                        {{-- Footer --}}
                        <div class="flex items-center justify-between px-7 py-4">
                            <p class="text-[11px] text-white/30">Enregistre le {{ $createdData['created_at'] }}</p>
                            <p class="text-[11px] text-white/30">ProStay Africa · Front Office</p>
                        </div>
                    </div>
                </div>

                {{-- ─── TICKET FORMAT RECU / PDF ─── --}}
                <div x-show="style === 'pdf'" x-cloak class="ticket-printable">
                    <div class="overflow-hidden rounded-xl border-2 border-slate-900 bg-white font-mono text-slate-900">
                        {{-- Header --}}
                        <div class="flex items-start justify-between border-b-2 border-slate-900 px-6 py-5">
                            <div>
                                <p class="text-xl font-black uppercase tracking-widest">ProStay Africa</p>
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Fiche d'enregistrement client</p>
                            </div>
                            <div class="text-right">
                                <p class="text-2xl font-black">{{ $createdData['guest_code'] }}</p>
                                <p class="text-[11px] text-slate-500">{{ $createdData['created_at'] }}</p>
                            </div>
                        </div>
                        {{-- Lines --}}
                        <div class="space-y-3 px-6 py-5">
                            <div class="flex justify-between border-b border-dashed border-slate-200 pb-2.5 text-sm">
                                <span class="font-semibold uppercase tracking-wide text-slate-500">Nom</span>
                                <span class="font-black text-right">{{ trim(($createdData['title'] ? $createdData['title'] . ' ' : '') . $createdData['full_name']) ?: 'Non renseigne' }}</span>
                            </div>
                            @if($createdData['phone'])
                            <div class="flex justify-between border-b border-dashed border-slate-200 pb-2.5 text-sm">
                                <span class="font-semibold uppercase tracking-wide text-slate-500">Tel</span>
                                <span class="font-semibold">{{ $createdData['phone'] }}</span>
                            </div>
                            @endif
                            @if($createdData['email'])
                            <div class="flex justify-between border-b border-dashed border-slate-200 pb-2.5 text-sm">
                                <span class="font-semibold uppercase tracking-wide text-slate-500">Email</span>
                                <span class="font-semibold">{{ $createdData['email'] }}</span>
                            </div>
                            @endif
                            @if($createdData['nationality'])
                            <div class="flex justify-between border-b border-dashed border-slate-200 pb-2.5 text-sm">
                                <span class="font-semibold uppercase tracking-wide text-slate-500">Nationalite</span>
                                <span class="font-semibold">{{ $createdData['nationality'] }}</span>
                            </div>
                            @endif
                            @if($createdData['company_name'])
                            <div class="flex justify-between border-b border-dashed border-slate-200 pb-2.5 text-sm">
                                <span class="font-semibold uppercase tracking-wide text-slate-500">Societe</span>
                                <span class="font-semibold">{{ $createdData['company_name'] }}</span>
                            </div>
                            @endif
                            <div class="flex justify-between border-b border-dashed border-slate-200 pb-2.5 text-sm">
                                <span class="font-semibold uppercase tracking-wide text-slate-500">Statut</span>
                                <span class="font-semibold">
                                    {{ $createdData['is_identified'] ? 'IDENTIFIE' : 'NON IDENTIFIE' }}{{ $createdData['vip_status'] ? ' · VIP' : '' }}
                                </span>
                            </div>
                            @if($createdData['travel_type'])
                            @php
                                $travelMapPdf = ['solo'=>'Seul(e)','accompanied'=>'Accompagne(e)','couple'=>'En couple','family'=>'En famille','group'=>'En groupe'];
                            @endphp
                            <div class="flex justify-between border-b border-dashed border-slate-200 pb-2.5 text-sm">
                                <span class="font-semibold uppercase tracking-wide text-slate-500">Voyage</span>
                                <span class="font-semibold">{{ $travelMapPdf[$createdData['travel_type']] ?? $createdData['travel_type'] }}</span>
                            </div>
                            @endif
                            @if($createdData['with_room_rental'])
                            <div class="mt-2 rounded-lg border border-slate-300 p-4">
                                <p class="mb-3 text-center text-xs font-black uppercase tracking-widest text-slate-700">── SEJOUR ──</p>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-slate-500">Chambre</span>
                                        <span class="font-black">{{ $createdData['room_number'] }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-slate-500">Check-in</span>
                                        <span class="font-semibold">{{ \Carbon\Carbon::parse($createdData['check_in_date'])->format('d/m/Y') }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-slate-500">Check-out</span>
                                        <span class="font-semibold">{{ \Carbon\Carbon::parse($createdData['check_out_date'])->format('d/m/Y') }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-slate-500">Occupants</span>
                                        <span class="font-semibold">{{ $createdData['adults'] }}A / {{ $createdData['children'] }}E</span>
                                    </div>
                                    <div class="flex justify-between border-t border-dashed border-slate-300 pt-2">
                                        <span class="font-semibold text-slate-500">Tarif / nuit</span>
                                        <span class="font-black">{{ number_format($createdData['nightly_rate'], 2) }}</span>
                                    </div>
                                    @if($createdData['nights'] && $createdData['total_estimate'])
                                    <div class="flex justify-between border-t-2 border-slate-900 pt-2 text-base">
                                        <span class="font-bold uppercase tracking-wide">Total ({{ $createdData['nights'] }} nuits)</span>
                                        <span class="font-black">{{ number_format($createdData['total_estimate'], 2) }}</span>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @endif
                        </div>
                        {{-- Footer --}}
                        <div class="border-t-2 border-slate-900 px-6 py-3 text-center">
                            <p class="text-[11px] uppercase tracking-widest text-slate-400">Merci de votre confiance · ProStay Africa</p>
                        </div>
                    </div>
                </div>

                {{-- Action buttons --}}
                <div class="mt-6 flex flex-wrap gap-3">
                    <button type="button" onclick="window.print()"
                        class="inline-flex items-center gap-2 rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800">
                        <i class="fa-solid fa-print"></i>
                        Imprimer / Sauvegarder PDF
                    </button>
                    <button type="button" wire:click="resetAll"
                        class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                        <i class="fa-solid fa-user-plus"></i>
                        Nouveau client
                    </button>
                    <a href="{{ route('customers.registry') }}" wire:navigate
                        class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                        <i class="fa-solid fa-address-book"></i>
                        Voir le registre
                    </a>
                </div>
            </div>
            @endif

            @endif
        </section>

        <aside class="space-y-6">
            <section class="prostay-surface p-5 sm:p-6">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Standards reception</p>
                <h2 class="mt-1 text-lg font-black text-slate-900">Ce que la fiche couvre</h2>
                <div class="mt-4 space-y-3 text-sm text-slate-600">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                        <p class="font-semibold text-slate-900">Identification fiable</p>
                        <p class="mt-1">Civilite, nationalite, piece, validite, origine et langue preferee.</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                        <p class="font-semibold text-slate-900">Exploitation immediate</p>
                        <p class="mt-1">Creation directe du sejour avec chambre, dates, occupants et tarif.</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                        <p class="font-semibold text-slate-900">Connaissance client</p>
                        <p class="mt-1">Preferences, notes front-office, statut VIP et contact d'urgence.</p>
                    </div>
                </div>
            </section>

            <section class="prostay-surface p-5 sm:p-6">
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input
                        type="text"
                        wire:model.live.debounce.400ms="search"
                        placeholder="Rechercher par code, nom, piece, contact, societe..."
                        class="prostay-input pl-10"
                    >
                </div>
                <p class="mt-3 text-xs text-slate-500">Le registre permet de retrouver rapidement un client au check-in, au restaurant ou a la caisse.</p>
            </section>
        </aside>
    </div>
    @endif

    @if($mode === 'registry')
    <section class="prostay-surface p-5 sm:p-6">
        <div class="flex justify-end">
            <div class="rounded-2xl bg-slate-100 px-4 py-2 text-xs font-semibold text-slate-600">
                {{ $customers->total() }} profils trouves
            </div>
        </div>

        <div class="mt-5 grid gap-4 lg:grid-cols-2">
            @forelse($customers as $customer)
                <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded-full bg-slate-900 px-2.5 py-1 text-[11px] font-bold uppercase tracking-wide text-white">
                                    {{ $customer->guest_code ?? 'Sans code' }}
                                </span>
                                @if($customer->vip_status)
                                    <span class="rounded-full bg-amber-100 px-2.5 py-1 text-[11px] font-bold uppercase tracking-wide text-amber-700">VIP</span>
                                @endif
                                @if($customer->blacklisted)
                                    <span class="rounded-full bg-rose-100 px-2.5 py-1 text-[11px] font-bold uppercase tracking-wide text-rose-700">Liste rouge</span>
                                @endif
                                <span class="rounded-full px-2.5 py-1 text-[11px] font-bold uppercase tracking-wide {{ $customer->is_identified ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-700' }}">
                                    {{ $customer->is_identified ? 'Identifie' : 'Non identifie' }}
                                </span>
                            </div>
                            <h3 class="mt-3 text-xl font-black text-slate-900">
                                {{ trim(collect([$customer->title, $customer->full_name])->filter()->join(' ')) ?: 'Client anonyme / walk-in' }}
                            </h3>
                            <p class="mt-1 text-sm text-slate-500">
                                {{ $customer->preferred_name ? 'Nom prefere: ' . $customer->preferred_name . ' · ' : '' }}
                                {{ $customer->nationality ?: 'Nationalite non renseignee' }}
                                {{ $customer->preferred_language ? ' · Langue: ' . strtoupper($customer->preferred_language) : '' }}
                            </p>
                        </div>

                        <div class="grid grid-cols-2 gap-2 text-center text-xs font-semibold">
                            <div class="rounded-2xl bg-slate-50 px-3 py-2">
                                <p class="text-slate-400">Sejours</p>
                                <p class="mt-1 text-base text-slate-900">{{ $customer->stays_count }}</p>
                            </div>
                            <div class="rounded-2xl bg-slate-50 px-3 py-2">
                                <p class="text-slate-400">Factures</p>
                                <p class="mt-1 text-base text-slate-900">{{ $customer->invoices_count }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 grid gap-3 text-sm text-slate-600 sm:grid-cols-2">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Contact principal</p>
                            <p class="mt-1 font-semibold text-slate-900">{{ $customer->phone ?: 'Non renseigne' }}</p>
                            <p class="mt-1">{{ $customer->email ?: 'Email non renseigne' }}</p>
                            @if($customer->secondary_phone)
                                <p class="mt-1">Secondaire: {{ $customer->secondary_phone }}</p>
                            @endif
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Piece & residence</p>
                            <p class="mt-1 font-semibold text-slate-900">
                                {{ $customer->identity_document_type ? ucfirst(str_replace('_', ' ', $customer->identity_document_type)) : 'Piece non renseignee' }}
                            </p>
                            <p class="mt-1">{{ $customer->identity_document ?: 'Numero non renseigne' }}</p>
                            <p class="mt-1">{{ collect([$customer->city, $customer->country])->filter()->join(', ') ?: 'Adresse courte non renseignee' }}</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Profil commercial</p>
                            <p class="mt-1 font-semibold text-slate-900">{{ $customer->company_name ?: 'Client individuel' }}</p>
                            <p class="mt-1">{{ $customer->profession ?: 'Profession non renseignee' }}</p>
                            <p class="mt-1">{{ $customer->marketing_source ?: 'Source acquisition non renseignee' }}</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Urgence & preferences</p>
                            <p class="mt-1 font-semibold text-slate-900">{{ $customer->emergency_contact_name ?: 'Contact d urgence absent' }}</p>
                            <p class="mt-1">{{ $customer->emergency_contact_phone ?: 'Telephone non renseigne' }}</p>
                            <p class="mt-1">{{ \Illuminate\Support\Str::limit($customer->guest_preferences ?: 'Aucune preference enregistree', 110) }}</p>
                        </div>
                    </div>

                    @if($customer->internal_notes)
                        <div class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Notes internes</p>
                            <p class="mt-1">{{ \Illuminate\Support\Str::limit($customer->internal_notes, 180) }}</p>
                        </div>
                    @endif
                </article>
            @empty
                <div class="lg:col-span-2 rounded-3xl border border-dashed border-slate-300 bg-slate-50 px-6 py-12 text-center">
                    <p class="text-lg font-bold text-slate-900">Aucun client enregistre</p>
                    <p class="mt-2 text-sm text-slate-500">Le nouveau formulaire est pret pour constituer un vrai registre clientele hotelier.</p>
                </div>
            @endforelse
        </div>

        <div class="mt-6">
            {{ $customers->links() }}
        </div>
    </section>
    @endif
</div>
