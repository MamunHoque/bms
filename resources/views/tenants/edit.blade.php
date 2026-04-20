@extends('layouts.app')
@section('title', 'Edit Tenant')
@section('content')
<h1 class="font-serif text-3xl mb-6">Edit · {{ $tenant->name }}</h1>
<div class="card p-6"><form method="POST" action="{{ route('tenants.update', $tenant) }}">@method('PUT')@include('tenants._form')</form></div>
@endsection
