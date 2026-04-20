@extends('layouts.app')
@section('title', 'Lease Detail')
@section('content')
<div class="mb-6">
    <a href="{{ route('leases.index') }}" class="text-xs text-[var(--muted)]">← Leases</a>
    <h1 class="font-serif text-4xl mt-1">{{ $lease->tenant->name }}</h1>
    <p class="text-[var(--muted)]">{{ $lease->unit->building->name }} · Unit #{{ $lease->unit->unit_number }} · @money($lease->monthly_rent)/mo · Due day {{ $lease->rent_due_day }}</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="card p-4">
        <p class="text-xs text-[var(--muted)]">Start</p>
        <p class="font-medium mt-1">@bmsdate($lease->start_date)</p>
    </div>
    <div class="card p-4">
        <p class="text-xs text-[var(--muted)]">End</p>
        <p class="font-medium mt-1">@bmsdate($lease->end_date)</p>
    </div>
    <div class="card p-4">
        <p class="text-xs text-[var(--muted)]">Security deposit</p>
        <p class="font-medium mt-1">@money($lease->security_deposit)</p>
    </div>
    <div class="card p-4">
        <p class="text-xs text-[var(--muted)]">Status</p>
        <p class="font-medium mt-1"><span class="badge {{ $lease->status === 'active' ? 'badge-green' : 'badge-gray' }}">{{ ucfirst($lease->status) }}</span></p>
    </div>
</div>

<div class="card">
    <div class="p-5 flex items-center justify-between border-b border-[var(--line)]">
        <h2 class="font-serif text-xl">Invoices ({{ $lease->invoices->count() }})</h2>
        <a href="{{ route('invoices.create') }}" class="btn btn-ghost text-xs">+ Invoice</a>
    </div>
    <table class="bms">
        <thead><tr><th>Invoice</th><th>Period</th><th>Due</th><th>Total</th><th>Paid</th><th>Status</th></tr></thead>
        <tbody>
        @forelse($lease->invoices->sortByDesc('period_month') as $inv)
            <tr>
                <td><a href="{{ route('invoices.show', $inv) }}" class="font-mono text-sm">{{ $inv->invoice_number }}</a></td>
                <td>{{ $inv->period_month->format('M Y') }}</td>
                <td>@bmsdate($inv->due_date)</td>
                <td>@money($inv->total)</td>
                <td>@money($inv->paid_amount)</td>
                <td>@include('invoices._status', ['status' => $inv->status])</td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-[var(--muted)] py-6">No invoices yet.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
