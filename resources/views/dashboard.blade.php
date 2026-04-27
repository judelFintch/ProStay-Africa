<x-app-layout>
    @php
        $workspaceContext = session('workspace_context', 'all');
        $domainPermissions = [
            'hotel' => ['customers.manage', 'rooms.manage', 'stays.manage', 'laundry.manage'],
            'restaurant' => ['orders.manage', 'pos.use', 'stock.manage'],
        ];

        $currentUser = auth()->user();
        $hasAssignedRoles = $currentUser?->roles()->exists() ?? false;
        $isAdmin = $hasAssignedRoles
            ? $currentUser->roles()->where('name', 'admin')->exists()
            : false;
        $canAccessHotel = ! $hasAssignedRoles || $isAdmin || $currentUser->roles()
            ->whereHas('permissions', fn ($query) => $query->whereIn('name', $domainPermissions['hotel']))
            ->exists();
        $canAccessRestaurant = ! $hasAssignedRoles || $isAdmin || $currentUser->roles()
            ->whereHas('permissions', fn ($query) => $query->whereIn('name', $domainPermissions['restaurant']))
            ->exists();

        $actionSections = [
            [
                'title' => 'Front Desk',
                'subtitle' => 'Reception, hebergement et relation client',
                'domain' => 'hotel',
                'theme' => [
                    'panel' => 'from-cyan-500/15 via-white to-emerald-500/10',
                    'ring' => 'ring-cyan-200',
                    'kicker' => 'text-cyan-700',
                    'icon' => 'from-cyan-500 to-emerald-600',
                    'button' => 'border-cyan-200 bg-white text-cyan-900 hover:bg-cyan-50',
                ],
                'actions' => [
                    [
                        'label' => 'Nouveau client',
                        'description' => 'Creer une fiche client complete.',
                        'route' => route('customers.index'),
                        'icon' => 'fa-user-plus',
                    ],
                    [
                        'label' => 'Liste des clients',
                        'description' => 'Acceder au registre des clients.',
                        'route' => route('customers.registry'),
                        'icon' => 'fa-address-book',
                    ],
                    [
                        'label' => 'Nouvelle reservation',
                        'description' => 'Enregistrer une reservation rapidement.',
                        'route' => route('reservations.index'),
                        'icon' => 'fa-calendar-plus',
                    ],
                    [
                        'label' => 'Tableau reception',
                        'description' => 'Voir arrivees, departs et planning hotel.',
                        'route' => route('hotel.reception'),
                        'icon' => 'fa-concierge-bell',
                    ],
                    [
                        'label' => 'Check-in client',
                        'description' => 'Lancer une arrivee avec attribution de chambre.',
                        'route' => route('customers.index'),
                        'icon' => 'fa-right-to-bracket',
                    ],
                    [
                        'label' => 'Check-out client',
                        'description' => 'Traiter un depart et cloturer le sejour.',
                        'route' => route('reservations.index'),
                        'icon' => 'fa-right-from-bracket',
                    ],
                    [
                        'label' => 'Chambres disponibles',
                        'description' => 'Voir l etat des chambres et disponibilites.',
                        'route' => route('rooms.index'),
                        'icon' => 'fa-bed',
                    ],
                ],
            ],
            [
                'title' => 'Restaurant & Caisse',
                'subtitle' => 'Commandes, encaissement et suivi quotidien',
                'domain' => 'restaurant',
                'theme' => [
                    'panel' => 'from-amber-500/15 via-white to-orange-500/10',
                    'ring' => 'ring-amber-200',
                    'kicker' => 'text-amber-700',
                    'icon' => 'from-amber-500 to-orange-600',
                    'button' => 'border-amber-200 bg-white text-amber-900 hover:bg-amber-50',
                ],
                'actions' => [
                    [
                        'label' => 'Prendre commande',
                        'description' => 'Saisir une commande restaurant ou bar.',
                        'route' => route('orders.create'),
                        'icon' => 'fa-utensils',
                    ],
                    [
                        'label' => 'Gestion des plats',
                        'description' => 'Composer les recettes et verifier le stock ingredient.',
                        'route' => route('dishes.index'),
                        'icon' => 'fa-bowl-food',
                    ],
                    [
                        'label' => 'Suivi commandes livrees',
                        'description' => 'Valider et ajouter des commandes sur facture.',
                        'route' => route('billing.invoices'),
                        'icon' => 'fa-list-check',
                    ],
                    [
                        'label' => 'Gestion serveurs',
                        'description' => 'Activer les serveurs et leurs comptes.',
                        'route' => route('servers.index'),
                        'icon' => 'fa-user-tie',
                    ],
                    [
                        'label' => 'Vente POS',
                        'description' => 'Encaisser une vente rapide immediatement.',
                        'route' => route('pos.quick-sale'),
                        'icon' => 'fa-cash-register',
                    ],
                    [
                        'label' => 'Creer facture',
                        'description' => 'Generer une facture a partir des commandes.',
                        'route' => route('billing.invoices'),
                        'icon' => 'fa-file-invoice-dollar',
                    ],
                    [
                        'label' => 'Enregistrer paiement',
                        'description' => 'Saisir un reglement client.',
                        'route' => route('billing.payments'),
                        'icon' => 'fa-wallet',
                    ],
                    [
                        'label' => 'Stock et alertes',
                        'description' => 'Suivre les mouvements et les seuils de stock.',
                        'route' => route('stock.index'),
                        'icon' => 'fa-boxes-stacked',
                    ],
                    [
                        'label' => 'Rapport du jour',
                        'description' => 'Consulter les indicateurs d activite.',
                        'route' => route('reports.index'),
                        'icon' => 'fa-chart-pie',
                    ],
                ],
            ],
        ];

        $actionSections = array_values(array_filter($actionSections, function (array $section) use ($workspaceContext, $canAccessHotel, $canAccessRestaurant): bool {
            $domain = $section['domain'] ?? null;

            if ($domain === 'hotel' && ! $canAccessHotel) {
                return false;
            }

            if ($domain === 'restaurant' && ! $canAccessRestaurant) {
                return false;
            }

            if ($workspaceContext === 'all') {
                return true;
            }

            return $domain === $workspaceContext;
        }));

        $moduleLinks = [
            ['label' => 'Clients', 'route' => route('customers.index'), 'icon' => 'fa-users', 'domain' => 'shared'],
            ['label' => 'Utilisateurs', 'route' => route('users.index'), 'icon' => 'fa-user-shield', 'domain' => 'shared'],
            ['label' => 'Factures', 'route' => route('billing.invoices'), 'icon' => 'fa-file-invoice', 'domain' => 'shared'],
            ['label' => 'Paiements', 'route' => route('billing.payments'), 'icon' => 'fa-wallet', 'domain' => 'shared'],
            ['label' => 'Rapports', 'route' => route('reports.index'), 'icon' => 'fa-chart-line', 'domain' => 'shared'],
            ['label' => 'Services', 'route' => route('services.index'), 'icon' => 'fa-diagram-project', 'domain' => 'shared'],
            ['label' => 'Reception', 'route' => route('hotel.reception'), 'icon' => 'fa-concierge-bell', 'domain' => 'hotel'],
            ['label' => 'Reservations', 'route' => route('reservations.index'), 'icon' => 'fa-calendar-check', 'domain' => 'hotel'],
            ['label' => 'Chambres', 'route' => route('rooms.index'), 'icon' => 'fa-bed', 'domain' => 'hotel'],
            ['label' => 'Blanchisserie', 'route' => route('laundry.index'), 'icon' => 'fa-soap', 'domain' => 'hotel'],
            ['label' => 'Commandes', 'route' => route('orders.create'), 'icon' => 'fa-utensils', 'domain' => 'restaurant'],
            ['label' => 'Plats', 'route' => route('dishes.index'), 'icon' => 'fa-bowl-food', 'domain' => 'restaurant'],
            ['label' => 'Serveurs', 'route' => route('servers.index'), 'icon' => 'fa-user-tie', 'domain' => 'restaurant'],
            ['label' => 'Stock', 'route' => route('stock.index'), 'icon' => 'fa-boxes-stacked', 'domain' => 'restaurant'],
            ['label' => 'POS', 'route' => route('pos.quick-sale'), 'icon' => 'fa-cash-register', 'domain' => 'restaurant'],
        ];

        $moduleLinks = array_values(array_filter($moduleLinks, function (array $module) use ($workspaceContext, $canAccessHotel, $canAccessRestaurant): bool {
            $domain = $module['domain'] ?? 'shared';

            if ($domain === 'hotel' && ! $canAccessHotel) {
                return false;
            }

            if ($domain === 'restaurant' && ! $canAccessRestaurant) {
                return false;
            }

            if ($workspaceContext === 'all') {
                return true;
            }

            return $domain === 'shared' || $domain === $workspaceContext;
        }));

        $workspaceLabel = match ($workspaceContext) {
            'hotel' => 'Mode Hotel',
            'restaurant' => 'Mode Restaurant',
            default => 'Mode Global',
        };

        $checkoutDueCount = $canAccessHotel
            ? \App\Models\Stay::query()
                ->where('status', \App\Enums\StayStatus::Active->value)
                ->whereDate('expected_check_out_at', '<=', now()->toDateString())
                ->count()
            : 0;
    @endphp

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-600">ProStay Africa</p>
                <h2 class="text-2xl font-black leading-tight text-slate-900">
                    {{ __('messages.operational_dashboard') }}
                </h2>
                <p class="text-sm text-slate-500">{{ now()->translatedFormat('l d F Y') }} · {{ $workspaceLabel }}</p>
            </div>
            <div class="hidden items-center gap-2 rounded-full bg-slate-100 px-4 py-2 sm:flex">
                <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                <span class="text-xs font-medium text-slate-700">{{ __('messages.system_online') }}</span>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto flex max-w-7xl flex-col gap-6 px-4 sm:px-6 lg:px-8">
            <section class="overflow-hidden rounded-[2rem] bg-[radial-gradient(circle_at_top_left,_rgba(16,185,129,0.18),_transparent_32%),linear-gradient(135deg,_#ffffff_0%,_#f8fafc_55%,_#ecfeff_100%)] p-6 shadow-sm ring-1 ring-slate-200 sm:p-8">
                <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-emerald-700">Action Center</p>
                        <h3 class="mt-1 max-w-3xl text-xl font-black leading-tight text-slate-900 sm:text-2xl">
                            Acces rapide.
                        </h3>
                        <p class="mt-1 text-xs text-slate-500">Mode: <span class="font-semibold text-slate-700">{{ $workspaceLabel }}</span> · Donnees partagees hotel/restaurant.</p>
                    </div>

                    <div class="grid gap-2.5 sm:grid-cols-2 lg:grid-cols-6">
                        <a href="{{ route('customers.index') }}" wire:navigate class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-900 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                            Nouveau client
                        </a>
                        <a href="{{ route('rooms.index') }}" wire:navigate class="rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-800 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md hover:bg-emerald-100">
                            Tableau occupation chambres
                        </a>
                        @if($canAccessHotel)
                            <a href="{{ route('reservations.index', ['report' => 'checkout_due']) }}" wire:navigate class="inline-flex items-center justify-between gap-2 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-800 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md hover:bg-amber-100">
                                <span>Rapport checkout a terme</span>
                                <span class="rounded-full bg-amber-200 px-2 py-0.5 text-[10px] font-black text-amber-900">{{ $checkoutDueCount }}</span>
                            </a>
                        @endif
                        <a href="{{ route('billing.invoices') }}" wire:navigate class="rounded-xl border border-indigo-200 bg-indigo-50 px-3 py-2 text-xs font-semibold text-indigo-700 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md hover:bg-indigo-100">
                            Suivi commandes livrees
                        </a>
                        <a href="{{ route('pos.quick-sale') }}" wire:navigate class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-900 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                            Vente POS
                        </a>
                        <a href="{{ route('reports.index') }}" wire:navigate class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-900 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                            Rapport du jour
                        </a>
                    </div>
                </div>

                <div class="mt-5 rounded-2xl border border-slate-200 bg-white/80 p-3">
                    <p class="mb-2 text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Contexte d utilisation</p>
                    <div class="grid gap-2 sm:grid-cols-3">
                        <a href="{{ route('workspace.switch', 'all') }}" wire:navigate class="rounded-xl px-3 py-2 text-center text-xs font-semibold transition {{ $workspaceContext === 'all' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">Global</a>
                        <a href="{{ route('workspace.switch', 'hotel') }}" wire:navigate class="rounded-xl px-3 py-2 text-center text-xs font-semibold transition {{ $workspaceContext === 'hotel' ? 'bg-emerald-700 text-white' : 'bg-emerald-50 text-emerald-800 hover:bg-emerald-100' }}">Hotel</a>
                        <a href="{{ route('workspace.switch', 'restaurant') }}" wire:navigate class="rounded-xl px-3 py-2 text-center text-xs font-semibold transition {{ $workspaceContext === 'restaurant' ? 'bg-amber-600 text-white' : 'bg-amber-50 text-amber-800 hover:bg-amber-100' }}">Restaurant</a>
                    </div>
                </div>
            </section>

            <div class="space-y-7">
                @foreach($actionSections as $section)
                    <section class="rounded-3xl bg-gradient-to-br p-4 shadow-sm ring-1 sm:p-5 {{ $section['theme']['panel'] }} {{ $section['theme']['ring'] }}">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.2em] {{ $section['theme']['kicker'] }}">{{ $section['title'] }}</p>
                                <h3 class="mt-1 text-lg font-black text-slate-900 sm:text-xl">{{ $section['subtitle'] }}</h3>
                            </div>
                            <span class="self-start rounded-full bg-white/80 px-3 py-1 text-xs font-semibold text-slate-600">
                                {{ count($section['actions']) }} actions
                            </span>
                        </div>

                        <div class="mt-5 grid gap-2.5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5">
                            @foreach($section['actions'] as $action)
                                <a
                                    href="{{ $action['route'] }}"
                                    wire:navigate
                                    class="group flex min-h-[108px] flex-col rounded-xl border p-3 transition hover:-translate-y-0.5 hover:shadow-md {{ $section['theme']['button'] }}"
                                >
                                    <div class="inline-flex h-7 w-7 items-center justify-center rounded-md bg-gradient-to-br text-white {{ $section['theme']['icon'] }}">
                                        <i class="fa-solid {{ $action['icon'] }}"></i>
                                    </div>
                                    <p class="mt-2.5 text-xs font-bold text-slate-900">{{ $action['label'] }}</p>
                                    <p class="mt-1 line-clamp-2 text-[10px] leading-4 text-slate-600">{{ $action['description'] }}</p>
                                    <p class="mt-auto pt-1.5 text-[10px] font-semibold text-slate-700 group-hover:text-slate-900">
                                        Ouvrir <i class="fa-solid fa-arrow-right ml-1"></i>
                                    </p>
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endforeach
            </div>

            <section class="rounded-3xl bg-white p-5 shadow-sm ring-1 ring-slate-200 sm:p-6">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Navigation</p>
                        <h3 class="mt-1 text-xl font-black text-slate-900">Acces complet aux modules</h3>
                        <p class="mt-1 text-sm text-slate-500">
                            Utilise la barre laterale pour la navigation principale. Cette zone sert de rappel visuel rapide.
                        </p>
                    </div>
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                        {{ count($moduleLinks) }} modules
                    </span>
                </div>

                <div class="mt-5 grid gap-2.5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
                    @foreach($moduleLinks as $module)
                        <a
                            href="{{ $module['route'] }}"
                            wire:navigate
                            class="flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-100 hover:text-slate-900"
                        >
                            <i class="fa-solid {{ $module['icon'] }} w-4 text-center text-slate-400"></i>
                            <span>{{ $module['label'] }}</span>
                        </a>
                    @endforeach
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
