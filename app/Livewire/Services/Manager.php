<?php

namespace App\Livewire\Services;

use App\Models\ServiceArea;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Manager extends Component
{
    public string $search = '';
    public string $domainFilter = 'all';
    public string $statusFilter = 'all';

    public ?int $editing_service_id = null;
    public string $name = '';
    public string $code = '';
    public string $domain = 'restaurant';
    public ?string $description = null;
    public ?string $manager_name = null;
    public ?string $manager_phone = null;
    public ?string $opens_at = null;
    public ?string $closes_at = null;
    public float $daily_target_amount = 0;
    public float $monthly_budget = 0;
    public int $sort_order = 0;
    public bool $is_active = true;
    public bool $supports_orders = true;
    public bool $supports_menu = true;
    public bool $supports_pos = true;
    public bool $supports_stock = true;
    public bool $supports_tables = true;

    public ?string $feedbackMessage = null;
    public string $feedbackTone = 'success';

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'nullable',
                'string',
                'max:100',
                'regex:/^[a-z0-9\-]+$/',
                Rule::unique('service_areas', 'code')->ignore($this->editing_service_id),
            ],
            'domain' => ['required', Rule::in(ServiceArea::DOMAINS)],
            'description' => ['nullable', 'string', 'max:1000'],
            'manager_name' => ['nullable', 'string', 'max:255'],
            'manager_phone' => ['nullable', 'string', 'max:50'],
            'opens_at' => ['nullable', 'date_format:H:i'],
            'closes_at' => ['nullable', 'date_format:H:i'],
            'daily_target_amount' => ['required', 'numeric', 'min:0'],
            'monthly_budget' => ['required', 'numeric', 'min:0'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['boolean'],
            'supports_orders' => ['boolean'],
            'supports_menu' => ['boolean'],
            'supports_pos' => ['boolean'],
            'supports_stock' => ['boolean'],
            'supports_tables' => ['boolean'],
        ];
    }

    public function mount(): void
    {
        if (session('workspace_context') === 'hotel') {
            $this->applyDomainPreset('hotel');
        }
    }

    public function updatedDomain(string $domain): void
    {
        if ($this->editing_service_id === null) {
            $this->applyDomainPreset($domain);
        }
    }

    public function resetForm(): void
    {
        $this->reset([
            'editing_service_id',
            'name',
            'code',
            'description',
            'manager_name',
            'manager_phone',
            'opens_at',
            'closes_at',
            'feedbackMessage',
        ]);

        $this->feedbackTone = 'success';
        $this->daily_target_amount = 0;
        $this->monthly_budget = 0;
        $this->sort_order = 0;
        $this->is_active = true;
        $this->applyDomainPreset(session('workspace_context') === 'hotel' ? 'hotel' : 'restaurant');
        $this->resetErrorBag();
    }

    public function edit(int $serviceAreaId): void
    {
        $serviceArea = ServiceArea::query()->findOrFail($serviceAreaId);

        $this->editing_service_id = $serviceArea->id;
        $this->name = $serviceArea->name;
        $this->code = $serviceArea->code;
        $this->domain = $serviceArea->domain;
        $this->description = $serviceArea->description;
        $this->manager_name = $serviceArea->manager_name;
        $this->manager_phone = $serviceArea->manager_phone;
        $this->opens_at = $serviceArea->opens_at ? substr((string) $serviceArea->opens_at, 0, 5) : null;
        $this->closes_at = $serviceArea->closes_at ? substr((string) $serviceArea->closes_at, 0, 5) : null;
        $this->daily_target_amount = (float) $serviceArea->daily_target_amount;
        $this->monthly_budget = (float) $serviceArea->monthly_budget;
        $this->sort_order = (int) $serviceArea->sort_order;
        $this->is_active = (bool) $serviceArea->is_active;
        $this->supports_orders = (bool) $serviceArea->supports_orders;
        $this->supports_menu = (bool) $serviceArea->supports_menu;
        $this->supports_pos = (bool) $serviceArea->supports_pos;
        $this->supports_stock = (bool) $serviceArea->supports_stock;
        $this->supports_tables = (bool) $serviceArea->supports_tables;
        $this->feedbackMessage = null;
        $this->resetErrorBag();
    }

    public function save(): void
    {
        $validated = $this->validate();
        $validated['code'] = Str::slug($validated['code'] ?: $validated['name']);
        $validated['opens_at'] = $validated['opens_at'] ? $validated['opens_at'].':00' : null;
        $validated['closes_at'] = $validated['closes_at'] ? $validated['closes_at'].':00' : null;

        $serviceArea = ServiceArea::query()->updateOrCreate(
            ['id' => $this->editing_service_id],
            $validated,
        );

        $this->resetForm();
        $this->edit($serviceArea->id);
        $this->feedbackTone = 'success';
        $this->feedbackMessage = $serviceArea->wasRecentlyCreated
            ? 'Le service a ete cree.'
            : 'Le service a ete mis a jour.';
    }

    public function toggleStatus(int $serviceAreaId): void
    {
        $serviceArea = ServiceArea::query()->findOrFail($serviceAreaId);
        $serviceArea->update(['is_active' => ! $serviceArea->is_active]);

        $this->feedbackTone = 'success';
        $this->feedbackMessage = $serviceArea->is_active
            ? 'Le service a ete reactive.'
            : 'Le service a ete desactive.';
    }

    public function delete(int $serviceAreaId): void
    {
        $serviceArea = ServiceArea::query()
            ->withCount(['diningTables', 'menuCategories', 'menus', 'orders', 'products', 'stockMovements'])
            ->findOrFail($serviceAreaId);

        $dependencyCount = (int) $serviceArea->dining_tables_count
            + (int) $serviceArea->menu_categories_count
            + (int) $serviceArea->menus_count
            + (int) $serviceArea->orders_count
            + (int) $serviceArea->products_count
            + (int) $serviceArea->stock_movements_count;

        if ($dependencyCount > 0) {
            $this->feedbackTone = 'warning';
            $this->feedbackMessage = 'Suppression refusee: ce service est deja utilise dans des operations ou des catalogues.';

            return;
        }

        $serviceArea->delete();

        if ($this->editing_service_id === $serviceAreaId) {
            $this->resetForm();
        }

        $this->feedbackTone = 'success';
        $this->feedbackMessage = 'Le service a ete supprime.';
    }

    public function render()
    {
        $services = ServiceArea::query()
            ->withCount(['diningTables', 'menuCategories', 'menus', 'orders', 'products', 'stockMovements'])
            ->when($this->search !== '', function ($query): void {
                $search = trim($this->search);

                $query->where(function ($subQuery) use ($search): void {
                    $subQuery
                        ->where('name', 'like', '%'.$search.'%')
                        ->orWhere('code', 'like', '%'.$search.'%')
                        ->orWhere('description', 'like', '%'.$search.'%');
                });
            })
            ->when($this->domainFilter !== 'all', fn ($query) => $query->forDomain($this->domainFilter))
            ->when($this->statusFilter !== 'all', fn ($query) => $query->where('is_active', $this->statusFilter === 'active'))
            ->ordered()
            ->get();

        $allServices = ServiceArea::query()->get();
        $openNowCount = $allServices->filter(fn (ServiceArea $serviceArea): bool => $serviceArea->isOpenNow() === true)->count();

        return view('livewire.services.manager', [
            'services' => $services,
            'domainLabels' => [
                'shared' => 'Transversal',
                'hotel' => 'Hotel',
                'restaurant' => 'Restaurant',
            ],
            'stats' => [
                'total' => $allServices->count(),
                'active' => $allServices->where('is_active', true)->count(),
                'hotel' => $allServices->where('domain', 'hotel')->count(),
                'restaurant' => $allServices->where('domain', 'restaurant')->count(),
                'open_now' => $openNowCount,
                'monthly_budget' => (float) $allServices->sum(fn (ServiceArea $serviceArea): float => (float) $serviceArea->monthly_budget),
                'dynamic' => $allServices->filter(fn (ServiceArea $serviceArea): bool => $serviceArea->supports_orders || $serviceArea->supports_menu || $serviceArea->supports_pos || $serviceArea->supports_stock || $serviceArea->supports_tables)->count(),
            ],
        ]);
    }

    private function applyDomainPreset(string $domain): void
    {
        $this->domain = in_array($domain, ServiceArea::DOMAINS, true) ? $domain : 'shared';

        if ($this->domain !== 'restaurant') {
            $this->supports_orders = false;
            $this->supports_menu = false;
            $this->supports_pos = false;
            $this->supports_stock = true;
            $this->supports_tables = false;
            $this->daily_target_amount = 0;

            return;
        }

        $this->supports_orders = true;
        $this->supports_menu = true;
        $this->supports_pos = true;
        $this->supports_stock = true;
        $this->supports_tables = true;
    }
}