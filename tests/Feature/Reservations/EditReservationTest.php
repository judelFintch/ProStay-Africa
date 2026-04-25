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

class EditReservationTest extends TestCase
{
    use RefreshDatabase;

    public function test_reception_can_edit_confirmed_reservation(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $roomA = $this->createRoom('101');
        $roomB = $this->createRoom('102');
        $reservation = $this->createReservation($roomA);

        Livewire::test(Manager::class)
            ->call('startEdit', $reservation->id)
            ->assertSet('edit_reservation_id', $reservation->id)
            ->set('edit_room_id', $roomB->id)
            ->set('edit_check_in_date', now()->addDays(2)->toDateString())
            ->set('edit_check_out_date', now()->addDays(4)->toDateString())
            ->set('edit_adults', 2)
            ->set('edit_children', 0)
            ->set('edit_notes', 'Changed dates')
            ->call('updateReservation')
            ->assertHasNoErrors()
            ->assertSet('edit_reservation_id', null);

        $reservation->refresh();

        $this->assertSame($roomB->id, $reservation->room_id);
        $this->assertSame(now()->addDays(2)->toDateString(), $reservation->check_in_date->toDateString());
        $this->assertSame(now()->addDays(4)->toDateString(), $reservation->check_out_date->toDateString());
        $this->assertSame(2, $reservation->adults);
        $this->assertSame('Changed dates', $reservation->notes);
    }

    public function test_reception_cannot_edit_reservation_into_conflicting_room_period(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $roomA = $this->createRoom('201');
        $roomB = $this->createRoom('202');
        $reservation = $this->createReservation($roomA);
        $otherReservation = $this->createReservation(
            room: $roomB,
            checkIn: now()->addDays(2)->toDateString(),
            checkOut: now()->addDays(5)->toDateString(),
        );

        Livewire::test(Manager::class)
            ->call('startEdit', $reservation->id)
            ->set('edit_room_id', $roomB->id)
            ->set('edit_check_in_date', now()->addDays(3)->toDateString())
            ->set('edit_check_out_date', now()->addDays(4)->toDateString())
            ->call('updateReservation')
            ->assertHasErrors(['edit_room_id']);

        $this->assertSame($roomA->id, $reservation->fresh()->room_id);
        $this->assertSame($roomB->id, $otherReservation->fresh()->room_id);
    }

    public function test_checked_in_reservation_cannot_be_edited(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $reservation = $this->createReservation($this->createRoom('301'), status: 'checked_in');

        Livewire::test(Manager::class)
            ->call('startEdit', $reservation->id)
            ->assertHasErrors(['reservation_action'])
            ->assertSet('edit_reservation_id', null);
    }

    private function createReservation(
        Room $room,
        string $status = 'confirmed',
        ?string $checkIn = null,
        ?string $checkOut = null,
    ): Reservation {
        $customer = Customer::query()->create([
            'full_name' => 'Client Edition',
            'is_identified' => true,
        ]);

        return Reservation::query()->create([
            'customer_id' => $customer->id,
            'room_id' => $room->id,
            'check_in_date' => $checkIn ?? now()->addDay()->toDateString(),
            'check_out_date' => $checkOut ?? now()->addDays(3)->toDateString(),
            'adults' => 1,
            'children' => 0,
            'status' => $status,
        ]);
    }

    private function createRoom(string $number, int $capacity = 2, string $status = 'available'): Room
    {
        $roomType = RoomType::query()->create([
            'name' => 'Standard '.$number,
            'code' => 'STD-EDIT-'.$number,
            'capacity' => $capacity,
            'base_price' => 30000,
        ]);

        return Room::query()->create([
            'room_type_id' => $roomType->id,
            'number' => $number,
            'capacity' => $capacity,
            'price' => 30000,
            'status' => $status,
        ]);
    }
}
