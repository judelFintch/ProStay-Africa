<?php

namespace App\Livewire\Dishes;

use App\Models\Menu;
use App\Models\MenuCategory;
use App\Models\Product;
use App\Models\ServiceArea;
use App\Services\Menu\MenuRecipeService;
use Illuminate\Support\Str;
use Livewire\Component;

class Manager extends Component
{
    public ?int $editing_menu_id = null;
    public ?int $menu_category_id = null;
    public ?int $service_area_id = null;
    public string $name = '';
    public ?string $sku = null;
    public float $price = 0;
    public bool $is_available = true;
    public array $ingredients = [
        ['product_id' => null, 'quantity' => 1, 'unit' => ''],
    ];

    public function addIngredient(): void
    {
        $this->ingredients[] = ['product_id' => null, 'quantity' => 1, 'unit' => ''];
    }

    public function removeIngredient(int $index): void
    {
        unset($this->ingredients[$index]);
        $this->ingredients = array_values($this->ingredients);

        if ($this->ingredients === []) {
            $this->addIngredient();
        }
    }

    public function edit(int $menuId): void
    {
        $menu = Menu::query()->with('ingredients.product')->findOrFail($menuId);

        $this->editing_menu_id = $menu->id;
        $this->menu_category_id = $menu->menu_category_id;
        $this->service_area_id = $menu->service_area_id;
        $this->name = $menu->name;
        $this->sku = $menu->sku;
        $this->price = (float) $menu->price;
        $this->is_available = (bool) $menu->is_available;
        $this->ingredients = $menu->ingredients
            ->map(fn ($ingredient): array => [
                'product_id' => $ingredient->product_id,
                'quantity' => (float) $ingredient->quantity,
                'unit' => $ingredient->unit ?: $ingredient->product?->unit,
            ])
            ->values()
            ->all() ?: [['product_id' => null, 'quantity' => 1, 'unit' => '']];
    }

    public function resetForm(): void
    {
        $this->reset(['editing_menu_id', 'menu_category_id', 'service_area_id', 'name', 'sku']);
        $this->price = 0;
        $this->is_available = true;
        $this->ingredients = [['product_id' => null, 'quantity' => 1, 'unit' => '']];
        $this->resetErrorBag();
    }

    public function save(MenuRecipeService $recipeService): void
    {
        $this->validate([
            'menu_category_id' => ['required', 'exists:menu_categories,id'],
            'service_area_id' => ['nullable', 'exists:service_areas,id'],
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:100'],
            'price' => ['required', 'numeric', 'min:0'],
            'is_available' => ['boolean'],
            'ingredients' => ['required', 'array', 'min:1'],
            'ingredients.*.product_id' => ['nullable', 'exists:products,id'],
            'ingredients.*.quantity' => ['nullable', 'numeric', 'min:0.001'],
            'ingredients.*.unit' => ['nullable', 'string', 'max:50'],
        ]);

        $menu = Menu::query()->updateOrCreate(
            ['id' => $this->editing_menu_id],
            [
                'menu_category_id' => $this->menu_category_id,
                'service_area_id' => $this->service_area_id,
                'name' => $this->name,
                'sku' => $this->sku ?: 'DISH-'.Str::upper(Str::random(8)),
                'price' => $this->price,
                'is_available' => $this->is_available,
            ],
        );

        $recipeService->syncIngredients($menu, $this->ingredients);
        $this->resetForm();
    }

    public function render()
    {
        $recipeService = app(MenuRecipeService::class);

        $menus = Menu::query()
            ->with(['category', 'serviceArea', 'ingredients.product'])
            ->orderBy('name')
            ->get()
            ->map(function (Menu $menu) use ($recipeService): Menu {
                $menu->setAttribute('recipe_availability', $recipeService->availability($menu));

                return $menu;
            });

        return view('livewire.dishes.manager', [
            'menus' => $menus,
            'categories' => MenuCategory::query()->orderBy('name')->get(),
            'areas' => ServiceArea::query()
                ->active()
                ->forDomain('restaurant')
                ->supporting('menu')
                ->ordered()
                ->get(),
            'products' => Product::query()
                ->with('category')
                ->orderByRaw('product_category_id is null')
                ->orderBy('product_category_id')
                ->orderBy('name')
                ->get(),
        ]);
    }
}
