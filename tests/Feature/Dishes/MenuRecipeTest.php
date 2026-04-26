<?php

namespace Tests\Feature\Dishes;

use App\Enums\CustomerType;
use App\Enums\OrderStatus;
use App\Models\Menu;
use App\Models\MenuCategory;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ServiceArea;
use App\Models\User;
use App\Services\Menu\MenuRecipeService;
use App\Services\Orders\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuRecipeTest extends TestCase
{
    use RefreshDatabase;

    public function test_recipe_availability_is_computed_from_ingredient_stock(): void
    {
        [$menu] = $this->createDishFixture();

        $availability = app(MenuRecipeService::class)->availability($menu, 2);

        $this->assertTrue($availability['is_available']);
        $this->assertSame(5, $availability['max_servings']);

        $tooMany = app(MenuRecipeService::class)->availability($menu, 6);

        $this->assertFalse($tooMany['is_available']);
        $this->assertSame('Poulet frais', $tooMany['missing']->first()['product']->name);
    }

    public function test_ordering_dish_deducts_recipe_ingredients(): void
    {
        [$menu, $chicken, $rice] = $this->createDishFixture();
        $user = User::factory()->create();

        app(OrderService::class)->create([
            'customer_type' => CustomerType::WalkInAnonymous->value,
            'status' => OrderStatus::Confirmed->value,
            'created_by' => $user->id,
            'served_by' => $user->id,
            'items' => [
                [
                    'menu_id' => $menu->id,
                    'item_name' => $menu->name,
                    'quantity' => 2,
                    'unit_price' => 12000,
                ],
            ],
        ]);

        $this->assertSame(3.0, (float) $chicken->fresh()->stock_quantity);
        $this->assertSame(7.0, (float) $rice->fresh()->stock_quantity);
    }

    private function createDishFixture(): array
    {
        $area = ServiceArea::query()->firstOrCreate(
            ['code' => 'restaurant'],
            ['name' => 'Restaurant', 'is_active' => true],
        );

        $menuCategory = MenuCategory::query()->create([
            'name' => 'Plats chauds',
            'service_area_id' => $area->id,
        ]);

        $productCategory = ProductCategory::query()->firstOrCreate(
            ['code' => 'fresh-food'],
            ['name' => 'Vivres frais', 'is_active' => true],
        );

        $chicken = Product::query()->create([
            'product_category_id' => $productCategory->id,
            'name' => 'Poulet frais',
            'sku' => 'TEST-CHICKEN',
            'unit' => 'kg',
            'unit_cost' => 3000,
            'selling_price' => 0,
            'stock_quantity' => 5,
            'alert_threshold' => 1,
            'is_active' => true,
        ]);

        $rice = Product::query()->create([
            'product_category_id' => $productCategory->id,
            'name' => 'Riz',
            'sku' => 'TEST-RICE',
            'unit' => 'kg',
            'unit_cost' => 800,
            'selling_price' => 0,
            'stock_quantity' => 10,
            'alert_threshold' => 2,
            'is_active' => true,
        ]);

        $menu = Menu::query()->create([
            'menu_category_id' => $menuCategory->id,
            'service_area_id' => $area->id,
            'name' => 'Poulet riz',
            'sku' => 'DISH-POULET-RIZ',
            'price' => 12000,
            'is_available' => true,
        ]);

        $menu->ingredients()->createMany([
            ['product_id' => $chicken->id, 'quantity' => 1, 'unit' => 'kg'],
            ['product_id' => $rice->id, 'quantity' => 1.5, 'unit' => 'kg'],
        ]);

        return [$menu->fresh('ingredients.product'), $chicken, $rice];
    }
}
