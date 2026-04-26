<div class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
    <div class="rounded-2xl bg-slate-950 p-6 text-white shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-amber-300">Restaurant</p>
        <h1 class="mt-2 text-2xl font-black">Plats et recettes</h1>
        <p class="mt-2 text-sm text-slate-300">Constitution des plats, prix de vente et disponibilite selon les ingredients en stock.</p>
    </div>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_420px]">
        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white">
            <div class="border-b border-slate-200 px-4 py-3">
                <h2 class="text-lg font-bold text-slate-900">Catalogue des plats</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr class="text-left text-xs uppercase tracking-wide text-slate-600">
                            <th class="px-4 py-3">Plat</th>
                            <th class="px-4 py-3">Prix</th>
                            <th class="px-4 py-3">Recette</th>
                            <th class="px-4 py-3">Disponibilite</th>
                            <th class="px-4 py-3">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($menus as $menu)
                            @php($availability = $menu->getAttribute('recipe_availability'))
                            <tr class="align-top">
                                <td class="px-4 py-3">
                                    <p class="font-semibold text-slate-900">{{ $menu->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $menu->category?->name ?? '-' }} · {{ $menu->serviceArea?->name ?? '-' }}</p>
                                </td>
                                <td class="px-4 py-3 font-semibold text-slate-900">{{ number_format((float) $menu->price, 2, '.', ' ') }}</td>
                                <td class="px-4 py-3">
                                    <div class="space-y-1">
                                        @forelse($menu->ingredients as $ingredient)
                                            <p class="text-xs text-slate-600">
                                                {{ number_format((float) $ingredient->quantity, 3, '.', ' ') }}
                                                {{ $ingredient->unit ?: $ingredient->product?->unit }}
                                                {{ $ingredient->product?->name }}
                                            </p>
                                        @empty
                                            <p class="text-xs text-slate-400">Aucune recette</p>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    @if($availability['is_available'])
                                        <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                                            Disponible{{ $availability['max_servings'] !== null ? ' · '.$availability['max_servings'].' portion(s)' : '' }}
                                        </span>
                                    @else
                                        <div class="space-y-1">
                                            <span class="inline-flex rounded-full bg-rose-100 px-2.5 py-1 text-xs font-semibold text-rose-700">Indisponible</span>
                                            @foreach($availability['missing'] as $missing)
                                                <p class="text-xs text-rose-700">
                                                    {{ $missing['product']->name }}: {{ number_format($missing['available'], 3, '.', ' ') }} / {{ number_format($missing['required'], 3, '.', ' ') }} {{ $missing['unit'] }}
                                                </p>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <button wire:click="edit({{ $menu->id }})" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">
                                        Modifier
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-slate-500">Aucun plat cree.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <aside class="rounded-xl border border-slate-200 bg-white">
            <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
                <h2 class="text-lg font-bold text-slate-900">{{ $editing_menu_id ? 'Modifier le plat' : 'Nouveau plat' }}</h2>
                @if($editing_menu_id)
                    <button wire:click="resetForm" class="text-xs font-semibold text-slate-500 hover:text-slate-900">Annuler</button>
                @endif
            </div>

            <form wire:submit="save" class="space-y-4 p-4">
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Nom du plat</label>
                    <input type="text" wire:model.blur="name" class="prostay-input" placeholder="Ex: Poulet braise" />
                    @error('name') <p class="mt-1 text-xs font-semibold text-rose-700">{{ $message }}</p> @enderror
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Categorie</label>
                        <select wire:model="menu_category_id" class="prostay-input">
                            <option value="">Selectionner</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('menu_category_id') <p class="mt-1 text-xs font-semibold text-rose-700">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Zone</label>
                        <select wire:model="service_area_id" class="prostay-input">
                            <option value="">Selectionner</option>
                            @foreach($areas as $area)
                                <option value="{{ $area->id }}">{{ $area->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Prix de vente</label>
                        <input type="number" step="0.01" min="0" wire:model.blur="price" class="prostay-input" />
                        @error('price') <p class="mt-1 text-xs font-semibold text-rose-700">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Code</label>
                        <input type="text" wire:model.blur="sku" class="prostay-input" placeholder="Auto si vide" />
                    </div>
                </div>

                <label class="flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700">
                    <input type="checkbox" wire:model="is_available" class="rounded border-slate-300 text-emerald-700 focus:ring-emerald-600" />
                    Actif a la vente
                </label>

                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-bold text-slate-900">Recette</p>
                        <button type="button" wire:click="addIngredient" class="rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-semibold text-white">Ajouter ingredient</button>
                    </div>

                    @foreach($ingredients as $index => $ingredient)
                        <div class="rounded-lg border border-slate-200 p-3">
                            <div class="grid gap-2 sm:grid-cols-[minmax(0,1fr)_90px_80px_40px]">
                                <select wire:model="ingredients.{{ $index }}.product_id" class="prostay-input text-xs">
                                    <option value="">Ingredient stock</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}">{{ $product->name }} ({{ number_format((float) $product->stock_quantity, 3, '.', ' ') }} {{ $product->unit }})</option>
                                    @endforeach
                                </select>
                                <input type="number" step="0.001" min="0.001" wire:model.blur="ingredients.{{ $index }}.quantity" class="prostay-input text-xs" />
                                <input type="text" wire:model.blur="ingredients.{{ $index }}.unit" class="prostay-input text-xs" placeholder="unite" />
                                <button type="button" wire:click="removeIngredient({{ $index }})" class="rounded-lg border border-rose-200 bg-rose-50 text-rose-700">
                                    <i class="fa-solid fa-trash text-xs"></i>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>

                <button type="submit" class="w-full rounded-lg bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-600">
                    Enregistrer le plat
                </button>
            </form>
        </aside>
    </div>
</div>
