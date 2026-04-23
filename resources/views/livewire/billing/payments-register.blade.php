<div class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
    <div class="rounded-3xl bg-gradient-to-br from-slate-900 via-emerald-900 to-cyan-900 p-6 text-white shadow-xl sm:p-8">
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-300">Payments</p>
        <h1 class="mt-2 text-2xl font-black sm:text-3xl">Payment Register</h1>
        <p class="mt-2 text-sm text-slate-200/90">Record full or partial payments and keep full history.</p>
    </div>

    <div class="prostay-surface p-5 sm:p-6">
        <h2 class="text-lg font-bold text-slate-900">Record payment</h2>
        <form wire:submit="record" class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-5">
            <select wire:model="invoice_id" class="prostay-input xl:col-span-2">
                <option value="">Select invoice</option>
                @foreach($openInvoices as $invoice)
                    <option value="{{ $invoice->id }}">{{ $invoice->reference }} • balance {{ number_format($invoice->balance, 2, '.', ' ') }}</option>
                @endforeach
            </select>

            <input type="number" wire:model="amount" step="0.01" min="0.01" class="prostay-input" placeholder="Amount" />

            <select wire:model="method" class="prostay-input">
                @foreach($methods as $paymentMethod)
                    <option value="{{ $paymentMethod }}">{{ $paymentMethod }}</option>
                @endforeach
            </select>

            <input type="text" wire:model="provider_reference" class="prostay-input" placeholder="Provider ref" />
            <input type="text" wire:model="notes" class="prostay-input md:col-span-2 xl:col-span-4" placeholder="Notes" />
            <button class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-700">Record</button>
        </form>
    </div>

    <div class="prostay-surface p-5">
        <h2 class="text-lg font-bold text-slate-900">Recent payments</h2>
        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr class="text-left text-xs uppercase tracking-wide text-slate-600">
                        <th class="px-4 py-3">Reference</th>
                        <th class="px-4 py-3">Invoice</th>
                        <th class="px-4 py-3">Amount</th>
                        <th class="px-4 py-3">Method</th>
                        <th class="px-4 py-3">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @foreach($payments as $payment)
                        <tr>
                            <td class="px-4 py-3 font-medium text-slate-900">{{ $payment->reference }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $payment->invoice?->reference ?? '-' }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ number_format($payment->amount, 2, '.', ' ') }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $payment->method->value }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $payment->paid_at?->format('Y-m-d H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
