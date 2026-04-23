<div class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
    <div class="rounded-3xl bg-gradient-to-br from-slate-900 via-slate-800 to-cyan-900 p-6 text-white shadow-xl sm:p-8">
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-cyan-300">{{ __('Housekeeping') }}</p>
        <h1 class="mt-2 text-2xl font-black sm:text-3xl">{{ __('Rooms') }}</h1>
        <p class="mt-2 text-sm text-slate-200/90">{{ __('Live room board with quick status updates for operations.') }}</p>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @foreach($rooms as $room)
            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Room') }}</p>
                        <p class="mt-1 text-xl font-black text-slate-900">{{ $room->number }}</p>
                    </div>
                    <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">{{ $room->status->value }}</span>
                </div>

                <div class="mt-4 space-y-1 text-sm text-slate-600">
                    <p>{{ __('Type') }}: <span class="font-medium text-slate-800">{{ $room->roomType?->name ?? '-' }}</span></p>
                    <p>{{ __('Price') }}: <span class="font-medium text-slate-800">{{ number_format($room->price, 2, '.', ' ') }}</span> XOF</p>
                </div>

                <div class="mt-4 flex flex-wrap gap-2">
                    @foreach($statuses as $status)
                        <button
                            wire:click="setStatus({{ $room->id }}, '{{ $status }}')"
                            class="rounded-lg border px-2.5 py-1 text-xs font-semibold transition {{ $room->status->value === $status ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-300 bg-white text-slate-700 hover:bg-slate-50' }}"
                        >
                            {{ $status }}
                        </button>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>
