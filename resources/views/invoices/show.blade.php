@extends('layouts.app')
@section('title', 'Invoice '.$invoice->invoice_number)
@section('content')
<div class="mb-6 flex items-end justify-between flex-wrap gap-3">
    <div>
        <a href="{{ route('invoices.index') }}" class="text-xs text-[var(--muted)]">← Invoices</a>
        <h1 class="font-serif text-4xl mt-1">{{ $invoice->invoice_number }}</h1>
        <p class="text-[var(--muted)]">{{ $invoice->lease->tenant->name }} · {{ $invoice->lease->unit->building->name }} #{{ $invoice->lease->unit->unit_number }} · Period {{ $invoice->period_month->format('F Y') }}</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('invoices.print', $invoice) }}" target="_blank" class="btn btn-ghost">🖨 Print</a>
        @if($invoice->status !== 'paid')
            <a href="{{ route('payments.create', ['invoice_id' => $invoice->id]) }}" class="btn btn-accent">Record Payment</a>
        @endif
        <a href="{{ route('invoices.edit', $invoice) }}" class="btn btn-ghost">Edit</a>
    </div>
</div>

<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="card p-4"><p class="text-xs text-[var(--muted)]">Issue date</p><p class="font-medium mt-1">@bmsdate($invoice->issue_date)</p></div>
    <div class="card p-4"><p class="text-xs text-[var(--muted)]">Due date</p><p class="font-medium mt-1 {{ $invoice->isOverdue() ? 'text-red-600' : '' }}">@bmsdate($invoice->due_date)</p></div>
    <div class="card p-4"><p class="text-xs text-[var(--muted)]">Total</p><p class="font-semibold mt-1">@money($invoice->total)</p></div>
    <div class="card p-4"><p class="text-xs text-[var(--muted)]">Status</p><p class="mt-1">@include('invoices._status', ['status' => $invoice->status])</p></div>
</div>

<div class="card mb-5">
    <div class="p-5 border-b border-[var(--line)]">
        <h2 class="font-serif text-xl">Line items</h2>
    </div>
    <table class="bms">
        <thead><tr><th>Type</th><th>Description</th><th>Qty</th><th class="text-right">Unit price</th><th class="text-right">Amount</th></tr></thead>
        <tbody>
        @foreach($invoice->items as $item)
            <tr>
                <td><span class="badge {{ $item->type === 'rent' ? 'badge-blue' : 'badge-gray' }}">{{ ucfirst($item->type) }}</span></td>
                <td>{{ $item->description }}</td>
                <td>{{ rtrim(rtrim((string)$item->quantity, '0'), '.') }}</td>
                <td class="text-right">@money($item->unit_price)</td>
                <td class="text-right font-medium">@money($item->amount)</td>
            </tr>
        @endforeach
        </tbody>
        <tfoot>
            <tr><td colspan="4" class="text-right text-[var(--muted)]">Subtotal (rent)</td><td class="text-right">@money($invoice->subtotal)</td></tr>
            <tr><td colspan="4" class="text-right text-[var(--muted)]">Utilities</td><td class="text-right">@money($invoice->utility_total)</td></tr>
            @if($invoice->late_fee > 0)
                <tr><td colspan="4" class="text-right text-[var(--muted)]">Late fee</td><td class="text-right text-red-600">@money($invoice->late_fee)</td></tr>
            @endif
            <tr class="font-semibold"><td colspan="4" class="text-right">Total</td><td class="text-right">@money($invoice->total)</td></tr>
            <tr><td colspan="4" class="text-right text-[var(--muted)]">Paid</td><td class="text-right text-green-700">@money($invoice->paid_amount)</td></tr>
            <tr class="font-semibold"><td colspan="4" class="text-right">Amount due</td><td class="text-right text-[var(--accent)]">@money($invoice->total - $invoice->paid_amount)</td></tr>
        </tfoot>
    </table>
</div>

<div class="card">
    <div class="p-5 border-b border-[var(--line)]"><h2 class="font-serif text-xl">Payments</h2></div>
    <table class="bms">
        <thead><tr><th>Date</th><th>Amount</th><th>Method</th><th>Reference</th><th></th></tr></thead>
        <tbody>
        @forelse($invoice->payments as $p)
            <tr>
                <td>@bmsdate($p->paid_on)</td>
                <td class="font-medium">@money($p->amount)</td>
                <td>{{ ucfirst($p->method) }}</td>
                <td class="text-[var(--muted)] font-mono text-xs">{{ $p->reference }}</td>
                <td class="text-right">
                    <form action="{{ route('payments.destroy', $p) }}" method="POST" class="inline" onsubmit="return confirm('Remove this payment?');">
                        @csrf @method('DELETE')
                        <button class="btn btn-danger text-xs">Remove</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-center text-[var(--muted)] py-6">No payments recorded.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
