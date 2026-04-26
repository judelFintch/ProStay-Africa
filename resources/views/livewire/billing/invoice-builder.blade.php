<div class="mx-auto max-w-7xl space-y-4 px-4 py-4 sm:px-6 lg:px-8">
    <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 sm:p-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-indigo-600">Billing Control</p>
                <h1 class="mt-1 text-2xl font-black text-slate-900">Suivi des commandes livrees</h1>
                <p class="mt-1 max-w-2xl text-sm text-slate-500">
                    Suis les factures ouvertes et enregistre rapidement les paiements restants.
                </p>
            </div>

            <div class="grid gap-2 sm:grid-cols-4">
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400">Commandes a facturer</p>
                    <p class="mt-1 text-xl font-black text-slate-900">{{ $stats['deliverable_orders'] }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400">Montant en attente</p>
                    <p class="mt-1 text-xl font-black text-slate-900">{{ number_format($stats['deliverable_total'], 2, '.', ' ') }}</p>
                </div>
                <div class="rounded-xl border border-indigo-200 bg-indigo-50 px-3 py-2.5">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-indigo-700">Factures externes</p>
                    <p class="mt-1 text-xl font-black text-indigo-700">{{ $stats['external_open_invoices'] }} / {{ number_format($stats['external_unpaid_balance'], 2, '.', ' ') }}</p>
                </div>
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2.5">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-emerald-700">Factures hotel</p>
                    <p class="mt-1 text-xl font-black text-emerald-700">{{ $stats['hotel_open_invoices'] }} / {{ number_format($stats['hotel_unpaid_balance'], 2, '.', ' ') }}</p>
                </div>
            </div>
        </div>
    </section>

    <div class="grid gap-4">
        <section class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
            <div class="border-b border-slate-200 px-4 py-3 sm:px-5">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-base font-black text-slate-900">Factures en suivi (ajout possible a tout moment)</h2>
                    <div class="flex items-center gap-2">
                        <label class="inline-flex items-center gap-2 rounded-lg border border-indigo-200 bg-indigo-50 px-2.5 py-1.5 text-xs font-semibold text-indigo-700">
                            <input type="checkbox" wire:model.live="show_external_only" class="rounded border-indigo-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                            Externes uniquement
                        </label>
                        <a href="{{ route('billing.payments') }}" wire:navigate class="inline-flex items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-100">
                            <i class="fa-solid fa-wallet"></i>
                            Ecran paiements
                        </a>
                    </div>
                </div>
            </div>

            <div class="max-h-[560px] divide-y divide-slate-100 overflow-auto">
                <div class="px-4 py-3 sm:px-5">
                    <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-indigo-700">Clients externes (prioritaire)</p>
                </div>
                @forelse($externalOpenInvoices as $invoice)
                    <div class="space-y-2 border-b border-slate-100 px-4 py-3 sm:px-5">
                        @php
                            $invoiceServers = $invoice->items
                                ->map(fn ($item) => $item->orderItem?->order?->server?->name)
                                ->filter()
                                ->unique()
                                ->values();
                        @endphp

                        <div class="flex items-center justify-between gap-3">
                            <p class="text-sm font-semibold text-slate-900">{{ $invoice->reference }}</p>
                            <span class="rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $invoice->status->value === 'partially_paid' ? 'bg-amber-100 text-amber-700' : ($invoice->status->value === 'draft' ? 'bg-slate-100 text-slate-700' : 'bg-rose-100 text-rose-700') }}">
                                {{ $invoice->status->value }}
                            </span>
                        </div>

                        <p class="text-xs text-slate-500">
                            {{ $invoice->customer?->full_name ?? ($invoice->room ? 'Ch. ' . $invoice->room->number : 'Client non specifie') }}
                        </p>

                        <p class="text-xs text-slate-500">
                            Serveur: {{ $invoiceServers->isNotEmpty() ? $invoiceServers->implode(', ') : 'Non renseigne' }}
                        </p>

                        <div class="grid grid-cols-3 gap-2 text-xs">
                            <div class="rounded-lg border border-slate-200 bg-slate-50 px-2 py-1.5">
                                <p class="text-slate-500">Total</p>
                                <p class="font-semibold text-slate-900">{{ number_format($invoice->total, 2, '.', ' ') }}</p>
                            </div>
                            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-2 py-1.5">
                                <p class="text-emerald-700">Paye</p>
                                <p class="font-semibold text-emerald-700">{{ number_format($invoice->paid_total, 2, '.', ' ') }}</p>
                            </div>
                            <div class="rounded-lg border border-rose-200 bg-rose-50 px-2 py-1.5">
                                <p class="text-rose-700">Reste</p>
                                <p class="font-semibold text-rose-700">{{ number_format($invoice->balance, 2, '.', ' ') }}</p>
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-2 pt-1">
                            <a
                                href="{{ route('billing.payments', ['invoice' => $invoice->id]) }}"
                                wire:navigate
                                class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-200 bg-emerald-50 px-2.5 py-1.5 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-100"
                            >
                                <i class="fa-solid fa-money-bill-wave"></i>
                                Enregistrer paiement
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="px-4 py-5 text-center text-sm text-slate-500 sm:px-5">Aucune facture externe ouverte.</div>
                @endforelse

                <div class="{{ $show_external_only ? 'hidden' : '' }}">
                    <div class="px-4 py-3 sm:px-5">
                        <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-emerald-700">Clients hotel (secondaire)</p>
                    </div>
                    @forelse($hotelOpenInvoices as $invoice)
                        <div class="space-y-2 border-b border-slate-100 px-4 py-3 sm:px-5">
                            @php
                                $invoiceServers = $invoice->items
                                    ->map(fn ($item) => $item->orderItem?->order?->server?->name)
                                    ->filter()
                                    ->unique()
                                    ->values();
                            @endphp

                            <div class="flex items-center justify-between gap-3">
                                <p class="text-sm font-semibold text-slate-900">{{ $invoice->reference }}</p>
                                <span class="rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $invoice->status->value === 'partially_paid' ? 'bg-amber-100 text-amber-700' : ($invoice->status->value === 'draft' ? 'bg-slate-100 text-slate-700' : 'bg-rose-100 text-rose-700') }}">
                                    {{ $invoice->status->value }}
                                </span>
                            </div>

                            <p class="text-xs text-slate-500">
                                {{ $invoice->customer?->full_name ?? ($invoice->room ? 'Ch. ' . $invoice->room->number : 'Client non specifie') }}
                            </p>

                            <p class="text-xs text-slate-500">
                                Serveur: {{ $invoiceServers->isNotEmpty() ? $invoiceServers->implode(', ') : 'Non renseigne' }}
                            </p>

                            <div class="grid grid-cols-3 gap-2 text-xs">
                                <div class="rounded-lg border border-slate-200 bg-slate-50 px-2 py-1.5">
                                    <p class="text-slate-500">Total</p>
                                    <p class="font-semibold text-slate-900">{{ number_format($invoice->total, 2, '.', ' ') }}</p>
                                </div>
                                <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-2 py-1.5">
                                    <p class="text-emerald-700">Paye</p>
                                    <p class="font-semibold text-emerald-700">{{ number_format($invoice->paid_total, 2, '.', ' ') }}</p>
                                </div>
                                <div class="rounded-lg border border-rose-200 bg-rose-50 px-2 py-1.5">
                                    <p class="text-rose-700">Reste</p>
                                    <p class="font-semibold text-rose-700">{{ number_format($invoice->balance, 2, '.', ' ') }}</p>
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-2 pt-1">
                                <a
                                    href="{{ route('billing.payments', ['invoice' => $invoice->id]) }}"
                                    wire:navigate
                                    class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-200 bg-emerald-50 px-2.5 py-1.5 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-100"
                                >
                                    <i class="fa-solid fa-money-bill-wave"></i>
                                    Enregistrer paiement
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="px-4 py-5 text-center text-sm text-slate-500 sm:px-5">Aucune facture hotel ouverte.</div>
                    @endforelse
                </div>
            </div>
        </section>
    </div>
</div>
