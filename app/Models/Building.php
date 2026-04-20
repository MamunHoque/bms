<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Building extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'address', 'city', 'total_floors', 'notes',
    ];

    public function units(): HasMany
    {
        return $this->hasMany(Unit::class);
    }

    public function occupiedUnitsCount(): int
    {
        return $this->units()->where('status', 'occupied')->count();
    }

    public function vacantUnitsCount(): int
    {
        return $this->units()->where('status', 'vacant')->count();
    }
}
