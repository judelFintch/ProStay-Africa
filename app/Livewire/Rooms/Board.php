<?php

namespace App\Livewire\Rooms;

use App\Enums\RoomStatus;
use App\Models\Room;
use Livewire\Component;

class Board extends Component
{
    public function setStatus(int $roomId, string $status): void
    {
        $allowed = array_column(RoomStatus::cases(), 'value');
        if (! in_array($status, $allowed, true)) {
            return;
        }

        Room::query()->findOrFail($roomId)->update([
            'status' => $status,
        ]);
    }

    public function render()
    {
        $rooms = Room::query()->with('roomType')->orderBy('number')->get();

        return view('livewire.rooms.board', [
            'rooms' => $rooms,
            'statuses' => array_column(RoomStatus::cases(), 'value'),
        ]);
    }
}
