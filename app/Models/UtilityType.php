<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UtilityType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'unit_label', 'rate_per_unit', 'is_metered', 'flat_fee', 'active',
    ];

    protected $casts = [
        'rate_per_unit' => 'decimal:4',
        'flat_fee' => 'decimal:2',
        'is_metered' => 'boolean',
        'active' => 'boolean',
    ];
}
