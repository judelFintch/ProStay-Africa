<?php

namespace App\Livewire\Orders;

use App\Enums\CurrencyCode;
use App\Enums\InvoiceStatus;
use App\Models\DiningTable;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\ServiceArea;
use Illuminate\Support\Collection;
use Livewire\Component;

class TrackingBoard extends Component
{
    public string $search = '';
    public ?int $service_area_id = null;
    public string $view_mode = 'all';
    public string $currency_filter = 'USD';
    public ?string $selected_key = null;

    public function mount(): void
    {
        $this->currency_filter = CurrencyCode::default();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'service_area_id', 'view_mode']);
        $this->view_mode = 'all';
        $this->currency_filter = CurrencyCode::default();
    }

    public function render()
    {
        $openInvoices = Invoice::query()
            ->with(['customer', 'room', 'stay', 'items.orderItem.order.table', 'items.orderItem.order.serviceArea', 'items.orderItem.order.server'])
            ->whereIn('status', [
                InvoiceStatus::Draft->value,
                InvoiceStatus::Unpaid->value,
                InvoiceStatus::PartiallyPaid->value,
            ])
            ->where('currency', strtoupper($this->currency_filter))
            ->latest('issued_at')
            ->limit(100)
            ->get();

        $unbilledOrders = Order::query()
            ->with(['items.invoiceItems', 'customer', 'room', 'stay', 'table', 'serviceArea', 'server'])
            ->whereIn('status', ['served', 'closed'])
            ->whereHas('items', function ($query): void {
                $query->whereDoesntHave('invoiceItems');
            })
            ->where('currency', strtoupper($this->currency_filter))
            ->latest()
            ->limit(100)
            ->get();

        $cards = $this->buildCards($openInvoices, $unbilledOrders)
            ->filter(fn (array $card): bool => $this->matchesFilters($card))
            ->sortByDesc('last_activity')
            ->values();
        $selectedCard = $this->selected_key
            ? $cards->firstWhere('key', $this->selected_key)
            : null;

        if (! $selectedCard && $cards->isNotEmpty()) {
            $selectedCard = $cards->first();
            $this->selected_key = $selectedCard['key'];
        }

        return view('livewire.orders.tracking-board', [
            'cards' => $cards,
            'selectedCard' => $selectedCard,
            'serviceAreas' => ServiceArea::query()
                ->active()
                ->forDomain('restaurant')
                ->supporting('orders')
                ->ordered()
                ->get(),
            'supportedCurrencies' => CurrencyCode::supported(),
            'stats' => [
                'cards' => $cards->count(),
                'due' => (float) $cards->sum('due_total'),
                'unbilled' => (float) $cards->sum('unbilled_total'),
                'invoiced' => (float) $cards->sum('invoice_balance'),
                'currency' => strtoupper($this->currency_filter),
            ],
        ]);
    }

    public function selectCard(string $key): void
    {
        $this->selected_key = $key;
    }

    private function buildCards(Collection $openInvoices, Collection $unbilledOrders): Collection
    {
        $cards = collect();

        foreach ($openInvoices as $invoice) {
            $relatedOrders = $invoice->items
                ->map(fn ($item) => $item->orderItem?->order)
                ->filter()
                ->unique('id')
                ->values();
            $firstOrder = $relatedOrders->first();
            $key = $this->cardKey(
                currency: strtoupper((string) $invoice->currency),
                customerId: $invoice->customer_id,
                stayId: $invoice->stay_id,
                roomId: $invoice->room_id,
                tableId: $firstOrder?->dining_table_id,
                externalLabel: $firstOrder?->external_label,
            );

            $card = $cards->get($key, $this->emptyCard($key));
            $card['title'] = $this->accountTitle($invoice, $firstOrder);
            $card['subtitle'] = $this->accountSubtitle($invoice, $firstOrder);
            $card['kind'] = $invoice->stay_id || $invoice->room_id ? 'hotel' : ($firstOrder?->dining_table_id ? 'table' : 'external');
            $card['invoice_balance'] += (float) $invoice->balance;
            $card['due_total'] += (float) $invoice->balance;
            $card['invoice_ids'][] = $invoice->id;
            $card['invoices'][] = $invoice;
            $card['service_area_id'] ??= $firstOrder?->service_area_id;
            $card['service_area_name'] ??= $firstOrder?->serviceArea?->name;
            $card['server_name'] ??= $firstOrder?->server?->name;
            $card['last_activity'] = max($card['last_activity'], optional($invoice->updated_at)->timestamp ?? 0);
            $card = $this->decorateCard($card);

            $cards->put($key, $card);
        }

        foreach ($unbilledOrders as $order) {
            $key = $this->cardKey(
                currency: strtoupper((string) $order->currency),
                customerId: $order->customer_id,
                stayId: $order->stay_id,
                roomId: $order->room_id,
                tableId: $order->dining_table_id,
                externalLabel: $order->external_label,
            );

            $card = $cards->get($key, $this->emptyCard($key));
            $card['title'] = $card['title'] ?: $this->accountTitle(null, $order);
            $card['subtitle'] = $card['subtitle'] ?: $this->accountSubtitle(null, $order);
            $card['kind'] = $card['kind'] ?: ($order->stay_id || $order->room_id ? 'hotel' : ($order->dining_table_id ? 'table' : 'external'));
            $card['unbilled_total'] += (float) $order->total;
            $card['due_total'] += (float) $order->total;
            $card['order_ids'][] = $order->id;
            $card['orders'][] = $order;
            $card['service_area_id'] ??= $order->service_area_id;
            $card['service_area_name'] ??= $order->serviceArea?->name;
            $card['server_name'] ??= $order->server?->name;
            $card['last_activity'] = max($card['last_activity'], optional($order->updated_at)->timestamp ?? 0);
            $card = $this->decorateCard($card);

            $cards->put($key, $card);
        }

        return $cards;
    }

    private function matchesFilters(array $card): bool
    {
        if ($this->service_area_id && (int) ($card['service_area_id'] ?? 0) !== $this->service_area_id) {
            return false;
        }

        if ($this->view_mode !== 'all' && ($card['kind'] ?? '') !== $this->view_mode) {
            return false;
        }

        $term = strtolower(trim($this->search));
        if ($term === '') {
            return true;
        }

        return str_contains(strtolower($card['title'].' '.$card['subtitle'].' '.$card['server_name'].' '.$card['service_area_name']), $term);
    }

    private function emptyCard(string $key): array
    {
        return [
            'key' => $key,
            'title' => '',
            'subtitle' => '',
            'kind' => '',
            'due_total' => 0.0,
            'unbilled_total' => 0.0,
            'invoice_balance' => 0.0,
            'order_ids' => [],
            'invoice_ids' => [],
            'orders' => [],
            'invoices' => [],
            'service_area_id' => null,
            'service_area_name' => null,
            'server_name' => null,
            'last_activity' => 0,
            'status_label' => 'Ouvert',
            'status_class' => 'bg-slate-100 text-slate-700',
            'last_activity_label' => '-',
            'currency' => strtoupper($this->currency_filter),
        ];
    }

    private function decorateCard(array $card): array
    {
        if (($card['kind'] ?? '') === 'hotel' && ($card['unbilled_total'] > 0 || $card['invoice_balance'] > 0)) {
            $card['status_label'] = 'A traiter reception';
            $card['status_class'] = 'bg-slate-900 text-white';
        } elseif ($card['unbilled_total'] > 0 && $card['invoice_balance'] > 0) {
            $card['status_label'] = 'A facturer + encaisser';
            $card['status_class'] = 'bg-rose-100 text-rose-700';
        } elseif ($card['unbilled_total'] > 0) {
            $card['status_label'] = 'A facturer';
            $card['status_class'] = 'bg-amber-100 text-amber-800';
        } elseif ($card['invoice_balance'] > 0) {
            $card['status_label'] = 'A encaisser';
            $card['status_class'] = 'bg-emerald-100 text-emerald-700';
        }

        $card['last_activity_label'] = $card['last_activity'] > 0
            ? now()->createFromTimestamp($card['last_activity'])->diffForHumans()
            : '-';

        return $card;
    }

    private function cardKey(string $currency, ?int $customerId, ?int $stayId, ?int $roomId, ?int $tableId, ?string $externalLabel): string
    {
        $prefix = 'currency:'.strtoupper($currency).'|';

        if ($stayId) {
            return $prefix.'stay:'.$stayId;
        }

        if ($roomId) {
            return $prefix.'room:'.$roomId;
        }

        if ($tableId) {
            return $prefix.'table:'.$tableId;
        }

        if ($customerId) {
            return $prefix.'customer:'.$customerId;
        }

        return $prefix.'external:'.strtolower(trim($externalLabel ?: 'passage'));
    }

    private function accountTitle(?Invoice $invoice, ?Order $order): string
    {
        $diningTable = $order?->getRelationValue('table');

        if ($diningTable instanceof DiningTable) {
            return 'Table '.$diningTable->number;
        }

        if ($invoice?->room || $order?->room) {
            return 'Chambre '.($invoice?->room?->number ?? $order?->room?->number);
        }

        return $invoice?->customer?->full_name
            ?? $order?->customer?->full_name
            ?? $order?->external_label
            ?? 'Client passage';
    }

    private function accountSubtitle(?Invoice $invoice, ?Order $order): string
    {
        if ($invoice?->customer || $order?->customer) {
            return $invoice?->customer?->full_name ?? $order?->customer?->full_name;
        }

        if ($order?->external_label) {
            return $order->external_label;
        }

        return $order?->serviceArea?->name ?? 'Compte ouvert';
    }
}
