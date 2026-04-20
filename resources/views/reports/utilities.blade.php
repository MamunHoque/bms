@extends('layouts.app')
@section('title', 'Utilities Report')
@section('content')
<div class="mb-6">
    <a href="{{ route('reports.index') }}" class="text-xs text-[var(--muted)]">← Reports</a>
    <h1 class="font-serif text-4xl mt-1">Utilities Report</h1>
</div>

<form method="GET" class="card p-4 mb-5 flex gap-3 items-end">
    <div><label class="label">From</label><input type="date" name="from" value="{{ $from }}" class="input"></div>
    <div><label class="label">To</label><input type="date" name="to" value="{{ $to }}" class="input"></div>
    <button class="btn btn-primary">Apply</button>
</form>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-5">
    <div class="card p-5">
        <h2 class="font-serif text-xl mb-4">By utility type</h2>
        <div style="position:relative; height:220px;"><canvas id="typeChart"></canvas></div>
    </div>
    <div class="card p-5">
        <h2 class="font-serif text-xl mb-4">Monthly totals</h2>
        <div style="position:relative; height:220px;"><canvas id="monthChart"></canvas></div>
    </div>
</div>

<div class="card overflow-hidden">
    <div class="p-5 border-b border-[var(--line)]"><h2 class="font-serif text-xl">Breakdown</h2></div>
    <table class="bms">
        <thead><tr><th>Utility</th><th>Consumption</th><th class="text-right">Amount</th></tr></thead>
        <tbody>
        @forelse($byType as $b)
            <tr>
                <td>{{ $b->utilityType->name }}</td>
                <td>{{ $b->consumption }} {{ $b->utilityType->unit_label }}</td>
                <td class="text-right font-semibold">@money($b->amount)</td>
            </tr>
        @empty
            <tr><td colspan="3" class="text-center text-[var(--muted)] py-6">No readings in range.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

@push('scripts')
<script>
Chart.defaults.font.family = 'Inter, sans-serif';
new Chart(document.getElementById('typeChart'), {
    type: 'doughnut',
    data: {
        labels: @json($byType->map(fn($b) => $b->utilityType->name)),
        datasets: [{
            data: @json($byType->pluck('amount')),
            backgroundColor: ['#0b0d10', '#d4471f', '#3b82f6', '#c9a227', '#16a34a', '#8b5cf6']
        }]
    },
    options: { responsive: true, maintainAspectRatio: false, cutout: '60%', plugins: { legend: { position: 'bottom' } } }
});
new Chart(document.getElementById('monthChart'), {
    type: 'bar',
    data: {
        labels: @json($monthly->pluck('ym')),
        datasets: [{ label: 'Total', data: @json($monthly->pluck('total')), backgroundColor: '#0b0d10', borderRadius: 6 }]
    },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } },
               scales: { y: { beginAtZero: true, ticks: { callback: v => (v/1000) + 'k' } } } }
});
</script>
@endpush
@endsection
