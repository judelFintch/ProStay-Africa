<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Benefit extends Model
{
    protected $fillable = ['name', 'code', 'icon', 'description', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /** Chambres qui incluent cette prestation. */
    public function rooms(): BelongsToMany
    {
        return $this->belongsToMany(Room::class)
            ->withPivot('quantity_per_stay');
    }

    /** Plats du menu qui réalisent cette prestation. */
    public function menus(): BelongsToMany
    {
        return $this->belongsToMany(Menu::class);
    }
}
