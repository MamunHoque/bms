@extends('layouts.app')
@section('title', 'Edit Maintenance')
@section('content')
<h1 class="font-serif text-3xl mb-6">Edit · {{ $request->title }}</h1>
<div class="card p-6"><form method="POST" action="{{ route('maintenance.update', $request) }}">@method('PUT')@include('maintenance._form')</form></div>
@endsection
