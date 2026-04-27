<div class="mx-auto max-w-7xl space-y-5 px-4 py-4 sm:px-6 lg:px-8" x-data="{ panel: 'product' }">
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 bg-[linear-gradient(135deg,#ffffff_0%,#f8fafc_55%,#ecfdf5_100%)] px-5 py-5 sm:px-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-3xl">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-emerald-700">Inventory Control</p>
                    <h1 class="mt-1 text-2xl font-black text-slate-900">Gestion de stock</h1>
                    <p class="mt-2 text-sm text-slate-600">
                        Gestion des articles, approvisionnements, affectations et alertes.
                        Les plats sont gérés à part via leurs recettes et ne sont pas approvisionnés ici.
                    </p>
                </div>

                <div class="grid gap-2 sm:grid-cols-2 xl:grid-cols-5">
                    <div class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 shadow-sm">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400">Articles</p>
                        <p class="mt-1 text-xl font-black text-slate-900">{{ number_format($stats['products']) }}</p>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 shadow-sm">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400">Catégories</p>
                        <p class="mt-1 text-xl font-black text-slate-900">{{ number_format($stats['categories']) }}</p>
                    </div>
                    <div class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2.5 shadow-sm">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-rose-700">Alertes</p>
                        <p class="mt-1 text-xl font-black text-rose-700">{{ number_format($stats['alerts']) }}</p>
                    </div>
                    <div class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-2.5 shadow-sm">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-amber-700">Périssables</p>
                        <p class="mt-1 text-xl font-black text-amber-700">{{ number_format($stats['fresh']) }}</p>
                    </div>
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2.5 shadow-sm">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-emerald-700">Valeur stock</p>
                        <p class="mt-1 text-lg font-black text-emerald-900">{{ $currencySymbol }} {{ number_format($stats['inventory_value'], 2, '.', ' ') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="rounded-2xl border border-amber-200 bg-[linear-gradient(135deg,#fffaf0_0%,#ffffff_55%,#f8fafc_100%)] p-4 shadow-sm sm:p-5">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div class="max-w-3xl">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-amber-700">Séparation Articles / Plats</p>
                <h2 class="mt-1 text-lg font-black text-slate-900">Les plats ne sont pas approvisionnés</h2>
                <p class="mt-2 text-sm text-slate-600">
                    Ici, vous gérez uniquement les <span class="font-semibold text-slate-900">articles de stock</span>.
                    Les <span class="font-semibold text-slate-900">plats</span> sont calculés à partir des recettes et consomment les articles lors des ventes.
                </p>
            </div>

            <a href="{{ route('dishes.index') }}" wire:navigate class="inline-flex items-center justify-center rounded-xl border border-slate-900 bg-slate-900 px-4 py-2 text-xs font-semibold text-white transition hover:bg-slate-800">
                Ouvrir les plats et recettes
            </a>
        </div>

        <div class="mt-4 grid gap-3 sm:grid-cols-3">
            <div class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 shadow-sm">
                <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-500">Plats</p>
                <p class="mt-1 text-xl font-black text-slate-900">{{ number_format($dishStats['total']) }}</p>
            </div>
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2.5 shadow-sm">
                <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-emerald-700">Plats disponibles</p>
                <p class="mt-1 text-xl font-black text-emerald-900">{{ number_format($dishStats['available']) }}</p>
            </div>
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2.5 shadow-sm">
                <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-rose-700">Plats en alerte recette</p>
                <p class="mt-1 text-xl font-black text-rose-900">{{ number_format($dishStats['unavailable']) }}</p>
            </div>
        </div>

        <div class="mt-4 overflow-x-auto rounded-2xl border border-slate-200 bg-white">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr class="text-left text-xs uppercase tracking-wide text-slate-500">
                        <th class="px-3 py-2.5">Plat</th>
                        <th class="px-3 py-2.5">Catégorie</th>
                        <th class="px-3 py-2.5">Service</th>
                        <th class="px-3 py-2.5">Disponibilité recette</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($dishPreview as $dish)
                        <tr class="{{ $dish['is_available'] ? '' : 'bg-rose-50/40' }}">
                            <td class="px-3 py-2.5 font-semibold text-slate-900">{{ $dish['name'] }}</td>
                            <td class="px-3 py-2.5 text-slate-700">{{ $dish['category'] ?? '-' }}</td>
                            <td class="px-3 py-2.5 text-slate-700">{{ $dish['service_area'] ?? '-' }}</td>
                            <td class="px-3 py-2.5">
                                @if($dish['is_available'])
                                    <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                                        Disponible{{ $dish['max_servings'] !== null ? ' · '.$dish['max_servings'].' portion(s)' : '' }}
                                    </span>
                                @else
                                    <span class="inline-flex rounded-full bg-rose-100 px-2.5 py-1 text-xs font-semibold text-rose-700">
                                        Indisponible
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-3 py-6 text-center text-slate-500">Aucun plat configuré.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <div class="grid gap-4 xl:grid-cols-[1.55fr_0.85fr]">
        <section class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
            <div class="border-b border-slate-200 px-4 py-3 sm:px-5">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Opérations</p>
                        <h2 class="mt-1 text-lg font-black text-slate-900">Saisie rapide</h2>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <button type="button" @click="panel = 'product'" class="rounded-lg px-3 py-1.5 text-sm font-semibold transition" :class="panel === 'product' ? 'bg-slate-900 text-white' : 'border border-slate-200 bg-white text-slate-700 hover:bg-slate-50'">Article</button>
                        <button type="button" @click="panel = 'movement'" class="rounded-lg px-3 py-1.5 text-sm font-semibold transition" :class="panel === 'movement' ? 'bg-slate-900 text-white' : 'border border-slate-200 bg-white text-slate-700 hover:bg-slate-50'">Mouvement</button>
                        <button type="button" @click="panel = 'category'" class="rounded-lg px-3 py-1.5 text-sm font-semibold transition" :class="panel === 'category' ? 'bg-slate-900 text-white' : 'border border-slate-200 bg-white text-slate-700 hover:bg-slate-50'">Catégorie</button>
                    </div>
                </div>
            </div>

            <div class="p-4 sm:p-5">
                <form x-show="panel === 'product'" wire:submit="saveProduct" class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Catégorie</label>
                        <select wire:model="product_category_id" class="prostay-input">
                            <option value="">Sélectionner</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Fournisseur</label>
                        <select wire:model="supplier_id" class="prostay-input">
                            <option value="">Sélectionner</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Service / département</label>
                        <select wire:model="product_service_area_id" class="prostay-input">
                            <option value="">Aucun</option>
                            @foreach($serviceAreas as $serviceArea)
                                <option value="{{ $serviceArea->id }}">{{ $serviceArea->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-2 xl:col-span-2">
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Nom article</label>
                        <input type="text" wire:model="product_name" class="prostay-input" placeholder="Ex: Poulet frais" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Code article</label>
                        <input type="text" wire:model="sku" class="prostay-input" placeholder="Auto si vide" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Unité stock</label>
                        <select wire:model="unit" class="prostay-input">
                            @foreach($referenceUnits as $referenceUnit)
                                <option value="{{ $referenceUnit }}">{{ $referenceUnit }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Unité achat</label>
                        <select wire:model="purchase_unit" class="prostay-input">
                            <option value="">Sélectionner</option>
                            @foreach($referencePurchaseUnits as $referencePurchaseUnit)
                                <option value="{{ $referencePurchaseUnit }}">{{ $referencePurchaseUnit }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Zone stockage</label>
                        <select wire:model="storage_area" class="prostay-input">
                            <option value="">Sélectionner</option>
                            @foreach($referenceStorageAreas as $referenceStorageArea)
                                <option value="{{ $referenceStorageArea }}">{{ $referenceStorageArea }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Coût unitaire ({{ $currency }})</label>
                        <input type="number" wire:model="product_unit_cost" step="0.01" min="0" class="prostay-input" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Prix vente ({{ $currency }})</label>
                        <input type="number" wire:model="selling_price" step="0.01" min="0" class="prostay-input" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Stock initial</label>
                        <input type="number" wire:model="opening_stock" step="0.01" min="0" class="prostay-input" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Seuil alerte</label>
                        <input type="number" wire:model="alert_threshold_value" step="0.01" min="0" class="prostay-input" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Expiration</label>
                        <input type="date" wire:model="expires_at" class="prostay-input" />
                    </div>
                    <div class="flex items-end">
                        <label class="inline-flex w-full items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-medium text-slate-700">
                            <input type="checkbox" wire:model="is_perishable" class="rounded border-slate-300 text-emerald-600 shadow-sm focus:ring-emerald-500">
                            Produit périssable
                        </label>
                    </div>
                    <div class="flex items-end">
                        <label class="inline-flex w-full items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-medium text-slate-700">
                            <input type="checkbox" wire:model="is_active" class="rounded border-slate-300 text-emerald-600 shadow-sm focus:ring-emerald-500">
                            Article actif
                        </label>
                    </div>
                    <div class="flex items-end">
                        <button class="w-full rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800">
                            Enregistrer l'article
                        </button>
                    </div>
                </form>

                <form x-show="panel === 'movement'" wire:submit="saveMovement" class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Devise</label>
                        <select wire:model="currency" class="prostay-input">
                            <option value="USD">USD</option>
                            <option value="CDF">CDF</option>
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Article</label>
                        <select wire:model="product_id" class="prostay-input">
                            <option value="">Sélectionner un article</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }} ({{ number_format($product->stock_quantity, 2, '.', ' ') }} {{ $product->unit }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Service concerné</label>
                        <select wire:model="movement_service_area_id" class="prostay-input">
                            <option value="">Aucun</option>
                            @foreach($serviceAreas as $serviceArea)
                                <option value="{{ $serviceArea->id }}">{{ $serviceArea->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Type</label>
                        <select wire:model="movement_type" class="prostay-input">
                            <option value="in">Entrée</option>
                            <option value="out">Sortie</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Quantité</label>
                        <input type="number" wire:model="quantity" step="0.01" min="0.01" class="prostay-input" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Coût unitaire ({{ $currency }})</label>
                        <input type="number" wire:model="unit_cost" step="0.01" min="0" class="prostay-input" />
                    </div>
                    <div class="sm:col-span-2 xl:col-span-2">
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Motif</label>
                        <select wire:model="reason" class="prostay-input">
                            <option value="">Sélectionner</option>
                            @foreach($referenceMovementReasons as $referenceMovementReason)
                                <option value="{{ $referenceMovementReason }}">{{ $referenceMovementReason }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end xl:col-span-1">
                        <button class="w-full rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800">
                            Enregistrer le mouvement
                        </button>
                    </div>
                </form>

                <form x-show="panel === 'category'" wire:submit="saveCategory" class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Nom catégorie</label>
                        <input type="text" wire:model="category_name" class="prostay-input" placeholder="Ex: Vivres frais" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Code</label>
                        <input type="text" wire:model="category_code" class="prostay-input" placeholder="fresh-food" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Couleur</label>
                        <select wire:model="category_color" class="prostay-input">
                            <option value="emerald">Emerald</option>
                            <option value="amber">Amber</option>
                            <option value="sky">Sky</option>
                            <option value="orange">Orange</option>
                            <option value="slate">Slate</option>
                        </select>
                    </div>
                    <div class="sm:col-span-2 xl:col-span-3">
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Description</label>
                        <input type="text" wire:model="category_description" class="prostay-input" placeholder="Catégorie des produits frais de cuisine" />
                    </div>
                    <div class="flex items-end">
                        <div class="flex w-full flex-col gap-2">
                            <label class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-medium text-slate-700">
                                <input type="checkbox" wire:model="category_is_perishable" class="rounded border-slate-300 text-emerald-600 shadow-sm focus:ring-emerald-500">
                                Périssable
                            </label>
                            <label class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-medium text-slate-700">
                                <input type="checkbox" wire:model="category_is_active" class="rounded border-slate-300 text-emerald-600 shadow-sm focus:ring-emerald-500">
                                Active
                            </label>
                        </div>
                    </div>
                    <div class="xl:col-span-4">
                        <button class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800">
                            Enregistrer la catégorie
                        </button>
                    </div>
                </form>
            </div>
        </section>

        <aside class="space-y-4">
            <section class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Alertes</p>
                        <h3 class="mt-1 text-base font-black text-slate-900">Stocks critiques</h3>
                    </div>
                    <span class="rounded-full bg-rose-100 px-3 py-1 text-xs font-semibold text-rose-700">{{ count($alerts) }}</span>
                </div>

                <div class="mt-3 space-y-2.5">
                    @forelse($alerts as $alert)
                        <div class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2.5">
                            <p class="text-sm font-semibold text-rose-900">{{ $alert->name }}</p>
                            <p class="mt-1 text-xs text-rose-700">
                                {{ number_format($alert->stock_quantity, 2, '.', ' ') }} {{ $alert->unit }} restants
                                @if($alert->category)
                                    · {{ $alert->category->name }}
                                @endif
                            </p>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">Aucune alerte critique actuellement.</p>
                    @endforelse
                </div>
            </section>

            <section class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Mouvements</p>
                <h3 class="mt-1 text-base font-black text-slate-900">Historique récent</h3>

                <div class="mt-3 space-y-2.5">
                    @foreach($recentMovements as $movement)
                        <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5">
                            <p class="text-sm font-semibold text-slate-900">{{ $movement->product?->name ?? '-' }}</p>
                            <p class="mt-1 text-xs text-slate-600">
                                {{ $movement->movement_type === 'in' ? 'Entrée' : 'Sortie' }}
                                {{ number_format($movement->quantity, 2, '.', ' ') }}
                                @if($movement->product?->unit)
                                    {{ $movement->product->unit }}
                                @endif
                                · {{ $movement->created_at->format('d/m H:i') }}
                            </p>
                            @if($movement->reason)
                                <p class="mt-1 text-xs text-slate-500">{{ $movement->reason }}</p>
                            @endif
                            @if($movement->serviceArea)
                                <p class="mt-1 text-xs font-semibold text-slate-600">Service: {{ $movement->serviceArea->name }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </section>
        </aside>
    </div>

    <section class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
        <div class="border-b border-slate-200 px-4 py-4 sm:px-5">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Catalogue</p>
                    <h2 class="mt-1 text-lg font-black text-slate-900">Registre des articles</h2>
                    <p class="mt-1 text-sm text-slate-500">Recherche, filtrage et lecture rapide des informations importantes.</p>
                </div>
                <div class="grid gap-2 sm:grid-cols-4">
                    <input type="text" wire:model.live.debounce.300ms="search" class="prostay-input" placeholder="Article, code, stockage..." />
                    <select wire:model.live="categoryFilter" class="prostay-input">
                        <option value="">Toutes catégories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->code }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                    <select wire:model.live="productServiceFilter" class="prostay-input">
                        <option value="">Tous services</option>
                        @foreach($serviceAreas as $serviceArea)
                            <option value="{{ $serviceArea->id }}">{{ $serviceArea->name }}</option>
                        @endforeach
                    </select>
                    <select wire:model.live="stockFilter" class="prostay-input">
                        <option value="all">Tous</option>
                        <option value="alerts">En alerte</option>
                        <option value="perishable">Périssables</option>
                        <option value="fresh">Vivres frais</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="px-4 py-4 sm:px-5">
            <div class="overflow-x-auto rounded-2xl border border-slate-200">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr class="text-left text-xs uppercase tracking-wide text-slate-500">
                            <th class="px-3 py-2.5">Article</th>
                            <th class="px-3 py-2.5">Catégorie</th>
                            <th class="px-3 py-2.5">Service</th>
                            <th class="px-3 py-2.5">Stock</th>
                            <th class="px-3 py-2.5">Seuil</th>
                            <th class="px-3 py-2.5">Stockage</th>
                            <th class="px-3 py-2.5">État</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($products as $product)
                            <tr>
                                <td class="px-3 py-3">
                                    <p class="font-semibold text-slate-900">{{ $product->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $product->sku ?: '-' }}</p>
                                </td>
                                <td class="px-3 py-3 text-slate-700">{{ $product->category?->name ?? '-' }}</td>
                                <td class="px-3 py-3 text-slate-700">{{ $product->serviceArea?->name ?? '-' }}</td>
                                <td class="px-3 py-3 text-slate-700">{{ number_format($product->stock_quantity, 2, '.', ' ') }} {{ $product->unit }}</td>
                                <td class="px-3 py-3 text-slate-700">{{ number_format($product->alert_threshold, 2, '.', ' ') }}</td>
                                <td class="px-3 py-3 text-slate-700">{{ $product->storage_area ?: '-' }}</td>
                                <td class="px-3 py-3">
                                    <div class="flex flex-wrap gap-2">
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $product->stock_quantity <= $product->alert_threshold ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700' }}">
                                            {{ $product->stock_quantity <= $product->alert_threshold ? 'Alerte' : 'Correct' }}
                                        </span>
                                        @if($product->is_perishable)
                                            <span class="inline-flex rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-700">Périssable</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-3 py-8 text-center text-slate-500">Aucun article ne correspond aux filtres actuels.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <section class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
        <div class="border-b border-slate-200 px-4 py-4 sm:px-5">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Traçabilité</p>
                    <h2 class="mt-1 text-lg font-black text-slate-900">Mouvements complets de stock</h2>
                    <p class="mt-1 text-sm text-slate-500">Historique détaillé des entrées/sorties avec export Excel et PDF.</p>
                </div>
                <div class="grid gap-2 sm:grid-cols-2 xl:grid-cols-7">
                    <input type="text" wire:model.live.debounce.300ms="movementSearch" class="prostay-input" placeholder="Article, code, motif, utilisateur..." />
                    <select wire:model.live="movementTypeFilter" class="prostay-input">
                        <option value="all">Tous les types</option>
                        <option value="in">Entrées</option>
                        <option value="out">Sorties</option>
                    </select>
                    <select wire:model.live="movementServiceFilter" class="prostay-input">
                        <option value="">Tous services</option>
                        @foreach($serviceAreas as $serviceArea)
                            <option value="{{ $serviceArea->id }}">{{ $serviceArea->name }}</option>
                        @endforeach
                    </select>
                    <input type="date" wire:model.live="movementStartDate" class="prostay-input" />
                    <input type="date" wire:model.live="movementEndDate" class="prostay-input" />
                    <select wire:model.live="movementPerPage" class="prostay-input">
                        <option value="10">10 / page</option>
                        <option value="25">25 / page</option>
                        <option value="50">50 / page</option>
                        <option value="100">100 / page</option>
                    </select>
                    <button type="button" wire:click="resetMovementFilters" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-100">Réinitialiser</button>
                    <button type="button" wire:click="exportMovementsExcel" class="rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-800 transition hover:bg-emerald-100">Export Excel</button>
                    <button type="button" wire:click="exportMovementsCsv" class="rounded-xl border border-sky-200 bg-sky-50 px-3 py-2 text-xs font-semibold text-sky-800 transition hover:bg-sky-100">Export CSV</button>
                    <button type="button" wire:click="exportMovementsPdf" class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-800 transition hover:bg-rose-100">Export PDF</button>
                </div>
            </div>
        </div>

        <div class="px-4 py-4 sm:px-5">
            <div class="grid gap-2 sm:grid-cols-3">
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2.5 shadow-sm">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.15em] text-emerald-700">Entrées</p>
                    <p class="mt-1 text-sm font-bold text-emerald-900">{{ number_format((float) $movementTotals['in_quantity'], 2, '.', ' ') }} · {{ $currencySymbol }} {{ number_format((float) $movementTotals['in_amount'], 2, '.', ' ') }}</p>
                </div>
                <div class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-2.5 shadow-sm">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.15em] text-amber-700">Sorties</p>
                    <p class="mt-1 text-sm font-bold text-amber-900">{{ number_format((float) $movementTotals['out_quantity'], 2, '.', ' ') }} · {{ $currencySymbol }} {{ number_format((float) $movementTotals['out_amount'], 2, '.', ' ') }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 shadow-sm">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.15em] text-slate-600">Net valorisé</p>
                    <p class="mt-1 text-sm font-bold text-slate-900">{{ $currencySymbol }} {{ number_format((float) $movementTotals['net_amount'], 2, '.', ' ') }}</p>
                </div>
            </div>

            <div class="mt-4 overflow-x-auto rounded-2xl border border-slate-200">
                <p class="px-3 pt-3 text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Consommation par service</p>
                <table class="min-w-full divide-y divide-slate-200 text-xs">
                    <thead class="bg-slate-50">
                        <tr class="text-left uppercase tracking-wide text-slate-500">
                            <th class="px-3 py-2">Service</th>
                            <th class="px-3 py-2 text-right">Entrées (Qté)</th>
                            <th class="px-3 py-2 text-right">Sorties (Qté)</th>
                            <th class="px-3 py-2 text-right">Entrées (Montant)</th>
                            <th class="px-3 py-2 text-right">Sorties (Montant)</th>
                            <th class="px-3 py-2 text-right">Net</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($serviceConsumption as $serviceRow)
                            <tr>
                                <td class="px-3 py-2 text-slate-700">{{ $serviceRow['service_name'] }}</td>
                                <td class="px-3 py-2 text-right text-slate-700">{{ number_format((float) $serviceRow['in_quantity'], 2, '.', ' ') }}</td>
                                <td class="px-3 py-2 text-right text-slate-700">{{ number_format((float) $serviceRow['out_quantity'], 2, '.', ' ') }}</td>
                                <td class="px-3 py-2 text-right text-slate-700">{{ $currencySymbol }} {{ number_format((float) $serviceRow['in_amount'], 2, '.', ' ') }}</td>
                                <td class="px-3 py-2 text-right text-slate-700">{{ $currencySymbol }} {{ number_format((float) $serviceRow['out_amount'], 2, '.', ' ') }}</td>
                                <td class="px-3 py-2 text-right font-semibold text-slate-900">{{ $currencySymbol }} {{ number_format((float) $serviceRow['net_amount'], 2, '.', ' ') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-3 py-6 text-center text-slate-500">Aucune consommation par service disponible.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="px-4 pb-5 sm:px-5">
            <div class="overflow-x-auto rounded-2xl border border-slate-200">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr class="text-left text-xs uppercase tracking-wide text-slate-500">
                            <th class="px-3 py-2.5">
                                <button type="button" wire:click="setMovementSort('created_at')" class="inline-flex items-center gap-1 font-semibold text-slate-600 transition hover:text-slate-900">
                                    Date
                                    @if($movementSortField === 'created_at')
                                        <span>{{ $movementSortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    @endif
                                </button>
                            </th>
                            <th class="px-3 py-2.5">Article</th>
                            <th class="px-3 py-2.5">Service</th>
                            <th class="px-3 py-2.5">
                                <button type="button" wire:click="setMovementSort('movement_type')" class="inline-flex items-center gap-1 font-semibold text-slate-600 transition hover:text-slate-900">
                                    Type
                                    @if($movementSortField === 'movement_type')
                                        <span>{{ $movementSortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    @endif
                                </button>
                            </th>
                            <th class="px-3 py-2.5 text-right">
                                <button type="button" wire:click="setMovementSort('quantity')" class="inline-flex items-center gap-1 font-semibold text-slate-600 transition hover:text-slate-900">
                                    Quantité
                                    @if($movementSortField === 'quantity')
                                        <span>{{ $movementSortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    @endif
                                </button>
                            </th>
                            <th class="px-3 py-2.5 text-right">
                                <button type="button" wire:click="setMovementSort('unit_cost')" class="inline-flex items-center gap-1 font-semibold text-slate-600 transition hover:text-slate-900">
                                    Coût unitaire
                                    @if($movementSortField === 'unit_cost')
                                        <span>{{ $movementSortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    @endif
                                </button>
                            </th>
                            <th class="px-3 py-2.5 text-right">Montant ({{ $currency }})</th>
                            <th class="px-3 py-2.5">Motif</th>
                            <th class="px-3 py-2.5">Utilisateur</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($movements as $movement)
                            @php($amount = (float) $movement->quantity * (float) $movement->unit_cost)
                            <tr>
                                <td class="px-3 py-2.5 text-slate-700">{{ $movement->created_at?->format('d/m/Y H:i') }}</td>
                                <td class="px-3 py-2.5">
                                    <p class="font-semibold text-slate-900">{{ $movement->product?->name ?? '-' }}</p>
                                    <p class="text-xs text-slate-500">{{ $movement->product?->sku ?? '-' }}</p>
                                </td>
                                <td class="px-3 py-2.5 text-slate-700">{{ $movement->serviceArea?->name ?? '-' }}</td>
                                <td class="px-3 py-2.5">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $movement->movement_type === 'in' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                        {{ $movement->movement_type === 'in' ? 'Entrée' : 'Sortie' }}
                                    </span>
                                </td>
                                <td class="px-3 py-2.5 text-right font-semibold text-slate-700">{{ number_format((float) $movement->quantity, 2, '.', ' ') }} {{ $movement->product?->unit }}</td>
                                <td class="px-3 py-2.5 text-right text-slate-700">{{ $currencySymbol }} {{ number_format((float) $movement->unit_cost, 2, '.', ' ') }}</td>
                                <td class="px-3 py-2.5 text-right font-semibold text-slate-900">{{ $currencySymbol }} {{ number_format($amount, 2, '.', ' ') }}</td>
                                <td class="px-3 py-2.5 text-slate-700">{{ $movement->reason ?: '-' }}</td>
                                <td class="px-3 py-2.5 text-slate-700">{{ $movement->user?->name ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-3 py-8 text-center text-slate-500">Aucun mouvement ne correspond aux filtres actuels.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $movements->onEachSide(1)->links() }}
            </div>
        </div>
    </section>
</div>
