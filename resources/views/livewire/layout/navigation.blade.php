<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component
{
    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<nav x-data="{ open: false }" class="relative z-40">
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

        $links = [
            ['label' => __('Dashboard'), 'route' => 'dashboard', 'match' => 'dashboard', 'icon' => 'fa-chart-line', 'domain' => 'shared'],
            ['label' => __('Customers'), 'route' => 'customers.index', 'match' => 'customers.*', 'icon' => 'fa-users', 'domain' => 'shared'],
            ['label' => __('Users'), 'route' => 'users.index', 'match' => 'users.*', 'icon' => 'fa-user-shield', 'domain' => 'shared'],
            ['label' => __('Invoices'), 'route' => 'billing.invoices', 'match' => 'billing.invoices', 'icon' => 'fa-file-invoice', 'domain' => 'shared'],
            ['label' => __('Payments'), 'route' => 'billing.payments', 'match' => 'billing.payments', 'icon' => 'fa-wallet', 'domain' => 'shared'],
            ['label' => __('Reports'), 'route' => 'reports.index', 'match' => 'reports.*', 'icon' => 'fa-chart-pie', 'domain' => 'shared'],
            ['label' => __('Services'), 'route' => 'services.index', 'match' => 'services.*', 'icon' => 'fa-diagram-project', 'domain' => 'shared'],
            ['label' => __('Reception'), 'route' => 'hotel.reception', 'match' => 'hotel.*', 'icon' => 'fa-concierge-bell', 'domain' => 'hotel'],
            ['label' => __('Reservations'), 'route' => 'reservations.index', 'match' => 'reservations.*', 'icon' => 'fa-calendar-check', 'domain' => 'hotel'],
            ['label' => __('Rooms'), 'route' => 'rooms.index', 'match' => 'rooms.*', 'icon' => 'fa-bed', 'domain' => 'hotel'],
            ['label' => __('Laundry'), 'route' => 'laundry.index', 'match' => 'laundry.*', 'icon' => 'fa-soap', 'domain' => 'hotel'],
            ['label' => __('Suivi commandes'), 'route' => 'orders.tracking', 'match' => 'orders.tracking', 'icon' => 'fa-table-list', 'highlight' => true, 'domain' => 'restaurant'],
            ['label' => __('Orders'), 'route' => 'orders.create', 'match' => 'orders.create', 'icon' => 'fa-utensils', 'domain' => 'restaurant'],
            ['label' => __('Plats'), 'route' => 'dishes.index', 'match' => 'dishes.*', 'icon' => 'fa-bowl-food', 'domain' => 'restaurant'],
            ['label' => __('Servers'), 'route' => 'servers.index', 'match' => 'servers.*', 'icon' => 'fa-user-tie', 'domain' => 'restaurant'],
            ['label' => __('Stock'), 'route' => 'stock.index', 'match' => 'stock.*', 'icon' => 'fa-boxes-stacked', 'domain' => 'restaurant'],
            ['label' => __('POS'), 'route' => 'pos.quick-sale', 'match' => 'pos.*', 'icon' => 'fa-cash-register', 'domain' => 'restaurant'],
        ];

        $links = array_values(array_filter($links, function (array $link) use ($workspaceContext, $canAccessHotel, $canAccessRestaurant): bool {
            $domain = $link['domain'] ?? 'shared';

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
    @endphp

    <div class="sticky top-0 z-30 border-b border-slate-200 bg-white/90 backdrop-blur lg:hidden">
        <div class="flex items-center justify-between px-4 py-3">
            <div class="flex items-center gap-2">
                <x-application-logo class="h-8 w-8 fill-current text-emerald-700" />
                <div>
                    <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-emerald-700">ProStay</p>
                    <p class="text-sm font-bold text-slate-900">{{ $workspaceLabel }}</p>
                </div>
            </div>
            <button @click="open = !open" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-700 shadow-sm">
                <i class="fa-solid" :class="open ? 'fa-xmark' : 'fa-bars'"></i>
            </button>
        </div>
    </div>

    <div x-cloak x-show="open" class="fixed inset-0 z-40 bg-slate-900/40 lg:hidden" @click="open = false"></div>

    <aside
        class="fixed inset-y-0 left-0 z-50 w-72 border-r border-slate-200 bg-white shadow-xl transition-transform duration-300 lg:hidden"
        :class="open ? 'translate-x-0' : '-translate-x-full'"
    >
        <div class="flex h-full flex-col">
            <div class="border-b border-slate-200 px-5 py-4">
                <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center gap-3" @click="open = false">
                    <x-application-logo class="h-10 w-10 fill-current text-emerald-700" />
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-emerald-700">ProStay Africa</p>
                        <p class="text-sm font-bold text-slate-900">{{ $workspaceLabel }}</p>
                    </div>
                </a>
            </div>

            <div class="flex-1 overflow-y-auto px-3 py-4">
                <div class="space-y-1">
                    <div class="mb-2 rounded-xl border border-slate-200 bg-slate-50 p-2">
                        <p class="mb-1 px-2 text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-500">Contexte</p>
                        <div class="grid grid-cols-3 gap-1">
                            <a href="{{ route('workspace.switch', 'all') }}" wire:navigate @click="open = false" class="rounded-lg px-2 py-1.5 text-center text-[11px] font-semibold transition {{ $workspaceContext === 'all' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100' }}">Global</a>
                            <a href="{{ route('workspace.switch', 'hotel') }}" wire:navigate @click="open = false" class="rounded-lg px-2 py-1.5 text-center text-[11px] font-semibold transition {{ $workspaceContext === 'hotel' ? 'bg-emerald-700 text-white' : 'text-slate-600 hover:bg-slate-100' }}">Hotel</a>
                            <a href="{{ route('workspace.switch', 'restaurant') }}" wire:navigate @click="open = false" class="rounded-lg px-2 py-1.5 text-center text-[11px] font-semibold transition {{ $workspaceContext === 'restaurant' ? 'bg-amber-600 text-white' : 'text-slate-600 hover:bg-slate-100' }}">Restaurant</a>
                        </div>
                    </div>

                    @foreach($links as $link)
                        @php
                            $isActive = request()->routeIs($link['match']);
                            $isHighlighted = $link['highlight'] ?? false;
                        @endphp
                        <a
                            href="{{ route($link['route']) }}"
                            wire:navigate
                            @click="open = false"
                            class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition {{ $isActive ? 'bg-slate-900 text-white' : ($isHighlighted ? 'border border-amber-200 bg-amber-50 text-amber-800 hover:bg-amber-100' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900') }}"
                        >
                            <i class="fa-solid {{ $link['icon'] }} w-4 text-center"></i>
                            <span>{{ $link['label'] }}</span>
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="border-t border-slate-200 p-4">
                <div class="mb-3 rounded-xl bg-slate-50 px-3 py-2">
                    <p class="text-sm font-semibold text-slate-900" x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name" x-on:profile-updated.window="name = $event.detail.name"></p>
                    <p class="text-xs text-slate-500">{{ auth()->user()->email }}</p>
                </div>

                <div class="mb-3 rounded-full border border-slate-200 p-1 text-center text-xs shadow-sm">
                    <a href="{{ route('locale.switch', 'fr') }}" class="rounded-full px-3 py-1 font-semibold {{ app()->getLocale() === 'fr' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:text-slate-900' }}">FR</a>
                    <a href="{{ route('locale.switch', 'en') }}" class="rounded-full px-3 py-1 font-semibold {{ app()->getLocale() === 'en' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:text-slate-900' }}">EN</a>
                </div>

                <a href="{{ route('profile') }}" wire:navigate class="mb-2 flex w-full items-center gap-2 rounded-xl px-3 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-100 hover:text-slate-900" @click="open = false">
                    <i class="fa-regular fa-user w-4 text-center"></i>
                    {{ __('Profile') }}
                </a>

                <button wire:click="logout" class="flex w-full items-center gap-2 rounded-xl px-3 py-2 text-sm font-semibold text-rose-600 transition hover:bg-rose-50">
                    <i class="fa-solid fa-right-from-bracket w-4 text-center"></i>
                    {{ __('Log Out') }}
                </button>
            </div>
        </div>
    </aside>

    {{-- Desktop re-open button (visible when sidebar is closed) --}}
    <button
        x-cloak
        x-show="!$store.sidebar.open"
        @click="$store.sidebar.toggle()"
        class="fixed top-4 left-4 z-50 hidden lg:flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white shadow-md text-slate-700 hover:bg-slate-50 transition"
        title="Ouvrir le menu"
    >
        <i class="fa-solid fa-bars text-sm"></i>
    </button>

    <aside
        class="fixed inset-y-0 left-0 z-40 w-72 border-r border-slate-200 bg-white transition-transform duration-300 ease-in-out hidden lg:flex flex-col"
        :class="$store.sidebar.open ? 'translate-x-0' : '-translate-x-full'"
    >
        <div class="flex h-full flex-col">
            <div class="border-b border-slate-200 px-5 py-4">
                <div class="flex items-center justify-between">
                    <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center gap-3">
                        <x-application-logo class="h-10 w-10 fill-current text-emerald-700" />
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-emerald-700">ProStay Africa</p>
                            <p class="text-sm font-bold text-slate-900">{{ $workspaceLabel }}</p>
                        </div>
                    </a>
                    <button
                        @click="$store.sidebar.toggle()"
                        class="ml-2 flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition"
                        title="Fermer le menu"
                    >
                        <i class="fa-solid fa-chevron-left text-xs"></i>
                    </button>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto px-3 py-4">
                <div class="space-y-1">
                    <div class="mb-2 rounded-xl border border-slate-200 bg-slate-50 p-2">
                        <p class="mb-1 px-2 text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-500">Contexte</p>
                        <div class="grid grid-cols-3 gap-1">
                            <a href="{{ route('workspace.switch', 'all') }}" wire:navigate class="rounded-lg px-2 py-1.5 text-center text-[11px] font-semibold transition {{ $workspaceContext === 'all' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100' }}">Global</a>
                            <a href="{{ route('workspace.switch', 'hotel') }}" wire:navigate class="rounded-lg px-2 py-1.5 text-center text-[11px] font-semibold transition {{ $workspaceContext === 'hotel' ? 'bg-emerald-700 text-white' : 'text-slate-600 hover:bg-slate-100' }}">Hotel</a>
                            <a href="{{ route('workspace.switch', 'restaurant') }}" wire:navigate class="rounded-lg px-2 py-1.5 text-center text-[11px] font-semibold transition {{ $workspaceContext === 'restaurant' ? 'bg-amber-600 text-white' : 'text-slate-600 hover:bg-slate-100' }}">Restaurant</a>
                        </div>
                    </div>

                    @foreach($links as $link)
                        @php
                            $isActive = request()->routeIs($link['match']);
                            $isHighlighted = $link['highlight'] ?? false;
                        @endphp
                        <a
                            href="{{ route($link['route']) }}"
                            wire:navigate
                            class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition {{ $isActive ? 'bg-slate-900 text-white' : ($isHighlighted ? 'border border-amber-200 bg-amber-50 text-amber-800 hover:bg-amber-100' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900') }}"
                        >
                            <i class="fa-solid {{ $link['icon'] }} w-4 text-center"></i>
                            <span>{{ $link['label'] }}</span>
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="border-t border-slate-200 p-4">
                <div class="mb-3 rounded-xl bg-slate-50 px-3 py-2">
                    <p class="text-sm font-semibold text-slate-900" x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name" x-on:profile-updated.window="name = $event.detail.name"></p>
                    <p class="text-xs text-slate-500">{{ auth()->user()->email }}</p>
                </div>

                <div class="mb-3 rounded-full border border-slate-200 p-1 text-center text-xs shadow-sm">
                    <a href="{{ route('locale.switch', 'fr') }}" class="rounded-full px-3 py-1 font-semibold {{ app()->getLocale() === 'fr' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:text-slate-900' }}">FR</a>
                    <a href="{{ route('locale.switch', 'en') }}" class="rounded-full px-3 py-1 font-semibold {{ app()->getLocale() === 'en' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:text-slate-900' }}">EN</a>
                </div>

                <a href="{{ route('profile') }}" wire:navigate class="mb-2 flex w-full items-center gap-2 rounded-xl px-3 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-100 hover:text-slate-900">
                    <i class="fa-regular fa-user w-4 text-center"></i>
                    {{ __('Profile') }}
                </a>

                <button wire:click="logout" class="flex w-full items-center gap-2 rounded-xl px-3 py-2 text-sm font-semibold text-rose-600 transition hover:bg-rose-50">
                    <i class="fa-solid fa-right-from-bracket w-4 text-center"></i>
                    {{ __('Log Out') }}
                </button>
            </div>
        </div>
    </aside>
</nav>
