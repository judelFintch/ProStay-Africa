<?php

namespace App\Livewire\Billing;

use App\Enums\PaymentMethod;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\Billing\PaymentService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class PaymentsRegister extends Component
{
    public ?int $invoice_id = null;
    public ?string $notice = null;
    public float $amount = 0;
    public string $method = 'cash';
    public ?string $provider_reference = null;
    public ?string $notes = null;

    public function updatedInvoiceId(): void
    {
        $invoice = $this->selectedInvoice();

        if ($invoice && $this->amount <= 0) {
            $this->amount = (float) $invoice->balance;
        }
    }

    public function payFullBalance(): void
    {
        $invoice = $this->selectedInvoice();

        if ($invoice) {
            $this->amount = (float) $invoice->balance;
        }
    }

    public function mount(): void
    {
        $invoiceId = request()->integer('invoice');

        if ($invoiceId > 0) {
            $invoice = Invoice::query()->find($invoiceId);

            if ($invoice && $this->isExternalRestaurantInvoice($invoice)) {
                $this->invoice_id = $invoiceId;
            } elseif ($invoice) {
                $this->notice = 'Cette facture concerne un client loge. Le paiement se fait a l hotel/reception, pas au restaurant.';
            }
        }
    }

    public function record(PaymentService $paymentService): void
    {
        $this->validate([
            'invoice_id' => ['required', 'exists:invoices,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'method' => ['required', 'in:' . implode(',', array_column(PaymentMethod::cases(), 'value'))],
            'provider_reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $invoice = Invoice::query()->findOrFail($this->invoice_id);

        if (! $this->isExternalRestaurantInvoice($invoice)) {
            $this->addError('invoice_id', 'Paiement refuse ici: les clients loges paient a l hotel/reception.');
            $this->notice = 'Le restaurant garde le suivi, mais l encaissement du client loge doit etre fait a l hotel.';

            return;
        }

        $paymentService->record([
            'invoice_id' => $invoice->id,
            'customer_id' => $invoice->customer_id,
            'recorded_by' => Auth::id(),
            'amount' => $this->amount,
            'method' => $this->method,
            'provider_reference' => $this->provider_reference,
            'notes' => $this->notes,
        ]);

        $this->reset(['invoice_id', 'provider_reference', 'notes']);
        $this->method = PaymentMethod::Cash->value;
        $this->amount = 0;
    }

    public function render()
    {
        $openInvoices = Invoice::query()
            ->whereIn('status', ['unpaid', 'partially_paid'])
            ->whereNull('stay_id')
            ->whereNull('room_id')
            ->latest()
            ->limit(50)
            ->get();
        $selectedInvoice = $this->invoice_id
            ? $openInvoices->firstWhere('id', $this->invoice_id)
            : null;
        $payments = Payment::query()
            ->with(['invoice', 'customer', 'recorder'])
            ->whereHas('invoice', function ($query): void {
                $query->whereNull('stay_id')->whereNull('room_id');
            })
            ->latest()
            ->limit(25)
            ->get();

        return view('livewire.billing.payments-register', [
            'openInvoices' => $openInvoices,
            'selectedInvoice' => $selectedInvoice,
            'payments' => $payments,
            'methods' => array_column(PaymentMethod::cases(), 'value'),
            'stats' => [
                'open_count' => $openInvoices->count(),
                'open_balance' => (float) $openInvoices->sum('balance'),
                'paid_today' => (float) $payments->where('paid_at', '>=', today())->sum('amount'),
            ],
        ]);
    }

    private function isExternalRestaurantInvoice(Invoice $invoice): bool
    {
        return ! $invoice->stay_id && ! $invoice->room_id;
    }

    private function selectedInvoice(): ?Invoice
    {
        if (! $this->invoice_id) {
            return null;
        }

        $invoice = Invoice::query()->find($this->invoice_id);

        return $invoice && $this->isExternalRestaurantInvoice($invoice) ? $invoice : null;
    }
}
