<div class="mx-auto max-w-7xl space-y-4 px-4 py-4 sm:px-6 lg:px-8" x-data="{ panel: 'product' }">
    <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 sm:p-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-emerald-600">Inventory Control</p>
                <h1 class="mt-1 text-2xl font-black text-slate-900">Gestion de stock</h1>
                <p class="mt-1 max-w-2xl text-sm text-slate-500">
                    Catalogue produits, categories, mouvements et alertes dans une interface plus simple,
                    plus lisible et adaptee a un usage ERP.
                </p>
            </div>

            <div class="grid gap-2 sm:grid-cols-4">
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400">Produits</p>
                    <p class="mt-1 text-xl font-black text-slate-900">{{ number_format($stats['products']) }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400">Categories</p>
                    <p class="mt-1 text-xl font-black text-slate-900">{{ number_format($stats['categories']) }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400">Alertes</p>
                    <p class="mt-1 text-xl font-black text-rose-700">{{ number_format($stats['alerts']) }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400">Perissables</p>
                    <p class="mt-1 text-xl font-black text-amber-700">{{ number_format($stats['fresh']) }}</p>
                </div>
            </div>
        </div>
    </section>

    <div class="grid gap-4 xl:grid-cols-[1.55fr_0.85fr]">
        <section class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
            <div class="border-b border-slate-200 px-4 py-3 sm:px-5">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Operations</p>
                        <h2 class="mt-1 text-lg font-black text-slate-900">Saisie rapide</h2>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <button
                            type="button"
                            @click="panel = 'product'"
                            class="rounded-lg px-3 py-1.5 text-sm font-semibold transition"
                            :class="panel === 'product' ? 'bg-slate-900 text-white' : 'border border-slate-200 bg-white text-slate-700 hover:bg-slate-50'"
                        >
                            Produit
                        </button>
                        <button
                            type="button"
                            @click="panel = 'movement'"
                            class="rounded-lg px-3 py-1.5 text-sm font-semibold transition"
                            :class="panel === 'movement' ? 'bg-slate-900 text-white' : 'border border-slate-200 bg-white text-slate-700 hover:bg-slate-50'"
                        >
                            Mouvement
                        </button>
                        <button
                            type="button"
                            @click="panel = 'category'"
                            class="rounded-lg px-3 py-1.5 text-sm font-semibold transition"
                            :class="panel === 'category' ? 'bg-slate-900 text-white' : 'border border-slate-200 bg-white text-slate-700 hover:bg-slate-50'"
                        >
                            Categorie
                        </button>
                    </div>
                </div>
            </div>

            <div class="p-4 sm:p-5">
                <form x-show="panel === 'product'" wire:submit="saveProduct" class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Categorie</label>
                        <select wire:model="product_category_id" class="prostay-input">
                            <option value="">Selectionner</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Fournisseur</label>
                        <select wire:model="supplier_id" class="prostay-input">
                            <option value="">Selectionner</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-2 xl:col-span-2">
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Nom produit</label>
                        <input type="text" wire:model="product_name" class="prostay-input" placeholder="Ex: Poulet frais" />
                    </div>

                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">SKU</label>
                        <input type="text" wire:model="sku" class="prostay-input" placeholder="Auto si vide" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Unite stock</label>
                        <select wire:model="unit" class="prostay-input">
                            @foreach($referenceUnits as $referenceUnit)
                                <option value="{{ $referenceUnit }}">{{ $referenceUnit }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Unite achat</label>
                        <select wire:model="purchase_unit" class="prostay-input">
                            <option value="">Selectionner</option>
                            @foreach($referencePurchaseUnits as $referencePurchaseUnit)
                                <option value="{{ $referencePurchaseUnit }}">{{ $referencePurchaseUnit }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Zone stockage</label>
                        <select wire:model="storage_area" class="prostay-input">
                            <option value="">Selectionner</option>
                            @foreach($referenceStorageAreas as $referenceStorageArea)
                                <option value="{{ $referenceStorageArea }}">{{ $referenceStorageArea }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Cout unitaire</label>
                        <input type="number" wire:model="product_unit_cost" step="0.01" min="0" class="prostay-input" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Prix vente</label>
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
                            Produit perissable
                        </label>
                    </div>
                    <div class="flex items-end">
                        <label class="inline-flex w-full items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-medium text-slate-700">
                            <input type="checkbox" wire:model="is_active" class="rounded border-slate-300 text-emerald-600 shadow-sm focus:ring-emerald-500">
                            Produit actif
                        </label>
                    </div>
                    <div class="flex items-end">
                        <button class="w-full rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800">
                            Enregistrer le produit
                        </button>
                    </div>
                </form>

                <form x-show="panel === 'movement'" wire:submit="saveMovement" class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Produit</label>
                        <select wire:model="product_id" class="prostay-input">
                            <option value="">Selectionner un produit</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }} ({{ number_format($product->stock_quantity, 2, '.', ' ') }} {{ $product->unit }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Type</label>
                        <select wire:model="movement_type" class="prostay-input">
                            <option value="in">Entree</option>
                            <option value="out">Sortie</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Quantite</label>
                        <input type="number" wire:model="quantity" step="0.01" min="0.01" class="prostay-input" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Cout unitaire</label>
                        <input type="number" wire:model="unit_cost" step="0.01" min="0" class="prostay-input" />
                    </div>
                    <div class="sm:col-span-2 xl:col-span-2">
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Motif</label>
                        <select wire:model="reason" class="prostay-input">
                            <option value="">Selectionner</option>
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
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Nom categorie</label>
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
                        <input type="text" wire:model="category_description" class="prostay-input" placeholder="Categorie des produits frais de cuisine" />
                    </div>
                    <div class="flex items-end">
                        <div class="flex w-full flex-col gap-2">
                            <label class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-medium text-slate-700">
                                <input type="checkbox" wire:model="category_is_perishable" class="rounded border-slate-300 text-emerald-600 shadow-sm focus:ring-emerald-500">
                                Perissable
                            </label>
                            <label class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-medium text-slate-700">
                                <input type="checkbox" wire:model="category_is_active" class="rounded border-slate-300 text-emerald-600 shadow-sm focus:ring-emerald-500">
                                Active
                            </label>
                        </div>
                    </div>
                    <div class="xl:col-span-4">
                        <button class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800">
                            Enregistrer la categorie
                        </button>
                    </div>
                </form>
            </div>
        </section>

        <aside class="space-y-6">
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
                <h3 class="mt-1 text-base font-black text-slate-900">Historique recent</h3>

                <div class="mt-3 space-y-2.5">
                    @foreach($movements as $movement)
                        <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5">
                            <p class="text-sm font-semibold text-slate-900">{{ $movement->product?->name ?? '-' }}</p>
                            <p class="mt-1 text-xs text-slate-600">
                                {{ $movement->movement_type === 'in' ? 'Entree' : 'Sortie' }}
                                {{ number_format($movement->quantity, 2, '.', ' ') }}
                                @if($movement->product?->unit)
                                    {{ $movement->product->unit }}
                                @endif
                                · {{ $movement->created_at->format('d/m H:i') }}
                            </p>
                            @if($movement->reason)
                                <p class="mt-1 text-xs text-slate-500">{{ $movement->reason }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </section>
        </aside>
    </div>

    <section class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200 sm:p-5">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Catalogue</p>
                <h2 class="mt-1 text-lg font-black text-slate-900">Registre des produits</h2>
                <p class="mt-1 text-sm text-slate-500">Recherche, filtrage et lecture rapide des informations importantes.</p>
            </div>

            <div class="grid gap-2 sm:grid-cols-3">
                <input type="text" wire:model.live.debounce.300ms="search" class="prostay-input" placeholder="Produit, SKU, stockage..." />
                <select wire:model.live="categoryFilter" class="prostay-input">
                    <option value="">Toutes categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->code }}">{{ $category->name }}</option>
                    @endforeach
                </select>
                <select wire:model.live="stockFilter" class="prostay-input">
                    <option value="all">Tous</option>
                    <option value="alerts">En alerte</option>
                    <option value="perishable">Perissables</option>
                    <option value="fresh">Vivres frais</option>
                </select>
            </div>
        </div>

        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr class="text-left text-xs uppercase tracking-wide text-slate-500">
                        <th class="px-3 py-2.5">Produit</th>
                        <th class="px-3 py-2.5">Categorie</th>
                        <th class="px-3 py-2.5">Stock</th>
                        <th class="px-3 py-2.5">Seuil</th>
                        <th class="px-3 py-2.5">Stockage</th>
                        <th class="px-3 py-2.5">Etat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($products as $product)
                        <tr>
                            <td class="px-3 py-2.5">
                                <p class="font-semibold text-slate-900">{{ $product->name }}</p>
                                <p class="text-xs text-slate-500">{{ $product->sku ?: '-' }}</p>
                            </td>
                            <td class="px-3 py-2.5 text-slate-700">{{ $product->category?->name ?? '-' }}</td>
                            <td class="px-3 py-2.5 text-slate-700">{{ number_format($product->stock_quantity, 2, '.', ' ') }} {{ $product->unit }}</td>
                            <td class="px-3 py-2.5 text-slate-700">{{ number_format($product->alert_threshold, 2, '.', ' ') }}</td>
                            <td class="px-3 py-2.5 text-slate-700">{{ $product->storage_area ?: '-' }}</td>
                            <td class="px-3 py-2.5">
                                <div class="flex flex-wrap gap-2">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $product->stock_quantity <= $product->alert_threshold ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700' }}">
                                        {{ $product->stock_quantity <= $product->alert_threshold ? 'Alerte' : 'Correct' }}
                                    </span>
                                    @if($product->is_perishable)
                                        <span class="inline-flex rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-700">
                                            Perissable
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-3 py-8 text-center text-slate-500">Aucun produit ne correspond aux filtres actuels.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
