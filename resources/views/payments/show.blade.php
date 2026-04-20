@extends('layouts.app')
@section('title', 'Payment')
@section('content')
<div class="mb-6">
    <a href="{{ route('payments.index') }}" class="text-xs text-[var(--muted)]">← Payments</a>
    <h1 class="font-serif text-4xl mt-1">@money($payment->amount)</h1>
    <p class="text-[var(--muted)]">Received from {{ $payment->invoice->lease->tenant->name }} on @bmsdate($payment->paid_on)</p>
</div>

<div class="card p-6 space-y-3">
    <div class="flex justify-between"><span class="text-[var(--muted)]">Invoice</span><a href="{{ route('invoices.show', $payment->invoice) }}" class="font-mono">{{ $payment->invoice->invoice_number }}</a></div>
    <div class="flex justify-between"><span class="text-[var(--muted)]">Method</span><span>{{ ucfirst($payment->method) }}</span></div>
    <div class="flex justify-between"><span class="text-[var(--muted)]">Reference</span><span class="font-mono">{{ $payment->reference ?: '—' }}</span></div>
    @if($payment->notes)<div><p class="text-[var(--muted)] mb-1">Notes</p><p>{{ $payment->notes }}</p></div>@endif
</div>
@endsection
