@extends('layouts.app')
@section('title', 'Payments')
@section('content')
<div class="mb-6 flex items-end justify-between">
    <div>
        <p class="text-xs uppercase tracking-widest text-[var(--muted)]">Collections</p>
        <h1 class="font-serif text-4xl">Payments</h1>
    </div>
    <a href="{{ route('payments.create') }}" class="btn btn-accent">+ Record Payment</a>
</div>

<div class="card p-4 mb-5">
    <p class="text-xs text-[var(--muted)]">Total for filter</p>
    <p class="kpi mt-2 text-green-700">@money($total)</p>
</div>

<form method="GET" class="card p-4 mb-5 flex flex-wrap gap-3 items-end">
    <div>
        <label class="label">Method</label>
        <select name="method" class="select">
            <option value="">All</option>
            @foreach(['cash','bkash','nagad','bank','card','other'] as $m)
                <option value="{{ $m }}" @selected(request('method') === $m)>{{ ucfirst($m) }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="label">From</label>
        <input type="date" name="from" value="{{ request('from') }}" class="input">
    </div>
    <div>
        <label class="label">To</label>
        <input type="date" name="to" value="{{ request('to') }}" class="input">
    </div>
    <button class="btn btn-ghost">Filter</button>
</form>

<div class="card overflow-hidden">
    <table class="bms">
        <thead><tr><th>Date</th><th>Invoice</th><th>Tenant</th><th>Amount</th><th>Method</th><th>Reference</th><th></th></tr></thead>
        <tbody>
        @forelse($payments as $p)
            <tr>
                <td>@bmsdate($p->paid_on)</td>
                <td><a href="{{ route('invoices.show', $p->invoice) }}" class="font-mono text-sm">{{ $p->invoice->invoice_number }}</a></td>
                <td>{{ $p->invoice->lease->tenant->name ?? '—' }}</td>
                <td class="font-semibold text-green-700">@money($p->amount)</td>
                <td><span class="badge badge-blue">{{ ucfirst($p->method) }}</span></td>
                <td class="font-mono text-xs text-[var(--muted)]">{{ $p->reference }}</td>
                <td class="text-right">
                    <a href="{{ route('payments.edit', $p) }}" class="btn btn-ghost text-xs">Edit</a>
                    <form action="{{ route('payments.destroy', $p) }}" method="POST" class="inline" onsubmit="return confirm('Delete this payment?');">
                        @csrf @method('DELETE')
                        <button class="btn btn-danger text-xs">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-center text-[var(--muted)] py-8">No payments yet.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $payments->links() }}</div>
@endsection
