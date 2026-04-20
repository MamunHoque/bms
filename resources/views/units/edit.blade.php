@extends('layouts.app')
@section('title', 'Edit Unit')
@section('content')
<h1 class="font-serif text-3xl mb-6">Edit Unit #{{ $unit->unit_number }}</h1>
<div class="card p-6">
    <form method="POST" action="{{ route('units.update', $unit) }}">
        @method('PUT')
        @include('units._form')
    </form>
</div>
@endsection
