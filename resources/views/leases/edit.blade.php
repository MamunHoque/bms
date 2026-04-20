@extends('layouts.app')
@section('title', 'Edit Lease')
@section('content')
<h1 class="font-serif text-3xl mb-6">Edit Lease</h1>
<div class="card p-6"><form method="POST" action="{{ route('leases.update', $lease) }}">@method('PUT')@include('leases._form')</form></div>
@endsection
