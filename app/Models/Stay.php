<?php

namespace App\Models;

use App\Enums\StayStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Stay extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'room_id',
        'reservation_id',
        'check_in_at',
        'expected_check_out_at',
        'check_out_at',
        'status',
        'nightly_rate',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'check_in_at' => 'datetime',
            'expected_check_out_at' => 'datetime',
            'check_out_at' => 'datetime',
            'status' => StayStatus::class,
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}
