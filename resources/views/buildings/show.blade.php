@extends('layouts.app')
@section('title', $building->name)
@section('content')
<div class="mb-6">
    <a href="{{ route('buildings.index') }}" class="text-xs text-[var(--muted)]">← Buildings</a>
    <h1 class="font-serif text-4xl mt-1">{{ $building->name }}</h1>
    <p class="text-[var(--muted)]">{{ $building->address }}, {{ $building->city }} · {{ $building->total_floors }} floors</p>
</div>

<div class="card">
    <div class="p-5 flex items-center justify-between border-b border-[var(--line)]">
        <h2 class="font-serif text-xl">Units ({{ $building->units->count() }})</h2>
        <a href="{{ route('units.create') }}" class="btn btn-ghost text-xs">+ Unit</a>
    </div>
    <table class="bms">
        <thead><tr><th>Unit</th><th>Floor</th><th>Beds/Baths</th><th>Rent</th><th>Status</th></tr></thead>
        <tbody>
        @foreach($building->units as $u)
            <tr>
                <td class="font-medium"><a href="{{ route('units.show', $u) }}">#{{ $u->unit_number }}</a></td>
                <td>{{ $u->floor }}</td>
                <td>{{ $u->bedrooms }} / {{ $u->bathrooms }}</td>
                <td>@money($u->base_rent)</td>
                <td>
                    <span class="badge {{ $u->status === 'occupied' ? 'badge-green' : ($u->status === 'maintenance' ? 'badge-amber' : 'badge-gray') }}">{{ ucfirst($u->status) }}</span>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
