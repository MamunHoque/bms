@extends('layouts.app')
@section('title', 'Unit '.$unit->unit_number)
@section('content')
<div class="mb-6">
    <a href="{{ route('units.index') }}" class="text-xs text-[var(--muted)]">← Units</a>
    <h1 class="font-serif text-4xl mt-1">{{ $unit->building->name }} · #{{ $unit->unit_number }}</h1>
    <p class="text-[var(--muted)]">Floor {{ $unit->floor }} · {{ $unit->bedrooms }} bed, {{ $unit->bathrooms }} bath · @money($unit->base_rent)/mo</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
    <div class="card p-5">
        <h2 class="font-serif text-xl mb-3">Leases</h2>
        <table class="bms">
            <thead><tr><th>Tenant</th><th>Period</th><th>Rent</th><th>Status</th></tr></thead>
            <tbody>
            @forelse($unit->leases as $l)
                <tr>
                    <td>{{ $l->tenant->name }}</td>
                    <td class="text-xs">@bmsdate($l->start_date) – @bmsdate($l->end_date)</td>
                    <td>@money($l->monthly_rent)</td>
                    <td>{{ ucfirst($l->status) }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-[var(--muted)] py-4">No leases.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card p-5">
        <h2 class="font-serif text-xl mb-3">Recent utility readings</h2>
        <table class="bms">
            <thead><tr><th>Period</th><th>Utility</th><th>Consumption</th><th>Amount</th></tr></thead>
            <tbody>
            @foreach($unit->utilityReadings->sortByDesc('period_month')->take(8) as $r)
                <tr>
                    <td class="text-xs">{{ $r->period_month->format('M Y') }}</td>
                    <td>{{ $r->utilityType->name }}</td>
                    <td>{{ $r->consumption }} {{ $r->utilityType->unit_label }}</td>
                    <td>@money($r->amount)</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
