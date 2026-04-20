<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number', 'lease_id', 'period_month', 'issue_date', 'due_date',
        'subtotal', 'utility_total', 'late_fee', 'total', 'paid_amount',
        'status', 'notes',
    ];

    protected $casts = [
        'period_month' => 'date',
        'issue_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'utility_total' => 'decimal:2',
        'late_fee' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    public function lease(): BelongsTo
    {
        return $this->belongsTo(Lease::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function getDueAmountAttribute(): float
    {
        return (float) ($this->total - $this->paid_amount);
    }

    public function isOverdue(): bool
    {
        return $this->status !== 'paid' && $this->due_date->isPast();
    }

    public function recalculateStatus(): void
    {
        $paid = (float) $this->paid_amount;
        $total = (float) $this->total;

        if ($paid <= 0) {
            $this->status = $this->isOverdue() ? 'overdue' : 'unpaid';
        } elseif ($paid >= $total) {
            $this->status = 'paid';
        } else {
            $this->status = 'partial';
        }
        $this->save();
    }
}
