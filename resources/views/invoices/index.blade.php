@extends('layouts.app')
@section('title', 'Invoices')
@section('content')
<div class="mb-6 flex items-end justify-between flex-wrap gap-3">
    <div>
        <p class="text-xs uppercase tracking-widest text-[var(--muted)]">Billing</p>
        <h1 class="font-serif text-4xl">Invoices</h1>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('invoices.create') }}" class="btn btn-ghost">+ Single Invoice</a>
        <button onclick="document.getElementById('genModal').classList.remove('hidden')" class="btn btn-primary">⚡ Generate Monthly Batch</button>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5">
    <div class="card p-4">
        <p class="text-xs text-[var(--muted)]">Total invoiced</p>
        <p class="kpi mt-2">@money($summary['total'])</p>
    </div>
    <div class="card p-4">
        <p class="text-xs text-[var(--muted)]">Total collected</p>
        <p class="kpi mt-2 text-green-700">@money($summary['collected'])</p>
    </div>
    <div class="card p-4">
        <p class="text-xs text-[var(--muted)]">Outstanding</p>
        <p class="kpi mt-2 text-[var(--accent)]">@money($summary['outstanding'])</p>
    </div>
</div>

<form method="GET" class="card p-4 mb-5 flex flex-wrap gap-3 items-end">
    <div>
        <label class="label">Status</label>
        <select name="status" class="select">
            <option value="">All</option>
            @foreach(['unpaid','partial','paid','overdue'] as $s)
                <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="label">Period month</label>
        <input type="month" name="month" value="{{ request('month') }}" class="input">
    </div>
    <button class="btn btn-ghost">Filter</button>
</form>

<div class="card overflow-hidden">
    <table class="bms">
        <thead><tr><th>Invoice</th><th>Tenant / Unit</th><th>Period</th><th>Due</th><th>Total</th><th>Paid</th><th>Due</th><th>Status</th></tr></thead>
        <tbody>
        @forelse($invoices as $inv)
            <tr>
                <td><a href="{{ route('invoices.show', $inv) }}" class="font-mono text-sm font-medium">{{ $inv->invoice_number }}</a></td>
                <td>
                    <div>{{ $inv->lease->tenant->name ?? '—' }}</div>
                    <div class="text-xs text-[var(--muted)]">{{ $inv->lease->unit->building->name ?? '' }} #{{ $inv->lease->unit->unit_number ?? '' }}</div>
                </td>
                <td>{{ $inv->period_month->format('M Y') }}</td>
                <td>@bmsdate($inv->due_date)</td>
                <td>@money($inv->total)</td>
                <td class="text-green-700">@money($inv->paid_amount)</td>
                <td class="text-[var(--accent)]">@money($inv->total - $inv->paid_amount)</td>
                <td>@include('invoices._status', ['status' => $inv->status])</td>
            </tr>
        @empty
            <tr><td colspan="8" class="text-center text-[var(--muted)] py-8">No invoices yet.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $invoices->links() }}</div>

{{-- Generate batch modal --}}
<div id="genModal" class="hidden fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4">
    <div class="card p-6 w-full max-w-md">
        <h3 class="font-serif text-2xl mb-2">Generate monthly invoices</h3>
        <p class="text-sm text-[var(--muted)] mb-4">Creates one invoice per active lease for the selected month, combining rent and recorded utility readings. Already-existing invoices are skipped.</p>
        <form method="POST" action="{{ route('invoices.generate') }}">
            @csrf
            <label class="label">Period month</label>
            <input type="month" name="period_month" value="{{ now()->format('Y-m') }}" class="input mb-4" required>
            <div class="flex gap-2 justify-end">
                <button type="button" onclick="document.getElementById('genModal').classList.add('hidden')" class="btn btn-ghost">Cancel</button>
                <button class="btn btn-primary">Generate</button>
            </div>
        </form>
    </div>
</div>
@endsection
