<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('product_categories', function (Blueprint $table) {
            $table->string('code')->nullable()->unique()->after('name');
            $table->text('description')->nullable()->after('code');
            $table->string('color', 30)->nullable()->after('description');
            $table->boolean('is_perishable')->default(false)->after('color');
            $table->unsignedSmallInteger('sort_order')->default(0)->after('is_perishable');
            $table->boolean('is_active')->default(true)->after('sort_order');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->string('purchase_unit')->nullable()->after('unit');
            $table->string('storage_area')->nullable()->after('purchase_unit');
            $table->boolean('is_perishable')->default(false)->after('storage_area');
            $table->date('expires_at')->nullable()->after('is_perishable');
        });

        $categories = [
            ['name' => 'Vivres frais', 'code' => 'fresh-food', 'description' => 'Viandes, poissons, legumes, fruits et produits frais.', 'color' => 'emerald', 'is_perishable' => true, 'sort_order' => 1],
            ['name' => 'Epicerie seche', 'code' => 'dry-goods', 'description' => 'Farine, riz, sucre, pates et denrees seches.', 'color' => 'amber', 'is_perishable' => false, 'sort_order' => 2],
            ['name' => 'Boissons', 'code' => 'drinks', 'description' => 'Eaux, jus, sodas et boissons diverses.', 'color' => 'sky', 'is_perishable' => false, 'sort_order' => 3],
            ['name' => 'Surgeles', 'code' => 'frozen', 'description' => 'Produits conserves au froid negatif.', 'color' => 'cyan', 'is_perishable' => true, 'sort_order' => 4],
            ['name' => 'Condiments', 'code' => 'condiments', 'description' => 'Epices, huiles, sauces et aides culinaires.', 'color' => 'orange', 'is_perishable' => false, 'sort_order' => 5],
            ['name' => 'Entretien', 'code' => 'cleaning', 'description' => 'Produits de nettoyage et d hygiene.', 'color' => 'slate', 'is_perishable' => false, 'sort_order' => 6],
        ];

        foreach ($categories as $category) {
            DB::table('product_categories')->updateOrInsert(
                ['code' => $category['code']],
                array_merge($category, [
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        DB::table('product_categories')
            ->whereNull('code')
            ->orderBy('id')
            ->get()
            ->each(function (object $category): void {
                DB::table('product_categories')
                    ->where('id', $category->id)
                    ->update([
                        'code' => 'cat-' . $category->id,
                        'color' => 'slate',
                        'is_active' => true,
                        'updated_at' => now(),
                    ]);
            });

        DB::table('products')
            ->whereNull('purchase_unit')
            ->update([
                'purchase_unit' => DB::raw('unit'),
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'purchase_unit',
                'storage_area',
                'is_perishable',
                'expires_at',
            ]);
        });

        Schema::table('product_categories', function (Blueprint $table) {
            $table->dropColumn([
                'code',
                'description',
                'color',
                'is_perishable',
                'sort_order',
                'is_active',
            ]);
        });
    }
};
