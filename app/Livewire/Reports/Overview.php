<?php

namespace App\Livewire\Reports;

use App\Exports\ReportsOverviewExport;
use App\Enums\CurrencyCode;
use App\Enums\InvoiceStatus;
use App\Enums\StayStatus;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Room;
use App\Models\ServiceArea;
use App\Models\Stay;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Facades\Excel;
use Livewire\Component;

class Overview extends Component
{
    public string $startDate = '';
    public string $endDate = '';
    public string $currencyFilter = 'all';
    public string $userFilter = 'all';
    public string $serviceFilter = 'all';

    public function mount(): void
    {
        $this->startDate = now()->startOfMonth()->toDateString();
        $this->endDate = now()->toDateString();
    }

    public function setPreset(string $preset): void
    {
        $today = now()->toDateString();

        if ($preset === 'today') {
            $this->startDate = $today;
            $this->endDate = $today;

            return;
        }

        if ($preset === '7d') {
            $this->startDate = now()->subDays(6)->toDateString();
            $this->endDate = $today;

            return;
        }

        if ($preset === 'month') {
            $this->startDate = now()->startOfMonth()->toDateString();
            $this->endDate = $today;

            return;
        }

        if ($preset === 'year') {
            $this->startDate = now()->startOfYear()->toDateString();
            $this->endDate = $today;
        }
    }

    public function resetFilters(): void
    {
        $this->currencyFilter = 'all';
        $this->userFilter = 'all';
        $this->serviceFilter = 'all';
        $this->setPreset('month');
    }

    public function exportExcel()
    {
        $timestamp = now()->format('Ymd_His');

        return Excel::download(
            new ReportsOverviewExport($this->render()->getData()),
            'reports_overview_'.$timestamp.'.xlsx'
        );
    }

    public function exportPdf()
    {
        $data = $this->render()->getData();

        $pdf = Pdf::loadView('exports.reports-overview-pdf', $data)
            ->setPaper('a4', 'landscape');

        return response()->streamDownload(
            static function () use ($pdf): void {
                echo $pdf->output();
            },
            'reports_overview_'.now()->format('Ymd_His').'.pdf'
        );
    }

