<?php

namespace App\Services\Stock;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class StockService
{
    public function moveIn(Product $product, float $quantity, float $unitCost = 0, ?int $userId = null, ?string $reason = null, ?int $serviceAreaId = null): StockMovement
    {
        return $this->recordMovement($product, 'in', abs($quantity), $unitCost, $userId, $reason, $serviceAreaId);
    }

    public function moveOut(Product $product, float $quantity, float $unitCost = 0, ?int $userId = null, ?string $reason = null, ?int $serviceAreaId = null): StockMovement
    {
        return $this->recordMovement($product, 'out', abs($quantity), $unitCost, $userId, $reason, $serviceAreaId);
    }

    private function recordMovement(Product $product, string $movementType, float $quantity, float $unitCost, ?int $userId, ?string $reason, ?int $serviceAreaId): StockMovement
    {
        return DB::transaction(function () use ($product, $movementType, $quantity, $unitCost, $userId, $reason, $serviceAreaId): StockMovement {
            $freshProduct = Product::query()->lockForUpdate()->findOrFail($product->id);

            $current = (float) $freshProduct->stock_quantity;
            $next = $movementType === 'out'
                ? max(0, $current - $quantity)
                : $current + $quantity;

            $freshProduct->update(['stock_quantity' => $next]);

            return StockMovement::query()->create([
                'product_id' => $freshProduct->id,
                'service_area_id' => $serviceAreaId,
                'user_id' => $userId,
                'movement_type' => $movementType,
                'quantity' => $quantity,
                'unit_cost' => $unitCost,
                'reason' => $reason,
            ]);
        });
    }
}
