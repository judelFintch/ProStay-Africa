<?php

namespace App\Livewire\Pos;

use App\Models\Customer;
use App\Models\ServiceArea;
use App\Services\Pos\PosService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class QuickSale extends Component
{
    public ?int $service_area_id = null;
    public ?int $customer_id = null;
    public string $payment_method = 'cash';
    public array $items = [
        ['item_name' => '', 'quantity' => 1, 'unit_price' => 0],
    ];

    public function addItem(): void
    {
        $this->items[] = ['item_name' => '', 'quantity' => 1, 'unit_price' => 0];
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function submit(PosService $posService): void
    {
        $this->validate([
            'service_area_id' => ['nullable', 'exists:service_areas,id'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'payment_method' => ['required', 'in:cash,mobile_money,card,bank_transfer'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_name' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $order = $posService->quickSale([
            'service_area_id' => $this->service_area_id,
            'customer_id' => $this->customer_id,
            'payment_method' => $this->payment_method,
            'items' => $this->items,
            'created_by' => Auth::id(),
        ]);

        $this->dispatch('pos-sale-done', reference: $order->reference);
        $this->reset(['customer_id']);
        $this->payment_method = 'cash';
        $this->items = [['item_name' => '', 'quantity' => 1, 'unit_price' => 0]];
    }

    public function render()
    {
        return view('livewire.pos.quick-sale', [
            'areas' => ServiceArea::query()->whereIn('code', ['restaurant', 'bar', 'terrace', 'pos'])->orderBy('name')->get(),
            'customers' => Customer::query()->orderBy('full_name')->limit(100)->get(),
        ]);
    }
}
