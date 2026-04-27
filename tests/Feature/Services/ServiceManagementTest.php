<?php

namespace Tests\Feature\Services;

use App\Livewire\Dishes\Manager as DishesManager;
use App\Livewire\Services\Manager as ServicesManager;
use App\Models\ServiceArea;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ServiceManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_services_page_can_be_rendered(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('services.index'))
            ->assertOk()
            ->assertSee('Gestion dynamique des services');
    }

    public function test_service_manager_can_create_a_dynamic_service(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(ServicesManager::class)
            ->set('name', 'Room Service')
            ->set('code', 'room-service')
            ->set('domain', 'hotel')
            ->set('description', 'Service de commandes internes aux chambres.')
            ->set('manager_name', 'Superviseur Nuit')
            ->set('manager_phone', '+243970000000')
            ->set('opens_at', '06:00')
            ->set('closes_at', '22:00')
            ->set('daily_target_amount', 0)
            ->set('monthly_budget', 4000)
            ->set('sort_order', 15)
            ->set('supports_orders', false)
            ->set('supports_menu', false)
            ->set('supports_pos', false)
            ->set('supports_stock', true)
            ->set('supports_tables', false)
            ->call('save');

        $this->assertDatabaseHas('service_areas', [
            'name' => 'Room Service',
            'code' => 'room-service',
            'domain' => 'hotel',
            'manager_name' => 'Superviseur Nuit',
            'opens_at' => '06:00:00',
            'monthly_budget' => 4000,
            'supports_stock' => true,
            'supports_menu' => false,
        ]);
    }

    public function test_dishes_manager_only_exposes_services_configured_for_menu(): void
    {
        ServiceArea::query()->create([
            'name' => 'Restaurant Chef',
            'code' => 'restaurant-chef',
            'domain' => 'restaurant',
            'sort_order' => 5,
            'is_active' => true,
            'supports_orders' => true,
            'supports_menu' => true,
            'supports_pos' => true,
            'supports_stock' => true,
            'supports_tables' => true,
        ]);

        ServiceArea::query()->create([
            'name' => 'Comptoir Express',
            'code' => 'comptoir-express',
            'domain' => 'restaurant',
            'sort_order' => 6,
            'is_active' => true,
            'supports_orders' => true,
            'supports_menu' => false,
            'supports_pos' => true,
            'supports_stock' => true,
            'supports_tables' => false,
        ]);

        $this->actingAs(User::factory()->create());

        Livewire::test(DishesManager::class)
            ->assertViewHas('areas', function ($areas): bool {
                return $areas->pluck('code')->contains('restaurant-chef')
                    && ! $areas->pluck('code')->contains('comptoir-express');
            });
    }
}