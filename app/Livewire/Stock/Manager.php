<?php

namespace App\Livewire\Stock;

use App\Exports\StockMovementsExport;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Enums\CurrencyCode;
use App\Models\Menu;
use App\Models\ProductCategory;
use App\Models\Product;
use App\Models\ServiceArea;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Services\Menu\MenuRecipeService;
use App\Services\Stock\StockService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;
use Livewire\Component;
use Livewire\WithPagination;

class Manager extends Component
{
    use WithPagination;

    public string $search = '';
    public string $categoryFilter = '';
    public string $productServiceFilter = '';
    public string $stockFilter = 'all';
    public string $movementSearch = '';
    public string $movementTypeFilter = 'all';
    public string $movementServiceFilter = '';
    public string $movementStartDate = '';
    public string $movementEndDate = '';
    public int $movementPerPage = 25;
    public string $movementSortField = 'created_at';
    public string $movementSortDirection = 'desc';
    public string $currency = 'USD';

    public ?int $product_category_id = null;
    public ?int $supplier_id = null;
    public ?int $product_service_area_id = null;
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
    public ?int $movement_service_area_id = null;
    public string $movement_type = 'in';
    public float $quantity = 1;
    public float $unit_cost = 0;
    public ?string $reason = null;

    public function updatedSearch(): void
    {
        // Intentionally empty. Livewire re-renders the listing from current filters.
    }

