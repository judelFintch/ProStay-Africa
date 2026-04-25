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
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

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

        Reservation::create([
            'customer_id' => $this->customer_id,
            'room_id' => $this->room_id,
            'check_in_date' => $this->check_in_date,
            'check_out_date' => $this->check_out_date,
            'adults' => $this->adults,
            'children' => $this->children,
            'status' => ReservationStatus::Confirmed->value,
            'notes' => $this->notes,
        ]);

        $this->reset(['customer_id', 'room_id', 'check_in_date', 'check_out_date', 'notes']);
        $this->adults = 1;
        $this->children = 0;
    }

    public function checkIn(int $reservationId): void
    {
        $reservation = Reservation::query()->with('room')->findOrFail($reservationId);

        if ($reservation->status === ReservationStatus::CheckedIn) {
            return;
        }

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
}
