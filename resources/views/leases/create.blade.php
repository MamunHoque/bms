@extends('layouts.app')
@section('title', 'New Lease')
@section('content')
<h1 class="font-serif text-3xl mb-6">New Lease</h1>
@if($units->isEmpty())
    <div class="card p-6 text-center">
        <p class="text-[var(--muted)]">No vacant units available. <a href="{{ route('units.create') }}" class="text-[var(--accent)]">Add a unit</a> or free one up first.</p>
    </div>
@else
    <div class="card p-6"><form method="POST" action="{{ route('leases.store') }}">@include('leases._form')</form></div>
@endif
@endsection
