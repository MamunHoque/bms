@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
<div class="mb-6 flex items-end justify-between flex-wrap gap-4">
    <div>
        <p class="text-xs uppercase tracking-widest text-[var(--muted)]">Overview</p>
        <h1 class="font-serif text-4xl leading-tight">Good day, {{ auth()->user()->name }}.</h1>
        <p class="text-sm text-[var(--muted)] mt-1">{{ now()->format('l, d F Y') }}</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('invoices.create') }}" class="btn btn-ghost">+ Invoice</a>
        <a href="{{ route('payments.create') }}" class="btn btn-accent">+ Record Payment</a>
    </div>
</div>

{{-- KPI Row --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="card p-5">
        <p class="text-xs uppercase tracking-wider text-[var(--muted)]">Invoiced this month</p>
        <p class="kpi mt-2">@money($monthInvoiced)</p>
        <p class="text-xs text-[var(--muted)] mt-1">{{ now()->format('F Y') }}</p>
    </div>
    <div class="card p-5">
        <p class="text-xs uppercase tracking-wider text-[var(--muted)]">Collected this month</p>
        <p class="kpi mt-2 text-green-700">@money($monthCollected)</p>
        <p class="text-xs text-[var(--muted)] mt-1">{{ $collectionRate }}% collection rate</p>
    </div>
    <div class="card p-5">
        <p class="text-xs uppercase tracking-wider text-[var(--muted)]">Outstanding (all)</p>
        <p class="kpi mt-2 text-[var(--accent)]">@money($totalOutstanding)</p>
        <p class="text-xs text-[var(--muted)] mt-1">{{ $overdueCount }} overdue invoice(s)</p>
    </div>
    <div class="card p-5">
        <p class="text-xs uppercase tracking-wider text-[var(--muted)]">Occupancy</p>
        <p class="kpi mt-2">{{ $occupancyRate }}<span class="text-lg">%</span></p>
        <p class="text-xs text-[var(--muted)] mt-1">{{ $occupiedUnits }} of {{ $totalUnits }} units</p>
    </div>
</div>

{{-- Secondary stats --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="card p-4">
        <p class="text-xs text-[var(--muted)]">Buildings</p>
        <p class="text-2xl font-semibold mt-1">{{ $totalBuildings }}</p>
    </div>
    <div class="card p-4">
        <p class="text-xs text-[var(--muted)]">Units (vacant)</p>
        <p class="text-2xl font-semibold mt-1">{{ $vacantUnits }}</p>
    </div>
    <div class="card p-4">
        <p class="text-xs text-[var(--muted)]">Tenants</p>
        <p class="text-2xl font-semibold mt-1">{{ $totalTenants }}</p>
    </div>
    <div class="card p-4">
        <p class="text-xs text-[var(--muted)]">Active leases</p>
        <p class="text-2xl font-semibold mt-1">{{ $activeLeases }}</p>
    </div>
</div>

{{-- Charts --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-6">
    <div class="card p-5 lg:col-span-2">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-serif text-xl">Invoiced vs Collected</h2>
            <span class="text-xs text-[var(--muted)]">Last 6 months</span>
        </div>
        <div style="position:relative; height:220px;"><canvas id="trendChart"></canvas></div>
    </div>
    <div class="card p-5">
        <h2 class="font-serif text-xl mb-4">Payment methods</h2>
        <p class="text-xs text-[var(--muted)] -mt-3 mb-3">Last 30 days</p>
        <div style="position:relative; height:220px;"><canvas id="methodChart"></canvas></div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-6">
    <div class="card p-5">
        <h2 class="font-serif text-xl mb-4">Invoice status</h2>
        <div style="position:relative; height:220px;"><canvas id="statusChart"></canvas></div>
    </div>
    <div class="card p-5 lg:col-span-2">
        <div class="flex items-center justify-between mb-3">
            <h2 class="font-serif text-xl">Overdue invoices</h2>
            <a href="{{ route('reports.dues') }}" class="text-xs text-[var(--accent)]">View all →</a>
        </div>
        @if($overdueInvoices->isEmpty())
            <p class="text-sm text-[var(--muted)] py-4">No overdue invoices. Nicely done.</p>
        @else
        <div class="overflow-x-auto">
        <table class="bms">
            <thead><tr><th>Invoice</th><th>Tenant</th><th>Due</th><th>Amount due</th></tr></thead>
            <tbody>
                @foreach($overdueInvoices as $inv)
                <tr>
                    <td><a href="{{ route('invoices.show', $inv) }}" class="font-mono text-sm">{{ $inv->invoice_number }}</a></td>
                    <td>{{ $inv->lease->tenant->name ?? '—' }} <span class="text-xs text-[var(--muted)]">· {{ $inv->lease->unit->building->name }} #{{ $inv->lease->unit->unit_number }}</span></td>
                    <td class="text-red-600">@bmsdate($inv->due_date)</td>
                    <td class="font-semibold">@money($inv->total - $inv->paid_amount)</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
        @endif
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
    <div class="card p-5">
        <h2 class="font-serif text-xl mb-3">Recent invoices</h2>
        <div class="divide-y divide-[var(--line)]">
            @forelse($recentInvoices as $inv)
                <div class="py-3 flex items-center justify-between">
                    <div>
                        <a href="{{ route('invoices.show', $inv) }}" class="font-mono text-sm font-medium">{{ $inv->invoice_number }}</a>
                        <p class="text-xs text-[var(--muted)]">{{ $inv->lease->tenant->name ?? '—' }} · {{ $inv->period_month->format('M Y') }}</p>
                    </div>
                    <div class="text-right">
                        <p class="font-semibold">@money($inv->total)</p>
                        @include('invoices._status', ['status' => $inv->status])
                    </div>
                </div>
            @empty
                <p class="text-sm text-[var(--muted)] py-4">No invoices yet.</p>
            @endforelse
        </div>
    </div>
    <div class="card p-5">
        <h2 class="font-serif text-xl mb-3">Recent payments</h2>
        <div class="divide-y divide-[var(--line)]">
            @forelse($recentPayments as $p)
                <div class="py-3 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium">{{ $p->invoice->lease->tenant->name ?? '—' }}</p>
                        <p class="text-xs text-[var(--muted)]">{{ ucfirst($p->method) }} · @bmsdate($p->paid_on)</p>
                    </div>
                    <p class="font-semibold text-green-700">@money($p->amount)</p>
                </div>
            @empty
                <p class="text-sm text-[var(--muted)] py-4">No payments yet.</p>
            @endforelse
        </div>
    </div>
</div>

@push('scripts')
@php
    $statusColors = collect(array_keys($statusCounts))->map(fn($k) => match($k) {
        'paid'    => '#16a34a',
        'partial' => '#d4471f',
        'overdue' => '#dc2626',
        default   => '#6b7280',
    })->values()->toArray();
@endphp
<script>
Chart.defaults.font.family = 'Inter, sans-serif';
Chart.defaults.color = '#6b6b6b';
Chart.defaults.borderColor = '#e7e5df';

new Chart(document.getElementById('trendChart'), {
    type: 'bar',
    data: {
        labels: @json($labels),
        datasets: [
            { label: 'Invoiced', data: @json($invoicedSeries), backgroundColor: '#0b0d10', borderRadius: 6 },
            { label: 'Collected', data: @json($collectedSeries), backgroundColor: '#d4471f', borderRadius: 6 }
        ]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, padding: 15 } } },
        scales: { y: { beginAtZero: true, ticks: { callback: v => (v/1000) + 'k' } } }
    }
});

new Chart(document.getElementById('methodChart'), {
    type: 'doughnut',
    data: {
        labels: @json(array_keys($methodBreakdown)),
        datasets: [{
            data: @json(array_values($methodBreakdown)),
            backgroundColor: ['#0b0d10', '#d4471f', '#6b6b6b', '#c9a227', '#3b82f6', '#8b5cf6']
        }]
    },
    options: { responsive: true, maintainAspectRatio: false, cutout: '65%', plugins: { legend: { position: 'bottom' } } }
});

new Chart(document.getElementById('statusChart'), {
    type: 'doughnut',
    data: {
        labels: @json(array_keys($statusCounts)),
        datasets: [{
            data: @json(array_values($statusCounts)),
            backgroundColor: @json($statusColors)
        }]
    },
    options: { responsive: true, maintainAspectRatio: false, cutout: '65%', plugins: { legend: { position: 'bottom' } } }
});
</script>
@endpush
@endsection
