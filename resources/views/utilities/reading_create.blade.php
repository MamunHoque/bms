@extends('layouts.app')
@section('title', 'Record Reading')
@section('content')
<h1 class="font-serif text-3xl mb-6">Record Utility Reading</h1>
<div class="card p-6">
    <form method="POST" action="{{ route('utilities.readings.store') }}">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="label">Unit *</label>
                <select name="unit_id" class="select" required>
                    <option value="">— Select —</option>
                    @foreach($units as $u)
                        <option value="{{ $u->id }}">{{ $u->building->name }} — #{{ $u->unit_number }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label">Utility *</label>
                <select name="utility_type_id" class="select" required>
                    <option value="">— Select —</option>
                    @foreach($types as $t)
                        <option value="{{ $t->id }}" data-metered="{{ $t->is_metered ? 1 : 0 }}" data-fee="{{ $t->flat_fee }}">{{ $t->name }} ({{ $t->is_metered ? $t->unit_label : 'flat' }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label">Period month *</label>
                <input type="month" name="period_month" value="{{ now()->format('Y-m') }}" class="input" required>
            </div>
            <div class="metered-only">
                <label class="label">Previous reading</label>
                <input type="number" step="0.01" min="0" name="previous_reading" value="0" class="input">
            </div>
            <div class="metered-only">
                <label class="label">Current reading</label>
                <input type="number" step="0.01" min="0" name="current_reading" value="0" class="input">
            </div>
            <div class="flat-only" style="display:none">
                <label class="label">Amount ({{ config('app.currency_symbol') }})</label>
                <input type="number" step="0.01" min="0" name="amount" value="0" class="input">
            </div>
            <div class="md:col-span-2">
                <label class="label">Notes</label>
                <textarea name="notes" rows="2" class="textarea"></textarea>
            </div>
        </div>
        <p class="text-xs text-[var(--muted)] mt-3">For metered utilities, consumption and amount are calculated automatically from previous/current readings × rate. For flat utilities, enter the amount directly.</p>
        <div class="mt-6 flex gap-2">
            <button class="btn btn-primary">Save Reading</button>
            <a href="{{ route('utilities.index') }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>

@push('scripts')
<script>
const sel = document.querySelector('select[name="utility_type_id"]');
sel.addEventListener('change', () => {
    const opt = sel.options[sel.selectedIndex];
    const metered = opt.dataset.metered === '1';
    document.querySelectorAll('.metered-only').forEach(e => e.style.display = metered ? '' : 'none');
    document.querySelectorAll('.flat-only').forEach(e => e.style.display = metered ? 'none' : '');
});
</script>
@endpush
@endsection
