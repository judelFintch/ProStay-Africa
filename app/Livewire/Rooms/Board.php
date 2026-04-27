<?php

namespace App\Livewire\Rooms;

use App\Enums\ReservationStatus;
use App\Enums\RoomStatus;
use App\Enums\StayStatus;
use App\Models\AuditLog;
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

class Board extends Component
{
    public string $filter = 'all';
    public string $search = '';
    public ?int $expandedRoomId = null;
    public ?int $historyRoomId = null;

    public ?int $editingRoomId = null;
    public ?int $edit_room_type_id = null;
    public string $edit_number = '';
    public ?string $edit_floor = null;
    public int $edit_capacity = 1;
    public string $edit_price = '0';
    public string $edit_status = RoomStatus::Available->value;

    public function setStatus(int $roomId, string $status): void
    {
        $this->ensureCanManageRooms();

        $allowed = $this->validRoomStatuses();
        if (! in_array($status, $allowed, true)) {
            return;
        }

        try {
            DB::transaction(function () use ($roomId, $status): void {
                $room = Room::query()->findOrFail($roomId);
                $oldStatus = $room->status->value;

                if ($oldStatus === $status) {
                    return;
                }

                $room->update([
                    'status' => $status,
                ]);

                $sideEffects = $status === RoomStatus::Available->value
                    ? $this->closeActiveOccupancyForAvailableRoom($room)
                    : [];

                app(AuditLogger::class)->log(
                    action: 'room_status_updated',
                    entityType: 'room',
                    entityId: $room->id,
                    oldValues: [
                        'status' => $oldStatus,
                    ],
                    newValues: [
                        'status' => $status,
                        'source' => 'quick_action',
                        'side_effects' => $sideEffects,
                    ],
                );
            });
        } catch (RuntimeException $exception) {
            $this->addError('room_action', $exception->getMessage());

            return;
        }

        if ($this->editingRoomId === $roomId) {
            $this->edit_status = $status;
        }
        $this->historyRoomId = $roomId;
    }

    public function setFilter(string $status): void
    {
        $allowedFilters = [
            'all',
            ...$this->validRoomStatuses(),
        ];

        $this->filter = in_array($status, $allowedFilters, true) ? $status : 'all';
    }

    public function resetFilters(): void
    {
        $this->filter = 'all';
        $this->search = '';
    }

    public function startEditRoom(int $roomId): void
    {
        $this->ensureCanManageRooms();

        $room = Room::query()->findOrFail($roomId);

        $this->editingRoomId = $room->id;
        $this->edit_room_type_id = $room->room_type_id;
        $this->edit_number = (string) $room->number;
        $this->edit_floor = $room->floor;
        $this->edit_capacity = (int) $room->capacity;
        $this->edit_price = number_format((float) $room->price, 2, '.', '');
        $this->edit_status = $room->status->value;
        $this->historyRoomId = $room->id;

        $this->resetErrorBag();
    }

    public function cancelEditRoom(): void
    {
        $this->ensureCanManageRooms();

        $this->editingRoomId = null;
        $this->edit_room_type_id = null;
        $this->edit_number = '';
        $this->edit_floor = null;
        $this->edit_capacity = 1;
        $this->edit_price = '0';
        $this->edit_status = RoomStatus::Available->value;

        $this->resetErrorBag();
    }

