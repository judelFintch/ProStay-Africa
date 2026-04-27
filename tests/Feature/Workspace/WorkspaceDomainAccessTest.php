<?php

namespace Tests\Feature\Workspace;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkspaceDomainAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_restaurant_user_cannot_access_hotel_domain_routes(): void
    {
        $user = User::factory()->create();

        $role = Role::query()->create([
            'name' => 'restaurant_operator',
            'label' => 'Restaurant Operator',
        ]);

        $permission = Permission::query()->create([
            'name' => 'orders.manage',
            'label' => 'Orders Manage',
        ]);

        $role->permissions()->sync([$permission->id]);
        $user->roles()->sync([$role->id]);

        $this->actingAs($user)
            ->get(route('orders.create'))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('rooms.index'))
            ->assertForbidden();

        $this->assertSame('restaurant', session('workspace_context'));
    }

    public function test_hotel_user_cannot_access_restaurant_domain_routes(): void
    {
        $user = User::factory()->create();

        $role = Role::query()->create([
            'name' => 'hotel_operator',
            'label' => 'Hotel Operator',
        ]);

        $permission = Permission::query()->create([
            'name' => 'rooms.manage',
            'label' => 'Rooms Manage',
        ]);

        $role->permissions()->sync([$permission->id]);
        $user->roles()->sync([$role->id]);

        $this->actingAs($user)
            ->get(route('rooms.index'))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('orders.create'))
            ->assertForbidden();

        $this->assertSame('hotel', session('workspace_context'));
    }
}
