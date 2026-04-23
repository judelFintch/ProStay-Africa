<div style="max-width: 1100px; margin: 0 auto; padding: 1.5rem; font-family: sans-serif;">
    <h1 style="margin-bottom: 1rem;">Customers</h1>

    <form wire:submit="createCustomer" style="display: grid; gap: .75rem; margin-bottom: 1.5rem; grid-template-columns: repeat(4, minmax(0, 1fr));">
        <input type="text" wire:model="full_name" placeholder="Full name" style="padding: .6rem; border: 1px solid #d1d5db; border-radius: .5rem;">
        <input type="text" wire:model="phone" placeholder="Phone" style="padding: .6rem; border: 1px solid #d1d5db; border-radius: .5rem;">
        <input type="email" wire:model="email" placeholder="Email" style="padding: .6rem; border: 1px solid #d1d5db; border-radius: .5rem;">
        <label style="display: flex; align-items: center; gap: .5rem;">
            <input type="checkbox" wire:model="is_identified">
            Identified
        </label>
        <button type="submit" style="grid-column: span 4; background: #0f766e; color: #fff; border: 0; padding: .7rem; border-radius: .5rem; cursor: pointer;">Create customer</button>
    </form>

    <input type="text" wire:model.live.debounce.400ms="search" placeholder="Search by name, phone, email..." style="width: 100%; padding: .6rem; border: 1px solid #d1d5db; border-radius: .5rem; margin-bottom: 1rem;">

    <table style="width: 100%; border-collapse: collapse; background: #fff;">
        <thead>
            <tr style="text-align: left; border-bottom: 1px solid #e5e7eb;">
                <th style="padding: .6rem;">Name</th>
                <th style="padding: .6rem;">Phone</th>
                <th style="padding: .6rem;">Email</th>
                <th style="padding: .6rem;">Identified</th>
            </tr>
        </thead>
        <tbody>
            @forelse($customers as $customer)
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: .6rem;">{{ $customer->full_name ?? 'Anonymous / Walk-in' }}</td>
                    <td style="padding: .6rem;">{{ $customer->phone ?? '-' }}</td>
                    <td style="padding: .6rem;">{{ $customer->email ?? '-' }}</td>
                    <td style="padding: .6rem;">{{ $customer->is_identified ? 'Yes' : 'No' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="padding: 1rem;">No customers yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 1rem;">
        {{ $customers->links() }}
    </div>
</div>
