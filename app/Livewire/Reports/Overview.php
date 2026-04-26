<?php

namespace App\Livewire\Reports;

use App\Enums\InvoiceStatus;
use App\Enums\StayStatus;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Room;
use App\Models\ServiceArea;
use App\Models\Stay;
use Livewire\Component;

class Overview extends Component
{
    public function render()
    {
        $totalRooms = Room::query()->count();
        $activeStays = Stay::query()->where('status', StayStatus::Active->value)->count();
        $occupancy = $totalRooms > 0 ? round(($activeStays / $totalRooms) * 100, 1) : 0;

        $todayRevenue = (float) Payment::query()
            ->whereDate('paid_at', today())
            ->sum('amount');
        $restaurantExternalRevenue = (float) Payment::query()
            ->whereDate('paid_at', today())
            ->whereHas('invoice', function ($query): void {
                $query->whereNull('stay_id')
                    ->whereNull('room_id')
                    ->whereHas('items.orderItem.order.serviceArea', function ($areaQuery): void {
                        $areaQuery->whereIn('code', ['restaurant', 'bar', 'terrace']);
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
            ->whereHas('items.orderItem.order.serviceArea', function ($areaQuery): void {
                $areaQuery->whereIn('code', ['restaurant', 'bar', 'terrace']);
            })
            ->sum('balance');

        $openInvoices = Invoice::query()->whereIn('status', [
            InvoiceStatus::Unpaid->value,
            InvoiceStatus::PartiallyPaid->value,
        ])->count();

        $ordersToday = Order::query()->whereDate('created_at', today())->count();

        $serviceAreaLoad = ServiceArea::query()
            ->withCount('orders')
            ->orderByDesc('orders_count')
            ->limit(5)
            ->get();

        return view('livewire.reports.overview', [
            'totalRooms' => $totalRooms,
            'activeStays' => $activeStays,
            'occupancy' => $occupancy,
            'todayRevenue' => $todayRevenue,
            'restaurantExternalRevenue' => $restaurantExternalRevenue,
            'restaurantHotelTransferBalance' => $restaurantHotelTransferBalance,
            'openInvoices' => $openInvoices,
            'ordersToday' => $ordersToday,
            'serviceAreaLoad' => $serviceAreaLoad,
        ]);
    }
}
