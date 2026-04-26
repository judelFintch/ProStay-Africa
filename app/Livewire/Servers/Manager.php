<?php

namespace App\Livewire\Servers;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Manager extends Component
{
    public string $search = '';

    public string $name = '';
    public string $email = '';
    public string $password = '';
    public ?string $server_alias = null;
    public bool $server_active = true;

    public ?int $promote_user_id = null;

    public function saveServer(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8'],
            'server_alias' => ['nullable', 'string', 'max:80'],
            'server_active' => ['boolean'],
        ]);

        User::query()->create([
            'name' => trim($this->name),
            'email' => strtolower(trim($this->email)),
            'password' => Hash::make($this->password),
            'is_server' => true,
            'server_active' => $this->server_active,
            'server_alias' => $this->server_alias ? trim($this->server_alias) : null,
        ]);

        $this->reset(['name', 'email', 'password', 'server_alias']);
        $this->server_active = true;
    }

    public function promoteUser(): void
    {
        $this->validate([
            'promote_user_id' => ['required', 'exists:users,id'],
        ]);

        User::query()
            ->where('id', $this->promote_user_id)
            ->update([
                'is_server' => true,
                'server_active' => true,
            ]);

        $this->promote_user_id = null;
    }

    public function toggleServerStatus(int $userId): void
    {
        $server = User::query()
            ->where('is_server', true)
            ->findOrFail($userId);

        $server->update([
            'server_active' => ! (bool) $server->server_active,
        ]);
    }

    public function removeServerRole(int $userId): void
    {
        User::query()
            ->where('id', $userId)
            ->where('is_server', true)
            ->update([
                'is_server' => false,
                'server_active' => false,
                'server_alias' => null,
            ]);
    }

    public function render()
    {
        $servers = User::query()
            ->where('is_server', true)
            ->when($this->search, function ($query): void {
                $needle = trim($this->search);

                $query->where(function ($nested) use ($needle): void {
                    $nested->where('name', 'like', '%' . $needle . '%')
                        ->orWhere('email', 'like', '%' . $needle . '%')
                        ->orWhere('server_alias', 'like', '%' . $needle . '%');
                });
            })
            ->orderByDesc('server_active')
            ->orderBy('name')
            ->get();

        $promotableUsers = User::query()
            ->where('is_server', false)
            ->orderBy('name')
            ->limit(100)
            ->get();

        return view('livewire.servers.manager', [
            'servers' => $servers,
            'promotableUsers' => $promotableUsers,
            'stats' => [
                'total' => User::query()->where('is_server', true)->count(),
                'active' => User::query()->where('is_server', true)->where('server_active', true)->count(),
                'inactive' => User::query()->where('is_server', true)->where('server_active', false)->count(),
            ],
        ]);
    }
}
