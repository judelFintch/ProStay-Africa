<?php

namespace App\Livewire\Billing;

use App\Enums\InvoiceStatus;
use App\Enums\CustomerType;
use App\Models\Order;
use App\Models\Invoice;
use App\Models\ServiceArea;
use App\Models\User;
use App\Services\Billing\InvoiceService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class InvoiceBuilder extends Component
{
    public string $build_mode = 'new';
    public array $selectedOrderIds = [];
    public ?int $target_invoice_id = null;
    public ?int $customer_id = null;
    public ?int $stay_id = null;
    public ?int $room_id = null;

    public ?int $server_filter = null;
    public ?int $service_area_filter = null;
    public string $order_search = '';
    public bool $show_external_only = true;
    public ?string $date_from = null;
    public ?string $date_to = null;

    /**
     * Paid invoices remain appendable: adding new orders can reopen the balance.
     *
     * @return array<int, string>
     */
    private function appendableStatuses(): array
    {
        return [
            InvoiceStatus::Draft->value,
            InvoiceStatus::Unpaid->value,
            InvoiceStatus::PartiallyPaid->value,
            InvoiceStatus::Paid->value,
        ];
    }

    public function build(InvoiceService $invoiceService): void
    {
        $validated = $this->validate([
            'build_mode' => ['required', 'in:new,existing'],
            'selectedOrderIds' => ['required', 'array', 'min:1'],
            'selectedOrderIds.*' => ['exists:orders,id'],
            'target_invoice_id' => ['nullable', 'exists:invoices,id'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'stay_id' => ['nullable', 'exists:stays,id'],
            'room_id' => ['nullable', 'exists:rooms,id'],
        ]);

        $orders = Order::query()
            ->with(['items', 'customer', 'stay', 'room'])
            ->whereIn('id', $this->selectedOrderIds)
            ->whereIn('status', ['served', 'closed'])
            ->get();

        if ($orders->isEmpty()) {
            $this->addError('selectedOrderIds', 'Selectionne au moins une commande livree a facturer.');

            return;
        }

        if ($validated['build_mode'] === 'existing') {
            if (! $this->target_invoice_id) {
                $this->addError('target_invoice_id', 'Selectionne une facture ouverte.');

                return;
            }

            $invoice = Invoice::query()
                ->whereIn('status', $this->appendableStatuses())
                ->find($this->target_invoice_id);

            if (! $invoice) {
                $this->addError('target_invoice_id', 'La facture selectionnee n est plus ouverte.');

                return;
            }

            foreach ($orders as $order) {
                if (! $this->isOrderCompatibleWithInvoice($order, $invoice)) {
                    $this->addError(
                        'selectedOrderIds',
                        'Commande ' . $order->reference . ' incompatible avec la facture cible (client/chambre/sejour).'
                    );

                    return;
                }
            }

            $updatedInvoice = $invoiceService->appendOrdersToInvoice($invoice, $orders->all());

            $this->dispatch('invoice-created', reference: $updatedInvoice->reference);
            $this->reset(['selectedOrderIds']);

            return;
        }

        $attributes = [
            'customer_id' => $this->customer_id ?: $orders->first()?->customer_id,
            'stay_id' => $this->stay_id ?: $orders->first()?->stay_id,
            'room_id' => $this->room_id ?: $orders->first()?->room_id,
            'issued_by' => Auth::id(),
        ];

        $invoice = $invoiceService->createFromOrders($orders->all(), $attributes);

        $this->dispatch('invoice-created', reference: $invoice->reference);
        $this->reset(['selectedOrderIds']);
    }

    private function isOrderCompatibleWithInvoice(Order $order, Invoice $invoice): bool
    {
        if ($invoice->stay_id) {
            return (int) $invoice->stay_id === (int) $order->stay_id;
        }

        if ($invoice->room_id && (int) $invoice->room_id !== (int) $order->room_id) {
            return false;
        }

        if ($invoice->customer_id && (int) $invoice->customer_id !== (int) $order->customer_id) {
            return false;
        }

        return true;
    }

    public function clearFilters(): void
    {
        $this->reset(['server_filter', 'service_area_filter', 'order_search', 'date_from', 'date_to']);
    }

    public function focusInvoiceForAppend(int $invoiceId): void
    {
        $invoice = Invoice::query()
            ->whereIn('status', $this->appendableStatuses())
            ->find($invoiceId);

        if (! $invoice) {
            $this->addError('target_invoice_id', 'La facture selectionnee n est plus ouverte.');

            return;
        }

        $this->build_mode = 'existing';
        $this->target_invoice_id = $invoice->id;
        $this->resetErrorBag();
    }

    public function render()
    {
        $openInvoices = Invoice::query()
            ->with(['customer', 'room', 'stay', 'items.orderItem.order.server'])
            ->whereIn('status', $this->appendableStatuses())
            ->latest('issued_at')
            ->limit(50)
            ->get();

        $externalOpenInvoices = $openInvoices
            ->filter(fn (Invoice $invoice) => ! $invoice->stay_id && ! $invoice->room_id)
            ->values();

        $hotelOpenInvoices = $openInvoices
            ->filter(fn (Invoice $invoice) => (bool) $invoice->stay_id || (bool) $invoice->room_id)
            ->values();

        $deliverableOrders = Order::query()
            ->with(['items', 'customer', 'room', 'stay', 'server'])
            ->whereIn('status', ['served', 'closed'])
            ->when($this->server_filter, function ($query): void {
                $query->where('served_by', $this->server_filter);
            })
            ->when($this->service_area_filter, function ($query): void {
                $query->where('service_area_id', $this->service_area_filter);
            })
            ->when($this->order_search !== '', function ($query): void {
                $term = trim($this->order_search);

                $query->where(function ($nested) use ($term): void {
                    $nested->where('reference', 'like', '%' . $term . '%')
                        ->orWhere('external_label', 'like', '%' . $term . '%');
                });
            })
            ->when($this->date_from, function ($query): void {
                $query->whereDate('created_at', '>=', $this->date_from);
            })
            ->when($this->date_to, function ($query): void {
                $query->whereDate('created_at', '<=', $this->date_to);
            })
            ->whereHas('items', function ($query): void {
                $query->whereDoesntHave('invoiceItems');
            })
            ->latest()
            ->limit(80)
            ->get();

        $servers = User::query()
            ->where('is_server', true)
            ->where('server_active', true)
            ->orderBy('name')
            ->get();

        $serviceAreas = ServiceArea::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $externalOrders = $deliverableOrders->whereIn('customer_type', [
            CustomerType::WalkInAnonymous,
            CustomerType::WalkInIdentified,
        ])->values();

        $lodgedOrders = $deliverableOrders->where('customer_type', CustomerType::Lodged)->values();
        $visibleLodgedOrders = $this->show_external_only ? collect() : $lodgedOrders;

        $pendingDeliveredTotal = (float) $deliverableOrders->sum('total');
        $unpaidBalance = (float) $openInvoices->sum('balance');
        $externalUnpaidBalance = (float) $externalOpenInvoices->sum('balance');
        $hotelUnpaidBalance = (float) $hotelOpenInvoices->sum('balance');

        return view('livewire.billing.invoice-builder', [
            'openOrders' => $deliverableOrders,
            'externalOrders' => $externalOrders,
            'lodgedOrders' => $lodgedOrders,
            'visibleLodgedOrders' => $visibleLodgedOrders,
            'openInvoices' => $openInvoices,
            'externalOpenInvoices' => $externalOpenInvoices,
            'hotelOpenInvoices' => $hotelOpenInvoices,
            'servers' => $servers,
            'serviceAreas' => $serviceAreas,
            'stats' => [
                'deliverable_orders' => $deliverableOrders->count(),
                'deliverable_total' => $pendingDeliveredTotal,
                'external_orders' => $externalOrders->count(),
                'external_total' => (float) $externalOrders->sum('total'),
                'lodged_orders' => $lodgedOrders->count(),
                'lodged_total' => (float) $lodgedOrders->sum('total'),
                'open_invoices' => $openInvoices->count(),
                'unpaid_balance' => $unpaidBalance,
                'external_open_invoices' => $externalOpenInvoices->count(),
                'external_unpaid_balance' => $externalUnpaidBalance,
                'hotel_open_invoices' => $hotelOpenInvoices->count(),
                'hotel_unpaid_balance' => $hotelUnpaidBalance,
            ],
        ]);
    }
}
