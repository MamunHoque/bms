@extends('layouts.app')
@section('title', 'Units')
@section('content')
<div class="mb-6 flex items-end justify-between">
    <div>
        <p class="text-xs uppercase tracking-widest text-[var(--muted)]">Inventory</p>
        <h1 class="font-serif text-4xl">Units</h1>
    </div>
    <a href="{{ route('units.create') }}" class="btn btn-primary">+ New Unit</a>
</div>

<form method="GET" class="card p-4 mb-5 flex flex-wrap gap-3 items-end">
    <div>
        <label class="label">Building</label>
        <select name="building_id" class="select">
            <option value="">All</option>
            @foreach($buildings as $b)
                <option value="{{ $b->id }}" @selected(request('building_id') == $b->id)>{{ $b->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="label">Status</label>
        <select name="status" class="select">
            <option value="">All</option>
            @foreach(['vacant', 'occupied', 'maintenance'] as $s)
                <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
    </div>
    <button class="btn btn-ghost">Filter</button>
</form>

<div class="card overflow-hidden">
    <table class="bms">
        <thead><tr><th>Building / Unit</th><th>Floor</th><th>Size</th><th>Rent</th><th>Tenant</th><th>Status</th><th></th></tr></thead>
        <tbody>
        @forelse($units as $u)
            <tr>
                <td>
                    <span class="text-[var(--muted)] text-xs">{{ $u->building->name }}</span><br>
                    <a href="{{ route('units.show', $u) }}" class="font-medium">#{{ $u->unit_number }}</a>
                </td>
                <td>{{ $u->floor }}</td>
                <td>{{ $u->size_sqft ? $u->size_sqft.' sqft' : '—' }}</td>
                <td>@money($u->base_rent)</td>
                <td>{{ $u->activeLease?->tenant?->name ?? '—' }}</td>
                <td><span class="badge {{ $u->status === 'occupied' ? 'badge-green' : ($u->status === 'maintenance' ? 'badge-amber' : 'badge-gray') }}">{{ ucfirst($u->status) }}</span></td>
                <td class="text-right">
                    <a href="{{ route('units.edit', $u) }}" class="btn btn-ghost text-xs">Edit</a>
                    <form action="{{ route('units.destroy', $u) }}" method="POST" class="inline" onsubmit="return confirm('Delete this unit?');">
                        @csrf @method('DELETE')
                        <button class="btn btn-danger text-xs">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-center text-[var(--muted)] py-8">No units found.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $units->links() }}</div>
@endsection
