<?php

namespace App\Livewire\Orders;

use App\Enums\CustomerType;
use App\Enums\OrderStatus;
use App\Models\Customer;
use App\Models\Order;
use App\Models\ServiceArea;
use App\Services\Orders\OrderService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CreateOrder extends Component
{
    public ?int $service_area_id = null;
    public ?int $customer_id = null;
    public string $customer_type = 'walk_in_anonymous';
    public array $items = [
        ['item_name' => '', 'quantity' => 1, 'unit_price' => 0],
    ];
    public ?string $notes = null;

    public function addItemRow(): void
    {
        $this->items[] = ['item_name' => '', 'quantity' => 1, 'unit_price' => 0];
    }

    public function removeItemRow(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function save(OrderService $orderService): void
    {
        $this->validate([
            'service_area_id' => ['nullable', 'exists:service_areas,id'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'customer_type' => ['required', 'in:' . implode(',', array_column(CustomerType::cases(), 'value'))],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_name' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $order = $orderService->create([
            'service_area_id' => $this->service_area_id,
            'customer_id' => $this->customer_id,
            'customer_type' => $this->customer_type,
            'status' => OrderStatus::Confirmed->value,
            'items' => $this->items,
            'notes' => $this->notes,
            'created_by' => Auth::id(),
        ]);

        $this->dispatch('order-created', reference: $order->reference);
        $this->reset(['customer_id', 'notes']);
        $this->customer_type = CustomerType::WalkInAnonymous->value;
        $this->items = [['item_name' => '', 'quantity' => 1, 'unit_price' => 0]];
    }

    public function render()
    {
        return view('livewire.orders.create-order', [
            'serviceAreas' => ServiceArea::query()->where('is_active', true)->orderBy('name')->get(),
            'customers' => Customer::query()->orderBy('full_name')->limit(100)->get(),
            'recentOrders' => Order::query()->latest()->limit(10)->get(),
            'customerTypes' => array_column(CustomerType::cases(), 'value'),
        ]);
    }
}
