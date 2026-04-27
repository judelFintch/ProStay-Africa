<?php

namespace App\Exports;

use App\Models\StockMovement;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StockMovementsExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(
        private readonly string $search,
        private readonly string $movementFilter,
        private readonly string $serviceFilter,
        private readonly string $startDate,
        private readonly string $endDate,
        private readonly string $sortField,
        private readonly string $sortDirection,
    ) {
    }

    public function collection(): Collection
    {
        return $this->buildQuery()->get();
    }

    public function headings(): array
    {
        return [
            'Date',
            'Produit',
            'SKU',
            'Service',
            'Type',
            'Quantite',
            'Unite',
            'Cout unitaire',
            'Montant',
            'Motif',
            'Utilisateur',
        ];
    }

    /**
     * @param StockMovement $movement
     */
    public function map($movement): array
    {
        $amount = (float) $movement->quantity * (float) $movement->unit_cost;

        return [
            $movement->created_at?->format('Y-m-d H:i:s'),
            $movement->product?->name,
            $movement->product?->sku,
            $movement->serviceArea?->name,
            $movement->movement_type === 'in' ? 'Entree' : 'Sortie',
            number_format((float) $movement->quantity, 2, '.', ' '),
            $movement->product?->unit,
            number_format((float) $movement->unit_cost, 2, '.', ' '),
            number_format($amount, 2, '.', ' '),
            $movement->reason,
            $movement->user?->name,
        ];
    }

    private function buildQuery(): Builder
    {
        $sortField = in_array($this->sortField, ['created_at', 'movement_type', 'quantity', 'unit_cost'], true)
            ? $this->sortField
            : 'created_at';
        $sortDirection = $this->sortDirection === 'asc' ? 'asc' : 'desc';

        return StockMovement::query()
            ->with(['product', 'user', 'serviceArea'])
            ->when($this->search !== '', function (Builder $query): void {
                $search = $this->search;
                $query->where(function (Builder $movementQuery) use ($search): void {
                    $movementQuery->where('movement_type', 'like', '%'.$search.'%')
                        ->orWhere('reason', 'like', '%'.$search.'%')
                        ->orWhereHas('product', function (Builder $productQuery) use ($search): void {
                            $productQuery->where('name', 'like', '%'.$search.'%')
                                ->orWhere('sku', 'like', '%'.$search.'%');
                        })
                        ->orWhereHas('user', function (Builder $userQuery) use ($search): void {
                            $userQuery->where('name', 'like', '%'.$search.'%');
                        })
                        ->orWhereHas('serviceArea', function (Builder $serviceAreaQuery) use ($search): void {
                            $serviceAreaQuery->where('name', 'like', '%'.$search.'%')
                                ->orWhere('code', 'like', '%'.$search.'%');
                        });
                });
            })
            ->when(in_array($this->movementFilter, ['in', 'out'], true), function (Builder $query): void {
                $query->where('movement_type', $this->movementFilter);
            })
            ->when($this->serviceFilter !== '', function (Builder $query): void {
                $query->where('service_area_id', (int) $this->serviceFilter);
            })
            ->when($this->startDate !== '', function (Builder $query): void {
                $query->whereDate('created_at', '>=', $this->startDate);
            })
            ->when($this->endDate !== '', function (Builder $query): void {
                $query->whereDate('created_at', '<=', $this->endDate);
            })
            ->orderBy($sortField, $sortDirection)
            ->orderBy('id', 'desc');
    }
}