    public function render()
    {
        $reportCurrency = CurrencyCode::default();
        $startDate = $this->startDate !== '' ? $this->startDate : now()->startOfMonth()->toDateString();
        $endDate = $this->endDate !== '' ? $this->endDate : now()->toDateString();

        if ($startDate > $endDate) {
            [$startDate, $endDate] = [$endDate, $startDate];
        }

        $paymentsBaseQuery = Payment::query()
            ->with(['recorder', 'order.serviceArea'])
            ->whereDate('paid_at', '>=', $startDate)
            ->whereDate('paid_at', '<=', $endDate)
            ->when($this->currencyFilter !== 'all', function (Builder $query): void {
                $query->where('currency', strtoupper($this->currencyFilter));
            })
            ->when($this->userFilter !== 'all', function (Builder $query): void {
                $query->where('recorded_by', (int) $this->userFilter);
            })
            ->when($this->serviceFilter !== 'all', function (Builder $query): void {
                $query->whereHas('order', function (Builder $orderQuery): void {
                    $orderQuery->where('service_area_id', (int) $this->serviceFilter);
                });
            });

        $ordersBaseQuery = Order::query()
            ->with(['creator', 'serviceArea'])
            ->whereDate('orders.created_at', '>=', $startDate)
            ->whereDate('orders.created_at', '<=', $endDate)
            ->when($this->currencyFilter !== 'all', function (Builder $query): void {
                $query->where('currency', strtoupper($this->currencyFilter));
            })
            ->when($this->userFilter !== 'all', function (Builder $query): void {
                $query->where('created_by', (int) $this->userFilter);
            })
            ->when($this->serviceFilter !== 'all', function (Builder $query): void {
                $query->where('service_area_id', (int) $this->serviceFilter);
            });

        $stockBaseQuery = StockMovement::query()
            ->with(['user', 'serviceArea'])
            ->whereDate('stock_movements.created_at', '>=', $startDate)
            ->whereDate('stock_movements.created_at', '<=', $endDate)
            ->when($this->userFilter !== 'all', function (Builder $query): void {
                $query->where('user_id', (int) $this->userFilter);
            })
            ->when($this->serviceFilter !== 'all', function (Builder $query): void {
                $query->where('service_area_id', (int) $this->serviceFilter);
            });

        $totalRooms = Room::query()->count();
        $activeStays = Stay::query()->where('status', StayStatus::Active->value)->count();
        $occupancy = $totalRooms > 0 ? round(($activeStays / $totalRooms) * 100, 1) : 0;

        $todayRevenue = (float) Payment::query()
            ->whereDate('paid_at', today())
            ->where('currency', $reportCurrency)
            ->sum('amount');

        $restaurantExternalRevenue = (float) Payment::query()
            ->whereDate('paid_at', '>=', $startDate)
            ->whereDate('paid_at', '<=', $endDate)
            ->where('currency', $reportCurrency)
            ->when($this->userFilter !== 'all', function (Builder $query): void {
                $query->where('recorded_by', (int) $this->userFilter);
            })
            ->when($this->serviceFilter !== 'all', function (Builder $query): void {
                $query->whereHas('order', function (Builder $orderQuery): void {
                    $orderQuery->where('service_area_id', (int) $this->serviceFilter);
                });
            })
            ->whereHas('invoice', function ($query): void {
                $query->whereNull('stay_id')
                    ->whereNull('room_id')
                    ->whereHas('items.orderItem.order.serviceArea', function ($areaQuery): void {
                        $areaQuery
                            ->where('domain', 'restaurant')
                            ->where('supports_orders', true);
                    });
            })
            ->sum('amount');

        $restaurantHotelTransferBalance = (float) Invoice::query()
            ->whereIn('status', [
                InvoiceStatus::Unpaid->value,
                InvoiceStatus::PartiallyPaid->value,
            ])
            ->where(function ($query): void {
                $query->whereNotNull('stay_id')->orWhereNotNull('room_id');
            })
            ->where('currency', $reportCurrency)
            ->whereHas('items.orderItem.order.serviceArea', function ($areaQuery): void {
                $areaQuery
                    ->where('domain', 'restaurant')
                    ->where('supports_orders', true);
            })
            ->when($this->serviceFilter !== 'all', function (Builder $query): void {
                $query->whereHas('items.orderItem.order', function (Builder $orderQuery): void {
                    $orderQuery->where('service_area_id', (int) $this->serviceFilter);
                });
            })
            ->sum('balance');

        $openInvoices = Invoice::query()->whereIn('status', [
            InvoiceStatus::Unpaid->value,
            InvoiceStatus::PartiallyPaid->value,
        ])->count();

        $ordersToday = Order::query()
            ->whereDate('orders.created_at', today())
            ->when($this->serviceFilter !== 'all', function (Builder $query): void {
                $query->where('service_area_id', (int) $this->serviceFilter);
            })
            ->count();

        $salesTotals = (clone $ordersBaseQuery)
            ->selectRaw('COUNT(*) as orders_count')
            ->selectRaw('COALESCE(SUM(total), 0) as orders_amount')
            ->first();

        $paymentsTotal = (float) (clone $paymentsBaseQuery)->sum('amount');
        $salesOrderCount = (int) ($salesTotals?->orders_count ?? 0);
        $salesOrderAmount = (float) ($salesTotals?->orders_amount ?? 0);

        $stockTotals = (clone $stockBaseQuery)
            ->selectRaw("COALESCE(SUM(CASE WHEN movement_type = 'in' THEN quantity ELSE 0 END), 0) as in_qty")
            ->selectRaw("COALESCE(SUM(CASE WHEN movement_type = 'out' THEN quantity ELSE 0 END), 0) as out_qty")
            ->selectRaw("COALESCE(SUM(CASE WHEN movement_type = 'in' THEN quantity * unit_cost ELSE 0 END), 0) as in_amount")
            ->selectRaw("COALESCE(SUM(CASE WHEN movement_type = 'out' THEN quantity * unit_cost ELSE 0 END), 0) as out_amount")
            ->first();

        $salesByService = (clone $ordersBaseQuery)
            ->leftJoin('service_areas', 'orders.service_area_id', '=', 'service_areas.id')
            ->selectRaw('orders.service_area_id')
            ->selectRaw("COALESCE(service_areas.name, 'Non affecte') as service_name")
            ->selectRaw('COUNT(*) as orders_count')
            ->selectRaw('COALESCE(SUM(orders.total), 0) as orders_amount')
            ->groupBy('orders.service_area_id', 'service_areas.name')
            ->orderByDesc('orders_amount')
            ->get();

        $paymentsByUser = (clone $paymentsBaseQuery)
            ->leftJoin('users', 'payments.recorded_by', '=', 'users.id')
            ->selectRaw('payments.recorded_by')
            ->selectRaw("COALESCE(users.name, 'Systeme / inconnu') as user_name")
            ->selectRaw('COUNT(*) as payments_count')
            ->selectRaw('COALESCE(SUM(payments.amount), 0) as payments_amount')
            ->groupBy('payments.recorded_by', 'users.name')
            ->orderByDesc('payments_amount')
            ->get();

        $ordersByUser = (clone $ordersBaseQuery)
            ->leftJoin('users', 'orders.created_by', '=', 'users.id')
            ->selectRaw('orders.created_by')
            ->selectRaw("COALESCE(users.name, 'Systeme / inconnu') as user_name")
            ->selectRaw('COUNT(*) as orders_count')
            ->selectRaw('COALESCE(SUM(orders.total), 0) as orders_amount')
            ->groupBy('orders.created_by', 'users.name')
            ->orderByDesc('orders_amount')
            ->get();

        $stockByService = (clone $stockBaseQuery)
            ->leftJoin('service_areas', 'stock_movements.service_area_id', '=', 'service_areas.id')
            ->selectRaw('stock_movements.service_area_id')
            ->selectRaw("COALESCE(service_areas.name, 'Non affecte') as service_name")
            ->selectRaw("COALESCE(SUM(CASE WHEN stock_movements.movement_type = 'in' THEN stock_movements.quantity ELSE 0 END), 0) as in_qty")
            ->selectRaw("COALESCE(SUM(CASE WHEN stock_movements.movement_type = 'out' THEN stock_movements.quantity ELSE 0 END), 0) as out_qty")
            ->selectRaw("COALESCE(SUM(CASE WHEN stock_movements.movement_type = 'out' THEN stock_movements.quantity * stock_movements.unit_cost ELSE 0 END), 0) as out_amount")
            ->groupBy('stock_movements.service_area_id', 'service_areas.name')
            ->orderByDesc('out_amount')
            ->get();

        $stockByUser = (clone $stockBaseQuery)
            ->leftJoin('users', 'stock_movements.user_id', '=', 'users.id')
            ->selectRaw('stock_movements.user_id')
            ->selectRaw("COALESCE(users.name, 'Systeme / inconnu') as user_name")
            ->selectRaw('COUNT(*) as movement_count')
            ->selectRaw("COALESCE(SUM(CASE WHEN stock_movements.movement_type = 'in' THEN stock_movements.quantity ELSE 0 END), 0) as in_qty")
            ->selectRaw("COALESCE(SUM(CASE WHEN stock_movements.movement_type = 'out' THEN stock_movements.quantity ELSE 0 END), 0) as out_qty")
            ->groupBy('stock_movements.user_id', 'users.name')
            ->orderByDesc('movement_count')
            ->get();

        $topProductsOut = (clone $stockBaseQuery)
            ->join('products', 'stock_movements.product_id', '=', 'products.id')
            ->where('stock_movements.movement_type', 'out')
            ->selectRaw('stock_movements.product_id')
            ->selectRaw('products.name as product_name')
            ->selectRaw('COALESCE(SUM(stock_movements.quantity), 0) as out_qty')
            ->selectRaw('COALESCE(SUM(stock_movements.quantity * stock_movements.unit_cost), 0) as out_amount')
            ->groupBy('stock_movements.product_id', 'products.name')
            ->orderByDesc('out_amount')
            ->limit(8)
            ->get();

        $serviceAreaLoad = ServiceArea::query()
            ->active()
            ->withCount([
                'orders as period_orders_count' => function (Builder $query) use ($startDate, $endDate): void {
                    $query->whereDate('orders.created_at', '>=', $startDate)
                        ->whereDate('orders.created_at', '<=', $endDate);
                },
            ])
            ->orderByDesc('period_orders_count')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->limit(8)
            ->get();

        $activeCurrencies = Payment::query()
            ->select('currency')
            ->distinct()
            ->orderBy('currency')
            ->pluck('currency')
            ->filter()
            ->values();

        $reportUsers = User::query()
            ->orderBy('name')
            ->limit(200)
            ->get(['id', 'name']);

        $reportServices = ServiceArea::query()
            ->active()
            ->ordered()
            ->get(['id', 'name']);

        return view('livewire.reports.overview', [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'activeCurrencies' => $activeCurrencies,
            'reportUsers' => $reportUsers,
            'reportServices' => $reportServices,
            'totalRooms' => $totalRooms,
            'activeStays' => $activeStays,
            'occupancy' => $occupancy,
            'todayRevenue' => $todayRevenue,
            'restaurantExternalRevenue' => $restaurantExternalRevenue,
            'restaurantHotelTransferBalance' => $restaurantHotelTransferBalance,
            'openInvoices' => $openInvoices,
            'ordersToday' => $ordersToday,
            'salesOrderCount' => $salesOrderCount,
            'salesOrderAmount' => $salesOrderAmount,
            'paymentsTotal' => $paymentsTotal,
            'avgTicket' => $salesOrderCount > 0 ? ($salesOrderAmount / $salesOrderCount) : 0,
            'stockInQty' => (float) ($stockTotals?->in_qty ?? 0),
            'stockOutQty' => (float) ($stockTotals?->out_qty ?? 0),
            'stockInAmount' => (float) ($stockTotals?->in_amount ?? 0),
            'stockOutAmount' => (float) ($stockTotals?->out_amount ?? 0),
            'salesByService' => $salesByService,
            'paymentsByUser' => $paymentsByUser,
            'ordersByUser' => $ordersByUser,
            'stockByService' => $stockByService,
            'stockByUser' => $stockByUser,
            'topProductsOut' => $topProductsOut,
            'serviceAreaLoad' => $serviceAreaLoad,
            'reportCurrency' => $reportCurrency,
        ]);
    }
}
