<div style="max-width: 1100px; margin: 0 auto; padding: 1.5rem; font-family: sans-serif;">
    <h1 style="margin-bottom: 1rem;">Rooms board</h1>

    <div style="display:grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap:.75rem;">
        @foreach($rooms as $room)
            <div style="border:1px solid #e5e7eb; border-radius:.75rem; background:#fff; padding:.9rem;">
                <p style="margin:0; font-weight:700;">Room {{ $room->number }}</p>
                <p style="margin:.25rem 0; color:#475569;">Type: {{ $room->roomType?->name ?? '-' }}</p>
                <p style="margin:.25rem 0; color:#475569;">Price: {{ number_format($room->price, 2, '.', ' ') }}</p>
                <p style="margin:.25rem 0 .6rem; color:#111827;">Status: <strong>{{ $room->status->value }}</strong></p>

                <div style="display:flex; flex-wrap:wrap; gap:.4rem;">
                    @foreach($statuses as $status)
                        <button wire:click="setStatus({{ $room->id }}, '{{ $status }}')"
                                style="border:1px solid #cbd5e1; background:{{ $room->status->value === $status ? '#111827' : '#fff' }}; color:{{ $room->status->value === $status ? '#fff' : '#111827' }}; border-radius:.45rem; padding:.3rem .55rem; font-size:.75rem; cursor:pointer;">
                            {{ $status }}
                        </button>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>
