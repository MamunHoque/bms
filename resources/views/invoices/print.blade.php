<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif; color: #111; margin: 0;
            background: #eee; padding: 40px 20px;
        }
        .sheet {
            max-width: 820px; margin: 0 auto; background: white;
            padding: 60px 70px; box-shadow: 0 10px 40px rgba(0,0,0,.08);
        }
        .serif { font-family: 'Instrument Serif', serif; }
        h1 { font-size: 42px; margin: 0; letter-spacing: -0.02em; }
        .muted { color: #666; }
        .row { display: flex; justify-content: space-between; align-items: flex-start; gap: 30px; }
        table { width: 100%; border-collapse: collapse; margin-top: 30px; }
        th { text-align: left; padding: 10px 0; border-bottom: 2px solid #111;
             font-size: 11px; text-transform: uppercase; letter-spacing: .05em; color: #666; }
        td { padding: 12px 0; border-bottom: 1px solid #eee; font-size: 14px; }
        .r { text-align: right; }
        .total-row td { font-weight: 600; border-top: 2px solid #111; border-bottom: none; padding-top: 16px; }
        .paid { color: #16a34a; }
        .due { color: #d4471f; }
        .status {
            display: inline-block; padding: 4px 12px; border-radius: 999px;
            font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .05em;
        }
        .s-paid    { background: #dcfce7; color: #166534; }
        .s-unpaid  { background: #f3f4f6; color: #374151; }
        .s-partial { background: #fef3c7; color: #92400e; }
        .s-overdue { background: #fee2e2; color: #991b1b; }
        .no-print { position: fixed; top: 20px; right: 20px; }
        @media print { body { background: white; padding: 0; } .sheet { box-shadow: none; } .no-print { display: none; } }
    </style>
</head>
<body>
<div class="no-print">
    <button onclick="window.print()" style="padding: 10px 20px; background: #111; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 500;">🖨 Print / Save as PDF</button>
</div>
<div class="sheet">
    <div class="row" style="align-items: flex-start; margin-bottom: 50px;">
        <div>
            <h1 class="serif">Invoice</h1>
            <p class="muted" style="margin: 4px 0 0; font-family: monospace; font-size: 14px;">{{ $invoice->invoice_number }}</p>
        </div>
        <div style="text-align: right;">
            <span class="status s-{{ $invoice->status }}">{{ ucfirst($invoice->status) }}</span>
            <p class="muted" style="margin: 12px 0 0; font-size: 13px;">Issued @bmsdate($invoice->issue_date)<br>Due @bmsdate($invoice->due_date)</p>
        </div>
    </div>

    <div class="row" style="margin-bottom: 20px;">
        <div>
            <p class="muted" style="font-size: 11px; text-transform: uppercase; letter-spacing: .05em; margin: 0 0 6px;">Bill to</p>
            <p style="margin: 0; font-weight: 600; font-size: 16px;">{{ $invoice->lease->tenant->name }}</p>
            @if($invoice->lease->tenant->phone)<p class="muted" style="margin: 2px 0; font-size: 13px;">{{ $invoice->lease->tenant->phone }}</p>@endif
            @if($invoice->lease->tenant->email)<p class="muted" style="margin: 2px 0; font-size: 13px;">{{ $invoice->lease->tenant->email }}</p>@endif
        </div>
        <div style="text-align: right;">
            <p class="muted" style="font-size: 11px; text-transform: uppercase; letter-spacing: .05em; margin: 0 0 6px;">Property</p>
            <p style="margin: 0; font-weight: 600; font-size: 16px;">{{ $invoice->lease->unit->building->name }}</p>
            <p class="muted" style="margin: 2px 0; font-size: 13px;">Unit #{{ $invoice->lease->unit->unit_number }}, Floor {{ $invoice->lease->unit->floor }}</p>
            <p class="muted" style="margin: 2px 0; font-size: 13px;">{{ $invoice->lease->unit->building->address }}</p>
        </div>
    </div>

    <p class="muted" style="font-size: 13px;">For billing period: <strong style="color: #111;">{{ $invoice->period_month->format('F Y') }}</strong></p>

    <table>
        <thead>
            <tr><th>Description</th><th class="r" style="width: 100px;">Qty</th><th class="r" style="width: 120px;">Unit Price</th><th class="r" style="width: 140px;">Amount</th></tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
            <tr>
                <td>{{ $item->description }}</td>
                <td class="r">{{ rtrim(rtrim((string)$item->quantity, '0'), '.') }}</td>
                <td class="r">@money($item->unit_price)</td>
                <td class="r">@money($item->amount)</td>
            </tr>
            @endforeach
            @if($invoice->late_fee > 0)
            <tr><td colspan="3" class="r muted">Late fee</td><td class="r due">@money($invoice->late_fee)</td></tr>
            @endif
            <tr class="total-row"><td colspan="3" class="r">Total</td><td class="r">@money($invoice->total)</td></tr>
            <tr><td colspan="3" class="r muted">Amount paid</td><td class="r paid">@money($invoice->paid_amount)</td></tr>
            <tr class="total-row"><td colspan="3" class="r">Balance due</td><td class="r due">@money($invoice->total - $invoice->paid_amount)</td></tr>
        </tbody>
    </table>

    @if($invoice->notes)
        <div style="margin-top: 40px; padding: 16px; background: #faf9f6; border-radius: 8px;">
            <p class="muted" style="font-size: 11px; text-transform: uppercase; letter-spacing: .05em; margin: 0 0 6px;">Notes</p>
            <p style="margin: 0; font-size: 13px;">{{ $invoice->notes }}</p>
        </div>
    @endif

    <p class="muted" style="text-align: center; font-size: 12px; margin-top: 60px;">Thank you for your prompt payment.</p>
</div>
</body>
</html>
