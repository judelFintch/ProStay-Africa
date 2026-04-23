<?php

use App\Enums\LaundryItemStatus;
use App\Enums\ServiceAreaCode;
use App\Enums\TableStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('service_areas', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('dining_tables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_area_id')->constrained()->restrictOnDelete();
            $table->string('number');
            $table->unsignedSmallInteger('capacity')->default(2);
            $table->string('status')->default(TableStatus::Free->value);
            $table->timestamps();

            $table->unique(['service_area_id', 'number']);
            $table->index('status');
        });

        Schema::create('menu_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('service_area_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_category_id')->constrained()->restrictOnDelete();
            $table->foreignId('service_area_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('sku')->nullable()->unique();
            $table->decimal('price', 12, 2);
            $table->boolean('is_available')->default(true);
            $table->timestamps();

            $table->index('is_available');
        });

        Schema::create('product_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('sku')->nullable()->unique();
            $table->string('unit')->default('unit');
            $table->decimal('unit_cost', 12, 2)->default(0);
            $table->decimal('selling_price', 12, 2)->default(0);
            $table->decimal('stock_quantity', 12, 2)->default(0);
            $table->decimal('alert_threshold', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'stock_quantity']);
        });

        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('movement_type');
            $table->decimal('quantity', 12, 2);
            $table->decimal('unit_cost', 12, 2)->default(0);
            $table->text('reason')->nullable();
            $table->timestamps();

            $table->index(['product_id', 'movement_type']);
        });

        Schema::create('laundry_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('status')->default(LaundryItemStatus::Dirty->value);
            $table->unsignedInteger('quantity')->default(1);
            $table->timestamps();

            $table->index('status');
        });

        Schema::create('laundry_operations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laundry_item_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('from_status');
            $table->string('to_status');
            $table->unsignedInteger('quantity')->default(1);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        $now = now();
        Schema::disableForeignKeyConstraints();
        DB::table('service_areas')->insert([
            ['name' => 'Accommodation', 'code' => ServiceAreaCode::Accommodation->value, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Restaurant', 'code' => ServiceAreaCode::Restaurant->value, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Bar', 'code' => ServiceAreaCode::Bar->value, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Terrace', 'code' => ServiceAreaCode::Terrace->value, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Laundry', 'code' => ServiceAreaCode::Laundry->value, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'POS', 'code' => ServiceAreaCode::Pos->value, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);
        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::dropIfExists('laundry_operations');
        Schema::dropIfExists('laundry_items');
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('products');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('product_categories');
        Schema::dropIfExists('menus');
        Schema::dropIfExists('menu_categories');
        Schema::dropIfExists('dining_tables');
        Schema::dropIfExists('service_areas');
    }
};
