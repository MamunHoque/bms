@extends('layouts.app')
@section('title', 'Dues Report')
@section('content')
<div class="mb-6">
    <a href="{{ route('reports.index') }}" class="text-xs text-[var(--muted)]">← Reports</a>
    <h1 class="font-serif text-4xl mt-1">Dues &amp; Aging</h1>
</div>

<form method="GET" class="card p-4 mb-5 flex gap-3 items-end">
    <div>
        <label class="label">Building</label>
        <select name="building_id" class="select">
            <option value="">All</option>
            @foreach($buildings as $b)
                <option value="{{ $b->id }}" @selected(request('building_id') == $b->id)>{{ $b->name }}</option>
            @endforeach
        </select>
    </div>
    <button class="btn btn-primary">Apply</button>
</form>

<div class="card p-6 mb-5">
    <p class="text-xs uppercase tracking-widest text-[var(--muted)]">Total outstanding</p>
    <p class="kpi mt-2 text-[var(--accent)]">@money($totalDue)</p>
    <p class="text-sm text-[var(--muted)] mt-1">{{ $dues->count() }} invoice(s) with balance</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-5 gap-3 mb-5">
    @php
        $bucketLabels = [
            'current' => ['Current', 'badge-blue'],
            '1_30' => ['1–30 days', 'badge-gray'],
            '31_60' => ['31–60 days', 'badge-amber'],
            '61_90' => ['61–90 days', 'badge-amber'],
            'over_90' => ['Over 90 days', 'badge-red'],
        ];
    @endphp
    @foreach($buckets as $key => $value)
        <div class="card p-4">
            <span class="badge {{ $bucketLabels[$key][1] }}">{{ $bucketLabels[$key][0] }}</span>
            <p class="kpi mt-2">@money($value)</p>
        </div>
    @endforeach
</div>

<div class="card p-5 mb-5">
    <h2 class="font-serif text-xl mb-4">Aging distribution</h2>
    <div style="position:relative; height:220px;"><canvas id="agingChart"></canvas></div>
</div>

<div class="card overflow-hidden">
    <div class="p-5 border-b border-[var(--line)]"><h2 class="font-serif text-xl">Outstanding invoices</h2></div>
    <table class="bms">
        <thead><tr><th>Invoice</th><th>Tenant</th><th>Unit</th><th>Due date</th><th>Days late</th><th class="text-right">Amount due</th></tr></thead>
        <tbody>
        @php $now = \Carbon\Carbon::now(); @endphp
        @forelse($dues as $inv)
            @php $days = $inv->due_date->isFuture() ? 0 : (int) abs($inv->due_date->diffInDays($now)); @endphp
            <tr>
                <td><a href="{{ route('invoices.show', $inv) }}" class="font-mono text-sm">{{ $inv->invoice_number }}</a></td>
                <td>{{ $inv->lease->tenant->name ?? '—' }}</td>
                <td class="text-[var(--muted)] text-xs">{{ $inv->lease->unit->building->name ?? '' }} #{{ $inv->lease->unit->unit_number ?? '' }}</td>
                <td>@bmsdate($inv->due_date)</td>
                <td class="{{ $days > 30 ? 'text-red-600 font-medium' : '' }}">{{ $days }} days</td>
                <td class="text-right font-semibold text-[var(--accent)]">@money($inv->total - $inv->paid_amount)</td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-[var(--muted)] py-6">No outstanding invoices. 🎉</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

@push('scripts')
<script>
Chart.defaults.font.family = 'Inter, sans-serif';
new Chart(document.getElementById('agingChart'), {
    type: 'bar',
    data: {
        labels: ['Current', '1–30 days', '31–60 days', '61–90 days', 'Over 90 days'],
        datasets: [{
            label: 'Amount outstanding',
            data: [{{ $buckets['current'] }}, {{ $buckets['1_30'] }}, {{ $buckets['31_60'] }}, {{ $buckets['61_90'] }}, {{ $buckets['over_90'] }}],
            backgroundColor: ['#3b82f6', '#9ca3af', '#f59e0b', '#f97316', '#dc2626'],
            borderRadius: 6
        }]
    },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } },
               scales: { y: { beginAtZero: true, ticks: { callback: v => (v/1000) + 'k' } } } }
});
</script>
@endpush
@endsection
