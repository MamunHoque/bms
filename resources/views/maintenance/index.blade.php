@extends('layouts.app')
@section('title', 'Maintenance')
@section('content')
<div class="mb-6 flex items-end justify-between">
    <div>
        <p class="text-xs uppercase tracking-widest text-[var(--muted)]">Upkeep</p>
        <h1 class="font-serif text-4xl">Maintenance</h1>
    </div>
    <a href="{{ route('maintenance.create') }}" class="btn btn-primary">+ New Request</a>
</div>

<form method="GET" class="card p-4 mb-5 flex gap-3">
    <select name="status" class="select max-w-xs">
        <option value="">All statuses</option>
        @foreach(['open','in_progress','resolved','cancelled'] as $s)
            <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst(str_replace('_',' ', $s)) }}</option>
        @endforeach
    </select>
    <button class="btn btn-ghost">Filter</button>
</form>

<div class="card overflow-hidden">
    <table class="bms">
        <thead><tr><th>Reported</th><th>Title</th><th>Unit</th><th>Tenant</th><th>Priority</th><th>Status</th><th>Cost</th><th></th></tr></thead>
        <tbody>
        @php
            $priorityMap = ['low' => 'badge-gray', 'normal' => 'badge-blue', 'high' => 'badge-amber', 'urgent' => 'badge-red'];
            $statusMap = ['open' => 'badge-amber', 'in_progress' => 'badge-blue', 'resolved' => 'badge-green', 'cancelled' => 'badge-gray'];
        @endphp
        @forelse($requests as $r)
            <tr>
                <td class="text-xs">@bmsdate($r->reported_on)</td>
                <td class="font-medium">{{ $r->title }}</td>
                <td>{{ $r->unit->building->name }} #{{ $r->unit->unit_number }}</td>
                <td>{{ $r->tenant->name ?? '—' }}</td>
                <td><span class="badge {{ $priorityMap[$r->priority] ?? 'badge-gray' }}">{{ ucfirst($r->priority) }}</span></td>
                <td><span class="badge {{ $statusMap[$r->status] ?? 'badge-gray' }}">{{ ucfirst(str_replace('_',' ', $r->status)) }}</span></td>
                <td>@money($r->cost)</td>
                <td class="text-right">
                    <a href="{{ route('maintenance.edit', $r) }}" class="btn btn-ghost text-xs">Edit</a>
                    <form action="{{ route('maintenance.destroy', $r) }}" method="POST" class="inline" onsubmit="return confirm('Delete this request?');">
                        @csrf @method('DELETE')
                        <button class="btn btn-danger text-xs">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="8" class="text-center text-[var(--muted)] py-8">No maintenance requests.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $requests->links() }}</div>
@endsection
