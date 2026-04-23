<div class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
    <div class="rounded-3xl bg-gradient-to-br from-slate-900 via-slate-800 to-teal-900 p-6 text-white shadow-xl sm:p-8">
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-300">Guest CRM</p>
        <h1 class="mt-2 text-2xl font-black sm:text-3xl">Customers</h1>
        <p class="mt-2 text-sm text-slate-200/90">Centralize guest profiles and identify walk-ins faster at check-in and POS.</p>
    </div>

    <div class="prostay-surface p-5 sm:p-6">
        <h2 class="text-lg font-bold text-slate-900">Create customer</h2>

        <form wire:submit="createCustomer" class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-4">
            <input type="text" wire:model="full_name" placeholder="Full name" class="prostay-input" />
            <input type="text" wire:model="phone" placeholder="Phone" class="prostay-input" />
            <input type="email" wire:model="email" placeholder="Email" class="prostay-input" />

            <label class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-medium text-slate-700">
                <input type="checkbox" wire:model="is_identified" class="rounded border-slate-300 text-emerald-600 shadow-sm focus:ring-emerald-500">
                Identified
            </label>

            <button type="submit" class="md:col-span-2 xl:col-span-4 inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-600">
                <i class="fa-solid fa-user-plus"></i>
                Create customer
            </button>
        </form>
    </div>

    <div class="prostay-surface p-5 sm:p-6">
        <div class="relative">
            <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
            <input
                type="text"
                wire:model.live.debounce.400ms="search"
                placeholder="Search by name, phone, email..."
                class="prostay-input pl-10"
            >
        </div>

        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr class="text-left text-xs uppercase tracking-wide text-slate-600">
                        <th class="px-4 py-3">Name</th>
                        <th class="px-4 py-3">Phone</th>
                        <th class="px-4 py-3">Email</th>
                        <th class="px-4 py-3">Identified</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($customers as $customer)
                        <tr>
                            <td class="px-4 py-3 font-medium text-slate-900">{{ $customer->full_name ?? 'Anonymous / Walk-in' }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $customer->phone ?? '-' }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $customer->email ?? '-' }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold {{ $customer->is_identified ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-700' }}">
                                    {{ $customer->is_identified ? 'Yes' : 'No' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-slate-500">No customers yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $customers->links() }}
        </div>
    </div>
</div>
