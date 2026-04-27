<div wire:poll.15s class="mx-auto max-w-7xl space-y-5 px-4 py-5 sm:px-6 lg:px-8">
    @php
        $invoiceStatusLabels = [
            'draft' => 'Brouillon',
            'unpaid' => 'Impayée',
            'partially_paid' => 'Partiellement payée',
            'paid' => 'Payée',
            'cancelled' => 'Annulée',
        ];
    @endphp

    <section class="rounded-xl border border-slate-200 bg-white">
        <div class="border-b border-slate-200 px-4 py-4">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-500">Suivi restaurant</p>
                    <h1 class="mt-1 text-xl font-bold text-slate-900">Comptes ouverts</h1>
                    <p class="mt-1 text-sm text-slate-500">Suivi en temps reel des dettes par table, client ou chambre.</p>
                </div>

                <div class="grid gap-2 sm:grid-cols-4">
                    <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
                        <p class="text-[11px] uppercase tracking-wide text-slate-500">Comptes</p>
                        <p class="mt-1 text-sm font-semibold text-slate-900">{{ $stats['cards'] }}</p>
                    </div>
                    <div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2">
                        <p class="text-[11px] uppercase tracking-wide text-amber-700">A facturer</p>
                        <p class="mt-1 text-sm font-semibold text-amber-800">{{ number_format($stats['unbilled'], 2, '.', ' ') }} {{ $stats['currency'] }}</p>
                    </div>
                    <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2">
                        <p class="text-[11px] uppercase tracking-wide text-emerald-700">A encaisser</p>
                        <p class="mt-1 text-sm font-semibold text-emerald-800">{{ number_format($stats['invoiced'], 2, '.', ' ') }} {{ $stats['currency'] }}</p>
                    </div>
                    <div class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2">
                        <p class="text-[11px] uppercase tracking-wide text-rose-700">Total du</p>
                        <p class="mt-1 text-sm font-semibold text-rose-800">{{ number_format($stats['due'], 2, '.', ' ') }} {{ $stats['currency'] }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid gap-3 p-4 lg:grid-cols-[minmax(0,1fr)_220px_180px_140px_auto] lg:items-end">
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Recherche</label>
                <input type="search" wire:model.live.debounce.300ms="search" class="prostay-input" placeholder="Table, client, chambre, serveur..." />
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Zone</label>
                <select wire:model.live="service_area_id" class="prostay-input">
                    <option value="">Toutes les zones</option>
                    @foreach($serviceAreas as $area)
                        <option value="{{ $area->id }}">{{ $area->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Vue</label>
                <select wire:model.live="view_mode" class="prostay-input">
                    <option value="all">Tout</option>
                    <option value="table">Tables</option>
                    <option value="external">Clients externes</option>
                    <option value="hotel">Hotel</option>
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Devise</label>
                <select wire:model.live="currency_filter" class="prostay-input">
                    @foreach($supportedCurrencies as $supportedCurrency)
                        <option value="{{ $supportedCurrency }}">{{ $supportedCurrency }}</option>
                    @endforeach
                </select>
            </div>
            <button type="button" wire:click="clearFilters" class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                <i class="fa-solid fa-filter-circle-xmark"></i>
                Effacer
            </button>
        </div>
    </section>

    <section class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_420px]">
        <div class="grid gap-3 md:grid-cols-2">
            @forelse($cards as $card)
                <button
                    type="button"
                    wire:click="selectCard(@js($card['key']))"
                    class="rounded-xl border bg-white p-4 text-left shadow-sm transition hover:border-slate-400 {{ $selectedCard && $selectedCard['key'] === $card['key'] ? 'border-slate-900 ring-2 ring-slate-900/10' : 'border-slate-200' }}"
                >
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="truncate text-base font-bold text-slate-900">{{ $card['title'] }}</p>
                            <p class="mt-1 truncate text-xs text-slate-500">{{ $card['subtitle'] }}</p>
                        </div>
                        <span class="rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $card['status_class'] }}">
                            {{ $card['status_label'] }}
                        </span>
                    </div>

                    <div class="mt-4 grid grid-cols-[minmax(0,1fr)_auto] items-end gap-3">
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Montant du</p>
                            <p class="mt-1 text-2xl font-black text-slate-900">{{ number_format($card['due_total'], 2, '.', ' ') }} {{ $card['currency'] }}</p>
                        </div>
                        <span class="rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $card['kind'] === 'table' ? 'bg-blue-100 text-blue-700' : ($card['kind'] === 'hotel' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-700') }}">
                            {{ $card['kind'] === 'table' ? 'Table' : ($card['kind'] === 'hotel' ? 'Hotel' : 'Client') }}
                        </span>
                    </div>

                    <div class="mt-4 grid grid-cols-3 gap-2 text-xs">
                        <div class="rounded-lg bg-amber-50 px-2 py-2">
                            <p class="font-semibold text-amber-700">Non facture</p>
                            <p class="mt-1 font-bold text-amber-900">{{ number_format($card['unbilled_total'], 2, '.', ' ') }} {{ $card['currency'] }}</p>
                        </div>
                        <div class="rounded-lg bg-emerald-50 px-2 py-2">
                            <p class="font-semibold text-emerald-700">Facture</p>
                            <p class="mt-1 font-bold text-emerald-900">{{ number_format($card['invoice_balance'], 2, '.', ' ') }} {{ $card['currency'] }}</p>
                        </div>
                        <div class="rounded-lg bg-slate-50 px-2 py-2">
                            <p class="font-semibold text-slate-500">Activite</p>
                            <p class="mt-1 truncate font-bold text-slate-800">{{ $card['last_activity_label'] }}</p>
                        </div>
                    </div>
                </button>
            @empty
                <div class="rounded-xl border border-dashed border-slate-300 bg-white px-4 py-12 text-center text-sm text-slate-500 md:col-span-2">
                    Aucun compte ouvert pour le moment.
                </div>
            @endforelse
        </div>

        <aside class="xl:sticky xl:top-20 xl:self-start">
            <div class="rounded-xl border border-slate-200 bg-white">
                @if($selectedCard)
                    <div class="border-b border-slate-200 px-4 py-3">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-lg font-bold text-slate-900">{{ $selectedCard['title'] }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $selectedCard['subtitle'] }}</p>
                            </div>
                            <span class="rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $selectedCard['status_class'] }}">{{ $selectedCard['status_label'] }}</span>
                        </div>
                    </div>

                    <div class="space-y-4 p-4">
                        <div class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-3">
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-rose-700">Solde actuel</p>
                            <p class="mt-1 text-3xl font-black text-rose-800">{{ number_format($selectedCard['due_total'], 2, '.', ' ') }} {{ $selectedCard['currency'] }}</p>
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2">
                                <p class="text-[11px] font-semibold uppercase tracking-wide text-amber-700">Commandes a facturer</p>
                                <p class="mt-1 text-sm font-bold text-amber-900">{{ count($selectedCard['order_ids']) }} · {{ number_format($selectedCard['unbilled_total'], 2, '.', ' ') }} {{ $selectedCard['currency'] }}</p>
                            </div>
                            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2">
                                <p class="text-[11px] font-semibold uppercase tracking-wide text-emerald-700">Factures a encaisser</p>
                                <p class="mt-1 text-sm font-bold text-emerald-900">{{ count($selectedCard['invoice_ids']) }} · {{ number_format($selectedCard['invoice_balance'], 2, '.', ' ') }} {{ $selectedCard['currency'] }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2 text-xs text-slate-500">
                            <p>Zone: <span class="font-semibold text-slate-800">{{ $selectedCard['service_area_name'] ?: '-' }}</span></p>
                            <p>Serveur: <span class="font-semibold text-slate-800">{{ $selectedCard['server_name'] ?: '-' }}</span></p>
                        </div>

                        <div>
                            <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">Commandes non facturees</p>
                            <div class="space-y-2">
                                @forelse(collect($selectedCard['orders']) as $order)
                                    <div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2">
                                        <div class="flex items-center justify-between gap-3 text-sm">
                                            <span class="font-semibold text-amber-950">{{ $order->reference }}</span>
                                            <span class="font-bold text-amber-950">{{ number_format((float) $order->total, 2, '.', ' ') }} {{ strtoupper((string) $order->currency) }}</span>
                                        </div>
                                        <p class="mt-1 text-xs text-amber-800">{{ $order->items->count() }} article(s) · {{ optional($order->created_at)->format('d/m H:i') }}</p>
                                    </div>
                                @empty
                                    <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-3 text-sm text-slate-500">Aucune commande en attente de facturation.</div>
                                @endforelse
                            </div>
                        </div>

                        <div>
                            <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">Factures ouvertes</p>
                            <div class="space-y-2">
                                @forelse(collect($selectedCard['invoices']) as $invoice)
                                    <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
                                        <div class="flex items-center justify-between gap-3 text-sm">
                                            <span class="font-semibold text-slate-900">{{ $invoice->reference }}</span>
                                            <span class="font-bold text-slate-900">{{ number_format((float) $invoice->balance, 2, '.', ' ') }} {{ strtoupper((string) $invoice->currency) }}</span>
                                        </div>
                                        <p class="mt-1 text-xs text-slate-500">{{ $invoiceStatusLabels[$invoice->status->value] ?? $invoice->status->value }} · {{ $invoice->items->count() }} ligne(s)</p>
                                    </div>
                                @empty
                                    <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-3 text-sm text-slate-500">Aucune facture ouverte.</div>
                                @endforelse
                            </div>
                        </div>

                        <div class="grid gap-2 border-t border-slate-200 pt-4 sm:grid-cols-3">
                            @if($selectedCard['kind'] === 'hotel')
                                <a href="{{ route('hotel.reception') }}" wire:navigate class="inline-flex items-center justify-center gap-2 rounded-lg bg-slate-900 px-3 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-700">
                                    <i class="fa-solid fa-concierge-bell"></i>
                                    Reception
                                </a>
                            @elseif(count($selectedCard['invoice_ids']) > 0)
                                <a href="{{ route('billing.payments', ['invoice' => $selectedCard['invoice_ids'][0]]) }}" wire:navigate class="inline-flex items-center justify-center gap-2 rounded-lg bg-emerald-700 px-3 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-600">
                                    <i class="fa-solid fa-wallet"></i>
                                    Payer
                                </a>
                            @else
                                <a href="{{ route('billing.invoices') }}" wire:navigate class="inline-flex items-center justify-center gap-2 rounded-lg bg-amber-600 px-3 py-2.5 text-sm font-semibold text-white transition hover:bg-amber-500">
                                    <i class="fa-solid fa-file-invoice"></i>
                                    Facturer
                                </a>
                            @endif
                            <a href="{{ route('billing.invoices') }}" wire:navigate class="inline-flex items-center justify-center gap-2 rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-2.5 text-sm font-semibold text-indigo-700 transition hover:bg-indigo-100">
                                <i class="fa-solid fa-list-check"></i>
                                Factures
                            </a>
                            <a href="{{ route('orders.create') }}" wire:navigate class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                                <i class="fa-solid fa-plus"></i>
                                Ajouter
                            </a>
                        </div>
                    </div>
                @else
                    <div class="px-4 py-12 text-center text-sm text-slate-500">Selectionne un compte pour afficher le detail.</div>
                @endif
            </div>
        </aside>
    </section>
</div>
