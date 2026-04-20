@extends('layouts.app')
@section('title', 'Tenants')
@section('content')
<div class="mb-6 flex items-end justify-between">
    <div>
        <p class="text-xs uppercase tracking-widest text-[var(--muted)]">People</p>
        <h1 class="font-serif text-4xl">Tenants</h1>
    </div>
    <a href="{{ route('tenants.create') }}" class="btn btn-primary">+ Add Tenant</a>
</div>

<form method="GET" class="card p-4 mb-5 flex gap-3">
    <input name="q" value="{{ request('q') }}" class="input max-w-md" placeholder="Search by name, phone or email…">
    <button class="btn btn-ghost">Search</button>
</form>

<div class="card overflow-hidden">
    <table class="bms">
        <thead><tr><th>Name</th><th>Phone</th><th>Email</th><th>Occupation</th><th></th></tr></thead>
        <tbody>
        @forelse($tenants as $t)
            <tr>
                <td class="font-medium"><a href="{{ route('tenants.show', $t) }}">{{ $t->name }}</a></td>
                <td>{{ $t->phone }}</td>
                <td class="text-[var(--muted)]">{{ $t->email }}</td>
                <td>{{ $t->occupation }}</td>
                <td class="text-right">
                    <a href="{{ route('tenants.edit', $t) }}" class="btn btn-ghost text-xs">Edit</a>
                    <form action="{{ route('tenants.destroy', $t) }}" method="POST" class="inline" onsubmit="return confirm('Delete this tenant?');">
                        @csrf @method('DELETE')
                        <button class="btn btn-danger text-xs">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-center text-[var(--muted)] py-8">No tenants yet.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $tenants->links() }}</div>
@endsection
