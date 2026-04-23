<?php

namespace Database\Seeders;

use App\Models\ProductCategory;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class InventoryReferenceSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Vivres frais',
                'code' => 'fresh-food',
                'description' => 'Viandes, poissons, legumes, fruits et autres produits frais.',
                'color' => 'emerald',
                'is_perishable' => true,
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Epicerie seche',
                'code' => 'dry-goods',
                'description' => 'Riz, farine, sucre, pates et ingredients secs.',
                'color' => 'amber',
                'is_perishable' => false,
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Boissons',
                'code' => 'drinks',
                'description' => 'Eaux, sodas, jus, boissons gazeuses et service bar.',
                'color' => 'sky',
                'is_perishable' => false,
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Surgeles',
                'code' => 'frozen',
                'description' => 'Produits conserves en congelation.',
                'color' => 'cyan',
                'is_perishable' => true,
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'name' => 'Condiments',
                'code' => 'condiments',
                'description' => 'Epices, huiles, sauces et assaisonnements.',
                'color' => 'orange',
                'is_perishable' => false,
                'sort_order' => 5,
                'is_active' => true,
            ],
            [
                'name' => 'Boulangerie & patisserie',
                'code' => 'bakery',
                'description' => 'Pain, farine speciale, levure et produits de patisserie.',
                'color' => 'yellow',
                'is_perishable' => true,
                'sort_order' => 6,
                'is_active' => true,
            ],
            [
                'name' => 'Entretien',
                'code' => 'cleaning',
                'description' => 'Produits de nettoyage, savon, javel et entretien general.',
                'color' => 'slate',
                'is_perishable' => false,
                'sort_order' => 7,
                'is_active' => true,
            ],
            [
                'name' => 'Consommables service',
                'code' => 'service-consumables',
                'description' => 'Serviettes, gobelets, emballages et consommables de service.',
                'color' => 'zinc',
                'is_perishable' => false,
                'sort_order' => 8,
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            ProductCategory::query()->updateOrCreate(
                ['code' => $category['code']],
                $category
            );
        }

        $suppliers = [
            [
                'name' => 'Marche Frais Central',
                'phone' => '+243970000001',
                'email' => 'frais@prostay.local',
                'address' => 'Marche central - produits frais',
            ],
            [
                'name' => 'Depot Boissons',
                'phone' => '+243970000002',
                'email' => 'boissons@prostay.local',
                'address' => 'Zone commerciale - boissons',
            ],
            [
                'name' => 'Grossiste Epicerie',
                'phone' => '+243970000003',
                'email' => 'epicerie@prostay.local',
                'address' => 'Entrepot de denrees seches',
            ],
            [
                'name' => 'Fournisseur Surgeles',
                'phone' => '+243970000004',
                'email' => 'surgele@prostay.local',
                'address' => 'Zone frigorifique',
            ],
            [
                'name' => 'Maison Entretien',
                'phone' => '+243970000005',
                'email' => 'entretien@prostay.local',
                'address' => 'Avenue de l hygiene',
            ],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::query()->updateOrCreate(
                ['name' => $supplier['name']],
                $supplier
            );
        }
    }
}
