<?php

namespace App\Services\Billing;

use App\Enums\InvoiceStatus;
use App\Enums\CurrencyCode;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Order;
use App\Models\Stay;
use App\Services\Audit\AuditLogger;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class InvoiceService
{
    public function openFolderForOrder(Order $order, array $attributes = []): Invoice
    {
        return DB::transaction(function () use ($order, $attributes): Invoice {
            $invoice = $this->findReusableInvoice($order, $attributes);

            if (! $invoice) {
                return $this->createFromOrders([$order->fresh('items')], [
                    'customer_id' => $attributes['customer_id'] ?? $order->customer_id,
                    'stay_id' => $attributes['stay_id'] ?? $order->stay_id,
                    'room_id' => $attributes['room_id'] ?? $order->room_id,
                    'currency' => $attributes['currency'] ?? $order->currency ?? CurrencyCode::default(),
                    'issued_by' => $attributes['issued_by'] ?? null,
                    'status' => $attributes['status'] ?? InvoiceStatus::Draft->value,
                    'notes' => $attributes['notes'] ?? 'Dossier de facturation ouvert depuis la prise de commande.',
                ]);
            }

            if ($order->currency && strtoupper((string) $invoice->currency) !== strtoupper((string) $order->currency)) {
                throw new RuntimeException('Impossible de melanger des commandes de devises differentes sur une meme facture.');
            }

            $this->appendOrderToInvoice($invoice, $order);

            return $this->recalculateTotals($invoice->fresh('items'));
        });
    }

    public function openFolderForStay(Stay $stay, array $attributes = []): Invoice
    {
        return DB::transaction(function () use ($stay, $attributes): Invoice {
            $invoice = Invoice::query()
                ->where('stay_id', $stay->id)
                ->whereIn('status', [
                    InvoiceStatus::Draft->value,
                    InvoiceStatus::Unpaid->value,
                    InvoiceStatus::PartiallyPaid->value,
                    InvoiceStatus::Paid->value,
                ])
                ->latest('issued_at')
                ->first();

            if (! $invoice) {
                $invoice = Invoice::create([
                    'reference' => $attributes['reference'] ?? $this->generateReference(),
                    'customer_id' => $attributes['customer_id'] ?? $stay->customer_id,
                    'stay_id' => $stay->id,
                    'room_id' => $attributes['room_id'] ?? $stay->room_id,
                    'issued_by' => $attributes['issued_by'] ?? null,
                    'issued_at' => now(),
                    'due_at' => $attributes['due_at'] ?? null,
                    'status' => $attributes['status'] ?? InvoiceStatus::Unpaid->value,
                    'currency' => strtoupper((string) ($attributes['currency'] ?? CurrencyCode::default())),
                    'tax_amount' => $attributes['tax_amount'] ?? 0,
                    'discount_amount' => $attributes['discount_amount'] ?? 0,
                    'notes' => $attributes['notes'] ?? 'Dossier de facturation du sejour.',
                ]);
            }

            return $this->syncAccommodationCharge($invoice, $stay);
        });
    }

    public function syncAccommodationCharge(Invoice $invoice, Stay $stay): Invoice
    {
        $stay->loadMissing('room');

        $nights = $this->billableNights($stay);
        $nightlyRate = (float) $stay->nightly_rate;
        $description = $this->accommodationDescription($stay);

        $item = $invoice->items()
            ->whereNull('order_item_id')
            ->where('description', $description)
            ->first();

        if ($nights <= 0 || $nightlyRate <= 0) {
            if ($item) {
                $item->delete();
            }

            return $this->recalculateTotals($invoice->fresh('items'));
        }

        $invoice->items()->updateOrCreate(
            [
                'order_item_id' => null,
                'description' => $description,
            ],
            [
                'quantity' => $nights,
                'unit_price' => $nightlyRate,
                'line_total' => $nights * $nightlyRate,
            ]
        );

        return $this->recalculateTotals($invoice->fresh('items'));
    }

    /**
     * @param  list<Order>  $orders
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
                'currency' => strtoupper((string) ($attributes['currency'] ?? ($orders[0]->currency ?? CurrencyCode::default()))),
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

            $freshInvoice = $this->recalculateTotals($invoice->fresh('items'));

            app(AuditLogger::class)->log(
                action: 'invoice.created',
                entityType: 'invoice',
                entityId: $freshInvoice->id,
                newValues: [
                    'reference' => $freshInvoice->reference,
                    'status' => $freshInvoice->status->value,
                    'total' => $freshInvoice->total,
                ]
            );

            return $freshInvoice;
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

    public function appendOrderToInvoice(Invoice $invoice, Order $order): void
    {
        if ($order->currency && strtoupper((string) $invoice->currency) !== strtoupper((string) $order->currency)) {
            throw new RuntimeException('Devise de la commande incompatible avec la facture cible.');
        }

        $order->loadMissing('items');

        foreach ($order->items as $orderItem) {
            $alreadyLinked = InvoiceItem::query()
                ->where('invoice_id', $invoice->id)
                ->where('order_item_id', $orderItem->id)
                ->exists();

            if ($alreadyLinked) {
                continue;
            }

            $invoice->items()->create([
                'order_item_id' => $orderItem->id,
                'description' => $orderItem->item_name,
                'quantity' => $orderItem->quantity,
                'unit_price' => $orderItem->unit_price,
                'line_total' => $orderItem->line_total,
            ]);
        }
    }

    /**
     * @param  list<Order>  $orders
     */
    public function appendOrdersToInvoice(Invoice $invoice, array $orders): Invoice
    {
        return DB::transaction(function () use ($invoice, $orders): Invoice {
            foreach ($orders as $order) {
                $this->appendOrderToInvoice($invoice, $order);
            }

            return $this->recalculateTotals($invoice->fresh('items'));
        });
    }

    private function findReusableInvoice(Order $order, array $attributes = []): ?Invoice
    {
        if (! ($attributes['stay_id'] ?? $order->stay_id)) {
            return null;
        }

        return Invoice::query()
            ->where('stay_id', $attributes['stay_id'] ?? $order->stay_id)
            ->whereIn('status', [
                InvoiceStatus::Draft->value,
                InvoiceStatus::Unpaid->value,
                InvoiceStatus::PartiallyPaid->value,
            ])
            ->latest('issued_at')
            ->first();
    }

    private function billableNights(Stay $stay): int
    {
        $checkIn = Carbon::parse($stay->check_in_at)->startOfDay();
        $checkOut = Carbon::parse($stay->expected_check_out_at ?? $stay->check_out_at ?? now())->startOfDay();

        return max(1, (int) $checkIn->diffInDays($checkOut));
    }

    private function accommodationDescription(Stay $stay): string
    {
        $roomNumber = $stay->room?->number ? ' chambre '.$stay->room->number : '';

        return 'Hebergement sejour #'.$stay->id.$roomNumber;
    }

    private function generateReference(): string
    {
        return 'INV-'.now()->format('Ymd-His').'-'.Str::upper(Str::random(4));
    }
}
