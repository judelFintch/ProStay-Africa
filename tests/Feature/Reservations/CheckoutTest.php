<?php

namespace Tests\Feature\Reservations;

use App\Enums\InvoiceStatus;
use App\Livewire\Reservations\Manager;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\Stay;
use App\Models\User;
use App\Services\Billing\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_creates_accommodation_charge_and_blocks_unpaid_stay(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $stay = $this->createActiveStay();

        Livewire::test(Manager::class)
            ->call('checkOut', $stay->id)
            ->assertHasErrors(['checkout']);

        $invoice = Invoice::query()->where('stay_id', $stay->id)->firstOrFail();

        $this->assertSame(90000.0, (float) $invoice->total);
        $this->assertSame(90000.0, (float) $invoice->balance);
        $this->assertSame(InvoiceStatus::Unpaid, $invoice->status);
        $this->assertDatabaseHas('invoice_items', [
            'invoice_id' => $invoice->id,
            'description' => 'Hebergement sejour #'.$stay->id.' chambre 101',
            'quantity' => 3,
            'unit_price' => 30000,
            'line_total' => 90000,
        ]);

        $stay->refresh();
        $this->assertSame('active', $stay->status->value);
        $this->assertSame('occupied', $stay->room->fresh()->status->value);
    }

    public function test_paid_stay_can_be_checked_out_and_room_goes_to_cleaning(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $stay = $this->createActiveStay();

        Livewire::test(Manager::class)
            ->call('checkOut', $stay->id)
            ->assertHasErrors(['checkout']);

        $invoice = Invoice::query()->where('stay_id', $stay->id)->firstOrFail();

        app(PaymentService::class)->record([
            'invoice_id' => $invoice->id,
            'customer_id' => $stay->customer_id,
            'recorded_by' => $user->id,
            'amount' => $invoice->balance,
            'method' => 'cash',
        ]);

        Livewire::test(Manager::class)
            ->call('checkOut', $stay->id)
            ->assertHasNoErrors();

        $stay->refresh();

        $this->assertSame('checked_out', $stay->status->value);
        $this->assertNotNull($stay->check_out_at);
        $this->assertSame('cleaning', $stay->room->fresh()->status->value);
        $this->assertSame('checked_out', $stay->reservation->fresh()->status->value);
        $this->assertSame(0.0, (float) $invoice->fresh()->balance);
    }

    private function createActiveStay(): Stay
    {
        $customer = Customer::query()->create([
            'full_name' => 'Client Checkout',
            'is_identified' => true,
        ]);

        $roomType = RoomType::query()->create([
            'name' => 'Standard',
            'code' => 'STD-CHECKOUT',
            'capacity' => 2,
            'base_price' => 30000,
        ]);

        $room = Room::query()->create([
            'room_type_id' => $roomType->id,
            'number' => '101',
            'capacity' => 2,
            'price' => 30000,
            'status' => 'occupied',
        ]);

        $reservation = Reservation::query()->create([
            'customer_id' => $customer->id,
            'room_id' => $room->id,
            'check_in_date' => now()->subDays(3)->toDateString(),
            'check_out_date' => now()->toDateString(),
            'adults' => 1,
            'children' => 0,
            'status' => 'checked_in',
        ]);

        return Stay::query()->create([
            'customer_id' => $customer->id,
            'room_id' => $room->id,
            'reservation_id' => $reservation->id,
            'check_in_at' => now()->subDays(3),
            'expected_check_out_at' => now(),
            'status' => 'active',
            'nightly_rate' => 30000,
        ]);
    }
}
