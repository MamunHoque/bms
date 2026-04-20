@extends('layouts.app')
@section('title', 'Collection Report')
@section('content')
<div class="mb-6">
    <a href="{{ route('reports.index') }}" class="text-xs text-[var(--muted)]">← Reports</a>
    <h1 class="font-serif text-4xl mt-1">Collection Report</h1>
</div>

<form method="GET" class="card p-4 mb-5 flex flex-wrap gap-3 items-end">
    <div>
        <label class="label">From</label>
        <input type="date" name="from" value="{{ $from }}" class="input">
    </div>
    <div>
        <label class="label">To</label>
        <input type="date" name="to" value="{{ $to }}" class="input">
    </div>
    <button class="btn btn-primary">Apply</button>
</form>

<div class="card p-6 mb-5">
    <p class="text-xs uppercase tracking-widest text-[var(--muted)]">Total collected</p>
    <p class="kpi mt-2 text-green-700">@money($totalCollected)</p>
    <p class="text-sm text-[var(--muted)] mt-1">{{ \Carbon\Carbon::parse($from)->format('d M Y') }} – {{ \Carbon\Carbon::parse($to)->format('d M Y') }}</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-5">
    <div class="card p-5 lg:col-span-2">
        <h2 class="font-serif text-xl mb-4">Monthly collection</h2>
        <div style="position:relative; height:220px;"><canvas id="monthlyChart"></canvas></div>
    </div>
    <div class="card p-5">
        <h2 class="font-serif text-xl mb-4">By method</h2>
        <div style="position:relative; height:220px;"><canvas id="methodChart"></canvas></div>
    </div>
</div>

<div class="card overflow-hidden">
    <div class="p-5 border-b border-[var(--line)]"><h2 class="font-serif text-xl">Payment methods breakdown</h2></div>
    <table class="bms">
        <thead><tr><th>Method</th><th>Count</th><th class="text-right">Total</th></tr></thead>
        <tbody>
        @forelse($methods as $m)
            <tr>
                <td><span class="badge badge-blue">{{ ucfirst($m->method) }}</span></td>
                <td>{{ $m->cnt }}</td>
                <td class="text-right font-semibold">@money($m->total)</td>
            </tr>
        @empty
            <tr><td colspan="3" class="text-center text-[var(--muted)] py-6">No payments in range.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

@push('scripts')
<script>
Chart.defaults.font.family = 'Inter, sans-serif';
Chart.defaults.color = '#6b6b6b';
new Chart(document.getElementById('monthlyChart'), {
    type: 'line',
    data: {
        labels: @json($payments->pluck('ym')),
        datasets: [{
            label: 'Collected',
            data: @json($payments->pluck('total')),
            borderColor: '#d4471f',
            backgroundColor: 'rgba(212,71,31,0.1)',
            tension: 0.3, fill: true, pointRadius: 5, pointBackgroundColor: '#d4471f'
        }]
    },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } },
               scales: { y: { beginAtZero: true, ticks: { callback: v => (v/1000) + 'k' } } } }
});
new Chart(document.getElementById('methodChart'), {
    type: 'doughnut',
    data: {
        labels: @json($methods->pluck('method')),
        datasets: [{
            data: @json($methods->pluck('total')),
            backgroundColor: ['#0b0d10', '#d4471f', '#6b6b6b', '#c9a227', '#3b82f6', '#8b5cf6']
        }]
    },
    options: { responsive: true, maintainAspectRatio: false, cutout: '60%', plugins: { legend: { position: 'bottom' } } }
});
</script>
@endpush
@endsection
