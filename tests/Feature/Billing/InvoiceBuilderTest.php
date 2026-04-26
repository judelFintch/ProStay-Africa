<?php

namespace Tests\Feature\Billing;

use App\Enums\InvoiceStatus;
use App\Enums\OrderStatus;
use App\Livewire\Billing\InvoiceBuilder;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class InvoiceBuilderTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_append_delivered_order_to_existing_invoice(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $customer = Customer::query()->create([
            'full_name' => 'Client Facturation',
            'is_identified' => true,
        ]);

        $invoice = Invoice::query()->create([
            'reference' => 'INV-OPEN-001',
            'customer_id' => $customer->id,
            'status' => InvoiceStatus::Unpaid->value,
            'issued_by' => $user->id,
            'issued_at' => now(),
        ]);

        $order = Order::query()->create([
            'reference' => 'ORD-SERVED-001',
            'customer_id' => $customer->id,
            'status' => OrderStatus::Served->value,
            'customer_type' => 'walk_in_anonymous',
            'created_by' => $user->id,
            'served_by' => $user->id,
        ]);

        $orderItem = OrderItem::query()->create([
            'order_id' => $order->id,
            'item_name' => 'Plat du jour',
            'quantity' => 2,
            'unit_price' => 5000,
            'line_total' => 10000,
        ]);

        Livewire::test(InvoiceBuilder::class)
            ->set('build_mode', 'existing')
            ->set('target_invoice_id', $invoice->id)
            ->set('selectedOrderIds', [$order->id])
            ->call('build')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('invoice_items', [
            'invoice_id' => $invoice->id,
            'order_item_id' => $orderItem->id,
        ]);

        $invoice->refresh();

        $this->assertSame(10000.0, (float) $invoice->total);
        $this->assertSame(10000.0, (float) $invoice->balance);
    }

    public function test_it_rejects_incompatible_order_for_existing_invoice(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $customerA = Customer::query()->create(['full_name' => 'Client A', 'is_identified' => true]);
        $customerB = Customer::query()->create(['full_name' => 'Client B', 'is_identified' => true]);

        $invoice = Invoice::query()->create([
            'reference' => 'INV-OPEN-002',
            'customer_id' => $customerA->id,
            'status' => InvoiceStatus::Unpaid->value,
            'issued_by' => $user->id,
            'issued_at' => now(),
        ]);

        $order = Order::query()->create([
            'reference' => 'ORD-SERVED-002',
            'customer_id' => $customerB->id,
            'status' => OrderStatus::Served->value,
            'customer_type' => 'walk_in_anonymous',
            'created_by' => $user->id,
            'served_by' => $user->id,
        ]);

        OrderItem::query()->create([
            'order_id' => $order->id,
            'item_name' => 'Boisson',
            'quantity' => 1,
            'unit_price' => 2000,
            'line_total' => 2000,
        ]);

        Livewire::test(InvoiceBuilder::class)
            ->set('build_mode', 'existing')
            ->set('target_invoice_id', $invoice->id)
            ->set('selectedOrderIds', [$order->id])
            ->call('build')
            ->assertHasErrors(['selectedOrderIds']);

        $this->assertDatabaseCount('invoice_items', 0);
    }
}
