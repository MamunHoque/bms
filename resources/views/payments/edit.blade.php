@extends('layouts.app')
@section('title', 'Edit Payment')
@section('content')
<h1 class="font-serif text-3xl mb-6">Edit Payment</h1>
<p class="text-[var(--muted)] mb-4 -mt-4">For invoice <a href="{{ route('invoices.show', $payment->invoice) }}" class="font-mono">{{ $payment->invoice->invoice_number }}</a></p>
<div class="card p-6">
    <form method="POST" action="{{ route('payments.update', $payment) }}">
        @csrf @method('PUT')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="label">Amount *</label>
                <input type="number" step="0.01" min="0.01" name="amount" value="{{ $payment->amount }}" class="input" required>
            </div>
            <div>
                <label class="label">Paid on *</label>
                <input type="date" name="paid_on" value="{{ $payment->paid_on->toDateString() }}" class="input" required>
            </div>
            <div>
                <label class="label">Method *</label>
                <select name="method" class="select" required>
                    @foreach(['cash','bkash','nagad','bank','card','other'] as $m)
                        <option value="{{ $m }}" @selected($payment->method === $m)>{{ ucfirst($m) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label">Reference</label>
                <input name="reference" value="{{ $payment->reference }}" class="input">
            </div>
            <div class="md:col-span-2">
                <label class="label">Notes</label>
                <textarea name="notes" rows="2" class="textarea">{{ $payment->notes }}</textarea>
            </div>
        </div>
        <div class="mt-6 flex gap-2">
            <button class="btn btn-primary">Save</button>
            <a href="{{ route('payments.index') }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>
@endsection
