<?php

namespace App\Services\Hotel;

use App\Enums\InvoiceStatus;
use App\Enums\ReservationStatus;
use App\Enums\RoomStatus;
use App\Enums\StayStatus;
use App\Models\Invoice;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\Stay;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class HotelDashboardService
{
    public function summary(?Carbon $date = null): array
    {
        $businessDate = ($date ?? today())->copy()->startOfDay();
        $totalRooms = Room::query()->count();
        $activeStays = Stay::query()->where('status', StayStatus::Active->value)->count();
        $openBalance = (float) Invoice::query()
            ->whereIn('status', [InvoiceStatus::Unpaid->value, InvoiceStatus::PartiallyPaid->value])
            ->sum('balance');

        return [
            'businessDate' => $businessDate,
            'totalRooms' => $totalRooms,
            'activeStays' => $activeStays,
            'occupancyRate' => $totalRooms > 0 ? round(($activeStays / $totalRooms) * 100, 1) : 0,
            'arrivalsToday' => $this->arrivals($businessDate)->count(),
            'departuresToday' => $this->departures($businessDate)->count(),
            'roomsToClean' => Room::query()->where('status', RoomStatus::Cleaning->value)->count(),
            'roomsInMaintenance' => Room::query()->where('status', RoomStatus::Maintenance->value)->count(),
            'openInvoiceBalance' => $openBalance,
        ];
    }

    public function arrivals(?Carbon $date = null): Collection
    {
        $businessDate = ($date ?? today())->copy()->toDateString();

        return Reservation::query()
            ->with(['customer', 'room.roomType'])
            ->whereDate('check_in_date', $businessDate)
            ->whereIn('status', [
                ReservationStatus::Pending->value,
                ReservationStatus::Confirmed->value,
            ])
            ->orderBy('check_in_date')
            ->get();
    }

    public function departures(?Carbon $date = null): Collection
    {
        $businessDate = ($date ?? today())->copy()->toDateString();

        return Stay::query()
            ->with(['customer', 'room.roomType', 'invoices'])
            ->where('status', StayStatus::Active->value)
            ->whereDate('expected_check_out_at', $businessDate)
            ->orderBy('expected_check_out_at')
            ->get();
    }

    public function overdueDepartures(?Carbon $date = null): Collection
    {
        $businessDate = ($date ?? today())->copy()->startOfDay();

        return Stay::query()
            ->with(['customer', 'room'])
            ->where('status', StayStatus::Active->value)
            ->whereNotNull('expected_check_out_at')
            ->where('expected_check_out_at', '<', $businessDate)
            ->orderBy('expected_check_out_at')
            ->limit(20)
            ->get();
    }

    public function roomStatusCounts(): array
    {
        $counts = Room::query()
            ->selectRaw('status, COUNT(*) as rooms_count')
            ->groupBy('status')
            ->pluck('rooms_count', 'status');

        return collect(RoomStatus::cases())
            ->mapWithKeys(fn (RoomStatus $status) => [$status->value => (int) ($counts[$status->value] ?? 0)])
            ->all();
    }

    public function unpaidStayInvoices(): Collection
    {
        return Invoice::query()
            ->with(['customer', 'room', 'stay'])
            ->whereNotNull('stay_id')
            ->whereIn('status', [InvoiceStatus::Unpaid->value, InvoiceStatus::PartiallyPaid->value])
            ->where('balance', '>', 0)
            ->orderByDesc('balance')
            ->limit(10)
            ->get();
    }

    public function sevenDayPlanning(?Carbon $startDate = null): array
    {
        $start = ($startDate ?? today())->copy()->startOfDay();
        $dates = collect(range(0, 6))->map(fn (int $offset) => $start->copy()->addDays($offset));

        $rooms = Room::query()
            ->with(['roomType', 'reservations.customer', 'stays.customer'])
            ->orderBy('number')
            ->get();

        return [
            'dates' => $dates,
            'rooms' => $rooms->map(function (Room $room) use ($dates): array {
                return [
                    'room' => $room,
                    'days' => $dates->map(fn (Carbon $date) => $this->roomDayState($room, $date))->all(),
                ];
            }),
        ];
    }

    private function roomDayState(Room $room, Carbon $date): array
    {
        $activeStay = $room->stays
            ->first(function (Stay $stay) use ($date): bool {
                if ($stay->status !== StayStatus::Active) {
                    return false;
                }

                $checkIn = $stay->check_in_at->copy()->startOfDay();
                $expectedCheckOut = ($stay->expected_check_out_at ?? now())->copy()->startOfDay();

                return $checkIn->lessThanOrEqualTo($date) && $expectedCheckOut->greaterThan($date);
            });

        if ($activeStay) {
            return [
                'state' => 'occupied',
                'label' => $activeStay->customer?->full_name ?? 'Occupied',
            ];
        }

        $reservation = $room->reservations
            ->first(function (Reservation $reservation) use ($date): bool {
                if (! in_array($reservation->status, [
                    ReservationStatus::Pending,
                    ReservationStatus::Confirmed,
                ], true)) {
                    return false;
                }

                return $reservation->check_in_date->lessThanOrEqualTo($date)
                    && $reservation->check_out_date->greaterThan($date);
            });

        if ($reservation) {
            return [
                'state' => 'reserved',
                'label' => $reservation->customer?->full_name ?? 'Reserved',
            ];
        }

        if ($room->status === RoomStatus::Maintenance) {
            return ['state' => 'maintenance', 'label' => 'Maintenance'];
        }

        if ($room->status === RoomStatus::Cleaning) {
            return ['state' => 'cleaning', 'label' => 'Cleaning'];
        }

        return ['state' => 'available', 'label' => 'Available'];
    }
}
