<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    public function index(Request $request)
    {
        $query = Tenant::query();
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($w) use ($q) {
                $w->where('name', 'like', "%$q%")
                  ->orWhere('phone', 'like', "%$q%")
                  ->orWhere('email', 'like', "%$q%");
            });
        }
        $tenants = $query->orderBy('name')->paginate(20)->withQueryString();
        return view('tenants.index', compact('tenants'));
    }

    public function create()
    {
        return view('tenants.create', ['tenant' => new Tenant()]);
    }

    public function store(Request $request)
    {
        $data = $this->validateTenant($request);
        Tenant::create($data);
        return redirect()->route('tenants.index')->with('status', 'Tenant added.');
    }

    public function show(Tenant $tenant)
    {
        $tenant->load(['leases.unit.building', 'leases.invoices']);
        return view('tenants.show', compact('tenant'));
    }

    public function edit(Tenant $tenant)
    {
        return view('tenants.edit', compact('tenant'));
    }

    public function update(Request $request, Tenant $tenant)
    {
        $data = $this->validateTenant($request);
        $tenant->update($data);
        return redirect()->route('tenants.index')->with('status', 'Tenant updated.');
    }

    public function destroy(Tenant $tenant)
    {
        $tenant->delete();
        return redirect()->route('tenants.index')->with('status', 'Tenant deleted.');
    }

    protected function validateTenant(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:150'],
            'national_id' => ['nullable', 'string', 'max:40'],
            'emergency_contact' => ['nullable', 'string', 'max:150'],
            'occupation' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
        ]);
    }
}
