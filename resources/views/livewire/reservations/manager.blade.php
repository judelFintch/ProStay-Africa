<div class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
    <div class="rounded-3xl bg-gradient-to-br from-slate-900 via-slate-800 to-emerald-900 p-6 text-white shadow-xl sm:p-8">
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-300">{{ __('Front Desk') }}</p>
        <h1 class="mt-2 text-2xl font-black sm:text-3xl">{{ __('Reservations') }}</h1>
        <p class="mt-2 text-sm text-slate-200/90">{{ __('Create bookings, check in guests, and monitor reservation flow.') }}</p>
    </div>

    <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 sm:p-6">
        <h2 class="text-lg font-bold text-slate-900">{{ __('New reservation') }}</h2>

        <form wire:submit="createReservation" class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">{{ __('Customer') }}</label>
                <select wire:model="customer_id" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">{{ __('Select customer') }}</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}">{{ $customer->full_name ?? 'Unnamed' }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">{{ __('Room') }}</label>
                <select wire:model="room_id" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">{{ __('Select room') }}</option>
                    @foreach($rooms as $room)
                        <option value="{{ $room->id }}">Room {{ $room->number }} ({{ $room->status->value }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">{{ __('Check in') }}</label>
                <input type="date" wire:model="check_in_date" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" />
            </div>

            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">{{ __('Check out') }}</label>
                <input type="date" wire:model="check_out_date" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" />
            </div>

            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">{{ __('Adults') }}</label>
                <input type="number" min="1" wire:model="adults" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" />
            </div>

            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">{{ __('Children') }}</label>
                <input type="number" min="0" wire:model="children" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" />
            </div>

            <div class="md:col-span-2 xl:col-span-3">
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">{{ __('Notes') }}</label>
                <textarea wire:model="notes" rows="3" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="{{ __('Special requests, preferences, arrival details...') }}"></textarea>
            </div>

            <div class="md:col-span-2 xl:col-span-3">
                <button type="submit" class="inline-flex items-center rounded-xl bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-600">
                    {{ __('Create reservation') }}
                </button>
            </div>
        </form>
    </div>

    <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
        <div class="border-b border-slate-200 px-5 py-4 sm:px-6">
            <h2 class="text-lg font-bold text-slate-900">{{ __('Recent reservations') }}</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr class="text-left text-xs uppercase tracking-wide text-slate-600">
                        <th class="px-4 py-3">{{ __('Customer') }}</th>
                        <th class="px-4 py-3">{{ __('Room') }}</th>
                        <th class="px-4 py-3">{{ __('Dates') }}</th>
                        <th class="px-4 py-3">{{ __('Status') }}</th>
                        <th class="px-4 py-3">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($reservations as $reservation)
                        <tr class="align-middle">
                            <td class="px-4 py-3 font-medium text-slate-900">{{ $reservation->customer?->full_name ?? 'Unknown' }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $reservation->room?->number ?? '-' }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $reservation->check_in_date?->format('Y-m-d') }} {{ __('to') }} {{ $reservation->check_out_date?->format('Y-m-d') }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">{{ $reservation->status->value }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-2">
                                    @if($reservation->status->value !== $checkedInValue)
                                        <button wire:click="checkIn({{ $reservation->id }})" class="rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-slate-700">{{ __('Check-in') }}</button>
                                    @endif
                                    <button wire:click="cancel({{ $reservation->id }})" class="rounded-lg bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-rose-500">{{ __('Cancel') }}</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-slate-500">{{ __('No reservations yet.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
        <div class="border-b border-slate-200 px-5 py-4 sm:px-6">
            <h2 class="text-lg font-bold text-slate-900">Active stays</h2>
        </div>

        @error('checkout')
            <div class="border-b border-rose-200 bg-rose-50 px-5 py-3 text-sm font-semibold text-rose-700 sm:px-6">
                {{ $message }}
            </div>
        @enderror

        <div class="border-b border-slate-200 px-5 py-3 sm:px-6">
            <div class="flex flex-wrap items-end gap-3">
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Extend nights</label>
                    <input type="number" min="1" max="30" wire:model="extend_nights" class="w-32 rounded-xl border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" />
                </div>
                <p class="text-xs text-slate-500">Use extension button on a stay row to add nights.</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr class="text-left text-xs uppercase tracking-wide text-slate-600">
                        <th class="px-4 py-3">Customer</th>
                        <th class="px-4 py-3">Room</th>
                        <th class="px-4 py-3">Check in</th>
                        <th class="px-4 py-3">Expected check out</th>
                        <th class="px-4 py-3">Invoice</th>
                        <th class="px-4 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($activeStays as $stay)
                        <tr>
                            <td class="px-4 py-3 font-medium text-slate-900">{{ $stay->customer?->full_name ?? '-' }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $stay->room?->number ?? '-' }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $stay->check_in_at?->format('Y-m-d H:i') }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $stay->expected_check_out_at?->format('Y-m-d H:i') ?? '-' }}</td>
                            <td class="px-4 py-3 text-slate-700">
                                @php($stayInvoice = $stay->invoices->sortByDesc('issued_at')->first())
                                @if($stayInvoice)
                                    <div class="space-y-1">
                                        <div class="font-semibold text-slate-900">{{ $stayInvoice->reference }}</div>
                                        <div class="text-xs text-slate-500">Reste: {{ number_format($stayInvoice->balance, 2, '.', ' ') }}</div>
                                        @if($stayInvoice->balance > 0)
                                            <a href="{{ route('billing.payments', ['invoice' => $stayInvoice->id]) }}" wire:navigate class="text-xs font-semibold text-emerald-700 hover:text-emerald-600">Paiement</a>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-xs text-slate-400">Preparee au check-out</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-2">
                                    <button wire:click="extendStay({{ $stay->id }})" class="rounded-lg bg-amber-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-amber-500">Extend</button>
                                    <button wire:click="checkOut({{ $stay->id }})" class="rounded-lg bg-emerald-700 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-emerald-600">Check-out</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-slate-500">No active stays.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
