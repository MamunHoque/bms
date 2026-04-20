@extends('layouts.app')
@section('title', 'Utility Types')
@section('content')
<div class="mb-6 flex items-end justify-between">
    <div>
        <a href="{{ route('utilities.index') }}" class="text-xs text-[var(--muted)]">← Readings</a>
        <h1 class="font-serif text-4xl mt-1">Utility Types</h1>
        <p class="text-[var(--muted)]">Define each utility, its rate, and how it's charged.</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
    <div class="lg:col-span-2 card overflow-hidden">
        <table class="bms">
            <thead><tr><th>Name</th><th>Type</th><th>Rate / Fee</th><th>Unit</th><th>Active</th><th></th></tr></thead>
            <tbody>
            @forelse($types as $t)
                <tr>
                    <td class="font-medium">{{ $t->name }}</td>
                    <td>
                        @if($t->is_metered)<span class="badge badge-blue">Metered</span>
                        @else<span class="badge badge-gray">Flat fee</span>@endif
                    </td>
                    <td>
                        @if($t->is_metered) @money($t->rate_per_unit) / {{ $t->unit_label }}
                        @else @money($t->flat_fee) @endif
                    </td>
                    <td>{{ $t->unit_label ?: '—' }}</td>
                    <td>@if($t->active)<span class="badge badge-green">Yes</span>@else<span class="badge badge-gray">No</span>@endif</td>
                    <td class="text-right">
                        <button onclick="editType({{ $t->id }}, '{{ addslashes($t->name) }}', '{{ $t->unit_label }}', {{ $t->rate_per_unit }}, {{ $t->flat_fee }}, {{ $t->is_metered ? 1 : 0 }}, {{ $t->active ? 1 : 0 }})" class="btn btn-ghost text-xs">Edit</button>
                        <form action="{{ route('utilities.types.destroy', $t) }}" method="POST" class="inline" onsubmit="return confirm('Delete this type? Existing readings will be removed.');">
                            @csrf @method('DELETE')
                            <button class="btn btn-danger text-xs">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-[var(--muted)] py-6">No utility types defined.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="card p-5">
        <h2 class="font-serif text-xl mb-3" id="formTitle">Add utility type</h2>
        <form id="typeForm" method="POST" action="{{ route('utilities.types.store') }}" class="space-y-3">
            @csrf
            <input type="hidden" name="_method" id="methodField" value="POST">
            <div>
                <label class="label">Name *</label>
                <input name="name" id="nameField" class="input" required>
            </div>
            <div>
                <label class="label">Unit label (kWh, gal, cft…)</label>
                <input name="unit_label" id="unitField" class="input">
            </div>
            <div>
                <label class="label">Rate per unit (for metered)</label>
                <input type="number" step="0.0001" min="0" name="rate_per_unit" id="rateField" class="input" value="0">
            </div>
            <div>
                <label class="label">Flat fee (for non-metered)</label>
                <input type="number" step="0.01" min="0" name="flat_fee" id="feeField" class="input" value="0">
            </div>
            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="is_metered" id="meteredField" value="1" checked> Metered (uses readings)
            </label>
            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="active" id="activeField" value="1" checked> Active
            </label>
            <div class="flex gap-2">
                <button class="btn btn-primary">Save</button>
                <button type="button" onclick="resetForm()" class="btn btn-ghost">Reset</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function resetForm() {
    const form = document.getElementById('typeForm');
    form.action = "{{ route('utilities.types.store') }}";
    document.getElementById('methodField').value = 'POST';
    document.getElementById('formTitle').textContent = 'Add utility type';
    form.reset();
    document.getElementById('meteredField').checked = true;
    document.getElementById('activeField').checked = true;
}
function editType(id, name, unit, rate, fee, metered, active) {
    const form = document.getElementById('typeForm');
    form.action = "{{ url('utilities/types') }}/" + id;
    document.getElementById('methodField').value = 'PUT';
    document.getElementById('formTitle').textContent = 'Edit: ' + name;
    document.getElementById('nameField').value = name;
    document.getElementById('unitField').value = unit || '';
    document.getElementById('rateField').value = rate;
    document.getElementById('feeField').value = fee;
    document.getElementById('meteredField').checked = !!metered;
    document.getElementById('activeField').checked = !!active;
    window.scrollTo({top: 0, behavior: 'smooth'});
}
</script>
@endpush
@endsection
