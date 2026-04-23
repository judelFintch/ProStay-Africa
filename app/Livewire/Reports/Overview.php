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
            'openInvoices' => $openInvoices,
            'ordersToday' => $ordersToday,
            'serviceAreaLoad' => $serviceAreaLoad,
        ]);
    }
}
