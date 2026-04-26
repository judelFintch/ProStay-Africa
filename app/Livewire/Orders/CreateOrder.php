<?php

namespace App\Livewire\Orders;

use App\Enums\CustomerType;
use App\Enums\CurrencyCode;
use App\Enums\OrderStatus;
use App\Models\Customer;
use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ServiceArea;
use App\Models\Stay;
use App\Models\User;
use App\Services\Billing\InvoiceService;
use App\Services\Menu\MenuRecipeService;
use App\Services\Orders\OrderService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use RuntimeException;

class CreateOrder extends Component
{
    public ?int $append_order_id = null;
    public string $order_mode = 'external';
    public ?int $service_area_id = null;
    public ?int $customer_id = null;
    public ?string $external_customer_name = null;
    public ?int $stay_id = null;
    public ?int $room_id = null;
    public ?int $served_by = null;
    public string $currency = 'USD';
    public string $customer_type = 'walk_in_anonymous';
    public string $order_status = 'confirmed';
    public array $items = [];
    public string $catalog_tab = 'dishes';
    public string $catalog_search = '';
    public ?string $free_item_name = null;
    public float $free_item_price = 0;
    public ?string $notes = null;

    public function mount(): void
    {
        $this->currency = CurrencyCode::default();
        $this->syncOrderContext();

        if (Auth::id()) {
            $this->served_by = Auth::id();
        }
    }

    public function addItemRow(): void
    {
        $this->items[] = $this->emptyItemRow();
    }

