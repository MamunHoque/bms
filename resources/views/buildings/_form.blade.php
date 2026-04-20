@csrf
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label class="label">Name *</label>
        <input name="name" value="{{ old('name', $building->name) }}" class="input" required>
    </div>
    <div>
        <label class="label">City</label>
        <input name="city" value="{{ old('city', $building->city) }}" class="input">
    </div>
    <div class="md:col-span-2">
        <label class="label">Address *</label>
        <input name="address" value="{{ old('address', $building->address) }}" class="input" required>
    </div>
    <div>
        <label class="label">Total floors *</label>
        <input type="number" min="1" name="total_floors" value="{{ old('total_floors', $building->total_floors ?? 1) }}" class="input" required>
    </div>
    <div class="md:col-span-2">
        <label class="label">Notes</label>
        <textarea name="notes" rows="3" class="textarea">{{ old('notes', $building->notes) }}</textarea>
    </div>
</div>
<div class="mt-6 flex gap-2">
    <button class="btn btn-primary">Save</button>
    <a href="{{ route('buildings.index') }}" class="btn btn-ghost">Cancel</a>
</div>
