<?php

namespace App\Livewire\Hotel;

use App\Services\Hotel\HotelDashboardService;
use Illuminate\Support\Carbon;
use Livewire\Component;

class ReceptionDashboard extends Component
{
    public string $planning_start_date = '';

    public function mount(): void
    {
        $this->planning_start_date = today()->toDateString();
    }

    public function previousWeek(): void
    {
        $this->planning_start_date = Carbon::parse($this->planning_start_date)->subDays(7)->toDateString();
    }

    public function nextWeek(): void
    {
        $this->planning_start_date = Carbon::parse($this->planning_start_date)->addDays(7)->toDateString();
    }

    public function today(): void
    {
        $this->planning_start_date = today()->toDateString();
    }

    public function render()
    {
        $dashboard = app(HotelDashboardService::class);
        $planningStart = Carbon::parse($this->planning_start_date)->startOfDay();

        return view('livewire.hotel.reception-dashboard', [
            'summary' => $dashboard->summary(),
            'arrivals' => $dashboard->arrivals(),
            'departures' => $dashboard->departures(),
            'overdueDepartures' => $dashboard->overdueDepartures(),
            'roomStatusCounts' => $dashboard->roomStatusCounts(),
            'unpaidStayInvoices' => $dashboard->unpaidStayInvoices(),
            'planning' => $dashboard->sevenDayPlanning($planningStart),
        ]);
    }
}
