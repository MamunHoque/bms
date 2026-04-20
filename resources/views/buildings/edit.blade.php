@extends('layouts.app')
@section('title', 'Edit Building')
@section('content')
<h1 class="font-serif text-3xl mb-6">Edit · {{ $building->name }}</h1>
<div class="card p-6">
    <form method="POST" action="{{ route('buildings.update', $building) }}">
        @method('PUT')
        @include('buildings._form')
    </form>
</div>
@endsection
