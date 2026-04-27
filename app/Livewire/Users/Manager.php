<?php

namespace App\Livewire\Users;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Manager extends Component
{
    public string $search = '';
    public string $roleFilter = 'all';
    public string $serverFilter = 'all';

    public ?int $editing_user_id = null;
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public array $role_ids = [];
    public bool $is_server = false;
    public bool $server_active = true;
    public ?string $server_alias = null;

    public ?string $feedbackMessage = null;
    public string $feedbackTone = 'success';

    protected function rules(): array
    {
        $passwordRules = $this->editing_user_id
            ? ['nullable', 'string', 'min:8', 'same:password_confirmation']
            : ['required', 'string', 'min:8', 'same:password_confirmation'];

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->editing_user_id)],
            'password' => $passwordRules,
            'password_confirmation' => $this->editing_user_id ? ['nullable', 'string', 'min:8'] : ['required', 'string', 'min:8'],
            'role_ids' => ['array'],
            'role_ids.*' => ['exists:roles,id'],
            'is_server' => ['boolean'],
            'server_active' => ['boolean'],
            'server_alias' => ['nullable', 'string', 'max:80'],
        ];
    }

    public function resetForm(): void
    {
        $this->reset([
            'editing_user_id',
            'name',
            'email',
            'password',
            'password_confirmation',
            'role_ids',
            'server_alias',
            'feedbackMessage',
        ]);

        $this->is_server = false;
        $this->server_active = true;
        $this->feedbackTone = 'success';
        $this->resetErrorBag();
    }

    public function edit(int $userId): void
    {
        $user = User::query()->with('roles.permissions')->findOrFail($userId);

        $this->editing_user_id = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->password = '';
        $this->password_confirmation = '';
        $this->role_ids = $user->roles->pluck('id')->all();
        $this->is_server = (bool) $user->is_server;
        $this->server_active = (bool) $user->server_active;
        $this->server_alias = $user->server_alias;
        $this->feedbackMessage = null;
        $this->resetErrorBag();
    }

    public function save(): void
    {
        $validated = $this->validate();

        $payload = [
            'name' => trim($validated['name']),
            'email' => strtolower(trim($validated['email'])),
            'is_server' => (bool) $validated['is_server'],
            'server_active' => (bool) $validated['is_server'] && (bool) $validated['server_active'],
            'server_alias' => (bool) $validated['is_server'] ? ($validated['server_alias'] ? trim($validated['server_alias']) : null) : null,
        ];

        if (! empty($validated['password'])) {
            $payload['password'] = Hash::make($validated['password']);
        }

        $user = User::query()->updateOrCreate(
            ['id' => $this->editing_user_id],
            $payload,
        );

        $user->roles()->sync($validated['role_ids'] ?? []);

        $this->feedbackTone = 'success';
        $this->feedbackMessage = $this->editing_user_id
            ? 'Le compte utilisateur a ete mis a jour.'
            : 'Le compte utilisateur a ete cree.';

        $this->edit($user->id);
    }

    public function toggleServerStatus(int $userId): void
    {
        $user = User::query()->findOrFail($userId);

        if (! $user->is_server) {
            $user->update([
                'is_server' => true,
                'server_active' => true,
            ]);

            $this->feedbackTone = 'success';
            $this->feedbackMessage = 'Le profil a ete promu en serveur actif.';

            return;
        }

        $user->update([
            'server_active' => ! (bool) $user->server_active,
        ]);

        $this->feedbackTone = 'success';
        $this->feedbackMessage = $user->fresh()->server_active
            ? 'Le serveur a ete active.'
            : 'Le serveur a ete desactive.';
    }

    public function delete(int $userId): void
    {
        $user = User::query()->findOrFail($userId);

        if ((int) auth()->id() === (int) $user->id) {
            $this->feedbackTone = 'warning';
            $this->feedbackMessage = 'Vous ne pouvez pas supprimer votre propre compte.';

            return;
        }

        $user->roles()->detach();
        $user->delete();

        if ($this->editing_user_id === $userId) {
            $this->resetForm();
        }

        $this->feedbackTone = 'success';
        $this->feedbackMessage = 'Le compte utilisateur a ete supprime.';
    }

    public function render()
    {
        $users = User::query()
            ->with(['roles.permissions'])
            ->when($this->search !== '', function ($query): void {
                $needle = trim($this->search);

                $query->where(function ($nested) use ($needle): void {
                    $nested->where('name', 'like', '%'.$needle.'%')
                        ->orWhere('email', 'like', '%'.$needle.'%')
                        ->orWhere('server_alias', 'like', '%'.$needle.'%');
                });
            })
            ->when($this->roleFilter !== 'all', function ($query): void {
                $roleId = (int) $this->roleFilter;
                $query->whereHas('roles', fn ($roleQuery) => $roleQuery->where('roles.id', $roleId));
            })
            ->when($this->serverFilter !== 'all', function ($query): void {
                if ($this->serverFilter === 'server') {
                    $query->where('is_server', true);

                    return;
                }

                if ($this->serverFilter === 'active_server') {
                    $query->where('is_server', true)->where('server_active', true);

                    return;
                }

                if ($this->serverFilter === 'inactive_server') {
                    $query->where('is_server', true)->where('server_active', false);
                }
            })
            ->orderBy('name')
            ->get()
            ->map(function (User $user): User {
                $effectivePermissions = $user->roles
                    ->flatMap(fn ($role) => $role->permissions)
                    ->unique('id')
                    ->sortBy('label')
                    ->values();

                $user->setRelation('effective_permissions', $effectivePermissions);

                return $user;
            });

        $roles = Role::query()->with('permissions')->orderBy('label')->orderBy('name')->get();
        $permissions = Permission::query()->orderBy('label')->orderBy('name')->get();
        $allUsers = User::query()->get();

        return view('livewire.users.manager', [
            'users' => $users,
            'roles' => $roles,
            'permissions' => $permissions,
            'stats' => [
                'total' => $allUsers->count(),
                'servers' => $allUsers->where('is_server', true)->count(),
                'active_servers' => $allUsers->where('is_server', true)->where('server_active', true)->count(),
                'admins' => User::query()->whereHas('roles', fn ($query) => $query->where('name', 'admin'))->count(),
            ],
        ]);
    }
}