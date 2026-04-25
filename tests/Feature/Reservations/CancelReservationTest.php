<?php

namespace Tests\Feature\Reservations;

use App\Livewire\Reservations\Manager;
use App\Models\Customer;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CancelReservationTest extends TestCase
{
    use RefreshDatabase;

    public function test_confirmed_reservation_can_be_cancelled(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $reservation = $this->createReservation(status: 'confirmed');

        Livewire::test(Manager::class)
            ->call('cancel', $reservation->id)
            ->assertHasNoErrors();

        $this->assertSame('cancelled', $reservation->fresh()->status->value);
    }

    public function test_checked_in_reservation_cannot_be_cancelled(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $reservation = $this->createReservation(status: 'checked_in');

        Livewire::test(Manager::class)
            ->call('cancel', $reservation->id)
            ->assertHasErrors(['reservation_action']);

        $this->assertSame('checked_in', $reservation->fresh()->status->value);
    }

    public function test_no_show_reservation_cannot_be_cancelled(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $reservation = $this->createReservation(status: 'no_show');

        Livewire::test(Manager::class)
            ->call('cancel', $reservation->id)
            ->assertHasErrors(['reservation_action']);

        $this->assertSame('no_show', $reservation->fresh()->status->value);
    }

    private function createReservation(string $status): Reservation
    {
        $customer = Customer::query()->create([
            'full_name' => 'Client Annulation',
            'is_identified' => true,
        ]);

        $roomType = RoomType::query()->create([
            'name' => 'Standard',
            'code' => 'STD-CANCEL-'.uniqid(),
            'capacity' => 2,
            'base_price' => 30000,
        ]);

        $room = Room::query()->create([
            'room_type_id' => $roomType->id,
            'number' => (string) random_int(100, 999),
            'capacity' => 2,
            'price' => 30000,
            'status' => 'available',
        ]);

        return Reservation::query()->create([
            'customer_id' => $customer->id,
            'room_id' => $room->id,
            'check_in_date' => now()->addDay()->toDateString(),
            'check_out_date' => now()->addDays(2)->toDateString(),
            'adults' => 1,
            'children' => 0,
            'status' => $status,
        ]);
    }
}
