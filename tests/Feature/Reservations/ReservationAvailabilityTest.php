<?php

namespace Tests\Feature\Reservations;

use App\Livewire\Reservations\Manager;
use App\Models\Customer;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\Stay;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ReservationAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_reception_cannot_create_overlapping_reservation_for_same_room(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $customer = Customer::query()->create([
            'full_name' => 'Client Reservation',
            'is_identified' => true,
        ]);
        $room = $this->createRoom();

        Reservation::query()->create([
            'customer_id' => $customer->id,
            'room_id' => $room->id,
            'check_in_date' => now()->addDay()->toDateString(),
            'check_out_date' => now()->addDays(3)->toDateString(),
            'adults' => 1,
            'children' => 0,
            'status' => 'confirmed',
        ]);

        Livewire::test(Manager::class)
            ->set('customer_id', $customer->id)
            ->set('room_id', $room->id)
            ->set('check_in_date', now()->addDays(2)->toDateString())
            ->set('check_out_date', now()->addDays(4)->toDateString())
            ->set('adults', 1)
            ->set('children', 0)
            ->call('createReservation')
            ->assertHasErrors(['room_id']);

        $this->assertDatabaseCount('reservations', 1);
    }

    public function test_reception_cannot_reserve_room_beyond_capacity(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $customer = Customer::query()->create([
            'full_name' => 'Famille Capacity',
            'is_identified' => true,
        ]);
        $room = $this->createRoom(capacity: 2);

        Livewire::test(Manager::class)
            ->set('customer_id', $customer->id)
            ->set('room_id', $room->id)
            ->set('check_in_date', now()->addDay()->toDateString())
            ->set('check_out_date', now()->addDays(2)->toDateString())
            ->set('adults', 2)
            ->set('children', 1)
            ->call('createReservation')
            ->assertHasErrors(['room_id']);

        $this->assertDatabaseCount('reservations', 0);
    }

    public function test_reception_can_create_reservation_when_room_is_available(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $customer = Customer::query()->create([
            'full_name' => 'Client Disponible',
            'is_identified' => true,
        ]);
        $room = $this->createRoom();

        Livewire::test(Manager::class)
            ->set('customer_id', $customer->id)
            ->set('room_id', $room->id)
            ->set('check_in_date', now()->addDay()->toDateString())
            ->set('check_out_date', now()->addDays(2)->toDateString())
            ->set('adults', 1)
            ->set('children', 0)
            ->call('createReservation')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('reservations', [
            'customer_id' => $customer->id,
            'room_id' => $room->id,
            'status' => 'confirmed',
        ]);
    }

    public function test_reception_cannot_create_reservation_over_active_stay(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $customer = Customer::query()->create([
            'full_name' => 'Client Actif',
            'is_identified' => true,
        ]);
        $room = $this->createRoom(status: 'available');

        Stay::query()->create([
            'customer_id' => $customer->id,
            'room_id' => $room->id,
            'check_in_at' => now()->addDay(),
            'expected_check_out_at' => now()->addDays(3),
            'status' => 'active',
            'nightly_rate' => 30000,
        ]);

        Livewire::test(Manager::class)
            ->set('customer_id', $customer->id)
            ->set('room_id', $room->id)
            ->set('check_in_date', now()->addDays(2)->toDateString())
            ->set('check_out_date', now()->addDays(4)->toDateString())
            ->set('adults', 1)
            ->set('children', 0)
            ->call('createReservation')
            ->assertHasErrors(['room_id']);

        $this->assertDatabaseCount('reservations', 0);
    }

    public function test_room_selector_filters_unavailable_rooms_for_selected_dates(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $customer = Customer::query()->create([
            'full_name' => 'Client Filtre',
            'is_identified' => true,
        ]);
        $bookedRoom = $this->createRoom(number: '401');
        $availableRoom = $this->createRoom(number: '402');

        Reservation::query()->create([
            'customer_id' => $customer->id,
            'room_id' => $bookedRoom->id,
            'check_in_date' => now()->addDay()->toDateString(),
            'check_out_date' => now()->addDays(3)->toDateString(),
            'adults' => 1,
            'children' => 0,
            'status' => 'confirmed',
        ]);

        Livewire::test(Manager::class)
            ->set('check_in_date', now()->addDays(2)->toDateString())
            ->set('check_out_date', now()->addDays(4)->toDateString())
            ->assertViewHas('rooms', fn ($rooms): bool => $rooms->contains($availableRoom)
                && ! $rooms->contains($bookedRoom));
    }

    public function test_edit_room_selector_keeps_current_reservation_room_available(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $customer = Customer::query()->create([
            'full_name' => 'Client Filtre Edition',
            'is_identified' => true,
        ]);
        $room = $this->createRoom(number: '501');

        $reservation = Reservation::query()->create([
            'customer_id' => $customer->id,
            'room_id' => $room->id,
            'check_in_date' => now()->addDay()->toDateString(),
            'check_out_date' => now()->addDays(3)->toDateString(),
            'adults' => 1,
            'children' => 0,
            'status' => 'confirmed',
        ]);

        Livewire::test(Manager::class)
            ->call('startEdit', $reservation->id)
            ->assertViewHas('editRooms', fn ($rooms): bool => $rooms->contains($room));
    }

    private function createRoom(int $capacity = 2, string $status = 'available', ?string $number = null): Room
    {
        $roomType = RoomType::query()->create([
            'name' => 'Standard',
            'code' => 'STD-'.uniqid(),
            'capacity' => $capacity,
            'base_price' => 30000,
        ]);

        return Room::query()->create([
            'room_type_id' => $roomType->id,
            'number' => $number ?? (string) random_int(100, 999),
            'capacity' => $capacity,
            'price' => 30000,
            'status' => $status,
        ]);
    }
}
