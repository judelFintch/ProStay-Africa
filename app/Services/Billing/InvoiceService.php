<?php

namespace App\Services\Billing;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InvoiceService
{
    /**
     * @param list<Order> $orders
     */
    public function createFromOrders(array $orders, array $attributes = []): Invoice
    {
        return DB::transaction(function () use ($orders, $attributes): Invoice {
            $invoice = Invoice::create([
                'reference' => $attributes['reference'] ?? $this->generateReference(),
                'customer_id' => $attributes['customer_id'] ?? $orders[0]->customer_id ?? null,
                'stay_id' => $attributes['stay_id'] ?? $orders[0]->stay_id ?? null,
                'room_id' => $attributes['room_id'] ?? $orders[0]->room_id ?? null,
                'issued_by' => $attributes['issued_by'] ?? null,
                'issued_at' => now(),
                'due_at' => $attributes['due_at'] ?? null,
                'status' => $attributes['status'] ?? InvoiceStatus::Unpaid->value,
                'tax_amount' => $attributes['tax_amount'] ?? 0,
                'discount_amount' => $attributes['discount_amount'] ?? 0,
                'notes' => $attributes['notes'] ?? null,
            ]);

            foreach ($orders as $order) {
                foreach ($order->items as $orderItem) {
                    $invoice->items()->create([
                        'order_item_id' => $orderItem->id,
                        'description' => $orderItem->item_name,
                        'quantity' => $orderItem->quantity,
                        'unit_price' => $orderItem->unit_price,
                        'line_total' => $orderItem->line_total,
                    ]);
                }
            }

            return $this->recalculateTotals($invoice->fresh('items'));
        });
    }

    public function recalculateTotals(Invoice $invoice): Invoice
    {
        $subtotal = (float) $invoice->items->sum('line_total');
        $taxAmount = (float) $invoice->tax_amount;
        $discount = (float) $invoice->discount_amount;
        $total = max(0, $subtotal + $taxAmount - $discount);
        $paid = (float) $invoice->payments()->sum('amount');
        $balance = max(0, $total - $paid);

        $status = InvoiceStatus::Unpaid;
        if ($total === 0) {
            $status = InvoiceStatus::Paid;
        } elseif ($paid > 0 && $balance > 0) {
            $status = InvoiceStatus::PartiallyPaid;
        } elseif ($balance == 0.0) {
            $status = InvoiceStatus::Paid;
        }

        $invoice->forceFill([
            'subtotal' => $subtotal,
            'total' => $total,
            'paid_total' => $paid,
            'balance' => $balance,
            'status' => $status,
        ])->save();

        return $invoice->fresh(['items', 'payments']);
    }

    private function generateReference(): string
    {
        return 'INV-' . now()->format('Ymd-His') . '-' . Str::upper(Str::random(4));
    }
}
