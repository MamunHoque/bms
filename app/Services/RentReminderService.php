<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Setting;
use Carbon\Carbon;

class RentReminderService
{
    protected SmsService $sms;

    public function __construct(SmsService $sms)
    {
        $this->sms = $sms;
    }

    /**
     * Send rent reminders for unpaid/partial invoices due within N days.
     *
     * @return array{sent: int, failed: int, skipped: int}
     */
    public function sendReminders(): array
    {
        $daysBefore = (int) Setting::get('sms_reminder_days', 3);
        $template   = Setting::get('sms_template', 'Dear {tenant}, your rent of {amount} for {unit} ({building}) is due on {due_date}. Please pay soon.');

        $cutoff = Carbon::now()->addDays($daysBefore)->endOfDay();

        $invoices = Invoice::with('lease.tenant', 'lease.unit.building')
            ->whereIn('status', ['unpaid', 'partial'])
            ->where('due_date', '<=', $cutoff->toDateString())
            ->get();

        $sent = 0;
        $failed = 0;
        $skipped = 0;

        foreach ($invoices as $invoice) {
            $tenant = $invoice->lease?->tenant;
            if (! $tenant || ! $tenant->phone) {
                $skipped++;
                continue;
            }

            $unit     = $invoice->lease->unit;
            $building = $unit?->building;

            $message = str_replace(
                ['{tenant}', '{amount}', '{unit}', '{building}', '{due_date}', '{invoice_no}'],
                [
                    $tenant->name,
                    number_format((float) ($invoice->total - $invoice->paid_amount), 2),
                    $unit?->unit_number ?? '—',
                    $building?->name ?? '—',
                    $invoice->due_date->format('d M Y'),
                    $invoice->invoice_number,
                ],
                $template
            );

            $result = $this->sms->send($tenant->phone, $message, $tenant->id);

            if ($result['success']) {
                $sent++;
            } else {
                $failed++;
            }
        }

        return compact('sent', 'failed', 'skipped');
    }
}
