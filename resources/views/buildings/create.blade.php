@extends('layouts.app')
@section('title', 'New Building')
@section('content')
<h1 class="font-serif text-3xl mb-6">New Building</h1>
<div class="card p-6">
    <form method="POST" action="{{ route('buildings.store') }}">
        @include('buildings._form')
    </form>
</div>
@endsection
