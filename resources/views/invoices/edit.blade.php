@extends('layouts.app')
@section('title', 'Edit Invoice')
@section('content')
<h1 class="font-serif text-3xl mb-6">Edit · {{ $invoice->invoice_number }}</h1>
<div class="card p-6">
    <form method="POST" action="{{ route('invoices.update', $invoice) }}">
        @csrf @method('PUT')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="label">Due date *</label>
                <input type="date" name="due_date" value="{{ $invoice->due_date->toDateString() }}" class="input" required>
            </div>
            <div>
                <label class="label">Late fee ({{ config('app.currency_symbol') }})</label>
                <input type="number" step="0.01" min="0" name="late_fee" value="{{ $invoice->late_fee }}" class="input">
            </div>
            <div class="md:col-span-2">
                <label class="label">Notes</label>
                <textarea name="notes" rows="3" class="textarea">{{ $invoice->notes }}</textarea>
            </div>
        </div>
        <p class="text-xs text-[var(--muted)] mt-3">Line items are generated from the lease and utility readings. To change rent or utility charges, edit those records instead.</p>
        <div class="mt-6 flex gap-2">
            <button class="btn btn-primary">Save</button>
            <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>
@endsection
