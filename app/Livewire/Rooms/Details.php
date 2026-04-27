<?php

namespace App\Livewire\Rooms;

use App\Enums\ReservationStatus;
use App\Enums\RoomStatus;
use App\Enums\StayStatus;
use App\Models\AuditLog;
use App\Models\Benefit;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\Stay;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Billing\InvoiceService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;
use RuntimeException;

class Details extends Component
{
    public int $roomId;
    public bool $editing = false;

    // ── Champs principaux ──────────────────────────────────────
    public ?int $edit_room_type_id = null;
    public string $edit_number = '';
    public ?string $edit_floor = null;
    public int $edit_capacity = 1;
    public string $edit_price = '0';
    public string $edit_status = '';

    // ── Champs descriptifs ─────────────────────────────────────
    public string $edit_description = '';
    public string $edit_surface_m2 = '';
    public string $edit_bed_type = '';
    public string $edit_view_type = '';
    public bool $edit_smoking = false;
    public bool $edit_has_private_bathroom = true;
    public bool $edit_has_air_conditioning = true;
    public bool $edit_has_wifi = true;
    public bool $edit_has_tv = true;
    public bool $edit_has_balcony = false;
    public bool $edit_has_kitchenette = false;
    public bool $edit_has_safe = false;
    public bool $edit_has_minibar = false;
    public bool $edit_extra_bed_available = false;
    public string $edit_internal_notes = '';

    // ── Prestations ────────────────────────────────────────────
    /** @var array<int, array{id: int, name: string, icon: string|null, quantity_per_stay: int}> */
    public array $selectedBenefits = [];  // ex: [{benefit_id: 1, quantity_per_stay: 1}, ...]
    public ?int $addBenefitId = null;
    public int $addBenefitQty = 1;

    public function mount(int $roomId): void
    {
        $this->roomId = $roomId;
    }

    public function startEdit(): void
    {
        $this->ensureCanManageRooms();

        $room = Room::query()->findOrFail($this->roomId);

        $this->edit_room_type_id = $room->room_type_id;
        $this->edit_number       = (string) $room->number;
        $this->edit_floor        = $room->floor;
        $this->edit_capacity     = (int) $room->capacity;
        $this->edit_price        = number_format((float) $room->price, 2, '.', '');
        $this->edit_status       = $room->status->value;

        // Champs descriptifs
        $this->edit_description          = (string) ($room->description ?? '');
        $this->edit_surface_m2           = $room->surface_m2 !== null ? (string) $room->surface_m2 : '';
        $this->edit_bed_type             = (string) ($room->bed_type ?? '');
        $this->edit_view_type            = (string) ($room->view_type ?? '');
        $this->edit_smoking              = (bool) $room->smoking;
        $this->edit_has_private_bathroom = (bool) ($room->has_private_bathroom ?? true);
        $this->edit_has_air_conditioning = (bool) ($room->has_air_conditioning ?? true);
        $this->edit_has_wifi             = (bool) ($room->has_wifi ?? true);
        $this->edit_has_tv               = (bool) ($room->has_tv ?? true);
        $this->edit_has_balcony          = (bool) ($room->has_balcony ?? false);
        $this->edit_has_kitchenette      = (bool) ($room->has_kitchenette ?? false);
        $this->edit_has_safe             = (bool) ($room->has_safe ?? false);
        $this->edit_has_minibar          = (bool) ($room->has_minibar ?? false);
        $this->edit_extra_bed_available  = (bool) ($room->extra_bed_available ?? false);
        $this->edit_internal_notes       = (string) ($room->internal_notes ?? '');

        // Prestations courantes
        $this->selectedBenefits = $room->benefits()
            ->get()
            ->map(fn ($b) => [
                'benefit_id'       => $b->id,
                'name'             => $b->name,
                'icon'             => $b->icon,
                'quantity_per_stay' => (int) $b->pivot->quantity_per_stay,
            ])
            ->values()
            ->toArray();

        $this->addBenefitId  = null;
        $this->addBenefitQty = 1;
        $this->editing           = true;

        $this->resetErrorBag();
    }

    public function cancelEdit(): void
    {
        $this->editing = false;
        $this->resetErrorBag();
    }

