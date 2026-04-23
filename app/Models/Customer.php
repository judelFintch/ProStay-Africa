<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
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
        'preferred_language',
        'identity_document',
        'identity_document_type',
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
        'vip_status',
        'blacklisted',
        'is_identified',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'identity_document_issued_at' => 'date',
            'identity_document_expires_at' => 'date',
            'is_identified' => 'boolean',
            'vip_status' => 'boolean',
            'blacklisted' => 'boolean',
        ];
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function stays(): HasMany
    {
        return $this->hasMany(Stay::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
