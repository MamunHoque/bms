@csrf
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div><label class="label">Name *</label><input name="name" value="{{ old('name', $tenant->name) }}" class="input" required></div>
    <div><label class="label">Phone</label><input name="phone" value="{{ old('phone', $tenant->phone) }}" class="input"></div>
    <div><label class="label">Email</label><input type="email" name="email" value="{{ old('email', $tenant->email) }}" class="input"></div>
    <div><label class="label">National ID</label><input name="national_id" value="{{ old('national_id', $tenant->national_id) }}" class="input"></div>
    <div><label class="label">Emergency contact</label><input name="emergency_contact" value="{{ old('emergency_contact', $tenant->emergency_contact) }}" class="input"></div>
    <div><label class="label">Occupation</label><input name="occupation" value="{{ old('occupation', $tenant->occupation) }}" class="input"></div>
    <div class="md:col-span-2"><label class="label">Notes</label><textarea name="notes" rows="2" class="textarea">{{ old('notes', $tenant->notes) }}</textarea></div>
</div>
<div class="mt-6 flex gap-2"><button class="btn btn-primary">Save</button><a href="{{ route('tenants.index') }}" class="btn btn-ghost">Cancel</a></div>
