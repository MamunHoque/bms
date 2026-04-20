@extends('layouts.app')
@section('title', 'Utilities')
@section('content')
<div class="mb-6 flex items-end justify-between flex-wrap gap-3">
    <div>
        <p class="text-xs uppercase tracking-widest text-[var(--muted)]">Metered</p>
        <h1 class="font-serif text-4xl">Utility Readings</h1>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('utilities.types') }}" class="btn btn-ghost">Manage Types</a>
        <a href="{{ route('utilities.readings.create') }}" class="btn btn-primary">+ New Reading</a>
    </div>
</div>

<form method="GET" class="card p-4 mb-5 flex flex-wrap gap-3 items-end">
    <div>
        <label class="label">Utility type</label>
        <select name="utility_type_id" class="select">
            <option value="">All</option>
            @foreach($types as $t)
                <option value="{{ $t->id }}" @selected(request('utility_type_id') == $t->id)>{{ $t->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="label">Month</label>
        <input type="month" name="month" value="{{ request('month') }}" class="input">
    </div>
    <button class="btn btn-ghost">Filter</button>
</form>

<div class="card overflow-hidden">
    <table class="bms">
        <thead><tr><th>Period</th><th>Unit</th><th>Utility</th><th>Previous</th><th>Current</th><th>Consumption</th><th>Amount</th><th></th></tr></thead>
        <tbody>
        @forelse($readings as $r)
            <tr>
                <td>{{ $r->period_month->format('M Y') }}</td>
                <td>{{ $r->unit->building->name }} #{{ $r->unit->unit_number }}</td>
                <td>{{ $r->utilityType->name }}</td>
                <td class="text-[var(--muted)]">{{ $r->previous_reading }}</td>
                <td class="text-[var(--muted)]">{{ $r->current_reading }}</td>
                <td class="font-medium">{{ $r->consumption }} {{ $r->utilityType->unit_label }}</td>
                <td class="font-semibold">@money($r->amount)</td>
                <td class="text-right">
                    <form action="{{ route('utilities.readings.destroy', $r) }}" method="POST" class="inline" onsubmit="return confirm('Delete this reading?');">
                        @csrf @method('DELETE')
                        <button class="btn btn-danger text-xs">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="8" class="text-center text-[var(--muted)] py-8">No readings yet. <a href="{{ route('utilities.readings.create') }}" class="text-[var(--accent)]">Add the first one.</a></td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $readings->links() }}</div>
@endsection
