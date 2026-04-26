<?php

namespace App\Services\Menu;

use App\Models\Menu;
use App\Models\Product;
use App\Services\Stock\StockService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class MenuRecipeService
{
    public function availability(Menu $menu, float $servings = 1): array
    {
        $menu->loadMissing('ingredients.product');
        $servings = max(1, $servings);

        if ($menu->ingredients->isEmpty()) {
            return [
                'is_available' => (bool) $menu->is_available,
                'max_servings' => null,
                'missing' => collect(),
            ];
        }

        $missing = $menu->ingredients
            ->filter(function ($ingredient) use ($servings): bool {
                if (! $ingredient->product) {
                    return true;
                }

                $required = (float) $ingredient->quantity * $servings;

                return (float) $ingredient->product->stock_quantity < $required;
            })
            ->map(function ($ingredient) use ($servings): array {
                $required = (float) $ingredient->quantity * $servings;
                $product = $ingredient->product;

                return [
                    'product' => $product,
                    'name' => $product?->name ?? 'Article supprime ou introuvable',
                    'required' => $required,
                    'available' => $product ? (float) $product->stock_quantity : 0,
                    'unit' => $ingredient->unit ?: ($product?->unit ?? ''),
                    'missing_product' => ! $product,
                ];
            })
            ->values();

        return [
            'is_available' => (bool) $menu->is_available && $missing->isEmpty(),
            'max_servings' => $this->maxServings($menu),
            'missing' => $missing,
        ];
    }

    public function maxServings(Menu $menu): ?int
    {
        $menu->loadMissing('ingredients.product');

        if ($menu->ingredients->isEmpty()) {
            return null;
        }

        return (int) $menu->ingredients
            ->map(function ($ingredient): int {
                if (! $ingredient->product) {
                    return 0;
                }

                $quantity = (float) $ingredient->quantity;

                if ($quantity <= 0) {
                    return 0;
                }

                return (int) floor((float) $ingredient->product->stock_quantity / $quantity);
            })
            ->min();
    }

    public function syncIngredients(Menu $menu, array $ingredients): void
    {
        DB::transaction(function () use ($menu, $ingredients): void {
            $normalized = collect($ingredients)
                ->filter(fn (array $ingredient): bool => ! empty($ingredient['product_id']) && (float) ($ingredient['quantity'] ?? 0) > 0)
                ->mapWithKeys(function (array $ingredient): array {
                    $product = Product::query()->findOrFail($ingredient['product_id']);

                    return [
                        $product->id => [
                            'quantity' => (float) $ingredient['quantity'],
                            'unit' => $ingredient['unit'] ?: $product->unit,
                        ],
                    ];
                });

            $menu->ingredients()->delete();

            $normalized->each(function (array $ingredient, int $productId) use ($menu): void {
                $menu->ingredients()->create([
                    'product_id' => $productId,
                    'quantity' => $ingredient['quantity'],
                    'unit' => $ingredient['unit'],
                ]);
            });
        });
    }

    public function deductIngredients(Menu $menu, float $servings, ?int $userId = null, ?string $reason = null): void
    {
        DB::transaction(function () use ($menu, $servings, $userId, $reason): void {
            $menu->loadMissing('ingredients.product');
            $availability = $this->availability($menu, $servings);

            if (! $availability['is_available']) {
                $names = $availability['missing'] instanceof Collection
                    ? $availability['missing']->map(fn (array $missing): string => $missing['product']->name)->join(', ')
                    : 'ingredients';

                throw new RuntimeException('Plat indisponible: stock insuffisant pour '.$names.'.');
            }

            foreach ($menu->ingredients as $ingredient) {
                $quantity = (float) $ingredient->quantity * max(1, $servings);

                app(StockService::class)->moveOut(
                    product: $ingredient->product,
                    quantity: $quantity,
                    unitCost: (float) $ingredient->product->unit_cost,
                    userId: $userId,
                    reason: $reason ?: 'Recipe '.$menu->name
                );
            }
        });
    }
}
