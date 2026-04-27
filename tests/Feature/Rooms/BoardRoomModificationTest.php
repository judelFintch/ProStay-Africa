<?php

namespace Tests\Feature\Rooms;

use App\Livewire\Rooms\Board;
use App\Models\AuditLog;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BoardRoomModificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_reception_can_edit_room_and_history_is_logged(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $standardType = RoomType::query()->create([
            'name' => 'Standard',
            'code' => 'STD-RM-01',
            'capacity' => 2,
            'base_price' => 70,
        ]);

        $deluxeType = RoomType::query()->create([
            'name' => 'Deluxe',
            'code' => 'DLX-RM-01',
            'capacity' => 4,
            'base_price' => 120,
        ]);

        $room = Room::query()->create([
            'room_type_id' => $standardType->id,
            'number' => '401',
            'floor' => '4',
            'capacity' => 2,
            'price' => 70,
            'status' => 'available',
        ]);

        Livewire::test(Board::class)
            ->call('startEditRoom', $room->id)
            ->set('edit_room_type_id', $deluxeType->id)
            ->set('edit_number', '401B')
            ->set('edit_floor', '4A')
            ->set('edit_capacity', 3)
            ->set('edit_price', '135.50')
            ->set('edit_status', 'maintenance')
            ->call('saveRoomChanges')
            ->assertHasNoErrors();

        $room->refresh();

        $this->assertSame($deluxeType->id, $room->room_type_id);
        $this->assertSame('401B', $room->number);
        $this->assertSame('4A', $room->floor);
        $this->assertSame(3, $room->capacity);
        $this->assertSame('maintenance', $room->status->value);
        $this->assertSame(135.5, (float) $room->price);

        $log = AuditLog::query()
            ->where('entity_type', 'room')
            ->where('entity_id', $room->id)
            ->where('action', 'room_updated')
            ->latest('id')
            ->first();

        $this->assertNotNull($log);
        $this->assertSame('available', $log->old_values['status']);
        $this->assertSame('maintenance', $log->new_values['status']);
        $this->assertSame('401', $log->old_values['number']);
        $this->assertSame('401B', $log->new_values['number']);
        $this->assertSame('edit_form', $log->new_values['source']);
    }

    public function test_quick_status_change_is_logged_for_room_history(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $roomType = RoomType::query()->create([
            'name' => 'Standard',
            'code' => 'STD-RM-02',
            'capacity' => 2,
            'base_price' => 80,
        ]);

        $room = Room::query()->create([
            'room_type_id' => $roomType->id,
            'number' => '402',
            'floor' => '4',
            'capacity' => 2,
            'price' => 80,
            'status' => 'available',
        ]);

        Livewire::test(Board::class)
            ->call('setStatus', $room->id, 'cleaning')
            ->assertHasNoErrors();

        $room->refresh();
        $this->assertSame('cleaning', $room->status->value);

        $log = AuditLog::query()
            ->where('entity_type', 'room')
            ->where('entity_id', $room->id)
            ->where('action', 'room_status_updated')
            ->latest('id')
            ->first();

        $this->assertNotNull($log);
        $this->assertSame('available', $log->old_values['status']);
        $this->assertSame('cleaning', $log->new_values['status']);
        $this->assertSame('quick_action', $log->new_values['source']);
    }
}
