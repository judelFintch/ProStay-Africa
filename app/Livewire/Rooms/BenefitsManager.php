<?php

namespace App\Livewire\Rooms;

use App\Models\Benefit;
use App\Models\Menu;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;

class BenefitsManager extends Component
{
    // ── Liste / filtres ────────────────────────────────────────
    public string $search = '';
    public string $filterActive = 'all'; // all | active | inactive

    // ── Formulaire création / édition ─────────────────────────
    public bool $showForm = false;
    public ?int $editingId = null;

    public string $form_name = '';
    public string $form_code = '';
    public string $form_icon = '';
    public string $form_description = '';
    public bool $form_is_active = true;

    /** Ids des plats sélectionnés pour cette prestation */
    public array $form_menu_ids = [];

    // ── Confirmation suppression ───────────────────────────────
    public ?int $deletingId = null;

    // ─────────────────────────────────────────────────────────
    // Formulaire
    // ─────────────────────────────────────────────────────────

    public function openCreate(): void
    {
        $this->ensureCanManageRooms();
        $this->resetForm();
        $this->editingId = null;
        $this->showForm  = true;
        $this->resetErrorBag();
    }

    public function openEdit(int $id): void
    {
        $this->ensureCanManageRooms();
        $benefit = Benefit::with('menus')->findOrFail($id);

        $this->editingId         = $id;
        $this->form_name         = $benefit->name;
        $this->form_code         = $benefit->code;
        $this->form_icon         = (string) ($benefit->icon ?? '');
        $this->form_description  = (string) ($benefit->description ?? '');
        $this->form_is_active    = (bool) $benefit->is_active;
        $this->form_menu_ids     = $benefit->menus->pluck('id')->map(fn ($v) => (int) $v)->toArray();

        $this->showForm = true;
        $this->resetErrorBag();
    }

    public function closeForm(): void
    {
        $this->showForm = false;
        $this->resetForm();
        $this->resetErrorBag();
    }

    public function save(): void
    {
        $this->ensureCanManageRooms();

        $validated = $this->validate([
            'form_name'        => ['required', 'string', 'max:100'],
            'form_code'        => ['required', 'string', 'max:50', 'regex:/^[a-z0-9_]+$/'],
            'form_icon'        => ['nullable', 'string', 'max:10'],
            'form_description' => ['nullable', 'string', 'max:1000'],
            'form_is_active'   => ['boolean'],
            'form_menu_ids'    => ['array'],
            'form_menu_ids.*'  => ['integer', 'exists:menus,id'],
        ], [
            'form_code.regex' => 'Le code ne doit contenir que des lettres minuscules, chiffres et underscores.',
        ]);

        // Unicité du code (ignorer l'enregistrement en cours si édition)
        $codeQuery = Benefit::where('code', $validated['form_code']);
        if ($this->editingId) {
            $codeQuery->where('id', '!=', $this->editingId);
        }
        if ($codeQuery->exists()) {
            $this->addError('form_code', 'Ce code est déjà utilisé par une autre prestation.');
            return;
        }

        $data = [
            'name'        => $validated['form_name'],
            'code'        => $validated['form_code'],
            'icon'        => $validated['form_icon'] ?: null,
            'description' => $validated['form_description'] ?: null,
            'is_active'   => (bool) $validated['form_is_active'],
        ];

        if ($this->editingId) {
            $benefit = Benefit::findOrFail($this->editingId);
            $benefit->update($data);
        } else {
            $benefit = Benefit::create($data);
        }

        $benefit->menus()->sync($validated['form_menu_ids']);

        $this->showForm = false;
        $this->resetForm();
    }

    public function updatedFormName(): void
    {
        // Auto-génère le code si on crée une nouvelle prestation
        if (! $this->editingId && $this->form_code === '') {
            $this->form_code = Str::slug($this->form_name, '_');
        }
    }

    // ─────────────────────────────────────────────────────────
    // Suppression
    // ─────────────────────────────────────────────────────────

    public function confirmDelete(int $id): void
    {
        $this->ensureCanManageRooms();
        $this->deletingId = $id;
    }

    public function cancelDelete(): void
    {
        $this->deletingId = null;
    }

    public function delete(): void
    {
        $this->ensureCanManageRooms();

        if (! $this->deletingId) {
            return;
        }

        $benefit = Benefit::find($this->deletingId);
        if ($benefit) {
            $benefit->rooms()->detach();
            $benefit->menus()->detach();
            $benefit->delete();
        }

        $this->deletingId = null;
    }

    // ─────────────────────────────────────────────────────────
    // Toggle actif
    // ─────────────────────────────────────────────────────────

    public function toggleActive(int $id): void
    {
        $this->ensureCanManageRooms();
        $benefit = Benefit::findOrFail($id);
        $benefit->update(['is_active' => ! $benefit->is_active]);
    }

    // ─────────────────────────────────────────────────────────
    // Render
    // ─────────────────────────────────────────────────────────

    public function render()
    {
        $query = Benefit::query()->withCount(['rooms', 'menus']);

        if ($this->search !== '') {
            $query->where(function ($q): void {
                $q->where('name', 'like', '%'.$this->search.'%')
                  ->orWhere('code', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->filterActive === 'active') {
            $query->where('is_active', true);
        } elseif ($this->filterActive === 'inactive') {
            $query->where('is_active', false);
        }

        $benefits = $query->orderBy('name')->with('menus')->get();
        $allMenus = Menu::query()->where('is_available', true)->orderBy('name')->get();

        return view('livewire.rooms.benefits', [
            'benefits' => $benefits,
            'allMenus' => $allMenus,
        ]);
    }

    // ─────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────

    private function resetForm(): void
    {
        $this->editingId        = null;
        $this->form_name        = '';
        $this->form_code        = '';
        $this->form_icon        = '';
        $this->form_description = '';
        $this->form_is_active   = true;
        $this->form_menu_ids    = [];
    }

    private function ensureCanManageRooms(): void
    {
        $user = Auth::user();
        abort_unless($user instanceof User, 403);

        /** @var User $user */
        if (! $user->roles()->exists()) {
            return;
        }

        $canManage = $user->roles()
            ->where(function ($q): void {
                $q->where('name', 'admin')
                    ->orWhereHas('permissions', fn ($p) => $p->where('name', 'rooms.manage'));
            })
            ->exists();

        abort_unless($canManage, 403);
    }
}
