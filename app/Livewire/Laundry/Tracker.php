<?php

namespace App\Livewire\Laundry;

use App\Enums\LaundryItemStatus;
use App\Models\LaundryItem;
use App\Models\LaundryOperation;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Tracker extends Component
{
    public string $name = '';
    public int $quantity = 1;

    public function createItem(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        LaundryItem::query()->create([
            'name' => $this->name,
            'quantity' => $this->quantity,
            'status' => LaundryItemStatus::Dirty->value,
        ]);

        $this->reset(['name']);
        $this->quantity = 1;
    }

    public function moveStatus(int $itemId, string $toStatus): void
    {
        $allowed = array_column(LaundryItemStatus::cases(), 'value');
        if (! in_array($toStatus, $allowed, true)) {
            return;
        }

        $item = LaundryItem::query()->findOrFail($itemId);
        $from = $item->status->value;

        if ($from === $toStatus) {
            return;
        }

        $item->update(['status' => $toStatus]);

        LaundryOperation::query()->create([
            'laundry_item_id' => $item->id,
            'user_id' => Auth::id(),
            'from_status' => $from,
            'to_status' => $toStatus,
            'quantity' => $item->quantity,
            'notes' => 'Status changed from tracker',
        ]);
    }

    public function render()
    {
        return view('livewire.laundry.tracker', [
            'items' => LaundryItem::query()->latest()->limit(80)->get(),
            'operations' => LaundryOperation::query()->with(['laundryItem', 'user'])->latest()->limit(25)->get(),
            'statuses' => array_column(LaundryItemStatus::cases(), 'value'),
        ]);
    }
}
