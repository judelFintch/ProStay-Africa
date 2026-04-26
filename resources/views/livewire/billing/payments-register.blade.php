<div class="mx-auto max-w-7xl space-y-5 px-4 py-5 sm:px-6 lg:px-8">
    <section class="overflow-hidden rounded-xl border border-slate-200 bg-white">
        <div class="border-b border-slate-200 px-4 py-4">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-500">Restaurant</p>
                    <h1 class="mt-1 text-xl font-bold text-slate-900">Encaissement clients externes</h1>
                    <p class="mt-1 text-sm text-slate-500">Paiement rapide, controle du solde et historique des encaissements restaurant.</p>
                </div>

                <div class="grid gap-2 sm:grid-cols-3">
                    <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
                        <p class="text-[11px] uppercase tracking-wide text-slate-500">Factures ouvertes</p>
                        <p class="mt-1 text-sm font-semibold text-slate-900">{{ $stats['open_count'] }}</p>
                    </div>
                    <div class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2">
                        <p class="text-[11px] uppercase tracking-wide text-rose-700">Solde externe</p>
                        <p class="mt-1 text-sm font-semibold text-rose-800">{{ number_format($stats['open_balance'], 2, '.', ' ') }} {{ $stats['currency'] }}</p>
                    </div>
                    <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2">
                        <p class="text-[11px] uppercase tracking-wide text-emerald-700">Encaisse aujourd hui</p>
                        <p class="mt-1 text-sm font-semibold text-emerald-800">{{ number_format($stats['paid_today'], 2, '.', ' ') }} {{ $stats['currency'] }}</p>
                    </div>
                </div>
            </div>
        </div>

        @if($notice)
            <div class="border-b border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-800">
                <i class="fa-solid fa-triangle-exclamation mr-2"></i>
                {{ $notice }}
            </div>
        @endif

        <div class="grid gap-4 p-4 xl:grid-cols-[minmax(0,1fr)_380px]">
            <form wire:submit="record" class="space-y-4">
                <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Facture externe</label>
                    <select wire:model.live="invoice_id" class="prostay-input">
                        <option value="">Selectionner une facture</option>
                        @foreach($openInvoices as $invoice)
                            <option value="{{ $invoice->id }}">{{ $invoice->reference }} - solde {{ number_format($invoice->balance, 2, '.', ' ') }} {{ strtoupper((string) $invoice->currency) }}</option>
                        @endforeach
                    </select>
                    @error('invoice_id') <p class="mt-2 text-xs font-semibold text-rose-700">{{ $message }}</p> @enderror
                </div>

                <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_240px]">
                    <div class="rounded-lg border border-slate-200 p-4">
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Montant recu ({{ strtoupper($currency) }})</label>
                        <div class="grid gap-2 sm:grid-cols-[140px_minmax(0,1fr)_auto]">
                            <select wire:model.live="currency" class="prostay-input">
                                @foreach($supportedCurrencies as $supportedCurrency)
                                    <option value="{{ $supportedCurrency }}">{{ $supportedCurrency }}</option>
                                @endforeach
                            </select>
                            <input type="number" wire:model.live="amount" step="0.01" min="0.01" class="prostay-input text-lg font-bold" placeholder="0.00" />
                            <button type="button" wire:click="payFullBalance" class="inline-flex items-center justify-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-100">
                                <i class="fa-solid fa-check-double"></i>
                                Solde exact
                            </button>
                        </div>
                        @error('currency') <p class="mt-2 text-xs font-semibold text-rose-700">{{ $message }}</p> @enderror
                        @error('amount') <p class="mt-2 text-xs font-semibold text-rose-700">{{ $message }}</p> @enderror
                    </div>

                    <div class="rounded-lg border border-slate-200 p-4">
                        <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">Methode</p>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach($methods as $paymentMethod)
                                @php
                                    $labels = [
                                        'cash' => ['Especes', 'fa-money-bill-wave'],
                                        'mobile_money' => ['Mobile', 'fa-mobile-screen-button'],
                                        'card' => ['Carte', 'fa-credit-card'],
                                        'bank_transfer' => ['Virement', 'fa-building-columns'],
                                    ];
                                    $meta = $labels[$paymentMethod] ?? [$paymentMethod, 'fa-wallet'];
                                @endphp
                                <button type="button" wire:click="$set('method', '{{ $paymentMethod }}')" class="rounded-lg border px-3 py-2 text-sm font-semibold transition {{ $method === $paymentMethod ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50' }}">
                                    <i class="fa-solid {{ $meta[1] }} mr-1.5"></i>
                                    {{ $meta[0] }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="grid gap-3 md:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Reference operateur</label>
                        <input type="text" wire:model.blur="provider_reference" class="prostay-input" placeholder="Transaction mobile, POS, banque..." />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Notes</label>
                        <input type="text" wire:model.blur="notes" class="prostay-input" placeholder="Information caisse" />
                    </div>
                </div>

                <button class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-700">
                    <i class="fa-solid fa-lock"></i>
                    Valider l encaissement externe
                </button>
            </form>

            <aside class="rounded-lg border border-slate-200 bg-white">
                <div class="border-b border-slate-200 px-4 py-3">
                    <p class="text-sm font-semibold text-slate-900">Resume facture</p>
                </div>
                <div class="space-y-3 p-4">
                    @if($selectedInvoice)
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Reference</p>
                            <p class="mt-1 text-base font-bold text-slate-900">{{ $selectedInvoice->reference }}</p>
                        </div>
                        <div class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-3">
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-rose-700">Solde restant</p>
                            <p class="mt-1 text-2xl font-black text-rose-800">{{ number_format((float) $selectedInvoice->balance, 2, '.', ' ') }} {{ strtoupper((string) $selectedInvoice->currency) }}</p>
                        </div>
                        <div class="grid grid-cols-2 gap-2 text-sm">
                            <div class="rounded-lg bg-slate-50 px-3 py-2">
                                <p class="text-xs text-slate-500">Total</p>
                                <p class="font-semibold text-slate-900">{{ number_format((float) $selectedInvoice->total, 2, '.', ' ') }} {{ strtoupper((string) $selectedInvoice->currency) }}</p>
                            </div>
                            <div class="rounded-lg bg-slate-50 px-3 py-2">
                                <p class="text-xs text-slate-500">Deja paye</p>
                                <p class="font-semibold text-slate-900">{{ number_format((float) $selectedInvoice->paid_total, 2, '.', ' ') }} {{ strtoupper((string) $selectedInvoice->currency) }}</p>
                            </div>
                        </div>
                        @if($amount > (float) $selectedInvoice->balance)
                            <div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm font-semibold text-amber-800">
                                Montant superieur au solde. Verifie avant validation.
                            </div>
                        @endif
                    @else
                        <div class="px-3 py-10 text-center text-sm text-slate-500">Selectionne une facture externe pour afficher le solde et encaisser.</div>
                    @endif
                </div>
            </aside>
        </div>
    </section>

    <section class="rounded-xl border border-slate-200 bg-white">
        <div class="border-b border-slate-200 px-4 py-3">
            <h2 class="text-lg font-bold text-slate-900">Paiements externes recents</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr class="text-left text-xs uppercase tracking-wide text-slate-600">
                        <th class="px-4 py-3">Reference</th>
                        <th class="px-4 py-3">Facture</th>
                        <th class="px-4 py-3">Montant</th>
                        <th class="px-4 py-3">Methode</th>
                        <th class="px-4 py-3">Caissier</th>
                        <th class="px-4 py-3">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($payments as $payment)
                        <tr>
                            <td class="px-4 py-3 font-medium text-slate-900">{{ $payment->reference }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $payment->invoice?->reference ?? '-' }}</td>
                            <td class="px-4 py-3 font-semibold text-slate-900">{{ number_format($payment->amount, 2, '.', ' ') }} {{ strtoupper((string) $payment->currency) }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $payment->method->value }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $payment->recorder?->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $payment->paid_at?->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-slate-500">Aucun paiement externe recent.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
