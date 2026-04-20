@extends('layouts.app')
@section('title', 'Buildings')
@section('content')
<div class="mb-6 flex items-end justify-between">
    <div>
        <p class="text-xs uppercase tracking-widest text-[var(--muted)]">Properties</p>
        <h1 class="font-serif text-4xl">Buildings</h1>
    </div>
    <a href="{{ route('buildings.create') }}" class="btn btn-primary">+ New Building</a>
</div>

<div class="card overflow-hidden">
    <table class="bms">
        <thead><tr><th>Name</th><th>Address</th><th>Floors</th><th>Units</th><th>Occupied</th><th></th></tr></thead>
        <tbody>
        @forelse($buildings as $b)
            <tr>
                <td class="font-medium"><a href="{{ route('buildings.show', $b) }}">{{ $b->name }}</a></td>
                <td class="text-[var(--muted)]">{{ $b->address }}, {{ $b->city }}</td>
                <td>{{ $b->total_floors }}</td>
                <td>{{ $b->units_count }}</td>
                <td>{{ $b->occupied_count }} / {{ $b->units_count }}</td>
                <td class="text-right">
                    <a href="{{ route('buildings.edit', $b) }}" class="btn btn-ghost text-xs">Edit</a>
                    <form action="{{ route('buildings.destroy', $b) }}" method="POST" class="inline" onsubmit="return confirm('Delete this building and all its units?');">
                        @csrf @method('DELETE')
                        <button class="btn btn-danger text-xs">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-[var(--muted)] py-8">No buildings yet. <a href="{{ route('buildings.create') }}" class="text-[var(--accent)]">Add the first one.</a></td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $buildings->links() }}</div>
@endsection