    public function saveRoomChanges(): void
    {
        $this->ensureCanManageRooms();

        if (! $this->editingRoomId) {
            return;
        }

        $statuses = $this->validRoomStatuses();

        $validated = $this->validate([
            'edit_room_type_id' => ['required', 'exists:room_types,id'],
            'edit_number' => ['required', 'string', 'max:50', Rule::unique('rooms', 'number')->ignore($this->editingRoomId)],
            'edit_floor' => ['nullable', 'string', 'max:50'],
            'edit_capacity' => ['required', 'integer', 'min:1', 'max:50'],
            'edit_price' => ['required', 'numeric', 'min:0'],
            'edit_status' => ['required', Rule::in($statuses)],
        ]);

        try {
            DB::transaction(function () use ($validated): void {
                $room = Room::query()->findOrFail($this->editingRoomId);

                $oldValues = [
                    'room_type_id' => (int) $room->room_type_id,
                    'number' => (string) $room->number,
                    'floor' => $room->floor,
                    'capacity' => (int) $room->capacity,
                    'price' => number_format((float) $room->price, 2, '.', ''),
                    'status' => $room->status->value,
                ];

                $newValues = [
                    'room_type_id' => (int) $validated['edit_room_type_id'],
                    'number' => trim((string) $validated['edit_number']),
                    'floor' => $validated['edit_floor'] !== null && trim((string) $validated['edit_floor']) !== ''
                        ? trim((string) $validated['edit_floor'])
                        : null,
                    'capacity' => (int) $validated['edit_capacity'],
                    'price' => number_format((float) $validated['edit_price'], 2, '.', ''),
                    'status' => (string) $validated['edit_status'],
                ];

                if ($oldValues === $newValues) {
                    return;
                }

                $room->update([
                    'room_type_id' => $newValues['room_type_id'],
                    'number' => $newValues['number'],
                    'floor' => $newValues['floor'],
                    'capacity' => $newValues['capacity'],
                    'price' => $newValues['price'],
                    'status' => $newValues['status'],
                ]);

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
                        'source' => 'edit_form',
                        'side_effects' => $sideEffects,
                    ]),
                );
            });
        } catch (RuntimeException $exception) {
            $this->addError('edit_status', $exception->getMessage());

            return;
        }

        $this->historyRoomId = $this->editingRoomId;
        $this->cancelEditRoom();
    }

    public function toggleOccupationDetails(int $roomId): void
    {
        $this->expandedRoomId = $this->expandedRoomId === $roomId ? null : $roomId;
    }

    public function toggleHistory(int $roomId): void
    {
        $this->historyRoomId = $this->historyRoomId === $roomId ? null : $roomId;
    }

    public function render()
    {
        $statuses = $this->validRoomStatuses();

        $roomsQuery = Room::query()
            ->with([
                'roomType',
                'activeStay.customer',
                'activeStay.reservation',
            ])
            ->withCount('benefits')
            ->orderBy('number');

        $search = trim($this->search);
        if ($search !== '') {
            $roomsQuery->where(function ($query) use ($search): void {
                $query->where('number', 'like', '%'.$search.'%')
                    ->orWhere('floor', 'like', '%'.$search.'%')
                    ->orWhereHas('roomType', function ($roomTypeQuery) use ($search): void {
                        $roomTypeQuery->where('name', 'like', '%'.$search.'%');
                    });
            });
        }

        if ($this->filter !== 'all') {
            $roomsQuery->where('status', $this->filter);
        }

        $rooms = $roomsQuery->get();

        $roomMetrics = Room::query()
            ->selectRaw('COUNT(*) as total_rooms')
            ->selectRaw("SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as occupied_rooms", [RoomStatus::Occupied->value])
            ->selectRaw("SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as available_rooms", [RoomStatus::Available->value])
            ->selectRaw("SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as cleaning_rooms", [RoomStatus::Cleaning->value])
            ->first();

        $statusCounts = Room::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->map(fn ($value) => (int) $value)
            ->all();

        $totalRooms = (int) ($roomMetrics?->total_rooms ?? 0);
        $occupiedRooms = (int) ($roomMetrics?->occupied_rooms ?? 0);
        $availableRooms = (int) ($roomMetrics?->available_rooms ?? 0);
        $cleaningRooms = (int) ($roomMetrics?->cleaning_rooms ?? 0);

        $currency = strtoupper((string) config('currency.default', 'USD'));
        $currencySymbols = config('currency.symbols', []);
        $currencySymbol = Arr::get($currencySymbols, $currency, $currency);

        $roomTypesLookup = RoomType::query()->pluck('name', 'id')->all();

        $roomHistoryEntries = collect();
        $formattedRoomHistoryEntries = collect();
        if ($this->historyRoomId !== null) {
            $roomHistoryEntries = AuditLog::query()
                ->with('user')
                ->where('entity_type', 'room')
                ->where('entity_id', $this->historyRoomId)
                ->latest()
                ->limit(20)
                ->get();

            $formattedRoomHistoryEntries = $roomHistoryEntries->map(function (AuditLog $entry) use ($roomTypesLookup) {
                $oldValues = is_array($entry->old_values) ? $entry->old_values : [];
                $newValues = is_array($entry->new_values) ? $entry->new_values : [];
                $sideEffects = is_array($newValues['side_effects'] ?? null) ? $newValues['side_effects'] : [];

                $changeKeys = array_values(array_unique(array_merge(array_keys($oldValues), array_keys($newValues))));
                $changes = [];

                foreach ($changeKeys as $key) {
                    if (in_array($key, ['source', 'side_effects'], true)) {
                        continue;
                    }

                    $old = $oldValues[$key] ?? null;
                    $new = $newValues[$key] ?? null;

                    if ((string) $old === (string) $new) {
                        continue;
                    }

                    if ($key === 'status') {
                        $old = $this->roomStatusLabel($old);
                        $new = $this->roomStatusLabel($new);
                    }

                    if ($key === 'room_type_id') {
                        $old = $old ? ($roomTypesLookup[(int) $old] ?? '#'.$old) : null;
                        $new = $new ? ($roomTypesLookup[(int) $new] ?? '#'.$new) : null;
                    }

                    $changes[] = [
                        'key' => $key,
                        'old' => $old,
                        'new' => $new,
                    ];
                }

                return [
                    'id' => $entry->id,
                    'action' => $entry->action,
                    'created_at' => $entry->created_at,
                    'user_name' => $entry->user?->name ?? 'Systeme',
                    'changes' => $changes,
                    'side_effects' => $sideEffects,
                ];
            });
        }

        return view('livewire.rooms.board', [
            'rooms' => $rooms,
            'roomTypes' => RoomType::query()->orderBy('name')->get(),
            'statuses' => $statuses,
            'totalRooms' => $totalRooms,
            'occupiedRooms' => $occupiedRooms,
            'availableRooms' => $availableRooms,
            'cleaningRooms' => $cleaningRooms,
            'occupancyRate' => $totalRooms > 0 ? round(($occupiedRooms / $totalRooms) * 100, 1) : 0,
            'currency' => $currency,
            'currencySymbol' => $currencySymbol,
            'expandedRoomId' => $this->expandedRoomId,
            'roomHistoryEntries' => $formattedRoomHistoryEntries,
            'statusCounts' => $statusCounts,
        ]);
    }

    /**
     * @return list<string>
     */
    private function validRoomStatuses(): array
    {
        return array_column(RoomStatus::cases(), 'value');
    }

    /**
     * @return array{stay_closed_id:int|null,reservation_closed_id:int|null}
     */
    private function closeActiveOccupancyForAvailableRoom(Room $room): array
    {
        $sideEffects = [
            'stay_closed_id' => null,
            'reservation_closed_id' => null,
        ];

        $activeStay = Stay::query()
            ->where('room_id', $room->id)
            ->where('status', StayStatus::Active->value)
            ->latest('id')
            ->first();

        if ($activeStay) {
            $invoice = app(InvoiceService::class)->openFolderForStay($activeStay, [
                'customer_id' => $activeStay->customer_id,
                'room_id' => $activeStay->room_id,
                'issued_by' => Auth::id(),
            ]);

            if ((float) $invoice->balance > 0) {
                throw new RuntimeException(
                    'Liberation bloquee: facture '.$invoice->reference.' avec solde restant '.number_format((float) $invoice->balance, 2, '.', ' ').'.'
                );
            }

            $activeStay->update([
                'status' => StayStatus::CheckedOut->value,
                'check_out_at' => now(),
            ]);

            $sideEffects['stay_closed_id'] = $activeStay->id;

            if ($activeStay->reservation) {
                $activeStay->reservation->update([
                    'status' => ReservationStatus::CheckedOut->value,
                ]);

                $sideEffects['reservation_closed_id'] = $activeStay->reservation->id;
            }

            return $sideEffects;
        }

        $checkedInReservation = Reservation::query()
            ->where('room_id', $room->id)
            ->where('status', ReservationStatus::CheckedIn->value)
            ->latest('id')
            ->first();

        if (! $checkedInReservation) {
            return $sideEffects;
        }

        $checkedInReservation->update([
            'status' => ReservationStatus::CheckedOut->value,
        ]);

        $sideEffects['reservation_closed_id'] = $checkedInReservation->id;

        return $sideEffects;
    }

    private function ensureCanManageRooms(): void
    {
        $user = Auth::user();

        abort_unless($user instanceof User, 403);

        /** @var User $user */

        $hasRoles = $user->roles()->exists();
        if (! $hasRoles) {
            return;
        }

        $canManage = $user->roles()
            ->where(function ($query): void {
                $query->where('name', 'admin')
                    ->orWhereHas('permissions', function ($permissionQuery): void {
                        $permissionQuery->where('name', 'rooms.manage');
                    });
            })
            ->exists();

        abort_unless($canManage, 403);
    }

    private function roomStatusLabel(?string $status): string
    {
        return match ($status) {
            RoomStatus::Available->value => 'Disponible',
            RoomStatus::Occupied->value => 'Occupee',
            RoomStatus::Cleaning->value => 'Nettoyage',
            RoomStatus::Maintenance->value => 'Maintenance',
            default => (string) $status,
        };
    }
}