    public function addBenefit(): void
    {
        if (! $this->addBenefitId) {
            return;
        }
        $alreadyAdded = collect($this->selectedBenefits)->pluck('benefit_id')->contains($this->addBenefitId);
        if ($alreadyAdded) {
            $this->addBenefitId  = null;
            $this->addBenefitQty = 1;
            return;
        }
        $benefit = Benefit::find($this->addBenefitId);
        if (! $benefit) {
            return;
        }
        $this->selectedBenefits[] = [
            'benefit_id'        => $benefit->id,
            'name'              => $benefit->name,
            'icon'              => $benefit->icon,
            'quantity_per_stay' => max(1, (int) $this->addBenefitQty),
        ];
        $this->addBenefitId  = null;
        $this->addBenefitQty = 1;
    }

    public function removeBenefit(int $index): void
    {
        array_splice($this->selectedBenefits, $index, 1);
    }

    public function save(): void
    {
        $this->ensureCanManageRooms();

        $statuses = array_column(RoomStatus::cases(), 'value');

        $validated = $this->validate([
            'edit_room_type_id'          => ['required', 'exists:room_types,id'],
            'edit_number'                => ['required', 'string', 'max:50', Rule::unique('rooms', 'number')->ignore($this->roomId)],
            'edit_floor'                 => ['nullable', 'string', 'max:50'],
            'edit_capacity'              => ['required', 'integer', 'min:1', 'max:50'],
            'edit_price'                 => ['required', 'numeric', 'min:0'],
            'edit_status'                => ['required', Rule::in($statuses)],
            'edit_description'           => ['nullable', 'string', 'max:2000'],
            'edit_surface_m2'            => ['nullable', 'numeric', 'min:1', 'max:9999'],
            'edit_bed_type'              => ['nullable', 'string', 'max:30'],
            'edit_view_type'             => ['nullable', 'string', 'max:30'],
            'edit_smoking'               => ['boolean'],
            'edit_has_private_bathroom'  => ['boolean'],
            'edit_has_air_conditioning'  => ['boolean'],
            'edit_has_wifi'              => ['boolean'],
            'edit_has_tv'                => ['boolean'],
            'edit_has_balcony'           => ['boolean'],
            'edit_has_kitchenette'       => ['boolean'],
            'edit_has_safe'              => ['boolean'],
            'edit_has_minibar'           => ['boolean'],
            'edit_extra_bed_available'   => ['boolean'],
            'edit_internal_notes'        => ['nullable', 'string', 'max:5000'],
            'selectedBenefits'           => ['array'],
            'selectedBenefits.*.benefit_id'        => ['required', 'exists:benefits,id'],
            'selectedBenefits.*.quantity_per_stay' => ['required', 'integer', 'min:1'],
        ]);

        try {
            DB::transaction(function () use ($validated): void {
                $room = Room::query()->findOrFail($this->roomId);

                $oldValues = [
                    'room_type_id' => (int) $room->room_type_id,
                    'number'       => (string) $room->number,
                    'floor'        => $room->floor,
                    'capacity'     => (int) $room->capacity,
                    'price'        => number_format((float) $room->price, 2, '.', ''),
                    'status'       => $room->status->value,
                ];

                $newValues = [
                    'room_type_id' => (int) $validated['edit_room_type_id'],
                    'number'       => trim((string) $validated['edit_number']),
                    'floor'        => $validated['edit_floor'] !== null && trim((string) $validated['edit_floor']) !== ''
                        ? trim((string) $validated['edit_floor'])
                        : null,
                    'capacity'     => (int) $validated['edit_capacity'],
                    'price'        => number_format((float) $validated['edit_price'], 2, '.', ''),
                    'status'       => (string) $validated['edit_status'],
                ];

                if ($oldValues === $newValues) {
                    $this->editing = false;
                    return;
                }

                $room->update([
                    'room_type_id'          => $newValues['room_type_id'],
                    'number'                => $newValues['number'],
                    'floor'                 => $newValues['floor'],
                    'capacity'              => $newValues['capacity'],
                    'price'                 => $newValues['price'],
                    'status'                => $newValues['status'],
                    'description'           => $validated['edit_description'] ?: null,
                    'surface_m2'            => $validated['edit_surface_m2'] !== null && $validated['edit_surface_m2'] !== '' ? (float) $validated['edit_surface_m2'] : null,
                    'bed_type'              => $validated['edit_bed_type'] ?: null,
                    'view_type'             => $validated['edit_view_type'] ?: null,
                    'smoking'               => (bool) $validated['edit_smoking'],
                    'has_private_bathroom'  => (bool) $validated['edit_has_private_bathroom'],
                    'has_air_conditioning'  => (bool) $validated['edit_has_air_conditioning'],
                    'has_wifi'              => (bool) $validated['edit_has_wifi'],
                    'has_tv'                => (bool) $validated['edit_has_tv'],
                    'has_balcony'           => (bool) $validated['edit_has_balcony'],
                    'has_kitchenette'       => (bool) $validated['edit_has_kitchenette'],
                    'has_safe'              => (bool) $validated['edit_has_safe'],
                    'has_minibar'           => (bool) $validated['edit_has_minibar'],
                    'extra_bed_available'   => (bool) $validated['edit_extra_bed_available'],
                    'internal_notes'        => $validated['edit_internal_notes'] ?: null,
                ]);

                // Sync prestations
                $syncData = collect($validated['selectedBenefits'])
                    ->mapWithKeys(fn ($item) => [
                        (int) $item['benefit_id'] => ['quantity_per_stay' => (int) $item['quantity_per_stay']],
                    ])
                    ->all();
                $room->benefits()->sync($syncData);

                $sideEffects = $oldValues['status'] !== $newValues['status']
                    && $newValues['status'] === RoomStatus::Available->value
                    ? $this->closeActiveOccupancyForAvailableRoom($room)
                    : [];

                app(AuditLogger::class)->log(
                    action: 'room_updated',
                    entityType: 'room',
                    entityId: $room->id,
                    oldValues: $oldValues,
                    newValues: array_merge($newValues, [
                        'source'       => 'edit_form',
                        'side_effects' => $sideEffects,
                    ]),
                );
            });
        } catch (RuntimeException $exception) {
            $this->addError('edit_status', $exception->getMessage());
            return;
        }

        $this->editing = false;
    }

