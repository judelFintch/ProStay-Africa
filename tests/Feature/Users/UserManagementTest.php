<?php

namespace Tests\Feature\Users;

use App\Livewire\Users\Manager;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_page_can_be_rendered(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('users.index'))
            ->assertOk()
            ->assertSee('Gestion des utilisateurs');
    }

    public function test_manager_can_create_user_with_roles_and_server_profile(): void
    {
        $admin = User::factory()->create();
        $this->actingAs($admin);

        $role = Role::query()->create([
            'name' => 'cashier',
            'label' => 'Caissier',
        ]);

        $permission = Permission::query()->create([
            'name' => 'payments.manage',
            'label' => 'Payments manage',
        ]);

        $role->permissions()->sync([$permission->id]);

        Livewire::test(Manager::class)
            ->set('name', 'Sarah Mukendi')
            ->set('email', 'sarah@prostay.africa')
            ->set('password', 'secret123')
            ->set('password_confirmation', 'secret123')
            ->set('role_ids', [$role->id])
            ->set('is_server', true)
            ->set('server_active', true)
            ->set('server_alias', 'Caisse soir')
            ->call('save');

        $this->assertDatabaseHas('users', [
            'email' => 'sarah@prostay.africa',
            'is_server' => true,
            'server_active' => true,
            'server_alias' => 'Caisse soir',
        ]);

        $created = User::query()->where('email', 'sarah@prostay.africa')->firstOrFail();
        $this->assertTrue(Hash::check('secret123', $created->password));
        $this->assertTrue($created->roles()->whereKey($role->id)->exists());
    }

    public function test_manager_can_update_roles_without_forcing_password_reset(): void
    {
        $admin = User::factory()->create();
        $this->actingAs($admin);

        $role = Role::query()->create(['name' => 'receptionist', 'label' => 'Receptioniste']);
        $user = User::factory()->create([
            'password' => 'initial-pass',
        ]);
        $originalHash = $user->password;

        Livewire::test(Manager::class)
            ->call('edit', $user->id)
            ->set('role_ids', [$role->id])
            ->set('password', '')
            ->set('password_confirmation', '')
            ->call('save');

        $this->assertSame($originalHash, $user->fresh()->password);
        $this->assertTrue($user->fresh()->roles()->whereKey($role->id)->exists());
    }
}