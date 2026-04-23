<div style="max-width: 900px; margin: 0 auto; padding: 1.5rem; font-family: sans-serif;">
    <h1 style="margin-bottom: 1rem;">POS Quick Sale</h1>

    <form wire:submit="submit" style="display: grid; gap: .75rem;">
        <div style="display:grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: .75rem;">
            <select wire:model="service_area_id" style="padding:.6rem; border:1px solid #d1d5db; border-radius:.5rem;">
                <option value="">POS area</option>
                @foreach($areas as $area)
                    <option value="{{ $area->id }}">{{ $area->name }}</option>
                @endforeach
            </select>

            <select wire:model="customer_id" style="padding:.6rem; border:1px solid #d1d5db; border-radius:.5rem;">
                <option value="">Anonymous walk-in</option>
                @foreach($customers as $customer)
                    <option value="{{ $customer->id }}">{{ $customer->full_name ?? 'Unnamed customer' }}</option>
                @endforeach
            </select>

            <select wire:model="payment_method" style="padding:.6rem; border:1px solid #d1d5db; border-radius:.5rem;">
                <option value="cash">cash</option>
                <option value="mobile_money">mobile_money</option>
                <option value="card">card</option>
                <option value="bank_transfer">bank_transfer</option>
            </select>
        </div>

        @foreach($items as $index => $item)
            <div style="display:grid; grid-template-columns: 2fr 1fr 1fr auto; gap:.75rem;">
                <input wire:model="items.{{ $index }}.item_name" type="text" placeholder="Item" style="padding:.6rem; border:1px solid #d1d5db; border-radius:.5rem;">
                <input wire:model="items.{{ $index }}.quantity" type="number" step="0.01" min="0.01" placeholder="Qty" style="padding:.6rem; border:1px solid #d1d5db; border-radius:.5rem;">
                <input wire:model="items.{{ $index }}.unit_price" type="number" step="0.01" min="0" placeholder="Price" style="padding:.6rem; border:1px solid #d1d5db; border-radius:.5rem;">
                <button type="button" wire:click="removeItem({{ $index }})" style="background:#dc2626; color:#fff; border:0; border-radius:.5rem; padding:.6rem;">X</button>
            </div>
        @endforeach

        <div style="display:flex; gap:.75rem;">
            <button type="button" wire:click="addItem" style="background:#0f766e; color:#fff; border:0; border-radius:.5rem; padding:.6rem .9rem;">Add item</button>
            <button type="submit" style="background:#111827; color:#fff; border:0; border-radius:.5rem; padding:.6rem .9rem;">Validate & pay</button>
        </div>
    </form>
</div>
