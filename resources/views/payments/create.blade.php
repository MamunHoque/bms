@extends('layouts.app')
@section('title', 'Record Payment')
@section('content')
<h1 class="font-serif text-3xl mb-6">Record Payment</h1>
<div class="card p-6">
    <form method="POST" action="{{ route('payments.store') }}">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <label class="label">Invoice *</label>
                <select name="invoice_id" class="select" required>
                    <option value="">— Select outstanding invoice —</option>
                    @foreach($invoices as $i)
                        <option value="{{ $i->id }}" @selected(old('invoice_id', $preselect) == $i->id)>
                            {{ $i->invoice_number }} · {{ $i->lease->tenant->name }} · {{ $i->lease->unit->building->name }} #{{ $i->lease->unit->unit_number }} · Due @moneyplain($i->total - $i->paid_amount)
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label">Amount ({{ config('app.currency_symbol') }}) *</label>
                <input type="number" step="0.01" min="0.01" name="amount" value="{{ old('amount') }}" class="input" required>
            </div>
            <div>
                <label class="label">Paid on *</label>
                <input type="date" name="paid_on" value="{{ old('paid_on', now()->toDateString()) }}" class="input" required>
            </div>
            <div>
                <label class="label">Method *</label>
                <select name="method" class="select" required>
                    @foreach(['cash','bkash','nagad','bank','card','other'] as $m)
                        <option value="{{ $m }}" @selected(old('method') === $m)>{{ ucfirst($m) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label">Reference / Trx ID</label>
                <input name="reference" value="{{ old('reference') }}" class="input" placeholder="Optional">
            </div>
            <div class="md:col-span-2">
                <label class="label">Notes</label>
                <textarea name="notes" rows="2" class="textarea">{{ old('notes') }}</textarea>
            </div>
        </div>
        <div class="mt-6 flex gap-2">
            <button class="btn btn-accent">Record Payment</button>
            <a href="{{ route('payments.index') }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>
@endsection
