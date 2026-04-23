<?php

namespace App\Services\Orders;

use App\Enums\CustomerType;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use App\Services\Audit\AuditLogger;
use App\Services\Stock\StockService;
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
                $productId = Arr::get($item, 'product_id');

                $createdItem = $order->items()->create([
                    'menu_id' => Arr::get($item, 'menu_id'),
                    'product_id' => $productId,
                    'item_name' => Arr::get($item, 'item_name', 'Item'),
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'line_total' => $quantity * $unitPrice,
                ]);

                // Deduct stock automatically when the order item is linked to a product.
                if ($productId) {
                    $product = Product::query()->find($productId);
                    if ($product) {
                        app(StockService::class)->moveOut(
                            product: $product,
                            quantity: $quantity,
                            unitCost: (float) $product->unit_cost,
                            userId: Arr::get($payload, 'created_by'),
                            reason: 'Order ' . $order->reference . ' / item #' . $createdItem->id
                        );
                    }
                }
            }

            $freshOrder = $this->recalculateTotals($order->fresh('items'));

            app(AuditLogger::class)->log(
                action: 'order.created',
                entityType: 'order',
                entityId: $freshOrder->id,
                newValues: [
                    'reference' => $freshOrder->reference,
                    'customer_type' => $freshOrder->customer_type->value,
                    'total' => $freshOrder->total,
                ]
            );

            return $freshOrder;
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
