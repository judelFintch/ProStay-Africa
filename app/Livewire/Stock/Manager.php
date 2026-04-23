<?php

namespace App\Livewire\Stock;

use App\Models\ProductCategory;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Services\Stock\StockService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;

class Manager extends Component
{
    public string $search = '';
    public string $categoryFilter = '';
    public string $stockFilter = 'all';

    public ?int $product_category_id = null;
    public ?int $supplier_id = null;
    public string $product_name = '';
    public ?string $sku = null;
    public string $unit = 'unit';
    public ?string $purchase_unit = null;
    public ?string $storage_area = null;
    public bool $is_perishable = false;
    public ?string $expires_at = null;
    public float $product_unit_cost = 0;
    public float $selling_price = 0;
    public float $opening_stock = 0;
    public float $alert_threshold_value = 0;
    public bool $is_active = true;

    public string $category_name = '';
    public ?string $category_code = null;
    public ?string $category_description = null;
    public string $category_color = 'emerald';
    public bool $category_is_perishable = false;
    public bool $category_is_active = true;

    public ?int $product_id = null;
    public string $movement_type = 'in';
    public float $quantity = 1;
    public float $unit_cost = 0;
    public ?string $reason = null;

    public function updatedSearch(): void
    {
        // Intentionally empty. Livewire re-renders the listing from current filters.
    }

    public function saveCategory(): void
    {
        $this->validate([
            'category_name' => ['required', 'string', 'max:255'],
            'category_code' => ['nullable', 'string', 'max:100', 'unique:product_categories,code'],
            'category_description' => ['nullable', 'string', 'max:1000'],
            'category_color' => ['required', 'string', 'max:30'],
            'category_is_perishable' => ['boolean'],
            'category_is_active' => ['boolean'],
        ]);

        ProductCategory::query()->create([
            'name' => $this->category_name,
            'code' => $this->category_code ?: Str::slug($this->category_name),
            'description' => $this->category_description,
            'color' => $this->category_color,
            'is_perishable' => $this->category_is_perishable,
            'sort_order' => ((int) ProductCategory::query()->max('sort_order')) + 1,
            'is_active' => $this->category_is_active,
        ]);

        $this->reset([
            'category_name',
            'category_code',
            'category_description',
        ]);
        $this->category_color = 'emerald';
        $this->category_is_perishable = false;
        $this->category_is_active = true;
    }

    public function saveProduct(): void
    {
        $this->validate([
            'product_category_id' => ['required', 'exists:product_categories,id'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'product_name' => ['required', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:100', 'unique:products,sku'],
            'unit' => ['required', 'string', 'max:50'],
            'purchase_unit' => ['nullable', 'string', 'max:50'],
            'storage_area' => ['nullable', 'string', 'max:255'],
            'is_perishable' => ['boolean'],
            'expires_at' => ['nullable', 'date'],
            'product_unit_cost' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['nullable', 'numeric', 'min:0'],
            'opening_stock' => ['nullable', 'numeric', 'min:0'],
            'alert_threshold_value' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        $product = Product::query()->create([
            'product_category_id' => $this->product_category_id,
            'supplier_id' => $this->supplier_id,
            'name' => $this->product_name,
            'sku' => $this->sku ?: 'PRD-' . Str::upper(Str::random(8)),
            'unit' => $this->unit,
            'purchase_unit' => $this->purchase_unit ?: $this->unit,
            'storage_area' => $this->storage_area,
            'is_perishable' => $this->is_perishable,
            'expires_at' => $this->expires_at ?: null,
            'unit_cost' => $this->product_unit_cost,
            'selling_price' => $this->selling_price,
            'stock_quantity' => 0,
            'alert_threshold' => $this->alert_threshold_value,
            'is_active' => $this->is_active,
        ]);

        if ($this->opening_stock > 0) {
            app(StockService::class)->moveIn(
                product: $product,
                quantity: $this->opening_stock,
                unitCost: $this->product_unit_cost,
                userId: Auth::id(),
                reason: 'Initial stock setup'
            );
        }

        $this->reset([
            'product_category_id',
            'supplier_id',
            'product_name',
            'sku',
            'purchase_unit',
            'storage_area',
            'expires_at',
        ]);
        $this->unit = 'unit';
        $this->is_perishable = false;
        $this->product_unit_cost = 0;
        $this->selling_price = 0;
        $this->opening_stock = 0;
        $this->alert_threshold_value = 0;
        $this->is_active = true;
    }

    public function saveMovement(StockService $stockService): void
    {
        $this->validate([
            'product_id' => ['required', 'exists:products,id'],
            'movement_type' => ['required', 'in:in,out'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $product = Product::query()->findOrFail($this->product_id);

        if ($this->movement_type === 'out') {
            $stockService->moveOut($product, $this->quantity, $this->unit_cost, Auth::id(), $this->reason);
        } else {
            $stockService->moveIn($product, $this->quantity, $this->unit_cost, Auth::id(), $this->reason);
        }

        $this->reset(['product_id', 'reason']);
        $this->movement_type = 'in';
        $this->quantity = 1;
        $this->unit_cost = 0;
    }

    public function render()
    {
        $productsQuery = Product::query()
            ->with(['category', 'supplier'])
            ->when($this->search, function ($query) {
                $query->where(function ($productQuery) {
                    $productQuery->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('sku', 'like', '%' . $this->search . '%')
                        ->orWhere('storage_area', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->categoryFilter, function ($query) {
                $query->whereHas('category', function ($categoryQuery) {
                    $categoryQuery->where('code', $this->categoryFilter);
                });
            })
            ->when($this->stockFilter === 'alerts', function ($query) {
                $query->whereColumn('stock_quantity', '<=', 'alert_threshold');
            })
            ->when($this->stockFilter === 'perishable', function ($query) {
                $query->where('is_perishable', true);
            })
            ->when($this->stockFilter === 'fresh', function ($query) {
                $query->whereHas('category', function ($categoryQuery) {
                    $categoryQuery->where('code', 'fresh-food');
                });
            })
            ->orderBy('name');

        $products = $productsQuery->get();

        return view('livewire.stock.manager', [
            'products' => $products,
            'categories' => ProductCategory::query()->orderBy('sort_order')->orderBy('name')->get(),
            'suppliers' => Supplier::query()->orderBy('name')->get(),
            'alerts' => Product::query()->with('category')->whereColumn('stock_quantity', '<=', 'alert_threshold')->orderBy('stock_quantity')->limit(20)->get(),
            'movements' => StockMovement::query()->with(['product', 'user'])->latest()->limit(25)->get(),
            'referenceUnits' => config('inventory.units', []),
            'referencePurchaseUnits' => config('inventory.purchase_units', []),
            'referenceStorageAreas' => config('inventory.storage_areas', []),
            'referenceMovementReasons' => config('inventory.movement_reasons', []),
            'stats' => [
                'products' => Product::query()->count(),
                'categories' => ProductCategory::query()->where('is_active', true)->count(),
                'alerts' => Product::query()->whereColumn('stock_quantity', '<=', 'alert_threshold')->count(),
                'fresh' => Product::query()->where('is_perishable', true)->count(),
            ],
        ]);
    }
}
