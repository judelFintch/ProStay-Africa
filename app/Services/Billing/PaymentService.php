<?php

namespace App\Services\Billing;

use App\Enums\PaymentMethod;
use App\Enums\CurrencyCode;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Payment;
use App\Services\Audit\AuditLogger;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class PaymentService
{
    public function record(array $payload): Payment
    {
        return DB::transaction(function () use ($payload): Payment {
            $invoice = null;
            if (Arr::get($payload, 'invoice_id')) {
                $invoice = Invoice::query()->find(Arr::get($payload, 'invoice_id'));
            }

            $order = null;
            if (Arr::get($payload, 'order_id')) {
                $order = Order::query()->find(Arr::get($payload, 'order_id'));
            }

            $resolvedCurrency = strtoupper((string) Arr::get(
                $payload,
                'currency',
                $invoice?->currency ?? $order?->currency ?? CurrencyCode::default()
            ));

            if ($invoice && strtoupper((string) $invoice->currency) !== $resolvedCurrency) {
                throw new RuntimeException('La devise du paiement doit correspondre a celle de la facture.');
            }

            if ($order && strtoupper((string) $order->currency) !== $resolvedCurrency) {
                throw new RuntimeException('La devise du paiement doit correspondre a celle de la commande.');
            }

            $payment = Payment::create([
                'reference' => Arr::get($payload, 'reference', $this->generateReference()),
                'invoice_id' => Arr::get($payload, 'invoice_id'),
                'order_id' => Arr::get($payload, 'order_id'),
                'customer_id' => Arr::get($payload, 'customer_id'),
                'recorded_by' => Arr::get($payload, 'recorded_by'),
                'method' => Arr::get($payload, 'method', PaymentMethod::Cash->value),
                'amount' => (float) Arr::get($payload, 'amount', 0),
                'currency' => $resolvedCurrency,
                'provider_reference' => Arr::get($payload, 'provider_reference'),
                'paid_at' => Arr::get($payload, 'paid_at', now()),
                'notes' => Arr::get($payload, 'notes'),
            ]);

            if ($payment->invoice_id) {
                if ($invoice) {
                    app(InvoiceService::class)->recalculateTotals($invoice);
                }
            }

            app(AuditLogger::class)->log(
                action: 'payment.recorded',
                entityType: 'payment',
                entityId: $payment->id,
                newValues: [
                    'reference' => $payment->reference,
                    'invoice_id' => $payment->invoice_id,
                    'order_id' => $payment->order_id,
                    'amount' => $payment->amount,
                    'method' => $payment->method->value,
                ]
            );

            return $payment;
        });
    }

    public function payOrderDirectly(Order $order, array $payload): Payment
    {
        $payload['order_id'] = $order->id;
        $payload['customer_id'] = $payload['customer_id'] ?? $order->customer_id;
        $payload['currency'] = $payload['currency'] ?? $order->currency ?? CurrencyCode::default();

        return $this->record($payload);
    }

    private function generateReference(): string
    {
        return 'PAY-' . now()->format('Ymd-His') . '-' . Str::upper(Str::random(4));
    }
}
