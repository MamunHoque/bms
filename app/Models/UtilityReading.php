<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UtilityReading extends Model
{
    use HasFactory;

    protected $fillable = [
        'unit_id', 'utility_type_id', 'period_month', 'previous_reading',
        'current_reading', 'consumption', 'amount', 'notes',
    ];

    protected $casts = [
        'period_month' => 'date',
        'previous_reading' => 'decimal:2',
        'current_reading' => 'decimal:2',
        'consumption' => 'decimal:2',
        'amount' => 'decimal:2',
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function utilityType(): BelongsTo
    {
        return $this->belongsTo(UtilityType::class);
    }
}
