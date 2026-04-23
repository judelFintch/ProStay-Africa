<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'admin',
            'receptionist',
            'cashier',
            'waiter',
            'barman',
            'stock_manager',
            'laundry_staff',
        ];

        $permissions = [
            'customers.manage',
            'rooms.manage',
            'stays.manage',
            'orders.manage',
            'billing.manage',
            'payments.manage',
            'pos.use',
            'stock.manage',
            'laundry.manage',
            'reports.view',
            'users.manage',
        ];

        foreach ($roles as $name) {
            Role::query()->firstOrCreate(['name' => $name], ['label' => ucfirst(str_replace('_', ' ', $name))]);
        }

        foreach ($permissions as $name) {
            Permission::query()->firstOrCreate(['name' => $name], ['label' => ucfirst(str_replace('.', ' ', $name))]);
        }

        $admin = Role::query()->where('name', 'admin')->first();
        if ($admin) {
            $admin->permissions()->sync(Permission::query()->pluck('id'));
        }
    }
}
