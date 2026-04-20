<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Lease;
use App\Models\UtilityReading;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    /**
     * Generate a monthly invoice for a given active lease.
     * Combines rent + any utility readings for the period.
     * Returns null if already exists for that lease/period.
     */
    public function generateForLease(Lease $lease, Carbon $periodMonth): ?Invoice
    {
        if ($lease->status !== 'active') {
            return null;
        }

        $period = $periodMonth->copy()->startOfMonth();

        // Idempotency — one invoice per lease per month.
        $existing = Invoice::where('lease_id', $lease->id)
            ->whereDate('period_month', $period->toDateString())
            ->first();
        if ($existing) {
            return $existing;
        }

        return DB::transaction(function () use ($lease, $period) {
            $subtotal = (float) $lease->monthly_rent;

            // Collect utility readings that haven't been invoiced yet for this month.
            $readings = UtilityReading::with('utilityType')
                ->where('unit_id', $lease->unit_id)
                ->whereDate('period_month', $period->toDateString())
                ->get();

            $utilityTotal = (float) $readings->sum('amount');
            $total = $subtotal + $utilityTotal;

            $dueDay = max(1, min(28, (int) $lease->rent_due_day));
            $issueDate = $period->copy();
            $dueDate = $period->copy()->day($dueDay);

            $invoice = Invoice::create([
                'invoice_number' => $this->nextInvoiceNumber(),
                'lease_id' => $lease->id,
                'period_month' => $period->toDateString(),
                'issue_date' => $issueDate->toDateString(),
                'due_date' => $dueDate->toDateString(),
                'subtotal' => $subtotal,
                'utility_total' => $utilityTotal,
                'late_fee' => 0,
                'total' => $total,
                'paid_amount' => 0,
                'status' => 'unpaid',
            ]);

            // Rent line
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'type' => 'rent',
                'description' => 'Monthly rent — '.$period->format('F Y'),
                'quantity' => 1,
                'unit_price' => $subtotal,
                'amount' => $subtotal,
            ]);

            // Utility lines
            foreach ($readings as $reading) {
                $desc = $reading->utilityType->name;
                if ($reading->utilityType->is_metered) {
                    $desc .= " ({$reading->consumption} {$reading->utilityType->unit_label} @ {$reading->utilityType->rate_per_unit})";
                }
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'type' => 'utility',
                    'description' => $desc,
                    'quantity' => $reading->utilityType->is_metered ? $reading->consumption : 1,
                    'unit_price' => $reading->utilityType->is_metered ? $reading->utilityType->rate_per_unit : $reading->amount,
                    'amount' => $reading->amount,
                ]);
            }

            return $invoice;
        });
    }

    /**
     * Generate invoices for all active leases for a given month.
     * @return array{created:int, skipped:int}
     */
    public function generateForMonth(Carbon $periodMonth): array
    {
        $created = 0;
        $skipped = 0;

        Lease::where('status', 'active')->get()->each(function ($lease) use ($periodMonth, &$created, &$skipped) {
            $before = Invoice::where('lease_id', $lease->id)
                ->whereDate('period_month', $periodMonth->copy()->startOfMonth()->toDateString())
                ->exists();
            $this->generateForLease($lease, $periodMonth);
            if ($before) {
                $skipped++;
            } else {
                $created++;
            }
        });

        return ['created' => $created, 'skipped' => $skipped];
    }

    protected function nextInvoiceNumber(): string
    {
        $prefix = 'INV-'.now()->format('Ym').'-';
        $lastNum = Invoice::where('invoice_number', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->value('invoice_number');

        $next = 1;
        if ($lastNum) {
            $parts = explode('-', $lastNum);
            $next = ((int) end($parts)) + 1;
        }
        return $prefix.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }
}
