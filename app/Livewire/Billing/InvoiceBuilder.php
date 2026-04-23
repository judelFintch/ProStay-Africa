<?php

namespace App\Livewire\Billing;

use App\Models\Order;
use App\Services\Billing\InvoiceService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class InvoiceBuilder extends Component
{
    public array $selectedOrderIds = [];
    public ?int $customer_id = null;
    public ?int $stay_id = null;
    public ?int $room_id = null;

    public function build(InvoiceService $invoiceService): void
    {
        $this->validate([
            'selectedOrderIds' => ['required', 'array', 'min:1'],
            'selectedOrderIds.*' => ['exists:orders,id'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'stay_id' => ['nullable', 'exists:stays,id'],
            'room_id' => ['nullable', 'exists:rooms,id'],
        ]);

        $orders = Order::query()->with('items')->whereIn('id', $this->selectedOrderIds)->get()->all();

        $invoice = $invoiceService->createFromOrders($orders, [
            'customer_id' => $this->customer_id,
            'stay_id' => $this->stay_id,
            'room_id' => $this->room_id,
            'issued_by' => Auth::id(),
        ]);

        $this->dispatch('invoice-created', reference: $invoice->reference);
        $this->reset('selectedOrderIds');
    }

    public function render()
    {
        return view('livewire.billing.invoice-builder', [
            'openOrders' => Order::query()->with('items')->whereIn('status', ['confirmed', 'served', 'closed'])->latest()->limit(30)->get(),
        ]);
    }
}
