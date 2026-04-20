<?php

namespace App\Http\Controllers;

use App\Models\Lease;
use App\Models\Tenant;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeaseController extends Controller
{
    public function index(Request $request)
    {
        $query = Lease::with('tenant', 'unit.building');
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        $leases = $query->latest('start_date')->paginate(20)->withQueryString();
        return view('leases.index', compact('leases'));
    }

    public function create()
    {
        $tenants = Tenant::orderBy('name')->get();
        $units = Unit::with('building')->where('status', 'vacant')->get();

        return view('leases.create', [
            'lease' => new Lease(['status' => 'active', 'rent_due_day' => 5]),
            'tenants' => $tenants,
            'units' => $units,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateLease($request);

        DB::transaction(function () use ($data) {
            $lease = Lease::create($data);
            // Mark unit as occupied
            $lease->unit->update(['status' => 'occupied']);
        });

        return redirect()->route('leases.index')->with('status', 'Lease created.');
    }

    public function show(Lease $lease)
    {
        $lease->load('tenant', 'unit.building', 'invoices.payments');
        return view('leases.show', compact('lease'));
    }

    public function edit(Lease $lease)
    {
        $tenants = Tenant::orderBy('name')->get();
        $units = Unit::with('building')->where(function ($q) use ($lease) {
            $q->where('status', 'vacant')->orWhere('id', $lease->unit_id);
        })->get();
        return view('leases.edit', compact('lease', 'tenants', 'units'));
    }

    public function update(Request $request, Lease $lease)
    {
        $data = $this->validateLease($request);
        $lease->update($data);
        return redirect()->route('leases.index')->with('status', 'Lease updated.');
    }

    public function destroy(Lease $lease)
    {
        DB::transaction(function () use ($lease) {
            $unit = $lease->unit;
            $lease->delete();
            // If this was the active lease, free up the unit.
            if ($unit && !$unit->leases()->where('status', 'active')->exists()) {
                $unit->update(['status' => 'vacant']);
            }
        });
        return redirect()->route('leases.index')->with('status', 'Lease deleted.');
    }

    public function end(Lease $lease)
    {
        DB::transaction(function () use ($lease) {
            $lease->update(['status' => 'ended', 'end_date' => $lease->end_date ?? now()->toDateString()]);
            if ($lease->unit) {
                $lease->unit->update(['status' => 'vacant']);
            }
        });
        return redirect()->route('leases.index')->with('status', 'Lease ended.');
    }

    protected function validateLease(Request $request): array
    {
        return $request->validate([
            'tenant_id' => ['required', 'exists:tenants,id'],
            'unit_id' => ['required', 'exists:units,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'monthly_rent' => ['required', 'numeric', 'min:0'],
            'security_deposit' => ['nullable', 'numeric', 'min:0'],
            'rent_due_day' => ['required', 'integer', 'min:1', 'max:28'],
            'status' => ['required', 'in:active,ended,terminated'],
            'notes' => ['nullable', 'string'],
        ]);
    }
}
