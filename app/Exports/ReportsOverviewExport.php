<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ReportsOverviewExport implements FromArray, ShouldAutoSize
{
    /**
     * @param array<string, mixed> $reportData
     */
    public function __construct(private readonly array $reportData)
    {
    }

    public function array(): array
    {
        $rows = [];

        $rows[] = ['ProStay Africa - Rapport global'];
        $rows[] = ['Periode', ($this->reportData['startDate'] ?? '').' -> '.($this->reportData['endDate'] ?? '')];
        $rows[] = ['Devise de reference', (string) ($this->reportData['reportCurrency'] ?? 'N/A')];
        $rows[] = [];

        $rows[] = ['KPI globaux', 'Valeur'];
        $rows[] = ['Taux occupation (%)', $this->number($this->reportData['occupancy'] ?? 0)];
        $rows[] = ['Chambres occupees', $this->number($this->reportData['activeStays'] ?? 0).' / '.$this->number($this->reportData['totalRooms'] ?? 0)];
        $rows[] = ['CA commandes', $this->money($this->reportData['salesOrderAmount'] ?? 0)];
        $rows[] = ['Nombre commandes', $this->number($this->reportData['salesOrderCount'] ?? 0)];
        $rows[] = ['Paiements encaisses', $this->money($this->reportData['paymentsTotal'] ?? 0)];
        $rows[] = ['Ticket moyen', $this->money($this->reportData['avgTicket'] ?? 0)];
        $rows[] = ['CA du jour', $this->money($this->reportData['todayRevenue'] ?? 0)];
        $rows[] = ['Factures ouvertes', $this->number($this->reportData['openInvoices'] ?? 0)];
        $rows[] = ['Commandes du jour', $this->number($this->reportData['ordersToday'] ?? 0)];
        $rows[] = ['Restaurant externe encaisse', $this->money($this->reportData['restaurantExternalRevenue'] ?? 0)];
        $rows[] = ['Restaurant transfere hotel', $this->money($this->reportData['restaurantHotelTransferBalance'] ?? 0)];
        $rows[] = [];

        $rows[] = ['Ventes par service'];
        $rows[] = ['Service', 'Commandes', 'Montant'];
        $rows = array_merge($rows, $this->tableRows($this->reportData['salesByService'] ?? collect(), static function ($row): array {
            return [
                (string) ($row->service_name ?? 'N/A'),
                $row->orders_count ?? 0,
                number_format((float) ($row->orders_amount ?? 0), 2, '.', ' '),
            ];
        }));
        $rows[] = [];

        $rows[] = ['Paiements par utilisateur'];
        $rows[] = ['Utilisateur', 'Paiements', 'Montant'];
        $rows = array_merge($rows, $this->tableRows($this->reportData['paymentsByUser'] ?? collect(), static function ($row): array {
            return [
                (string) ($row->user_name ?? 'N/A'),
                $row->payments_count ?? 0,
                number_format((float) ($row->payments_amount ?? 0), 2, '.', ' '),
            ];
        }));
        $rows[] = [];

        $rows[] = ['Commandes par utilisateur'];
        $rows[] = ['Utilisateur', 'Commandes', 'Montant'];
        $rows = array_merge($rows, $this->tableRows($this->reportData['ordersByUser'] ?? collect(), static function ($row): array {
            return [
                (string) ($row->user_name ?? 'N/A'),
                $row->orders_count ?? 0,
                number_format((float) ($row->orders_amount ?? 0), 2, '.', ' '),
            ];
        }));
        $rows[] = [];

        $rows[] = ['Charge service (commandes)'];
        $rows[] = ['Service', 'Commandes'];
        $rows = array_merge($rows, $this->tableRows($this->reportData['serviceAreaLoad'] ?? collect(), static function ($row): array {
            return [
                (string) ($row->name ?? 'N/A'),
                $row->period_orders_count ?? 0,
            ];
        }));
        $rows[] = [];

        $rows[] = ['Stock resume', 'Valeur'];
        $rows[] = ['Entrees qte', $this->number($this->reportData['stockInQty'] ?? 0)];
        $rows[] = ['Entrees montant', $this->money($this->reportData['stockInAmount'] ?? 0)];
        $rows[] = ['Sorties qte', $this->number($this->reportData['stockOutQty'] ?? 0)];
        $rows[] = ['Sorties montant', $this->money($this->reportData['stockOutAmount'] ?? 0)];
        $rows[] = ['Delta qte', $this->number(($this->reportData['stockInQty'] ?? 0) - ($this->reportData['stockOutQty'] ?? 0))];
        $rows[] = ['Delta montant', $this->money(($this->reportData['stockInAmount'] ?? 0) - ($this->reportData['stockOutAmount'] ?? 0))];
        $rows[] = [];

        $rows[] = ['Stock par service'];
        $rows[] = ['Service', 'Entrees', 'Sorties', 'Sorties valorisees'];
        $rows = array_merge($rows, $this->tableRows($this->reportData['stockByService'] ?? collect(), static function ($row): array {
            return [
                (string) ($row->service_name ?? 'N/A'),
                number_format((float) ($row->in_qty ?? 0), 2, '.', ' '),
                number_format((float) ($row->out_qty ?? 0), 2, '.', ' '),
                number_format((float) ($row->out_amount ?? 0), 2, '.', ' '),
            ];
        }));
        $rows[] = [];

        $rows[] = ['Stock par utilisateur'];
        $rows[] = ['Utilisateur', 'Mouvements', 'Entrees', 'Sorties'];
        $rows = array_merge($rows, $this->tableRows($this->reportData['stockByUser'] ?? collect(), static function ($row): array {
            return [
                (string) ($row->user_name ?? 'N/A'),
                $row->movement_count ?? 0,
                number_format((float) ($row->in_qty ?? 0), 2, '.', ' '),
                number_format((float) ($row->out_qty ?? 0), 2, '.', ' '),
            ];
        }));
        $rows[] = [];

        $rows[] = ['Top articles consommes (sorties stock)'];
        $rows[] = ['Article', 'Quantite sortie', 'Montant sortie'];
        $rows = array_merge($rows, $this->tableRows($this->reportData['topProductsOut'] ?? collect(), static function ($row): array {
            return [
                (string) ($row->product_name ?? 'N/A'),
                number_format((float) ($row->out_qty ?? 0), 2, '.', ' '),
                number_format((float) ($row->out_amount ?? 0), 2, '.', ' '),
            ];
        }));

        return $rows;
    }

    /**
     * @param iterable<mixed> $dataset
     * @param callable(mixed): array<int, string|int|float> $mapper
     * @return array<int, array<int, string|int|float>>
     */
    private function tableRows(iterable $dataset, callable $mapper): array
    {
        $rows = [];
        $count = 0;

        foreach ($dataset as $item) {
            $rows[] = $mapper($item);
            $count++;
        }

        if ($count === 0) {
            $rows[] = ['Aucune donnee'];
        }

        return $rows;
    }

    private function money(mixed $value): string
    {
        return number_format((float) $value, 2, '.', ' ');
    }

    private function number(mixed $value): string
    {
        return number_format((float) $value, 2, '.', ' ');
    }
}
