<div class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
    <div class="rounded-3xl bg-gradient-to-br from-slate-900 via-amber-900 to-orange-900 p-6 text-white shadow-xl sm:p-8">
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-amber-300">F&B Operations</p>
        <h1 class="mt-2 text-2xl font-black sm:text-3xl">Order Intake</h1>
        <p class="mt-2 text-sm text-slate-200/90">Capture restaurant, bar, and service orders with clear billing-ready lines.</p>
    </div>

    <div class="prostay-surface p-5 sm:p-6">
        <form wire:submit="save" class="space-y-4">
            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                <select wire:model="service_area_id" class="prostay-input">
                    <option value="">Area</option>
                    @foreach($serviceAreas as $area)
                        <option value="{{ $area->id }}">{{ $area->name }}</option>
                    @endforeach
                </select>

                <select wire:model="customer_id" class="prostay-input">
                    <option value="">Anonymous / No customer</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}">{{ $customer->full_name ?? 'Unnamed customer' }}</option>
                    @endforeach
                </select>

                <select wire:model="customer_type" class="prostay-input">
                    @foreach($customerTypes as $type)
                        <option value="{{ $type }}">{{ $type }}</option>
                    @endforeach
                </select>
            </div>

            <div class="space-y-3">
                @foreach($items as $index => $item)
                    <div class="grid gap-3 md:grid-cols-[1.3fr_1.8fr_1fr_1fr_auto]">
                        <select wire:model="items.{{ $index }}.product_id" class="prostay-input">
                            <option value="">Product (optional)</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                            @endforeach
                        </select>
                        <input type="text" wire:model="items.{{ $index }}.item_name" placeholder="Item name" class="prostay-input" />
                        <input type="number" step="0.01" min="0.01" wire:model="items.{{ $index }}.quantity" placeholder="Qty" class="prostay-input" />
                        <input type="number" step="0.01" min="0" wire:model="items.{{ $index }}.unit_price" placeholder="Unit price" class="prostay-input" />
                        <button type="button" wire:click="removeItemRow({{ $index }})" class="rounded-xl bg-rose-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-rose-500">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                @endforeach
            </div>

            <div class="flex flex-wrap gap-2">
                <button type="button" wire:click="addItemRow" class="inline-flex items-center gap-2 rounded-xl bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-600">
                    <i class="fa-solid fa-plus"></i>
                    Add item
                </button>
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-700">
                    <i class="fa-solid fa-floppy-disk"></i>
                    Save order
                </button>
            </div>

            <textarea wire:model="notes" placeholder="Notes" rows="3" class="prostay-input"></textarea>
        </form>
    </div>

    <div class="prostay-surface overflow-hidden">
        <div class="border-b border-slate-200 px-5 py-4 sm:px-6">
            <h2 class="text-lg font-bold text-slate-900">Recent orders</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr class="text-left text-xs uppercase tracking-wide text-slate-600">
                        <th class="px-4 py-3">Ref</th>
                        <th class="px-4 py-3">Type</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($recentOrders as $order)
                        <tr>
                            <td class="px-4 py-3 font-medium text-slate-900">{{ $order->reference }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $order->customer_type->value }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $order->status->value }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ number_format($order->total, 2, '.', ' ') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-slate-500">No orders yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
