<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'unit_id', 'tenant_id', 'title', 'description', 'priority',
        'status', 'cost', 'reported_on', 'resolved_on',
    ];

    protected $casts = [
        'reported_on' => 'date',
        'resolved_on' => 'date',
        'cost' => 'decimal:2',
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
