<?php

namespace Tests\Feature\Hotel;

use App\Enums\InvoiceStatus;
use App\Enums\ReservationStatus;
use App\Enums\RoomStatus;
use App\Enums\StayStatus;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\Stay;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReceptionDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_reception_dashboard_renders_hotel_operations_summary(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $roomType = RoomType::query()->create([
            'name' => 'Standard',
            'code' => 'STD',
            'capacity' => 2,
            'base_price' => 30000,
        ]);

        $arrivalRoom = Room::query()->create([
            'room_type_id' => $roomType->id,
            'number' => '101',
            'capacity' => 2,
            'price' => 30000,
            'status' => RoomStatus::Available->value,
        ]);

        $occupiedRoom = Room::query()->create([
            'room_type_id' => $roomType->id,
            'number' => '102',
            'capacity' => 2,
            'price' => 35000,
            'status' => RoomStatus::Occupied->value,
        ]);

        $arrivalCustomer = Customer::query()->create([
            'full_name' => 'Amina Diallo',
            'is_identified' => true,
        ]);

        $stayCustomer = Customer::query()->create([
            'full_name' => 'Moussa Konate',
            'is_identified' => true,
        ]);

        Reservation::query()->create([
            'customer_id' => $arrivalCustomer->id,
            'room_id' => $arrivalRoom->id,
            'check_in_date' => today(),
            'check_out_date' => today()->addDay(),
            'adults' => 1,
            'children' => 0,
            'status' => ReservationStatus::Confirmed->value,
        ]);

        $stay = Stay::query()->create([
            'customer_id' => $stayCustomer->id,
            'room_id' => $occupiedRoom->id,
            'check_in_at' => now()->subDay(),
            'expected_check_out_at' => today()->endOfDay(),
            'status' => StayStatus::Active->value,
            'nightly_rate' => 35000,
        ]);

        Invoice::query()->create([
            'reference' => 'INV-HOTEL-001',
            'customer_id' => $stayCustomer->id,
            'stay_id' => $stay->id,
            'room_id' => $occupiedRoom->id,
            'issued_at' => now(),
            'status' => InvoiceStatus::PartiallyPaid->value,
            'subtotal' => 70000,
            'total' => 70000,
            'paid_total' => 20000,
            'balance' => 50000,
        ]);

        $this->get(route('hotel.reception'))
            ->assertOk()
            ->assertSee('Tableau de bord hotel')
            ->assertSee('Amina Diallo')
            ->assertSee('Moussa Konate')
            ->assertSee('INV-HOTEL-001');
    }
}
