@extends('layouts.app')
@section('title', 'New Unit')
@section('content')
<h1 class="font-serif text-3xl mb-6">New Unit</h1>
<div class="card p-6">
    <form method="POST" action="{{ route('units.store') }}">@include('units._form')</form>
</div>
@endsection
