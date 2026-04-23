<?php

namespace App\Services\Orders;

use App\Enums\CustomerType;
use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService
{
    public function create(array $payload): Order
    {
        return DB::transaction(function () use ($payload): Order {
            $order = Order::create([
                'reference' => $payload['reference'] ?? $this->generateReference(),
                'service_area_id' => Arr::get($payload, 'service_area_id'),
                'customer_id' => Arr::get($payload, 'customer_id'),
                'stay_id' => Arr::get($payload, 'stay_id'),
                'room_id' => Arr::get($payload, 'room_id'),
                'dining_table_id' => Arr::get($payload, 'dining_table_id'),
                'created_by' => Arr::get($payload, 'created_by'),
                'customer_type' => Arr::get($payload, 'customer_type', CustomerType::WalkInAnonymous->value),
                'status' => Arr::get($payload, 'status', OrderStatus::Draft->value),
                'notes' => Arr::get($payload, 'notes'),
            ]);

            foreach (Arr::get($payload, 'items', []) as $item) {
                $quantity = (float) Arr::get($item, 'quantity', 1);
                $unitPrice = (float) Arr::get($item, 'unit_price', 0);

                $order->items()->create([
                    'menu_id' => Arr::get($item, 'menu_id'),
                    'product_id' => Arr::get($item, 'product_id'),
                    'item_name' => Arr::get($item, 'item_name', 'Item'),
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'line_total' => $quantity * $unitPrice,
                ]);
            }

            return $this->recalculateTotals($order->fresh('items'));
        });
    }

    public function recalculateTotals(Order $order): Order
    {
        $subtotal = (float) $order->items->sum('line_total');
        $taxAmount = (float) $order->tax_amount;
        $discount = (float) $order->discount_amount;

        $order->forceFill([
            'subtotal' => $subtotal,
            'total' => max(0, $subtotal + $taxAmount - $discount),
        ])->save();

        return $order->fresh('items');
    }

    private function generateReference(): string
    {
        return 'ORD-' . now()->format('Ymd-His') . '-' . Str::upper(Str::random(4));
    }
}
