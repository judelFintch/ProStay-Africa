<div class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
    <div class="rounded-3xl bg-gradient-to-br from-slate-900 via-indigo-900 to-cyan-900 p-6 text-white shadow-xl sm:p-8">
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-cyan-300">Finance</p>
        <h1 class="mt-2 text-2xl font-black sm:text-3xl">Invoice Builder</h1>
        <p class="mt-2 text-sm text-slate-200/90">Consolidate open orders into one invoice and speed up settlement at checkout.</p>
    </div>

    <div class="prostay-surface p-5 sm:p-6">
        <form wire:submit="build" class="space-y-4">
            <div class="max-h-[360px] space-y-2 overflow-auto rounded-2xl border border-slate-200 bg-slate-50/60 p-3">
                @forelse($openOrders as $order)
                    <label class="flex cursor-pointer items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm transition hover:border-slate-300">
                        <span class="inline-flex items-center gap-2 text-slate-700">
                            <input type="checkbox" wire:model="selectedOrderIds" value="{{ $order->id }}" class="rounded border-slate-300 text-emerald-600 shadow-sm focus:ring-emerald-500">
                            <span class="font-medium text-slate-900">{{ $order->reference }}</span>
                            <span class="text-xs text-slate-500">({{ $order->customer_type->value }})</span>
                        </span>
                        <strong class="text-slate-900">{{ number_format($order->total, 2, '.', ' ') }}</strong>
                    </label>
                @empty
                    <p class="p-3 text-sm text-slate-500">No billable orders found.</p>
                @endforelse
            </div>

            <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-700">
                <i class="fa-solid fa-file-circle-plus"></i>
                Build invoice from selection
            </button>
        </form>
    </div>
</div>
