@extends('layouts.app')
@section('title', 'New Invoice')
@section('content')
<h1 class="font-serif text-3xl mb-6">New Invoice</h1>
<div class="card p-6">
    <form method="POST" action="{{ route('invoices.store') }}">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="label">Lease *</label>
                <select name="lease_id" class="select" required>
                    <option value="">— Select —</option>
                    @foreach($leases as $l)
                        <option value="{{ $l->id }}">{{ $l->tenant->name }} · {{ $l->unit->building->name }} #{{ $l->unit->unit_number }} (@moneyplain($l->monthly_rent)/mo)</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label">Period month *</label>
                <input type="month" name="period_month" value="{{ now()->format('Y-m') }}" class="input" required>
            </div>
        </div>
        <p class="text-xs text-[var(--muted)] mt-3">The invoice will include the lease's monthly rent plus any utility readings recorded for that month on the unit.</p>
        <div class="mt-6 flex gap-2">
            <button class="btn btn-primary">Generate Invoice</button>
            <a href="{{ route('invoices.index') }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>
@endsection
