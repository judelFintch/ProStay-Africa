<?php

namespace Tests\Feature\Orders;

use App\Livewire\Orders\CreateOrder;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\ServiceArea;
use App\Models\Stay;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CreateOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_switching_lodged_customer_clears_stale_stay_and_room(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->markUserAsServer($user);

        [$customerWithStay, $stay] = $this->createCustomerWithActiveStay('Client A', '101');
        $customerWithoutStay = Customer::query()->create([
            'full_name' => 'Client B',
            'is_identified' => true,
        ]);

        Livewire::test(CreateOrder::class)
            ->set('order_mode', 'lodged')
            ->set('customer_id', $customerWithStay->id)
            ->set('served_by', $user->id)
            ->assertSet('stay_id', $stay->id)
            ->assertSet('room_id', $stay->room_id)
            ->set('customer_id', $customerWithoutStay->id)
            ->assertSet('stay_id', null)
            ->assertSet('room_id', null);
    }

    public function test_lodged_order_rejects_non_active_stay_before_save(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->markUserAsServer($user);

        [$customer, $stay] = $this->createCustomerWithActiveStay('Client A', '201');
        $product = $this->createProductWithStock();

        $component = Livewire::test(CreateOrder::class)
            ->set('order_mode', 'lodged')
            ->set('customer_id', $customer->id)
            ->set('served_by', $user->id)
            ->set('items.0.item_type', 'stocked_product')
            ->set('items.0.product_id', $product->id)
            ->set('items.0.item_name', $product->name)
            ->set('items.0.quantity', 1)
            ->set('items.0.unit_price', 1000);

        $stay->update(['status' => 'checked_out']);

        $component->call('save')
            ->assertHasErrors(['stay_id']);

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_lodged_order_requires_active_stay_before_save(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->markUserAsServer($user);

        $customer = Customer::query()->create([
            'full_name' => 'Client Sans Sejour',
            'is_identified' => true,
        ]);

        Livewire::test(CreateOrder::class)
            ->set('order_mode', 'lodged')
            ->set('customer_id', $customer->id)
            ->set('served_by', $user->id)
            ->set('items.0.item_type', 'free_item')
            ->set('items.0.item_name', 'Service special')
            ->set('items.0.quantity', 1)
            ->set('items.0.unit_price', 500)
            ->call('save')
            ->assertHasErrors(['stay_id']);

        $this->assertDatabaseCount('orders', 0);
    }

    /**
     * @return array{Customer, Stay}
     */
    private function createCustomerWithActiveStay(string $customerName, string $roomNumber): array
    {
        $customer = Customer::query()->create([
            'full_name' => $customerName,
            'is_identified' => true,
        ]);

        $roomType = RoomType::query()->create([
            'name' => 'Standard',
            'code' => 'STD-' . $roomNumber,
            'capacity' => 2,
            'base_price' => 30000,
        ]);

        $room = Room::query()->create([
            'room_type_id' => $roomType->id,
            'number' => $roomNumber,
            'capacity' => 2,
            'price' => 30000,
            'status' => 'available',
        ]);

        $reservation = Reservation::query()->create([
            'customer_id' => $customer->id,
            'room_id' => $room->id,
            'check_in_date' => now()->toDateString(),
            'check_out_date' => now()->addDay()->toDateString(),
            'status' => 'confirmed',
        ]);

        $stay = Stay::query()->create([
            'customer_id' => $customer->id,
            'room_id' => $room->id,
            'reservation_id' => $reservation->id,
            'check_in_at' => now()->subHour(),
            'expected_check_out_at' => now()->addDay(),
            'status' => 'active',
            'nightly_rate' => 30000,
        ]);

        return [$customer, $stay];
    }

    private function createProductWithStock(): Product
    {
        $category = ProductCategory::query()->create([
            'name' => 'Boissons',
            'code' => 'boissons',
            'is_active' => true,
        ]);

        $supplier = Supplier::query()->create([
            'name' => 'Fournisseur Test',
        ]);

        ServiceArea::query()->firstOrCreate(
            ['code' => 'restaurant'],
            ['name' => 'Restaurant', 'is_active' => true]
        );

        return Product::query()->create([
            'product_category_id' => $category->id,
            'supplier_id' => $supplier->id,
            'name' => 'Eau minerale',
            'sku' => 'SKU-EAU-001',
            'unit' => 'u',
            'purchase_unit' => 'u',
            'unit_cost' => 250,
            'selling_price' => 1000,
            'stock_quantity' => 20,
            'alert_threshold' => 2,
            'is_active' => true,
        ]);
    }

    private function markUserAsServer(User $user): void
    {
        $user->forceFill([
            'is_server' => true,
            'server_active' => true,
        ])->save();
    }
}
