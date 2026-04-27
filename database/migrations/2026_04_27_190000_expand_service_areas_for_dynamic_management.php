<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('service_areas', function (Blueprint $table) {
            $table->string('domain')->default('shared')->after('code');
            $table->text('description')->nullable()->after('domain');
            $table->unsignedInteger('sort_order')->default(0)->after('description');
            $table->boolean('supports_orders')->default(false)->after('is_active');
            $table->boolean('supports_menu')->default(false)->after('supports_orders');
            $table->boolean('supports_pos')->default(false)->after('supports_menu');
            $table->boolean('supports_stock')->default(true)->after('supports_pos');
            $table->boolean('supports_tables')->default(false)->after('supports_stock');
        });

        DB::table('service_areas')->where('code', 'accommodation')->update([
            'domain' => 'hotel',
            'description' => 'Service hotelier pour hebergement, chambres et sejours.',
            'sort_order' => 10,
            'supports_stock' => true,
        ]);

        DB::table('service_areas')->where('code', 'restaurant')->update([
            'domain' => 'restaurant',
            'description' => 'Service principal de restauration avec commandes, cartes et tables.',
            'sort_order' => 20,
            'supports_orders' => true,
            'supports_menu' => true,
            'supports_pos' => true,
            'supports_stock' => true,
            'supports_tables' => true,
        ]);

        DB::table('service_areas')->where('code', 'bar')->update([
            'domain' => 'restaurant',
            'description' => 'Point de vente bar rattache aux commandes et a la caisse.',
            'sort_order' => 30,
            'supports_orders' => true,
            'supports_menu' => true,
            'supports_pos' => true,
            'supports_stock' => true,
            'supports_tables' => true,
        ]);

        DB::table('service_areas')->where('code', 'terrace')->update([
            'domain' => 'restaurant',
            'description' => 'Zone terrasse pour service a table et carte dediee.',
            'sort_order' => 40,
            'supports_orders' => true,
            'supports_menu' => true,
            'supports_pos' => true,
            'supports_stock' => true,
            'supports_tables' => true,
        ]);

        DB::table('service_areas')->where('code', 'laundry')->update([
            'domain' => 'hotel',
            'description' => 'Service blanchisserie et traitement du linge.',
            'sort_order' => 50,
            'supports_stock' => true,
        ]);

        DB::table('service_areas')->where('code', 'pos')->update([
            'domain' => 'restaurant',
            'description' => 'Caisse comptoir pour ventes rapides et encaissement.',
            'sort_order' => 60,
            'supports_orders' => true,
            'supports_pos' => true,
            'supports_stock' => true,
        ]);
    }

    public function down(): void
    {
        Schema::table('service_areas', function (Blueprint $table) {
            $table->dropColumn([
                'domain',
                'description',
                'sort_order',
                'supports_orders',
                'supports_menu',
                'supports_pos',
                'supports_stock',
                'supports_tables',
            ]);
        });
    }
};