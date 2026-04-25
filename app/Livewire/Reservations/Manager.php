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
        Reservation::query()->findOrFail($reservationId)->update([
            'status' => ReservationStatus::Cancelled->value,
        ]);
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
        return view('livewire.reservations.manager', [
            'customers' => Customer::query()->orderBy('full_name')->limit(150)->get(),
            'rooms' => Room::query()->orderBy('number')->get(),
            'reservations' => Reservation::query()->with(['customer', 'room'])->latest()->limit(20)->get(),
            'activeStays' => Stay::query()->with(['customer', 'room', 'invoices'])->where('status', StayStatus::Active->value)->latest()->limit(20)->get(),
            'checkedInValue' => ReservationStatus::CheckedIn->value,
        ]);
    }

    private function ensureRoomCanBeReserved(Room $room, Carbon $checkIn, Carbon $checkOut): void
    {
        $guestCount = $this->adults + $this->children;

        if ($guestCount > (int) $room->capacity) {
            throw new RuntimeException('La capacite de la chambre est insuffisante pour le nombre de voyageurs.');
        }

        if ($room->status === RoomStatus::Maintenance) {
            throw new RuntimeException('Cette chambre est actuellement en maintenance.');
        }

        if ($room->status === RoomStatus::Occupied) {
            throw new RuntimeException('Cette chambre est actuellement occupee.');
        }

        $hasReservationConflict = Reservation::query()
            ->where('room_id', $room->id)
            ->whereIn('status', [
                ReservationStatus::Pending->value,
                ReservationStatus::Confirmed->value,
                ReservationStatus::CheckedIn->value,
            ])
            ->where('check_in_date', '<', $checkOut->toDateString())
            ->where('check_out_date', '>', $checkIn->toDateString())
            ->exists();

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
}
