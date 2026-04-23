<div style="max-width: 1100px; margin: 0 auto; padding: 1.5rem; font-family: sans-serif;">
    <h1 style="margin-bottom: 1rem;">Order Intake</h1>

    <form wire:submit="save" style="display: grid; gap: .75rem; margin-bottom: 1rem;">
        <div style="display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: .75rem;">
            <select wire:model="service_area_id" style="padding: .6rem; border: 1px solid #d1d5db; border-radius: .5rem;">
                <option value="">Area</option>
                @foreach($serviceAreas as $area)
                    <option value="{{ $area->id }}">{{ $area->name }}</option>
                @endforeach
            </select>

            <select wire:model="customer_id" style="padding: .6rem; border: 1px solid #d1d5db; border-radius: .5rem;">
                <option value="">Anonymous / No customer</option>
                @foreach($customers as $customer)
                    <option value="{{ $customer->id }}">{{ $customer->full_name ?? 'Unnamed customer' }}</option>
                @endforeach
            </select>

            <select wire:model="customer_type" style="padding: .6rem; border: 1px solid #d1d5db; border-radius: .5rem;">
                @foreach($customerTypes as $type)
                    <option value="{{ $type }}">{{ $type }}</option>
                @endforeach
            </select>
        </div>

        @foreach($items as $index => $item)
            <div style="display: grid; grid-template-columns: 2fr 1fr 1fr auto; gap: .75rem;">
                <input type="text" wire:model="items.{{ $index }}.item_name" placeholder="Item name" style="padding: .6rem; border: 1px solid #d1d5db; border-radius: .5rem;">
                <input type="number" step="0.01" min="0.01" wire:model="items.{{ $index }}.quantity" placeholder="Qty" style="padding: .6rem; border: 1px solid #d1d5db; border-radius: .5rem;">
                <input type="number" step="0.01" min="0" wire:model="items.{{ $index }}.unit_price" placeholder="Unit price" style="padding: .6rem; border: 1px solid #d1d5db; border-radius: .5rem;">
                <button type="button" wire:click="removeItemRow({{ $index }})" style="background: #dc2626; color: #fff; border: 0; border-radius: .5rem; padding: .6rem;">X</button>
            </div>
        @endforeach

        <div style="display: flex; gap: .75rem;">
            <button type="button" wire:click="addItemRow" style="background: #0f766e; color: #fff; border: 0; padding: .6rem .9rem; border-radius: .5rem;">Add item</button>
            <button type="submit" style="background: #111827; color: #fff; border: 0; padding: .6rem .9rem; border-radius: .5rem;">Save order</button>
        </div>

        <textarea wire:model="notes" placeholder="Notes" style="padding: .6rem; border: 1px solid #d1d5db; border-radius: .5rem;"></textarea>
    </form>

    <h2>Recent Orders</h2>
    <table style="width:100%; border-collapse: collapse;">
        <thead>
            <tr style="text-align:left; border-bottom:1px solid #e5e7eb;">
                <th style="padding:.5rem;">Ref</th>
                <th style="padding:.5rem;">Type</th>
                <th style="padding:.5rem;">Status</th>
                <th style="padding:.5rem;">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($recentOrders as $order)
                <tr style="border-bottom:1px solid #f1f5f9;">
                    <td style="padding:.5rem;">{{ $order->reference }}</td>
                    <td style="padding:.5rem;">{{ $order->customer_type->value }}</td>
                    <td style="padding:.5rem;">{{ $order->status->value }}</td>
                    <td style="padding:.5rem;">{{ number_format($order->total, 2, '.', ' ') }}</td>
                </tr>
            @empty
                <tr><td colspan="4" style="padding:.75rem;">No orders yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
