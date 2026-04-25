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

class CheckInTest extends TestCase
{
    use RefreshDatabase;

    public function test_confirmed_reservation_can_be_checked_in(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        [$reservation, $room] = $this->createReservation();

        Livewire::test(Manager::class)
            ->call('checkIn', $reservation->id)
            ->assertHasNoErrors();

        $reservation->refresh();

        $this->assertSame('checked_in', $reservation->status->value);
        $this->assertSame('occupied', $room->fresh()->status->value);
        $this->assertDatabaseHas('stays', [
            'reservation_id' => $reservation->id,
            'customer_id' => $reservation->customer_id,
            'room_id' => $room->id,
            'status' => 'active',
        ]);
    }

    public function test_check_in_is_blocked_when_room_has_active_stay(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        [$reservation, $room] = $this->createReservation();
        $otherCustomer = Customer::query()->create([
            'full_name' => 'Client Deja Loge',
            'is_identified' => true,
        ]);

        Stay::query()->create([
            'customer_id' => $otherCustomer->id,
            'room_id' => $room->id,
            'check_in_at' => now()->subHour(),
            'expected_check_out_at' => now()->addDay(),
            'status' => 'active',
            'nightly_rate' => 30000,
        ]);

        Livewire::test(Manager::class)
            ->call('checkIn', $reservation->id)
            ->assertHasErrors(['checkin']);

        $this->assertSame('confirmed', $reservation->fresh()->status->value);
        $this->assertDatabaseMissing('stays', [
            'reservation_id' => $reservation->id,
        ]);
    }

    public function test_cancelled_reservation_cannot_be_checked_in(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        [$reservation] = $this->createReservation(status: 'cancelled');

        Livewire::test(Manager::class)
            ->call('checkIn', $reservation->id)
            ->assertHasErrors(['checkin']);

        $this->assertDatabaseMissing('stays', [
            'reservation_id' => $reservation->id,
        ]);
    }

    public function test_no_show_reservation_cannot_be_checked_in(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        [$reservation] = $this->createReservation(status: 'confirmed');

        Livewire::test(Manager::class)
            ->call('markNoShow', $reservation->id)
            ->assertHasNoErrors()
            ->call('checkIn', $reservation->id)
            ->assertHasErrors(['checkin']);

        $this->assertSame('no_show', $reservation->fresh()->status->value);
        $this->assertDatabaseMissing('stays', [
            'reservation_id' => $reservation->id,
        ]);
    }

    public function test_checked_in_reservation_cannot_be_marked_no_show(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        [$reservation] = $this->createReservation(status: 'checked_in');

        Livewire::test(Manager::class)
            ->call('markNoShow', $reservation->id)
            ->assertHasErrors(['reservation_action']);

        $this->assertSame('checked_in', $reservation->fresh()->status->value);
    }

    /**
     * @return array{Reservation, Room}
     */
    private function createReservation(string $status = 'confirmed'): array
    {
        $customer = Customer::query()->create([
            'full_name' => 'Client Checkin',
            'is_identified' => true,
        ]);

        $roomType = RoomType::query()->create([
            'name' => 'Standard',
            'code' => 'STD-CHECKIN-'.uniqid(),
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

        $reservation = Reservation::query()->create([
            'customer_id' => $customer->id,
            'room_id' => $room->id,
            'check_in_date' => now()->toDateString(),
            'check_out_date' => now()->addDay()->toDateString(),
            'adults' => 1,
            'children' => 0,
            'status' => $status,
        ]);

        return [$reservation, $room];
    }
}
