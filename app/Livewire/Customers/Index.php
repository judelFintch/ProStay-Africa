<?php

namespace App\Livewire\Customers;

use App\Enums\ReservationStatus;
use App\Enums\RoomStatus;
use App\Enums\StayStatus;
use App\Models\Customer;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\Stay;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $mode = 'form';
    public string $search = '';
    public ?string $guest_code = null;
    public ?string $title = null;
    public string $full_name = '';
    public ?string $preferred_name = null;
    public ?string $gender = null;
    public ?string $date_of_birth = null;
    public ?string $place_of_birth = null;
    public ?string $nationality = null;
    public ?string $phone = null;
    public ?string $secondary_phone = null;
    public ?string $email = null;
    public ?string $profession = null;
    public ?string $company_name = null;
    public string $preferred_language = 'fr';
    public ?string $identity_document = null;
    public ?string $identity_document_type = null;
    public ?string $identity_document_issue_place = null;
    public ?string $identity_document_issued_at = null;
    public ?string $identity_document_expires_at = null;
    public ?string $country = null;
    public ?string $city = null;
    public ?string $address = null;
    public ?string $emergency_contact_name = null;
    public ?string $emergency_contact_phone = null;
    public ?string $emergency_contact_relationship = null;
    public ?string $guest_preferences = null;
    public ?string $internal_notes = null;
    public ?string $marketing_source = null;
    public bool $vip_status = false;
    public bool $blacklisted = false;
    public bool $is_identified = true;
    public ?string $travel_type = null;
    public bool $with_room_rental = true;
    public ?int $room_id = null;
    public string $check_in_date = '';
    public string $check_out_date = '';
    public int $adults = 1;
    public int $children = 0;
    public ?float $nightly_rate = null;
    public ?string $rental_notes = null;

    public string $step = 'form'; // 'form' | 'recap' | 'ticket'
    public ?array $createdData = null;

    public function mount(string $mode = 'form'): void
    {
        $this->mode = in_array($mode, ['form', 'registry'], true) ? $mode : 'form';
        $this->check_in_date = now()->toDateString();
        $this->check_out_date = now()->addDay()->toDateString();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    private function validationRules(): array
    {
        return [
            'guest_code' => ['nullable', 'string', 'max:50', 'unique:customers,guest_code'],
            'title' => ['nullable', 'string', 'max:20'],
            'full_name' => ['nullable', 'string', 'max:255'],
            'preferred_name' => ['nullable', 'string', 'max:255'],
            'gender' => ['nullable', 'in:male,female,other,not_specified'],
            'date_of_birth' => ['nullable', 'date', 'before_or_equal:today'],
            'place_of_birth' => ['nullable', 'string', 'max:255'],
            'nationality' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:50'],
            'secondary_phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'profession' => ['nullable', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'preferred_language' => ['required', 'in:fr,en,sw'],
            'identity_document_type' => ['nullable', 'string', 'max:50'],
            'identity_document' => ['nullable', 'string', 'max:100'],
            'identity_document_issue_place' => ['nullable', 'string', 'max:255'],
            'identity_document_issued_at' => ['nullable', 'date'],
            'identity_document_expires_at' => ['nullable', 'date', 'after_or_equal:identity_document_issued_at'],
            'country' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:1000'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:50'],
            'emergency_contact_relationship' => ['nullable', 'string', 'max:100'],
            'guest_preferences' => ['nullable', 'string', 'max:1000'],
            'internal_notes' => ['nullable', 'string', 'max:1500'],
            'marketing_source' => ['nullable', 'string', 'max:255'],
            'vip_status' => ['boolean'],
            'blacklisted' => ['boolean'],
            'is_identified' => ['boolean'],
            'travel_type' => ['nullable', 'in:solo,accompanied,couple,family,group'],
            'with_room_rental' => ['boolean'],
            'room_id' => ['required_if:with_room_rental,true', 'exists:rooms,id'],
            'check_in_date' => ['required_if:with_room_rental,true', 'date', 'after_or_equal:today'],
            'check_out_date' => ['required_if:with_room_rental,true', 'date', 'after:check_in_date'],
            'adults' => ['required_if:with_room_rental,true', 'integer', 'min:1'],
            'children' => ['required_if:with_room_rental,true', 'integer', 'min:0'],
            'nightly_rate' => ['nullable', 'numeric', 'min:0'],
            'rental_notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function goToRecap(): void
    {
        $this->validate($this->validationRules());

        if (! $this->full_name && ! $this->phone && ! $this->email) {
            $this->addError('full_name', 'Au moins un identifiant client est requis (nom, telephone ou email).');
            return;
        }

        $this->step = 'recap';
    }

    public function backToForm(): void
    {
        $this->step = 'form';
    }

    public function resetAll(): void
    {
        $this->createdData = null;
        $this->step = 'form';
    }

    public function confirmCreate(): void
    {
        if (! $this->full_name && ! $this->phone && ! $this->email) {
            $this->step = 'form';
            $this->addError('full_name', 'Au moins un identifiant client est requis.');
            return;
        }

        $ticketData = [];

        try {
            DB::transaction(function () use (&$ticketData): void {
                $customerCode = $this->guest_code ?: $this->generateGuestCode();

                $customer = Customer::query()->create([
                    'guest_code' => $customerCode,
                    'title' => $this->title,
                    'full_name' => $this->full_name ?: null,
                    'preferred_name' => $this->preferred_name,
                    'gender' => $this->gender,
                    'date_of_birth' => $this->date_of_birth ?: null,
                    'place_of_birth' => $this->place_of_birth,
                    'nationality' => $this->nationality,
                    'phone' => $this->phone,
                    'secondary_phone' => $this->secondary_phone,
                    'email' => $this->email,
                    'profession' => $this->profession,
                    'company_name' => $this->company_name,
                    'preferred_language' => $this->preferred_language,
                    'identity_document' => $this->identity_document,
                    'identity_document_type' => $this->identity_document_type,
                    'identity_document_issue_place' => $this->identity_document_issue_place,
                    'identity_document_issued_at' => $this->identity_document_issued_at ?: null,
                    'identity_document_expires_at' => $this->identity_document_expires_at ?: null,
                    'country' => $this->country,
                    'city' => $this->city,
                    'address' => $this->address,
                    'emergency_contact_name' => $this->emergency_contact_name,
                    'emergency_contact_phone' => $this->emergency_contact_phone,
                    'emergency_contact_relationship' => $this->emergency_contact_relationship,
                    'guest_preferences' => $this->guest_preferences,
                    'internal_notes' => $this->internal_notes,
                    'marketing_source' => $this->marketing_source,
                    'vip_status' => $this->vip_status,
                    'blacklisted' => $this->blacklisted,
                    'is_identified' => $this->is_identified,
                    'travel_type'   => $this->travel_type,
                ]);

                $ticketData = [
                    'guest_code'         => $customer->guest_code,
                    'title'              => $customer->title,
                    'full_name'          => $customer->full_name,
                    'preferred_name'     => $customer->preferred_name,
                    'nationality'        => $customer->nationality,
                    'phone'              => $customer->phone,
                    'email'              => $customer->email,
                    'company_name'       => $customer->company_name,
                    'profession'         => $customer->profession,
                    'country'            => $customer->country,
                    'city'               => $customer->city,
                    'is_identified'      => $customer->is_identified,
                    'travel_type'        => $customer->travel_type,
                    'vip_status'         => $customer->vip_status,
                    'blacklisted'        => $customer->blacklisted,
                    'preferred_language' => strtoupper($customer->preferred_language),
                    'created_at'         => now()->format('d/m/Y \à H:i'),
                    'with_room_rental'   => false,
                    'room_number'        => null,
                    'room_type'          => null,
                    'check_in_date'      => null,
                    'check_out_date'     => null,
                    'adults'             => null,
                    'children'           => null,
                    'nightly_rate'       => null,
                    'nights'             => null,
                    'total_estimate'     => null,
                    'rental_notes'       => null,
                ];

                if (! $this->with_room_rental) {
                    return;
                }

                $room = Room::query()->lockForUpdate()->findOrFail($this->room_id);
                $guestCount = $this->adults + $this->children;

                if ($guestCount > (int) $room->capacity) {
                    throw new \RuntimeException('La capacite de la chambre est insuffisante pour le nombre de voyageurs selectionne.');
                }

                if ($room->status === RoomStatus::Maintenance) {
                    throw new \RuntimeException('Cette chambre est actuellement en maintenance.');
                }

                if ($room->status === RoomStatus::Occupied) {
                    throw new \RuntimeException('Cette chambre est actuellement occupee. Veuillez en choisir une autre.');
                }

                $checkInDate = Carbon::parse($this->check_in_date)->startOfDay();
                $checkOutDate = Carbon::parse($this->check_out_date)->startOfDay();

                $hasReservationConflict = Reservation::query()
                    ->where('room_id', $room->id)
                    ->whereIn('status', [
                        ReservationStatus::Pending->value,
                        ReservationStatus::Confirmed->value,
                        ReservationStatus::CheckedIn->value,
                    ])
                    ->where(function ($query) use ($checkInDate, $checkOutDate) {
                        $query->where('check_in_date', '<', $checkOutDate->toDateString())
                            ->where('check_out_date', '>', $checkInDate->toDateString());
                    })
                    ->exists();

                $hasActiveStayConflict = Stay::query()
                    ->where('room_id', $room->id)
                    ->where('status', StayStatus::Active->value)
                    ->where(function ($query) use ($checkInDate) {
                        $query->whereNull('check_out_at')
                            ->orWhere('check_out_at', '>', $checkInDate);
                    })
                    ->exists();

                if ($hasReservationConflict || $hasActiveStayConflict) {
                    throw new \RuntimeException('Cette chambre est deja reservee ou occupee sur la periode demandee.');
                }

                $reservation = Reservation::query()->create([
                    'customer_id' => $customer->id,
                    'room_id' => $room->id,
                    'check_in_date' => $checkInDate->toDateString(),
                    'check_out_date' => $checkOutDate->toDateString(),
                    'adults' => $this->adults,
                    'children' => $this->children,
                    'status' => ReservationStatus::CheckedIn->value,
                    'notes' => $this->rental_notes,
                ]);

                Stay::query()->create([
                    'customer_id' => $customer->id,
                    'room_id' => $room->id,
                    'reservation_id' => $reservation->id,
                    'check_in_at' => now(),
                    'expected_check_out_at' => $checkOutDate->copy()->endOfDay(),
                    'status' => StayStatus::Active->value,
                    'nightly_rate' => $this->nightly_rate ?? (float) $room->price,
                    'notes' => $this->rental_notes ?: 'Created from customers module',
                ]);

                $finalRate              = $this->nightly_rate ?? (float) $room->price;
                $finalNights            = max(0, (int) $checkInDate->diffInDays($checkOutDate));

                $ticketData['with_room_rental'] = true;
                $ticketData['room_number']      = $room->number;
                $ticketData['room_type']        = $room->roomType?->name ?? 'Standard';
                $ticketData['check_in_date']    = $this->check_in_date;
                $ticketData['check_out_date']   = $this->check_out_date;
                $ticketData['adults']           = $this->adults;
                $ticketData['children']         = $this->children;
                $ticketData['nightly_rate']     = $finalRate;
                $ticketData['nights']           = $finalNights;
                $ticketData['total_estimate']   = $finalNights > 0 ? $finalNights * $finalRate : null;
                $ticketData['rental_notes']     = $this->rental_notes;

                $room->update([
                    'status' => RoomStatus::Occupied->value,
                ]);
            });
        } catch (\RuntimeException $exception) {
            $this->step = 'form';
            $this->addError('room_id', $exception->getMessage());
            return;
        }

        $this->createdData = $ticketData;

        $this->reset([
            'guest_code',
            'title',
            'full_name',
            'preferred_name',
            'gender',
            'date_of_birth',
            'place_of_birth',
            'nationality',
            'phone',
            'secondary_phone',
            'email',
            'profession',
            'company_name',
            'identity_document_type',
            'identity_document',
            'identity_document_issue_place',
            'identity_document_issued_at',
            'identity_document_expires_at',
            'country',
            'city',
            'address',
            'emergency_contact_name',
            'emergency_contact_phone',
            'emergency_contact_relationship',
            'guest_preferences',
            'internal_notes',
            'marketing_source',
            'room_id',
            'check_in_date',
            'check_out_date',
            'rental_notes',
        ]);
        $this->is_identified = true;
        $this->travel_type = null;
        $this->preferred_language = 'fr';
        $this->vip_status = false;
        $this->blacklisted = false;
        $this->with_room_rental = true;
        $this->adults = 1;
        $this->children = 0;
        $this->nightly_rate = null;
        $this->check_in_date = now()->toDateString();
        $this->check_out_date = now()->addDay()->toDateString();
        $this->step = 'ticket';
        $this->dispatch('customer-created');
    }

    public function updatedRoomId(): void
    {
        if ($this->room_id) {
            $room = Room::find($this->room_id);
            if ($room && ! $this->nightly_rate) {
                $this->nightly_rate = (float) $room->price;
            }
        }
    }

    public function render()
    {
        // Calcul du nombre de nuits et du total estimé
        $nights = 0;
        $estimatedTotal = null;
        if ($this->check_in_date && $this->check_out_date) {
            try {
                $cin  = Carbon::parse($this->check_in_date)->startOfDay();
                $cout = Carbon::parse($this->check_out_date)->startOfDay();
                $nights = max(0, (int) $cin->diffInDays($cout));
                $rate = $this->nightly_rate;
                if ($rate && $nights > 0) {
                    $estimatedTotal = $nights * $rate;
                }
            } catch (\Exception) {
                // dates invalides
            }
        }

        $customers = Customer::query()
            ->withCount(['reservations', 'stays', 'orders', 'invoices'])
            ->when($this->search, function ($query) {
                $query->where(function ($customerQuery) {
                    $customerQuery->where('guest_code', 'like', '%' . $this->search . '%')
                        ->orWhere('full_name', 'like', '%' . $this->search . '%')
                        ->orWhere('preferred_name', 'like', '%' . $this->search . '%')
                        ->orWhere('phone', 'like', '%' . $this->search . '%')
                        ->orWhere('secondary_phone', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%')
                        ->orWhere('identity_document', 'like', '%' . $this->search . '%')
                        ->orWhere('company_name', 'like', '%' . $this->search . '%')
                        ->orWhere('country', 'like', '%' . $this->search . '%');
                });
            })
            ->latest()
            ->paginate(10);

        return view('livewire.customers.index', [
            'customers' => $customers,
            'frontDeskAgent' => Auth::user()?->name,
            'stats' => [
                'total' => Customer::query()->count(),
                'identified' => Customer::query()->where('is_identified', true)->count(),
                'vip' => Customer::query()->where('vip_status', true)->count(),
                'inHouse' => Stay::query()->where('status', StayStatus::Active->value)->count(),
            ],
            'rooms' => Room::query()
                ->whereNotIn('status', [
                    RoomStatus::Maintenance->value,
                    RoomStatus::Occupied->value,
                ])
                ->orderBy('number')
                ->get(),
            'nights'         => $nights,
            'estimatedTotal' => $estimatedTotal,
        ]);
    }

    private function generateGuestCode(): string
    {
        do {
            $candidate = 'GST-' . now()->format('ymd') . '-' . Str::upper(Str::random(4));
        } while (Customer::query()->where('guest_code', $candidate)->exists());

        return $candidate;
    }
}
