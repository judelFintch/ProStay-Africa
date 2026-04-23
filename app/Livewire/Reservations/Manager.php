<?php

namespace App\Livewire\Reservations;

use App\Enums\ReservationStatus;
use App\Enums\RoomStatus;
use App\Enums\StayStatus;
use App\Models\Customer;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\Stay;
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

    public function render()
    {
        return view('livewire.reservations.manager', [
            'customers' => Customer::query()->orderBy('full_name')->limit(150)->get(),
            'rooms' => Room::query()->orderBy('number')->get(),
            'reservations' => Reservation::query()->with(['customer', 'room'])->latest()->limit(20)->get(),
            'checkedInValue' => ReservationStatus::CheckedIn->value,
        ]);
    }
}
