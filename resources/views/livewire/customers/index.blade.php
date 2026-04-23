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

    <div class="grid gap-6 xl:grid-cols-[1.45fr_0.95fr]">
        <section class="prostay-surface overflow-hidden">
            <div class="border-b border-slate-200 bg-slate-50/80 px-5 py-4 sm:px-6">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-600">Front Office</p>
                <h2 class="mt-1 text-xl font-black text-slate-900">Nouvelle fiche client</h2>
                <p class="mt-1 text-sm text-slate-500">Formulaire complet pour reception, hebergement, identification et relation client.</p>
            </div>

            <form wire:submit="createCustomer" class="space-y-6 p-5 sm:p-6">
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
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Nom complet</label>
                        <input type="text" wire:model="full_name" placeholder="Nom officiel du client" class="prostay-input" />
                        @error('full_name') <p class="mt-1 text-xs font-semibold text-rose-700">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Nom prefere</label>
                        <input type="text" wire:model="preferred_name" placeholder="Comment l'equipe doit s'adresser au client" class="prostay-input" />
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

                <div class="rounded-2xl border border-slate-200 bg-white p-4">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Identite et provenance</p>
                            <h3 class="mt-1 text-base font-bold text-slate-900">Informations civiles</h3>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <label class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-800">
                                <input type="checkbox" wire:model="is_identified" class="rounded border-emerald-300 text-emerald-600 shadow-sm focus:ring-emerald-500">
                                Client identifie
                            </label>
                            <label class="inline-flex items-center gap-2 rounded-full border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-800">
                                <input type="checkbox" wire:model="vip_status" class="rounded border-amber-300 text-amber-600 shadow-sm focus:ring-amber-500">
                                VIP
                            </label>
                            <label class="inline-flex items-center gap-2 rounded-full border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-800">
                                <input type="checkbox" wire:model="blacklisted" class="rounded border-rose-300 text-rose-600 shadow-sm focus:ring-rose-500">
                                Liste rouge
                            </label>
                        </div>
                    </div>

                    <div class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
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

                <div class="rounded-2xl border border-slate-200 bg-white p-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Contact et relation client</p>
                    <h3 class="mt-1 text-base font-bold text-slate-900">Coordonnees d'exploitation</h3>

                    <div class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Telephone principal</label>
                            <input type="text" wire:model="phone" class="prostay-input" />
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Telephone secondaire</label>
                            <input type="text" wire:model="secondary_phone" class="prostay-input" />
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Email</label>
                            <input type="email" wire:model="email" class="prostay-input" />
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
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Source acquisition</label>
                            <input type="text" wire:model="marketing_source" placeholder="Walk-in, agence, OTA..." class="prostay-input" />
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Verification documentaire</p>
                    <h3 class="mt-1 text-base font-bold text-slate-900">Piece d'identite</h3>

                    <div class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
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
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Numero de piece</label>
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

                <div class="grid gap-4 lg:grid-cols-2">
                    <div class="rounded-2xl border border-slate-200 bg-white p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Securite client</p>
                        <h3 class="mt-1 text-base font-bold text-slate-900">Contact d'urgence</h3>

                        <div class="mt-4 grid gap-4">
                            <div>
                                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Nom du contact</label>
                                <input type="text" wire:model="emergency_contact_name" class="prostay-input" />
                            </div>
                            <div>
                                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Telephone du contact</label>
                                <input type="text" wire:model="emergency_contact_phone" class="prostay-input" />
                            </div>
                            <div>
                                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Lien avec le client</label>
                                <input type="text" wire:model="emergency_contact_relationship" placeholder="Conjoint, frere, collegue..." class="prostay-input" />
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Connaissance client</p>
                        <h3 class="mt-1 text-base font-bold text-slate-900">Preferences et notes internes</h3>

                        <div class="mt-4 grid gap-4">
                            <div>
                                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Preferences client</label>
                                <textarea wire:model="guest_preferences" rows="3" class="prostay-input" placeholder="Habitudes, regime alimentaire, preferences de chambre, allergies..."></textarea>
                            </div>
                            <div>
                                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Notes internes</label>
                                <textarea wire:model="internal_notes" rows="3" class="prostay-input" placeholder="Informations visibles seulement par l'equipe"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-cyan-200 bg-cyan-50/70 p-4">
                    <label class="inline-flex items-center gap-2 rounded-full border border-cyan-200 bg-white px-3 py-2 text-sm font-semibold text-cyan-900">
                        <input type="checkbox" wire:model.live="with_room_rental" class="rounded border-cyan-300 text-cyan-700 shadow-sm focus:ring-cyan-500">
                        Creer immediatement le sejour du client (reservation + check-in)
                    </label>

                    @if($with_room_rental)
                        <div class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                            <div>
                                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-cyan-800">Chambre</label>
                                <select wire:model="room_id" class="prostay-input">
                                    <option value="">Selectionner une chambre</option>
                                    @foreach($rooms as $room)
                                        <option value="{{ $room->id }}">
                                            Chambre {{ $room->number }} | cap {{ $room->capacity }} | {{ number_format($room->price, 2, '.', ' ') }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('room_id') <p class="mt-1 text-xs font-semibold text-rose-700">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-cyan-800">Check-in</label>
                                <input type="date" wire:model="check_in_date" class="prostay-input" />
                                @error('check_in_date') <p class="mt-1 text-xs font-semibold text-rose-700">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-cyan-800">Check-out</label>
                                <input type="date" wire:model="check_out_date" class="prostay-input" />
                                @error('check_out_date') <p class="mt-1 text-xs font-semibold text-rose-700">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-cyan-800">Tarif nuit</label>
                                <input type="number" step="0.01" min="0" wire:model="nightly_rate" class="prostay-input" />
                            </div>
                            <div>
                                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-cyan-800">Adultes</label>
                                <input type="number" min="1" wire:model="adults" class="prostay-input" />
                            </div>
                            <div>
                                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-cyan-800">Enfants</label>
                                <input type="number" min="0" wire:model="children" class="prostay-input" />
                            </div>
                            <div class="md:col-span-2">
                                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-cyan-800">Notes de sejour</label>
                                <input type="text" wire:model="rental_notes" class="prostay-input" />
                            </div>
                        </div>
                    @endif
                </div>

                <div class="flex flex-col gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm text-slate-500">Au moins un identifiant client est requis: nom, telephone ou email.</p>
                    <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800">
                        <i class="fa-solid fa-user-plus"></i>
                        {{ $with_room_rental ? 'Enregistrer le client et lancer le sejour' : 'Enregistrer la fiche client' }}
                    </button>
                </div>
            </form>
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

    <section class="prostay-surface p-5 sm:p-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Guest Registry</p>
                <h2 class="mt-1 text-xl font-black text-slate-900">Clients enregistres</h2>
                <p class="mt-1 text-sm text-slate-500">Vue reception avec identification, relation commerciale et activite du client.</p>
            </div>
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
</div>
