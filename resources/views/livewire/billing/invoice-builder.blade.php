<div style="max-width: 1000px; margin: 0 auto; padding: 1.5rem; font-family: sans-serif;">
    <h1 style="margin-bottom: 1rem;">Invoice Builder</h1>

    <form wire:submit="build" style="display: grid; gap: .75rem;">
        <div style="display:grid; gap:.5rem; max-height: 320px; overflow:auto; border:1px solid #e5e7eb; border-radius:.5rem; padding:.75rem;">
            @forelse($openOrders as $order)
                <label style="display:flex; justify-content:space-between; gap:1rem;">
                    <span>
                        <input type="checkbox" wire:model="selectedOrderIds" value="{{ $order->id }}">
                        {{ $order->reference }} ({{ $order->customer_type->value }})
                    </span>
                    <strong>{{ number_format($order->total, 2, '.', ' ') }}</strong>
                </label>
            @empty
                <p>No billable orders found.</p>
            @endforelse
        </div>

        <button type="submit" style="background:#111827; color:#fff; border:0; border-radius:.5rem; padding:.7rem;">Build invoice from selection</button>
    </form>
</div>