    public function render()
    {
        $room = Room::query()
            ->with(['roomType', 'activeStay.customer', 'activeStay.reservation', 'benefits'])
            ->withCount(['stays', 'reservations'])
            ->withSum('invoices as open_invoice_balance', 'balance')
            ->withSum('orders as orders_total', 'total')
            ->findOrFail($this->roomId);

        $roomTypes = RoomType::query()->orderBy('name')->get();
        $allBenefits = Benefit::query()->where('is_active', true)->orderBy('name')->get();

        $occupancyHistory = Stay::query()
            ->with(['customer', 'reservation'])
            ->where('room_id', $room->id)
            ->latest('check_in_at')
            ->limit(20)
            ->get();

        $reservationHistory = Reservation::query()
            ->with('customer')
            ->where('room_id', $room->id)
            ->latest('check_in_date')
            ->limit(20)
            ->get();

        $recentInvoices = Invoice::query()
            ->with('customer')
            ->where('room_id', $room->id)
            ->latest('id')
            ->limit(10)
            ->get();

        $recentOrders = Order::query()
            ->with(['customer', 'server'])
            ->where('room_id', $room->id)
            ->latest('id')
            ->limit(10)
            ->get();

        $auditHistory = AuditLog::query()
            ->with('user')
            ->where('entity_type', 'room')
            ->where('entity_id', $room->id)
            ->latest()
            ->limit(25)
            ->get();

        $roomTypesLookup = $roomTypes->pluck('name', 'id')->all();

        $formattedAuditHistory = $auditHistory->map(function (AuditLog $entry) use ($roomTypesLookup) {
            $oldValues   = is_array($entry->old_values) ? $entry->old_values : [];
            $newValues   = is_array($entry->new_values) ? $entry->new_values : [];
            $sideEffects = is_array($newValues['side_effects'] ?? null) ? $newValues['side_effects'] : [];

            $changes = [];
            foreach (array_unique(array_merge(array_keys($oldValues), array_keys($newValues))) as $key) {
                if (in_array($key, ['source', 'side_effects'], true)) {
                    continue;
                }
                $old = $oldValues[$key] ?? null;
                $new = $newValues[$key] ?? null;
                if ((string) $old === (string) $new) {
                    continue;
                }
                if ($key === 'status') {
                    $old = $this->statusLabel($old);
                    $new = $this->statusLabel($new);
                }
                if ($key === 'room_type_id') {
                    $old = $old ? ($roomTypesLookup[(int) $old] ?? '#'.$old) : null;
                    $new = $new ? ($roomTypesLookup[(int) $new] ?? '#'.$new) : null;
                }
                $changes[] = ['key' => $key, 'old' => $old, 'new' => $new];
            }

            return [
                'id'           => $entry->id,
                'action'       => $entry->action,
                'created_at'   => $entry->created_at,
                'user_name'    => $entry->user?->name ?? 'Système',
                'changes'      => $changes,
                'side_effects' => $sideEffects,
            ];
        });

        $stats = [
            'stays_count'          => (int) ($room->stays_count ?? 0),
            'reservations_count'   => (int) ($room->reservations_count ?? 0),
            'open_invoice_balance' => (float) ($room->open_invoice_balance ?? 0),
            'orders_total'         => (float) ($room->orders_total ?? 0),
        ];

        $currency       = strtoupper((string) config('currency.default', 'USD'));
        $currencySymbol = Arr::get(config('currency.symbols', []), $currency, $currency);

        return view('livewire.rooms.details', [
            'room'               => $room,
            'roomTypes'          => $roomTypes,
            'allBenefits'        => $allBenefits,
            'statuses'           => array_column(RoomStatus::cases(), 'value'),
            'stats'              => $stats,
            'occupancyHistory'   => $occupancyHistory,
            'reservationHistory' => $reservationHistory,
            'recentInvoices'     => $recentInvoices,
            'recentOrders'       => $recentOrders,
            'auditHistory'       => $formattedAuditHistory,
            'currency'           => $currency,
            'currencySymbol'     => $currencySymbol,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // Private helpers (mirrors Board logic)
    // ─────────────────────────────────────────────────────────────

    /**
     * @return array{stay_closed_id:int|null,reservation_closed_id:int|null}
     */
    private function closeActiveOccupancyForAvailableRoom(Room $room): array
    {
        $sideEffects = ['stay_closed_id' => null, 'reservation_closed_id' => null];

        $activeStay = Stay::query()
            ->where('room_id', $room->id)
            ->where('status', StayStatus::Active->value)
            ->latest('id')
            ->first();

        if ($activeStay) {
            $invoice = app(InvoiceService::class)->openFolderForStay($activeStay, [
                'customer_id' => $activeStay->customer_id,
                'room_id'     => $activeStay->room_id,
                'issued_by'   => Auth::id(),
            ]);

            if ((float) $invoice->balance > 0) {
                throw new RuntimeException(
                    'Libération bloquée : facture '.$invoice->reference.' avec solde restant '.number_format((float) $invoice->balance, 2, '.', ' ').'.'
                );
            }

            $activeStay->update(['status' => StayStatus::CheckedOut->value, 'check_out_at' => now()]);
            $sideEffects['stay_closed_id'] = $activeStay->id;

            if ($activeStay->reservation) {
                $activeStay->reservation->update(['status' => ReservationStatus::CheckedOut->value]);
                $sideEffects['reservation_closed_id'] = $activeStay->reservation->id;
            }

            return $sideEffects;
        }

        $checkedInReservation = Reservation::query()
            ->where('room_id', $room->id)
            ->where('status', ReservationStatus::CheckedIn->value)
            ->latest('id')
            ->first();

        if ($checkedInReservation) {
            $checkedInReservation->update(['status' => ReservationStatus::CheckedOut->value]);
            $sideEffects['reservation_closed_id'] = $checkedInReservation->id;
        }

        return $sideEffects;
    }

    private function ensureCanManageRooms(): void
    {
        $user = Auth::user();
        abort_unless($user instanceof User, 403);

        /** @var User $user */
        if (! $user->roles()->exists()) {
            return;
        }

        $canManage = $user->roles()
            ->where(function ($q): void {
                $q->where('name', 'admin')
                    ->orWhereHas('permissions', fn ($p) => $p->where('name', 'rooms.manage'));
            })
            ->exists();

        abort_unless($canManage, 403);
    }

    private function statusLabel(?string $status): string
    {
        return match ($status) {
            RoomStatus::Available->value   => 'Disponible',
            RoomStatus::Occupied->value    => 'Occupée',
            RoomStatus::Cleaning->value    => 'Nettoyage',
            RoomStatus::Maintenance->value => 'Maintenance',
            default                        => (string) $status,
        };
    }
}
