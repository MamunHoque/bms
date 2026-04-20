@csrf
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label class="label">Building *</label>
        <select name="building_id" class="select" required>
            @foreach($buildings as $b)
                <option value="{{ $b->id }}" @selected(old('building_id', $unit->building_id) == $b->id)>{{ $b->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="label">Unit number *</label>
        <input name="unit_number" value="{{ old('unit_number', $unit->unit_number) }}" class="input" required>
    </div>
    <div>
        <label class="label">Floor *</label>
        <input type="number" min="0" name="floor" value="{{ old('floor', $unit->floor) }}" class="input" required>
    </div>
    <div>
        <label class="label">Size (sqft)</label>
        <input type="number" min="0" name="size_sqft" value="{{ old('size_sqft', $unit->size_sqft) }}" class="input">
    </div>
    <div>
        <label class="label">Bedrooms *</label>
        <input type="number" min="0" name="bedrooms" value="{{ old('bedrooms', $unit->bedrooms) }}" class="input" required>
    </div>
    <div>
        <label class="label">Bathrooms *</label>
        <input type="number" min="0" name="bathrooms" value="{{ old('bathrooms', $unit->bathrooms) }}" class="input" required>
    </div>
    <div>
        <label class="label">Base rent ({{ config('app.currency_symbol') }}) *</label>
        <input type="number" step="0.01" min="0" name="base_rent" value="{{ old('base_rent', $unit->base_rent) }}" class="input" required>
    </div>
    <div>
        <label class="label">Status *</label>
        <select name="status" class="select" required>
            @foreach(['vacant', 'occupied', 'maintenance'] as $s)
                <option value="{{ $s }}" @selected(old('status', $unit->status) === $s)>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
    </div>
    <div class="md:col-span-2">
        <label class="label">Notes</label>
        <textarea name="notes" rows="2" class="textarea">{{ old('notes', $unit->notes) }}</textarea>
    </div>
</div>
<div class="mt-6 flex gap-2">
    <button class="btn btn-primary">Save</button>
    <a href="{{ route('units.index') }}" class="btn btn-ghost">Cancel</a>
</div>
