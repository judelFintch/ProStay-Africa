<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('service_area_id')
                ->nullable()
                ->after('supplier_id')
                ->constrained()
                ->nullOnDelete();
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->foreignId('service_area_id')
                ->nullable()
                ->after('product_id')
                ->constrained()
                ->nullOnDelete();

            $table->index(['service_area_id', 'movement_type']);
        });
    }

    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropIndex(['service_area_id', 'movement_type']);
            $table->dropConstrainedForeignId('service_area_id');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('service_area_id');
        });
    }
};
