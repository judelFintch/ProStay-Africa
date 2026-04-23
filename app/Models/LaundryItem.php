<?php

namespace App\Models;

use App\Enums\LaundryItemStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LaundryItem extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'status', 'quantity'];

    protected function casts(): array
    {
        return [
            'status' => LaundryItemStatus::class,
        ];
    }

    public function operations(): HasMany
    {
        return $this->hasMany(LaundryOperation::class);
    }
}
