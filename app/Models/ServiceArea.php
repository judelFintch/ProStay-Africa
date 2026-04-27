<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceArea extends Model
{
    use HasFactory;

    public const DOMAINS = ['hotel', 'restaurant', 'shared'];

    public const CAPABILITIES = [
        'orders',
        'menu',
        'pos',
        'stock',
        'tables',
    ];

    protected $fillable = [
        'name',
        'code',
        'description',
        'domain',
        'manager_name',
        'manager_phone',
        'opens_at',
        'closes_at',
        'daily_target_amount',
        'monthly_budget',
        'sort_order',
        'is_active',
        'supports_orders',
        'supports_menu',
        'supports_pos',
        'supports_stock',
        'supports_tables',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'daily_target_amount' => 'decimal:2',
            'monthly_budget' => 'decimal:2',
            'is_active' => 'boolean',
            'supports_orders' => 'boolean',
            'supports_menu' => 'boolean',
            'supports_pos' => 'boolean',
            'supports_stock' => 'boolean',
            'supports_tables' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function scopeForDomain(Builder $query, ?string $domain): Builder
    {
        $normalizedDomain = strtolower(trim((string) $domain));

        if ($normalizedDomain === '' || $normalizedDomain === 'all') {
            return $query;
        }

        return $query->where('domain', $normalizedDomain);
    }

    public function scopeSupporting(Builder $query, string $capability): Builder
    {
        $normalizedCapability = strtolower(trim($capability));

        if (! in_array($normalizedCapability, self::CAPABILITIES, true)) {
            return $query;
        }

        return $query->where('supports_'.$normalizedCapability, true);
    }

    public function isOpenNow(?Carbon $reference = null): ?bool
    {
        if (! $this->opens_at || ! $this->closes_at) {
            return null;
        }

        $reference ??= now();
        $current = $reference->format('H:i');
        $opensAt = substr((string) $this->opens_at, 0, 5);
        $closesAt = substr((string) $this->closes_at, 0, 5);

        if ($opensAt <= $closesAt) {
            return $current >= $opensAt && $current <= $closesAt;
        }

        return $current >= $opensAt || $current <= $closesAt;
    }

    public function menuCategories(): HasMany
    {
        return $this->hasMany(MenuCategory::class);
    }

    public function diningTables(): HasMany
    {
        return $this->hasMany(DiningTable::class);
    }

    public function menus(): HasMany
    {
        return $this->hasMany(Menu::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }
}
