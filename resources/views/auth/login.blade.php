@extends('layouts.app')
@section('title', 'Sign in')
@section('content')
<div class="min-h-screen flex items-center justify-center px-4" style="background: linear-gradient(135deg, #f6f5f1 0%, #ece9e2 100%);">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <div class="inline-flex items-center gap-2 mb-2">
                <div class="accent-dot"></div>
                <span class="font-serif text-3xl">BMS</span>
            </div>
            <h1 class="font-serif text-4xl leading-tight">Building Management</h1>
            <p class="text-sm text-[var(--muted)] mt-2">Sign in to manage your properties</p>
        </div>

        <div class="card p-7">
            <form method="POST" action="{{ route('login.submit') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="label">Email</label>
                    <input type="email" name="email" value="{{ old('email', 'admin@bms.local') }}" class="input" required autofocus>
                </div>
                <div>
                    <label class="label">Password</label>
                    <input type="password" name="password" value="password" class="input" required>
                </div>
                <label class="flex items-center gap-2 text-sm text-[var(--muted)]">
                    <input type="checkbox" name="remember" class="rounded"> Remember me
                </label>
                <button type="submit" class="btn btn-primary w-full justify-center">Sign in →</button>
            </form>
        </div>

        <div class="mt-6 text-center text-xs text-[var(--muted)]">
            Demo account: <span class="font-mono">admin@bms.local</span> / <span class="font-mono">password</span>
        </div>
    </div>
</div>
@endsection
