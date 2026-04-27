<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Mouvements de stock</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #0f172a; }
        h1 { font-size: 16px; margin: 0 0 6px 0; }
        .meta { margin-bottom: 12px; color: #475569; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #cbd5e1; padding: 6px 8px; vertical-align: top; }
        th { background: #f1f5f9; text-align: left; font-size: 10px; text-transform: uppercase; }
        .right { text-align: right; }
    </style>
</head>
<body>
    <h1>Mouvements de stock</h1>
    <div class="meta">
        Export genere le {{ now()->format('d/m/Y H:i') }}
        @if($search !== '')
            | Recherche: {{ $search }}
        @endif
        @if($movementFilter !== 'all')
            | Type: {{ $movementFilter === 'in' ? 'Entree' : 'Sortie' }}
        @endif
        @if(! empty($serviceFilterLabel))
            | Service: {{ $serviceFilterLabel }}
        @endif
        @if($startDate !== '')
            | Du: {{ \Illuminate\Support\Carbon::parse($startDate)->format('d/m/Y') }}
        @endif
        @if($endDate !== '')
            | Au: {{ \Illuminate\Support\Carbon::parse($endDate)->format('d/m/Y') }}
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Produit</th>
                <th>Service</th>
                <th>Type</th>
                <th class="right">Quantite</th>
                <th class="right">Cout unitaire</th>
                <th class="right">Montant</th>
                <th>Motif</th>
                <th>Utilisateur</th>
            </tr>
        </thead>
        <tbody>
            @forelse($movements as $movement)
                @php($amount = (float) $movement->quantity * (float) $movement->unit_cost)
                <tr>
                    <td>{{ $movement->created_at?->format('d/m/Y H:i') }}</td>
                    <td>{{ $movement->product?->name }} @if($movement->product?->sku) ({{ $movement->product?->sku }}) @endif</td>
                    <td>{{ $movement->serviceArea?->name }}</td>
                    <td>{{ $movement->movement_type === 'in' ? 'Entree' : 'Sortie' }}</td>
                    <td class="right">{{ number_format((float) $movement->quantity, 2, '.', ' ') }} {{ $movement->product?->unit }}</td>
                    <td class="right">{{ number_format((float) $movement->unit_cost, 2, '.', ' ') }}</td>
                    <td class="right">{{ number_format($amount, 2, '.', ' ') }}</td>
                    <td>{{ $movement->reason }}</td>
                    <td>{{ $movement->user?->name }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9">Aucun mouvement</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 12px; border: 1px solid #cbd5e1; padding: 8px;">
        <strong>Totaux</strong><br>
        Entrees: {{ number_format((float) ($totals['in_quantity'] ?? 0), 2, '.', ' ') }}
        | Sorties: {{ number_format((float) ($totals['out_quantity'] ?? 0), 2, '.', ' ') }}<br>
        Montant entrees: {{ number_format((float) ($totals['in_amount'] ?? 0), 2, '.', ' ') }}
        | Montant sorties: {{ number_format((float) ($totals['out_amount'] ?? 0), 2, '.', ' ') }}
        | Net: {{ number_format((float) ($totals['net_amount'] ?? 0), 2, '.', ' ') }}
    </div>
</body>
</html>
