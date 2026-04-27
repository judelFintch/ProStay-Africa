<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Rapport global</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #0f172a; }
        h1 { font-size: 16px; margin: 0 0 4px 0; }
        h2 { font-size: 12px; margin: 14px 0 6px 0; color: #0f766e; }
        .meta { margin-bottom: 8px; color: #475569; }
        .kpi-grid { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
        .kpi-grid td { border: 1px solid #cbd5e1; padding: 5px 7px; }
        .kpi-grid td:first-child { font-weight: bold; background: #f8fafc; width: 35%; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        th, td { border: 1px solid #cbd5e1; padding: 5px 6px; vertical-align: top; }
        th { background: #f1f5f9; text-align: left; font-size: 9px; text-transform: uppercase; }
        .right { text-align: right; }
        .muted { color: #64748b; }
    </style>
</head>
<body>
    <h1>Rapport global ProStay Africa</h1>
    <div class="meta">
        Periode: {{ $startDate }} -> {{ $endDate }}
        | Genere le: {{ now()->format('d/m/Y H:i') }}
        | Devise: {{ $reportCurrency }}
        | Filtre utilisateur: {{ $userFilter === 'all' ? 'Tous' : $reportUsers->firstWhere('id', (int) $userFilter)?->name }}
        | Filtre service: {{ $serviceFilter === 'all' ? 'Tous' : $reportServices->firstWhere('id', (int) $serviceFilter)?->name }}
    </div>

    <h2>Indicateurs cles</h2>
    <table class="kpi-grid">
        <tr><td>Occupation</td><td>{{ $occupancy }}% ({{ $activeStays }}/{{ $totalRooms }})</td></tr>
        <tr><td>CA commandes</td><td>{{ number_format($salesOrderAmount, 2, '.', ' ') }}</td></tr>
        <tr><td>Paiements encaisses</td><td>{{ number_format($paymentsTotal, 2, '.', ' ') }}</td></tr>
        <tr><td>Ticket moyen</td><td>{{ number_format($avgTicket, 2, '.', ' ') }}</td></tr>
        <tr><td>CA du jour</td><td>{{ number_format($todayRevenue, 2, '.', ' ') }} {{ $reportCurrency }}</td></tr>
        <tr><td>Factures ouvertes</td><td>{{ $openInvoices }}</td></tr>
        <tr><td>Commandes du jour</td><td>{{ $ordersToday }}</td></tr>
        <tr><td>Restaurant externe encaisse</td><td>{{ number_format($restaurantExternalRevenue, 2, '.', ' ') }} {{ $reportCurrency }}</td></tr>
        <tr><td>Restaurant transfere hotel</td><td>{{ number_format($restaurantHotelTransferBalance, 2, '.', ' ') }} {{ $reportCurrency }}</td></tr>
    </table>

    <h2>Ventes par service</h2>
    <table>
        <thead>
            <tr>
                <th>Service</th>
                <th class="right">Commandes</th>
                <th class="right">Montant</th>
            </tr>
        </thead>
        <tbody>
            @forelse($salesByService as $row)
                <tr>
                    <td>{{ $row->service_name }}</td>
                    <td class="right">{{ $row->orders_count }}</td>
                    <td class="right">{{ number_format((float) $row->orders_amount, 2, '.', ' ') }}</td>
                </tr>
            @empty
                <tr><td colspan="3" class="muted">Aucune donnee</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Paiements et commandes par utilisateur</h2>
    <table>
        <thead>
            <tr>
                <th>Utilisateur</th>
                <th class="right">Paiements (nb)</th>
                <th class="right">Paiements (montant)</th>
                <th class="right">Commandes (nb)</th>
                <th class="right">Commandes (montant)</th>
            </tr>
        </thead>
        <tbody>
            @php
                $paymentsMap = $paymentsByUser->keyBy('user_name');
                $ordersMap = $ordersByUser->keyBy('user_name');
                $allUsers = collect(array_unique(array_merge($paymentsMap->keys()->all(), $ordersMap->keys()->all())));
            @endphp
            @forelse($allUsers as $username)
                @php
                    $pay = $paymentsMap->get($username);
                    $ord = $ordersMap->get($username);
                @endphp
                <tr>
                    <td>{{ $username }}</td>
                    <td class="right">{{ $pay->payments_count ?? 0 }}</td>
                    <td class="right">{{ number_format((float) ($pay->payments_amount ?? 0), 2, '.', ' ') }}</td>
                    <td class="right">{{ $ord->orders_count ?? 0 }}</td>
                    <td class="right">{{ number_format((float) ($ord->orders_amount ?? 0), 2, '.', ' ') }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="muted">Aucune donnee</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Stock: resume et ventilations</h2>
    <table class="kpi-grid">
        <tr><td>Entrees qte</td><td>{{ number_format($stockInQty, 2, '.', ' ') }}</td></tr>
        <tr><td>Sorties qte</td><td>{{ number_format($stockOutQty, 2, '.', ' ') }}</td></tr>
        <tr><td>Entrees montant</td><td>{{ number_format($stockInAmount, 2, '.', ' ') }}</td></tr>
        <tr><td>Sorties montant</td><td>{{ number_format($stockOutAmount, 2, '.', ' ') }}</td></tr>
        <tr><td>Delta qte</td><td>{{ number_format($stockInQty - $stockOutQty, 2, '.', ' ') }}</td></tr>
        <tr><td>Delta montant</td><td>{{ number_format($stockInAmount - $stockOutAmount, 2, '.', ' ') }}</td></tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>Service</th>
                <th class="right">Entrees</th>
                <th class="right">Sorties</th>
                <th class="right">Sorties valorisees</th>
            </tr>
        </thead>
        <tbody>
            @forelse($stockByService as $row)
                <tr>
                    <td>{{ $row->service_name }}</td>
                    <td class="right">{{ number_format((float) $row->in_qty, 2, '.', ' ') }}</td>
                    <td class="right">{{ number_format((float) $row->out_qty, 2, '.', ' ') }}</td>
                    <td class="right">{{ number_format((float) $row->out_amount, 2, '.', ' ') }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="muted">Aucune donnee</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Top articles consommes</h2>
    <table>
        <thead>
            <tr>
                <th>Article</th>
                <th class="right">Quantite sortie</th>
                <th class="right">Montant sortie</th>
            </tr>
        </thead>
        <tbody>
            @forelse($topProductsOut as $row)
                <tr>
                    <td>{{ $row->product_name }}</td>
                    <td class="right">{{ number_format((float) $row->out_qty, 2, '.', ' ') }}</td>
                    <td class="right">{{ number_format((float) $row->out_amount, 2, '.', ' ') }}</td>
                </tr>
            @empty
                <tr><td colspan="3" class="muted">Aucune donnee</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
