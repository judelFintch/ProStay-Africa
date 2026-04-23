<?php

namespace App\Livewire\Customers;

use App\Models\Customer;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $full_name = '';
    public ?string $phone = null;
    public ?string $email = null;
    public bool $is_identified = true;

    public function createCustomer(): void
    {
        $this->validate([
            'full_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'is_identified' => ['boolean'],
        ]);

        Customer::create([
            'full_name' => $this->full_name ?: null,
            'phone' => $this->phone,
            'email' => $this->email,
            'is_identified' => $this->is_identified,
        ]);

        $this->reset(['full_name', 'phone', 'email']);
        $this->is_identified = true;
        $this->dispatch('customer-created');
    }

    public function render()
    {
        $customers = Customer::query()
            ->when($this->search, function ($query) {
                $query->where('full_name', 'like', '%' . $this->search . '%')
                    ->orWhere('phone', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%');
            })
            ->latest()
            ->paginate(10);

        return view('livewire.customers.index', [
            'customers' => $customers,
        ]);
    }
}
