@extends('layouts.app')
@section('title', 'Leases')
@section('content')
<div class="mb-6 flex items-end justify-between">
    <div>
        <p class="text-xs uppercase tracking-widest text-[var(--muted)]">Contracts</p>
        <h1 class="font-serif text-4xl">Leases</h1>
    </div>
    <a href="{{ route('leases.create') }}" class="btn btn-primary">+ New Lease</a>
</div>

<form method="GET" class="card p-4 mb-5 flex gap-3">
    <select name="status" class="select max-w-xs">
        <option value="">All statuses</option>
        @foreach(['active', 'ended', 'terminated'] as $s)
            <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
        @endforeach
    </select>
    <button class="btn btn-ghost">Filter</button>
</form>

<div class="card overflow-hidden">
    <table class="bms">
        <thead><tr><th>Tenant</th><th>Unit</th><th>Start</th><th>End</th><th>Rent</th><th>Status</th><th></th></tr></thead>
        <tbody>
        @forelse($leases as $l)
            <tr>
                <td class="font-medium"><a href="{{ route('leases.show', $l) }}">{{ $l->tenant->name }}</a></td>
                <td>{{ $l->unit->building->name }} #{{ $l->unit->unit_number }}</td>
                <td>@bmsdate($l->start_date)</td>
                <td>@bmsdate($l->end_date)</td>
                <td>@money($l->monthly_rent)</td>
                <td><span class="badge {{ $l->status === 'active' ? 'badge-green' : 'badge-gray' }}">{{ ucfirst($l->status) }}</span></td>
                <td class="text-right">
                    <a href="{{ route('leases.edit', $l) }}" class="btn btn-ghost text-xs">Edit</a>
                    @if($l->status === 'active')
                        <form action="{{ route('leases.end', $l) }}" method="POST" class="inline" onsubmit="return confirm('End this lease?');">
                            @csrf
                            <button class="btn btn-ghost text-xs">End</button>
                        </form>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-center text-[var(--muted)] py-8">No leases yet.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $leases->links() }}</div>
@endsection
