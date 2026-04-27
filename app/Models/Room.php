<?php

namespace App\Models;

use App\Enums\RoomStatus;
use App\Enums\StayStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_type_id', 'number', 'floor', 'capacity', 'price', 'status',
        'description', 'surface_m2', 'bed_type', 'view_type', 'smoking',
        'has_private_bathroom', 'has_air_conditioning', 'has_wifi', 'has_tv',
        'has_balcony', 'has_kitchenette', 'has_safe', 'has_minibar',
        'extra_bed_available', 'internal_notes',
    ];

    protected function casts(): array
    {
        return [
            'status'               => RoomStatus::class,
            'smoking'              => 'boolean',
            'has_private_bathroom' => 'boolean',
            'has_air_conditioning' => 'boolean',
            'has_wifi'             => 'boolean',
            'has_tv'               => 'boolean',
            'has_balcony'          => 'boolean',
            'has_kitchenette'      => 'boolean',
            'has_safe'             => 'boolean',
            'has_minibar'          => 'boolean',
            'extra_bed_available'  => 'boolean',
            'surface_m2'           => 'decimal:1',
        ];
    }

    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function stays(): HasMany
    {
        return $this->hasMany(Stay::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function activeStay(): HasOne
    {
        return $this->hasOne(Stay::class)
            ->where('status', StayStatus::Active->value)
            ->latestOfMany();
    }

    /** Prestations incluses dans cette chambre. */
    public function benefits(): BelongsToMany
    {
        return $this->belongsToMany(Benefit::class)
            ->withPivot('quantity_per_stay');
    }
}
