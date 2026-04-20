<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Unit extends Model
{
    use HasFactory;

    protected $fillable = [
        'building_id', 'unit_number', 'floor', 'bedrooms', 'bathrooms',
        'size_sqft', 'base_rent', 'status', 'notes',
    ];

    protected $casts = [
        'base_rent' => 'decimal:2',
    ];

    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class);
    }

    public function leases(): HasMany
    {
        return $this->hasMany(Lease::class);
    }

    public function activeLease(): HasOne
    {
        return $this->hasOne(Lease::class)->where('status', 'active');
    }

    public function utilityReadings(): HasMany
    {
        return $this->hasMany(UtilityReading::class);
    }
}
