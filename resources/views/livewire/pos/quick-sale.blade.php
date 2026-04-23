<div class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
    <div class="rounded-3xl bg-gradient-to-br from-slate-900 via-rose-900 to-orange-900 p-6 text-white shadow-xl sm:p-8">
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-rose-300">Point Of Sale</p>
        <h1 class="mt-2 text-2xl font-black sm:text-3xl">POS Quick Sale</h1>
        <p class="mt-2 text-sm text-slate-200/90">Process fast counter sales with integrated payment flow and traceability.</p>
    </div>

    <div class="prostay-surface p-5 sm:p-6">
        <form wire:submit="submit" class="space-y-4">
            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                <select wire:model="service_area_id" class="prostay-input">
                    <option value="">POS area</option>
                    @foreach($areas as $area)
                        <option value="{{ $area->id }}">{{ $area->name }}</option>
                    @endforeach
                </select>

                <select wire:model="customer_id" class="prostay-input">
                    <option value="">Anonymous walk-in</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}">{{ $customer->full_name ?? 'Unnamed customer' }}</option>
                    @endforeach
                </select>

                <select wire:model="payment_method" class="prostay-input">
                    <option value="cash">cash</option>
                    <option value="mobile_money">mobile_money</option>
                    <option value="card">card</option>
                    <option value="bank_transfer">bank_transfer</option>
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
                        <input wire:model="items.{{ $index }}.item_name" type="text" placeholder="Item" class="prostay-input" />
                        <input wire:model="items.{{ $index }}.quantity" type="number" step="0.01" min="0.01" placeholder="Qty" class="prostay-input" />
                        <input wire:model="items.{{ $index }}.unit_price" type="number" step="0.01" min="0" placeholder="Price" class="prostay-input" />
                        <button type="button" wire:click="removeItem({{ $index }})" class="rounded-xl bg-rose-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-rose-500">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                @endforeach
            </div>

            <div class="flex flex-wrap gap-2">
                <button type="button" wire:click="addItem" class="inline-flex items-center gap-2 rounded-xl bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-600">
                    <i class="fa-solid fa-plus"></i>
                    Add item
                </button>
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-700">
                    <i class="fa-solid fa-receipt"></i>
                    Validate & pay
                </button>
            </div>
        </form>
    </div>
</div>
