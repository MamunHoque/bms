@csrf
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label class="label">Unit *</label>
        <select name="unit_id" class="select" required>
            <option value="">— Select unit —</option>
            @foreach($units as $u)
                <option value="{{ $u->id }}" @selected(old('unit_id', $request->unit_id) == $u->id)>{{ $u->building->name }} — #{{ $u->unit_number }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="label">Tenant</label>
        <select name="tenant_id" class="select">
            <option value="">— None —</option>
            @foreach($tenants as $t)
                <option value="{{ $t->id }}" @selected(old('tenant_id', $request->tenant_id) == $t->id)>{{ $t->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="md:col-span-2">
        <label class="label">Title *</label>
        <input name="title" value="{{ old('title', $request->title) }}" class="input" required>
    </div>
    <div class="md:col-span-2">
        <label class="label">Description</label>
        <textarea name="description" rows="3" class="textarea">{{ old('description', $request->description) }}</textarea>
    </div>
    <div>
        <label class="label">Priority *</label>
        <select name="priority" class="select" required>
            @foreach(['low', 'normal', 'high', 'urgent'] as $p)
                <option value="{{ $p }}" @selected(old('priority', $request->priority) === $p)>{{ ucfirst($p) }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="label">Status *</label>
        <select name="status" class="select" required>
            @foreach(['open', 'in_progress', 'resolved', 'cancelled'] as $s)
                <option value="{{ $s }}" @selected(old('status', $request->status) === $s)>{{ ucfirst(str_replace('_',' ', $s)) }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="label">Reported on *</label>
        <input type="date" name="reported_on" value="{{ old('reported_on', optional($request->reported_on)->toDateString()) }}" class="input" required>
    </div>
    <div>
        <label class="label">Resolved on</label>
        <input type="date" name="resolved_on" value="{{ old('resolved_on', optional($request->resolved_on)->toDateString()) }}" class="input">
    </div>
    <div>
        <label class="label">Cost ({{ config('app.currency_symbol') }})</label>
        <input type="number" step="0.01" min="0" name="cost" value="{{ old('cost', $request->cost ?? 0) }}" class="input">
    </div>
</div>
<div class="mt-6 flex gap-2">
    <button class="btn btn-primary">Save</button>
    <a href="{{ route('maintenance.index') }}" class="btn btn-ghost">Cancel</a>
</div>
