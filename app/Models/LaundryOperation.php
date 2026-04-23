<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LaundryOperation extends Model
{
    use HasFactory;

    protected $fillable = ['laundry_item_id', 'user_id', 'from_status', 'to_status', 'quantity', 'notes'];

    public function laundryItem(): BelongsTo
    {
        return $this->belongsTo(LaundryItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
