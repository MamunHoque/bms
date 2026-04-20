<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceRequest;
use App\Models\Tenant;
use App\Models\Unit;
use Illuminate\Http\Request;

class MaintenanceController extends Controller
{
    public function index(Request $request)
    {
        $query = MaintenanceRequest::with('unit.building', 'tenant');
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        $requests = $query->latest('reported_on')->paginate(20)->withQueryString();
        return view('maintenance.index', compact('requests'));
    }

    public function create()
    {
        $units = Unit::with('building')->orderBy('building_id')->orderBy('unit_number')->get();
        $tenants = Tenant::orderBy('name')->get();
        return view('maintenance.create', [
            'request' => new MaintenanceRequest(['priority' => 'normal', 'status' => 'open', 'reported_on' => now()->toDateString()]),
            'units' => $units,
            'tenants' => $tenants,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateIt($request);
        MaintenanceRequest::create($data);
        return redirect()->route('maintenance.index')->with('status', 'Maintenance request logged.');
    }

    public function edit(MaintenanceRequest $maintenance)
    {
        $units = Unit::with('building')->get();
        $tenants = Tenant::orderBy('name')->get();
        return view('maintenance.edit', ['request' => $maintenance, 'units' => $units, 'tenants' => $tenants]);
    }

    public function update(Request $request, MaintenanceRequest $maintenance)
    {
        $data = $this->validateIt($request);
        if ($data['status'] === 'resolved' && !$maintenance->resolved_on) {
            $data['resolved_on'] = now()->toDateString();
        }
        $maintenance->update($data);
        return redirect()->route('maintenance.index')->with('status', 'Maintenance request updated.');
    }

    public function destroy(MaintenanceRequest $maintenance)
    {
        $maintenance->delete();
        return redirect()->route('maintenance.index')->with('status', 'Maintenance request removed.');
    }

    protected function validateIt(Request $request): array
    {
        return $request->validate([
            'unit_id' => ['required', 'exists:units,id'],
            'tenant_id' => ['nullable', 'exists:tenants,id'],
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'priority' => ['required', 'in:low,normal,high,urgent'],
            'status' => ['required', 'in:open,in_progress,resolved,cancelled'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'reported_on' => ['required', 'date'],
            'resolved_on' => ['nullable', 'date'],
        ]);
    }
}
