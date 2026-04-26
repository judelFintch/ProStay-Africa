<div class="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:px-8">
    <div class="grid gap-4 xl:grid-cols-[minmax(0,1.75fr)_360px]">
        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white">
            <div class="border-b border-slate-200 px-4 py-3">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-500">Orders</p>
                        <h1 class="mt-1 text-xl font-bold text-slate-900">Prise de commande</h1>
                        <p class="mt-1 text-sm text-slate-500">Commande pour client loge ou client externe avec controle stock actif.</p>
                    </div>

                    <div class="grid gap-2 sm:grid-cols-3">
                        <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
                            <p class="text-[11px] uppercase tracking-wide text-slate-500">Lignes</p>
                            <p class="mt-1 text-sm font-semibold text-slate-900">{{ count($items) }}</p>
                        </div>
                        <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
                            <p class="text-[11px] uppercase tracking-wide text-slate-500">Quantite</p>
                            <p class="mt-1 text-sm font-semibold text-slate-900">{{ number_format($totalQuantity, 2, '.', ' ') }}</p>
                        </div>
                        <div class="rounded-lg border {{ $stockIssueCount > 0 ? 'border-rose-200 bg-rose-50' : 'border-emerald-200 bg-emerald-50' }} px-3 py-2">
                            <p class="text-[11px] uppercase tracking-wide {{ $stockIssueCount > 0 ? 'text-rose-600' : 'text-emerald-700' }}">Stock</p>
                            <p class="mt-1 text-sm font-semibold {{ $stockIssueCount > 0 ? 'text-rose-700' : 'text-emerald-700' }}">
                                {{ $stockIssueCount > 0 ? $stockIssueCount . ' alerte(s)' : 'Valide' }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="mt-3 flex flex-wrap items-center gap-2">
                    <a
                        href="{{ route('billing.invoices') }}"
                        wire:navigate
                        class="inline-flex items-center gap-2 rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-2 text-sm font-semibold text-indigo-700 transition hover:bg-indigo-100"
                    >
                        <i class="fa-solid fa-list-check"></i>
                        Suivi commandes livrees
                        <span class="rounded-full bg-white px-2 py-0.5 text-xs font-bold text-indigo-700">{{ $pendingToInvoiceCount }}</span>
                    </a>
                    <p class="text-xs text-slate-500">Valider ou ajouter d autres commandes sur une facture existante.</p>
                </div>

                @if($appendTargetOrder)
                    <div class="mt-3 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2.5">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <p class="text-sm font-semibold text-amber-800">
                                Mode ajout actif: {{ $appendTargetOrder->reference }}
                                ({{ $appendTargetOrder->items_count }} article(s))
                            </p>
                            <button
                                type="button"
                                wire:click="stopAppend"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-amber-300 bg-white px-3 py-1.5 text-xs font-semibold text-amber-800 transition hover:bg-amber-100"
                            >
                                <i class="fa-solid fa-xmark"></i>
                                Quitter le mode ajout
                            </button>
                        </div>
                        <p class="mt-1 text-xs text-amber-700">Le contexte client/chambre est verrouille pour ajouter seulement de nouveaux articles/produits.</p>
                    </div>
                @endif
            </div>

            <form wire:submit="save" class="space-y-4 p-4">
                <section class="rounded-lg border border-slate-200">
                    <div class="border-b border-slate-200 bg-slate-50 px-4 py-2.5">
                        <p class="text-sm font-semibold text-slate-900">Contexte client</p>
                    </div>

                    <div class="space-y-4 p-4">
                        <div class="grid gap-2 sm:grid-cols-2">
                            <button
                                type="button"
                                wire:click="$set('order_mode', 'lodged')"
                                class="rounded-lg border px-3 py-2.5 text-left text-sm font-semibold transition {{ $order_mode === 'lodged' ? 'border-emerald-700 bg-emerald-700 text-white' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50' }}"
                            >
                                Client loge
                            </button>
                            <button
                                type="button"
                                wire:click="$set('order_mode', 'external')"
                                class="rounded-lg border px-3 py-2.5 text-left text-sm font-semibold transition {{ $order_mode === 'external' ? 'border-blue-700 bg-blue-700 text-white' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50' }}"
                            >
                                Client externe
                            </button>
                        </div>

                        <div class="grid gap-3 lg:grid-cols-12">
                            <div class="lg:col-span-4">
                                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Zone de service</label>
                                <select wire:model.live="service_area_id" class="prostay-input">
                                    <option value="">Selectionner</option>
                                    @foreach($serviceAreas as $area)
                                        <option value="{{ $area->id }}">{{ $area->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            @if($order_mode === 'lodged')
                                <div class="lg:col-span-4">
                                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Client</label>
                                    <select wire:model.live="customer_id" class="prostay-input">
                                        <option value="">Selectionner un client</option>
                                        @foreach($lodgedCustomers as $customer)
                                            <option value="{{ $customer->id }}">{{ $customer->full_name ?? 'Client sans nom' }}</option>
                                        @endforeach
                                    </select>
                                    @error('customer_id') <p class="mt-1 text-xs font-semibold text-rose-700">{{ $message }}</p> @enderror
                                </div>

                                <div class="lg:col-span-4">
                                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Sejour actif</label>
                                    <div class="prostay-input bg-slate-50">
                                        {{ $stay_id ? ('Sejour #' . $stay_id . ' detecte automatiquement') : 'Aucun sejour actif detecte' }}
                                    </div>
                                    @error('stay_id') <p class="mt-1 text-xs font-semibold text-rose-700">{{ $message }}</p> @enderror
                                </div>
                            @else
                                <div class="lg:col-span-8">
                                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Repere client externe</label>
                                    <input
                                        type="text"
                                        wire:model.blur="external_customer_name"
                                        class="prostay-input"
                                        placeholder="Ex: Casquette rouge, Table terrasse 4, M. Koffi"
                                        @disabled($appendTargetOrder)
                                    />
                                    @error('external_customer_name') <p class="mt-1 text-xs font-semibold text-rose-700">{{ $message }}</p> @enderror
                                </div>
                            @endif

                            <div class="lg:col-span-4">
                                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Type client</label>
                                <div class="prostay-input bg-slate-50">{{ $customer_type }}</div>
                            </div>

                            <div class="lg:col-span-4">
                                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Chambre</label>
                                <div class="prostay-input bg-slate-50">
                                    @if($order_mode === 'lodged')
                                        {{ $room_id ? ('Ch. ' . optional($activeStays->firstWhere('room_id', $room_id)?->room)->number) : 'Non rattachee' }}
                                    @else
                                        Non applicable
                                    @endif
                                </div>
                            </div>

                            <div class="lg:col-span-4">
                                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Statut</label>
                                <select wire:model="order_status" class="prostay-input">
                                    <option value="draft">Brouillon</option>
                                    <option value="confirmed">Confirmee</option>
                                    <option value="served">Servie</option>
                                    <option value="closed">Cloturee</option>
                                </select>
                                @error('order_status') <p class="mt-1 text-xs font-semibold text-rose-700">{{ $message }}</p> @enderror
                            </div>

                            <div class="lg:col-span-4">
                                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Serveur</label>
                                <select wire:model.live="served_by" class="prostay-input">
                                    <option value="">Selectionner le serveur</option>
                                    @foreach($servers as $server)
                                        <option value="{{ $server->id }}">
                                            {{ $server->server_alias ? ($server->server_alias . ' - ') : '' }}{{ $server->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('served_by') <p class="mt-1 text-xs font-semibold text-rose-700">{{ $message }}</p> @enderror
                            </div>

                            <div class="lg:col-span-4">
                                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Devise</label>
                                <select wire:model.live="currency" class="prostay-input" @disabled($appendTargetOrder)>
                                    @foreach($supportedCurrencies as $supportedCurrency)
                                        <option value="{{ $supportedCurrency }}">{{ $supportedCurrency }}</option>
                                    @endforeach
                                </select>
                                @error('currency') <p class="mt-1 text-xs font-semibold text-rose-700">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                </section>

                <section class="rounded-lg border border-slate-200">
                    <div class="flex flex-col gap-3 border-b border-slate-200 bg-slate-50 px-4 py-2.5 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm font-semibold text-slate-900">Catalogue et panier</p>
                            <p class="text-xs text-slate-500">Choisis les plats, produits ou articles libres, puis ajuste le panier.</p>
                        </div>

                        <button type="button" wire:click="clearOrder" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                            <i class="fa-solid fa-broom"></i>
                            Reinitialiser
                        </button>
                    </div>

                    @error('items')
                        <div class="border-b border-rose-200 bg-rose-50 px-4 py-2.5 text-sm font-semibold text-rose-700">{{ $message }}</div>
                    @enderror

                    <div class="grid gap-4 p-4 lg:grid-cols-[minmax(0,1fr)_360px]">
                        <div class="space-y-3">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div class="inline-flex rounded-lg border border-slate-200 bg-white p-1">
                                    <button type="button" wire:click="$set('catalog_tab', 'dishes')" class="rounded-md px-3 py-1.5 text-sm font-semibold {{ $catalog_tab === 'dishes' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-50' }}">Plats</button>
                                    <button type="button" wire:click="$set('catalog_tab', 'products')" class="rounded-md px-3 py-1.5 text-sm font-semibold {{ $catalog_tab === 'products' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-50' }}">Stock</button>
                                    <button type="button" wire:click="$set('catalog_tab', 'free')" class="rounded-md px-3 py-1.5 text-sm font-semibold {{ $catalog_tab === 'free' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-50' }}">Libre</button>
                                </div>

                                @if($catalog_tab !== 'free')
                                    <input type="search" wire:model.live.debounce.300ms="catalog_search" class="prostay-input sm:max-w-xs" placeholder="Rechercher..." />
                                @endif
                            </div>

                            @if($catalog_tab === 'dishes')
                                <div class="grid gap-3 md:grid-cols-2">
                                    @forelse($catalogMenus as $menu)
                                        <button type="button" wire:click="addMenuToCart({{ $menu->id }})" class="rounded-lg border border-slate-200 bg-white p-3 text-left transition hover:border-slate-400 hover:bg-slate-50">
                                            <div class="flex items-start justify-between gap-3">
                                                <div>
                                                    <p class="font-semibold text-slate-900">{{ $menu->name }}</p>
                                                    <p class="mt-1 text-xs text-slate-500">{{ $menu->category?->name ?? 'Plat restaurant' }}</p>
                                                </div>
                                                <p class="text-sm font-bold text-slate-900">{{ number_format((float) $menu->price, 2, '.', ' ') }} {{ strtoupper($currency) }}</p>
                                            </div>
                                            <div class="mt-3 flex items-center justify-between gap-2">
                                                <span class="rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $menu->catalog_available ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                                    {{ $menu->catalog_available ? 'Disponible' : 'Indisponible' }}
                                                </span>
                                                <span class="text-xs text-slate-500">{{ $menu->catalog_max_servings !== null ? $menu->catalog_max_servings . ' portion(s)' : 'Recette non limitee' }}</span>
                                            </div>
                                        </button>
                                    @empty
                                        <div class="rounded-lg border border-dashed border-slate-300 px-4 py-10 text-center text-sm text-slate-500 md:col-span-2">Aucun plat trouve.</div>
                                    @endforelse
                                </div>
                            @elseif($catalog_tab === 'products')
                                <div class="grid gap-3 md:grid-cols-2">
                                    @forelse($catalogProducts as $product)
                                        <button type="button" wire:click="addProductToCart({{ $product->id }})" class="rounded-lg border border-slate-200 bg-white p-3 text-left transition hover:border-slate-400 hover:bg-slate-50">
                                            <div class="flex items-start justify-between gap-3">
                                                <div>
                                                    <p class="font-semibold text-slate-900">{{ $product->name }}</p>
                                                    <p class="mt-1 text-xs text-slate-500">{{ $product->category?->name ?? 'Stock global' }}</p>
                                                </div>
                                                <p class="text-sm font-bold text-slate-900">{{ number_format((float) $product->selling_price, 2, '.', ' ') }} {{ strtoupper($currency) }}</p>
                                            </div>
                                            <div class="mt-3 flex items-center justify-between gap-2 text-xs">
                                                <span class="font-semibold {{ (float) $product->stock_quantity <= 0 ? 'text-rose-700' : 'text-slate-600' }}">
                                                    Stock: {{ number_format((float) $product->stock_quantity, 2, '.', ' ') }} {{ $product->unit }}
                                                </span>
                                                <span class="text-slate-400">{{ $product->sku }}</span>
                                            </div>
                                        </button>
                                    @empty
                                        <div class="rounded-lg border border-dashed border-slate-300 px-4 py-10 text-center text-sm text-slate-500 md:col-span-2">Aucun produit trouve.</div>
                                    @endforelse
                                </div>
                            @else
                                <div class="rounded-lg border border-slate-200 bg-white p-4">
                                    <div class="grid gap-3 md:grid-cols-[minmax(0,1fr)_150px_auto] md:items-end">
                                        <div>
                                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Designation</label>
                                            <input type="text" wire:model.blur="free_item_name" class="prostay-input" placeholder="Ex: supplement cuisine" />
                                            @error('free_item_name') <p class="mt-1 text-xs font-semibold text-rose-700">{{ $message }}</p> @enderror
                                        </div>
                                        <div>
                                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Prix</label>
                                            <input type="number" min="0" step="0.01" wire:model.blur="free_item_price" class="prostay-input" />
                                        </div>
                                        <button type="button" wire:click="addFreeItemToCart" class="inline-flex items-center justify-center gap-2 rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-700">
                                            <i class="fa-solid fa-plus"></i>
                                            Ajouter
                                        </button>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="rounded-lg border border-slate-200 bg-white">
                            <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
                                <p class="text-sm font-semibold text-slate-900">Panier</p>
                                <p class="text-sm font-bold text-slate-900">{{ number_format($estimatedTotal, 2, '.', ' ') }} {{ strtoupper($currency) }}</p>
                            </div>

                            <div class="divide-y divide-slate-100">
                                @forelse($items as $index => $item)
                                    @php
                                        $row = $rowSummaries[$index] ?? null;
                                        $typeLabel = ($item['item_type'] ?? '') === 'menu_service' ? 'Plat' : (($item['item_type'] ?? '') === 'stocked_product' ? 'Stock' : 'Libre');
                                    @endphp

                                    <div class="p-3">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="min-w-0">
                                                <p class="truncate text-sm font-semibold text-slate-900">{{ ($item['item_name'] ?? '') ?: 'Article sans nom' }}</p>
                                                <p class="mt-1 text-xs text-slate-500">{{ $typeLabel }}</p>
                                            </div>
                                            <button type="button" wire:click="removeItemRow({{ $index }})" class="rounded-lg border border-rose-200 bg-rose-50 px-2.5 py-1.5 text-xs font-semibold text-rose-700 transition hover:bg-rose-100">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </div>

                                        <div class="mt-3 grid grid-cols-[104px_minmax(0,1fr)] gap-2">
                                            <div class="inline-flex h-10 items-center rounded-lg border border-slate-200">
                                                <button type="button" wire:click="decrementItem({{ $index }})" class="h-full px-3 text-slate-600 hover:bg-slate-50"><i class="fa-solid fa-minus"></i></button>
                                                <input type="number" min="0.01" step="0.01" wire:model.live.debounce.400ms="items.{{ $index }}.quantity" class="h-full w-12 border-x border-slate-200 text-center text-sm font-semibold focus:outline-none" />
                                                <button type="button" wire:click="incrementItem({{ $index }})" class="h-full px-3 text-slate-600 hover:bg-slate-50"><i class="fa-solid fa-plus"></i></button>
                                            </div>

                                            <input type="number" min="0" step="0.01" wire:model.live.debounce.400ms="items.{{ $index }}.unit_price" class="prostay-input" />
                                        </div>

                                        <div class="mt-2 flex items-center justify-between gap-2 text-xs">
                                            @if(($item['item_type'] ?? '') === 'menu_service')
                                                <span class="font-semibold {{ ($item['menu_available'] ?? true) ? 'text-emerald-700' : 'text-rose-700' }}">
                                                    {{ ($item['menu_available'] ?? null) === false ? 'Plat indisponible' : 'Plat disponible' }}
                                                </span>
                                            @elseif(($item['item_type'] ?? '') === 'stocked_product')
                                                <span class="font-semibold {{ $row && $row['is_stock_issue'] ? 'text-rose-700' : 'text-slate-500' }}">
                                                    Stock: {{ isset($item['stock_available']) ? number_format((float) $item['stock_available'], 2, '.', ' ') : '--' }} {{ $item['item_unit'] ?? '' }}
                                                </span>
                                            @else
                                                <span class="font-semibold text-slate-500">Article libre</span>
                                            @endif
                                            <span class="font-bold text-slate-900">{{ number_format((float) ($row['subtotal'] ?? 0), 2, '.', ' ') }} {{ strtoupper($currency) }}</span>
                                        </div>

                                        @if($row && $row['is_stock_issue'])
                                            <p class="mt-2 text-xs font-semibold text-rose-700">{{ ($item['item_type'] ?? '') === 'menu_service' ? 'Ingredients insuffisants pour ce plat.' : 'Quantite superieure au stock.' }}</p>
                                        @endif
                                        @error('items.' . $index . '.quantity') <p class="mt-2 text-xs font-semibold text-rose-700">{{ $message }}</p> @enderror
                                        @error('items.' . $index . '.unit_price') <p class="mt-2 text-xs font-semibold text-rose-700">{{ $message }}</p> @enderror
                                    </div>
                                @empty
                                    <div class="px-4 py-10 text-center text-sm text-slate-500">Le panier est vide.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </section>

                <section class="rounded-lg border border-slate-200">
                    <div class="border-b border-slate-200 bg-slate-50 px-4 py-2.5">
                        <p class="text-sm font-semibold text-slate-900">Validation</p>
                    </div>

                    <div class="space-y-4 p-4">
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Notes</label>
                            <textarea wire:model.blur="notes" rows="3" class="prostay-input" placeholder="Informations complementaires sur la commande"></textarea>
                        </div>

                        <div class="flex flex-wrap items-center gap-2 text-xs font-semibold">
                            @if($order_mode === 'lodged')
                                <span class="rounded-full bg-emerald-100 px-3 py-1 text-emerald-700">Client loge</span>
                            @else
                                <span class="rounded-full bg-blue-100 px-3 py-1 text-blue-700">Client externe</span>
                            @endif

                            @if($stockIssueCount > 0)
                                <span class="rounded-full bg-rose-100 px-3 py-1 text-rose-700">{{ $stockIssueCount }} ligne(s) en stock insuffisant</span>
                            @else
                                <span class="rounded-full bg-emerald-100 px-3 py-1 text-emerald-700">Stock valide</span>
                            @endif
                        </div>

                        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-200 pt-4">
                            <p class="text-sm text-slate-500">Blocage automatique si le stock est insuffisant.</p>

                            <div class="flex flex-wrap gap-2">
                                <a href="{{ route('billing.invoices') }}" wire:navigate class="inline-flex items-center gap-2 rounded-lg border border-indigo-200 bg-indigo-50 px-4 py-2.5 text-sm font-semibold text-indigo-700 transition hover:bg-indigo-100">
                                    <i class="fa-solid fa-list-check"></i>
                                    Suivre / Ajouter sur facture
                                </a>
                                <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-700">
                                    <i class="fa-solid fa-floppy-disk"></i>
                                    Enregistrer
                                </button>
                                <button type="button" wire:click="saveAndInvoice" class="inline-flex items-center gap-2 rounded-lg bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-600">
                                    <i class="fa-solid fa-file-invoice-dollar"></i>
                                    Enregistrer et facturer
                                </button>
                            </div>
                        </div>
                    </div>
                </section>
            </form>
        </section>

        <aside class="space-y-4 xl:sticky xl:top-20 xl:self-start">
            <section class="rounded-xl border border-slate-200 bg-white">
                <div class="border-b border-slate-200 px-4 py-2.5">
                    <p class="text-sm font-semibold text-slate-900">Resume commande</p>
                </div>

                <div class="space-y-3 p-4">
                    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-1">
                        <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5">
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Client</p>
                            <p class="mt-1 text-sm font-semibold text-slate-900">
                                @if($order_mode === 'external')
                                    {{ $external_customer_name ?: 'Repere externe non renseigne' }}
                                @else
                                    {{ $customer_id ? ($customers->firstWhere('id', $customer_id)?->full_name ?? 'Client') : 'Aucun client selectionne' }}
                                @endif
                            </p>
                        </div>

                        <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5">
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Chambre</p>
                            <p class="mt-1 text-sm font-semibold text-slate-900">{{ $room_id ? ('Chambre ' . (optional($activeStays->firstWhere('room_id', $room_id)?->room)->number ?? '-')) : 'Non rattachee' }}</p>
                        </div>

                        <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5">
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Serveur</p>
                            <p class="mt-1 text-sm font-semibold text-slate-900">{{ $served_by ? ($servers->firstWhere('id', $served_by)?->name ?? 'Serveur') : 'Non selectionne' }}</p>
                        </div>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-3 xl:grid-cols-1">
                        <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5">
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Articles</p>
                            <p class="mt-1 text-sm font-semibold text-slate-900">{{ number_format($totalQuantity, 2, '.', ' ') }}</p>
                        </div>

                        <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5">
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Statut</p>
                            <p class="mt-1 text-sm font-semibold text-slate-900">
                                {{ $order_status === 'draft' ? 'Brouillon' : ($order_status === 'confirmed' ? 'Confirmee' : ($order_status === 'served' ? 'Servie' : 'Cloturee')) }}
                            </p>
                        </div>

                        <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5">
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Total estime</p>
                            <p class="mt-1 text-lg font-bold text-slate-900">{{ number_format($estimatedTotal, 2, '.', ' ') }} {{ strtoupper($currency) }}</p>
                        </div>
                    </div>

                    <div class="rounded-lg border px-3 py-2.5 {{ $stockIssueCount > 0 ? 'border-rose-200 bg-rose-50' : 'border-emerald-200 bg-emerald-50' }}">
                        <p class="text-[11px] font-semibold uppercase tracking-wide {{ $stockIssueCount > 0 ? 'text-rose-600' : 'text-emerald-700' }}">Controle stock</p>
                        <p class="mt-1 text-sm font-semibold {{ $stockIssueCount > 0 ? 'text-rose-700' : 'text-emerald-700' }}">
                            {{ $stockIssueCount > 0 ? 'Action requise avant validation' : 'Commande validable' }}
                        </p>
                    </div>
                </div>
            </section>

            <section class="rounded-xl border border-slate-200 bg-white">
                <div class="border-b border-slate-200 px-4 py-2.5">
                    <p class="text-sm font-semibold text-slate-900">Commandes groupees par statut</p>
                </div>

                <div class="space-y-4 p-4">
                    @php
                        $statusLabels = [
                            'draft' => ['label' => 'Brouillon', 'class' => 'bg-slate-100 text-slate-700'],
                            'confirmed' => ['label' => 'Confirmees', 'class' => 'bg-blue-100 text-blue-700'],
                            'served' => ['label' => 'Servies', 'class' => 'bg-emerald-100 text-emerald-700'],
                            'closed' => ['label' => 'Cloturees', 'class' => 'bg-violet-100 text-violet-700'],
                            'cancelled' => ['label' => 'Annulees', 'class' => 'bg-rose-100 text-rose-700'],
                        ];
                    @endphp

                    @if($recentOrders->isEmpty())
                        <div class="px-3 py-8 text-center text-sm text-slate-500">Aucune commande recente.</div>
                    @else
                        @foreach($recentOrdersByStatus as $status => $ordersForStatus)
                            @continue($ordersForStatus->isEmpty())
                            <div class="rounded-lg border border-slate-200">
                                <div class="flex items-center justify-between border-b border-slate-200 bg-slate-50 px-3 py-2">
                                    <span class="rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $statusLabels[$status]['class'] ?? 'bg-slate-100 text-slate-700' }}">
                                        {{ $statusLabels[$status]['label'] ?? $status }}
                                    </span>
                                    <span class="text-xs font-semibold text-slate-500">{{ $ordersForStatus->count() }}</span>
                                </div>
                                <div class="divide-y divide-slate-100">
                                    @foreach($ordersForStatus->take(6) as $order)
                                        <div class="flex items-start justify-between gap-3 px-3 py-2.5 text-sm">
                                            <div>
                                                <p class="font-semibold text-slate-900">{{ $order->reference }}</p>
                                                <p class="text-xs text-slate-500">
                                                    {{ $order->customer?->full_name ?? ($order->external_label ?: ($order->room ? 'Ch. ' . $order->room->number : 'Passage')) }}
                                                </p>
                                                <p class="text-xs text-slate-400">
                                                    {{ $order->server?->name ?? 'Serveur non renseigne' }}
                                                </p>
                                            </div>
                                            <div class="text-right">
                                                <p class="font-semibold text-slate-700">{{ number_format($order->total, 2, '.', ' ') }} {{ strtoupper((string) $order->currency) }}</p>
                                                @if($status !== 'cancelled')
                                                    <button
                                                        type="button"
                                                        wire:click="startAppend({{ $order->id }})"
                                                        class="mt-1 inline-flex items-center gap-1 rounded-lg border border-blue-200 bg-blue-50 px-2 py-1 text-[11px] font-semibold text-blue-700 transition hover:bg-blue-100"
                                                    >
                                                        <i class="fa-solid fa-plus"></i>
                                                        Ajouter articles
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </section>
        </aside>
    </div>
</div>
