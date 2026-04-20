@extends('layouts.app')
@section('title', 'Add Tenant')
@section('content')
<h1 class="font-serif text-3xl mb-6">Add Tenant</h1>
<div class="card p-6"><form method="POST" action="{{ route('tenants.store') }}">@include('tenants._form')</form></div>
@endsection
