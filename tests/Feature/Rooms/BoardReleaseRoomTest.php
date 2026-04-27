<?php

namespace Tests\Feature\Rooms;

use App\Livewire\Rooms\Board;
use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\Stay;
use App\Models\User;
use App\Services\Billing\InvoiceService;
use App\Services\Billing\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BoardReleaseRoomTest extends TestCase
{
    use RefreshDatabase;

    public function test_releasing_room_checks_out_active_stay_and_reservation(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        [$room, $reservation, $stay] = $this->createRoomWithActiveStay();

        $invoice = app(InvoiceService::class)->openFolderForStay($stay, [
            'customer_id' => $stay->customer_id,
            'room_id' => $stay->room_id,
            'issued_by' => $user->id,
        ]);

        app(PaymentService::class)->record([
            'invoice_id' => $invoice->id,
            'customer_id' => $stay->customer_id,
            'recorded_by' => $user->id,
            'amount' => $invoice->balance,
            'method' => 'cash',
        ]);

        Livewire::test(Board::class)
            ->call('setStatus', $room->id, 'available')
            ->assertHasNoErrors();

        $this->assertSame('available', $room->fresh()->status->value);
        $this->assertSame('checked_out', $stay->fresh()->status->value);
        $this->assertNotNull($stay->fresh()->check_out_at);
        $this->assertSame('checked_out', $reservation->fresh()->status->value);

        $log = AuditLog::query()
            ->where('entity_type', 'room')
            ->where('entity_id', $room->id)
            ->where('action', 'room_status_updated')
            ->latest('id')
            ->first();

        $this->assertNotNull($log);
        $this->assertSame('occupied', $log->old_values['status']);
        $this->assertSame('available', $log->new_values['status']);
        $this->assertSame($stay->id, $log->new_values['side_effects']['stay_closed_id']);
        $this->assertSame($reservation->id, $log->new_values['side_effects']['reservation_closed_id']);
    }

    public function test_releasing_room_is_blocked_when_active_stay_invoice_is_unpaid(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        [$room, $reservation, $stay] = $this->createRoomWithActiveStay();

        app(InvoiceService::class)->openFolderForStay($stay, [
            'customer_id' => $stay->customer_id,
            'room_id' => $stay->room_id,
            'issued_by' => $user->id,
        ]);

        Livewire::test(Board::class)
            ->call('setStatus', $room->id, 'available')
            ->assertHasErrors(['room_action']);

        $this->assertSame('occupied', $room->fresh()->status->value);
        $this->assertSame('active', $stay->fresh()->status->value);
        $this->assertSame('checked_in', $reservation->fresh()->status->value);
    }

    public function test_releasing_room_closes_checked_in_reservation_without_stay(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        [$room, $reservation] = $this->createRoomWithCheckedInReservationOnly();

        Livewire::test(Board::class)
            ->call('setStatus', $room->id, 'available')
            ->assertHasNoErrors();

        $this->assertSame('available', $room->fresh()->status->value);
        $this->assertSame('checked_out', $reservation->fresh()->status->value);

        $log = AuditLog::query()
            ->where('entity_type', 'room')
            ->where('entity_id', $room->id)
            ->where('action', 'room_status_updated')
            ->latest('id')
            ->first();

        $this->assertNotNull($log);
        $this->assertSame('occupied', $log->old_values['status']);
        $this->assertSame('available', $log->new_values['status']);
        $this->assertSame($reservation->id, $log->new_values['side_effects']['reservation_closed_id']);
    }

    /**
     * @return array{Room, Reservation, Stay}
     */
    private function createRoomWithActiveStay(): array
    {
        $customer = Customer::query()->create([
            'full_name' => 'Client Rooms Board',
            'is_identified' => true,
        ]);

        $roomType = RoomType::query()->create([
            'name' => 'Executive',
            'code' => 'EXEC-ROOMS',
            'capacity' => 2,
            'base_price' => 80,
        ]);

        $room = Room::query()->create([
            'room_type_id' => $roomType->id,
            'number' => '301',
            'floor' => 3,
            'capacity' => 2,
            'price' => 80,
            'status' => 'occupied',
        ]);

        $reservation = Reservation::query()->create([
            'customer_id' => $customer->id,
            'room_id' => $room->id,
            'check_in_date' => now()->subDay()->toDateString(),
            'check_out_date' => now()->addDay()->toDateString(),
            'adults' => 1,
            'children' => 0,
            'status' => 'checked_in',
        ]);

        $stay = Stay::query()->create([
            'customer_id' => $customer->id,
            'room_id' => $room->id,
            'reservation_id' => $reservation->id,
            'check_in_at' => now()->subDay(),
            'expected_check_out_at' => now()->addDay(),
            'status' => 'active',
            'nightly_rate' => 80,
        ]);

        return [$room, $reservation, $stay];
    }

    /**
     * @return array{Room, Reservation}
     */
    private function createRoomWithCheckedInReservationOnly(): array
    {
        $customer = Customer::query()->create([
            'full_name' => 'Client Reservation Only',
            'is_identified' => true,
        ]);

        $roomType = RoomType::query()->create([
            'name' => 'Standard',
            'code' => 'STD-ROOMS',
            'capacity' => 2,
            'base_price' => 60,
        ]);

        $room = Room::query()->create([
            'room_type_id' => $roomType->id,
            'number' => '302',
            'floor' => 3,
            'capacity' => 2,
            'price' => 60,
            'status' => 'occupied',
        ]);

        $reservation = Reservation::query()->create([
            'customer_id' => $customer->id,
            'room_id' => $room->id,
            'check_in_date' => now()->toDateString(),
            'check_out_date' => now()->addDay()->toDateString(),
            'adults' => 1,
            'children' => 0,
            'status' => 'checked_in',
        ]);

        return [$room, $reservation];
    }
}
