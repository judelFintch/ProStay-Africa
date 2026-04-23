<div style="max-width: 1100px; margin: 0 auto; padding: 1.5rem; font-family: sans-serif;">
    <h1 style="margin-bottom: 1rem;">Reservations</h1>

    <form wire:submit="createReservation" style="display:grid; gap:.75rem; margin-bottom:1rem; grid-template-columns: repeat(3, minmax(0, 1fr));">
        <select wire:model="customer_id" style="padding:.6rem; border:1px solid #d1d5db; border-radius:.5rem;">
            <option value="">Customer</option>
            @foreach($customers as $customer)
                <option value="{{ $customer->id }}">{{ $customer->full_name ?? 'Unnamed' }}</option>
            @endforeach
        </select>

        <select wire:model="room_id" style="padding:.6rem; border:1px solid #d1d5db; border-radius:.5rem;">
            <option value="">Room</option>
            @foreach($rooms as $room)
                <option value="{{ $room->id }}">Room {{ $room->number }} ({{ $room->status->value }})</option>
            @endforeach
        </select>

        <input type="date" wire:model="check_in_date" style="padding:.6rem; border:1px solid #d1d5db; border-radius:.5rem;" />
        <input type="date" wire:model="check_out_date" style="padding:.6rem; border:1px solid #d1d5db; border-radius:.5rem;" />
        <input type="number" min="1" wire:model="adults" placeholder="Adults" style="padding:.6rem; border:1px solid #d1d5db; border-radius:.5rem;" />
        <input type="number" min="0" wire:model="children" placeholder="Children" style="padding:.6rem; border:1px solid #d1d5db; border-radius:.5rem;" />
        <textarea wire:model="notes" placeholder="Notes" style="grid-column: span 3; padding:.6rem; border:1px solid #d1d5db; border-radius:.5rem;"></textarea>
        <button type="submit" style="grid-column: span 3; background:#0f766e; color:#fff; border:0; border-radius:.5rem; padding:.7rem;">Create reservation</button>
    </form>

    <table style="width:100%; border-collapse: collapse; background:#fff;">
        <thead>
            <tr style="text-align:left; border-bottom:1px solid #e5e7eb;">
                <th style="padding:.6rem;">Customer</th>
                <th style="padding:.6rem;">Room</th>
                <th style="padding:.6rem;">Dates</th>
                <th style="padding:.6rem;">Status</th>
                <th style="padding:.6rem;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reservations as $reservation)
                <tr style="border-bottom:1px solid #f1f5f9;">
                    <td style="padding:.6rem;">{{ $reservation->customer?->full_name ?? 'Unknown' }}</td>
                    <td style="padding:.6rem;">{{ $reservation->room?->number ?? '-' }}</td>
                    <td style="padding:.6rem;">{{ $reservation->check_in_date?->format('Y-m-d') }} -> {{ $reservation->check_out_date?->format('Y-m-d') }}</td>
                    <td style="padding:.6rem;">{{ $reservation->status->value }}</td>
                    <td style="padding:.6rem; display:flex; gap:.5rem;">
                        @if($reservation->status->value !== $checkedInValue)
                            <button wire:click="checkIn({{ $reservation->id }})" style="background:#111827; color:#fff; border:0; border-radius:.4rem; padding:.4rem .6rem;">Check-in</button>
                        @endif
                        <button wire:click="cancel({{ $reservation->id }})" style="background:#dc2626; color:#fff; border:0; border-radius:.4rem; padding:.4rem .6rem;">Cancel</button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="padding:1rem;">No reservations yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
