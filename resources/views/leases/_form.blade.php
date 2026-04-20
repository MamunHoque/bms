@csrf
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label class="label">Tenant *</label>
        <select name="tenant_id" class="select" required>
            <option value="">— Select tenant —</option>
            @foreach($tenants as $t)
                <option value="{{ $t->id }}" @selected(old('tenant_id', $lease->tenant_id) == $t->id)>{{ $t->name }} @if($t->phone) ({{ $t->phone }}) @endif</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="label">Unit *</label>
        <select name="unit_id" class="select" required>
            <option value="">— Select unit —</option>
            @foreach($units as $u)
                <option value="{{ $u->id }}" @selected(old('unit_id', $lease->unit_id) == $u->id)>{{ $u->building->name }} — #{{ $u->unit_number }} (@moneyplain($u->base_rent)/mo)</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="label">Start date *</label>
        <input type="date" name="start_date" value="{{ old('start_date', optional($lease->start_date)->toDateString()) }}" class="input" required>
    </div>
    <div>
        <label class="label">End date</label>
        <input type="date" name="end_date" value="{{ old('end_date', optional($lease->end_date)->toDateString()) }}" class="input">
    </div>
    <div>
        <label class="label">Monthly rent ({{ config('app.currency_symbol') }}) *</label>
        <input type="number" step="0.01" min="0" name="monthly_rent" value="{{ old('monthly_rent', $lease->monthly_rent) }}" class="input" required>
    </div>
    <div>
        <label class="label">Security deposit</label>
        <input type="number" step="0.01" min="0" name="security_deposit" value="{{ old('security_deposit', $lease->security_deposit ?? 0) }}" class="input">
    </div>
    <div>
        <label class="label">Rent due day *</label>
        <input type="number" min="1" max="28" name="rent_due_day" value="{{ old('rent_due_day', $lease->rent_due_day ?? 5) }}" class="input" required>
        <p class="text-xs text-[var(--muted)] mt-1">Day of month the rent is due (1–28)</p>
    </div>
    <div>
        <label class="label">Status *</label>
        <select name="status" class="select" required>
            @foreach(['active', 'ended', 'terminated'] as $s)
                <option value="{{ $s }}" @selected(old('status', $lease->status) === $s)>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
    </div>
    <div class="md:col-span-2">
        <label class="label">Notes</label>
        <textarea name="notes" rows="2" class="textarea">{{ old('notes', $lease->notes) }}</textarea>
    </div>
</div>
<div class="mt-6 flex gap-2">
    <button class="btn btn-primary">Save</button>
    <a href="{{ route('leases.index') }}" class="btn btn-ghost">Cancel</a>
</div>
