@php
    $map = [
        'paid' => 'badge-green',
        'unpaid' => 'badge-gray',
        'partial' => 'badge-amber',
        'overdue' => 'badge-red',
    ];
    $class = $map[$status] ?? 'badge-gray';
@endphp
<span class="badge {{ $class }}">{{ ucfirst($status) }}</span>