    public function mount(): void
    {
        $this->currency = CurrencyCode::default();
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['movementSearch', 'movementTypeFilter', 'movementServiceFilter', 'movementStartDate', 'movementEndDate', 'movementPerPage', 'movementSortField', 'movementSortDirection'], true)) {
            $this->resetPage('movementsPage');
        }
    }

    public function resetMovementFilters(): void
    {
        $this->movementSearch = '';
        $this->movementTypeFilter = 'all';
        $this->movementServiceFilter = '';
        $this->movementStartDate = '';
        $this->movementEndDate = '';
        $this->movementPerPage = 25;
        $this->movementSortField = 'created_at';
        $this->movementSortDirection = 'desc';

        $this->resetPage('movementsPage');
    }

    public function setMovementSort(string $field): void
    {
        $allowed = ['created_at', 'movement_type', 'quantity', 'unit_cost'];
        if (! in_array($field, $allowed, true)) {
            return;
        }

        if ($this->movementSortField === $field) {
            $this->movementSortDirection = $this->movementSortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->movementSortField = $field;
            $this->movementSortDirection = 'asc';
        }
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
            'product_service_area_id' => ['nullable', 'exists:service_areas,id'],
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
            'service_area_id' => $this->product_service_area_id,
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
            'product_service_area_id',
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
            'movement_service_area_id' => ['nullable', 'exists:service_areas,id'],
            'movement_type' => ['required', 'in:in,out'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $product = Product::query()->findOrFail($this->product_id);

        if ($this->movement_type === 'out') {
            $stockService->moveOut($product, $this->quantity, $this->unit_cost, Auth::id(), $this->reason, $this->movement_service_area_id);
        } else {
            $stockService->moveIn($product, $this->quantity, $this->unit_cost, Auth::id(), $this->reason, $this->movement_service_area_id);
        }

        $this->reset(['product_id', 'movement_service_area_id', 'reason']);
        $this->movement_type = 'in';
        $this->quantity = 1;
        $this->unit_cost = 0;
    }

    public function exportMovementsExcel()
    {
        $timestamp = now()->format('Ymd_His');

        return Excel::download(
            new StockMovementsExport(
                search: trim($this->movementSearch),
                movementFilter: $this->movementTypeFilter,
                serviceFilter: $this->movementServiceFilter,
                startDate: $this->movementStartDate,
                endDate: $this->movementEndDate,
                sortField: $this->movementSortField,
                sortDirection: $this->movementSortDirection,
            ),
            'stock_movements_'.$timestamp.'.xlsx'
        );
    }

    public function exportMovementsCsv()
    {
        $timestamp = now()->format('Ymd_His');

        return Excel::download(
            new StockMovementsExport(
                search: trim($this->movementSearch),
                movementFilter: $this->movementTypeFilter,
                serviceFilter: $this->movementServiceFilter,
                startDate: $this->movementStartDate,
                endDate: $this->movementEndDate,
                sortField: $this->movementSortField,
                sortDirection: $this->movementSortDirection,
            ),
            'stock_movements_'.$timestamp.'.csv',
            ExcelFormat::CSV
        );
    }

    public function exportMovementsPdf()
    {
        $movements = $this->movementsQuery()->get();

        $pdf = Pdf::loadView('exports.stock-movements-pdf', [
            'movements' => $movements,
            'search' => trim($this->movementSearch),
            'movementFilter' => $this->movementTypeFilter,
            'serviceFilterLabel' => $this->resolveMovementServiceFilterLabel(),
            'startDate' => $this->movementStartDate,
            'endDate' => $this->movementEndDate,
            'totals' => $this->movementTotals(),
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(
            static function () use ($pdf): void {
                echo $pdf->output();
            },
            'stock_movements_'.now()->format('Ymd_His').'.pdf'
        );
    }

    private function applyMovementFilters(Builder $query, bool $includeTypeFilter = true): Builder
    {
        $search = trim($this->movementSearch);

        return $query
            ->when($search !== '', function (Builder $movementQuery) use ($search): void {
                $movementQuery->where(function (Builder $searchQuery) use ($search): void {
                    $searchQuery->where('movement_type', 'like', '%'.$search.'%')
                        ->orWhere('reason', 'like', '%'.$search.'%')
                        ->orWhereHas('product', function (Builder $productQuery) use ($search): void {
                            $productQuery->where('name', 'like', '%'.$search.'%')
                                ->orWhere('sku', 'like', '%'.$search.'%');
                        })
                        ->orWhereHas('user', function (Builder $userQuery) use ($search): void {
                            $userQuery->where('name', 'like', '%'.$search.'%');
                        })
                        ->orWhereHas('serviceArea', function (Builder $serviceAreaQuery) use ($search): void {
                            $serviceAreaQuery->where('name', 'like', '%'.$search.'%')
                                ->orWhere('code', 'like', '%'.$search.'%');
                        });
                });
            })
            ->when($includeTypeFilter && in_array($this->movementTypeFilter, ['in', 'out'], true), function (Builder $movementQuery): void {
                $movementQuery->where('movement_type', $this->movementTypeFilter);
            })
            ->when($this->movementServiceFilter !== '', function (Builder $movementQuery): void {
                $movementQuery->where('service_area_id', (int) $this->movementServiceFilter);
            })
            ->when($this->movementStartDate !== '', function (Builder $movementQuery): void {
                $movementQuery->whereDate('created_at', '>=', $this->movementStartDate);
            })
            ->when($this->movementEndDate !== '', function (Builder $movementQuery): void {
                $movementQuery->whereDate('created_at', '<=', $this->movementEndDate);
            });
    }

    private function movementsFiltersQuery(): Builder
    {
        return $this->applyMovementFilters(
            StockMovement::query()->with(['product', 'user', 'serviceArea'])
        );
    }

    private function movementConsumptionByService(): array
    {
        $rows = $this->applyMovementFilters(StockMovement::query(), false)
            ->selectRaw('service_area_id')
            ->selectRaw("COALESCE(SUM(CASE WHEN movement_type = 'in' THEN quantity ELSE 0 END), 0) as in_quantity")
            ->selectRaw("COALESCE(SUM(CASE WHEN movement_type = 'out' THEN quantity ELSE 0 END), 0) as out_quantity")
            ->selectRaw("COALESCE(SUM(CASE WHEN movement_type = 'in' THEN quantity * unit_cost ELSE 0 END), 0) as in_amount")
            ->selectRaw("COALESCE(SUM(CASE WHEN movement_type = 'out' THEN quantity * unit_cost ELSE 0 END), 0) as out_amount")
            ->groupBy('service_area_id')
            ->orderBy('service_area_id')
            ->get();

        $serviceNames = ServiceArea::query()->pluck('name', 'id');

        return $rows->map(function ($row) use ($serviceNames): array {
            $inAmount = (float) $row->in_amount;
            $outAmount = (float) $row->out_amount;

            return [
                'service_name' => $row->service_area_id
                    ? ($serviceNames[(int) $row->service_area_id] ?? 'Service #'.$row->service_area_id)
                    : 'Non affecte',
                'in_quantity' => (float) $row->in_quantity,
                'out_quantity' => (float) $row->out_quantity,
                'in_amount' => $inAmount,
                'out_amount' => $outAmount,
                'net_amount' => $inAmount - $outAmount,
            ];
        })->all();
    }

    private function resolveMovementServiceFilterLabel(): ?string
    {
        if ($this->movementServiceFilter === '') {
            return null;
        }

        return ServiceArea::query()->find((int) $this->movementServiceFilter)?->name;
    }

    private function movementsQuery(): Builder
    {
        $sortField = in_array($this->movementSortField, ['created_at', 'movement_type', 'quantity', 'unit_cost'], true)
            ? $this->movementSortField
            : 'created_at';
        $sortDirection = $this->movementSortDirection === 'asc' ? 'asc' : 'desc';

        return $this->movementsFiltersQuery()
            ->orderBy($sortField, $sortDirection)
            ->orderBy('id', 'desc');
    }

    private function movementTotals(): array
    {
        $totals = $this->movementsFiltersQuery()
            ->selectRaw("COALESCE(SUM(CASE WHEN movement_type = 'in' THEN quantity ELSE 0 END), 0) as total_in_quantity")
            ->selectRaw("COALESCE(SUM(CASE WHEN movement_type = 'out' THEN quantity ELSE 0 END), 0) as total_out_quantity")
            ->selectRaw("COALESCE(SUM(CASE WHEN movement_type = 'in' THEN quantity * unit_cost ELSE 0 END), 0) as total_in_amount")
            ->selectRaw("COALESCE(SUM(CASE WHEN movement_type = 'out' THEN quantity * unit_cost ELSE 0 END), 0) as total_out_amount")
            ->first();

        $totalInAmount = (float) ($totals->total_in_amount ?? 0);
        $totalOutAmount = (float) ($totals->total_out_amount ?? 0);

        return [
            'in_quantity' => (float) ($totals->total_in_quantity ?? 0),
            'out_quantity' => (float) ($totals->total_out_quantity ?? 0),
            'in_amount' => $totalInAmount,
            'out_amount' => $totalOutAmount,
            'net_amount' => $totalInAmount - $totalOutAmount,
        ];
    }

    public function render()
    {
        $recipeService = app(MenuRecipeService::class);
        $currency = strtoupper((string) $this->currency);
        $currencySymbol = config('currency.symbols.' . $currency, $currency);
        $productsQuery = Product::query()
            ->with(['category', 'supplier', 'serviceArea'])
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
            ->when($this->productServiceFilter !== '', function ($query) {
                $query->where('service_area_id', (int) $this->productServiceFilter);
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
        $movementTotals = $this->movementTotals();
        $serviceConsumption = $this->movementConsumptionByService();
        $movements = $this->movementsQuery()->paginate($this->movementPerPage, ['*'], 'movementsPage');
        $dishPreview = Menu::query()
            ->with(['category', 'serviceArea', 'ingredients.product'])
            ->orderBy('name')
            ->get()
            ->map(function (Menu $menu) use ($recipeService): array {
                $availability = $recipeService->availability($menu);

                return [
                    'name' => $menu->name,
                    'category' => $menu->category?->name,
                    'service_area' => $menu->serviceArea?->name,
                    'is_available' => (bool) ($availability['is_available'] ?? false),
                    'max_servings' => $availability['max_servings'] ?? null,
                ];
            });
        $dishUnavailableCount = $dishPreview->where('is_available', false)->count();

        return view('livewire.stock.manager', [
            'products' => $products,
            'categories' => ProductCategory::query()->orderBy('sort_order')->orderBy('name')->get(),
            'suppliers' => Supplier::query()->orderBy('name')->get(),
            'serviceAreas' => ServiceArea::query()
                ->active()
                ->supporting('stock')
                ->ordered()
                ->get(),
            'alerts' => Product::query()->with('category')->whereColumn('stock_quantity', '<=', 'alert_threshold')->orderBy('stock_quantity')->limit(20)->get(),
            'movements' => $movements,
            'recentMovements' => StockMovement::query()->with(['product', 'user', 'serviceArea'])->latest()->limit(8)->get(),
            'movementTotals' => $movementTotals,
            'serviceConsumption' => $serviceConsumption,
            'referenceUnits' => config('inventory.units', []),
            'referencePurchaseUnits' => config('inventory.purchase_units', []),
            'referenceStorageAreas' => config('inventory.storage_areas', []),
            'referenceMovementReasons' => config('inventory.movement_reasons', []),
            'currency' => $currency,
            'currencySymbol' => $currencySymbol,
            'dishPreview' => $dishPreview->take(6)->all(),
            'dishStats' => [
                'total' => $dishPreview->count(),
                'available' => $dishPreview->count() - $dishUnavailableCount,
                'unavailable' => $dishUnavailableCount,
            ],
            'stats' => [
                'products' => Product::query()->count(),
                'categories' => ProductCategory::query()->where('is_active', true)->count(),
                'alerts' => Product::query()->whereColumn('stock_quantity', '<=', 'alert_threshold')->count(),
                'fresh' => Product::query()->where('is_perishable', true)->count(),
                'inventory_value' => (float) Product::query()->selectRaw('COALESCE(SUM(stock_quantity * unit_cost), 0) as value')->value('value'),
            ],
        ]);
    }
}
