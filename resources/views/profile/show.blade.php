@extends('layouts.app')
@section('title', 'My Profile')

@section('content')
<div class="mb-6">
    <h1 class="font-serif text-3xl">My Profile</h1>
    <p class="text-sm text-[var(--muted)] mt-1">Manage your account details and password</p>
</div>

<div class="grid md:grid-cols-2 gap-6">
    {{-- Profile Information --}}
    <div class="card p-6">
        <div class="flex items-center gap-3 mb-5">
            <div class="w-12 h-12 rounded-full bg-[var(--ink)] text-white flex items-center justify-center text-lg font-semibold">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <div>
                <h2 class="font-semibold text-lg leading-tight">Profile Information</h2>
                <p class="text-xs text-[var(--muted)]">Update your name and email address</p>
            </div>
        </div>

        <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
            @csrf @method('PUT')

            <div>
                <label class="label" for="profile-name">Full Name</label>
                <input type="text" id="profile-name" name="name" value="{{ old('name', $user->name) }}" class="input" required>
                @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="label" for="profile-email">Email Address</label>
                <input type="email" id="profile-email" name="email" value="{{ old('email', $user->email) }}" class="input" required>
                @error('email') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <span class="text-xs text-[var(--muted)]">Member since {{ $user->created_at?->format('M d, Y') ?? '—' }}</span>
            </div>
        </form>
    </div>

    {{-- Change Password --}}
    <div class="card p-6">
        <div class="flex items-center gap-3 mb-5">
            <div class="w-12 h-12 rounded-full bg-[var(--accent)] text-white flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <div>
                <h2 class="font-semibold text-lg leading-tight">Change Password</h2>
                <p class="text-xs text-[var(--muted)]">Ensure your account uses a strong password</p>
            </div>
        </div>

        <form method="POST" action="{{ route('profile.password') }}" class="space-y-4">
            @csrf @method('PUT')

            <div>
                <label class="label" for="current-password">Current Password</label>
                <input type="password" id="current-password" name="current_password" class="input" required autocomplete="current-password">
                @error('current_password') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="label" for="new-password">New Password</label>
                <input type="password" id="new-password" name="password" class="input" required autocomplete="new-password">
                @error('password') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="label" for="confirm-password">Confirm New Password</label>
                <input type="password" id="confirm-password" name="password_confirmation" class="input" required autocomplete="new-password">
            </div>

            <div class="pt-2">
                <button type="submit" class="btn btn-accent">Update Password</button>
            </div>
        </form>
    </div>
</div>

{{-- Account Meta --}}
<div class="card p-5 mt-6">
    <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-sm">
        <div>
            <span class="text-[var(--muted)]">Role:</span>
            <span class="badge badge-blue ml-1 capitalize">{{ $user->role }}</span>
        </div>
        <div>
            <span class="text-[var(--muted)]">Email Verified:</span>
            @if($user->email_verified_at)
                <span class="badge badge-green ml-1">{{ $user->email_verified_at->format('M d, Y') }}</span>
            @else
                <span class="badge badge-amber ml-1">Not verified</span>
            @endif
        </div>
        <div>
            <span class="text-[var(--muted)]">Last Updated:</span>
            <span class="ml-1 font-medium">{{ $user->updated_at?->format('M d, Y h:i A') ?? '—' }}</span>
        </div>
    </div>
</div>
@endsection
