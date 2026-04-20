@extends('layouts.app')
@section('title', 'Reports')
@section('content')
<div class="mb-8">
    <p class="text-xs uppercase tracking-widest text-[var(--muted)]">Insights</p>
    <h1 class="font-serif text-5xl leading-tight">Reports</h1>
    <p class="text-[var(--muted)] mt-1">Financial health at a glance.</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-5">
    <a href="{{ route('reports.collection') }}" class="card p-6 hover:border-[var(--ink)] transition">
        <div class="flex items-start justify-between">
            <div>
                <h2 class="font-serif text-2xl">Collection Trend</h2>
                <p class="text-sm text-[var(--muted)] mt-1">Month-by-month payments with method breakdown.</p>
            </div>
            <div class="accent-dot mt-2"></div>
        </div>
    </a>
    <a href="{{ route('reports.dues') }}" class="card p-6 hover:border-[var(--ink)] transition">
        <div class="flex items-start justify-between">
            <div>
                <h2 class="font-serif text-2xl">Dues &amp; Aging</h2>
                <p class="text-sm text-[var(--muted)] mt-1">Outstanding amounts bucketed by how overdue they are.</p>
            </div>
            <div class="accent-dot mt-2"></div>
        </div>
    </a>
    <a href="{{ route('reports.occupancy') }}" class="card p-6 hover:border-[var(--ink)] transition">
        <div class="flex items-start justify-between">
            <div>
                <h2 class="font-serif text-2xl">Occupancy</h2>
                <p class="text-sm text-[var(--muted)] mt-1">Vacancy rate per building and portfolio-wide.</p>
            </div>
            <div class="accent-dot mt-2"></div>
        </div>
    </a>
    <a href="{{ route('reports.utilities') }}" class="card p-6 hover:border-[var(--ink)] transition">
        <div class="flex items-start justify-between">
            <div>
                <h2 class="font-serif text-2xl">Utilities</h2>
                <p class="text-sm text-[var(--muted)] mt-1">Total consumption and cost by type and month.</p>
            </div>
            <div class="accent-dot mt-2"></div>
        </div>
    </a>
</div>
@endsection
