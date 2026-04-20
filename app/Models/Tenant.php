<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'phone', 'email', 'national_id', 'emergency_contact',
        'occupation', 'notes',
    ];

    public function leases(): HasMany
    {
        return $this->hasMany(Lease::class);
    }

    public function activeLease()
    {
        return $this->leases()->where('status', 'active')->latest()->first();
    }
}
