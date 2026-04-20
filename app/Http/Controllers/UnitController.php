<?php

namespace App\Http\Controllers;

use App\Models\Building;
use App\Models\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function index(Request $request)
    {
        $query = Unit::with('building', 'activeLease.tenant');

        if ($request->filled('building_id')) {
            $query->where('building_id', $request->building_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $units = $query->orderBy('building_id')->orderBy('floor')->orderBy('unit_number')->paginate(20);
        $buildings = Building::orderBy('name')->get();

        return view('units.index', compact('units', 'buildings'));
    }

    public function create()
    {
        $buildings = Building::orderBy('name')->get();
        if ($buildings->isEmpty()) {
            return redirect()->route('buildings.create')->with('status', 'Please create a building first.');
        }
        return view('units.create', [
            'unit' => new Unit(['status' => 'vacant', 'bedrooms' => 1, 'bathrooms' => 1, 'floor' => 1]),
            'buildings' => $buildings,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateUnit($request);
        Unit::create($data);
        return redirect()->route('units.index')->with('status', 'Unit created.');
    }

    public function show(Unit $unit)
    {
        $unit->load(['building', 'leases.tenant', 'utilityReadings.utilityType']);
        return view('units.show', compact('unit'));
    }

    public function edit(Unit $unit)
    {
        $buildings = Building::orderBy('name')->get();
        return view('units.edit', compact('unit', 'buildings'));
    }

    public function update(Request $request, Unit $unit)
    {
        $data = $this->validateUnit($request, $unit->id);
        $unit->update($data);
        return redirect()->route('units.index')->with('status', 'Unit updated.');
    }

    public function destroy(Unit $unit)
    {
        $unit->delete();
        return redirect()->route('units.index')->with('status', 'Unit deleted.');
    }

    protected function validateUnit(Request $request, $ignoreId = null): array
    {
        return $request->validate([
            'building_id' => ['required', 'exists:buildings,id'],
            'unit_number' => ['required', 'string', 'max:30'],
            'floor' => ['required', 'integer', 'min:0', 'max:200'],
            'bedrooms' => ['required', 'integer', 'min:0', 'max:20'],
            'bathrooms' => ['required', 'integer', 'min:0', 'max:20'],
            'size_sqft' => ['nullable', 'integer', 'min:0'],
            'base_rent' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'in:vacant,occupied,maintenance'],
            'notes' => ['nullable', 'string'],
        ]);
    }
}
