@extends('layouts.app')
@section('title', 'Occupancy Report')
@section('content')
<div class="mb-6">
    <a href="{{ route('reports.index') }}" class="text-xs text-[var(--muted)]">← Reports</a>
    <h1 class="font-serif text-4xl mt-1">Occupancy</h1>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5">
    <div class="card p-5">
        <p class="text-xs text-[var(--muted)]">Total units</p>
        <p class="kpi mt-2">{{ $totalUnits }}</p>
    </div>
    <div class="card p-5">
        <p class="text-xs text-[var(--muted)]">Occupied</p>
        <p class="kpi mt-2 text-green-700">{{ $occupied }}</p>
    </div>
    <div class="card p-5">
        <p class="text-xs text-[var(--muted)]">Occupancy rate</p>
        <p class="kpi mt-2">{{ $rate }}<span class="text-lg">%</span></p>
    </div>
</div>

<div class="card p-5 mb-5">
    <h2 class="font-serif text-xl mb-4">By building</h2>
    <div style="position:relative; height:220px;"><canvas id="bldChart"></canvas></div>
</div>

<div class="card overflow-hidden">
    <table class="bms">
        <thead><tr><th>Building</th><th>Total</th><th>Occupied</th><th>Vacant</th><th>Maintenance</th><th class="text-right">Rate</th></tr></thead>
        <tbody>
        @foreach($buildings as $b)
            @php $rate = $b->units_count > 0 ? round(($b->occupied_count / $b->units_count) * 100, 1) : 0; @endphp
            <tr>
                <td class="font-medium">{{ $b->name }}</td>
                <td>{{ $b->units_count }}</td>
                <td class="text-green-700">{{ $b->occupied_count }}</td>
                <td>{{ $b->vacant_count }}</td>
                <td>{{ $b->maintenance_count }}</td>
                <td class="text-right font-semibold">{{ $rate }}%</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

@push('scripts')
<script>
Chart.defaults.font.family = 'Inter, sans-serif';
new Chart(document.getElementById('bldChart'), {
    type: 'bar',
    data: {
        labels: @json($buildings->pluck('name')),
        datasets: [
            { label: 'Occupied', data: @json($buildings->pluck('occupied_count')), backgroundColor: '#16a34a', stack: 's' },
            { label: 'Vacant', data: @json($buildings->pluck('vacant_count')), backgroundColor: '#9ca3af', stack: 's' },
            { label: 'Maintenance', data: @json($buildings->pluck('maintenance_count')), backgroundColor: '#d4471f', stack: 's' }
        ]
    },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } },
               scales: { x: { stacked: true }, y: { stacked: true, beginAtZero: true } } }
});
</script>
@endpush
@endsection
