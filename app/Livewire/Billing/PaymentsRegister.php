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
    public float $amount = 0;
    public string $method = 'cash';
    public ?string $provider_reference = null;
    public ?string $notes = null;

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
        return view('livewire.billing.payments-register', [
            'openInvoices' => Invoice::query()->whereIn('status', ['unpaid', 'partially_paid'])->latest()->limit(50)->get(),
            'payments' => Payment::query()->with(['invoice', 'customer', 'recorder'])->latest()->limit(25)->get(),
            'methods' => array_column(PaymentMethod::cases(), 'value'),
        ]);
    }
}
