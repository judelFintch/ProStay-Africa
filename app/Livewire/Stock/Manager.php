<?php

namespace App\Livewire\Stock;

use App\Models\Product;
use App\Models\StockMovement;
use App\Services\Stock\StockService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Manager extends Component
{
    public ?int $product_id = null;
    public string $movement_type = 'in';
    public float $quantity = 1;
    public float $unit_cost = 0;
    public ?string $reason = null;

    public function saveMovement(StockService $stockService): void
    {
        $this->validate([
            'product_id' => ['required', 'exists:products,id'],
            'movement_type' => ['required', 'in:in,out'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $product = Product::query()->findOrFail($this->product_id);

        if ($this->movement_type === 'out') {
            $stockService->moveOut($product, $this->quantity, $this->unit_cost, Auth::id(), $this->reason);
        } else {
            $stockService->moveIn($product, $this->quantity, $this->unit_cost, Auth::id(), $this->reason);
        }

        $this->reset(['product_id', 'reason']);
        $this->movement_type = 'in';
        $this->quantity = 1;
        $this->unit_cost = 0;
    }

    public function render()
    {
        return view('livewire.stock.manager', [
            'products' => Product::query()->with(['category', 'supplier'])->orderBy('name')->limit(200)->get(),
            'alerts' => Product::query()->whereColumn('stock_quantity', '<=', 'alert_threshold')->orderBy('stock_quantity')->limit(20)->get(),
            'movements' => StockMovement::query()->with(['product', 'user'])->latest()->limit(25)->get(),
        ]);
    }
}
