<?php

namespace App\Livewire\Reservations;

use App\Enums\ReservationStatus;
use App\Enums\RoomStatus;
use App\Enums\StayStatus;
use App\Models\Customer;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\Stay;
use App\Services\Billing\InvoiceService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use RuntimeException;

class Manager extends Component
{
    public ?int $customer_id = null;

    public ?int $room_id = null;

    public string $check_in_date = '';

    public string $check_out_date = '';

    public int $adults = 1;

    public int $children = 0;

    public ?string $notes = null;

    public int $extend_nights = 1;

    public ?string $invoice_notice = null;

    public ?int $edit_reservation_id = null;

    public ?int $edit_room_id = null;

    public string $edit_check_in_date = '';

    public string $edit_check_out_date = '';

    public int $edit_adults = 1;

    public int $edit_children = 0;

    public ?string $edit_notes = null;

    public function createReservation(): void
    {
        $this->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'room_id' => ['required', 'exists:rooms,id'],
            'check_in_date' => ['required', 'date', 'after_or_equal:today'],
            'check_out_date' => ['required', 'date', 'after:check_in_date'],
            'adults' => ['required', 'integer', 'min:1'],
            'children' => ['required', 'integer', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        try {
            DB::transaction(function (): void {
                $room = Room::query()->lockForUpdate()->findOrFail($this->room_id);
                $checkIn = Carbon::parse($this->check_in_date)->startOfDay();
                $checkOut = Carbon::parse($this->check_out_date)->startOfDay();

                $this->ensureRoomCanBeReserved($room, $checkIn, $checkOut);

                Reservation::create([
                    'customer_id' => $this->customer_id,
                    'room_id' => $room->id,
                    'check_in_date' => $checkIn->toDateString(),
                    'check_out_date' => $checkOut->toDateString(),
                    'adults' => $this->adults,
                    'children' => $this->children,
                    'status' => ReservationStatus::Confirmed->value,
                    'notes' => $this->notes,
                ]);
            });
        } catch (RuntimeException $exception) {
            $this->addError('room_id', $exception->getMessage());

            return;
        }

        $this->reset(['customer_id', 'room_id', 'check_in_date', 'check_out_date', 'notes']);
        $this->adults = 1;
        $this->children = 0;
    }

    public function checkIn(int $reservationId): void
    {
        try {
            DB::transaction(function () use ($reservationId): void {
                $reservation = Reservation::query()->with('room')->lockForUpdate()->findOrFail($reservationId);

                if ($reservation->status === ReservationStatus::CheckedIn) {
                    return;
                }

                $this->ensureReservationCanCheckIn($reservation);

                $reservation->update([
                    'status' => ReservationStatus::CheckedIn->value,
                ]);

                Stay::query()->firstOrCreate(
                    [
                        'reservation_id' => $reservation->id,
                    ],
                    [
                        'customer_id' => $reservation->customer_id,
                        'room_id' => $reservation->room_id,
                        'check_in_at' => now(),
                        'expected_check_out_at' => $reservation->check_out_date,
                        'status' => StayStatus::Active->value,
                        'nightly_rate' => $reservation->room->price,
                        'notes' => 'Checked-in from reservation',
                    ]
                );

                $reservation->room->update([
                    'status' => RoomStatus::Occupied->value,
                ]);
            });
        } catch (RuntimeException $exception) {
            $this->addError('checkin', $exception->getMessage());
        }
    }

    public function cancel(int $reservationId): void
    {
        try {
            DB::transaction(function () use ($reservationId): void {
                $reservation = Reservation::query()->lockForUpdate()->findOrFail($reservationId);

                if (! in_array($reservation->status, [
                    ReservationStatus::Pending,
                    ReservationStatus::Confirmed,
                ], true)) {
                    throw new RuntimeException('Seule une reservation en attente ou confirmee peut etre annulee.');
                }

                $reservation->update([
                    'status' => ReservationStatus::Cancelled->value,
                ]);
            });
        } catch (RuntimeException $exception) {
            $this->addError('reservation_action', $exception->getMessage());
        }
    }

    public function markNoShow(int $reservationId): void
    {
        try {
            DB::transaction(function () use ($reservationId): void {
                $reservation = Reservation::query()->lockForUpdate()->findOrFail($reservationId);

                if (! in_array($reservation->status, [
                    ReservationStatus::Pending,
                    ReservationStatus::Confirmed,
                ], true)) {
                    throw new RuntimeException('Seule une reservation en attente ou confirmee peut etre marquee no-show.');
                }

                $reservation->update([
                    'status' => ReservationStatus::NoShow->value,
                ]);
            });
        } catch (RuntimeException $exception) {
            $this->addError('reservation_action', $exception->getMessage());
        }
    }

    public function startEdit(int $reservationId): void
    {
        $reservation = Reservation::query()->findOrFail($reservationId);

        if (! in_array($reservation->status, [
            ReservationStatus::Pending,
            ReservationStatus::Confirmed,
        ], true)) {
            $this->addError('reservation_action', 'Seule une reservation en attente ou confirmee peut etre modifiee.');

            return;
        }

        $this->edit_reservation_id = $reservation->id;
        $this->edit_room_id = $reservation->room_id;
        $this->edit_check_in_date = $reservation->check_in_date->toDateString();
        $this->edit_check_out_date = $reservation->check_out_date->toDateString();
        $this->edit_adults = $reservation->adults;
        $this->edit_children = $reservation->children;
        $this->edit_notes = $reservation->notes;
        $this->resetErrorBag('reservation_action');
    }

    public function cancelEdit(): void
    {
        $this->reset([
            'edit_reservation_id',
            'edit_room_id',
            'edit_check_in_date',
            'edit_check_out_date',
            'edit_notes',
        ]);
        $this->edit_adults = 1;
        $this->edit_children = 0;
    }

    public function updateReservation(): void
    {
        $this->validate([
            'edit_reservation_id' => ['required', 'exists:reservations,id'],
            'edit_room_id' => ['required', 'exists:rooms,id'],
            'edit_check_in_date' => ['required', 'date', 'after_or_equal:today'],
            'edit_check_out_date' => ['required', 'date', 'after:edit_check_in_date'],
            'edit_adults' => ['required', 'integer', 'min:1'],
            'edit_children' => ['required', 'integer', 'min:0'],
            'edit_notes' => ['nullable', 'string'],
        ]);

        try {
            DB::transaction(function (): void {
                $reservation = Reservation::query()->lockForUpdate()->findOrFail($this->edit_reservation_id);

                if (! in_array($reservation->status, [
                    ReservationStatus::Pending,
                    ReservationStatus::Confirmed,
                ], true)) {
                    throw new RuntimeException('Seule une reservation en attente ou confirmee peut etre modifiee.');
                }

                $room = Room::query()->lockForUpdate()->findOrFail($this->edit_room_id);
                $checkIn = Carbon::parse($this->edit_check_in_date)->startOfDay();
                $checkOut = Carbon::parse($this->edit_check_out_date)->startOfDay();

                $this->ensureRoomCanBeReserved(
                    room: $room,
                    checkIn: $checkIn,
                    checkOut: $checkOut,
                    adults: $this->edit_adults,
                    children: $this->edit_children,
                    ignoredReservationId: $reservation->id,
                );

                $reservation->update([
                    'room_id' => $room->id,
                    'check_in_date' => $checkIn->toDateString(),
                    'check_out_date' => $checkOut->toDateString(),
                    'adults' => $this->edit_adults,
                    'children' => $this->edit_children,
                    'notes' => $this->edit_notes,
                ]);
            });
        } catch (RuntimeException $exception) {
            $this->addError('edit_room_id', $exception->getMessage());

            return;
        }

        $this->cancelEdit();
    }

    public function extendStay(int $stayId): void
    {
        $this->validate([
            'extend_nights' => ['required', 'integer', 'min:1', 'max:30'],
        ]);

        $stay = Stay::query()->findOrFail($stayId);
        $currentExpected = $stay->expected_check_out_at ?? now();

        $stay->update([
            'expected_check_out_at' => $currentExpected->copy()->addDays($this->extend_nights),
        ]);
    }

    public function checkOut(int $stayId): void
    {
        $stay = Stay::query()->with(['room', 'reservation'])->findOrFail($stayId);

        if ($stay->status !== StayStatus::Active) {
            return;
        }

        $invoice = app(InvoiceService::class)->openFolderForStay($stay, [
            'customer_id' => $stay->customer_id,
            'room_id' => $stay->room_id,
            'issued_by' => Auth::id(),
        ]);

        if ((float) $invoice->balance > 0) {
            $this->addError(
                'checkout',
                'Check-out bloque: facture '.$invoice->reference.' avec solde restant '.number_format((float) $invoice->balance, 2, '.', ' ').'.'
            );

            return;
        }

        $stay->update([
            'status' => StayStatus::CheckedOut->value,
            'check_out_at' => now(),
        ]);

        $stay->room?->update([
            'status' => RoomStatus::Cleaning->value,
        ]);

        $stay->reservation?->update([
            'status' => ReservationStatus::CheckedOut->value,
        ]);
    }

    public function prepareInvoice(int $stayId): void
    {
        $stay = Stay::query()->with(['room', 'reservation'])->findOrFail($stayId);

        if ($stay->status !== StayStatus::Active) {
            return;
        }

        $invoice = app(InvoiceService::class)->openFolderForStay($stay, [
            'customer_id' => $stay->customer_id,
            'room_id' => $stay->room_id,
            'issued_by' => Auth::id(),
        ]);

        $this->invoice_notice = 'Facture '.$invoice->reference.' preparee. Reste a payer: '.number_format((float) $invoice->balance, 2, '.', ' ').'.';
        $this->resetErrorBag('checkout');
    }

    public function render()
    {
        $rooms = $this->availableRoomsForPeriod(
            checkInDate: $this->check_in_date,
            checkOutDate: $this->check_out_date,
        );

        $editRooms = $this->availableRoomsForPeriod(
            checkInDate: $this->edit_check_in_date,
            checkOutDate: $this->edit_check_out_date,
            ignoredReservationId: $this->edit_reservation_id,
        );

        return view('livewire.reservations.manager', [
            'customers' => Customer::query()->orderBy('full_name')->limit(150)->get(),
            'rooms' => $rooms,
            'editRooms' => $editRooms,
            'reservations' => Reservation::query()->with(['customer', 'room'])->latest()->limit(20)->get(),
            'activeStays' => Stay::query()->with(['customer', 'room', 'invoices'])->where('status', StayStatus::Active->value)->latest()->limit(20)->get(),
            'checkedInValue' => ReservationStatus::CheckedIn->value,
        ]);
    }

    private function ensureRoomCanBeReserved(
        Room $room,
        Carbon $checkIn,
        Carbon $checkOut,
        ?int $adults = null,
        ?int $children = null,
        ?int $ignoredReservationId = null,
    ): void {
        $guestCount = ($adults ?? $this->adults) + ($children ?? $this->children);

        if ($guestCount > (int) $room->capacity) {
            throw new RuntimeException('La capacite de la chambre est insuffisante pour le nombre de voyageurs.');
        }

        if ($room->status === RoomStatus::Maintenance) {
            throw new RuntimeException('Cette chambre est actuellement en maintenance.');
        }

        if ($room->status === RoomStatus::Occupied) {
            throw new RuntimeException('Cette chambre est actuellement occupee.');
        }

        $reservationConflictQuery = Reservation::query()
            ->where('room_id', $room->id)
            ->whereIn('status', [
                ReservationStatus::Pending->value,
                ReservationStatus::Confirmed->value,
                ReservationStatus::CheckedIn->value,
            ])
            ->where('check_in_date', '<', $checkOut->toDateString())
            ->where('check_out_date', '>', $checkIn->toDateString());

        if ($ignoredReservationId) {
            $reservationConflictQuery->whereKeyNot($ignoredReservationId);
        }

        $hasReservationConflict = $reservationConflictQuery->exists();

        $hasActiveStayConflict = Stay::query()
            ->where('room_id', $room->id)
            ->where('status', StayStatus::Active->value)
            ->where('check_in_at', '<', $checkOut->copy()->endOfDay())
            ->where(function ($query) use ($checkIn): void {
                $query->whereNull('expected_check_out_at')
                    ->orWhere('expected_check_out_at', '>', $checkIn);
            })
            ->exists();

        if ($hasReservationConflict || $hasActiveStayConflict) {
            throw new RuntimeException('Cette chambre est deja reservee ou occupee sur la periode demandee.');
        }
    }

    private function ensureReservationCanCheckIn(Reservation $reservation): void
    {
        if (in_array($reservation->status, [
            ReservationStatus::Cancelled,
            ReservationStatus::CheckedOut,
            ReservationStatus::NoShow,
        ], true)) {
            throw new RuntimeException('Cette reservation ne peut plus etre transformee en sejour.');
        }

        if ($reservation->room->status === RoomStatus::Maintenance) {
            throw new RuntimeException('Cette chambre est actuellement en maintenance.');
        }

        $hasRoomConflict = Stay::query()
            ->where('room_id', $reservation->room_id)
            ->where('status', StayStatus::Active->value)
            ->where(function ($query) use ($reservation): void {
                $query->whereNull('reservation_id')
                    ->orWhere('reservation_id', '!=', $reservation->id);
            })
            ->exists();

        if ($hasRoomConflict) {
            throw new RuntimeException('Cette chambre est deja occupee par un sejour actif.');
        }
    }

    private function availableRoomsForPeriod(
        ?string $checkInDate,
        ?string $checkOutDate,
        ?int $ignoredReservationId = null,
    ) {
        $query = Room::query()->with('roomType')->orderBy('number');

        if (! $checkInDate || ! $checkOutDate) {
            return $query->get();
        }

        try {
            $checkIn = Carbon::parse($checkInDate)->startOfDay();
            $checkOut = Carbon::parse($checkOutDate)->startOfDay();
        } catch (\Throwable) {
            return $query->get();
        }

        if ($checkOut->lessThanOrEqualTo($checkIn)) {
            return $query->get();
        }

        return $query
            ->whereNotIn('status', [
                RoomStatus::Maintenance->value,
                RoomStatus::Occupied->value,
            ])
            ->whereDoesntHave('reservations', function ($reservationQuery) use ($checkIn, $checkOut, $ignoredReservationId): void {
                $reservationQuery
                    ->whereIn('status', [
                        ReservationStatus::Pending->value,
                        ReservationStatus::Confirmed->value,
                        ReservationStatus::CheckedIn->value,
                    ])
                    ->where('check_in_date', '<', $checkOut->toDateString())
                    ->where('check_out_date', '>', $checkIn->toDateString());

                if ($ignoredReservationId) {
                    $reservationQuery->whereKeyNot($ignoredReservationId);
                }
            })
            ->whereDoesntHave('stays', function ($stayQuery) use ($checkIn, $checkOut): void {
                $stayQuery
                    ->where('status', StayStatus::Active->value)
                    ->where('check_in_at', '<', $checkOut->copy()->endOfDay())
                    ->where(function ($nested) use ($checkIn): void {
                        $nested->whereNull('expected_check_out_at')
                            ->orWhere('expected_check_out_at', '>', $checkIn);
                    });
            })
            ->get();
    }
}