    public function removeItemRow(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function clearOrder(): void
    {
        $this->reset([
            'append_order_id',
            'service_area_id',
            'customer_id',
            'external_customer_name',
            'stay_id',
            'room_id',
            'notes',
        ]);
        $this->order_mode = 'external';
        $this->order_status = OrderStatus::Confirmed->value;
        $this->syncOrderContext();
        $this->items = [];
        $this->catalog_tab = 'dishes';
        $this->catalog_search = '';
        $this->free_item_name = null;
        $this->free_item_price = 0;
        $this->currency = CurrencyCode::default();
        $this->resetErrorBag();

        if (! $this->served_by && Auth::id()) {
            $this->served_by = Auth::id();
        }
    }

    public function startAppend(int $orderId): void
    {
        $order = Order::query()->with(['customer', 'stay', 'room'])->find($orderId);
        if (! $order || $order->status === OrderStatus::Cancelled) {
            $this->addError('append_order_id', 'Cette commande ne peut pas etre modifiee.');

            return;
        }

        $this->append_order_id = $order->id;
        $this->service_area_id = $order->service_area_id;
        $this->customer_id = $order->customer_id;
        $this->stay_id = $order->stay_id;
        $this->room_id = $order->room_id;
        $this->served_by = $order->served_by;
        $this->currency = strtoupper((string) ($order->currency ?: CurrencyCode::default()));
        $this->order_status = $order->status->value;
        $this->order_mode = $order->customer_type === CustomerType::Lodged ? 'lodged' : 'external';
        $this->customer_type = $order->customer_type->value;
        $this->external_customer_name = $order->external_label;
        $this->items = [];
        $this->notes = null;
        $this->resetErrorBag();
    }

    public function stopAppend(): void
    {
        $this->clearOrder();
    }

    public function updatedOrderMode(): void
    {
        if ($this->append_order_id) {
            return;
        }

        if ($this->order_mode === 'lodged') {
            $this->external_customer_name = null;
            $this->customer_type = CustomerType::Lodged->value;

            return;
        }

        $this->customer_id = null;
        $this->external_customer_name = null;
        $this->stay_id = null;
        $this->room_id = null;
        $this->customer_type = CustomerType::WalkInAnonymous->value;
    }

    public function updatedCustomerId(): void
    {
        if ($this->append_order_id) {
            return;
        }

        if ($this->order_mode !== 'lodged') {
            $this->customer_id = null;
            $this->customer_type = CustomerType::WalkInAnonymous->value;

            return;
        }

        $this->customer_type = CustomerType::Lodged->value;
        $this->stay_id = null;
        $this->room_id = null;

        if (! $this->customer_id) {
            return;
        }

        $activeStay = Stay::query()
            ->with('room')
            ->where('customer_id', $this->customer_id)
            ->where('status', 'active')
            ->whereNotNull('room_id')
            ->latest('check_in_at')
            ->first();

        if (! $activeStay) {
            return;
        }

        $this->stay_id = $activeStay->id;
        $this->room_id = $activeStay->room_id;
    }

    public function updatedStayId(): void
    {
        if ($this->append_order_id) {
            return;
        }

        if (! $this->stay_id) {
            $this->room_id = null;

            return;
        }

        $stay = Stay::query()->with(['customer', 'room'])->find($this->stay_id);
        if (! $stay) {
            return;
        }

        $this->customer_id = $stay->customer_id;
        $this->room_id = $stay->room_id;
        $this->customer_type = CustomerType::Lodged->value;
    }

    public function updatedItems($value, string $key): void
    {
        $segments = explode('.', $key);
        $index = (int) ($segments[0] ?? 0);

        if (str_ends_with($key, '.item_type')) {
            if (($this->items[$index]['item_type'] ?? '') !== 'stocked_product') {
                $this->items[$index]['product_id'] = null;
                $this->items[$index]['product_query'] = '';
                $this->items[$index]['stock_available'] = null;
                $this->items[$index]['item_unit'] = '';
            }

            if (($this->items[$index]['item_type'] ?? '') !== 'menu_service') {
                $this->items[$index]['menu_id'] = null;
                $this->items[$index]['menu_available'] = null;
                $this->items[$index]['menu_max_servings'] = null;
            }

            return;
        }

        if (str_ends_with($key, '.product_id')) {
            $productId = $this->items[$index]['product_id'] ?? null;
            if (! $productId) {
                $this->items[$index]['stock_available'] = null;
                $this->items[$index]['item_unit'] = '';

                return;
            }

            $product = Product::query()->find($productId);
            if (! $product) {
                return;
            }

            $this->hydrateProductRow($index, $product);

            return;
        }

        if (str_ends_with($key, '.menu_id')) {
            $menuId = $this->items[$index]['menu_id'] ?? null;
            if (! $menuId) {
                $this->items[$index]['menu_available'] = null;
                $this->items[$index]['menu_max_servings'] = null;

                return;
            }

            $menu = Menu::query()->with('ingredients.product')->find($menuId);
            if ($menu) {
                $this->hydrateMenuRow($index, $menu);
            }

            return;
        }

        if (str_ends_with($key, '.quantity')) {
            $menuId = $this->items[$index]['menu_id'] ?? null;
            if (($this->items[$index]['item_type'] ?? '') === 'menu_service' && $menuId) {
                $menu = Menu::query()->with('ingredients.product')->find($menuId);
                if ($menu) {
                    $this->hydrateMenuRow($index, $menu);
                }
            }

            return;
        }

        if (! str_ends_with($key, '.product_query')) {
            return;
        }

        $search = strtolower(trim((string) ($this->items[$index]['product_query'] ?? '')));
        if ($search === '') {
            $this->items[$index]['product_id'] = null;
            $this->items[$index]['stock_available'] = null;
            $this->items[$index]['item_unit'] = '';

            return;
        }

        $product = Product::query()
            ->where('is_active', true)
            ->where(function ($query) use ($search) {
                $query->whereRaw('LOWER(name) = ?', [$search])
                    ->orWhere('name', 'like', '%' . $search . '%')
                    ->orWhere('sku', 'like', '%' . $search . '%');
            })
            ->orderByRaw('LOWER(name) = ? DESC', [$search])
            ->orderBy('name')
            ->first();

        if ($product) {
            $this->items[$index]['product_id'] = $product->id;
            $this->hydrateProductRow($index, $product);
        }
    }

    public function addSuggestedProduct(int $productId): void
    {
        $this->addProductToCart($productId);
    }

    public function addProductToCart(int $productId): void
    {
        $product = Product::query()->where('is_active', true)->find($productId);
        if (! $product) {
            return;
        }

        $index = $this->findCartIndex('stocked_product', $product->id);
        if ($index !== null) {
            $this->items[$index]['quantity'] = (float) ($this->items[$index]['quantity'] ?? 0) + 1;
            $this->hydrateProductRow($index, $product);

            return;
        }

        $index = $this->pushCartItem('stocked_product');
        $this->items[$index]['product_id'] = $product->id;
        $this->hydrateProductRow($index, $product);
    }

    public function addMenuToCart(int $menuId): void
    {
        $menu = Menu::query()->with('ingredients.product')->where('is_available', true)->find($menuId);
        if (! $menu) {
            return;
        }

        $index = $this->findCartIndex('menu_service', $menu->id);
        if ($index !== null) {
            $this->items[$index]['quantity'] = (float) ($this->items[$index]['quantity'] ?? 0) + 1;
            $this->hydrateMenuRow($index, $menu);

            return;
        }

        $index = $this->pushCartItem('menu_service');
        $this->items[$index]['menu_id'] = $menu->id;
        $this->hydrateMenuRow($index, $menu);
    }

    public function addFreeItemToCart(): void
    {
        $name = trim((string) $this->free_item_name);
        if ($name === '') {
            $this->addError('free_item_name', 'Renseigne le nom de l article.');

            return;
        }

        $index = $this->pushCartItem('free_item');
        $this->items[$index]['item_name'] = $name;
        $this->items[$index]['unit_price'] = max(0, (float) $this->free_item_price);
        $this->free_item_name = null;
        $this->free_item_price = 0;
        $this->resetErrorBag('free_item_name');
    }

    public function incrementItem(int $index): void
    {
        if (! isset($this->items[$index])) {
            return;
        }

        $this->items[$index]['quantity'] = (float) ($this->items[$index]['quantity'] ?? 0) + 1;
        $this->refreshCartItem($index);
    }

    public function decrementItem(int $index): void
    {
        if (! isset($this->items[$index])) {
            return;
        }

        $quantity = (float) ($this->items[$index]['quantity'] ?? 1);
        if ($quantity <= 1) {
            $this->removeItemRow($index);

            return;
        }

        $this->items[$index]['quantity'] = $quantity - 1;
        $this->refreshCartItem($index);
    }

    public function save(OrderService $orderService): void
    {
        $order = $this->persistOrder($orderService);
        if (! $order) {
            return;
        }

        if ($this->append_order_id) {
            $this->dispatch('order-created', reference: $order->reference);
            $this->clearOrder();

            return;
        }

        $this->dispatch('order-created', reference: $order->reference);

        if ($order->customer_type === CustomerType::Lodged && $order->stay_id) {
            $invoice = app(InvoiceService::class)->openFolderForOrder($order, [
                'customer_id' => $order->customer_id,
                'stay_id' => $order->stay_id,
                'room_id' => $order->room_id,
                'currency' => $order->currency,
                'issued_by' => Auth::id(),
            ]);

            $this->dispatch('invoice-created', reference: $invoice->reference);
        }

        $this->clearOrder();
    }

    public function saveAndInvoice(OrderService $orderService, InvoiceService $invoiceService): void
    {
        $order = $this->persistOrder($orderService);
        if (! $order) {
            return;
        }

        if ($this->append_order_id) {
            $this->dispatch('order-created', reference: $order->reference);
            $this->clearOrder();
            $this->redirectRoute('billing.invoices', navigate: true);

            return;
        }

        $invoice = $order->customer_type === CustomerType::Lodged && $order->stay_id
            ? $invoiceService->openFolderForOrder($order, [
                'customer_id' => $order->customer_id,
                'stay_id' => $order->stay_id,
                'room_id' => $order->room_id,
                'currency' => $order->currency,
                'issued_by' => Auth::id(),
            ])
            : $invoiceService->createFromOrders([$order->fresh('items')], [
                'customer_id' => $order->customer_id,
                'stay_id' => $order->stay_id,
                'room_id' => $order->room_id,
                'currency' => $order->currency,
                'issued_by' => Auth::id(),
            ]);

        $this->dispatch('order-created', reference: $order->reference);
        $this->dispatch('invoice-created', reference: $invoice->reference);
        $this->clearOrder();
        $this->redirectRoute('billing.invoices', navigate: true);
    }

    public function render()
    {
        $serviceAreas = ServiceArea::query()->where('is_active', true)->orderBy('name')->get();
        $customers = Customer::query()->orderBy('full_name')->limit(100)->get();
        $activeServerScope = User::query()
            ->where('is_server', true)
            ->where('server_active', true);

        $servers = $activeServerScope->exists()
            ? $activeServerScope->orderBy('name')->get()
            : User::query()->orderBy('name')->limit(100)->get();
        $products = Product::query()->with('category')->where('is_active', true)->orderBy('name')->limit(300)->get();
        $menus = Menu::query()
            ->with(['category', 'ingredients.product'])
            ->where('is_available', true)
            ->orderBy('name')
            ->get();
        $catalogSearch = strtolower(trim($this->catalog_search));
        $catalogProducts = $products
            ->filter(fn (Product $product): bool => $catalogSearch === ''
                || str_contains(strtolower($product->name), $catalogSearch)
                || str_contains(strtolower((string) $product->sku), $catalogSearch)
                || str_contains(strtolower((string) ($product->category?->name ?? '')), $catalogSearch))
            ->take(24)
            ->values();
        $catalogMenus = $menus
            ->filter(fn (Menu $menu): bool => $catalogSearch === ''
                || str_contains(strtolower($menu->name), $catalogSearch)
                || str_contains(strtolower((string) ($menu->category?->name ?? '')), $catalogSearch))
            ->take(24)
            ->values()
            ->map(function (Menu $menu): Menu {
                $availability = app(MenuRecipeService::class)->availability($menu, 1);
                $menu->setAttribute('catalog_available', (bool) $availability['is_available']);
                $menu->setAttribute('catalog_max_servings', $availability['max_servings']);

                return $menu;
            });
        $activeStays = Stay::query()
            ->with(['customer', 'room'])
            ->where('status', 'active')
            ->latest('check_in_at')
            ->limit(50)
            ->get();

        $lodgedCustomers = Customer::query()
            ->whereHas('stays', function ($query) {
                $query->where('status', 'active')
                    ->whereNotNull('room_id')
                    ->whereNotNull('reservation_id');
            })
            ->orderBy('full_name')
            ->get();

        $customerActiveStays = $this->customer_id
            ? Stay::query()
                ->with(['customer', 'room'])
                ->where('customer_id', $this->customer_id)
                ->where('status', 'active')
                ->latest('check_in_at')
                ->get()
            : collect();

        $rowSummaries = collect($this->items)->map(function (array $item) {
            $qty = (float) ($item['quantity'] ?? 0);
            $price = (float) ($item['unit_price'] ?? 0);
            $stockAvailable = isset($item['stock_available']) ? (float) $item['stock_available'] : null;

            return [
                'quantity' => $qty,
                'subtotal' => $qty * $price,
                'is_stocked' => ($item['item_type'] ?? 'stocked_product') === 'stocked_product',
                'is_stock_issue' => ($stockAvailable !== null && $qty > 0 && $qty > $stockAvailable)
                    || (($item['item_type'] ?? '') === 'menu_service' && ($item['menu_available'] ?? true) === false),
                'is_low_stock' => $stockAvailable !== null && $stockAvailable > 0 && $stockAvailable <= 5,
                'is_out_of_stock' => $stockAvailable !== null && $stockAvailable <= 0,
            ];
        });

        $recentOrders = Order::query()
            ->with(['customer', 'room', 'stay', 'server'])
            ->withCount('items')
            ->latest()
            ->limit(30)
            ->get();

        $recentOrdersByStatus = collect(OrderStatus::cases())
            ->mapWithKeys(function (OrderStatus $status) use ($recentOrders) {
                return [$status->value => $recentOrders->where('status', $status)];
            });

        $appendTargetOrder = $this->append_order_id
            ? Order::query()
                ->with(['customer', 'room', 'stay'])
                ->withCount('items')
                ->find($this->append_order_id)
            : null;

        $pendingToInvoiceCount = Order::query()
            ->whereIn('status', ['served', 'closed'])
            ->whereHas('items', function ($query): void {
                $query->whereDoesntHave('invoiceItems');
            })
            ->count();

        return view('livewire.orders.create-order', [
            'serviceAreas' => $serviceAreas,
            'customers' => $customers,
            'servers' => $servers,
            'lodgedCustomers' => $lodgedCustomers,
            'products' => $products,
            'menus' => $menus,
            'catalogProducts' => $catalogProducts,
            'catalogMenus' => $catalogMenus,
            'activeStays' => $activeStays,
            'customerActiveStays' => $customerActiveStays,
            'recentOrders' => $recentOrders->take(10),
            'recentOrdersByStatus' => $recentOrdersByStatus,
            'appendTargetOrder' => $appendTargetOrder,
            'customerTypes' => array_column(CustomerType::cases(), 'value'),
            'rowSummaries' => $rowSummaries,
            'totalQuantity' => (float) $rowSummaries->sum('quantity'),
            'estimatedTotal' => (float) $rowSummaries->sum('subtotal'),
            'stockIssueCount' => (int) $rowSummaries->where('is_stock_issue', true)->count(),
            'suggestedProducts' => $this->suggestedProducts($products),
            'pendingToInvoiceCount' => $pendingToInvoiceCount,
            'supportedCurrencies' => CurrencyCode::supported(),
        ]);
    }

    private function persistOrder(OrderService $orderService): ?Order
    {
        $this->customer_type = $this->order_mode === 'lodged'
            ? CustomerType::Lodged->value
            : CustomerType::WalkInAnonymous->value;

        if ($this->order_mode !== 'lodged') {
            $this->stay_id = null;
            $this->room_id = null;
        }

        $this->validate([
            'order_mode' => ['required', 'in:lodged,external'],
            'currency' => ['required', 'in:' . implode(',', CurrencyCode::supported())],
            'service_area_id' => ['nullable', 'exists:service_areas,id'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'external_customer_name' => ['nullable', 'required_if:order_mode,external', 'string', 'max:255'],
            'stay_id' => ['nullable', 'exists:stays,id'],
            'room_id' => ['nullable', 'exists:rooms,id'],
            'served_by' => ['required', 'exists:users,id'],
            'order_status' => ['required', 'in:draft,confirmed,served,closed'],
            'customer_type' => ['required', 'in:' . implode(',', array_column(CustomerType::cases(), 'value'))],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_type' => ['required', 'in:stocked_product,free_item,menu_service'],
            'items.*.product_id' => ['nullable', 'exists:products,id'],
            'items.*.menu_id' => ['nullable', 'exists:menus,id'],
            'items.*.item_name' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        if ($this->order_mode === 'lodged' && ! $this->customer_id) {
            $this->addError('customer_id', 'Selectionne un client loge avant validation.');

            return null;
        }

        if ($this->order_mode === 'lodged') {
            if (! $this->stay_id) {
                $this->addError('stay_id', 'Selectionne un sejour actif avant validation.');

                return null;
            }

            $validatedStay = Stay::query()
                ->where('id', $this->stay_id)
                ->where('customer_id', $this->customer_id)
                ->where('status', 'active')
                ->whereNotNull('room_id')
                ->first();

            if (! $validatedStay) {
                $this->addError('stay_id', 'Le sejour selectionne ne correspond pas au client actif.');

                return null;
            }

            $this->room_id = $validatedStay->room_id;
        }

        if ($this->order_mode !== 'lodged' && ($this->stay_id || $this->room_id)) {
            $this->addError('room_id', 'Une commande client externe ne doit pas etre liee a une chambre.');

            return null;
        }

        if (! $this->ensureStockIsAvailable()) {
            return null;
        }

        $finalNotes = $this->notes;
        if ($this->order_mode === 'external' && $this->external_customer_name) {
            $externalLabel = 'Client externe: ' . trim((string) $this->external_customer_name);
            $finalNotes = $finalNotes ? ($externalLabel . PHP_EOL . $finalNotes) : $externalLabel;
        }

        try {
            if ($this->append_order_id) {
                $targetOrder = Order::query()->find($this->append_order_id);
                if (! $targetOrder || $targetOrder->status === OrderStatus::Cancelled) {
                    $this->addError('append_order_id', 'La commande cible n est plus modifiable.');

                    return null;
                }

                if (
                    (int) ($targetOrder->service_area_id ?? 0) !== (int) ($this->service_area_id ?? 0)
                    || (int) ($targetOrder->customer_id ?? 0) !== (int) ($this->customer_id ?? 0)
                    || (int) ($targetOrder->stay_id ?? 0) !== (int) ($this->stay_id ?? 0)
                    || (int) ($targetOrder->room_id ?? 0) !== (int) ($this->room_id ?? 0)
                ) {
                    $this->addError('append_order_id', 'Le contexte de la commande a change. Recharge et reessaye.');

                    return null;
                }

                if (strtoupper((string) $targetOrder->currency) !== strtoupper($this->currency)) {
                    $this->addError('currency', 'La devise doit rester identique a la commande d origine en mode ajout.');

                    return null;
                }

                return $orderService->appendItems(
                    order: $targetOrder,
                    items: $this->normalizedItems(),
                    userId: Auth::id(),
                );
            }

            return $orderService->create([
                'service_area_id' => $this->service_area_id,
                'customer_id' => $this->customer_id,
                'customer_type' => $this->customer_type,
                'external_label' => $this->order_mode === 'external' ? trim((string) $this->external_customer_name) : null,
                'stay_id' => $this->stay_id,
                'room_id' => $this->room_id,
                'served_by' => $this->served_by,
                'status' => $this->order_status,
                'currency' => strtoupper($this->currency),
                'items' => $this->normalizedItems(),
                'notes' => $finalNotes,
                'created_by' => Auth::id(),
            ]);
        } catch (RuntimeException $exception) {
            $this->addError('items', $exception->getMessage());

            return null;
        }
    }

    private function normalizedItems(): array
    {
        return collect($this->items)
            ->map(function (array $item) {
                $itemType = $item['item_type'] ?? 'stocked_product';

                return [
                    'product_id' => $itemType === 'stocked_product' ? ($item['product_id'] ?: null) : null,
                    'menu_id' => $itemType === 'menu_service' ? ($item['menu_id'] ?: null) : null,
                    'item_name' => trim((string) ($item['item_name'] ?? 'Article')),
                    'quantity' => (float) ($item['quantity'] ?? 1),
                    'unit_price' => (float) ($item['unit_price'] ?? 0),
                ];
            })
            ->values()
            ->all();
    }

    private function ensureStockIsAvailable(): bool
    {
        $isValid = true;

        foreach ($this->items as $index => $item) {
            if (($item['item_type'] ?? 'stocked_product') !== 'stocked_product') {
                if (($item['item_type'] ?? '') === 'menu_service') {
                    $menuId = $item['menu_id'] ?? null;
                    if (! $menuId) {
                        $this->addError("items.$index.menu_id", 'Choisis un plat du catalogue.');
                        $isValid = false;

                        continue;
                    }

                    $menu = Menu::query()->with('ingredients.product')->find($menuId);
                    if (! $menu) {
                        $this->addError("items.$index.menu_id", 'Plat introuvable.');
                        $isValid = false;

                        continue;
                    }

                    $quantity = (float) ($item['quantity'] ?? 1);
                    $availability = app(MenuRecipeService::class)->availability($menu, $quantity);

                    if (! $availability['is_available']) {
                        $missing = $availability['missing']->map(fn (array $line): string => $line['product']->name)->join(', ');
                        $this->addError("items.$index.menu_id", 'Plat indisponible: stock insuffisant pour '.$missing.'.');
                        $isValid = false;
                    }
                }

                continue;
            }

            $productId = $item['product_id'] ?? null;
            if (! $productId) {
                $this->addError("items.$index.product_id", 'Choisis un produit stocke ou passe la ligne en article libre.');
                $isValid = false;

                continue;
            }

            $product = Product::query()->find($productId);
            if (! $product) {
                $this->addError("items.$index.product_id", 'Produit introuvable.');
                $isValid = false;

                continue;
            }

            $requested = (float) ($item['quantity'] ?? 0);
            $available = (float) $product->stock_quantity;

            if ($requested > $available) {
                $this->addError("items.$index.quantity", "Stock insuffisant: {$available} {$product->unit} dispo pour {$product->name}.");
                $isValid = false;
            }
        }

        return $isValid;
    }

    private function hydrateProductRow(int $index, Product $product): void
    {
        $this->items[$index]['item_name'] = $product->name;
        $this->items[$index]['unit_price'] = (float) $product->selling_price;
        $this->items[$index]['item_unit'] = (string) ($product->unit ?? 'u');
        $this->items[$index]['stock_available'] = (float) $product->stock_quantity;
        $this->items[$index]['product_query'] = $product->name;
    }

    private function hydrateMenuRow(int $index, Menu $menu): void
    {
        $availability = app(MenuRecipeService::class)->availability($menu, (float) ($this->items[$index]['quantity'] ?? 1));

        $this->items[$index]['item_name'] = $menu->name;
        $this->items[$index]['unit_price'] = (float) $menu->price;
        $this->items[$index]['menu_available'] = (bool) $availability['is_available'];
        $this->items[$index]['menu_max_servings'] = $availability['max_servings'];
    }

    private function pushCartItem(string $type): int
    {
        $this->items[] = array_merge($this->emptyItemRow(), ['item_type' => $type]);

        return count($this->items) - 1;
    }

    private function findCartIndex(string $type, int $id): ?int
    {
        foreach ($this->items as $index => $item) {
            if (($item['item_type'] ?? '') !== $type) {
                continue;
            }

            $itemId = $type === 'menu_service'
                ? (int) ($item['menu_id'] ?? 0)
                : (int) ($item['product_id'] ?? 0);

            if ($itemId === $id) {
                return $index;
            }
        }

        return null;
    }

    private function refreshCartItem(int $index): void
    {
        if (! isset($this->items[$index])) {
            return;
        }

        if (($this->items[$index]['item_type'] ?? '') === 'menu_service' && ($this->items[$index]['menu_id'] ?? null)) {
            $menu = Menu::query()->with('ingredients.product')->find($this->items[$index]['menu_id']);
            if ($menu) {
                $this->hydrateMenuRow($index, $menu);
            }
        }

        if (($this->items[$index]['item_type'] ?? '') === 'stocked_product' && ($this->items[$index]['product_id'] ?? null)) {
            $product = Product::query()->find($this->items[$index]['product_id']);
            if ($product) {
                $this->hydrateProductRow($index, $product);
            }
        }
    }

    private function suggestedProducts(Collection $fallbackProducts): Collection
    {
        $topUsedProductIds = OrderItem::query()
            ->select('product_id')
            ->selectRaw('COUNT(*) as usage_count')
            ->whereNotNull('product_id')
            ->whereHas('order', function ($query) {
                $query->where('created_at', '>=', now()->subDays(30));

                if ($this->service_area_id) {
                    $query->where('service_area_id', $this->service_area_id);
                }

                if ($this->order_mode === 'lodged') {
                    $query->where('customer_type', CustomerType::Lodged->value);
                } elseif ($this->order_mode === 'external') {
                    $query->whereIn('customer_type', [
                        CustomerType::WalkInAnonymous->value,
                        CustomerType::WalkInIdentified->value,
                    ]);
                }
            })
            ->groupBy('product_id')
            ->orderByDesc('usage_count')
            ->limit(6)
            ->pluck('product_id');

        if ($topUsedProductIds->isNotEmpty()) {
            return Product::query()->whereIn('id', $topUsedProductIds)->orderBy('name')->get();
        }

        return $fallbackProducts->sortByDesc('selling_price')->take(6)->values();
    }

    private function emptyItemRow(): array
    {
        return [
            'item_type' => 'stocked_product',
            'product_id' => null,
            'menu_id' => null,
            'product_query' => '',
            'item_name' => '',
            'item_unit' => '',
            'stock_available' => null,
            'menu_available' => null,
            'menu_max_servings' => null,
            'quantity' => 1,
            'unit_price' => 0,
        ];
    }

    private function syncOrderContext(): void
    {
        $this->customer_type = CustomerType::WalkInAnonymous->value;
    }
}
