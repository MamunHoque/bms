@extends('layouts.app')
@section('title', $tenant->name)
@section('content')
<div class="mb-6">
    <a href="{{ route('tenants.index') }}" class="text-xs text-[var(--muted)]">← Tenants</a>
    <h1 class="font-serif text-4xl mt-1">{{ $tenant->name }}</h1>
    <p class="text-[var(--muted)]">{{ $tenant->phone }} · {{ $tenant->email }} · {{ $tenant->occupation }}</p>
</div>

<div class="card p-5 mb-5">
    <h2 class="font-serif text-xl mb-3">Leases</h2>
    <table class="bms">
        <thead><tr><th>Unit</th><th>Period</th><th>Rent</th><th>Status</th></tr></thead>
        <tbody>
        @foreach($tenant->leases as $l)
            <tr>
                <td>{{ $l->unit->building->name }} #{{ $l->unit->unit_number }}</td>
                <td class="text-xs">@bmsdate($l->start_date) – @bmsdate($l->end_date)</td>
                <td>@money($l->monthly_rent)</td>
                <td>{{ ucfirst($l->status) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
