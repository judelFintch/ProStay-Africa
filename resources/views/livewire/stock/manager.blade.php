<div class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
    <div class="rounded-3xl bg-gradient-to-br from-slate-900 via-emerald-900 to-teal-900 p-6 text-white shadow-xl sm:p-8">
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-300">Inventory</p>
        <h1 class="mt-2 text-2xl font-black sm:text-3xl">Stock Management</h1>
        <p class="mt-2 text-sm text-slate-200/90">Track entries, exits, and critical low-stock alerts.</p>
    </div>

    <div class="prostay-surface p-5 sm:p-6">
        <h2 class="text-lg font-bold text-slate-900">Record stock movement</h2>
        <form wire:submit="saveMovement" class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-5">
            <select wire:model="product_id" class="prostay-input xl:col-span-2">
                <option value="">Select product</option>
                @foreach($products as $product)
                    <option value="{{ $product->id }}">{{ $product->name }} ({{ number_format($product->stock_quantity, 2, '.', ' ') }})</option>
                @endforeach
            </select>

            <select wire:model="movement_type" class="prostay-input">
                <option value="in">in</option>
                <option value="out">out</option>
            </select>

            <input type="number" wire:model="quantity" step="0.01" min="0.01" class="prostay-input" placeholder="Quantity" />
            <input type="number" wire:model="unit_cost" step="0.01" min="0" class="prostay-input" placeholder="Unit cost" />

            <input type="text" wire:model="reason" class="prostay-input md:col-span-2 xl:col-span-4" placeholder="Reason" />
            <button class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-700">Save movement</button>
        </form>
    </div>

    <div class="grid gap-4 xl:grid-cols-3">
        <div class="prostay-surface p-5 xl:col-span-2">
            <h2 class="text-lg font-bold text-slate-900">Products</h2>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr class="text-left text-xs uppercase tracking-wide text-slate-600">
                            <th class="px-4 py-3">Product</th>
                            <th class="px-4 py-3">Stock</th>
                            <th class="px-4 py-3">Threshold</th>
                            <th class="px-4 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @foreach($products as $product)
                            <tr>
                                <td class="px-4 py-3 font-medium text-slate-900">{{ $product->name }}</td>
                                <td class="px-4 py-3 text-slate-700">{{ number_format($product->stock_quantity, 2, '.', ' ') }}</td>
                                <td class="px-4 py-3 text-slate-700">{{ number_format($product->alert_threshold, 2, '.', ' ') }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $product->stock_quantity <= $product->alert_threshold ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700' }}">
                                        {{ $product->stock_quantity <= $product->alert_threshold ? 'alert' : 'ok' }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="space-y-4">
            <div class="prostay-surface p-5">
                <h3 class="text-base font-bold text-slate-900">Low stock alerts</h3>
                <div class="mt-3 space-y-2">
                    @forelse($alerts as $alert)
                        <div class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs">
                            <p class="font-semibold text-rose-800">{{ $alert->name }}</p>
                            <p class="text-rose-700">{{ number_format($alert->stock_quantity, 2, '.', ' ') }} left</p>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">No alert currently.</p>
                    @endforelse
                </div>
            </div>

            <div class="prostay-surface p-5">
                <h3 class="text-base font-bold text-slate-900">Recent movements</h3>
                <div class="mt-3 space-y-2">
                    @foreach($movements as $movement)
                        <div class="rounded-xl border border-slate-200 px-3 py-2 text-xs">
                            <p class="font-semibold text-slate-900">{{ $movement->product?->name ?? '-' }}</p>
                            <p class="text-slate-600">{{ $movement->movement_type }} {{ number_format($movement->quantity, 2, '.', ' ') }} • {{ $movement->created_at->format('d/m H:i') }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
