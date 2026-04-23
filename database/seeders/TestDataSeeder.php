<?php

namespace Database\Seeders;

use App\Enums\CustomerType;
use App\Enums\InvoiceStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\RoomStatus;
use App\Enums\StayStatus;
use App\Models\Customer;
use App\Models\DiningTable;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Menu;
use App\Models\MenuCategory;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Role;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\ServiceArea;
use App\Models\Stay;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        $admin = $this->createUserWithRole('admin@prostay.africa', 'Admin ProStay', 'admin');
        $receptionist = $this->createUserWithRole('reception@prostay.africa', 'Reception ProStay', 'receptionist');
        $cashier = $this->createUserWithRole('cashier@prostay.africa', 'Cashier ProStay', 'cashier');
        $this->createUserWithRole('waiter@prostay.africa', 'Waiter ProStay', 'waiter');
        $this->createUserWithRole('barman@prostay.africa', 'Barman ProStay', 'barman');
        $this->createUserWithRole('stock@prostay.africa', 'Stock Manager ProStay', 'stock_manager');
        $this->createUserWithRole('laundry@prostay.africa', 'Laundry Staff ProStay', 'laundry_staff');

        $standard = RoomType::query()->updateOrCreate(
            ['code' => 'STD'],
            ['name' => 'Standard', 'capacity' => 2, 'base_price' => 35000, 'description' => 'Chambre standard']
        );

        $deluxe = RoomType::query()->updateOrCreate(
            ['code' => 'DLX'],
            ['name' => 'Deluxe', 'capacity' => 3, 'base_price' => 55000, 'description' => 'Chambre deluxe']
        );

        $room101 = Room::query()->updateOrCreate(
            ['number' => '101'],
            ['room_type_id' => $standard->id, 'floor' => '1', 'capacity' => 2, 'price' => 35000, 'status' => RoomStatus::Available->value]
        );

        Room::query()->updateOrCreate(
            ['number' => '102'],
            ['room_type_id' => $standard->id, 'floor' => '1', 'capacity' => 2, 'price' => 37000, 'status' => RoomStatus::Cleaning->value]
        );

        $room201 = Room::query()->updateOrCreate(
            ['number' => '201'],
            ['room_type_id' => $deluxe->id, 'floor' => '2', 'capacity' => 3, 'price' => 56000, 'status' => RoomStatus::Occupied->value]
        );

        $customerA = Customer::query()->updateOrCreate(
            ['email' => 'amina.diallo@example.com'],
            ['full_name' => 'Amina Diallo', 'phone' => '+221770000001', 'country' => 'Senegal', 'address' => 'Dakar', 'is_identified' => true]
        );

        $customerB = Customer::query()->updateOrCreate(
            ['email' => 'moussa.konate@example.com'],
            ['full_name' => 'Moussa Konate', 'phone' => '+223660000002', 'country' => 'Mali', 'address' => 'Bamako', 'is_identified' => true]
        );

        $customerWalkIn = Customer::query()->updateOrCreate(
            ['phone' => '+225070000003'],
            ['full_name' => 'Walk-In Identified', 'email' => null, 'country' => 'Cote d\'Ivoire', 'address' => null, 'is_identified' => true]
        );

        $stay = Stay::query()->updateOrCreate(
            ['customer_id' => $customerA->id, 'room_id' => $room201->id, 'status' => StayStatus::Active->value],
            [
                'reservation_id' => null,
                'check_in_at' => now()->subDay(),
                'expected_check_out_at' => now()->addDays(2),
                'check_out_at' => null,
                'nightly_rate' => $room201->price,
                'notes' => 'Sejour test actif',
            ]
        );

        $restaurant = ServiceArea::query()->where('code', 'restaurant')->first();
        $bar = ServiceArea::query()->where('code', 'bar')->first();
        $pos = ServiceArea::query()->where('code', 'pos')->first();

        $tableR1 = null;
        if ($restaurant) {
            $tableR1 = DiningTable::query()->updateOrCreate(
                ['service_area_id' => $restaurant->id, 'number' => 'R1'],
                ['capacity' => 4, 'status' => 'free']
            );
        }

        $catFood = null;
        if ($restaurant) {
            $catFood = MenuCategory::query()->updateOrCreate(
                ['name' => 'Plats chauds', 'service_area_id' => $restaurant->id],
                []
            );
        }

        $catDrinks = null;
        if ($bar) {
            $catDrinks = MenuCategory::query()->updateOrCreate(
                ['name' => 'Boissons', 'service_area_id' => $bar->id],
                []
            );
        }

        $menuThieb = null;
        if ($catFood && $restaurant) {
            $menuThieb = Menu::query()->updateOrCreate(
                ['sku' => 'MNU-THIEB'],
                [
                    'menu_category_id' => $catFood->id,
                    'service_area_id' => $restaurant->id,
                    'name' => 'Thieboudienne',
                    'price' => 8000,
                    'is_available' => true,
                ]
            );
        }

        $menuJus = null;
        if ($catDrinks && $bar) {
            $menuJus = Menu::query()->updateOrCreate(
                ['sku' => 'MNU-JUS-BISSAP'],
                [
                    'menu_category_id' => $catDrinks->id,
                    'service_area_id' => $bar->id,
                    'name' => 'Jus de bissap',
                    'price' => 2500,
                    'is_available' => true,
                ]
            );
        }

        $catFresh = ProductCategory::query()->updateOrCreate(
            ['code' => 'fresh-food'],
            ['name' => 'Vivres frais', 'description' => 'Produits frais pour la cuisine', 'color' => 'emerald', 'is_perishable' => true, 'sort_order' => 1, 'is_active' => true]
        );

        $catDry = ProductCategory::query()->updateOrCreate(
            ['code' => 'dry-goods'],
            ['name' => 'Epicerie seche', 'description' => 'Denrees seches et ingredients', 'color' => 'amber', 'is_perishable' => false, 'sort_order' => 2, 'is_active' => true]
        );

        $catDrinksStock = ProductCategory::query()->updateOrCreate(
            ['code' => 'drinks'],
            ['name' => 'Boissons', 'description' => 'Boissons fraiches et service bar', 'color' => 'sky', 'is_perishable' => false, 'sort_order' => 3, 'is_active' => true]
        );

        $catHousekeeping = ProductCategory::query()->updateOrCreate(
            ['code' => 'cleaning'],
            ['name' => 'Entretien', 'description' => 'Produits de nettoyage et entretien', 'color' => 'slate', 'is_perishable' => false, 'sort_order' => 6, 'is_active' => true]
        );
        $supplier = Supplier::query()->updateOrCreate(
            ['name' => 'Fournisseur Central'],
            ['phone' => '+221780000010', 'email' => 'supply@example.com', 'address' => 'Dakar']
        );

        $freshSupplier = Supplier::query()->updateOrCreate(
            ['name' => 'Marche Frais'],
            ['phone' => '+221780000020', 'email' => 'fresh@example.com', 'address' => 'Dakar']
        );

        $productWater = Product::query()->updateOrCreate(
            ['sku' => 'PRD-EAU-50CL'],
            [
                'product_category_id' => $catDrinksStock->id,
                'supplier_id' => $supplier->id,
                'name' => 'Eau minerale 50cl',
                'unit' => 'bottle',
                'purchase_unit' => 'carton',
                'storage_area' => 'Bar / reserve boissons',
                'is_perishable' => false,
                'unit_cost' => 200,
                'selling_price' => 1000,
                'stock_quantity' => 500,
                'alert_threshold' => 40,
                'is_active' => true,
            ]
        );

        Product::query()->updateOrCreate(
            ['sku' => 'PRD-POULET-FRAIS'],
            [
                'product_category_id' => $catFresh->id,
                'supplier_id' => $freshSupplier->id,
                'name' => 'Poulet frais',
                'unit' => 'kg',
                'purchase_unit' => 'crate',
                'storage_area' => 'Chambre froide cuisine',
                'is_perishable' => true,
                'expires_at' => now()->addDays(3)->toDateString(),
                'unit_cost' => 3200,
                'selling_price' => 0,
                'stock_quantity' => 25,
                'alert_threshold' => 8,
                'is_active' => true,
            ]
        );

        Product::query()->updateOrCreate(
            ['sku' => 'PRD-RIZ-BR-25'],
            [
                'product_category_id' => $catDry->id,
                'supplier_id' => $supplier->id,
                'name' => 'Riz brise 25kg',
                'unit' => 'kg',
                'purchase_unit' => 'bag',
                'storage_area' => 'Reserve seche',
                'is_perishable' => false,
                'unit_cost' => 700,
                'selling_price' => 0,
                'stock_quantity' => 120,
                'alert_threshold' => 30,
                'is_active' => true,
            ]
        );

        $order = Order::query()->updateOrCreate(
            ['reference' => 'ORD-DEMO-0001'],
            [
                'service_area_id' => $restaurant?->id,
                'customer_id' => $customerA->id,
                'stay_id' => $stay->id,
                'room_id' => $room201->id,
                'dining_table_id' => $tableR1?->id,
                'created_by' => $cashier->id,
                'customer_type' => CustomerType::Lodged->value,
                'status' => OrderStatus::Closed->value,
                'notes' => 'Commande test client heberge',
                'subtotal' => 0,
                'tax_amount' => 0,
                'discount_amount' => 0,
                'total' => 0,
            ]
        );

        OrderItem::query()->updateOrCreate(
            ['order_id' => $order->id, 'item_name' => 'Thieboudienne'],
            [
                'menu_id' => $menuThieb?->id,
                'product_id' => null,
                'quantity' => 2,
                'unit_price' => 8000,
                'line_total' => 16000,
            ]
        );

        OrderItem::query()->updateOrCreate(
            ['order_id' => $order->id, 'item_name' => 'Jus de bissap'],
            [
                'menu_id' => $menuJus?->id,
                'product_id' => null,
                'quantity' => 2,
                'unit_price' => 2500,
                'line_total' => 5000,
            ]
        );

        OrderItem::query()->updateOrCreate(
            ['order_id' => $order->id, 'item_name' => 'Eau minerale 50cl'],
            [
                'menu_id' => null,
                'product_id' => $productWater->id,
                'quantity' => 1,
                'unit_price' => 1000,
                'line_total' => 1000,
            ]
        );

        $orderTotal = (float) $order->items()->sum('line_total');
        $order->update([
            'subtotal' => $orderTotal,
            'total' => $orderTotal,
        ]);

        $invoice = Invoice::query()->updateOrCreate(
            ['reference' => 'INV-DEMO-0001'],
            [
                'customer_id' => $customerA->id,
                'stay_id' => $stay->id,
                'room_id' => $room201->id,
                'issued_by' => $cashier->id,
                'issued_at' => now()->subHours(2),
                'due_at' => now()->addDays(1),
                'status' => InvoiceStatus::Unpaid->value,
                'subtotal' => $orderTotal,
                'tax_amount' => 0,
                'discount_amount' => 0,
                'total' => $orderTotal,
                'paid_total' => 0,
                'balance' => $orderTotal,
                'notes' => 'Facture de demonstration',
            ]
        );

        foreach ($order->items as $item) {
            InvoiceItem::query()->updateOrCreate(
                ['invoice_id' => $invoice->id, 'description' => $item->item_name],
                [
                    'order_item_id' => $item->id,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'line_total' => $item->line_total,
                ]
            );
        }

        Payment::query()->updateOrCreate(
            ['reference' => 'PAY-DEMO-0001'],
            [
                'invoice_id' => $invoice->id,
                'order_id' => $order->id,
                'customer_id' => $customerA->id,
                'recorded_by' => $cashier->id,
                'method' => PaymentMethod::MobileMoney->value,
                'amount' => 10000,
                'currency' => 'XOF',
                'provider_reference' => 'MOMO-TEST-0001',
                'paid_at' => now()->subHour(),
                'notes' => 'Paiement partiel test',
            ]
        );

        $paid = (float) $invoice->payments()->sum('amount');
        $balance = max(0, $invoice->total - $paid);
        $status = $balance === 0.0 ? InvoiceStatus::Paid->value : InvoiceStatus::PartiallyPaid->value;

        $invoice->update([
            'paid_total' => $paid,
            'balance' => $balance,
            'status' => $status,
        ]);

        Order::query()->updateOrCreate(
            ['reference' => 'ORD-DEMO-POS-0001'],
            [
                'service_area_id' => $pos?->id,
                'customer_id' => $customerWalkIn->id,
                'stay_id' => null,
                'room_id' => $room101->id,
                'dining_table_id' => null,
                'created_by' => $cashier->id,
                'customer_type' => CustomerType::WalkInIdentified->value,
                'status' => OrderStatus::Closed->value,
                'notes' => 'Quick sale test',
                'subtotal' => 6000,
                'tax_amount' => 0,
                'discount_amount' => 0,
                'total' => 6000,
            ]
        );
    }

    private function createUserWithRole(string $email, string $name, string $roleName): User
    {
        $user = User::query()->updateOrCreate(
            ['email' => $email],
            ['name' => $name, 'password' => 'password', 'email_verified_at' => now()]
        );

        $role = Role::query()->where('name', $roleName)->first();
        if ($role) {
            $user->roles()->syncWithoutDetaching([$role->id]);
        }

        return $user;
    }
}
