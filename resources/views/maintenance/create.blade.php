@extends('layouts.app')
@section('title', 'New Maintenance Request')
@section('content')
<h1 class="font-serif text-3xl mb-6">New Maintenance Request</h1>
<div class="card p-6"><form method="POST" action="{{ route('maintenance.store') }}">@include('maintenance._form')</form></div>
@endsection
